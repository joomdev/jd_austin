/*
 * ---
 * name: Picker
 * description: Creates a Picker, which can be used for anything
 * authors: Arian Stolwijk
 * requires: [Core/Element.Dimensions, Core/Fx.Tween, Core/Fx.Transitions]
 * provides: Picker
 * ...
 */
Picker.Attach=new Class({Extends:Picker,options:{togglesOnly:true,showOnInit:false,blockKeydown:true},initialize:function(e,d){this.parent(d);this.attachedEvents=[];
this.attachedElements=[];this.toggles=[];this.inputs=[];var b=function(f){if(this.attachedElements.contains(f.target)){return;}this.close();}.bind(this);
var a=this.picker.getDocument().addEvent("click",b);var c=function(f){f.stopPropagation();return false;};this.picker.addEvent("click",c);if(this.options.toggleElements){this.options.toggle=a.getElements(this.options.toggleElements);
}this.attach(e,this.options.toggle);},attach:function(b,c){if(typeOf(b)=="string"){b=document.id(b);}if(typeOf(c)=="string"){c=document.id(c);}var a=Array.from(b),d=Array.from(c),e=[].append(a).combine(d),i=this;
var g=function(l){var j=i.options.blockKeydown&&l.type=="keydown"&&!(["tab","esc"].contains(l.key)),k=l.type=="keydown"&&(["tab","esc"].contains(l.key)),m=l.target.get("tag")=="a"||l.target.getParent().get("tag")=="a";
if(j||m){l.preventDefault();}if(k||m){i.close();}};var h=function(j){return function(l){var k=l.target.get("tag");if(k=="input"&&l.type=="click"&&!j.match(":focus")||(i.opened&&i.input==j)){return;
}if(k=="a"||l.target.getParent().get("tag")=="a"){l.stop();}i.position(j);i.open();i.fireEvent("attached",[l,j]);};};var f=function(j,k){return function(l){if(i.opened){k(l);
}else{j(l);}};};e.each(function(m){if(i.attachedElements.contains(m)){return;}var l={},j=m.get("tag"),n=h(m),k=f(n,g);if(j=="input"){if(!i.options.togglesOnly||!d.length){l={focus:n,click:n,keydown:g};
}i.inputs.push(m);}else{if(d.contains(m)){i.toggles.push(m);l.click=k;}else{l.click=n;}}m.addEvents(l);i.attachedElements.push(m);i.attachedEvents.push(l);
});return this;},detach:function(c,a){if(typeOf(c)=="string"){c=document.id(c);}if(typeOf(a)=="string"){a=document.id(a);}var f=Array.from(c),e=Array.from(a),d=[].append(f).combine(e),b=this;
if(!d.length){d=b.attachedElements;}d.each(function(j){var h=b.attachedElements.indexOf(j);if(h<0){return;}var g=b.attachedEvents[h];j.removeEvents(g);
delete b.attachedEvents[h];delete b.attachedElements[h];var l=b.toggles.indexOf(j);if(l!=-1){delete b.toggles[l];}var k=b.inputs.indexOf(j);if(l!=-1){delete b.inputs[k];
}});return this;},destroy:function(){this.detach();return this.parent();}});