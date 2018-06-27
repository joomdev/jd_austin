/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2012 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){if(typeof this.RokSprocket=="undefined"){this.RokSprocket={};}this.Tags=new Class({Implements:[Options,Events],initialize:function(a){this.setOptions(a);
this.elements=this.reload();this.attach();},reattach:function(){this.elements=this.reload();this.attach();},attach:function(){this.elements.each(function(a){if(!a.retrieve("tags:field:attached",false)){a.store("tags:field:attached",true);
var b={tags:{click:a.retrieve("tags:field:click",function(d,c){if(d.target.get("data-tags-holder")===null){return true;}a.getElement("[data-tags-maininput]").focus();
}.bind(this)),select:a.retrieve("tags:feeds:select",function(d,c){this.select.call(this,a,c);}.bind(this)),unselect:a.retrieve("tags:field:remove",function(d,c){this.unselect.call(this,a,c);
}.bind(this)),blur:a.retrieve("tags:feeds:blur",function(d,c){this.blur.call(this,a,c);}.bind(this)),keydown:a.retrieve("tags:feeds:keydown",function(d,c){this.keydown.call(this,d,a,c);
}.bind(this))}};a.addEvents({"click:relay([data-tags-holder])":b.tags.click,"click:relay([data-tags-value])":b.tags.select,"click:relay([data-tags-remove])":b.tags.unselect,"blur:relay([data-tags-maininput])":b.tags.blur,"keydown:relay([data-tags-maininput])":b.tags.keydown});
this.maininput=new ResizableTextbox(a.getElement("[data-tags-maininput]"),{min:1,max:180,step:9});}},this);},keydown:function(c,a,b){if(c.key=="enter"){c.preventDefault();
this.blur(a,b);}},blur:function(a,d){var c=a.getElement("[data-tags-maininput]"),b=c.get("value")?c.get("value").replace(/,\s/g,",").split(","):false;if(b!==false){b.each(function(e){this.select(a,e.replace(/('|"|\s)/g,""));
c.fireEvent("keyup");},this);}},select:function(a,e){var b=a.getElement("[data-tags-maininput]"),g=a.getElement("[data-tags-input]"),f=g.get("value"),c=f.split(",");
if(!c.contains(e)){var d=new Element("li.tags-box[data-tags-box="+e+"]",{html:'<span class="tags-title">'+e+'</span><span class="tags-remove" data-tags-remove>&times;</span>',style:{opacity:0,visibility:"hidden"}});
g.set("value",f?f+","+e:e);d.inject(a.getElement("[data-tags-holder] .main-input"),"before").set("tween",{duration:200}).fade("in");}a.getElement("[data-tags-maininput]").set("value","");
b.focus();},unselect:function(a,c){var b=a.getElement("[data-tags-maininput]"),g=a.getElement("[data-tags-input]"),d=c.getParent("[data-tags-box]"),f=d.get("data-tags-box"),e=g.get("value").clean().replace(/,\s/g,",").split(",");
e.erase(f);g.set("value",e.join(","));d.set("tween",{duration:200,onComplete:function(){d.dispose();}}).fade("out");},reload:function(a){if(!a){return document.getElements("[data-tags]");
}this.elements=document.getElements("[data-tags]");return this.elements;}});window.addEvent("domready",function(){this.RokSprocket.tags=new Tags();});})());
