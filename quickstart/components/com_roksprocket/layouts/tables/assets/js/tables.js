/*!
 * @version   $Id: tables.js 10889 2013-05-30 07:48:35Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){
	if (typeof this.RokSprocket == 'undefined') this.RokSprocket = {};
	else Object.merge(this.RokSprocket, {Tables: null});

	var Tables = new Class({

		Implements: [Options, Events],

		options: {
			settings: {}
		},

		initialize: function(options){
			this.setOptions(options);

			this.tables = document.getElements('[data-tables]');
			this.settings = {};
			this.timers = {};
			this.statuses = {};
			this.curve = Browser.opera ? {equation: 'ease-in-out'} : {curve: 'cubic-bezier(0.37,0.61,0.59,0.87)'};

		},

		attach: function(list, settings){
			list = typeOf(list) == 'number' ?
					document.getElements('[data-tables=' + this.getID(list) + ']')
					:
					list;
			settings = typeOf(settings) == 'string' ? JSON.decode(settings) : settings;

			var containers = (list ? new Elements([list]).flatten() : this.tables);

			containers.each(function(container){
				container.store('roksprocket:tables:attached', true);

				this.setSettings(container, settings, 'restore');

				var relay = {
					mouseenter: container.retrieve('roksprocket:tables:mouseenter', function(event){
						this.stopTimer.call(this, container);
						this.pause.call(this, container);
					}.bind(this)),

					mouseleave: container.retrieve('roksprocket:tables:mouseleave', function(event){
						this.resume.call(this, container);
						this.startTimer.call(this, container);
					}.bind(this)),

					page: container.retrieve('roksprocket:tables:relay', function(event, page){
						if (event) event.preventDefault();
						this.toPage.call(this, container, page);
					}.bind(this)),

					next: container.retrieve('roksprocket:tables:next', function(event, element){
						this.direction.call(this, event, container, element, 'next');
					}.bind(this)),

					previous: container.retrieve('roksprocket:tables:previous', function(event, element){
						this.direction.call(this, event, container, element, 'previous');
					}.bind(this)),

					swipe: container.retrieve('roksprocket:tables:swipe', function(event, element){
						event.preventDefault();
						this.direction.call(this, event, container, element, (event.direction == 'right' ? 'previous' : 'next'));
					}.bind(this))
				};

				['mouseenter', 'mouseleave'].each(function(type){
					container.addEvent(type, relay[type]);
				});

				['page', 'next', 'previous'].each(function(dir, i){
					var query = '[data-tables-' + dir + ']';
					if (i > 0) query += ', [data-' + dir + ']';

					container.addEvent('click:relay(' + query + ')', relay[dir]);
				}, this);

				container.retrieve('roksprocket:tables:ajax', new RokSprocket.Request({
					model: 'tables',
					model_action: 'getPage',
					onRequest: this.onRequest.bind(this, container),
					onSuccess: function(response){
						this.onSuccess(response, container, container.retrieve('roksprocket:tables:ajax'));
					}.bind(this)
				}));

				if (Browser.Features.Touch) container.addEvent('swipe', relay['swipe']);

				var active = container.getElement('[data-tables-page].active');
				if (!active) this.toPage(container, 0);
				else {
					if (this.getSettings(container).autoplay && this.getSettings(container).autoplay.toInt())
						this.startTimer(container);
				}

			}, this);
		},

		detach: function(list){
			list = typeOf(list) == 'number' ?
					document.getElements('[data-tables=' + this.getID(list) + ']')
					:
					list;

			var containers = (list ? new Elements([list]).flatten() : this.tables);

			containers.each(function(container){
				container.store('roksprocket:tables:attached', false);
				var relay = {
					mouseenter: container.retrieve('roksprocket:tables:mouseenter'),
					mouseleave: container.retrieve('roksprocket:tables:mouseleave'),
					page: container.retrieve('roksprocket:tables:relay'),
					next: container.retrieve('roksprocket:tables:next'),
					previous: container.retrieve('roksprocket:tables:previous')
				};

				['mouseenter', 'mouseleave'].each(function(type){
					container.removeEvent(type, relay[type]);
				});

				['page', 'next', 'previous'].each(function(dir, i){
					var query = '[data-tables-' + dir + ']';
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
			if (!container) container = document.getElements('[data-tables]');
			if (typeOf(container) == 'number') container = document.getElement('[data-tables='+container+']');
			if (typeOf(container) == 'string') container = document.getElement(container);

			return container;
		},

		getID: function(id){
			if (typeOf(id) == 'number') id = document.getElement('[data-tables='+id+']');
			if (typeOf(id) == 'string') id = document.getElement(id);
			return !id ? id : id.get('data-tables');
		},

		toPage: function(container, page){
			container = this.getContainer(container);
			page = (typeOf(page) == 'element') ? page.get('data-tables-page') : page;
			if (!container.retrieve('roksprocket:tables:attached')) return;

			var tables = container.getElements('[data-tables-page]'),
				ajax = container.retrieve('roksprocket:tables:ajax');

			if (!tables.length) return;

			if (page > tables.length) page = 1;
			if (page < 1) page = tables.length;

			if (tables[page - 1].hasClass('active')) return;

			if (!ajax.isRunning()){
				ajax.cancel().setParams({
					moduleid: container.get('data-tables'),
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
			if (!container.retrieve('roksprocket:tables:attached')) return;
			if (typeOf(container) == 'elements') return this.nextAll(container, element);

			var tables = container.getElements('[data-tables-page]');
			if (!tables.length) return;

			var	current = container.getElement('[data-tables-page].active').get('data-tables-page'),
				next = current.toInt() + 1;

			if (next > tables.length) next = 1;
			this.dir = 'right';
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
			if (!container.retrieve('roksprocket:tables:attached')) return;
			if (typeOf(container) == 'elements') return this.nextAll(container, element);

			var tables = container.getElements('[data-tables-page]');
			if (!tables.length) return;

			var	current = container.getElement('[data-tables-page].active').get('data-tables-page'),
				previous = current.toInt() - 1;

			if (previous < 1) previous = tables.length;
			this.dir = 'left';
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
			if (!container.retrieve('roksprocket:tables:attached')) return;

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
			var items = container.getElement('[data-tables-items]'),
				itemList = container.getElements('[data-tables-item]'),
				html = response.getPath('payload.html'),
				page = response.getPath('payload.page'),
				settings = this.getSettings(container),
				animation = settings.animation || 'fadeDelay';

			container.removeClass('loading');

			var dummy = new Element('div', {html: html}),
				elements = dummy.getChildren(), rand, anims;

			if (animation == 'random'){
				animation = Object.keys(Object.merge({}, this.Animations)).getRandom();
			}

			if (!this.Animations[animation]) animation = 'fadeDelay';

			settings.callback = function(){
				if (settings.autoplay && settings.autoplay.toInt()) this.startTimer(container);
			}.bind(this);

			this.Animations[animation].call(this, items, itemList, elements, settings);

			this._switchPage(container, page);
		},

		_switchPage: function(container, page){
			var tables = container.getElements('[data-tables-page]');

			tables.removeClass('active');
			tables[page - 1].addClass('active');
		}

	});

	var Animations = {
		fade: function(){
			var items = arguments[0],
				itemList = arguments[1],
				elements = arguments[2],
				settings = arguments[3],
				options = {
					duration: this.AnimationsSpeed.fade.duration,
					curve: this.curve,
					callback: function(){
						items.empty().adopt(elements);
						options.callback = settings.callback || function(){};
						moofx(items).animate({opacity: 1}, options);
					}
				};

			moofx(items).animate({opacity: 0}, options);
		},

		fadeDelay: function(){
			var items = arguments[0],
				itemList = arguments[1],
				elements = arguments[2],
				settings = arguments[3],
				options = {duration: this.AnimationsSpeed.fadeDelay.duration, curve: this.curve};

			if (this.dir == 'left') itemList.reverse();

			itemList.forEach(function(item, i){
				(function(){
					if (i == itemList.length - 1){
						options.callback = function(){
							moofx(elements).style({opacity: 0});
							items.empty().adopt(elements);
							options.callback = settings.callback || function(){};
							if (this.dir == 'left') elements.reverse();

							elements.forEach(function(element, j){
								(function(){
									moofx(elements[j]).animate({'opacity': 1}, options);
								}).delay(j * this.AnimationsSpeed.fadeDelay.delay);
							}, this);
						}.bind(this);
					}


					moofx(item).animate({opacity: 0}, options);

				}).delay(i * this.AnimationsSpeed.fadeDelay.delay, this);
			}, this);
		},

		slide: function(){
			var items = arguments[0],
				itemList = arguments[1],
				elements = arguments[2],
				settings = arguments[3],
				options = {
					duration: this.AnimationsSpeed.slide.duration,
					curve: this.curve,
					callback: function(){
						items.empty().adopt(elements);
						options.callback = settings.callback || function(){};
						distance = items.getSize().x + 25;

						if (this.dir == 'left'){
							moofx(elements).style({right: distance, position: 'relative', left: 'inherit'});
							moofx(elements).animate({right: 0}, options);
						} else {
							moofx(elements).style({left: distance, position: 'relative', right: 'inherit'});
							moofx(elements).animate({left: 0}, options);
						}

					}.bind(this)
				},
				distance = 0;

			moofx(itemList).style({position: 'relative'});
			distance = items.getSize().x + 25;

			if (this.dir == 'left') moofx(itemList).animate({left: distance, right: 'inherit'}, options);
			else moofx(itemList).animate({right: distance, left: 'inherit'}, options);

		},

		flyIn: function(){
			var items = arguments[0],
				itemList = arguments[1],
				elements = arguments[2],
				settings = arguments[3],
				options = {duration: this.AnimationsSpeed.flyIn.duration, curve: this.curve},
				distance = 0;

			if (this.dir == 'left') itemList.reverse();

			itemList.forEach(function(item, i){
				(function(){
					if (i == itemList.length - 1){
						options.callback = function(){
							items.empty().adopt(elements);
							options.callback = null;
							if (this.dir == 'left') elements.reverse();

							elements.forEach(function(element, j){
								distance = items.getSize().x + (elements[j].getSize().x / (j + 1)) + 25;
								if (this.dir == 'left') moofx(elements).style({right: distance, position: 'relative', left: 'inherit'});
								else moofx(elements).style({left: distance, position: 'relative', right: 'inherit'});


								(function(){
									if (j == elements.length - 1){
										options.callback = settings.callback || function(){};
									}

									if (this.dir == 'left') moofx(elements[j]).animate({right: 0}, options);
									else moofx(elements[j]).animate({left: 0}, options);
								}).delay(j * this.AnimationsSpeed.flyIn.delay, this);
							}, this);
						}.bind(this)
					}

					moofx(item).style({position: 'relative'});
					distance = items.getSize().x + (item.getSize().x / (i + 1)) + 25;

					if (this.dir == 'left') moofx(item).animate({left: distance, right: 'inherit'}, options);
					else moofx(item).animate({right: distance, left: 'inherit'}, options);

				}).delay(i * this.AnimationsSpeed.flyIn.delay, this);
			}, this);
		},

		fallDown: function(){
			var items = arguments[0],
				itemList = arguments[1],
				elements = arguments[2],
				settings = arguments[3],
				options = {duration: this.AnimationsSpeed.fallDown.duration, curve: this.curve},
				distance = 0;

			if (this.dir == 'left') itemList.reverse();

			itemList.forEach(function(item, i){
				(function(){
					if (i == itemList.length - 1){
						options.callback = function(){
							items.empty().adopt(elements);
							options.callback = null;
							if (this.dir == 'left') elements.reverse();

							elements.forEach(function(element, j){
								distance = items.getSize().y + (elements[j].getSize().y / (j + 1)) + 25;
								moofx(elements).style({bottom: distance, position: 'relative', top: 'inherit'});

								(function(){
									if (j == elements.length - 1){
										options.callback = function(){
											elements.set('style', null);
											settings.callback.call(this);
										}
									}

									moofx(elements[j]).animate({bottom: 0}, options);
								}).delay(j * this.AnimationsSpeed.fallDown.delay, this);
							}, this);
						}.bind(this)
					}

					moofx(item).style({position: 'relative'});
					distance = items.getSize().y + (item.getSize().y / (i + 1)) + 25;

					moofx(item).animate({top: distance, bottom: 'inherit'}, options);

				}).delay(i * this.AnimationsSpeed.fallDown.delay, this);
			}, this);
		},

		floatUp: function(){
			var items = arguments[0],
				itemList = arguments[1],
				elements = arguments[2],
				settings = arguments[3],
				options = {duration: this.AnimationsSpeed.floatUp.duration, curve: this.curve},
				distance = 0;

			if (this.dir == 'left') itemList.reverse();

			itemList.forEach(function(item, i){
				(function(){
					if (i == itemList.length - 1){
						options.callback = function(){
							items.empty().adopt(elements);
							options.callback = null;
							if (this.dir == 'left') elements.reverse();

							elements.forEach(function(element, j){
								distance = items.getSize().y + (elements[j].getSize().y / (j + 1)) + 25;
								moofx(elements).style({top: distance, position: 'relative', bottom: 'inherit'});

								(function(){
									if (j == elements.length - 1){
										options.callback = function(){
											elements.set('style', null);
											settings.callback.call(this);
										}
									}
									moofx(elements[j]).animate({top: 0}, options);
								}).delay(j * this.AnimationsSpeed.floatUp.delay, this);
							}, this);
						}.bind(this)
					}

					moofx(item).style({position: 'relative'});
					distance = items.getSize().y + (item.getSize().y / (i + 1)) + 25;

					moofx(item).animate({bottom: distance, top: 'inherit'}, options);

				}).delay(i * this.AnimationsSpeed.floatUp.delay, this);
			}, this);
		},

		scaleOut: function(){
			var items = arguments[0],
				itemList = arguments[1],
				elements = arguments[2],
				settings = arguments[3],
				options = {duration: this.AnimationsSpeed.scaleOut.duration, curve: this.curve};

			if (this.dir == 'left') itemList.reverse();

			itemList.forEach(function(item, i){
				(function(){
					if (i == itemList.length - 1){
						options.callback = function(){
							items.empty().adopt(elements);
							options.callback = null;
							if (this.dir == 'left') elements.reverse();

							elements.forEach(function(element, j){
								moofx(elements).style({transform: 'scale(0)', opacity: 0});

								if (j == elements.length - 1){
									options.callback = settings.callback || function(){};
								}

								(function(){
									moofx(elements[j]).animate({transform: 'scale(1)', opacity: 1}, options);
								}).delay(j * this.AnimationsSpeed.scaleOut.delay, this);
							}, this);
						}.bind(this)
					}

					moofx(item).style({'transform-origin': 'center'});
					moofx(item).animate({transform: 'scale(0)', opacity: 0}, options);

				}).delay(i * this.AnimationsSpeed.scaleOut.delay, this);
			}, this);
		},

		scaleIn: function(){
			var items = arguments[0],
				itemList = arguments[1],
				elements = arguments[2],
				settings = arguments[3],
				options = {duration: this.AnimationsSpeed.scaleIn.duration, curve: this.curve};

			if (this.dir == 'left') itemList.reverse();
			items.store('overflow', items.getStyle('overflow'));
			items.setStyle('overflow', 'visible');

			itemList.forEach(function(item, i){
				(function(){
					if (i == itemList.length - 1){
						options.callback = function(){
							items.empty().adopt(elements);
							options.callback = null;
							if (this.dir == 'left') elements.reverse();

							elements.forEach(function(element, j){
								moofx(elements).style({transform: 'scale(1.5)', opacity: 0});

								if (j == elements.length - 1){
									options.callback = function(){
										items.setStyle('overflow', null);
										settings.callback.call(this);
									}
								}

								(function(){
									moofx(elements[j]).animate({transform: 'scale(1)', opacity: 1}, options);
								}).delay(j * this.AnimationsSpeed.scaleIn.delay, this);
							}, this);
						}.bind(this)
					}

					moofx(item).style({'transform-origin': 'center'});
					moofx(item).animate({transform: 'scale(1.5)', opacity: 0}, options);

				}).delay(i * this.AnimationsSpeed.scaleIn.delay, this);
			}, this);
		}
	};


	Tables.prototype.Animations = Animations;

	this.RokSprocket.Tables = Tables;

})());
