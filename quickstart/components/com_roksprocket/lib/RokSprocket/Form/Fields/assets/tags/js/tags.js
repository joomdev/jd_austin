/*!
 * @version   $Id: tags.js 19428 2014-03-04 01:45:36Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

 ((function(){

    if (typeof this.RokSprocket == 'undefined') this.RokSprocket = {};

    this.Tags = new Class({

        Implements: [Options, Events],

        initialize: function(options){
            this.setOptions(options);

            this.elements = this.reload();
            this.attach();
        },

        reattach: function(){
            this.elements = this.reload();
            this.attach();
        },

        attach: function(){
            this.elements.each(function(container){
                if (!container.retrieve('tags:field:attached', false)){
                    container.store('tags:field:attached', true);

                    var relay = {
                        tags: {
                            click: container.retrieve('tags:field:click', function(event, element){
                                if (event.target.get('data-tags-holder') === null) return true;

                                container.getElement('[data-tags-maininput]').focus();
                            }.bind(this)),

                            select: container.retrieve('tags:feeds:select', function(event, element){
                                this.select.call(this, container, element);
                            }.bind(this)),

                            unselect: container.retrieve('tags:field:remove', function(event, element){
                                this.unselect.call(this, container, element);
                            }.bind(this)),

                            blur: container.retrieve('tags:feeds:blur', function(event, element){
                                this.blur.call(this, container, element);
                            }.bind(this)),

                            keydown: container.retrieve('tags:feeds:keydown', function(event, element){
                                this.keydown.call(this, event, container, element);
                            }.bind(this))
                        }
                    };

                    container.addEvents({
                        'click:relay([data-tags-holder])': relay.tags.click,
                        'click:relay([data-tags-value])': relay.tags.select,
                        'click:relay([data-tags-remove])': relay.tags.unselect,
                        'blur:relay([data-tags-maininput])': relay.tags.blur,
                        'keydown:relay([data-tags-maininput])': relay.tags.keydown
                    });

                    this.maininput = new ResizableTextbox(container.getElement('[data-tags-maininput]'), {min: 1, max: 180, step: 9});
                }
            }, this);
        },

        keydown: function(event, container, element){
            if (event.key == 'enter'){
                event.preventDefault();
                this.blur(container, element);
            }
        },

        blur: function(container, element){
            var input = container.getElement('[data-tags-maininput]'),
                values = input.get('value') ? input.get('value').replace(/,\s/g, ',').split(',') : false;

            if (values !== false){
                values.each(function(value){
                    this.select(container, value.replace(/('|"|\s)/g, ''));
                    input.fireEvent('keyup');
                }, this);
            }
        },

        select: function(container, value){
            var maininput = container.getElement('[data-tags-maininput]'),
                realinput = container.getElement('[data-tags-input]'),
                current = realinput.get('value'),
                currentList = current.split(',');

            if (!currentList.contains(value)){
                var box = new Element('li.tags-box[data-tags-box='+value+']', {
                        'html': '<span class="tags-title">'+value+'</span><span class="tags-remove" data-tags-remove>&times;</span>',
                        'style': {opacity: 0, 'visibility': 'hidden'}
                    });

                realinput.set('value', current ? current + ',' + value : value);
                box.inject(container.getElement('[data-tags-holder] .main-input'), 'before').set('tween', {duration: 200}).fade('in');
            }

            container.getElement('[data-tags-maininput]').set('value', '');
            maininput.focus();
        },

        unselect: function(container, element){
            var maininput = container.getElement('[data-tags-maininput]'),
                realinput = container.getElement('[data-tags-input]'),
                box = element.getParent('[data-tags-box]'),
                value = box.get('data-tags-box'),
                list = realinput.get('value').clean().replace(/,\s/g, ',').split(',');

            list.erase(value);
            realinput.set('value', list.join(','));
            box.set('tween', {duration: 200, onComplete: function(){ box.dispose(); }}).fade('out');
        },

        reset: function(container, values){
            var maininput = container.getElement('[data-tags-maininput]'),
                realinput = container.getElement('[data-tags-input]'),
                current = realinput.get('value'),
                currentList = current.split(',');

            var boxes = container.getElements('[data-tags-box]');
            realinput.set('value', '');
            if (boxes.length) boxes.dispose();
        },

        reload: function(assign){
            if (!assign) return document.getElements('[data-tags]');

            this.elements = document.getElements('[data-tags]');
            return this.elements;
        }

    });

    window.addEvent('domready', function(){
        this.RokSprocket.tags = new Tags();
    });

})());
