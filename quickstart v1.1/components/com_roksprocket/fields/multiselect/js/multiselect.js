/*
 * @version   $Id: multiselect.js 10889 2013-05-30 07:48:35Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){if(typeof this.RokSprocket=="undefined"){this.RokSprocket={};
}this.MultiSelect=new Class({Implements:[Options,Events],initialize:function(a){this.setOptions(a);this.elements=this.reload();this.attach();},reattach:function(){this.elements=this.reload();
this.attach();},attach:function(){this.elements.each(function(a){if(!a.retrieve("tags:field:attached",false)){a.store("multiselect:field:attached",true);
var b={tags:{click:a.retrieve("multiselect:field:click",function(d,c){if(d.target.get("data-multiselect-holder")===null){return true;}a.getElement("[data-multiselect-maininput]").focus();
}.bind(this)),unselect:a.retrieve("multiselect:field:remove",function(d,c){this.unselect.call(this,a,c);}.bind(this)),select:a.retrieve("multiselect:feeds:select",function(d,c){this.select.call(this,a,c);
}.bind(this)),mouseenter:a.retrieve("multiselect:feeds:mouseenter",function(d,c){this.mouseenter.call(this,a,c);}.bind(this)),keydown:a.retrieve("multiselect:feeds:keydown",function(d,c){this.keydown.call(this,d,a,c);
}.bind(this)),keyup:a.retrieve("multiselect:feeds:keyup",function(d,c){this.keyup.call(this,d,a,c);}.bind(this))},feeds:{mouseenter:a.retrieve("multiselect:feeds:mouseenter",function(d,c){this.refresh.call(this,a);
}.bind(this)),focus:a.retrieve("multiselect:feeds:focus",function(d,c){this.focus.call(this,a,c);}.bind(this)),blur:a.retrieve("multiselect:feeds:blur",function(d,c){this.blur.delay(100,this,a,c);
}.bind(this))}};a.addEvents({"click:relay([data-multiselect-holder])":b.tags.click,"click:relay([data-multiselect-value])":b.tags.select,"mouseenter:relay([data-multiselect-value])":b.tags.mouseenter,"click:relay([data-multiselect-remove])":b.tags.unselect,mouseenter:b.feeds.mouseenter,"keydown:relay([data-multiselect-maininput])":b.tags.keydown,"keyup:relay([data-multiselect-maininput])":b.tags.keyup,"focus:relay([data-multiselect-maininput])":b.feeds.focus,"blur:relay([data-multiselect-maininput])":b.feeds.blur});
this.maininput=new ResizableTextbox(a.getElement("[data-multiselect-maininput]"),{min:1,max:500,step:10});}},this);},focus:function(b,c){var a=b.getElement("[data-multiselect-feeds]");
this.refresh(b,c?c.get("value"):null);b.addClass("multiselect-showing-feeds");a.setStyle("display","block");},blur:function(b,c){var a=b.getElement("[data-multiselect-feeds]");
b.removeClass("multiselect-showing-feeds");a.setStyle("display","none");},keydown:function(e,c,d){var a=c.getElement("[data-multiselect-feed]"),b=a.getElement("[data-multiselect-value].hover"),f;
switch(e.key){case"down":f=b.getNext();if(f){this.mouseenter(c,f);}break;case"up":f=b.getPrevious();if(f){this.mouseenter(c,b.getPrevious());}break;case"enter":f=a.getElement("[data-multiselect-value].hover");
if(f){this.select(c,a.getElement("[data-multiselect-value].hover"));}break;default:}},keyup:function(c,a,b){if(c.key!="up"&&c.key!="down"&&c.key!="enter"){this.refresh(a,b.get("value"));
}else{if(c.key=="enter"){this.focus(a);}}},mouseenter:function(a,b){if(!b){return;}b.getSiblings().removeClass("hover").removeClass("last-item");b.addClass("hover");
},select:function(b,c){var a=b.getElement("[data-multiselect-select]"),e=c.get("data-multiselect-value"),f=c.get("text").clean(),d=new Element("li.multiselect-box[data-multiselect-box="+e+"]",{html:'<span class="multiselect-title">'+f+'</span><span class="multiselect-remove" data-multiselect-remove>&times;</span>',style:{opacity:0,visibility:"hidden"}});
a.getElement("option[value="+e+"]").set("selected","selected");b.getElement("[data-multiselect-maininput]").set("value","");d.inject(b.getElement("[data-multiselect-holder] .main-input"),"before").set("tween",{duration:200}).fade("in");
this.focus(b);},unselect:function(b,c){var a=b.getElement("[data-multiselect-select]"),d=c.getParent("[data-multiselect-box]"),e=d.get("data-multiselect-box");
a.getElement("option[value="+e+"]").set("selected",null);d.set("tween",{duration:200,onComplete:function(){d.dispose();}}).fade("out");},refresh:function(b,c){var d=b.getElements("[data-multiselect-select] option").filter(function(h){return !h.get("selected");
}),a=b.getElement("[data-multiselect-feed]"),e=[],g,f;d.each(function(j,h){g=this.highlight(j.get("text"),c);e.push(new Element("li[data-multiselect-value="+j.get("value")+"]").set("html",g));
},this);e=new Elements(e);a.empty().adopt(e.setStyle("display","block")).setStyle("width",b.getElement("[data-multiselect-holder]").offsetWidth-2);f=e.filter(function(h){return !h.get("text").test(c||"","i");
});if(f.length){f.setStyle("display","none");}f=e.filter(function(h){return h.getStyle("display")!="none";});if(f.length){f[0].addClass("hover");f[f.length-1].addClass("last-item");
}},highlight:function(b,a){return b.replace(new RegExp(a,"gi"),function(c){return"<em>"+c+"</em>";});},reload:function(a){if(!a){return document.getElements("[data-multiselect]");
}this.elements=document.getElements("[data-multiselect]");return this.elements;}});window.addEvent("domready",function(){this.RokSprocket.multiselect=new MultiSelect();
});})());