var JSTools = {version:1.0,author:'lymz',email:'lymz.86@gmail.com'};
var $id = function(id){
	return $extend(document.getElementById(id));
};
var $class = function(clas){
	return $extend(getElementsByClassName(clas));
};
var $Eclass = function(clas){
	var t = $class(clas);
	return t.length > 0 ? t[0] : null;
};
var $tag = function(tag){
	return $extend(document.getElementsByTagName(tag));
};
var $Etag = function(tag){
	var t = $tag(tag);
	return t.length > 0 ? t[0] : null;
};
var $dom = function(dom){
	var t = dom.split(' ');
	var p=null;
	for(var i = 0,l = t.length;i < l;++ i) {
		if(i > 0 && !p)
			break;
		else if(i > 0 && p) {
			if(p.length != undefined)
				p = p[0];
		}
		var tt = t[i].split('#');
		if(tt[0] == 'id')
			p = $id(tt[1]);
		else if(tt[0] == 'class') {
			tt[1] = tt[1].split('|');
			if(p)
				p = getElementsByClassName(tt[1][0],p);
			else
				p = $class(tt[1][0]);

			if(tt[1][1])
				p = $filter(p,tt[1][1]);
		} else if(tt[0] == 'tag') {
			tt[1] = tt[1].split('|');
			if(p)
				p = p.getElementsByTagName(tt[1][0]);
			else
				p = $tag(tt[1][0]);

			if(tt[1][1])
				p = $filter(p,tt[1][1]);
		} else
			p = null;
	}
	return $extend(p);
};
var $filter = function(p,filter){
	if(p == null)
		return null;
	var tt = new Array();
	if(filter.indexOf('=') !== -1) {
		var t = filter.split('=');
		for(var i = 0,j = 0,l = p.length;i < l;++ i) {
			if(p[i].getAttribute(t[0]) == t[1])
				tt[j++] = p[i];
		}
	} else if(filter.indexOf('^') !== -1) {
		var t = filter.split('^');
		for(var i = 0,j = 0,l = p.length;i < l;++ i) {
			if(p[i].getAttribute(t[0]).toLowerCase() != t[1].toLowerCase())
				tt[j++] = p[i];
		}
	} else {
		for(var i = 0,j = 0,l = p.length;i < l;++ i) {
			if(p[i].nodeName.toLowerCase() == filter.toLowerCase())
				tt[j++] = p[i];
		}
	}
	return tt;
};
var $Edom = function(dom){
	var t = $dom(dom);
	if(t != null && t.length != undefined)
		return t[0];
	else
		return t;
};
var $extend = function(object){
	if(!object)
		return object;
	var t = false;
	if(object.length == undefined){
		object = new Array(object);
		t = true;
	}
	for(var i = 0,l = object.length;i < l;++ i) {
		object[i].getAttr = function(attr){return this.getAttribute(attr);};
		object[i].setAttr = function(k,v){return this.setAttribute(k,v);};
		object[i].getStyle = function(k){return eval('this.style.'+k);};
		object[i].setStyle = function(k,v){return eval('this.style.'+k+'=\''+v+'\'');};
		object[i].getVal = function(){return this.value!=undefined?this.value:this.innerHTML;};
		object[i].setVal = function(v){if(this.value!=undefined)this.value=v;else this.innerHTML=v;return true;};
		object[i].show = function(){return this.setStyle('display','block');};
		object[i].hide = function(){return this.setStyle('display','none');};
	}
	object.each=function(func){
		if(this.length == undefined)
			object = new Array(object);
		var it = null;
		for(var i = 0,l = object.length;i < l;++ i) {
			it = object[i];
			func(it,i);
		}
	};
	return t ? object[0] : object;
};
var ajax = function(type,url,func,data){
	if(window.ActiveXObject){
		var xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
	} else {
		var xmlHttp=new XMLHttpRequest();
	}
	xmlHttp.open(type,url,true);
	xmlHttp.onreadystatechange=function(){
		if(xmlHttp.readyState == 4)
			if(xmlHttp.status == 200)
				if(func) func(xmlHttp.responseText);
	};
	xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xmlHttp.send(data?data:null);
};
var cookie = {
	get:function(k){
		var t = document.cookie.split('; ');
		for(var i = 0,l = t.length;i < l;++ i) {
			var tt = t[i].split('=');
			if(tt[0] == k) {
				return decodeURI(tt[1]);
			}
		}
		return undefined;
	},
	set:function(k,v,e){
		if(e) {
			var d = new Date();
			d.setTime(d.getTime()+(e*1000));
			e = ';expires='+d.toGMTString();
		} else
			e = '';
		document.cookie = k + '=' + v + e;
	}
};
var getElementsByClassName = function(c,p){
	p = p ? p : document;
    var arrElements = p.getElementsByTagName('*');
    var arrReturnElements = new Array();
    c = c.replace(/\-/g, "\\-");
    var oRegExp = new RegExp("(^|\\s)" + c + "(\\s|$)");
    var oElement;
    for(var i=0; i < arrElements.length; i++){
        oElement = arrElements[i];
        if(oRegExp.test(oElement.className)){
            arrReturnElements.push(oElement);
        }
    }
    return arrReturnElements;
};
