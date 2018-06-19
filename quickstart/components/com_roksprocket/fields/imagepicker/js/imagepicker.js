/*
 * @version   $Id: imagepicker.js 10889 2013-05-30 07:48:35Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
if(typeof this.RokSprocket=="undefined"){this.RokSprocket={};
}((function(){if(typeof this.RokSprocket=="undefined"){this.RokSprocket={};}var a=(Browser.name=="ie"&&Browser.version<=9)?"keypress":"input";this.ImagePicker=new Class({Implements:[Options,Events],options:{},initialize:function(b){this.setOptions(b);
this.attach();},getPickers:function(){this.pickers=document.getElements("[data-imagepicker]");return this.pickers;},attach:function(b){var c=(b?new Elements([b]).flatten():this.getPickers());
this.fireEvent("beforeAttach",c);c.each(function(h){var e=h.getElement("select"),i=h.getElement("[data-imagepicker-display]"),d=h.getElement("a.modal"),g=h.getElement("#"+h.get("data-imagepicker-id"));
var k=e.retrieve("roksprocket:pickers:change",function(l){this.change.call(this,l,e,d);}.bind(this)),f=i.retrieve("roksprocket:pickers:input",function(l){this.keypress.call(this,l,i,g,e,d);
}.bind(this)),j=i.retrieve("roksprocket:pickers:blur",function(l){this.blur.call(this,l,i,g,e,d);}.bind(this));if(!g.get("value").test(/^-([a-z]{1,})-$/)){i.store("display_value",i.get("value")||"");
i.store("display_datatitle",i.get("data-original-title")||"");g.store("json_value",g.get("value")||"");}e.addEvent("change",k);i.addEvent(a,f);i.twipsy({placement:"above",offset:5,html:true});
if(typeof SqueezeBox!="undefined"){h.getElement("a.modal").removeEvents("click");SqueezeBox.assign(h.getElement("a.modal"),{parse:"rel"});}},this);this.fireEvent("afterAttach",c);
},detach:function(b){var c=(b?new Elements([b]).flatten():this.pickers);this.fireEvent("beforeDetach",c);c.each(function(f){var h=f.retrieve("roksprocket:pickers:change"),e=f.retrieve("roksprocket:pickers:input"),d=f.getElement("select"),g=f.getElement("[data-imagepicker-display]");
d.removeEvent("change",h);g.removeEvent(a,e);},this);if(!b){document.store("roksprocket:pickers:document",false).removeEvent("click",this.bounds.document);
}this.fireEvent("afterDetach",c);},change:function(b,i,c){var j=i.get("value"),k=i.getParent(".imagepicker-wrapper"),d=k.getElement("input[type=hidden]"),f=k.getElement("[data-imagepicker-display]"),l=k.getElement(".sprocket-dropdown [data-toggle]"),g=l.getElement("i"),h=l.getElement("span.name"),e=k.getElement(".modal");
if(j.test(/^-([a-z]{1,})-$/)){k.addClass("peritempicker-noncustom");h.set("text",i.getElement("[value="+j+"]").get("text"));f.set("value",i.get("value"));
d.set("value",j);}else{k.removeClass("peritempicker-noncustom");h.set("text","");c.set("href",i.get("value"));if(f.get("value").test(/^-([a-z]{1,})-$/)){f.set("value",f.retrieve("display_value","")).set("data-original-title",f.retrieve("display_datatitle",""));
d.set("value",d.retrieve("json_value",""));}this.keypress(false,f,d,i);}},keypress:function(b,f,h,i,c){var g=h.get("value").test(/^-([a-z]{1,})-$/),e=JSON.decode(!g?h.get("value"):"")||{type:"mediamanager"},k=f.retrieve("twipsy"),j=f.get("value"),d={type:e.type,path:j,preview:""};
if(!j.length){d="";}this.update(h,d);if(k&&b!==false){k.setContent()[d?"show":"hide"]();}},blur:function(f,g,e,d,c){var b=g.retrieve("twipsy");if(b){b.hide();
}},update:function(d,g){d=document.id(d);var f=d.getParent("[data-imagepicker]"),i=f.getElement("[data-imagepicker-display]"),b=f.getElement("a.modal"),c=g.path;
g.link=b.get("href");if(c&&(!c.test(/^https?:\/\//)&&c.substr(0,1)!="/")){c=RokSprocket.SiteURL+"/"+c;}var h=(g.preview&&g.preview.length)?g.preview:c;
tip="<div class='imagepicker-tip-preview'><img src='"+h+"' /></div>";tip+=(g.width)?"<div class='imagepicker-tip-size'>"+g.width+" &times "+g.height+"</div>":"";
tip+="<div class='imagepicker-tip-path'>"+g.path+"</div>";i.set("value",g.path).store("display_value",g.path).set("data-original-title",(g.path?tip:"")).store("display_datatitle",(g.path?tip:"")).twipsy({placement:"above",offset:5,html:true});
var e=JSON.encode(g).replace(/\"/g,"'");d.set("value",e).store("json_value",e);}});window.addEvent("domready",function(){this.RokSprocket.imagepicker=new ImagePicker();
});if(typeof this.jInsertEditorText=="undefined"){this.jInsertEditorText=function(e,c){var b=e.match(/(src)=(\"[^\"]*\")/i),f=b[2].replace(/\"/g,""),d={type:"mediamanager",path:f,preview:""};
RokSprocket.imagepicker.update(c,d);};}if(typeof this.GalleryPickerInsertText=="undefined"){this.GalleryPickerInsertText=function(b,e,c,f){e=e.substr(RokSprocket.SiteURL.length+1);
var d={type:"rokgallery",path:e,width:c.width,height:c.height,preview:f};RokSprocket.imagepicker.update(b,d);};}})());