/*!
 * @version   $Id: grids.js 10889 2013-05-30 07:48:35Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){
	if (typeof this.RokSprocket == 'undefined') this.RokSprocket = {};
	else Object.merge(this.RokSprocket, {Grids: null, GridsBuilder: null});

	var supportsOrientationChange = "onorientationchange" in window;

	var Grids = new Class({

		Implements: [Options, Events],

		options: {
			settings: {}
		},

		initialize: function(options){
			this.setOptions(options);

			this.gridss = document.getElements('[data-grids]');
			this.grids = {};
			this.settings = {};
			this.curve = Browser.opera ? {equation: 'ease-in-out'} : {curve: 'cubic-bezier(0.37,0.61,0.59,0.87)'};

			try {
				RokMediaQueries.on('every', this.mediaQuery.bind(this));
			}
			catch(error) { if (typeof console != 'undefined') console.error('Error while trying to add a RokMediaQuery "match" event', error); }
		},

		attach: function(grids, settings){
			grids = typeOf(grids) == 'number' ?
					document.getElements('[data-grids=' + this.getID(grids) + ']')
					:
					grids;
			settings = typeOf(settings) == 'string' ? JSON.decode(settings) : settings;

			var containers = (grids ? new Elements([grids]).flatten() : this.gridss);

			containers.each(function(container){
				container.store('roksprocket:grids:attached', true);

				this.setSettings(container, settings, 'restore');

				var relay = {
					loadmore: container.retrieve('roksprocket:grids:loadmore', function(event, page){
						if (event) event.preventDefault();
						this.loadMore.call(this, event, container, page);
					}.bind(this)),

					ordering: container.retrieve('roksprocket:grids:ordering', function(event, element){
						this.orderBy.call(this, event, container, element);
					}.bind(this)),

					filtering: container.retrieve('roksprocket:grids:filtering', function(event, element){
						this.filterBy.call(this, event, container, element);
					}.bind(this)),

					document: document.retrieve('roksprocket:grids:document', function(event, element){
						this.toggleShift.call(this, event, container, element);
					}.bind(this))
				};

				container.addEvent('click:relay([data-grids-loadmore])', relay['loadmore']);
				container.addEvent('click:relay([data-grids-orderby])', relay['ordering']);
				container.addEvent('click:relay([data-grids-filterby])', relay['filtering']);

				container.retrieve('roksprocket:grids:ajax', new RokSprocket.Request({
					model: 'grids',
					model_action: 'getPage',
					onRequest: this.onRequest.bind(this, container),
					onSuccess: function(response){
						this.onSuccess(response, container, container.retrieve('roksprocket:grids:ajax'));
					}.bind(this)
				}));

				document.addEvents({
					'keydown:keys(shift)': relay['document'],
					'keyup:keys(shift)': relay['document']
				});

				this.initializeGrids(container, function(){
					this.mediaQuery.delay(5, this, RokMediaQueries.getQuery());
				}.bind(this));
			}, this);
		},

		detach: function(grids){
			grids = typeOf(grids) == 'number' ?
					document.getElements('[data-grids=' + this.getID(grids) + ']')
					:
					grids;

			var containers = (grids ? new Elements([grids]).flatten() : this.gridss);

			containers.each(function(container){
				container.store('roksprocket:grids:attached', false);
				var relay = {
					loadmore: container.retrieve('roksprocket:grids:loadmore'),
					ordering: container.retrieve('roksprocket:grids:ordering'),
					filtering: container.retrieve('roksprocket:grids:filtering'),
					document: document.retrieve('roksprocket:grids:document')
				};

				container.removeEvent('click:relay([data-grids-loadmore])', relay['loadmore']);
				container.removeEvent('click:relay([data-grids-orderby])', relay['ordering']);
				container.removeEvent('click:relay([data-grids-filterby])', relay['filtering']);
				document.removeEvents({
					'keydown:keys(shift)': relay['document'],
					'keyup:keys(shift)': relay['document']
				});

			}, this);
		},

		mediaQuery: function(query){
			var grids;

			for (var id in this.grids){
				grids = this.grids[id];
				grids.resize('fast');
			}
		},

		setSettings: function(container, settings, restore){
			var id = this.getID(container),
				options = Object.clone(this.getSettings(container) || this.options.settings);

			if (!restore || !this.settings['id-' + id]){
				this.settings['id-' + id] = Object.merge(options, settings || options);
			}
		},

		getSettings: function(container){
			var id = this.getID(container);

			return this.settings['id-' + id];
		},

		getContainer: function(container){
			if (!container) container = document.getElements('[data-grids]');
			if (typeOf(container) == 'number') container = document.getElement('[data-grids='+container+']');
			if (typeOf(container) == 'string') container = document.getElement(container);

			return container;
		},

		getID: function(id){
			if (typeOf(id) == 'number') id = document.getElement('[data-grids='+id+']');
			if (typeOf(id) == 'string') id = document.getElement(id);
			return !id ? id : id.get('data-grids');
		},

		loadMore: function(event, container, page){
			container = this.getContainer(container);
			page = (typeOf(page) == 'number') ? page : this.getSettings(container).page || 1;
			if (!container.retrieve('roksprocket:grids:attached')) return;

			var ajax = container.retrieve('roksprocket:grids:ajax'),
				filterby = container.getElement('[data-grids-filterby].active'),
				params = {
					moduleid: container.get('data-grids'),
					behavior: !page ? 'reset' : 'append',
					displayed: !page ? [] : this.getSettings(container).displayed || [],
					filter: filterby ? filterby.get('data-grids-filterby') || 'all' : 'all',
					page: ++page
				};

			if (event && event.shift) params.all = true;

			if (!ajax.isRunning()){
				ajax.cancel().setParams(params).send();
			}
		},

		filterBy: function(event, container, element){
			container.getElements('[data-grids-filterby]').removeClass('active');
			element.addClass('active');

			container.addClass('refreshing');
			this.loadMore(event, container, 0);

		},

		nextAll: function(containers, element){
			containers = this.getContainer(containers);
			if (typeOf(containers) == 'element') return this.next(containers, element);

			containers.each(function(container){
				this.next(container, element);
			}, this);
		},

		toggleShift: function(event, container, element){
			var type = event.type || 'keyup',
				loadmore = document.getElements('[data-grids-loadmore]');

			if (!loadmore.length) return true;

			if (type == 'keydown') loadmore.addClass('load-all');
			else loadmore.removeClass('load-all');
		},

		onRequest: function(container){
			var loadmore = container.getElements('[data-grids-loadmore]');
			if (loadmore) loadmore.addClass('loader');

			this.detach(container);
		},

		onSuccess: function(response, container){
			var id = 'id-' + this.getID(container),
				ajax = container.retrieve('roksprocket:grids:ajax'),
				items = container.getElement('[data-grids-items]'),
				html = response.getPath('payload.html'),
				page = response.getPath('payload.page'),
				more = response.getPath('payload.more'),
				behavior = response.getPath('payload.behavior'),
				displayed = response.getPath('payload.displayed'),
				settings = this.getSettings(container),
				animations = settings.animations,
				imgList;

			this.setSettings(container, {page: (behavior == 'reset' ? 1 : page), displayed: displayed});
			container.removeClass('refreshing');

			var dummy = new Element('div', {html: html}),
				elements = dummy.getChildren(),
				styles = {},
				options = {};

			styles = this.getAnimation(container, '_set').style;

			moofx(elements).style(styles);
			items.adopt(elements);
			imgList = new Elements(elements.getElements('img').flatten());

			this._loadImages(imgList.get('src'), function(){
				if (behavior == 'reset'){
					this.grids[id].bricks.each(function(element, i){
						(function(){
							styles = this.getAnimation(container, '_out');
							moofx(element).style(styles.style);
							options = Object.merge({}, this.curve, {duration: '250ms', callback: function(){ element.dispose(); }});
							moofx(element).animate(styles.animate, options);
						}).delay(i * 50, this);
					}, this);
				}
				this.grids[id][behavior](elements, function(){
					loadmore = container.getElements('[data-grids-loadmore]');
					if (loadmore) loadmore.removeClass('loader');

					elements = this.grids[id].bricks.filter(function(element){ return elements.contains(element); });
					elements.each(function(element, i){
						(function(){
							styles = this.getAnimation(container, '_in');
							options = Object.merge({}, this.curve, {curve: 'cubic-bezier(0.37,0.61,0.59,0.87)', duration: '300ms'});
							moofx(element).animate(styles.animate, options);
						}).delay(i * 100, this);
					}, this);

					this.attach(container);
					container.getElements('[data-grids-loadmore]').removeClass('load-all')[!more ? 'addClass' : 'removeClass']('hide');

				}.bind(this));
			}.bind(this));

		},

		getAnimation: function(container, what){
			var settings = this.getSettings(container),
				type = settings.animations || null,
				styles = {},
				animation = {
					_set: {style: {opacity: 0}, animate: {}},
					_out: {style: {opacity: 1}, animate: {opacity: 0}},
					_in: {style: {}, animate: {opacity: 1}}
				};

			type = type ? type.erase('fade') : null;
			if (type && type.contains('flip')) type = type.erase('scale').erase('rotate');

			// types: _set / _out / _in
			switch(type ? type.join(',') : null){
				case 'scale':
					animation['_set']['style'] = Object.merge(animation['_set']['style'], {
						transform: 'scale(0.5)'
					});
					animation['_out']['style'] = Object.merge(animation['_out']['style'], {
						'transform-origin': '50% 50%'
					});
					animation['_out']['animate'] = Object.merge(animation['_out']['animate'], {
						transform: Browser.ie9 ? 'scale(0.001)' : 'scale(0)'
					});
					animation['_in']['animate'] = Object.merge(animation['_in']['animate'], {
						transform: Browser.ie9 || Browser.opera ? 'matrix(1, 0, 0, 1, 0, 0)' : 'scale(1)'
					});
					break;

				case 'rotate':
					animation['_set']['style'] = Object.merge(animation['_set']['style'], {
						'transform-origin': '0 0',
						transform: 'rotate(-10deg)'
					});
					animation['_out']['style'] = Object.merge(animation['_out']['style'], {
						'transform-origin': '0 0'
					});
					animation['_out']['animate'] = Object.merge(animation['_out']['animate'], {
						transform: 'rotate(10deg)'
					});
					animation['_in']['animate'] = Object.merge(animation['_in']['animate'], {
						transform: 'rotate(0)'
					});
					break;

				case 'rotate,scale': case 'scale,rotate':
					animation['_set']['style'] = Object.merge(animation['_set']['style'], {
						'transform-origin': '0 0',
						transform: 'scale(0.5) rotate(-10deg)'
					});
					animation['_out']['style'] = Object.merge(animation['_out']['style'], {
						'transform-origin': '50% 50%'
					});
					animation['_out']['animate'] = Object.merge(animation['_out']['animate'], {
						transform: Browser.ie9 ? 'scale(0.001) rotate(10deg)' : 'scale(0) rotate(10deg)'
					});
					animation['_in']['animate'] = Object.merge(animation['_in']['animate'], {
						transform: Browser.ie9 || Browser.opera ? 'matrix(1, 0, 0, 1, 0, 0)' : 'scale(1) rotate(0)'
					});
					break;

				case 'flip':
					animation['_set']['style'] = Object.merge(animation['_set']['style'], {
						'transform-origin': '50% 50%',
						transform: 'scale(0.5) rotateY(360deg)'
					});
					animation['_out']['style'] = Object.merge(animation['_out']['style'], {
						'transform-origin': '50% 50%'
					});
					animation['_out']['animate'] = Object.merge(animation['_out']['animate'], {
						transform: Browser.ie9 ? 'scale(0.0001) rotateY(360deg)' : 'scale(0.5) rotateY(360deg)'
					});
					animation['_in']['animate'] = Object.merge(animation['_in']['animate'], {
						transform: 'scale(1) rotateY(0)'
					});
					break;
				default:
			}

			return animation[what];

		},

		orderBy: function(event, container, element){
			var id = 'id-' + this.getID(container);

			if (!this.grids || !this.grids[id]) throw new Error('RokSprocket Grids: Grids class not available');

			var orderBy = element.get('data-grids-orderby');
			this.grids[id].order(orderBy/*, null, function(callback){}*/);

			container.getElements('[data-grids-orderby]').removeClass('active');
			if (orderBy != 'random') element.addClass('active');
		},

		initializeGrids: function(container, callback){
			var id = 'id-' + this.getID(container), loadmore;

			if (this.grids && this.grids[id]){
				if (typeof callback == 'function') callback.call(this.grids[id].bricks);

				loadmore = container.getElements('[data-grids-loadmore]');
				if (loadmore) loadmore.removeClass('loader');

				return this.grids[id];
			}

			var imgList = container.getElements('img'),
				elements = container.getElement('[data-grids-items]'),
				activeOrder = container.getElement('.active[data-grids-orderby]'),
				options = {
					container: container,
					animated: true,
					gutter: 0,
					order: activeOrder ? activeOrder.get('data-grids-orderby') : (container.getElements('[data-grids-orderby]').length ? 'random' : 'default')
				},
				items = elements.getElements('[data-grids-item]');

			if (!items.length) return this.grids[id];

			if (callback && typeof callback == 'function') options.callback = callback;

			moofx(elements).style({'transform-style': 'preserve-3d', 'backface-visibility': 'hidden', opacity: 1});
			moofx(items).style(this.getAnimation(container, '_in').animate);
			if (!imgList.length){
				loadmore = container.getElements('[data-grids-loadmore]');
				if (loadmore) loadmore.removeClass('loader');

				this.grids[id] = new RokSprocket.GridsBuilder(elements, options);
			} else {
				this._loadImages(imgList.get('src'), function(){
					loadmore = container.getElements('[data-grids-loadmore]');
					if (loadmore) loadmore.removeClass('loader');

					this.grids[id] = new RokSprocket.GridsBuilder(elements, options);
				}.bind(this));
			}

			return this.grids[id];
		},

		_loadImages: function(images, callback){
			return images.length ? new Asset.images(images, {onComplete: callback.bind(this)}) : callback.bind(this)();
		}

	});


	var GridsBuilder = new Class({

		Implements: [Options, Events],

		options: {
			/*columnWidth: function(){},*/
			container: null,
			resizeable: false,
			animated: false,
			gutter: 0,
			fitwidth: false,
			order: 'default',
			containerStyle: {
				position: 'relative'
			}
		},

		initialize: function(element, options){
			this.setOptions(options);

			this.element = document.id(element) || document.getElement(element) || null;

			if (!this.element) throw new Error('Grids Builder Error: Element "'+element+'" not found in the DOM.');

			this.styleQueue = [];
			this.curve = Browser.opera ? {equation: 'ease-in-out'} : {curve: 'cubic-bezier(0.37,0.61,0.59,0.87)'};
			this.originalState = this.getBricks();

			this.build();
			this.init(options.callback || null);
		},

		build: function(){
			var styles = this.element.style;

			this.originalStyle = {
				height: styles.height || ''
			};

			Object.each(this.options.containerStyle, function(value, prop){
				this.originalStyle[prop] = styles[prop] || '';
			}, this);

			moofx(this.element).style(this.originalStyle);

			this.offset = {
				x: this.element.getStyle('padding-left').toInt(),
				y: this.element.getStyle('padding-top').toInt()
			};

			this.isFluid = this.options.columnWidth && typeof this.options.columnWidth === 'function';

			this.reloadItems(this.options.order || null);

		},

		init: function(callback){
			this.getColumns();
			this.reLayout(callback);
		},

		getBricks: function(items){
			return (items ? items : this.element.getElements('[data-grids-item]')).setStyle('position', 'absolute');
		},

		reloadItems: function(order, items){
			this.bricks = this.getBricks(items);
			if (order == 'random' || order == 'default'){
				if (order == 'random') this.bricks = this.bricks.shuffle();
				if (order == 'default') this.bricks = this.originalState.clone();

				return this.bricks;
			}

			this.bricks = order ? this.orderBy(order) : this.bricks;

			return this.bricks;
		},

		orderBy: function(order){
			var errorCheck = false;

			return this.bricks.sort(function(a, b){
				var aSort = a.getElement('[data-grids-order-' + order + ']'),
					bSort = b.getElement('[data-grids-order-' + order + ']');

				if (!aSort || !bSort){
					if (console && console.error && !errorCheck) console.error('RokSprocket GridsBuilder: Trying to sort by "'+order+'" but no sorting rule has been found.');
					errorCheck = true;
					return 0;
				}

				aSort = aSort.get('data-grids-order-' + order);
				bSort = bSort.get('data-grids-order-' + order);

				return aSort == bSort ? 0 : (aSort < bSort ? -1 : 1);
			}.bind(this));
		},

		reload: function(callback){
			this.reloadItems();
			this.init(callback);
		},

		layout: function(bricks, callback, fast){
			for (var i = 0, len = bricks.length; i < len; i++){
				this.placeBrick(bricks[i]);
			}

			var containerSize = {}, options = {};
			containerSize.height = Math.max.apply(Math, this.colYs);

			if (this.options.fitwidth){
				var unused = 0;
				i = this.cols;

				while (--i){
					if (this.colYs[i] !== 0) break;
					unused++;
				}

				containerSize.width = (this.cols - unused) * this.columnWidth - this.options.gutter;
			}

			this.styleQueue.push({element: this.element, style: containerSize});
			var styleFn = !this.isLaidOut ? 'style' : (this.options.animated && !fast ? 'animate' : 'style'), obj;

			this.styleQueue.each(function(style, i){
				options = Object.merge({}, this.curve, {duration: '400ms'});

				if (i == this.styleQueue.length - 1) if (callback) options.callback = callback.bind(callback, bricks);
				moofx(style.element)[styleFn](style.style, options);
			}, this);

			this.styleQueue.empty();

			if (callback && styleFn == 'style') callback.call(bricks);

			this.isLaidOut = true;
		},

		getColumns: function(){
			var container = this.options.fitwidth ? this.element.getParent() : this.element,
				containerWidth = container.offsetWidth;

			this.columnWidth =	this.isFluid ? this.options.columnWidth(containerWidth) : this.options.columnWidth ||
								(this.bricks.length && this.bricks[0].offsetWidth) ||
								containerWidth;

			this.columnWidth += this.options.gutter;
			this.cols = Math.round((containerWidth + this.options.gutter) / this.columnWidth);
			this.cols = Math.max(this.cols, 1);
		},

		placeBrick: function(brick){
			brick = document.id(brick);
			var colSpan, groupCount, groupY, groupColY;

			colSpan = Math.round(brick.offsetWidth / (this.columnWidth + this.options.gutter));
			colSpan = Math.min(colSpan, this.cols);

			if (colSpan == 1) groupY = this.colYs;
			else {
				groupCount = this.cols + 1 - colSpan;
				groupY = [];

				(groupCount).times(function(i){
					groupColY = this.colYs.slice(i, i + colSpan);
					groupY[i] = Math.max.apply(Math, groupColY);
				}, this);
			}

			var minimumY = Math.min.apply(Math, groupY), shortCol = 0;

			for (var i = 0, len = groupY.length; i < len; i++){
				if (groupY[i] === minimumY){
					shortCol = i;
					break;
				}
			}

			var position = {
				top: minimumY + this.offset.y
			};

			//position.left = this.columnWidth * shortCol + this.offset.x;
			position.left = shortCol * (100 / this.cols) + '%';
			this.styleQueue.push({element: brick, style: position});

			var setHeight = minimumY + brick.offsetHeight + ((this.options.gutter || 0)),
				setSpan = this.cols + 1 - groupY.length;

			(setSpan).times(function(i){
				this.colYs[shortCol + i] = setHeight;
			}, this);
		},

		resize: function(fast){
			var prevColCount = this.cols;
			this.getColumns();

			if ((this.isFluid || fast) && this.cols !== prevColCount || fast) this.reLayout(null, fast);
		},

		reLayout: function(callback, fast){
			var i = this.cols;
			this.colYs = [];
			while (i--) this.colYs.push(0);

			this.layout(this.bricks, callback, fast);
		},

		reset: function(content, callback){
			content = content.filter(function(element){
				return element.get('data-grids-item') !== null || element.getElement('data-grids-item');
			});

			this.bricks = this.originalState = new Elements();

			content.setStyles({'top': 0, left: 0, position: 'absolute'});
			this.appendedBricks.delay(1, this, [content, content, callback]);
		},

		append: function(content, callback){
			content = content.filter(function(element){
				return element.get('data-grids-item') !== null || element.getElement('data-grids-item');
			});

			if (!content) return;

			content.setStyles({'top': this.element.getSize().y, left: 0, position: 'absolute'});
			this.appendedBricks.delay(1, this, [content, null, callback]);
		},

		appendedBricks: function(content, items, callback){
			var order = this.options.container.getElement('[data-grids-orderby].active') || this.options.container.getElement('[data-grids-orderby=random]'),
				by = order ? order.get('data-grids-orderby') : (this.options.container.getElements('[data-grids-orderby]').length ? 'random' : 'default');

			this.originalState.append(content);
			this.order(by, items, callback);
			//this.reloadItems(by, callback);
			//this.reload(callback);
			//this.layout(content, callback);
		},

		order: function(type, items, callback){
			this.reloadItems(type, items || null);
			this.init(callback);
		}

	});

	this.RokSprocket.Grids = Grids;
	this.RokSprocket.GridsBuilder = GridsBuilder;

	if (supportsOrientationChange){
		window.addEventListener('orientationchange', function(){
			if (typeof RokSprocket == 'undefined' || typeof RokSprocket.instances == 'undefined' || typeof RokSprocket.instances.grids == 'undefined') return;
			var grids;

			for (var id in RokSprocket.instances.grids.grids){
				grids = RokSprocket.instances.grids.grids[id];
				grids.resize('fast');
			}
		});
	}

	Element.implement({
		grids : function(options) {
			var grids = this.retrieve('roksprocket:grids:builder');
			if (!grids) grids = this.store('roksprocket:grids:builder', new RokSprocket.GridsBuilder(this, options));

			return grids;
		}
	});

	/* Ugly workaround for data-sets issue for IE < 9 on Moo < 1.4.4 */
	if (MooTools.version < "1.4.4" && (Browser.name == 'ie' && Browser.version < 9)){
		((function(){
			var dataList = [
				'rel', 'data-next',
				'data-grids', 'data-grids-items', 'data-grids-item', 'data-grids-content', 'data-grids-page', 'data-grids-next',
				'data-grids-order', 'data-grids-orderby', 'data-grids-order-title', 'data-grids-order-date',
				'data-grids-filterby', 'data-grids-loadmore'
			];

			dataList.each(function(data){
				Element.Properties[data] = {get: function(){ return this.getAttribute(data); }};
			});
		})());
	}
})());
