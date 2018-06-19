/*!
 * @version   $Id: lists.js 10889 2013-05-30 07:48:35Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){
	if (typeof this.RokSprocket == 'undefined') this.RokSprocket = {};
	else Object.merge(this.RokSprocket, {Lists: null});

	var Lists = new Class({

		Implements: [Options, Events],

		options: {
			settings: {}
		},

		initialize: function(options){
			this.setOptions(options);

			this.lists = document.getElements('[data-lists]');
			this.settings = {};
			this.timers = {};
			this.statuses = {};

		},

		attach: function(list, settings){
			list = typeOf(list) == 'number' ?
					document.getElements('[data-lists=' + this.getID(list) + ']')
					:
					list;
			settings = typeOf(settings) == 'string' ? JSON.decode(settings) : settings;

			var containers = (list ? new Elements([list]).flatten() : this.lists);

			containers.each(function(container){
				container.store('roksprocket:lists:attached', true);

				this.setSettings(container, settings, 'restore');

				var relay = {
					mouseenter: container.retrieve('roksprocket:lists:mouseenter', function(event){
						this.stopTimer.call(this, container);
						this.pause.call(this, container);
					}.bind(this)),

					mouseleave: container.retrieve('roksprocket:lists:mouseleave', function(event){
						this.resume.call(this, container);
						this.startTimer.call(this, container);
					}.bind(this)),

					page: container.retrieve('roksprocket:lists:relay', function(event, page){
						if (event) event.preventDefault();
						this.toPage.call(this, container, page);
					}.bind(this)),

					next: container.retrieve('roksprocket:lists:next', function(event, element){
						this.direction.call(this, event, container, element, 'next');
					}.bind(this)),

					previous: container.retrieve('roksprocket:lists:previous', function(event, element){
						this.direction.call(this, event, container, element, 'previous');
					}.bind(this)),

					swipe: container.retrieve('roksprocket:lists:swipe', function(event, element){
						event.preventDefault();
						this.direction.call(this, event, container, element, (event.direction == 'right' ? 'previous' : 'next'));
					}.bind(this))
				};

				['mouseenter', 'mouseleave'].each(function(type){
					container.addEvent(type, relay[type]);
				});

				['page', 'next', 'previous'].each(function(dir, i){
					var query = '[data-lists-' + dir + ']';
					if (i > 0) query += ', [data-' + dir + ']';

					container.addEvent('click:relay(' + query + ')', relay[dir]);
				}, this);

				container.retrieve('roksprocket:lists:ajax', new RokSprocket.Request({
					model: 'lists',
					model_action: 'getPage',
					onRequest: this.onRequest.bind(this, container),
					onSuccess: function(response){
						this.onSuccess(response, container, container.retrieve('roksprocket:lists:ajax'));
					}.bind(this)
				}));

				if (Browser.Features.Touch) container.addEvent('swipe', relay['swipe']);

				var active = container.getElement('[data-lists-page].active');
				if (!active) this.toPage(container, 0);
				else {
					if (this.getSettings(container).autoplay && this.getSettings(container).autoplay.toInt())
						this.startTimer(container);
				}

				this._setAccordion(container);

			}, this);
		},

		detach: function(list){
			list = typeOf(list) == 'number' ?
					document.getElements('[data-lists=' + this.getID(list) + ']')
					:
					list;

			var containers = (list ? new Elements([list]).flatten() : this.lists);

			containers.each(function(container){
				container.store('roksprocket:lists:attached', false);
				var relay = {
					mouseenter: container.retrieve('roksprocket:lists:mouseenter'),
					mouseleave: container.retrieve('roksprocket:lists:mouseleave'),
					page: container.retrieve('roksprocket:lists:relay'),
					next: container.retrieve('roksprocket:lists:next'),
					previous: container.retrieve('roksprocket:lists:previous')
				};

				['mouseenter', 'mouseleave'].each(function(type){
					container.removeEvent(type, relay[type]);
				});

				['page', 'next', 'previous'].each(function(dir, i){
					var query = '[data-lists-' + dir + ']';
					if (i > 0) query += ', [data-' + dir + ']';

					container.removeEvent('click:relay(' + query + ')', relay[dir]);
				}, this);

				if (Browser.Features.Touch) container.removeEvent('swipe', relay['swipe']);

			}, this);
		},

		setSettings: function(container, settings, restore){
			var id = this.getID(container),
				options = Object.clone(this.options.settings);

			if (!restore || !this.settings['id-' + id]){
				this.settings['id-' + id] = Object.merge(options, settings || options);
			}
		},

		getSettings: function(container){
			var id = this.getID(container);

			return this.settings['id-' + id];
		},

		getContainer: function(container){
			if (!container) container = document.getElements('[data-lists]');
			if (typeOf(container) == 'number') container = document.getElement('[data-lists='+container+']');
			if (typeOf(container) == 'string') container = document.getElement(container);

			return container;
		},

		getID: function(id){
			if (typeOf(id) == 'number') id = document.getElement('[data-lists='+id+']');
			if (typeOf(id) == 'string') id = document.getElement(id);
			return !id ? id : id.get('data-lists');
		},

		toPage: function(container, page){
			container = this.getContainer(container);
			page = (typeOf(page) == 'element') ? page.get('data-lists-page') : page;
			if (!container.retrieve('roksprocket:lists:attached')) return;

			var lists = container.getElements('[data-lists-page]'),
				ajax = container.retrieve('roksprocket:lists:ajax');

			if (!lists.length) return;

			if (page > lists.length) page = 1;
			if (page < 1) page = lists.length;

			if (lists[page - 1].hasClass('active')) return;

			if (!ajax.isRunning()){
				ajax.cancel().setParams({
					moduleid: container.get('data-lists'),
					page: page
				}).send();
			}
		},

		direction: function(event, container, element, dir){
			if (event) event.preventDefault();

			dir = dir || 'next';
			this[dir](container, element);
		},

		next: function(container, element){
			container = this.getContainer(container);
			if (!container.retrieve('roksprocket:lists:attached')) return;
			if (typeOf(container) == 'elements') return this.nextAll(container, element);

			var lists = container.getElements('[data-lists-page]');
			if (!lists.length) return;

			var	current = container.getElement('[data-lists-page].active').get('data-lists-page'),
				next = current.toInt() + 1;

			if (next > lists.length) next = 1;
			this.toPage(container, next);
		},

		nextAll: function(containers, element){
			containers = this.getContainer(containers);
			if (typeOf(containers) == 'element') return this.next(containers, element);

			containers.each(function(container){
				this.next(container, element);
			}, this);
		},

		previous: function(container, element){
			container = this.getContainer(container);
			if (!container.retrieve('roksprocket:lists:attached')) return;
			if (typeOf(container) == 'elements') return this.nextAll(container, element);

			var lists = container.getElements('[data-lists-page]');
			if (!lists.length) return;

			var	current = container.getElement('[data-lists-page].active').get('data-lists-page'),
				previous = current.toInt() - 1;

			if (previous < 1) previous = lists.length;
			this.toPage(container, previous);
		},

		previousAll: function(containers, element){
			containers = this.getContainer(containers);
			if (typeOf(containers) == 'element') return this.previous(containers, element);

			containers.each(function(container){
				this.previous(container, element);
			}, this);
		},

		startTimer: function(container){
			container = this.getContainer(container);
			if (!container.retrieve('roksprocket:lists:attached')) return;

			var settings = this.getSettings(container),
				id = this.getID(container),
				status = this.statuses['id-' + id],
				autoplay = settings.autoplay.toInt(),
				delay = (settings.delay.toInt()) * 1000;

			clearTimeout(this.timers['id-' + id]);
			if (autoplay && status != 'pause') this.timers['id-' + id] = this.next.delay(delay, this, container);
		},

		stopTimer: function(container){
			container = this.getContainer(container);

			var id = this.getID(container);
			clearTimeout(this.timers['id-' + id]);
		},

		pause: function(container){
			container = this.getContainer(container);

			var id = this.getID(container);
			this.statuses['id-' + id] = 'pause';
		},

		resume: function(container){
			container = this.getContainer(container);

			var id = this.getID(container);
			this.statuses['id-' + id] = 'play';
		},

		onRequest: function(container){
			container.addClass('loading');
		},

		onSuccess: function(response, container){
			var items = container.getElement('[data-lists-items]'),
				html = response.getPath('payload.html'),
				page = response.getPath('payload.page'),
				settings = this.getSettings(container);

			container.removeClass('loading');

			var dummy = new Element('div', {html: html}),
				elements = dummy.getChildren()
							.setStyle('opacity', 0)
							.set('tween', {duration: 250, transition: 'quad:in:out'});

			items.empty().adopt(elements);
			elements.tween('opacity', 1);
			this._switchPage(container, page);
			this._setAccordion(container);

			if (settings.autoplay && settings.autoplay.toInt()) this.startTimer(container);
		},

		_setAccordion: function(container){
			if (!this.getSettings(container).accordion.toInt()) return;

			var togglers = container.getElements('[data-lists-toggler]'),
				elements = container.getElements('[data-lists-content]');

			container.store('roksprocket:lists:accordion', new Fx.Accordion(togglers, elements, {
				duration: 400,
				transition: 'quad:out',
				show: 1,
				resetHeight: true,
				initialDisplayFx: false,
				onActive: function(toggler, element){
					element.getParent('[data-lists-item]').addClass('active');
				},
				onBackground: function(toggler, element){
					element.getParent('[data-lists-item]').removeClass('active');
				}
			}));
		},

		_switchPage: function(container, page){
			var lists = container.getElements('[data-lists-page]');

			lists.removeClass('active');
			lists[page - 1].addClass('active');
		}

	});

	this.RokSprocket.Lists = Lists;

})());
