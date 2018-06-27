/*
 * @version   $Id: joomla-calendar.js 10889 2013-05-30 07:48:35Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){window.addEvent("domready",function(){if(typeof Calendar!="undefined"&&typeof Calendar.prototype.showAtElement!="undefined"){Calendar.prototype.showAtElement=function(c,d){var a=this;
var e=Calendar.getAbsolutePos(c),f=document.id(this.params.inputField);if(f){e=f.getPosition();this.showAt(e.x,e.y+f.offsetHeight+2);return true;}if(!d||typeof d!="string"){this.showAt(e.x,e.y+c.offsetHeight);
return true;}function b(j){if(j.x<0){j.x=0;}if(j.y<0){j.y=0;}var k=document.createElement("div");var i=k.style;i.position="absolute";i.right=i.bottom=i.width=i.height="0px";
document.body.appendChild(k);var h=Calendar.getAbsolutePos(k);document.body.removeChild(k);if(Calendar.is_ie){h.y+=document.body.scrollTop;h.x+=document.body.scrollLeft;
}else{h.y+=window.scrollY;h.x+=window.scrollX;}var g=j.x+j.width-h.x;if(g>0){j.x-=g;}g=j.y+j.height-h.y;if(g>0){j.y-=g;}}this.element.style.display="block";
Calendar.continuation_for_the_khtml_browser=function(){var g=a.element.offsetWidth;var j=a.element.offsetHeight;a.element.style.display="none";var i=d.substr(0,1);
var k="l";if(d.length>1){k=d.substr(1,1);}switch(i){case"T":e.y-=j;break;case"B":e.y+=c.offsetHeight;break;case"C":e.y+=(c.offsetHeight-j)/2;break;case"t":e.y+=c.offsetHeight-j;
break;case"b":break;}switch(k){case"L":e.x-=g;break;case"R":e.x+=c.offsetWidth;break;case"C":e.x+=(c.offsetWidth-g)/2;break;case"l":e.x+=c.offsetWidth-g;
break;case"r":break;}e.width=g;e.height=j+40;a.monthsCombo.style.display="none";b(e);a.showAt(e.x,e.y);};if(Calendar.is_khtml){setTimeout("Calendar.continuation_for_the_khtml_browser()",10);
}else{Calendar.continuation_for_the_khtml_browser();}};}});})());