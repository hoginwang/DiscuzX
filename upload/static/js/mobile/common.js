var platform = navigator.platform;
var ua = navigator.userAgent;
var ios = /iPhone|iPad|iPod/.test(platform) && ua.indexOf( "AppleWebKit" ) > -1;
var andriod = ua.indexOf( "Android" ) > -1;

var JSLOADED = [];

var HTML5PLAYER = [];
HTML5PLAYER['apload'] = 0;
HTML5PLAYER['dpload'] = 0;
HTML5PLAYER['flvload'] = 0;

var BROWSER = {};
var USERAGENT = navigator.userAgent.toLowerCase();
browserVersion({'ie':'msie','firefox':'','chrome':'','opera':'','safari':'','mozilla':'','webkit':'','maxthon':'','qq':'qqbrowser','rv':'rv'});
if(BROWSER.safari || BROWSER.rv) {
	BROWSER.firefox = true;
}
BROWSER.opera = BROWSER.opera ? opera.version() : 0;

var page = {
	converthtml : function() {
		var prevpage = qSel('div.pg .prev') ? qSel('div.pg .prev').href : undefined;
		var nextpage = qSel('div.pg .nxt') ? qSel('div.pg .nxt').href : undefined;
		var lastpage = qSel('div.pg label span') ? (qSel('div.pg label span').innerText.replace(/[^\d]/g, '') || 0) : 0;
		var curpage = qSel('div.pg input') ? qSel('div.pg input').value : 1;

		if(!lastpage) {
			prevpage = qSel('div.pg .pgb a') ? qSel('div.pg .pgb a').href : undefined;
		}

		var prevpagehref = nextpagehref = '';
		if(prevpage == undefined) {
			prevpagehref = 'javascript:;" class="grey';
		} else {
			prevpagehref = prevpage;
		}
		if(nextpage == undefined) {
			nextpagehref = 'javascript:;" class="grey';
		} else {
			nextpagehref = nextpage;
		}

		var selector = '';
		if(lastpage) {
			selector += '<a id="select_a">';
			selector += '<select id="dumppage">';
			for(var i=1; i<=lastpage; i++) {
				selector += '<option value="'+i+'" '+ (i == curpage ? 'selected' : '') +'>第'+i+'页</option>';
			}
			selector += '</select>';
			selector += '<span>第'+curpage+'页</span>';
		}

		var pgobj = qSel('div.pg');
		pgobj.classList.remove('pg');
		pgobj.classList.add('page');
		pgobj.innerHTML = '<a href="'+ prevpagehref +'">上一页</a>'+ selector +'<a href="'+ nextpagehref +'">下一页</a>';
		qSel('#dumppage').addEventListener('change', function() {
			var href = (prevpage || nextpage);
			window.location.href = href.replace(/page=\d+/, 'page=' + this.value);
		});
	},
};

var scrolltop = {
	obj : null,
	init : function(obj) {
		scrolltop.obj = obj;
		var pageHeight = Math.max(document.body.scrollHeight, document.body.offsetHeight);
		var screenHeight = window.innerHeight;
		var scrollType = 'bottom';
		var scrollToPos = function() {
			if(scrollType == 'bottom') {
				window.scrollTo(0, pageHeight);
			} else {
				window.scrollTo(0, 0);
			}
			scrollfunc();
		};
		var scrollfunc = function() {
			var newType;
			if(document.documentElement.scrollTop >= 50) {
				newType = 'top';
			} else {
				newType = 'bottom';
			}
			if(newType != scrollType) {
				scrollType = newType;
				if(newType == 'top') {
					obj.classList.remove('bottom');
				} else {
					obj.classList.add('bottom');
				}
			}
		};
		if(pageHeight - screenHeight < 100) {
			obj.style.display = 'none';
		} else {
			obj.addEventListener('click', scrollToPos);
			document.addEventListener('scroll', scrollfunc);
			scrollfunc();
		}
	},
};

var img = {
	init : function(is_err_t) {
		var errhandle = this.errorhandle;
		var images = qSelA("img");
		for (var i = 0; i < images.length; i++) {
			images[i].addEventListener("load", function() {
				var obj = this;
				obj.setAttribute("zsrc", obj.getAttribute("src"));
				if (obj.width < 5 && obj.height < 10 && obj.style.display != "none") {
					return errhandle(obj, is_err_t);
				}
				obj.style.display = "inline";
				obj.style.visibility = "visible";
				if (obj.width > window.innerWidth) {
					obj.style.width = window.innerWidth + "px";
				}
				var parent = obj.parentNode;
				var loading = parent.querySelector(".loading");
				var error_text = parent.querySelector(".error_text");
				if (loading) {
					loading.remove();
				}
				if (error_text) {
					error_text.remove();
				}
			});
			images[i].addEventListener("error", function() {
				var obj = this;
				obj.setAttribute("zsrc", obj.getAttribute("src"));
				errhandle(obj, is_err_t);
			});
		}
	},
	errorhandle : function(obj, is_err_t) {
		if (obj.getAttribute("noerror") == "true") {
			return;
		}
		obj.style.visibility = "hidden";
		obj.style.display = "none";
		var parentnode = obj.parentNode;
		var loading = parentnode.querySelector(".loading");
		if (loading) {
			loading.remove();
		}
		var error_text = parentnode.querySelector(".error_text");
		if (error_text) {
			error_text.remove();
		}
		var loadnums = parseInt(obj.getAttribute("load")) || 0;
		if (loadnums < 3) {
			obj.setAttribute("src", obj.getAttribute("zsrc"));
			obj.setAttribute("load", ++loadnums);
			return false;
		}
		if (is_err_t) {
			var div = document.createElement("div");
			div.classList.add("loading");
			div.style.background = "url('" + IMGDIR + "/imageloading.gif') no-repeat center center";
			div.style.width = parentnode.offsetWidth + "px";
			div.style.height = parentnode.offsetHeight + "px";
			parentnode.appendChild(div);
			var error_text = document.createElement("div");
			error_text.classList.add("error_text");
			error_text.textContent = "点击重新加载";
			parentnode.appendChild(error_text);
			error_text.addEventListener("click", function() {
				obj.setAttribute("load", 0);
				error_text.remove();
				var div = document.createElement("div");
				div.classList.add("loading");
				div.style.background = "url('" + IMGDIR + "/imageloading.gif') no-repeat center center";
				div.style.width = parentnode.offsetWidth + "px";
				div.style.height = parentnode.offsetHeight + "px";
				parentnode.appendChild(div);
				obj.setAttribute("src", obj.getAttribute("zsrc"));
			});
		}
		return false;
	}
};

var POPMENU = new Object;
var popup = {
	init : function() {
		var $this = this;
		var popups = qSelA(".popup");
		for (var i = 0; i < popups.length; i++) {
			var obj = popups[i];
			var pop = qSel(obj.getAttribute("href"));
			if (pop && pop.hasAttribute("popup")) {
				pop.style.display = "none";
				obj.addEventListener("click", function(e) {
					$this.open(pop);
					return false;
				});
			}
		}
		this.maskinit();
	},
	maskinit : function() {
		var $this = this;
		var mask = qSel("#mask");
		mask.addEventListener("click", function() {
			$this.close();
		});
	},

	open : function(pop, type, url) {
		this.close();
		this.maskinit();
		if (typeof pop == "string") {
			var ntcmsg = qSel("#ntcmsg");
			if (ntcmsg) {
				ntcmsg.parentNode.removeChild(ntcmsg);
			}
			if (type == "alert") {
				pop = '<div class="tip"><dt>' + pop + '</dt><dd><input class="button2" type="button" value="确定" onclick="popup.close();"></dd></div>';
			} else if (type == "confirm") {
				pop = '<div class="tip"><dt>' + pop + '</dt><dd><a class="button" href="' + url + '">确定</a> <button onclick="popup.close();" class="button">取消</a></dd></div>';
			}
			var div = document.createElement("div");
			div.setAttribute("id", "ntcmsg");
			div.style.display = "none";
			div.innerHTML = pop;
			document.body.appendChild(div);
			pop = qSel("#ntcmsg");
		}
		if (POPMENU[pop.getAttribute("id")]) {
			qSel("#" + pop.getAttribute("id") + "_popmenu").innerHTML = pop.innerHTML;
			if (pop.style.display == 'none') {
				pop.style.display = '';
				qSel("#" + pop.getAttribute("id") + "_popmenu").style.height = pop.offsetHeight + "px";
				qSel("#" + pop.getAttribute("id") + "_popmenu").style.width = pop.offsetWidth + "px";
				pop.style.display = 'none';
			} else {
				qSel("#" + pop.getAttribute("id") + "_popmenu").style.height = pop.offsetHeight + "px";
				qSel("#" + pop.getAttribute("id") + "_popmenu").style.width = pop.offsetWidth + "px";
			}
		} else {
			var div = document.createElement("div");
			div.classList.add("dialogbox");
			div.setAttribute("id", pop.getAttribute("id") + "_popmenu");
			if (pop.style.display == 'none') {
				pop.style.display = '';
				div.style.height = pop.offsetHeight + "px";
				div.style.width = pop.offsetWidth + "px";
				pop.style.display = 'none';
			} else {
				div.style.height = pop.offsetHeight + "px";
				div.style.width = pop.offsetWidth + "px";
			}
			div.innerHTML = pop.innerHTML;
			pop.parentNode.appendChild(div);
		}
		var popupobj = qSel("#" + pop.getAttribute("id") + "_popmenu");
		popupobj.style.display = "block";
		popupobj.style.position = "fixed";
		// Todo: 这里有没有更好的办法, 直接从 popupobj 取到值, 避免写死带来模板开发不便
		popupobj.style.left = (document.documentElement.clientWidth - popupobj.children[0].offsetWidth) / 2 + "px";
		popupobj.style.top = (document.documentElement.clientHeight - popupobj.children[0].offsetHeight) / 2 + "px";
		popupobj.style.zIndex = "120";
		popupobj.style.opacity = "1";
		var mask = qSel("#mask");
		mask.style.display = "block";
		mask.style.width = "100%";
		mask.style.height = "100%";
		mask.style.position = "fixed";
		mask.style.top = "0";
		mask.style.left = "0";
		mask.style.background = "black";
		mask.style.opacity = "0.2";
		mask.style.zIndex = "100";
		POPMENU[pop.getAttribute("id")] = pop;
	},
	close : function() {
		var mask = qSel("#mask");
		if (typeof(mask) != undefined) {
			mask.style.display = "none";
		}
		for (var key in POPMENU) {
			var popupobj = qSel("#" + key + "_popmenu");
			popupobj.style.display = "none";
		}
	}
};

var dialog = {
	init : function() {
		document.addEventListener("click", function(e) {
			var target = e.target;
			if (target.classList.contains("dialog")) {
				e.preventDefault();
				var obj = target;
				popup.open('<img src="' + IMGDIR + '/imageloading.gif">');
				var xhr = new XMLHttpRequest();
				xhr.open("GET", obj.getAttribute("href") + "&inajax=1", true);
				xhr.onload = function() {
					if (xhr.status >= 200 && xhr.status < 300) {
						var s = xhr.responseXML;
						if (typeof(s) == undefined) {
							window.location.href = obj.getAttribute("href");
							popup.close();
						} else {
							popup.open(s.lastChild.firstChild.nodeValue);
							evalscript(s.lastChild.firstChild.nodeValue);
						}
					} else {
						window.location.href = obj.getAttribute("href");
						popup.close();
					}
				};
				xhr.send();
			}
		});
	}

};

var formdialog = {
	init : function() {
		document.addEventListener("click", function(e) {
			var target = e.target;
			if (target.classList.contains("formdialog")) {
				e.preventDefault();
				popup.open('<img src="' + IMGDIR + '/imageloading.gif">');
				var obj = target;
				var formobj = obj.form;
				var isFormData = formobj.querySelectorAll("input[type='file']").length > 0;
				var xhr = new XMLHttpRequest();
				xhr.open("POST", formobj.getAttribute("action") + "&handlekey=" + formobj.getAttribute("id") + "&inajax=1", true);
				xhr.onload = function() {
					if (xhr.status >= 200 && xhr.status < 300) {
						var s = xhr.responseXML;
						if (typeof(s) == undefined) {
							popup.open("数据返回异常，无法完成您的请求", "alert");
						} else {
							popup.open(s.lastChild.firstChild.nodeValue);
							evalscript(s.lastChild.firstChild.nodeValue);
						}
					} else {
						popup.open("表单提交异常，无法完成您的请求", "alert");
					}
				};
				xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
				xhr.send(isFormData ? new FormData(formobj[0]) : new URLSearchParams(new FormData(formobj)).toString());
			}
		});
	}
};

var DISMENU = new Object;
var display = {
	init : function() {
		var $this = this;
		var nodes = qSelA('.display');
		for (var i = 0; i < nodes.length; i++) {
			var obj = nodes[i];
			var dis = qSel(obj.getAttribute('href'));
			if (dis && dis.hasAttribute('display')) {
				dis.style.display = 'none';
				dis.style.zIndex = '102';
				DISMENU[dis.getAttribute('id')] = dis;
				obj.addEventListener('click', function(e) {
					if (['A', 'IMG', 'INPUT'].indexOf(e.target.tagName) !== -1) return;
					$this.maskinit();
					if (dis.getAttribute('display') == 'true') {
						dis.style.display = 'block';
						dis.setAttribute('display', 'false');
						getID('mask').style = 'display:block;width:100%;height:100%;position:fixed;top:0;left:0;background:transparent;z-index:100;';
					}
					return false;
				});
			}
		}
	},
	maskinit : function() {
		var $this = this;
		getID('mask').addEventListener('touchstart', function() {
			$this.hide();
		});
	},
	hide : function() {
		getID('mask').style.display = 'none';
		for (var key in DISMENU) {
			DISMENU[key].style.display = 'none';
			DISMENU[key].setAttribute('display', 'true');
		}
	}
};

var displayswiper = {
	init : function() {
		['#dhnav_li', '#dhnavs_li'].forEach(function(nav) {
			if (document.querySelector(nav) != null) {
				if (document.querySelector(nav + ' .mon') != null) {
					var mon = document.querySelector(nav + ' .mon');
					var offsetLeft = mon.offsetLeft;
					var width = mon.offsetWidth;
					var windowWidth = window.innerWidth;
					var discuz_nav = offsetLeft + width >= windowWidth ? mon.getAttribute('data-index') : 0;
				} else {
					var discuz_nav = 0;
				}
				mySwiper = new Swiper(nav, {
					freeMode: true,
					slidesPerView: 'auto',
					initialSlide: discuz_nav,
					onTouchMove: function(swiper) {
						Discuz_Touch_on = 0;
					},
					onTouchEnd: function(swiper) {
						Discuz_Touch_on = 1;
					},
				});
			}
		});
	}
}

function getID(id) {
	return !id ? null : document.getElementById(id);
}

function qSel(sel) {
	return document.querySelector(sel);
}

function qSelA(sel) {
	return document.querySelectorAll(sel);
}

function mygetnativeevent(event) {

	while(event && typeof event.originalEvent !== "undefined") {
		event = event.originalEvent;
	}
	return event;
}

function evalscript(s) {
	if(s.indexOf('<script') == -1) return s;
	var p = /<script[^\>]*?>([^\x00]*?)<\/script>/ig;
	var arr = [];
	while(arr = p.exec(s)) {
		var p1 = /<script[^\>]*?src=\"([^\>]*?)\"[^\>]*?(reload=\"1\")?(?:charset=\"([\w\-]+?)\")?><\/script>/i;
		var arr1 = [];
		arr1 = p1.exec(arr[0]);
		if(arr1) {
			appendscript(arr1[1], '', arr1[2], arr1[3]);
		} else {
			p1 = /<script(.*?)>([^\x00]+?)<\/script>/i;
			arr1 = p1.exec(arr[0]);
			appendscript('', arr1[2], arr1[1].indexOf('reload=') != -1);
		}
	}
	return s;
}

var safescripts = {}, evalscripts = [];

function appendscript(src, text, reload, charset) {
	var id = hash(src + text);
	if(!reload && in_array(id, evalscripts)) return;
	if(reload && getID(id)) {
		getID(id).parentNode.removeChild(getID(id));
	}

	evalscripts.push(id);
	var scriptNode = document.createElement("script");
	scriptNode.type = "text/javascript";
	scriptNode.id = id;
	scriptNode.charset = charset ? charset : (!document.charset ? document.characterSet : document.charset);
	try {
		if(src) {
			scriptNode.src = src;
			scriptNode.onloadDone = false;
			scriptNode.onload = function () {
				scriptNode.onloadDone = true;
				JSLOADED[src] = 1;
			};
			scriptNode.onreadystatechange = function () {
				if((scriptNode.readyState == 'loaded' || scriptNode.readyState == 'complete') && !scriptNode.onloadDone) {
					scriptNode.onloadDone = true;
					JSLOADED[src] = 1;
				}
			};
		} else if(text){
			scriptNode.text = text;
		}
		document.getElementsByTagName('head')[0].appendChild(scriptNode);
	} catch(e) {}
}

function hash(string, length) {
	var length = length ? length : 32;
	var start = 0;
	var i = 0;
	var result = '';
	filllen = length - string.length % length;
	for(i = 0; i < filllen; i++){
		string += "0";
	}
	while(start < string.length) {
		result = stringxor(result, string.substr(start, length));
		start += length;
	}
	return result;
}

function stringxor(s1, s2) {
	var s = '';
	var hash = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	var max = Math.max(s1.length, s2.length);
	for(var i=0; i<max; i++) {
		var k = s1.charCodeAt(i) ^ s2.charCodeAt(i);
		s += hash.charAt(k % 52);
	}
	return s;
}

function in_array(needle, haystack) {
	if(typeof needle == 'string' || typeof needle == 'number') {
		for(var i in haystack) {
			if(haystack[i] == needle) {
					return true;
			}
		}
	}
	return false;
}

function isUndefined(variable) {
	return typeof variable == 'undefined' ? true : false;
}

function setcookie(cookieName, cookieValue, seconds, path, domain, secure) {
	if(cookieValue == '' || seconds < 0) {
		cookieValue = '';
		seconds = -2592000;
	}
	if(seconds) {
		var expires = new Date();
		expires.setTime(expires.getTime() + seconds * 1000);
	}
	domain = !domain ? cookiedomain : domain;
	path = !path ? cookiepath : path;
	document.cookie = escape(cookiepre + cookieName) + '=' + escape(cookieValue)
		+ (expires ? '; expires=' + expires.toGMTString() : '')
		+ (path ? '; path=' + path : '/')
		+ (domain ? '; domain=' + domain : '')
		+ (secure ? '; secure' : '');
}

function getcookie(name, nounescape) {
	name = cookiepre + name;
	var cookie_start = document.cookie.indexOf(name);
	var cookie_end = document.cookie.indexOf(";", cookie_start);
	if(cookie_start == -1) {
		return '';
	} else {
		var v = document.cookie.substring(cookie_start + name.length + 1, (cookie_end > cookie_start ? cookie_end : document.cookie.length));
		return !nounescape ? unescape(v) : v;
	}
}

function browserVersion(types) {
	var other = 1;
	for(i in types) {
		var v = types[i] ? types[i] : i;
		if(USERAGENT.indexOf(v) != -1) {
			var re = new RegExp(v + '(\\/|\\s|:)([\\d\\.]+)', 'ig');
			var matches = re.exec(USERAGENT);
			var ver = matches != null ? matches[2] : 0;
			other = ver !== 0 && v != 'mozilla' ? 0 : other;
		} else {
			var ver = 0;
		}
		eval('BROWSER.' + i + '= ver');
	}
	BROWSER.other = other;
}

function AC_FL_RunContent() {
	return '';
}

function appendstyle(url) {
	var link = document.createElement('link');
	link.type = 'text/css';
	link.rel = 'stylesheet';
	link.href = url;
	var head = document.getElementsByTagName('head')[0];
	head.appendChild(link);
}

function detectHtml5Support() {
	return document.createElement("Canvas").getContext;
}

function detectPlayer(randomid, ext, src, width, height) {
	var h5_support = new Array('aac', 'flac', 'mp3', 'm4a', 'wav', 'flv', 'mp4', 'm4v', '3gp', 'ogv', 'ogg', 'weba', 'webm');
	if (in_array(ext, h5_support) && detectHtml5Support()) {
		html5Player(randomid, ext, src, width, height);
	} else {
		document.getElementById(randomid).style.width = '100%';
		document.getElementById(randomid).style.height = height + 'px';
	}
}

function html5Player(randomid, ext, src, width, height) {
	switch (ext) {
		case 'aac':
		case 'flac':
		case 'mp3':
		case 'm4a':
		case 'wav':
		case 'ogg':
			height = 66;
			if(!HTML5PLAYER['apload']) {
				appendstyle(STATICURL + 'js/player/aplayer.min.css');
				appendscript(STATICURL + 'js/player/aplayer.min.js');
				HTML5PLAYER['apload'] = 1;
			}
			html5APlayer(randomid, ext, src, width, height);
			break;
		case 'flv':
			if(!HTML5PLAYER['flvload']) {
				appendscript(STATICURL + 'js/player/flv.min.js');
				HTML5PLAYER['flvload'] = 1;
			}
		case 'mp4':
		case 'm4v':
		case '3gp':
		case 'ogv':
		case 'webm':
			if(!HTML5PLAYER['dpload']) {
				appendstyle(STATICURL + 'js/player/dplayer.min.css');
				appendscript(STATICURL + 'js/player/dplayer.min.js');
				HTML5PLAYER['dpload'] = 1;
			}
			html5DPlayer(randomid, ext, src, width, height);
			break;
		default:
			break;
	}
	document.getElementById(randomid).style.width = '100%';
}

function html5APlayer(randomid, ext, src, width, height) {
	if (JSLOADED[STATICURL + 'js/player/aplayer.min.js']) {
		window[randomid] = new APlayer({
			container: document.getElementById(randomid + '_container'),
			mini: false,
			autoplay: false,
			loop: 'all',
			preload: 'none',
			volume: 1,
			mutex: true,
			listFolded: true,
			audio: [{
				name: ' ',
				artist: ' ',
				url: src,
			}]
		});
	} else {
		setTimeout(function () {
			html5APlayer(randomid, ext, src, width, height);
		}, 50);
	}
}

function html5DPlayer(randomid, ext, src, width, height) {
	if (JSLOADED[STATICURL + 'js/player/dplayer.min.js'] && (ext != 'flv' || JSLOADED[STATICURL + 'js/player/flv.min.js'])) {
		window[randomid] = new DPlayer({
			container: document.getElementById(randomid + '_container'),
			autoplay: false,
			loop: true,
			screenshot: false,
			hotkey: true,
			preload: 'none',
			volume: 1,
			mutex: true,
			listFolded: true,
			video: {
				url: src,
			}
		});
	} else {
		setTimeout(function () {
			html5DPlayer(randomid, ext, src, width, height);
		}, 50);
	}
}

document.addEventListener("DOMContentLoaded", function() {
	if(qSel('div.pg')) {
		page.converthtml();
	}
	if(qSel('.scrolltop')) {
		scrolltop.init(qSel('.scrolltop'));
	}
	if(qSelA('img').length > 0) {
		img.init(1);
	}
	if(qSelA('.popup').length > 0) {
		popup.init();
	}
	if(qSelA('.display').length > 0) {
		display.init();
	}
	dialog.init();
	formdialog.init();
	displayswiper.init();
});

function ajaxget(url, showid, waitid, loading, display, recall) {
	var xhr = new XMLHttpRequest();
	xhr.open("GET", url + '&inajax=1&ajaxtarget=' + showid);
	xhr.onreadystatechange = function() {
		if (xhr.readyState === 4 && xhr.status === 200) {
			getID(showid).innerHTML = xhr.responseText;
			var target = qSelA("[ajaxtarget]");
			for (var i = 0; i < target.length; i++) {
				target[i].addEventListener("click", function(e) {
					e.preventDefault();
					ajaxget(this.href, this.getAttribute("ajaxtarget"));
				});
			}
		}
	};
	xhr.send();
}

function getHost(url) {
	var host = "null";
	if(typeof url == "undefined"|| null == url) {
		url = window.location.href;
	}
	var regex = /^\w+\:\/\/([^\/]*).*/;
	var match = url.match(regex);
	if(typeof match != "undefined" && null != match) {
		host = match[1];
	}
	return host;
}

function hostconvert(url) {
	if(!url.match(/^https?:\/\//)) url = SITEURL + url;
	var url_host = getHost(url);
	var cur_host = getHost().toLowerCase();
	if(url_host && cur_host != url_host) {
		url = url.replace(url_host, cur_host);
	}
	return url;
}

function Ajax(recvType, waitId) {
	var aj = new Object();
	aj.loading = '请稍候...';
	aj.recvType = recvType ? recvType : 'XML';
	aj.waitId = waitId ? $(waitId) : null;
	aj.resultHandle = null;
	aj.sendString = '';
	aj.targetUrl = '';
	aj.setLoading = function(loading) {
		if(typeof loading !== 'undefined' && loading !== null) aj.loading = loading;
	};
	aj.setRecvType = function(recvtype) {
		aj.recvType = recvtype;
	};
	aj.setWaitId = function(waitid) {
		aj.waitId = typeof waitid == 'object' ? waitid : $(waitid);
	};
	aj.createXMLHttpRequest = function() {
		var request = new XMLHttpRequest();
		if(request.overrideMimeType) {
			request.overrideMimeType('text/xml');
		}
		return request;
	};
	aj.XMLHttpRequest = aj.createXMLHttpRequest();
	aj.showLoading = function() {
		if(aj.waitId && (aj.XMLHttpRequest.readyState != 4 || aj.XMLHttpRequest.status != 200)) {
			aj.waitId.style.display = '';
			aj.waitId.innerHTML = '<span><div class="loadicon vm"></div> ' + aj.loading + '</span>';
		}
	};
	aj.processHandle = function() {
		if(aj.XMLHttpRequest.readyState == 4 && aj.XMLHttpRequest.status == 200) {
			if(aj.waitId) {
				aj.waitId.style.display = 'none';
			}
			if(aj.recvType == 'HTML') {
				aj.resultHandle(aj.XMLHttpRequest.responseText, aj);
			} else if(aj.recvType == 'XML') {
				if(!aj.XMLHttpRequest.responseXML || !aj.XMLHttpRequest.responseXML.lastChild || aj.XMLHttpRequest.responseXML.lastChild.localName == 'parsererror') {
					aj.resultHandle('' , aj);
				} else {
					aj.resultHandle(aj.XMLHttpRequest.responseXML.lastChild.firstChild.nodeValue, aj);
				}
			} else if(aj.recvType == 'JSON') {
				var s = null;
				try {
					s = (new Function("return ("+aj.XMLHttpRequest.responseText+")"))();
				} catch (e) {
					s = null;
				}
				aj.resultHandle(s, aj);
			}
		}
	};
	aj.get = function(targetUrl, resultHandle) {
		targetUrl = hostconvert(targetUrl);
		setTimeout(function(){aj.showLoading()}, 250);
		aj.targetUrl = targetUrl;
		aj.XMLHttpRequest.onreadystatechange = aj.processHandle;
		aj.resultHandle = resultHandle;
		var attackevasive = isUndefined(attackevasive) ? 0 : attackevasive;
		if(window.XMLHttpRequest) {
			aj.XMLHttpRequest.open('GET', aj.targetUrl);
			aj.XMLHttpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			aj.XMLHttpRequest.send(null);
		} else {
			aj.XMLHttpRequest.open("GET", targetUrl, true);
			aj.XMLHttpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			aj.XMLHttpRequest.send();
		}
	};
	aj.post = function(targetUrl, sendString, resultHandle) {
		targetUrl = hostconvert(targetUrl);
		setTimeout(function(){aj.showLoading()}, 250);
		aj.targetUrl = targetUrl;
		aj.sendString = sendString;
		aj.XMLHttpRequest.onreadystatechange = aj.processHandle;
		aj.resultHandle = resultHandle;
		aj.XMLHttpRequest.open('POST', targetUrl);
		aj.XMLHttpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		aj.XMLHttpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		aj.XMLHttpRequest.send(aj.sendString);
	};
	aj.getJSON = function(targetUrl, resultHandle) {
		aj.setRecvType('JSON');
		aj.get(targetUrl+'&ajaxdata=json', resultHandle);
	};
	aj.getHTML = function(targetUrl, resultHandle) {
		aj.setRecvType('HTML');
		aj.get(targetUrl+'&ajaxdata=html', resultHandle);
	};
	return aj;
}

function portal_flowlazyload() {
	var obj = this;
	var times = 0;
	var processing = false;
	this.getOffset = function (el, isLeft) {
		var retValue = 0 ;
		while (el != null) {
			retValue += el["offset" + (isLeft ? "Left" : "Top" )];
			el = el.offsetParent;
		}
		return retValue;
	};
	this.attachEvent = function (obj, evt, func, eventobj) {
		eventobj = !eventobj ? obj : eventobj;
		if(obj.addEventListener) {
			obj.addEventListener(evt, func, false);
		} else if(eventobj.attachEvent) {
			obj.attachEvent('on' + evt, func);
		}
	};
	this.removeElement = function (_element) {
		var _parentElement = _element.parentNode;
		if(_parentElement) {
			_parentElement.removeChild(_element);
		}
	};
	this.showNextPage = function() {
		var scrollTop = Math.max(document.documentElement.scrollTop, document.body.scrollTop);
		var offsetTop = this.getOffset(document.getElementsByClassName('page')[0]);
		// 没有在进行的 Ajax 翻页或者 Ajax 翻页少于 10 次才翻页, 为了避免重复请求以及无限下拉导致的 DOM 问题
		// Todo: 大数据量站点测试下拉刷新合理范围, 适度放宽限制
		if (!processing && times <= 9 && offsetTop > document.documentElement.clientHeight && (offsetTop - scrollTop < document.documentElement.clientHeight)) {
			processing = true;
			times++;
			var x = new Ajax();
			x.get('portal.php?mod=index&page=' + ++flowpage + '&inajax=1', function(s) {
				if(s.indexOf(mobnodata) !== -1) {
					var infoli = s.match(/<li>([\w\W]+)<\/li>/g);
					var pgdiv = s.match(/<div class="pg">([\w\W]+)<\/div>/g);
					if (infoli !== null && pgdiv !== null) {
						document.getElementsByClassName('wzlist')[0].insertAdjacentHTML('beforeend', infoli);
						document.getElementsByClassName('page')[0].insertAdjacentHTML('afterend', pgdiv);
						obj.removeElement(document.getElementsByClassName('page')[0]);
						page.converthtml();
						processing = false;
					}
				}
			});
		}
	};
	this.attachEvent(window, 'scroll', function(){obj.showNextPage();});
}

function explode(sep, string) {
	return string.split(sep);
}

function setCopy(text, msg) {
	var cp = document.createElement('textarea');
	cp.style.fontSize = '12pt';
	cp.style.border = '0';
	cp.style.padding = '0';
	cp.style.margin = '0';
	cp.style.position = 'absolute';
	cp.style.left = '-9999px';
	var yPosition = window.pageYOffset || document.documentElement.scrollTop;
	cp.style.top = yPosition + 'px';
	cp.setAttribute('readonly', '');
	text = text.replace(/[\xA0]/g, ' ');
	cp.value = text;
	document.getElementById('append_parent').appendChild(cp);
	cp.select();
	cp.setSelectionRange(0, cp.value.length);
	try {
		var success = document.execCommand('copy', false, null);
	} catch(e) {
		var success = false;
	}
	document.getElementById('append_parent').removeChild(cp);

	if (success) {
		if (msg) {
			popup.open(msg, 'alert');
		}
	} else if (BROWSER.ie) {
		var r = clipboardData.setData('Text', text);
		if (r) {
			if (msg) {
				popup.open(msg, 'alert');
			}
		} else {
			popup.open('复制失败', 'alerts');
		}
	} else {
		popup.open('复制失败', 'alerts');
	}
}