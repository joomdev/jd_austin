/*
 * ---
 * name: Picker
 * description: Creates a Picker, which can be used for anything
 * authors: Arian Stolwijk
 * requires: [Core/Element.Dimensions, Core/Fx.Tween, Core/Fx.Transitions]
 * provides: Picker
 * ...
 */
(function(){this.DatePicker=Picker.Date=new Class({Extends:Picker.Attach,options:{timePicker:false,timePickerOnly:false,timeWheelStep:1,yearPicker:true,yearsPerPage:20,startDay:1,rtl:false,startView:"days",openLastView:false,pickOnly:false,canAlwaysGoUp:["months","days"],updateAll:false,weeknumbers:false,months_abbr:null,days_abbr:null,years_title:function(f,e){var g=f.get("year");
return g+"-"+(g+e.yearsPerPage-1);},months_title:function(f,e){return f.get("year");},days_title:function(f,e){return f.format("%b %Y");},time_title:function(f,e){return(e.pickOnly=="time")?Locale.get("DatePicker.select_a_time"):f.format("%d %B, %Y");
}},initialize:function(g,f){this.parent(g,f);this.setOptions(f);f=this.options;["year","month","day","time"].some(function(h){if(f[h+"PickerOnly"]){f.pickOnly=h;
return true;}return false;});if(f.pickOnly){f[f.pickOnly+"Picker"]=true;f.startView=f.pickOnly;}var e=["days","months","years"];["month","year","decades"].some(function(j,h){return(f.startView==j)&&(f.startView=e[h]);
});f.canAlwaysGoUp=f.canAlwaysGoUp?Array.from(f.canAlwaysGoUp):[];if(f.minDate){if(!(f.minDate instanceof Date)){f.minDate=Date.parse(f.minDate);}f.minDate.clearTime();
}if(f.maxDate){if(!(f.maxDate instanceof Date)){f.maxDate=Date.parse(f.maxDate);}f.maxDate.clearTime();}if(!f.format){f.format=(f.pickOnly!="time")?Locale.get("Date.shortDate"):"";
if(f.timePicker){f.format=(f.format)+(f.format?" ":"")+Locale.get("Date.shortTime");}}this.addEvent("attached",function(l,k){if(!this.currentView||!f.openLastView){this.currentView=f.startView;
}this.date=c(new Date(),f.minDate,f.maxDate);var h=k.get("tag"),i;if(h=="input"){i=k;}else{var j=this.toggles.indexOf(k);if(this.inputs[j]){i=this.inputs[j];
}}this.getInputDate(i);this.input=i;this.setColumns(this.originalColumns);}.bind(this),true);},getInputDate:function(e){this.date=new Date();if(!e){return;
}var g=Date.parse(e.get("value"));if(g==null||!g.isValid()){var f=e.retrieve("datepicker:value");if(f){g=Date.parse(f);}}if(g!=null&&g.isValid()){this.date=g;
}},constructPicker:function(){this.parent();if(!this.options.rtl){this.previous=new Element("div.previous[html=&#171;]").inject(this.header);this.next=new Element("div.next[html=&#187;]").inject(this.header);
}else{this.next=new Element("div.previous[html=&#171;]").inject(this.header);this.previous=new Element("div.next[html=&#187;]").inject(this.header);}},hidePrevious:function(e,f){this[e?"next":"previous"].setStyle("display",f?"block":"none");
return this;},showPrevious:function(e){return this.hidePrevious(e,true);},setPreviousEvent:function(f,e){this[e?"next":"previous"].removeEvents("click");
if(f){this[e?"next":"previous"].addEvent("click",f);}return this;},hideNext:function(){return this.hidePrevious(true);},showNext:function(){return this.showPrevious(true);
},setNextEvent:function(e){return this.setPreviousEvent(e,true);},setColumns:function(h,e,g,i){var f=this.parent(h),j;if((e||this.currentView)&&(j="render"+(e||this.currentView).capitalize())&&this[j]){this[j](g||this.date.clone(),i);
}return f;},renderYears:function(g,j){var q=this.options,f=q.columns,o=q.yearsPerPage,e=[],h=[];this.dateElements=[];g=g.clone().decrement("year",g.get("year")%o);
var k=g.clone().decrement("year",Math.floor((f-1)/2)*o);for(var l=f;l--;){var p=k.clone();h.push(p);e.push(a.years(b.years(q,p.clone()),q,this.date.clone(),this.dateElements,function(i){if(q.pickOnly=="years"){this.select(i);
}else{this.renderMonths(i,"fade");}this.date=i;}.bind(this)));k.increment("year",o);}this.setColumnsContent(e,j);this.setTitle(h,q.years_title);var n=(q.minDate&&g.get("year")<=q.minDate.get("year")),m=(q.maxDate&&(g.get("year")+q.yearsPerPage)>=q.maxDate.get("year"));
this[(n?"hide":"show")+"Previous"]();this[(m?"hide":"show")+"Next"]();this.setPreviousEvent(function(){this.renderYears(g.decrement("year",o),"left");}.bind(this));
this.setNextEvent(function(){this.renderYears(g.increment("year",o),"right");}.bind(this));this.setTitleEvent(null);this.currentView="years";},renderMonths:function(g,j){var s=this.options,n=s.columns,f=[],h=[],k=g.clone().decrement("year",Math.floor((n-1)/2));
this.dateElements=[];for(var l=n;l--;){var r=k.clone();h.push(r);f.push(a.months(b.months(s,r.clone()),s,this.date.clone(),this.dateElements,function(i){if(s.pickOnly=="months"){this.select(i);
}else{this.renderDays(i,"fade");}this.date=i;}.bind(this)));k.increment("year",1);}this.setColumnsContent(f,j);this.setTitle(h,s.months_title);var p=g.get("year"),o=(s.minDate&&p<=s.minDate.get("year")),m=(s.maxDate&&p>=s.maxDate.get("year"));
this[(o?"hide":"show")+"Previous"]();this[(m?"hide":"show")+"Next"]();this.setPreviousEvent(function(){this.renderMonths(g.decrement("year",n),"left");
}.bind(this));this.setNextEvent(function(){this.renderMonths(g.increment("year",n),"right");}.bind(this));var e=s.yearPicker&&(s.pickOnly!="months"||s.canAlwaysGoUp.contains("months"));
var q=(e)?function(){this.renderYears(g,"fade");}.bind(this):null;this.setTitleEvent(q);this.currentView="months";},renderDays:function(h,k){var r=this.options,f=r.columns,g=[],j=[],m=h.clone().decrement("month",Math.floor((f-1)/2));
this.dateElements=[];for(var n=f;n--;){_date=m.clone();j.push(_date);g.push(a.days(b.days(r,_date.clone()),r,this.date.clone(),this.dateElements,function(i){if(r.pickOnly=="days"||!r.timePicker){this.select(i);
}else{this.renderTime(i,"fade");}this.date=i;}.bind(this)));m.increment("month",1);}this.setColumnsContent(g,k);this.setTitle(j,r.days_title);var l=h.format("%Y%m").toInt(),p=(r.minDate&&l<=r.minDate.format("%Y%m")),o=(r.maxDate&&l>=r.maxDate.format("%Y%m"));
this[(p?"hide":"show")+"Previous"]();this[(o?"hide":"show")+"Next"]();this.setPreviousEvent(function(){this.renderDays(h.decrement("month",f),"left");}.bind(this));
this.setNextEvent(function(){this.renderDays(h.increment("month",f),"right");}.bind(this));var e=r.pickOnly!="days"||r.canAlwaysGoUp.contains("days");var q=(e)?function(){this.renderMonths(h,"fade");
}.bind(this):null;this.setTitleEvent(q);this.currentView="days";},renderTime:function(i,j){var h=this.options;this.setTitle(i,h.time_title);var f=this.originalColumns=h.columns;
this.currentView=null;if(f!=1){this.setColumns(1);}this.setContent(a.time(h,i.clone(),function(k){this.select(k);}.bind(this)),j);this.hidePrevious().hideNext().setPreviousEvent(null).setNextEvent(null);
var g=h.pickOnly!="time"||h.canAlwaysGoUp.contains("time");var e=(g)?function(){this.setColumns(f,"days",i,"fade");}.bind(this):null;this.setTitleEvent(e);
this.currentView="time";},select:function(f,g){this.date=f;var h=f.format(this.options.format),i=f.strftime(),e=(!this.options.updateAll&&!g&&this.input)?[this.input]:this.inputs;
e.each(function(j){j.set("value",h).store("datepicker:value",i).fireEvent("change");},this);this.fireEvent("select",[f].concat(e));this.close();return this;
}});var b={years:function(f,e){var h=[];for(var g=0;g<f.yearsPerPage;g++){h.push(+e);e.increment("year",1);}return h;},months:function(f,e){var h=[];e.set("month",0);
for(var g=0;g<=11;g++){h.push(+e);e.increment("month",1);}return h;},days:function(f,e){var h=[];e.set("date",1);while(e.get("day")!=f.startDay){e.set("date",e.get("date")-1);
}for(var g=0;g<42;g++){h.push(+e);e.increment("day",1);}return h;}};var a={years:function(j,m,f,i,l){var e=new Element("div.years"),k=new Date(),h,g;j.each(function(o,p){var n=new Date(o),q=n.get("year");
g=".year.year"+p;if(q==k.get("year")){g+=".today";}if(q==f.get("year")){g+=".selected";}h=new Element("div"+g,{text:q}).inject(e);i.push({element:h,time:o});
if(d("year",n,m)){h.addClass("unavailable");}else{h.addEvent("click",l.pass(n));}});return e;},months:function(g,q,f,l,p){var o=new Date(),m=o.get("month"),k=o.get("year"),n=f.get("year"),e=new Element("div.months"),h=q.months_abbr||Locale.get("Date.months_abbr"),j,i;
g.each(function(s,t){var r=new Date(s),u=r.get("year");i=".month.month"+(t+1);if(t==m&&u==k){i+=".today";}if(t==f.get("month")&&u==n){i+=".selected";}j=new Element("div"+i,{text:h[t]}).inject(e);
l.push({element:j,time:s});if(d("month",r,q)){j.addClass("unavailable");}else{j.addEvent("click",p.pass(r));}});return e;},days:function(j,g,m,w,l){var v=new Date(j[14]).get("month"),k=new Date().toDateString(),e=m.toDateString(),n=g.weeknumbers,q=new Element("table.days"+(n?".weeknumbers":""),{role:"grid","aria-labelledby":this.titleID}),s=new Element("thead").inject(q),p=new Element("tbody").inject(q),x=new Element("tr.titles").inject(s),i=g.days_abbr||Locale.get("Date.days_abbr"),r,t,f,h,u,o=g.rtl?"top":"bottom";
if(n){new Element("th.title.day.weeknumber",{text:Locale.get("DatePicker.week")}).inject(x);}for(r=g.startDay;r<(g.startDay+7);r++){new Element("th.title.day.day"+(r%7),{text:i[(r%7)],role:"columnheader"}).inject(x,o);
}j.each(function(y,A){var z=new Date(y);if(A%7==0){h=new Element("tr.week.week"+(Math.floor(A/7))).set("role","row").inject(p);if(n){new Element("th.day.weeknumber",{text:z.get("week"),scope:"row",role:"rowheader"}).inject(h);
}}u=z.toDateString();t=".day.day"+z.get("day");if(u==k){t+=".today";}if(z.get("month")!=v){t+=".otherMonth";}f=new Element("td"+t,{text:z.getDate(),role:"gridcell"}).inject(h,o);
if(u==e){f.addClass("selected").set("aria-selected","true");}else{f.set("aria-selected","false");}w.push({element:f,time:y});if(d("date",z,g)){f.addClass("unavailable");
}else{f.addEvent("click",l.pass(z.clone()));}});return q;},time:function(g,f,h){var e=new Element("div.time"),j=(f.get("minutes")/g.timeWheelStep).round()*g.timeWheelStep;
if(j>=60){j=0;}f.set("minutes",j);var i=new Element("input.hour[type=text]",{title:Locale.get("DatePicker.use_mouse_wheel"),value:f.format("%H"),events:{click:function(l){l.target.focus();
l.stop();},mousewheel:function(l){l.stop();i.focus();var m=i.get("value").toInt();m=(l.wheel>0)?((m<23)?m+1:0):((m>0)?m-1:23);f.set("hours",m);i.set("value",f.format("%H"));
}.bind(this)},maxlength:2}).inject(e);var k=new Element("input.minutes[type=text]",{title:Locale.get("DatePicker.use_mouse_wheel"),value:f.format("%M"),events:{click:function(l){l.target.focus();
l.stop();},mousewheel:function(l){l.stop();k.focus();var m=k.get("value").toInt();m=(l.wheel>0)?((m<59)?(m+g.timeWheelStep):0):((m>0)?(m-g.timeWheelStep):(60-g.timeWheelStep));
if(m>=60){m=0;}f.set("minutes",m);k.set("value",f.format("%M"));}.bind(this)},maxlength:2}).inject(e);new Element("div.separator[text=:]").inject(e);new Element("input.ok[type=submit]",{value:Locale.get("DatePicker.time_confirm_button"),events:{click:function(l){l.stop();
f.set({hours:i.get("value").toInt(),minutes:k.get("value").toInt()});h(f.clone());}}}).inject(e);return e;}};Picker.Date.defineRenderer=function(e,f){a[e]=f;
return this;};var c=function(f,g,e){if(g&&f<g){return g;}if(e&&f>e){return e;}return f;};var d=function(k,g,o){var h=o.minDate,f=o.maxDate,n=o.availableDates,l,j,m,e;
if(!h&&!f&&!n){return false;}g.clearTime();if(k=="year"){l=g.get("year");return((h&&l<h.get("year"))||(f&&l>f.get("year"))||((n!=null&&!o.invertAvailable)&&(n[l]==null||Object.getLength(n[l])==0||Object.getLength(Object.filter(n[l],function(p){return(p.length>0);
}))==0)));}if(k=="month"){l=g.get("year");j=g.get("month")+1;e=g.format("%Y%m").toInt();return((h&&e<h.format("%Y%m").toInt())||(f&&e>f.format("%Y%m").toInt())||((n!=null&&!o.invertAvailable)&&(n[l]==null||n[l][j]==null||n[l][j].length==0)));
}l=g.get("year");j=g.get("month")+1;m=g.get("date");var i=(h&&g<h)||(h&&g>f);if(n!=null){i=i||n[l]==null||n[l][j]==null||!n[l][j].contains(m);if(o.invertAvailable){i=!i;
}}return i;};})();