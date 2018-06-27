/*!
 * @version   $Id: headlines.js 10889 2013-05-30 07:48:35Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){
	if (typeof this.RokSprocket == 'undefined') this.RokSprocket = {};
	else Object.merge(this.RokSprocket, {Headlines: null});

	var Headlines = new Class({

		Implements: [Options, Events],

		options: {
			settings: {}
		},

		initialize: function(options){
			this.setOptions(options);

			this.headlines = document.getElements('[data-headlines]');
			this.settings = {};
			this.timers = {};
			this.statuses = {};
		},

		attach: function(headline, settings){
			headline = typeOf(headline) == 'number' ?
					document.getElements('[data-headlines=' + this.getID(headline) + ']')
					:
					headline;

			settings = typeOf(settings) == 'string' ? JSON.decode(settings) : settings;
			var containers = (headline ? new Elements([headline]).flatten() : this.headlines);

			containers.each(function(container){
				container.store('roksprocket:headlines:attached', true);

				this.setSettings(container, settings, 'restore');

				var relay = {
					mouseenter: container.retrieve('roksprocket:headlines:mouseenter', function(event){
						this.stopTimer.call(this, container);
						this.pause.call(this, container);
					}.bind(this)),

					mouseleave: container.retrieve('roksprocket:headlines:mouseleave', function(event){
						this.resume.call(this, container);
						this.startTimer.call(this, container);
					}.bind(this)),
					next: container.retrieve('roksprocket:headlines:next', function(event, element){
						this.direction.call(this, event, container, element, 'next');
					}.bind(this)),

					previous: container.retrieve('roksprocket:headlines:previous', function(event, element){
						this.direction.call(this, event, container, element, 'previous');
					}.bind(this)),

					swipe: container.retrieve('roksprocket:headlines:swipe', function(event, element){
						event.preventDefault();
						this.direction.call(this, event, container, element, (event.direction == 'right' ? 'previous' : 'next'));
					}.bind(this))
				};

				['mouseenter', 'mouseleave'].each(function(type){
					container.addEvent(type, relay[type]);
				});

				['next', 'previous'].each(function(dir, i){
					var query = '[data-headlines-' + dir + ']';
					if (i > 0) query += ', [data-' + dir + ']';

					container.addEvent('click:relay(' + query + ')', relay[dir]);
				}, this);

				if (Browser.Features.Touch) container.addEvent('swipe', relay['swipe']);

				var active = container.getElement('[data-headlines-item].active');

				if (!active) this.toPosition(container, 0);
				else {
					if (this.getSettings(container).autoplay && this.getSettings(container).autoplay.toInt())
						this.startTimer(container);
				}

			}, this);
		},

		detach: function(headline){
			headline = typeOf(headline) == 'number' ?
					document.getElements('[data-headlines=' + this.getID(headline) + ']')
					:
					headline;

			var containers = (headline ? new Elements([headline]).flatten() : this.headlines);

			containers.each(function(container){
				container.store('roksprocket:headlines:attached', false);
				var relay = {
					mouseenter: container.retrieve('roksprocket:headlines:mouseenter'),
					mouseleave: container.retrieve('roksprocket:headlines:mouseleave'),
					next: container.retrieve('roksprocket:headlines:next'),
					previous: container.retrieve('roksprocket:headlines:previous')
				};

				['mouseenter', 'mouseleave'].each(function(type){
					container.removeEvent(type, relay[type]);
				});

				['next', 'previous'].each(function(dir, i){
					var query = '[data-headlines-' + dir + ']';
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
			if (!container) container = document.getElements('[data-headlines]');
			if (typeOf(container) == 'number') container = document.getElement('[data-headlines='+container+']');
			if (typeOf(container) == 'string') container = document.getElement(container);

			return container;
		},

		getID: function(id){
			if (typeOf(id) == 'number') id = document.getElement('[data-headlines='+id+']');
			if (typeOf(id) == 'string') id = document.getElement(id);
			return !id ? id : id.get('data-headlines');
		},

		toPosition: function(container, position){
			container = this.getContainer(container);
			if (!container.retrieve('roksprocket:headlines:attached')) return;

			var headlines = container.getElements('[data-headlines-item]'),
				panels = container.getElements('[data-headlines-panel]'),
				settings = this.getSettings(container);

			if (!headlines.length) return;
			if (headlines[position].hasClass('active')) return;

			if (position > headlines.length - 1) position = 0;
			if (position < 0) position = headlines.length - 1;

			headlines.removeClass('active');
			headlines[position].addClass('active');

			if (settings.autoplay && settings.autoplay.toInt()) this.startTimer(container);
		},

		toHeadline: function(event, container, headline){
			if (event) event.preventDefault();
			container = this.getContainer(container);
			if (!container.retrieve('roksprocket:headlines:attached')) return;

			var headlines = container.getElements('[data-headlines-item]'),
				position = headlines.indexOf(headline);

			if (position == -1) throw new Error('RokSprocket Headlines: Instance ID "' + container.get('data-headlines') + '", index not found.');

			this.toPosition(container, position);
		},

		direction: function(event, container, element, dir){
			if (event) event.preventDefault();

			dir = dir || 'next';
			this[dir](container, element);
		},

		next: function(container, element){
			container = this.getContainer(container);
			if (!container.retrieve('roksprocket:headlines:attached')) return;
			if (typeOf(container) == 'elements') return this.nextAll(container, element);

			var headlines = container.getElements('[data-headlines-item]'),
				active = headlines.filter(function(headline){ return headline.hasClass('active'); }),
				position = headlines.indexOf(active.length ? active[0] : '') || 0,
				next = position + 1;

			if (next > headlines.length - 1) next = 0;
			this.toPosition(container, next);
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
			if (!container.retrieve('roksprocket:headlines:attached')) return;
			if (typeOf(container) == 'elements') return this.nextAll(container, element);

			var headlines = container.getElements('[data-headlines-item]'),
				active = headlines.filter(function(headline){ return headline.hasClass('active'); }),
				position = headlines.indexOf(active.length ? active[0] : '') || 0,
				previous = position - 1;

			if (previous < 0) previous = headlines.length - 1;
			this.toPosition(container, previous);
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
			if (!container.retrieve('roksprocket:headlines:attached')) return;

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
		}

	});

	this.RokSprocket.Headlines = Headlines;

})());
