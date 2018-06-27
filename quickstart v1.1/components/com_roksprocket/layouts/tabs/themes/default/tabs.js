/*!
 * @version   $Id: tabs.js 10889 2013-05-30 07:48:35Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){
	if (typeof this.RokSprocket == 'undefined') this.RokSprocket = {};
	else Object.merge(this.RokSprocket, {Tabs: null});

	var Tabs = new Class({

		Implements: [Options, Events],

		options: {
			settings: {}
		},

		initialize: function(options){
			this.setOptions(options);

			this.tabs = document.getElements('[data-tabs]');
			this.settings = {};
			this.timers = {};
			this.statuses = {};
		},

		attach: function(tab, settings){
			tab = typeOf(tab) == 'number' ?
					document.getElements('[data-tabs=' + this.getID(tab) + ']')
					:
					tab;

			settings = typeOf(settings) == 'string' ? JSON.decode(settings) : settings;
			var containers = (tab ? new Elements([tab]).flatten() : this.tabs);

			containers.each(function(container){
				container.store('roksprocket:tabs:attached', true);

				this.setSettings(container, settings, 'restore');

				var relay = {
					mouseenter: container.retrieve('roksprocket:tabs:mouseenter', function(event){
						this.stopTimer.call(this, container);
						this.pause.call(this, container);
					}.bind(this)),

					mouseleave: container.retrieve('roksprocket:tabs:mouseleave', function(event){
						this.resume.call(this, container);
						this.startTimer.call(this, container);
					}.bind(this)),

					navigation: container.retrieve('roksprocket:tabs:relay', function(event, tab){
						this.toTab.call(this, event, container, tab);
					}.bind(this)),

					next: container.retrieve('roksprocket:tabs:next', function(event, element){
						this.direction.call(this, event, container, element, 'next');
					}.bind(this)),

					previous: container.retrieve('roksprocket:tabs:previous', function(event, element){
						this.direction.call(this, event, container, element, 'previous');
					}.bind(this)),

					swipe: container.retrieve('roksprocket:tabs:swipe', function(event, element){
						event.preventDefault();
						this.direction.call(this, event, container, element, (event.direction == 'right' ? 'previous' : 'next'));
					}.bind(this))
				};

				['mouseenter', 'mouseleave'].each(function(type){
					container.addEvent(type, relay[type]);
				});

				['navigation', 'next', 'previous'].each(function(dir, i){
					var query = '[data-tabs-' + dir + ']';
					if (i > 0) query += ', [data-' + dir + ']';

					container.addEvent('click:relay(' + query + ')', relay[dir]);
				}, this);

				if (Browser.Features.Touch) container.getElements('[data-tabs-panel]').addEvent('swipe', relay['swipe']);

				var active = container.getElement('[data-tabs-navigation].active');
				if (!active) this.toPosition(container, 0);
				else {
					if (this.getSettings(container).autoplay && this.getSettings(container).autoplay.toInt())
						this.startTimer(container);
				}

			}, this);
		},

		detach: function(tab){
			tab = typeOf(tab) == 'number' ?
					document.getElements('[data-tabs=' + this.getID(tab) + ']')
					:
					tab;

			var containers = (tab ? new Elements([tab]).flatten() : this.tabs);

			containers.each(function(container){
				container.store('roksprocket:tabs:attached', false);
				var relay = {
					mouseenter: container.retrieve('roksprocket:tabs:mouseenter'),
					mouseleave: container.retrieve('roksprocket:tabs:mouseleave'),
					navigation: container.retrieve('roksprocket:tabs:relay'),
					next: container.retrieve('roksprocket:tabs:next'),
					previous: container.retrieve('roksprocket:tabs:previous')
				};

				['mouseenter', 'mouseleave'].each(function(type){
					container.removeEvent(type, relay[type]);
				});

				['navigation', 'next', 'previous'].each(function(dir, i){
					var query = '[data-tabs-' + dir + ']';
					if (i > 0) query += ', [data-' + dir + ']';

					container.removeEvent('click:relay(' + query + ')', relay[dir]);
				}, this);

				if (Browser.Features.Touch) container.getElements('[data-tabs-panel]').removeEvent('swipe', relay['swipe']);

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
			if (!container) container = document.getElements('[data-tabs]');
			if (typeOf(container) == 'number') container = document.getElement('[data-tabs='+container+']');
			if (typeOf(container) == 'string') container = document.getElement(container);

			return container;
		},

		getID: function(id){
			if (typeOf(id) == 'number') id = document.getElement('[data-tabs='+id+']');
			if (typeOf(id) == 'string') id = document.getElement(id);
			return !id ? id : id.get('data-tabs');
		},

		toPosition: function(container, position){
			container = this.getContainer(container);
			if (!container.retrieve('roksprocket:tabs:attached')) return;

			var tabs = container.getElements('[data-tabs-navigation]'),
				panels = container.getElements('[data-tabs-panel]'),
				settings = this.getSettings(container);

			if (!tabs.length || !panels.length) return;
			if (tabs[position].hasClass('active')) return;

			if (position > tabs.length - 1) position = 0;
			if (position < 0) position = tabs.length - 1;

			tabs.removeClass('active');
			panels.removeClass('active');
			tabs[position].addClass('active');
			panels[position].addClass('active');

			if (settings.autoplay && settings.autoplay.toInt()) this.startTimer(container);
		},

		toTab: function(event, container, tab){
			if (event) event.preventDefault();
			container = this.getContainer(container);
			if (!container.retrieve('roksprocket:tabs:attached')) return;

			var tabs = container.getElements('[data-tabs-navigation]'),
				panels = container.getElements('[data-tabs-panel]'),
				position = tabs.indexOf(tab);

			if (position == -1) throw new Error('RokSprocket Tabs: Instance ID "' + container.get('data-tabs') + '", index not found.');

			this.toPosition(container, position);
		},

		direction: function(event, container, element, dir){
			if (event) event.preventDefault();

			dir = dir || 'next';
			this[dir](container, element);
		},

		next: function(container, element){
			container = this.getContainer(container);
			if (!container.retrieve('roksprocket:tabs:attached')) return;
			if (typeOf(container) == 'elements') return this.nextAll(container, element);

			var tabs = container.getElements('[data-tabs-navigation]'),
				active = tabs.filter(function(tab){ return tab.hasClass('active'); }),
				position = tabs.indexOf(active.length ? active[0] : '') || 0,
				next = position + 1;

			if (next > tabs.length - 1) next = 0;
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
			if (!container.retrieve('roksprocket:tabs:attached')) return;
			if (typeOf(container) == 'elements') return this.nextAll(container, element);

			var tabs = container.getElements('[data-tabs-navigation]'),
				active = tabs.filter(function(tab){ return tab.hasClass('active'); }),
				position = tabs.indexOf(active.length ? active[0] : '') || 0,
				previous = position - 1;

			if (previous < 0) previous = tabs.length - 1;
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
			if (!container.retrieve('roksprocket:tabs:attached')) return;

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

	this.RokSprocket.Tabs = Tabs;

})());
