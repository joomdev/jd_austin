/*!
 * @version   $Id: features.js 30547 2018-03-08 21:14:11Z reggie $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){
	if (typeof this.RokSprocket == 'undefined') this.RokSprocket = {};
	else Object.merge(this.RokSprocket, {Features: null});

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

	var Features = new Class({

		Implements: [Options, Events],

		animations: {},

		options: {
			data: 'features',
			settings: {}
		},

		initialize: function(options){
			this.setOptions(options);
			this.data = this.options.data;

			this.features = document.getElements('[data-' + this.data + ']');
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

			var containers = (feature ? new Elements([feature]).flatten() : this.features);

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

					pagination: container.retrieve('roksprocket:' + this.data + ':relay', function(event, pane){
						this.toPane.call(this, event, container, pane);
					}.bind(this)),

					next: container.retrieve('roksprocket:' + this.data + ':next', function(event, element){
						this.direction.call(this, event, container, element, 'next');
					}.bind(this)),

					previous: container.retrieve('roksprocket:' + this.data + ':previous', function(event, element){
						this.direction.call(this, event, container, element, 'previous');
					}.bind(this)),

					swipe: container.retrieve('roksprocket:' + this.data + ':swipe', function(event, element){
						event.preventDefault();
						this.direction.call(this, event, container, element, (event.direction == 'right' ? 'previous' : 'next'));
					}.bind(this))
				};

				['mouseenter', 'mouseleave'].each(function(type){
					container.addEvent(type, relay[type]);
				});

				['pagination', 'next', 'previous'].each(function(dir, i){
					var query = '[data-' + this.data + '-' + dir + ']';
					if (i > 0) query += ', [data-' + dir + ']';

					container.addEvent('click:relay(' + query + ')', relay[dir]);
				}, this);

				if (Browser.Features.Touch) container.addEvent('swipe', relay['swipe']);

				if (this.getSettings(container).autoplay && this.getSettings(container).autoplay.toInt()) this.startTimer(container);

				//this.toPosition(container, 0);
			}, this);
		},

		detach: function(feature){
			feature = typeOf(feature) == 'number' ?
						document.getElements('[data-' + this.data + '=' + this.getID(feature) + ']')
						:
						feature;

			var containers = (feature ? new Elements([feature]).flatten() : this.features);

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

		getRandom: function(){
			var random = Number.random(0, Object.getLength(this.animations) - 1),
				keys = Object.keys(this.animations);

			return this.animations[keys[random]];
		},

		toPosition: function(container, position){
			container = this.getContainer(container);
			if (!container.retrieve('roksprocket:' + this.data + ':attached')) return;

			this.stopTimer(container);

			var features = container.getElements('[data-' + this.data + '-pagination]'),
				current = container.getElement('[data-' + this.data + '-pagination][class=active]');

			if (!features.length) return;

			if (features[position] && features[position].hasClass('active')) return;

			if (position > features.length - 1) position = 0;
			if (position < 0) position = features.length - 1;

			if (features.length){
				features.removeClass('active');
				features[position].addClass('active');
			}

			this.animate(container, current.get('data-' + this.data + '-pagination') - 1, position);
		},

		toPane: function(event, container, pane){
			if (event) event.preventDefault();
			container = this.getContainer(container);
			if (!container.retrieve('roksprocket:' + this.data + ':attached')) return;

			var features = container.getElements('[data-' + this.data + '-pagination]'),
				position = pane.get('data-' + this.data + '-pagination') - 1;

			if (position == -1) throw new Error('RokSprocket Feature [' + this.data + ']: Instance ID "' + container.get('data-' + this.data) + '", index not found.');

			this.toPosition(container, position);
		},

		direction: function(event, container, element, dir){
			if (event) event.preventDefault();

			dir = dir || 'next';
			this[dir](container, element);
		},

		next: function(container, element){
			container = this.getContainer(container);
			if (!container.retrieve('roksprocket:' + this.data + ':attached')) return;
			if (typeOf(container) == 'elements') return this.nextAll(container, element);

			var pages = container.getElements('[data-' + this.data + '-pagination]');
			if (!pages.length) return;

			var active = pages.filter(function(page){ return page.hasClass('active'); }),
				position = pages.indexOf(active.length ? active[0] : '') || 0,
				next = position + 1;

			if (next > pages.length - 1) next = 0;
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

			var pages = container.getElements('[data-' + this.data + '-pagination]');
			if (!pages.length) return;

			var active = pages.filter(function(page){ return page.hasClass('active'); }),
				position = pages.indexOf(active.length ? active[0] : '') || 0,
				previous = position - 1;

			if (previous < 0) previous = pages.length - 1;
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
		},

		animate: function(container, from, to){
			var contents = container.getElements('[data-' + this.data + '-content]'),
				images = container.getElements('[data-' + this.data + '-image]'),
				settings = this.getSettings(container),
				styles = {
					content: {
						show: {display: 'block', 'z-index': 2},
						hide: {display: 'none', 'z-index': 1}
					},
					image: {
						show: {display: 'block', position: 'relative', 'z-index': 2},
						hide: {display: 'none', position: 'absolute', 'z-index': 1}
					}
				},
				current = {
					content: contents[from],
					image: images[from]
				},
				next = {
					content: contents[to],
					image: images[to]
				};

			Object.each(current, function(value, key){
				current[key].style(styles[key].hide);
				next[key].style(styles[key].show);
			}, this);

			if (settings.autoplay && settings.autoplay.toInt()) this.startTimer(container);

		},

		addAnimations: function(animations){
			Object.merge(this.animations, animations);
		}

	});

	Features.prototype.addAnimations({
		crossfade: {
			from: {opacity: 0},
			to: {opacity: 1}
		},
		fromTop: {
			from: {opacity: 0, top: '-50%'},
			to: {opacity: 1, top: '0%'}
		},
		fromTopLeft: {
			from: {opacity: 0,top: '-50%',left: '-50%'},
			to: {opacity: 1, top: '0%', left: '0%'}
		},
		fromTopRight: {
			from: {opacity: 0, top: '-50%', right: '-50%'},
			to: {opacity: 1, top: '0%', right: '0%'}
		},
		fromBottom: {
			from: {opacity: 0, top: '50%'},
			to: {opacity: 1, top: '0%'}
		},
		fromBottomLeft: {
			from: {opacity: 0, top: '75%', left: '-75%'},
			to: {opacity: 1, top: '0%', left: '0%'}
		},
		fromBottomRight: {
			from: {opacity: 0, right: '-50%', top: '50%'},
			to: {opacity: 1, right: '0%', top: '0%'}
		},
		fromLeft: {
			from: {opacity: 0, transform: 'translate3d(-50%, 0, 0)'},
			to: {opacity: 1, transform: 'translate3d(0, 0, 0)'}
		},
		fromRight: {
			from: {opacity: 0, transform: 'translate3d(50%, 0, 0)'},
			to: {opacity: 1, transform: 'translate3d(0, 0, 0)'}
		}
	});

	this.RokSprocket.Features = Features;

})());
