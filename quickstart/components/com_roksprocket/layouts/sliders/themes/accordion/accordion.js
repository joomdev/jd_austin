/*!
 * @version   $Id: sliders.js 10889 2013-05-30 07:48:35Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){
	if (typeof this.RokSprocket == 'undefined') this.RokSprocket = {};
	else Object.merge(this.RokSprocket, {Sliders: null});

	Element.implement({
		'styles': function(){
			var mu = moofx(this), result = mu.style.apply(mu, arguments);
			if (arguments.length == 1 && typeof arguments[0] == 'string') return result;
			return this;
		},
		'moofx': function(){
			var mu = moofx(this);
			mu.animate.apply(mu, arguments);
			return this;
		}
	});

	var Sliders = new Class({

		Implements: [Options, Events],

		animations: {},

		options: {
			data: 'accordion',
			settings: {}
		},

		initialize: function(options){
			this.setOptions(options);
			this.data = this.options.data;

			this.sliders = document.getElements('[data-' + this.data + ']');
			this.settings = {};
			this.timers = {};
			this.statuses = {};
		},

		attach: function(feature, settings){
			feature = typeOf(feature) == 'number' ?
						document.getElements('[data-' + this.data + '=' + this.getID(feature) + ']')
						:
						feature;
			settings = typeOf(settings) == 'string' ? JSON.decode(settings) : settings;

			var containers = (feature ? new Elements([feature]).flatten() : this.sliders);

			containers.each(function(container){
				container.store('roksprocket:' + this.data + ':attached', true);

				this.setSettings(container, settings, 'restore');

				var relay = {
					mouseenter: container.retrieve('roksprocket:' + this.data + ':mouseenter', function(event){
						this.stopTimer.call(this, container);
						this.pause.call(this, container);
					}.bind(this)),

					mouseleave: container.retrieve('roksprocket:' + this.data + ':mouseleave', function(event){
						this.resume.call(this, container);
						this.startTimer.call(this, container);
					}.bind(this)),

					next: container.retrieve('roksprocket:' + this.data + ':next', function(event, element){
						this.direction.call(this, event, container, element, 'next');
					}.bind(this)),

					previous: container.retrieve('roksprocket:' + this.data + ':previous', function(event, element){
						this.direction.call(this, event, container, element, 'previous');
					}.bind(this)),

					item: container.retrieve('roksprocket:' + this.data + ':item', function(event, element){
						var position = container.getElements('[data-'+this.data+'-item]').indexOf(element);
						this.toPosition(container, position);
					}.bind(this))
				};

				['mouseenter', 'mouseleave'].each(function(type){
					container.addEvent(type, relay[type]);
				});

				['next', 'previous'].each(function(dir, i){
					var query = '[data-' + this.data + '-' + dir + ']';
					if (i > 0) query += ', [data-' + dir + ']';

					container.addEvent('click:relay(' + query + ')', relay[dir]);
				}, this);

				container.addEvent('click:relay([data-' + this.data + '-item])', relay.item);

				if (container.getElements('[data-'+this.data+'-item]').length > 1) this.showArrows(container);

				this.toPosition(container, 0, true);

				if (this.getSettings(container).autoplay && this.getSettings(container).autoplay.toInt()) this.startTimer(container);

			}, this);
		},


		detach: function(feature){
			feature = typeOf(feature) == 'number' ?
						document.getElements('[data-' + this.data + '=' + this.getID(feature) + ']')
						:
						feature;

			var containers = (feature ? new Elements([feature]).flatten() : this.sliders);

			containers.each(function(container){
				container.store('roksprocket:' + this.data + ':attached', false);
				var relay = {
					mouseenter: container.retrieve('roksprocket:' + this.data + ':mouseenter'),
					mouseleave: container.retrieve('roksprocket:' + this.data + ':mouseleave'),
					pagination: container.retrieve('roksprocket:' + this.data + ':relay'),
					next: container.retrieve('roksprocket:' + this.data + ':next'),
					previous: container.retrieve('roksprocket:' + this.data + ':previous')
				};

				['mouseenter', 'mouseleave'].each(function(type){
					container.removeEvent(type, relay[type]);
				});

				['pagination', 'next', 'previous'].each(function(dir, i){
					var query = '[data-' + this.data + '-' + dir + ']';
					if (i > 0) query += ', [data-' + dir + ']';

					container.removeEvent('click:relay(' + query + ')', relay[dir]);
				}, this);

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
			if (!container) container = document.getElements('[data-' + this.data + ']');
			if (typeOf(container) == 'number') container = document.getElement('[data-' + this.data + '='+container+']');
			if (typeOf(container) == 'string') container = document.getElement(container);

			return container;
		},

		getID: function(id){
			if (typeOf(id) == 'number') id = document.getElement('[data-' + this.data + '='+id+']');
			if (typeOf(id) == 'string') id = document.getElement(id);
			return !id ? id : id.get('data-' + this.data);
		},

		toPosition: function(container, position, force){
			container = this.getContainer(container);
			if (!container.retrieve('roksprocket:' + this.data + ':attached')) return;

			this.stopTimer(container);

			var settings = this.getSettings(container),
				sliders  = container.getElements('[data-' + this.data + '-item]'),
				current  = container.getElement('[data-' + this.data + '-item].active'),
				closed   = container.getElement('[data-' + this.data + '-item]:not(.active)'),
				height   = settings.height_fixed;

			if (!sliders.length) return;

			if (sliders[position] && sliders[position].hasClass('active') && !force) return;

			if (position > sliders.length - 1) position = 0;
			if (position < 0) position = sliders.length - 1;

			if (sliders.length){
				if (settings.height_control == 'auto') height = (force) ? sliders[position].getSize().y : sliders[position].getFirst().getSize().y;

				var closedHeight = closed.getSize().y;
				current.moofx({height: closedHeight}, {duration: '300ms', equation: 'ease-in-out'});
				sliders.removeClass('active');
				sliders[position].addClass('active').setStyle('height', closedHeight);
				sliders[position].moofx({height: height}, {duration: '300ms', equation: 'ease-in-out'});
			}

			if (settings.autoplay && settings.autoplay.toInt()) this.startTimer(container);
		},

		direction: function(event, container, element, dir){
			if (event) event.preventDefault();

			dir = dir || 'next';
			this[dir](container, element);
		},

		showArrows: function(){
			var args = Array.slice(arguments),
				container = $(args[0]);
				arrows = args.splice(1);

			if (!arrows.length) arrows = ['next', 'previous'];

			arrows.forEach(function(arrow){
				arrow = container.getElement('[data-' + this.data + '-' + arrow + ']');
				if (arrow) arrow.setStyle('visibility', 'visible');
			}, this);
		},

		next: function(container, element){
			container = this.getContainer(container);
			if (!container.retrieve('roksprocket:' + this.data + ':attached')) return;
			if (typeOf(container) == 'elements') return this.nextAll(container, element);

			var sliders = container.getElements('[data-' + this.data + '-item]');
			if (!sliders.length) return;

			var active = sliders.filter(function(page){ return page.hasClass('active'); }),
				position = sliders.indexOf(active.length ? active[0] : '') || 0,
				next = position + 1;

			if (next > sliders.length - 1) next = 0;
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
			if (!container.retrieve('roksprocket:' + this.data + ':attached')) return;

			if (typeOf(container) == 'elements') return this.previousAll(container, element);

			var sliders = container.getElements('[data-' + this.data + '-item]');
			if (!sliders.length) return;

			var active = sliders.filter(function(page){ return page.hasClass('active'); }),
				position = sliders.indexOf(active.length ? active[0] : '') || 0,
				previous = position - 1;

			if (previous < 0) previous = sliders.length - 1;
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
			if (!container.retrieve('roksprocket:' + this.data + ':attached')) return;

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

	this.RokSprocket.Sliders = Sliders;

})());
