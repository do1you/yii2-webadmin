// load box
(function(){
	if(window.isLoading || window.isLoading===undefined){
		var c = function(el,obj){
			for(var i in obj) el.style[i] = obj[i];
		},loadObj = setInterval(function(){
			if(document.body && document.body.appendChild){
				var newNode = document.createElement("div");
				newNode.className = 'loadingBox';
				c(newNode,{
					position : 'absolute',left:'0px',top:'0px',width:'100%',height:'100%',backgroundColor:'#ffffff',zIndex:'999999'
				});
				document.body.appendChild(newNode);
				clearInterval(loadObj);
			}
		},5);
	}
})();

// load js async
(function(global, undefined) {
	if (global.pui){ return; }
	var pui = global.pui = {version:"1.0.0"};
	var data = pui.data = {};

	function isType(type) {
		return function(obj) {
			return ({}.toString.call(obj) == "[object " + type + "]");
		};
	}

	var isObject = isType("Object");
	var isString = isType("String");
	var isArray = Array.isArray || isType("Array");
	var isFunction = isType("Function");

	var _cid = 0;
	function cid() {
		return _cid++;
	}

	var events = data.events = {};
	pui.on = function(name, callback) {
		var list = events[name] || (events[name] = []);
		list.push(callback);
		return pui;
	};
	pui.off = function(name, callback) {
		if (!(name || callback)) {
			events = data.events = {};
			return pui;
		}
		var list = events[name];
		if(list){
			if (callback) {
				for (var i = list.length - 1; i >= 0; i--) {
					if (list[i] === callback) {
						list.splice(i, 1);
					}
				}
			}else{
				delete events[name];
			}
		}
		return pui;
	};

	var emit = pui.emit = function(name, data) {
		var list = events[name];
		if (list) {
			list = list.slice();
			for(var i = 0, len = list.length; i < len; i++) {
				list[i](data);
			}
		}
		return pui;
	};

	var DIRNAME_RE = new RegExp('[^?#]*\/');
	var DOT_RE = new RegExp('\/\.\/','g');
	var DOUBLE_DOT_RE = new RegExp('\/[^/]+\/\.\.\/');
	var MULTI_SLASH_RE = new RegExp('([^:/])\/+\/','g');
	function dirname(path) {
		return path.match(DIRNAME_RE)[0];
	}

	function realpath(path) {
		path = path.replace(DOT_RE, "/");
		path = path.replace(MULTI_SLASH_RE, "$1/");
		while(path.match(DOUBLE_DOT_RE)) {
			path = path.replace(DOUBLE_DOT_RE, "/");
		}
		return path;
	}

	function normalize(path) {
		var last = path.length - 1;
		var lastC = path.charCodeAt(last);
		if (lastC === 35) {
			return path.substring(0, last);
		}
		return ((path.substring(last - 2) === ".js" || path.indexOf("?") > 0 || lastC === 47) ? path : path + ".js");
	}


	var PATHS_RE = new RegExp('^([^/:]+)(\/.+)$');
	var VARS_RE = new RegExp('{([^{]+)}','g');
	function parseAlias(id) {
		var alias = data.alias;
		return (alias && isString(alias[id]) ? alias[id] : id);
	}

	function parsePaths(id) {
		var m,paths = data.paths;
		if (paths && (m = id.match(PATHS_RE)) && isString(paths[m[1]])){
			id = paths[m[1]] + m[2];
		}
		return id;
	}

	function parseVars(id) {
		var vars = data.vars;
		if (vars && id.indexOf("{") > -1) {
			id = id.replace(VARS_RE, function(m, key) {
				return (isString(vars[key]) ? vars[key] : m);
			});
		}
		return id;
	}

	function parseMap(uri) {
		var map = data.map;
		var ret = uri;
		if (map) {
			for (var i = 0, len = map.length; i < len; i++) {
				var rule = map[i];
				ret = isFunction(rule) ?
						(rule(uri) || uri) :
						uri.replace(rule[0], rule[1]);
			  if (ret !== uri) break;
			}
		}
		return ret;
	}


	var ABSOLUTE_RE = new RegExp('^\/\/.|:\/');
	var ROOT_DIR_RE = new RegExp('^.*?\/\/.*?\/');
	function addBase(id, refUri) {
		var ret,first = id.charCodeAt(0);
		if (ABSOLUTE_RE.test(id)) {
			ret = id;
		}else if (first === 46) {
			ret = (refUri ? dirname(refUri) : data.cwd) + id;
		}else if (first === 47) {
			var m = data.cwd.match(ROOT_DIR_RE);
			ret = m ? m[0] + id.substring(1) : id;
		}else{
			ret = data.base + id;
		}
		if (ret.indexOf("//") === 0) {
			ret = location.protocol + ret;
		}
		return realpath(ret);
	}

	function id2Uri(id, refUri) {
		if (!id) return "";
		id = parseAlias(id);
		id = parsePaths(id);
		id = parseAlias(id);
		id = parseVars(id);
		id = parseAlias(id);
		id = normalize(id);
		id = parseAlias(id);
		var uri = addBase(id, refUri);
		uri = parseAlias(uri);
		uri = parseMap(uri);
		return uri;
	}
	pui.resolve = id2Uri;
	var loaderDir,loaderPath;
	var isWebWorker = typeof window === 'undefined' && typeof importScripts !== 'undefined' && isFunction(importScripts);
	var IGNORE_LOCATION_RE = new RegExp('^(about|blob):');
	var cwd = (!location.href || IGNORE_LOCATION_RE.test(location.href)) ? '' : dirname(location.href);

	if (isWebWorker){
		var stack;
		try {
			var up = new Error();
			throw up;
		} catch (e) {
			stack = e.stack.split('\n');
		}
		stack.shift();
		var m;
		var TRACE_RE = new RegExp('.*?((?:http|https|file)(?::\/{2}[\w]+)(?:[\/|\.]?)(?:[^\s"]*)).*?','i');
		var URL_RE = new RegExp('(.*?):\d+:\d+\)?$');
		while (stack.length > 0) {
			var top = stack.shift();
			m = TRACE_RE.exec(top);
			if (m != null) {
				break;
			}
		}
		var url;
		if (m != null) {
			var url = URL_RE.exec(m[1])[1];
		}
		loaderPath = url;
		loaderDir = dirname(url || cwd);
		if (cwd === '') {
			cwd = loaderDir;
		}
	}else {
		var doc = document;
		var scripts = doc.scripts;
		var loaderScript = doc.getElementById("puinode") || scripts[scripts.length - 1];
		function getScriptAbsoluteSrc(node) {
			return (node.hasAttribute ? node.src : node.getAttribute("src", 4));
		}
		loaderPath = getScriptAbsoluteSrc(loaderScript);
		loaderDir = dirname(loaderPath || cwd);
	}

	if (isWebWorker) {
		function requestFromWebWorker(url, callback, charset) {
			var error;
			try {
				importScripts(url);
			} catch (e) {
				error = e;
			}
			callback(error);
		}
		pui.request = requestFromWebWorker;
	}else{
		var doc = document;
		var head = doc.head || doc.getElementsByTagName("head")[0] || doc.documentElement;
		var baseElement = head.getElementsByTagName("base")[0];
		var currentlyAddingScript;
		function request(url, callback, charset) {
			var node = doc.createElement("script");
			if (charset) {
				var cs = isFunction(charset) ? charset(url) : charset;
				if (cs) {
					node.charset = cs;
				}
			}
			addOnload(node, callback, url);
			node.async = true;
			node.src = url;
			currentlyAddingScript = node;
			baseElement ? head.insertBefore(node, baseElement) : head.appendChild(node);
			currentlyAddingScript = null;
		}

		function addOnload(node, callback, url){
			var supportOnload = ("onload" in node);
			if (supportOnload){
				node.onload = onload;
				node.onerror = function(){
					emit("error", { uri: url, node: node });
					onload(true);
				};
			}else{
				node.onreadystatechange = function(){
					if ((new RegExp('loaded|complete')).test(node.readyState)) {
						onload();
					}
				};
			}

			function onload(error) {
				node.onload = node.onerror = node.onreadystatechange = null;
				if (!data.debug) {
					head.removeChild(node);
				}
				node = null;
				callback(error);
			}
		}
		pui.request = request;
	}
	var interactiveScript;
	function getCurrentScript() {
		if (currentlyAddingScript) {
			return currentlyAddingScript;
		}
		if (interactiveScript && interactiveScript.readyState === "interactive") {
			return interactiveScript;
		}
		var scripts = head.getElementsByTagName("script");
		for (var i = scripts.length - 1; i >= 0; i--) {
			var script = scripts[i];
			if (script.readyState === "interactive") {
				interactiveScript = script;
				return interactiveScript;
			}
		}
	}
	function parseDependencies(s) {
		if(s.indexOf('require') == -1) {
			return [];
		}
		var index = 0, peek, length = s.length, isReg = 1, modName = 0, parentheseState = 0, parentheseStack = [], res = [];
		while(index < length) {
			readch();
			if(isBlank()){
			}else if(isQuote()) {
				dealQuote();
				isReg = 1;
			}else if(peek == '/') {
				readch();
				if(peek == '/'){
					index = s.indexOf('\n', index);
					if(index == -1){
						index = s.length;
					}
				}else if(peek == '*') {
					index = s.indexOf('*/', index);
					if(index == -1) {
						index = length;
					}else {
						index += 2;
					}
				}else if(isReg) {
					dealReg();
					isReg = 0;
				}else {
					index--;
					isReg = 1;
				}
			}else if(isWord()) {
				dealWord();
			}else if(isNumber()) {
				dealNumber();
			}else if(peek == '(') {
				parentheseStack.push(parentheseState);
				isReg = 1;
			}else if(peek == ')') {
				isReg = parentheseStack.pop();
			}else {
				isReg = peek != ']';
				modName = 0;
			}
		}
		return res;
		function readch() {
			peek = s.charAt(index++);
		}
		function isBlank() {
			return (new RegExp('\s')).test(peek);
		}
		function isQuote() {
			return (peek == '"' || peek == "'");
		}
		function dealQuote(){
			var start = index;
			var c = peek;
			var end = s.indexOf(c, start);
			if(end == -1) {
				index = length;
			}else if(s.charAt(end - 1) != '\\') {
				index = end + 1;
			}else{
				while(index < length) {
					readch();
					if(peek == '\\'){
					  index++;
					}else if(peek == c) {
						break;
					}
				}
			}
			if(modName) {
				res.push(s.slice(start, index - 1));
				modName = 0;
			}
		}
		function dealReg() {
			index--;
			while(index < length) {
				readch();
				if(peek == '\\') {
					index++;
				}else if(peek == '/') {
					break;
				}else if(peek == '[') {
					while(index < length) {
						readch();
						  if(peek == '\\') {
							index++;
						  }else if(peek == ']') {
							break;
						}
					}
				}
			}
		}
		function isWord() {
			return new RegExp('[a-z_$]','i').test(peek);
		}
		function dealWord() {
			var s2 = s.slice(index - 1);
			var r = (new RegExp('^[\w$]+')).exec(s2)[0];
			parentheseState = {
				'if': 1,
				'for': 1,
				'while': 1,
				'with': 1
			}[r];
			isReg = {
				'break': 1,
				'case': 1,
				'continue': 1,
				'debugger': 1,
				'delete': 1,
				'do': 1,
				'else': 1,
				'false': 1,
				'if': 1,
				'in': 1,
				'instanceof': 1,
				'return': 1,
				'typeof': 1,
				'void': 1
			}[r];
			modName = (new RegExp('^require\s*\(\s*([\'"]).+?\1\s*\)')).test(s2);
			if(modName) {
				r = (new RegExp('^require\s*\(\s*[\'"]')).exec(s2)[0];
				index += r.length - 2;
			}else{
				index += new RegExp('^[\w$]+(?:\s*\.\s*[\w$]+)*').exec(s2)[0].length - 1;
			}
		}
		function isNumber() {
			return ((new RegExp('\d')).test(peek) || peek == '.' && (new RegExp('\d')).test(s.charAt(index)));
		}
		function dealNumber() {
			var s2 = s.slice(index - 1);
			var r;
			if(peek == '.') {
				 r = (new RegExp('^\.\d+(?:E[+-]?\d*)?\s*','i')).exec(s2)[0];
			}else if((new RegExp('^0x[\da-f]*','i')).test(s2)) {
				r = (new RegExp('^0x[\da-f]*\s*','i')).exec(s2)[0];
			}else {
				r = (new RegExp('^\d+\.?\d*(?:E[+-]?\d*)?\s*','i')).exec(s2)[0];
			}
			index += r.length - 1;
			isReg = 0;
		}
	}

	var cachedMods = pui.cache = {};
	var anonymousMeta, fetchingList = {}, fetchedList = {}, callbackList = {};
	var STATUS = Module.STATUS = {
		FETCHING: 1,
		SAVED: 2,
		LOADING: 3,
		LOADED: 4,
		EXECUTING: 5,
		EXECUTED: 6,
		ERROR: 7
	};
	function Module(uri, deps) {
		this.uri = uri;
		this.dependencies = deps || [];
		this.deps = {};
		this.status = 0;
		this._entry = [];
	}
	Module.prototype.resolve = function() {
		var mod = this;
		var ids = mod.dependencies;
		var uris = [];
		for (var i = 0, len = ids.length; i < len; i++) {
			uris[i] = Module.resolve(ids[i], mod.uri);
		}
		return uris;
	};

	Module.prototype.pass = function() {
		var mod = this;
		var len = mod.dependencies.length;
		for (var i = 0; i < mod._entry.length; i++) {
			var entry = mod._entry[i];
			var count = 0;
			for (var j = 0; j < len; j++) {
				var m = mod.deps[mod.dependencies[j]];
				if (m.status < STATUS.LOADED && !entry.history.hasOwnProperty(m.uri)) {
					entry.history[m.uri] = true;
					count++;
					m._entry.push(entry);
					if(m.status === STATUS.LOADING) {
						m.pass();
					}
				}
			}
			if (count > 0) {
				entry.remain += count - 1;
				mod._entry.shift();
				i--;
			}
		}
	};
	Module.prototype.load = function() {
		var mod = this;
		if (mod.status >= STATUS.LOADING) {
			return;
		}
		mod.status = STATUS.LOADING;
		var uris = mod.resolve();
		emit("load", uris);
		for (var i = 0, len = uris.length; i < len; i++) {
			mod.deps[mod.dependencies[i]] = Module.get(uris[i]);
		}
		mod.pass();
		if (mod._entry.length) {
			mod.onload();
			return;
		}
		var m, requestCache = {};
		for (i = 0; i < len; i++) {
			m = cachedMods[uris[i]];
			if (m.status < STATUS.FETCHING) {
				m.fetch(requestCache);
			}else if (m.status === STATUS.SAVED) {
				m.load();
			}
		}

		for (var requestUri in requestCache) {
			if (requestCache.hasOwnProperty(requestUri)) {
				requestCache[requestUri]();
			}
		}
	};
	Module.prototype.onload = function() {
		var mod = this;
		mod.status = STATUS.LOADED;
		for (var i = 0, len = (mod._entry || []).length; i < len; i++) {
			var entry = mod._entry[i];
			if (--entry.remain === 0) {
			  entry.callback();
			}
		}
		delete mod._entry;
	};
	Module.prototype.error = function() {
		var mod = this;
		mod.onload();
		mod.status = STATUS.ERROR;
	};
	Module.prototype.exec = function () {
		var mod = this;
		if (mod.status >= STATUS.EXECUTING) {
			return mod.exports;
		}
		mod.status = STATUS.EXECUTING;
		if (mod._entry && !mod._entry.length) {
			delete mod._entry;
		}
		if (!mod.hasOwnProperty('factory')) {
			mod.non = true;
			return;
		}
		var uri = mod.uri;
		function require(id) {
			var m = mod.deps[id] || Module.get(require.resolve(id));
			if (m.status == STATUS.ERROR) {
				throw new Error('module was broken: ' + m.uri);
			}
			return m.exec();
		}
		require.resolve = function(id) {
			return Module.resolve(id, uri);
		};
		require.async = function(ids, callback) {
			Module.use(ids, callback, uri + "_async_" + cid());
			return require;
		};
		var factory = mod.factory;
		var exports = isFunction(factory) ?
			factory(require, mod.exports = {}, mod) :
			factory;
		if (exports === undefined){
			exports = mod.exports;
		}
		delete mod.factory;
		mod.exports = exports;
		mod.status = STATUS.EXECUTED;
		emit("exec", mod);
		return mod.exports;
	};
	Module.prototype.fetch = function(requestCache) {
		var mod = this;
		var uri = mod.uri;
		mod.status = STATUS.FETCHING;
		var emitData = { uri: uri };
		emit("fetch", emitData);
		var requestUri = emitData.requestUri || uri;
		if (!requestUri || fetchedList.hasOwnProperty(requestUri)) {
			mod.load();
			return;
		}
		if (fetchingList.hasOwnProperty(requestUri)) {
			callbackList[requestUri].push(mod);
			return;
		}
		fetchingList[requestUri] = true;
		callbackList[requestUri] = [mod];
		emit("request", emitData = {
			uri: uri,
			requestUri: requestUri,
			onRequest: onRequest,
			charset: isFunction(data.charset) ? data.charset(requestUri) || 'utf-8' : data.charset
		});
		if (!emitData.requested) {
			requestCache ?
			  (requestCache[emitData.requestUri] = sendRequest) :
			  sendRequest();
		}
		function sendRequest() {
			pui.request(emitData.requestUri, emitData.onRequest, emitData.charset);
		}
		function onRequest(error) {
			delete fetchingList[requestUri];
			fetchedList[requestUri] = true;
			if (anonymousMeta) {
				Module.save(uri, anonymousMeta);
				anonymousMeta = null;
			}
			var m, mods = callbackList[requestUri];
			delete callbackList[requestUri];
			while ((m = mods.shift())) {
				if(error === true) {
					m.error();
				}else {
					m.load();
				}
			}
		}
	};

	Module.resolve = function(id, refUri) {
		var emitData = { id: id, refUri: refUri };
		emit("resolve", emitData);
		return (emitData.uri || pui.resolve(emitData.id, refUri));
	};

	Module.define = function (id, deps, factory) {
		var argsLen = arguments.length;
		if (argsLen === 1) {
			factory = id;
			id = undefined;
		}else if (argsLen === 2) {
			factory = deps;
			if (isArray(id)) {
			  deps = id;
			  id = undefined;
			} else {
			  deps = undefined;
			}
		}
		if (!isArray(deps) && isFunction(factory)) {
			deps = typeof parseDependencies === "undefined" ? [] : parseDependencies(factory.toString());
		}
		var meta = {
			id: id,
			uri: Module.resolve(id),
			deps: deps,
			factory: factory
		};
		if (!isWebWorker && !meta.uri && doc.attachEvent && typeof getCurrentScript !== "undefined") {
			var script = getCurrentScript();
			if (script) {
				meta.uri = script.src;
			}
		}
		emit("define", meta);
		meta.uri ? Module.save(meta.uri, meta) : (anonymousMeta = meta);
	};

	Module.save = function(uri, meta) {
		var mod = Module.get(uri);
		if (mod.status < STATUS.SAVED) {
			mod.id = meta.id || uri;
			mod.dependencies = meta.deps || [];
			mod.factory = meta.factory;
			mod.status = STATUS.SAVED;
			emit("save", mod);
		}
	};

	Module.get = function(uri, deps) {
		return cachedMods[uri] || (cachedMods[uri] = new Module(uri, deps))
	}

	Module.use = function (ids, callback, uri) {
		var mod = Module.get(uri, isArray(ids) ? ids : [ids]);
		mod._entry.push(mod);
		mod.history = {};
		mod.remain = 1;
		mod.callback = function() {
			var exports = [];
			var uris = mod.resolve();
			for (var i = 0, len = uris.length; i < len; i++) {
				exports[i] = cachedMods[uris[i]].exec();
			}
			if (callback) {
				callback.apply(global, exports);
			}
			delete mod.callback;
			delete mod.history;
			delete mod.remain;
			delete mod._entry;
		};
		mod.load()
	};

	pui.use = function(ids, callback) {
		Module.use(ids, callback, data.cwd + "_use_" + cid());
		return pui;
	};

	Module.define.cmd = {};
	global.define = Module.define;
	pui.Module = Module;
	data.fetchedList = fetchedList;
	data.cid = cid;
	pui.require = function(id) {
		var mod = Module.get(Module.resolve(id));
		if (mod.status < STATUS.EXECUTING) {
			mod.onload();
			mod.exec();
		}
		return mod.exports;
	};
	data.base = loaderDir;
	data.dir = loaderDir;
	data.loader = loaderPath;
	data.cwd = cwd;
	data.charset = "utf-8";
	pui.config = function(configData) {
		for (var key in configData) {
			var curr = configData[key];
			var prev = data[key];
			if (prev && isObject(prev)) {
				for (var k in curr) {
					prev[k] = curr[k];
				}
			}else {
				if (isArray(prev)) {
					curr = prev.concat(curr);
				}else if (key === "base") {
					if (curr.slice(-1) !== "/") {
						curr += "/";
					}
					curr = addBase(curr);
				}
				data[key] = curr;
			}
		}
		emit("config", configData);
		return pui;
	};
})(this);

// load css async
(function(){
	function isType(type) {
		return function(obj) {
			return ({}.toString.call(obj) == "[object " + type + "]");
		};
	}
	var isString = isType("String");
	var doc = document;
	var head = doc.head || doc.getElementsByTagName("head")[0] || doc.documentElement;
	var baseElement = head.getElementsByTagName("base")[0];
	var IS_CSS_RE = new RegExp('\.css(?:\\?|$)','i');
	var currentlyAddingScript;
	var interactiveScript;
	var isOldWebKit = +navigator.userAgent.replace((new RegExp('.*(?:AppleWebKit|AndroidWebKit)\/?(\d+).*','i')), "$1") < 536;
	function isFunction(obj) {
		return ({}.toString.call(obj) == "[object Function]");
	}
	function request(url, callback, charset, crossorigin) {
		var isCSS = IS_CSS_RE.test(url);
		var node = doc.createElement(isCSS ? "link" : "script");
		if (charset) {
			var cs = isFunction(charset) ? charset(url) : charset;
			if (cs) {
				node.charset = cs;
			}
		}
		if (crossorigin !== void 0) {
			node.setAttribute("crossorigin", crossorigin);
		}
		addOnload(node, callback, isCSS, url);
		if (isCSS) {
			node.rel = "stylesheet";
			node.href = url;
		}else {
			node.async = true;
			node.src = url;
		}
		currentlyAddingScript = node;
		baseElement ? head.insertBefore(node, baseElement) : head.appendChild(node);
		currentlyAddingScript = null;
	}
	function addOnload(node, callback, isCSS, url) {
		var supportOnload = ("onload" in node);
		if (isCSS && (isOldWebKit || !supportOnload)) {
			setTimeout(function() {
				pollCss(node, callback)
			}, 1);
			return;
		}
		if (supportOnload) {
			node.onload = onload;
			node.onerror = function() {
				pui.emit("error", { uri: url, node: node });
				onload();
			};
		}else {
			node.onreadystatechange = function() {
				if ((new RegExp('loaded|complete')).test(node.readyState)) {
					onload();
				}
			};
		}
		function onload() {
			node.onload = node.onerror = node.onreadystatechange = null;
			if (!isCSS && !pui.data.debug) {
				head.removeChild(node);
			}
			node = null;
			callback();
		}
	}
	function pollCss(node, callback) {
		var sheet = node.sheet;
		var isLoaded;
		if (isOldWebKit) {
			if (sheet) {
				isLoaded = true;
			}
		}else if (sheet) {
			try {
				if (sheet.cssRules) {
					isLoaded = true;
				}
			} catch (ex) {
				if (ex.name === "NS_ERROR_DOM_SECURITY_ERR") {
					isLoaded = true;
				}
			}
		}
		setTimeout(function() {
			if (isLoaded) {
				callback();
			}else {
				pollCss(node, callback);
			}
		}, 20);
	}
	pui.request = request;
	var data = pui.data;
	var DIRNAME_RE = new RegExp('[^?#]*\/');
	var DOT_RE = new RegExp('\/\.\/','g');
	var DOUBLE_DOT_RE = new RegExp('\/[^/]+\/\.\.\/');
	var MULTI_SLASH_RE = new RegExp('([^:/])\/+\/','g');
	function dirname(path) {
		return path.match(DIRNAME_RE)[0];
	}
	function realpath(path) {
		path = path.replace(DOT_RE, "/");
		path = path.replace(MULTI_SLASH_RE, "$1/");
		while (path.match(DOUBLE_DOT_RE)) {
			path = path.replace(DOUBLE_DOT_RE, "/");
		}
		return path;
	}
	function normalize(path) {
		var last = path.length - 1;
		var lastC = path.charAt(last);
		if (lastC === "#") {
			return path.substring(0, last);
		}
		return ((path.substring(last - 2) === ".js" ||
			path.indexOf("?") > 0 ||
			path.substring(last - 3) === ".css" ||
			lastC === "/") ? path : path + ".js");
	}
	var PATHS_RE = new RegExp('^([^/:]+)(\/.+)$');
	var VARS_RE = new RegExp('{([^{]+)}','g');
	function parseAlias(id) {
		var alias = data.alias;
		return (alias && isString(alias[id]) ? alias[id] : id);
	}
	function parsePaths(id) {
		var m, paths = data.paths;
		if (paths && (m = id.match(PATHS_RE)) && isString(paths[m[1]])) {
			id = paths[m[1]] + m[2];
		}
		return id;
	}
	function parseVars(id) {
		var vars = data.vars;
		if (vars && id.indexOf("{") > -1) {
			id = id.replace(VARS_RE, function(m, key) {
				return isString(vars[key]) ? vars[key] : m
			});
		}
		return id;
	}
	function parseMap(uri) {
		var map = data.map;
		var ret = uri;
		if (map) {
			for (var i = 0, len = map.length; i < len; i++) {
				var rule = map[i];
				ret = isFunction(rule) ?
					(rule(uri) || uri) :
					uri.replace(rule[0], rule[1]);
				if (ret !== uri) break;
			}
		}
		return ret;
	}
	var ABSOLUTE_RE = new RegExp('^\/\/.|:\/');
	var ROOT_DIR_RE = new RegExp('^.*?\/\/.*?\/');
	function addBase(id, refUri) {
		var ret, first = id.charAt(0);
		if (ABSOLUTE_RE.test(id)) {
			ret = id;
		}else if (first === ".") {
			ret = realpath((refUri ? dirname(refUri) : data.cwd) + id);
		}else if (first === "/") {
			var m = data.cwd.match(ROOT_DIR_RE);
			ret = m ? m[0] + id.substring(1) : id;
		}else {
			ret = data.base + id;
		}
		if (ret.indexOf("//") === 0) {
			ret = location.protocol + ret;
		}
		return ret;
	}
	function id2Uri(id, refUri) {
		if (!id) return "";
		id = parseAlias(id);
		id = parsePaths(id);
		id = parseVars(id);
		id = normalize(id);
		var uri = addBase(id, refUri);
		uri = parseMap(uri)
		return uri;
	}
	var doc = document;
	var cwd = (!location.href || location.href.indexOf('about:') === 0) ? '' : dirname(location.href);
	var scripts = doc.scripts;
	var loaderScript = doc.getElementById("puinode") || scripts[scripts.length - 1];
	var loaderDir = dirname(getScriptAbsoluteSrc(loaderScript) || cwd);
	function getScriptAbsoluteSrc(node) {
		return (node.hasAttribute ? node.src : node.getAttribute("src", 4));
	}
	pui.resolve = id2Uri;
	define("pui/pui-css/1.0.5/pui-css-debug", [], {});
})();

// 初始化配置
(function(){
	var pui = window.pui || pui,
		basePath = (window.puiPath || pui.data.base); // 获取当前路径
	pui.version = '1.0.1'; // 版本
	pui.config({
		base : basePath, // 根目录
		charset: 'utf-8', // 设置编码
		paths : {
			'pui' : basePath+'plugins', // 模块目录
			'lib' : basePath+'vendors' // 库目录
		},
		alias:{ // 库缩写
			'kindeditor':'lib/kindeditor', // kindeditor
			'jquery':'lib/jquery' // jquery
		},
		map : [ // 脚本路径替换
			function(uri){
				var lastIndex = uri.lastIndexOf('/'),
					scriptName = uri.substring(lastIndex + 1);
				uri = uri.substring(0, lastIndex + 1)+scriptName.replace((new RegExp('.(js|css)$')),"/")+scriptName+'?v='+pui.version;
				return uri;
				//return uri.replace((new RegExp('.js$')), '.min.js'); // 是否使用压缩形式
			}
        ]
	});
	// 已加载的脚本 window.puiLoaded
	if(window.puiLoaded){
		var i,puiLoaded = ({}.toString.call(window.puiLoaded) == "[object Array]") ? window.puiLoaded : [];
		for(i=0;i<puiLoaded.length;i++){
			pui.Module.save(pui.resolve(puiLoaded[i]),{});
		}
	}
})();

// 页面初始化
window.onload = function(){
	var loadFn = function($){
		// 定义一些常用的基本控件
		// 多选框全选/反选
		$.fn.checker = function(options) {
			options=$.extend({
				parentDom : 'form:first', // 父级寻找checkbox
				findDom : 'input[type=checkbox]' // 匹配的checkbox
			},options);
			this.click(function(){
				var _this = $(this),
					els = _this.parents(options.parentDom).find(options.findDom),
					checked = !_this.attr('checked');
				els.add(_this).attr('checked',checked).trigger("change");
				return false;
			});
		};
		// 重置表单提交地址
		$.fn.resetaction = function(options) {
			if(typeof options=='string') options={"action":options};
			options=$.extend({},options);
			this.click(function(){
				$(this).parents('form:first').attr(options);
			});
		};
		// 表单至少选择一项多选框
		$.fn.mustchecked = function() {
			this.bind('submit',function(e){
				if(!e.isDefaultPrevented()){
					var els = $(this).find('input[name][type=checkbox]');
					if(els.length && els.filter(':checked').length<=0){
						alert('请先选择需要操作的记录！');
						e.preventDefault();
					}
				}
			});
		};
		// 再次确认操作
		$.fn.confirm = function(msg) {
			msg = msg||'确定要执行该操作吗?';
			this.each(function(){
				var t = $(this),
					events = $.extend(true,{},$._data(this,'events'));

				// 触发确认事件
				t.unbind('click').click(function(e){
					if(e.isDefaultPrevented() || !confirm(msg)){
						e.preventDefault();
						return false;
					}
					if(events && !$.isEmptyObject(events) && events.click){ // 触发其它事件
						var result,_this=this;
						$.each(events.click,function(index,evt){
							if(evt.handler.call(_this,e)===false){
								result = false;
							}
						});
						if(result===false){
							e.preventDefault();
						}
						return result;
					}
				});
			});
		};
		// 跳转元素
		$.fn.url = function() {
			this.click(function(e){
				location.href = $(this).att('url') || $(this).data('pui-url') || $(this).data('href') || '?';
				return false;
			});
		};
		// 切换标签(不含样式)
		$.fn.itabs = function(options) {
			options=$.extend({
				tabDom : '>ul>li:not(.tabs_link)', // 寻找标签元素
				divDom : '>div', // 寻找内容元素
				eventType : 'click', // 事件 click or mouseenter / mouseover
				tabClass : 'on' // 标签焦点样式
			},options);
			this.each(function(){
				var box = $(this),
					lis = box.find(options.tabDom),
					divs = box.find(options.divDom);
				lis.bind(options.eventType, function(e){
					if(!$(this).hasClass('tabs_link')){
						var index = lis.removeClass(options.tabClass).index(this);
						$(this).addClass(options.tabClass);
						divs.hide().eq(index).show();
						e.preventDefault();
					}
				});
				(lis.filter(options.tabClass).length ? lis.filter(options.tabClass) : lis.eq(0)).trigger(options.eventType);
			});
		};

		// 解析 html
		pui.parseHtml = function(box){
			box = box ? $(box) : $('body');
			box.find('*').andSelf().each(function(){
				var t = $(this),data = t.data(),parsed = t.attr('parsed');
				if(!data || parsed) return;
				$.each(data,function(name,value){
					if(name.substr(0,3)!='pui') return;
					name = name.substr(3).toLocaleLowerCase(); // 控件名称，只允许小写
					t.attr('parsed',1); // 定义元素为已解析过
					
					// 控件解析
					try{var opts = eval('('+(value||'{}')+')');}catch(e){opts=value;}
					opts = $.isEmptyObject(opts) ? (opts || undefined) : opts;
					if($.fn[name]){
						$.isArray(opts) ? t[name].apply(t,opts) : t[name](opts);
					}else{
						pui.use(['jquery','pui/'+name],function($,plug){
							if($.fn[name]){
								$.isArray(opts) ? t[name].apply(t,opts) : t[name](opts);
							}else if(plug && $.isFunction(plug)){
								$.isArray(opts) ? plug.apply(t[0],opts) : plug.call(t[0],opts);
							}
							$(window).resize();
						});
					}
				});
			});
			setTimeout(function(){$(window).resize();},10);
		};

		// 初始化
		$(function(){
			pui.parseHtml();
			if($.browser && $.browser.msie && ($.browser.version == "6.0") && !$.support.style) {  // ie6的PNG透明处理
				pui.use('lib/ie6png',function(U){ U('*'); });
			}
			
			// 初始元素删除
			var loadObj = setInterval(function(){
				if($('.loadingBox').length){
					$('.loadingBox').fadeOut('fast',function(){$('.loadingBox').remove();});
					clearInterval(loadObj);
				}
			},5);
		});
	};
	window.jQuery ? loadFn(window.jQuery) : pui.use('jquery',loadFn);
};