
((function(){
	if (typeof this.RokSprocket == 'undefined') this.RokSprocket = {};
	else Object.merge(this.RokSprocket, {Slideshow: null});

	var Slideshow = new Class({

		Extends: this.RokSprocket.Features,

		options: {
			curve: 'cubic-bezier(0.37,0.61,0.59,0.87)',
			duration: '400ms',
			data: 'slideshow',
			settings: {
				animation: 'crossfade',
				autoplay: false,
				delay: 5
			}
		},

		animate: function(container, from, to){
			var contents = container.getElements('[data-' + this.data + '-content]'),
				images = container.getElements('[data-' + this.data + '-image]'),
				settings = this.getSettings(container),
				animation = {
					content: this.animations.crossfade,
					image: this.animations[settings.animation] || this.animations.crossfade
				},
				current = {
					content: contents[from],
					image: images[from]
				},
				next = {
					content: contents[to],
					image: images[to]
				};

			if (Browser.ie && Browser.version < 8){
				contents.setStyle('zoom', 1);
				images.setStyle('zoom', 1);
			}

			Object.each(current, function(value, key){
				var transition = (key == 'content') ?
									animation[key]
									:
									settings.animation == 'random' ? this.getRandom() : animation[key];

				if (key == 'content') contents.styles(Object.merge({}, transition.from, {position: 'absolute'}));
				if (key == 'image') images.styles(Object.merge({}, transition.from, {position: 'absolute'}));
				current[key].styles(Object.merge({}, transition.to, {'z-index': 1}));

				var initialStyles = {'z-index': 2, 'position': 'absolute'};
				if (key == 'image'){
					current[key].styles({position: 'relative'});

					['top', 'right', 'bottom', 'left'].each(function(dir){
						next[key].style[dir] = '';
					}, this);

					if (transition == 'crossfade'){
						Object.merge(initialStyles, {top: 0, left: 0});
					}
				}

				next[key].styles(Object.merge({}, transition.from, initialStyles));

				if (Browser.ie && Browser.version < 9){
					next[key].set('morph', {
						link: 'cancel',
						duration: this.options.duration.toInt(),
						transition: 'quad:in:out',
						onComplete: function(){
							if (key == 'image'){
								next[key].styles(Object.merge({}, transition.to, {position: 'relative'}));
								current[key].styles(Object.merge({}, transition.from, {position: 'absolute'}));
							}

							current[key].styles(transition.from);
							next[key].get('morph').removeEvents('onComplete');

							if (settings.autoplay && settings.autoplay.toInt()) this.startTimer(container);
						}.bind(this)
					}).morph(transition.to);

					if (key == 'content'){
						current[key].set('morph', {
							link: 'cancel',
							duration: this.options.duration.toInt(),
							transition: 'quad:in:out'
						}).morph(transition.from);
					}
				} else {
					next[key].moofx(transition.to, {
						duration: this.options.duration,
						equation: this.options.curve,
						callback: function(){
							if (key == 'image'){
								next[key].styles(Object.merge({}, transition.to, {position: 'relative'}));
								current[key].styles(Object.merge({}, transition.from, {position: 'absolute'}));
							}
							current[key].styles(transition.from);

							if (settings.autoplay && settings.autoplay.toInt()) this.startTimer(container);
						}.bind(this)
					});

					if (key == 'content'){
						current[key].moofx(transition.from, {
							duration: this.options.duration,
							equation: this.options.curve
						});
					}
				}

			}, this);

		}

	});

	this.RokSprocket.Slideshow = Slideshow;

})());
