/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2012 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){this.ResizableTextbox=new Class({Implements:Options,options:{min:1,max:180,step:8},initialize:function(b,a){this.setOptions(a);this.element=document.id(b);
this.width=this.element.offsetWidth;this.element.addEvents({keydown:function(){var c=this.element,d=this.options.step*c.get("value").length;if(d<25){d=25;
}if(d>=this.options.max){d=this.options.max;}c.setStyle("width",d);}.bind(this),keyup:function(){var c=this.element,d=this.options.step*c.get("value").length;
if(d<=this.options.min){d=this.width;}if(d>=this.options.max){d=this.options.max;}if(!(c.get("value").length==c.retrieve("rt-value")||d<=this.options.min||d>=this.options.max)){c.setStyle("width",d);
}}.bind(this)});},toElement:function(){return this.element;}});})());