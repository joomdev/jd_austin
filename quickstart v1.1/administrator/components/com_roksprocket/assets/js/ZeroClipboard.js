/*
// Simple Set Clipboard System
// Author: Joseph Huckaby
*/
((function(){var a="ZeroClipboard"+(Browser.Plugins.Flash&&Browser.Plugins.Flash.version>=10?"10":"")+".swf";
this.ZeroClipboard={version:"1.0.7",clients:{},moviePath:"/components/com_roksprocket/assets/js/"+a,nextId:1,$:function(b){return document.id(b)||document.getElement(b)||null;
},setMoviePath:function(b){this.moviePath=b;},dispatch:function(e,c,d){var b=this.clients[e];if(b){b.receiveEvent(c,d);}},register:function(c,b){this.clients[c]=b;
},getDOMObjectPosition:function(d,b){var c={left:0,top:0,width:d.width?d.width:d.offsetWidth,height:d.height?d.height:d.offsetHeight};while(d&&(d!=b)){c.left+=d.offsetLeft;
c.top+=d.offsetTop;d=d.offsetParent;}return c;},Client:function(b){this.handlers={};this.id=ZeroClipboard.nextId++;this.movieId="ZeroClipboardMovie_"+this.id;
ZeroClipboard.register(this.id,this);if(b){this.glue(b);}}};ZeroClipboard.Client.prototype={id:0,ready:false,movie:null,clipText:"",handCursorEnabled:true,cssEffects:true,handlers:null,glue:function(e,c,g){this.domElement=ZeroClipboard.$(e);
var h=99;if(this.domElement.style.zIndex){h=parseInt(this.domElement.style.zIndex,10)+1;}if(typeof(c)=="string"){c=ZeroClipboard.$(c);}else{if(typeof(c)=="undefined"){c=document.getElementsByTagName("body")[0];
}}var d=ZeroClipboard.getDOMObjectPosition(this.domElement,c);this.div=document.createElement("div");var b=this.div.style;b.position="absolute";b.left=""+d.left+"px";
b.top=""+d.top+"px";b.width=""+d.width+"px";b.height=""+d.height+"px";b.zIndex=h;if(typeof(g)=="object"){for(var f in g){b[f]=g[f];}}c.appendChild(this.div);
this.div.innerHTML=this.getHTML(d.width,d.height);},getHTML:function(e,b){var d="";var c="id="+this.id+"&width="+e+"&height="+b;if(navigator.userAgent.match(/MSIE/)){var f=location.href.match(/^https/i)?"https://":"http://";
d+='<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="'+f+'download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="'+e+'" height="'+b+'" id="'+this.movieId+'" align="middle"><param name="allowScriptAccess" value="always" /><param name="allowFullScreen" value="false" /><param name="movie" value="'+ZeroClipboard.moviePath+'" /><param name="loop" value="false" /><param name="menu" value="false" /><param name="quality" value="best" /><param name="bgcolor" value="#ffffff" /><param name="flashvars" value="'+c+'"/><param name="wmode" value="transparent"/></object>';
}else{d+='<embed id="'+this.movieId+'" src="'+ZeroClipboard.moviePath+'" loop="false" menu="false" quality="best" bgcolor="#ffffff" width="'+e+'" height="'+b+'" name="'+this.movieId+'" align="middle" allowScriptAccess="always" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" flashvars="'+c+'" wmode="transparent" />';
}return d;},hide:function(){if(this.div){this.div.style.left="-2000px";}},show:function(){this.reposition();},destroy:function(){if(this.domElement&&this.div){this.hide();
this.div.innerHTML="";var b=document.getElementsByTagName("body")[0];try{b.removeChild(this.div);}catch(c){}this.domElement=null;this.div=null;}},reposition:function(d){if(d){this.domElement=ZeroClipboard.$(d);
if(!this.domElement){this.hide();}}if(this.domElement&&this.div){var c=ZeroClipboard.getDOMObjectPosition(this.domElement);var b=this.div.style;b.left=""+c.left+"px";
b.top=""+c.top+"px";}},setText:function(b){this.clipText=b;if(this.ready){this.movie.setText(b);}},addEventListener:function(b,c){b=b.toString().toLowerCase().replace(/^on/,"");
if(!this.handlers[b]){this.handlers[b]=[];}this.handlers[b].push(c);},setHandCursor:function(b){this.handCursorEnabled=b;if(this.ready){this.movie.setHandCursor(b);
}},setCSSEffects:function(b){this.cssEffects=!!b;},receiveEvent:function(e,f){e=e.toString().toLowerCase().replace(/^on/,"");var d=this;switch(e){case"load":this.movie=document.getElementById(this.movieId);
if(!this.movie){d=this;setTimeout(function(){d.receiveEvent("load",null);},1);return;}if(!this.ready&&navigator.userAgent.match(/Firefox/)&&navigator.userAgent.match(/Windows/)){d=this;
setTimeout(function(){d.receiveEvent("load",null);},100);this.ready=true;return;}this.ready=true;this.movie.setText(this.clipText);this.movie.setHandCursor(this.handCursorEnabled);
break;case"mouseover":if(this.domElement&&this.cssEffects){this.domElement.addClass("hover");if(this.recoverActive){this.domElement.addClass("active");
}}break;case"mouseout":if(this.domElement&&this.cssEffects){this.recoverActive=false;if(this.domElement.hasClass("active")){this.domElement.removeClass("active");
this.recoverActive=true;}this.domElement.removeClass("hover");}break;case"mousedown":if(this.domElement&&this.cssEffects){this.domElement.addClass("active");
}break;case"mouseup":if(this.domElement&&this.cssEffects){this.domElement.removeClass("active");this.recoverActive=false;}break;}if(this.handlers[e]){for(var c=0,b=this.handlers[e].length;
c<b;c++){var g=this.handlers[e][c];if(typeof(g)=="function"){g(this,f);}else{if((typeof(g)=="object")&&(g.length==2)){g[0][g[1]](this,f);}else{if(typeof(g)=="string"){window[g](this,f);
}}}}}}};})());