/*
 * @version   $Id: roksprocket.request.js 10889 2013-05-30 07:48:35Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){if(typeof this.RokSprocket=="undefined"){this.RokSprocket={};
}else{Object.merge(this.RokSprocket,{Request:null});}var f=function(g){return g!=null;};var e=Object.prototype.hasOwnProperty;Object.extend({getFromPath:function(j,k){if(typeof k=="string"){k=k.split(".");
}for(var h=0,g=k.length;h<g;h++){if(e.call(j,k[h])){j=j[k[h]];}else{return null;}}return j;},cleanValues:function(g,i){i=i||f;for(var h in g){if(!i(g[h])){delete g[h];
}}return g;},erase:function(g,h){if(e.call(g,h)){delete g[h];}return g;},run:function(h){var g=Array.slice(arguments,1);for(var i in h){if(h[i].apply){h[i].apply(h,g);
}}return h;}});var d=function(){},a=("onprogress" in new Browser.Request()),c=new Class({Extends:this.Request,options:{method:"post",model:"",model_action:"",params:{}},initialize:function(g){this.options.url=RokSprocket.AjaxURL.replace(/&amp;/g,"&");
this.parent(g);},processScripts:function(g){return g;},onStateChange:function(){var g=this.xhr;if(g.readyState!=4||!this.running){return;}this.running=false;
this.status=0;Function.attempt(function(){var h=g.status;this.status=(h==1223)?204:h;}.bind(this));g.onreadystatechange=d;if(a){g.onprogress=g.onloadstart=d;
}clearTimeout(this.timer);this.response=new b(this.xhr.responseText||"",{onError:this.onResponseError.bind(this)});if(this.options.isSuccess.call(this,this.status)){if(this.response.getPath("status")=="success"){this.success(this.response);
}else{this.onResponseError(this.response);}}else{this.failure();this.onResponseError(this.response);}},onResponseError:function(i){var h=this.options.data,g="RokSprocket Error [model: "+h.model+", model_action: "+h.model_action+", params: "+h.params+"]: ";
g+=(i.status?i.status+" - "+i.statusText:i);this.fireEvent("onResponseError",i,g);throw new Error(g);},setParams:function(h){var g=Object.merge(this.options.data||{},{params:h||{}});
g.params=JSON.encode(g.params);this.options.data=g;["model","model_action"].each(function(i){this.options.data[i]=this.options[i];},this);return this;}});
var b=new Class({Implements:[Options,Events],options:{},initialize:function(h,g){this.setOptions(g);this.setData(h);return this;},setData:function(g){if(typeOf(g)=="string"){g=g.trim();
}this.data=g;},getData:function(){return(typeOf(this.data)!="object")?this.parseData(this.data):this.data;},parseData:function(){if(!JSON.validate(this.data)){return this.error("Invalid JSON data <hr /> "+this.data);
}this.data=JSON.decode(this.data);if(this.data.status!="success"){return this.error(this.data.message);}this.fireEvent("parse",this.data);return this.success(this.data);
},getPath:function(h){var g=this.getData();if(typeOf(g)=="object"){return Object.getFromPath(g,h||"");}else{return null;}},success:function(g){this.data=g;
this.fireEvent("success",this.data);return this.data;},error:function(g){this.data=g;this.fireEvent("error",this.data);return this.data;}});this.RokSprocket.Request=c;
})());