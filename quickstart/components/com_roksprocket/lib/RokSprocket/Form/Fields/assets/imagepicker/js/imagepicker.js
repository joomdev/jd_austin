if (typeof this.RokSprocket == 'undefined') this.RokSprocket = {};


((function(){
	if (typeof this.RokSprocket == 'undefined') this.RokSprocket = {};
	var OnInputEvent = (Browser.name == 'ie' && Browser.version <= 9) ? 'keypress' : 'input';

	this.ImagePicker = new Class({

		Implements: [Options, Events],
		options: {},

		initialize: function(options){
			this.setOptions(options);

			this.attach();
		},

		getPickers: function(){
			this.pickers = document.getElements('[data-imagepicker]');

			return this.pickers;
		},

		attach: function(picker){
			var pickers = (picker ? new Elements([picker]).flatten() : this.getPickers());

			this.fireEvent('beforeAttach', pickers);

			pickers.each(function(picker){
				var select = picker.getElement('select'),
					display = picker.getElement('[data-imagepicker-display]'),
					selector = picker.getElement('a.modal') || picker.getElement('a.thickbox'),
					input = picker.getElement('#' + picker.get('data-imagepicker-id'));

				var change = select.retrieve('roksprocket:pickers:change', function(event){
						this.change.call(this, event, select, selector);
					}.bind(this)),
					keypress = display.retrieve('roksprocket:pickers:input', function(event){
						this.keypress.call(this, event, display, input, select, selector);
					}.bind(this)),
					blur = display.retrieve('roksprocket:pickers:blur', function(event){
						this.blur.call(this, event, display, input, select, selector);
					}.bind(this)),
					thickboxID = document.retrieve('roksprocket:pickers:thickboxID', function(event, element){
						this.thickboxID.call(this, event, element, display, input, select, selector);
					}.bind(this));

				if (!input.get('value').test(/^-([a-z]{1,})-$/)){
					display.store('display_value', display.get('value') || '');
					display.store('display_datatitle', display.get('data-original-title') || '');
					input.store('json_value', input.get('value') || '');
				}

				select.addEvent('change', change);
				display.addEvent(OnInputEvent, keypress);
				//display.addEvent('blur', blur);
				display.twipsy({placement: 'above', offset: 5, html: true});

				if (typeof SqueezeBox != 'undefined'){
					picker.getElement('a.modal').removeEvents('click');
					SqueezeBox.assign(picker.getElement('a.modal'), {parse: 'rel'});
				}

				if (typeof tb_init != 'undefined'){
					if (!document.retrieve('roksprocket:imagepicker:thickbox', false)){
						document.store('roksprocket:imagepicker:thickbox', true);
						document.addEvent('mouseenter:relay([data-imagepicker-id])', thickboxID);
					}
				}

			}, this);

			this.fireEvent('afterAttach', pickers);
		},

		detach: function(picker){
			var pickers = (picker ? new Elements([picker]).flatten() : this.pickers);

			this.fireEvent('beforeDetach', pickers);

			pickers.each(function(picker){
				var change = picker.retrieve('roksprocket:pickers:change'),
					keypress = picker.retrieve('roksprocket:pickers:input'),
					select = picker.getElement('select'),
					display = picker.getElement('[data-imagepicker-display]'),
					thickboxID = document.retrieve('roksprocket:pickers:thickboxID');

				select.removeEvent('change', change);
				display.removeEvent(OnInputEvent, keypress);

				if (typeof tb_init != 'undefined'){
					if (document.retrieve('roksprocket:imagepicker:thickbox', false)){
						document.removeEvent('mouseenter:relay([data-imagepicker-id])', thickboxID);
					}
				}
			}, this);

			if (!picker) document.store('roksprocket:pickers:document', false).removeEvent('click', this.bounds.document);

			this.fireEvent('afterDetach', pickers);
		},

		change: function(event, select, selector){
			var value = select.get('value'),
				parent = select.getParent('.imagepicker-wrapper'),
				hidden = parent.getElement('input[type=hidden]'),
				display = parent.getElement('[data-imagepicker-display]'),
				dropdown = parent.getElement('.sprocket-dropdown [data-toggle]'),
				icon = dropdown.getElement('i'),
				title = dropdown.getElement('span.name'),
				picker = parent.getElement('.modal') || parent.getElement('a.thickbox');

			if (value.test(/^-([a-z]{1,})-$/)){
				parent.addClass('peritempicker-noncustom');
				title.set('text', select.getElement('[value='+value+']').get('text'));

				display.set('value', select.get('value'));
				hidden.set('value', value);
			} else {
				parent.removeClass('peritempicker-noncustom');
				title.set('text', '');
				selector.set('href', select.get('value'));

				if (display.get('value').test(/^-([a-z]{1,})-$/)){
					display.set('value', display.retrieve('display_value', '')).set('data-original-title', display.retrieve('display_datatitle', ''));
					hidden.set('value', hidden.retrieve('json_value', ''));
				}

				this.keypress(false, display, hidden, select);
			}

		},

		keypress: function(event, display, input, select, selector){
			var testValue = input.get('value').test(/^-([a-z]{1,})-$/),
				obj = JSON.decode(!testValue ? input.get('value') : '') || {type: 'mediamanager'},
				twipsy = display.retrieve('twipsy'),
				value = display.get('value'),
				data = {
					type: obj.type,
					path: value,
					preview: ''
				};

			if (!value.length) data = "";

			this.update(input, data);
			if (twipsy && event !== false){
				twipsy.setContent()[data ? 'show' : 'hide']();
			}
		},

		blur: function(event, display, input, select, selector){
			var twipsy = display.retrieve('twipsy');
			if (twipsy) twipsy.hide();
		},

		thickboxID: function(event, element, display, input, select, selector){
			window.imagePickerID = element.get('data-imagepicker-id');
		},

		update: function(input, settings){
			input = document.id(input);

			// RokSprocket.SiteURL is always available

			var parent = input.getParent('[data-imagepicker]'),
				display = parent.getElement('[data-imagepicker-display]'),
				selector = parent.getElement('a.modal') || parent.getElement('a.thickbox'),
				previewIMG = settings.path;

			settings.link = selector.get('href');

			if (previewIMG && (!previewIMG.test(/^https?:\/\//) && previewIMG.substr(0, 1) != '/')){
				previewIMG = RokSprocket.SiteURL + '/' + previewIMG;
			}


			var preview = (settings.preview && settings.preview.length) ? settings.preview : previewIMG;
				tip = "<div class='imagepicker-tip-preview'><img src='"+preview+"' /></div>";
				tip += (settings.width) ? "<div class='imagepicker-tip-size'>"+settings.width+" &times "+settings.height+"</div>": "";
				tip += "<div class='imagepicker-tip-path'>"+settings.path+"</div>";

			display
				.set('value', settings.path).store('display_value', settings.path)
				.set('data-original-title', (settings.path ? tip : '')).store('display_datatitle', (settings.path ? tip : ''))
				.twipsy({placement: 'above', offset: 5, html: true});

			var json = JSON.encode(settings).replace(/\"/g, "'");
			input.set('value', json).store('json_value', json);
		}

	});

	window.addEvent('domready', function(){
		this.RokSprocket.imagepicker = new ImagePicker();
	});

	if (typeof this.jInsertEditorText == 'undefined'){
		this.jInsertEditorText = function(value, input){
			var tag = value.match(/(src)=(\"[^\"]*\")/i),
				path = tag && tag.length ? tag[2].replace(/\"/g, '') : value,
				data = {
					type: 'mediamanager',
					path: path,
					preview: ''
				};

			RokSprocket.imagepicker.update(input, data);
		};
	}

	if (typeof this.GalleryPickerInsertText == 'undefined'){
		this.GalleryPickerInsertText = function(input, value, size, minithumb){
			value = value.substr(RokSprocket.SiteURL.length + 1);

			var data = {
				type: 'rokgallery',
				path: value,
				width: size.width,
				height: size.height,
				preview: minithumb
			};

			RokSprocket.imagepicker.update(input, data);
		};
	}
})());
