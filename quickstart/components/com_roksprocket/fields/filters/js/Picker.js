/*
---
name: Picker
description: Creates a Picker, which can be used for anything
authors: Arian Stolwijk
requires: [Core/Element.Dimensions, Core/Fx.Tween, Core/Fx.Transitions]
provides: Picker
...
*/
var Picker=new Class({Implements:[Options,Events],options:{pickerClass:"datepicker",inject:null,animationDuration:400,useFadeInOut:true,positionOffset:{x:0,y:0},pickerPosition:"bottom",draggable:true,showOnInit:true,columns:1,footer:false},initialize:function(a){this.setOptions(a);
this.constructPicker();if(this.options.showOnInit){this.show();}},constructPicker:function(){var c=this.options;var b=this.picker=new Element("div",{"class":c.pickerClass,styles:{left:0,top:0,display:"none",opacity:0}}).inject(c.inject||document.body);
b.addClass("column_"+c.columns);if(c.useFadeInOut){b.set("tween",{duration:c.animationDuration,link:"cancel"});}var h=this.header=new Element("div.header").inject(b);
var f=this.title=new Element("div.title").inject(h);var e=this.titleID="pickertitle-"+String.uniqueID();this.titleText=new Element("div",{role:"heading","class":"titleText",id:e,"aria-live":"assertive","aria-atomic":"true"}).inject(f);
this.closeButton=new Element("div.closeButton[text=x][role=button]").addEvent("click",this.close.pass(false,this)).inject(h);var a=this.body=new Element("div.body").inject(b);
if(c.footer){this.footer=new Element("div.footer").inject(b);b.addClass("footer");}var d=this.slider=new Element("div.slider",{styles:{position:"absolute",top:0,left:0}}).set("tween",{duration:c.animationDuration,transition:Fx.Transitions.Quad.easeInOut}).inject(a);
this.newContents=new Element("div",{styles:{position:"absolute",top:0,left:0}}).inject(d);this.oldContents=new Element("div",{styles:{position:"absolute",top:0}}).inject(d);
this.originalColumns=c.columns;this.setColumns(c.columns);var g=this.shim=window.IframeShim?new IframeShim(b):null;if(c.draggable&&typeOf(b.makeDraggable)=="function"){this.dragger=b.makeDraggable(g?{onDrag:g.position.bind(g)}:null);
b.setStyle("cursor","move");}},open:function(b){if(this.opened==true){return this;}this.opened=true;var a=this.picker.setStyle("display","block").set("aria-hidden","false");
if(this.shim){this.shim.show();}this.fireEvent("open");if(this.options.useFadeInOut&&!b){a.fade("in").get("tween").chain(this.fireEvent.pass("show",this));
}else{a.setStyle("opacity",1);this.fireEvent("show");}return this;},show:function(){return this.open(true);},close:function(d){if(this.opened==false){return this;
}this.opened=false;this.fireEvent("close");var a=this,b=this.picker,c=function(){b.setStyle("display","none").set("aria-hidden","true");if(a.shim){a.shim.hide();
}a.fireEvent("hide");};if(this.options.useFadeInOut&&!d){b.fade("out").get("tween").chain(c);}else{b.setStyle("opacity",0);c();}return this;},hide:function(){return this.close(true);
},toggle:function(){return this[this.opened==true?"close":"open"]();},destroy:function(){this.picker.destroy();if(this.shim){this.shim.destroy();}},position:function(f,e){var a=this.options.positionOffset,g=document.getScroll(),i=document.getSize(),h=this.picker.getSize();
if(typeOf(f)=="element"){var b=f,c=e||this.options.pickerPosition;var d=b.getCoordinates();f=(c=="left")?d.left-h.x:(c=="bottom"||c=="top")?d.left:d.right;
e=(c=="bottom")?d.bottom:(c=="top")?d.top-h.y:d.top;}f+=a.x*((c&&c=="left")?-1:1);e+=a.y*((c&&c=="top")?-1:1);if((f+h.x)>(i.x+g.x)){f=(i.x+g.x)-h.x;}if((e+h.y)>(i.y+g.y)){e=(i.y+g.y)-h.y;
}if(f<0){f=0;}if(e<0){e=0;}this.picker.setStyles({left:f,top:e});if(this.shim){this.shim.position();}return this;},setBodySize:function(){var a=this.bodysize=this.body.getSize();
this.slider.setStyles({width:2*a.x,height:a.y});this.oldContents.setStyles({left:a.x,width:a.x,height:a.y});this.newContents.setStyles({width:a.x,height:a.y});
},setColumnContent:function(c,d){var a=this.columns[c];if(!a){return this;}var b=typeOf(d);if(["string","number"].contains(b)){a.set("text",d);}else{a.empty().adopt(d);
}return this;},setColumnsContent:function(c,b){var a=this.columns;this.columns=this.newColumns;this.newColumns=a;c.forEach(function(d,e){this.setColumnContent(e,d);
},this);return this.setContent(null,b);},setColumns:function(d){var a=this.columns=new Elements,f=this.newColumns=new Elements;for(var c=d;c--;){a.push(new Element("div.column").addClass("column_"+(d-c)));
f.push(new Element("div.column").addClass("column_"+(d-c)));}var b="column_"+this.options.columns,e="column_"+d;this.picker.removeClass(b).addClass(e);
this.options.columns=d;return this;},setContent:function(c,b){if(c){return this.setColumnsContent([c],b);}var a=this.oldContents;this.oldContents=this.newContents;
this.newContents=a;this.newContents.empty();this.newContents.adopt(this.columns);this.setBodySize();if(b){this.fx(b);}else{this.slider.setStyle("left",0);
this.oldContents.setStyles({left:0,opacity:0});this.newContents.setStyles({left:0,opacity:1});}return this;},fx:function(e){var a=this.oldContents,b=this.newContents,d=this.slider,c=this.bodysize;
if(e=="right"){a.setStyles({left:0,opacity:1});b.setStyles({left:c.x,opacity:1});d.setStyle("left",0).tween("left",0,-c.x);}else{if(e=="left"){a.setStyles({left:c.x,opacity:1});
b.setStyles({left:0,opacity:1});d.setStyle("left",-c.x).tween("left",-c.x,0);}else{if(e=="fade"){d.setStyle("left",0);a.setStyle("left",0).set("tween",{duration:this.options.animationDuration/2}).tween("opacity",1,0).get("tween").chain(function(){a.setStyle("left",c.x);
});b.setStyles({opacity:0,left:0}).set("tween",{duration:this.options.animationDuration}).tween("opacity",0,1);}}}},toElement:function(){return this.picker;
},setTitle:function(b,a){if(!a){a=Function.from;}this.titleText.empty().adopt(Array.from(b).map(function(d,c){return typeOf(d)=="element"?d:new Element("div.column",{text:a(d,this.options)}).addClass("column_"+(c+1));
},this));return this;},setTitleEvent:function(a){this.titleText.removeEvents("click");if(a){this.titleText.addEvent("click",a);}this.titleText.setStyle("cursor",a?"pointer":"");
return this;}});