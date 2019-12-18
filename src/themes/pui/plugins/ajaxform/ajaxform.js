/**
 @Name：ajaxform V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-5-20 
 */
define('pui/ajaxform',['jquery'],function(require,exports,moudles){
	(function($){
		var feature = {};
		feature.fileapi = $("<input type='file'/>").get(0).files !== undefined;
		feature.formdata = window.FormData !== undefined;

		// AJAX提交表单
		$.fn.ajaxsubmit = function(options) {
			if (!this.length) { return this; }

			var method, action, url, $form = this;

			if (typeof options == 'function') {
				options = { success: options };
			}
			else if ( options === undefined ) {
				options = {};
			}

			method = options.type || this.attr('method');
			action = options.url  || this.attr('action');

			url = (typeof action === 'string') ? $.trim(action) : '';
			url = url || window.location.href || '';
			if (url) {
				url = (url.match(/^([^#]+)/)||[])[1];
			}

			options = $.extend(true, {
				url:  url,
				success: $.ajaxSettings.success,
				type: method || $.ajaxSettings.type,
				iframeSrc: /^https/i.test(window.location.href || '') ? 'javascript:false' : 'about:blank'
			}, options);

			var veto = {};
			this.trigger('form-pre-serialize', [this, options, veto]);
			if (veto.veto) { return this; }

			if (options.beforeSerialize && options.beforeSerialize(this, options) === false) { return this; }

			var traditional = options.traditional;
			if ( traditional === undefined ) {
				traditional = $.ajaxSettings.traditional;
			}

			var elements = [];
			var qx, a = this.formToArray(options.semantic, elements); // this.formToArray(options.semantic, elements) or this.formToArray();
			if (options.data) {
				options.extraData = options.data;
				qx = $.param(options.data, traditional);
			}

			this.trigger('form-submit-validate', [a, this, options, veto]);
			if (veto.veto) { return this; }

			var q = $.param(a, traditional);
			if (qx) {
				q = ( q ? (q + '&' + qx) : qx );
			}
			if (options.type.toUpperCase() == 'GET') {
				options.url += (options.url.indexOf('?') >= 0 ? '&' : '?') + q;
				options.data = null;  // data is null for 'get'
			}else {
				options.data = q; // data is the query string for 'post'
			}

			if (options.beforeSubmit && options.beforeSubmit(a, this, options) === false) { return this; }

			var callbacks = [];

			if (!options.dataType && options.target) {
				var oldSuccess = options.success || function(){};
				callbacks.push(function(data) {
					var fn = options.replaceTarget ? 'replaceWith' : 'html';
					$(options.target)[fn](data).each(oldSuccess, arguments);
				});
			}else if (options.success) {
				callbacks.push(options.success);
			}

			options.success = function(data, status, xhr) { // jQuery 1.4+ passes xhr as 3rd arg
				var context = options.context || this ;    // jQuery 1.4+ supports scope context
				for (var i=0, max=callbacks.length; i < max; i++) {
					callbacks[i].apply(context, [data, status, xhr || $form, $form]);
				}
			};

			if (options.error) {
				var oldError = options.error;
				options.error = function(xhr, status, error) {
					var context = options.context || this;
					oldError.apply(context, [xhr, status, error, $form]);
				};
			}

			 if (options.complete) {
				var oldComplete = options.complete;
				options.complete = function(xhr, status) {
					var context = options.context || this;
					oldComplete.apply(context, [xhr, status, $form]);
				};
			}

			var fileInputs = $('input[type=file]:enabled', this).filter(function() { return $(this).val() !== ''; });

			var hasFileInputs = fileInputs.length > 0;
			var mp = 'multipart/form-data';
			var multipart = ($form.attr('enctype') == mp || $form.attr('encoding') == mp);

			var fileAPI = feature.fileapi && feature.formdata;
				var shouldUseFrame = (hasFileInputs || multipart) && !fileAPI;

			var jqxhr;

			if (options.iframe !== false && (options.iframe || shouldUseFrame)) {
				if (options.closeKeepAlive) {
					$.get(options.closeKeepAlive, function() {
						jqxhr = fileUploadIframe(a);
					});
				}
				else {
					jqxhr = fileUploadIframe(a);
				}
			}
			else if ((hasFileInputs || multipart) && fileAPI) {
				jqxhr = fileUploadXhr(a);
			}
			else {
				jqxhr = $.ajax(options);
			}

			$form.removeData('jqxhr').data('jqxhr', jqxhr);

			// clear element array
			for (var k=0; k < elements.length; k++) {
				elements[k] = null;
			}

			// fire 'notify' event
			this.trigger('form-submit-notify', [this, options]);
			return this;

			// utility fn for deep serialization
			function deepSerialize(extraData){
				var serialized = $.param(extraData, options.traditional).split('&');
				var len = serialized.length;
				var result = [];
				var i, part;
				for (i=0; i < len; i++) {
					// #252; undo param space replacement
					serialized[i] = serialized[i].replace(/\+/g,' ');
					part = serialized[i].split('=');
					// #278; use array instead of object storage, favoring array serializations
					result.push([decodeURIComponent(part[0]), decodeURIComponent(part[1])]);
				}
				return result;
			}

			 // XMLHttpRequest Level 2 file uploads (big hat tip to francois2metz)
			function fileUploadXhr(a) {
				var formdata = new FormData();

				for (var i=0; i < a.length; i++) {
					formdata.append(a[i].name, a[i].value);
				}

				if (options.extraData) {
					var serializedData = deepSerialize(options.extraData);
					for (i=0; i < serializedData.length; i++) {
						if (serializedData[i]) {
							formdata.append(serializedData[i][0], serializedData[i][1]);
						}
					}
				}

				options.data = null;

				var s = $.extend(true, {}, $.ajaxSettings, options, {
					contentType: false,
					processData: false,
					cache: false,
					type: method || 'POST'
				});

				if (options.uploadProgress) {
					// workaround because jqXHR does not expose upload property
					s.xhr = function() {
						var xhr = $.ajaxSettings.xhr();
						if (xhr.upload) {
							xhr.upload.addEventListener('progress', function(event) {
								var percent = 0;
								var position = event.loaded || event.position; /*event.position is deprecated*/
								var total = event.total;
								if (event.lengthComputable) {
									percent = Math.ceil(position / total * 100);
								}
								options.uploadProgress(event, position, total, percent);
							}, false);
						}
						return xhr;
					};
				}

				s.data = null;
				var beforeSend = s.beforeSend;
				s.beforeSend = function(xhr, o) {
					//Send FormData() provided by user
					if (options.formData) {
						o.data = options.formData;
					}
					else {
						o.data = formdata;
					}
					if(beforeSend) {
						beforeSend.call(this, xhr, o);
					}
				};
				return $.ajax(s);
			}

			// private function for handling file uploads (hat tip to YAHOO!)
			function fileUploadIframe(a) {
				var form = $form[0], el, i, s, g, id, $io, io, xhr, sub, n, timedOut, timeoutHandle;
				var deferred = $.Deferred();

				// #341
				deferred.abort = function(status) {
					xhr.abort(status);
				};

				if (a) {
					// ensure that every serialized input is still enabled
					for (i=0; i < elements.length; i++) {
						el = $(elements[i]);
						el.removeAttr('disabled');
					}
				}

				s = $.extend(true, {}, $.ajaxSettings, options);
				s.context = s.context || s;
				id = 'jqFormIO' + (new Date().getTime());
				if (s.iframeTarget) {
					$io = $(s.iframeTarget);
					n = $io.attr('name');
					if (!n) {
						$io.attr('name', id);
					}
					else {
						id = n;
					}
				}
				else {
					$io = $('<iframe name="' + id + '" src="'+ s.iframeSrc +'" />');
					$io.css({ position: 'absolute', top: '-1000px', left: '-1000px' });
				}
				io = $io[0];


				xhr = { // mock object
					aborted: 0,
					responseText: null,
					responseXML: null,
					status: 0,
					statusText: 'n/a',
					getAllResponseHeaders: function() {},
					getResponseHeader: function() {},
					setRequestHeader: function() {},
					abort: function(status) {
						var e = (status === 'timeout' ? 'timeout' : 'aborted');
										this.aborted = 1;

						try { // #214, #257
							if (io.contentWindow.document.execCommand) {
								io.contentWindow.document.execCommand('Stop');
							}
						}
						catch(ignore) {}

						$io.attr('src', s.iframeSrc); // abort op in progress
						xhr.error = e;
						if (s.error) {
							s.error.call(s.context, xhr, e, status);
						}
						if (g) {
							$.event.trigger("ajaxError", [xhr, s, e]);
						}
						if (s.complete) {
							s.complete.call(s.context, xhr, e);
						}
					}
				};

				g = s.global;
				// trigger ajax global events so that activity/block indicators work like normal
				if (g && 0 === $.active++) {
					$.event.trigger("ajaxStart");
				}
				if (g) {
					$.event.trigger("ajaxSend", [xhr, s]);
				}

				if (s.beforeSend && s.beforeSend.call(s.context, xhr, s) === false) {
					if (s.global) {
						$.active--;
					}
					deferred.reject();
					return deferred;
				}
				if (xhr.aborted) {
					deferred.reject();
					return deferred;
				}

				// add submitting element to data if we know it
				sub = form.clk;
				if (sub) {
					n = sub.name;
					if (n && !sub.disabled) {
						s.extraData = s.extraData || {};
						s.extraData[n] = sub.value;
						if (sub.type == "image") {
							s.extraData[n+'.x'] = form.clk_x;
							s.extraData[n+'.y'] = form.clk_y;
						}
					}
				}

				var CLIENT_TIMEOUT_ABORT = 1;
				var SERVER_ABORT = 2;
						
				function getDoc(frame) {
					var doc = null;
					
					// IE8 cascading access check
					try {
						if (frame.contentWindow) {
							doc = frame.contentWindow.document;
						}
					} catch(err) {
						// IE8 access denied under ssl & missing protocol
									}

					if (doc) { // successful getting content
						return doc;
					}

					try { // simply checking may throw in ie8 under ssl or mismatched protocol
						doc = frame.contentDocument ? frame.contentDocument : frame.document;
					} catch(err) {
						// last attempt
										doc = frame.document;
					}
					return doc;
				}

				// Rails CSRF hack (thanks to Yvan Barthelemy)
				var csrf_token = $('meta[name=csrf-token]').attr('content');
				var csrf_param = $('meta[name=csrf-param]').attr('content');
				if (csrf_param && csrf_token) {
					s.extraData = s.extraData || {};
					s.extraData[csrf_param] = csrf_token;
				}

				// take a breath so that pending repaints get some cpu time before the upload starts
				function doSubmit() {
					// make sure form attrs are set
					var t = $form.attr('target'), 
						a = $form.attr('action'), 
						mp = 'multipart/form-data',
						et = $form.attr('enctype') || $form.attr('encoding') || mp;

					// update form attrs in IE friendly way
					form.setAttribute('target',id);
					if (!method || /post/i.test(method) ) {
						form.setAttribute('method', 'POST');
					}
					if (a != s.url) {
						form.setAttribute('action', s.url);
					}

					// ie borks in some cases when setting encoding
					if (! s.skipEncodingOverride && (!method || /post/i.test(method))) {
						$form.attr({
							encoding: 'multipart/form-data',
							enctype:  'multipart/form-data'
						});
					}

					// support timout
					if (s.timeout) {
						timeoutHandle = setTimeout(function() { timedOut = true; cb(CLIENT_TIMEOUT_ABORT); }, s.timeout);
					}

					// look for server aborts
					function checkState() {
						try {
							var state = getDoc(io).readyState;
												if (state && state.toLowerCase() == 'uninitialized') {
								setTimeout(checkState,50);
							}
						}
						catch(e) {
												cb(SERVER_ABORT);
							if (timeoutHandle) {
								clearTimeout(timeoutHandle);
							}
							timeoutHandle = undefined;
						}
					}

					// add "extra" data to form if provided in options
					var extraInputs = [];
					try {
						if (s.extraData) {
							for (var n in s.extraData) {
								if (s.extraData.hasOwnProperty(n)) {
								   // if using the $.param format that allows for multiple values with the same name
								   if($.isPlainObject(s.extraData[n]) && s.extraData[n].hasOwnProperty('name') && s.extraData[n].hasOwnProperty('value')) {
									   extraInputs.push(
									   $('<input type="hidden" name="'+s.extraData[n].name+'">').val(s.extraData[n].value)
										   .appendTo(form)[0]);
								   } else {
									   extraInputs.push(
									   $('<input type="hidden" name="'+n+'">').val(s.extraData[n])
										   .appendTo(form)[0]);
								   }
								}
							}
						}

						if (!s.iframeTarget) {
							// add iframe to doc and submit the form
							$io.appendTo('body');
						}
						if (io.attachEvent) {
							io.attachEvent('onload', cb);
						}
						else {
							io.addEventListener('load', cb, false);
						}
						setTimeout(checkState,15);

						try {
							form.submit();
						} catch(err) {
							// just in case form has element with name/id of 'submit'
							var submitFn = document.createElement('form').submit;
							submitFn.apply(form);
						}
					}
					finally {
						// reset attrs and remove "extra" input elements
						form.setAttribute('action',a);
						form.setAttribute('enctype', et); // #380
						if(t) {
							form.setAttribute('target', t);
						} else {
							$form.removeAttr('target');
						}
						$(extraInputs).remove();
					}
				}

				if (s.forceSync) {
					doSubmit();
				}
				else {
					setTimeout(doSubmit, 10); // this lets dom updates render
				}

				var data, doc, domCheckCount = 50, callbackProcessed;

				function cb(e) {
					if (xhr.aborted || callbackProcessed) {
						return;
					}
					
					doc = getDoc(io);
					if(!doc) {
										e = SERVER_ABORT;
					}
					if (e === CLIENT_TIMEOUT_ABORT && xhr) {
						xhr.abort('timeout');
						deferred.reject(xhr, 'timeout');
						return;
					}
					else if (e == SERVER_ABORT && xhr) {
						xhr.abort('server abort');
						deferred.reject(xhr, 'error', 'server abort');
						return;
					}

					if (!doc || doc.location.href == s.iframeSrc) {
						// response not received yet
						if (!timedOut) {
							return;
						}
					}
					if (io.detachEvent) {
						io.detachEvent('onload', cb);
					}
					else {
						io.removeEventListener('load', cb, false);
					}

					var status = 'success', errMsg;
					try {
						if (timedOut) {
							throw 'timeout';
						}

						var isXml = s.dataType == 'xml' || doc.XMLDocument || $.isXMLDoc(doc);
										if (!isXml && window.opera && (doc.body === null || !doc.body.innerHTML)) {
							if (--domCheckCount) {
								// in some browsers (Opera) the iframe DOM is not always traversable when
								// the onload callback fires, so we loop a bit to accommodate
														setTimeout(cb, 250);
								return;
							}
							// let this fall through because server response could be an empty document
							//                    //throw 'DOMException: not available';
						}

						//                var docRoot = doc.body ? doc.body : doc.documentElement;
						xhr.responseText = docRoot ? docRoot.innerHTML : null;
						xhr.responseXML = doc.XMLDocument ? doc.XMLDocument : doc;
						if (isXml) {
							s.dataType = 'xml';
						}
						xhr.getResponseHeader = function(header){
							var headers = {'content-type': s.dataType};
							return headers[header.toLowerCase()];
						};
						// support for XHR 'status' & 'statusText' emulation :
						if (docRoot) {
							xhr.status = Number( docRoot.getAttribute('status') ) || xhr.status;
							xhr.statusText = docRoot.getAttribute('statusText') || xhr.statusText;
						}

						var dt = (s.dataType || '').toLowerCase();
						var scr = /(json|script|text)/.test(dt);
						if (scr || s.textarea) {
							// see if user embedded response in textarea
							var ta = doc.getElementsByTagName('textarea')[0];
							if (ta) {
								xhr.responseText = ta.value;
								// support for XHR 'status' & 'statusText' emulation :
								xhr.status = Number( ta.getAttribute('status') ) || xhr.status;
								xhr.statusText = ta.getAttribute('statusText') || xhr.statusText;
							}
							else if (scr) {
								// account for browsers injecting pre around json response
								var pre = doc.getElementsByTagName('pre')[0];
								var b = doc.getElementsByTagName('body')[0];
								if (pre) {
									xhr.responseText = pre.textContent ? pre.textContent : pre.innerText;
								}
								else if (b) {
									xhr.responseText = b.textContent ? b.textContent : b.innerText;
								}
							}
						}
						else if (dt == 'xml' && !xhr.responseXML && xhr.responseText) {
							xhr.responseXML = toXml(xhr.responseText);
						}

						try {
							data = httpData(xhr, dt, s);
						}
						catch (err) {
							status = 'parsererror';
							xhr.error = errMsg = (err || status);
						}
					}
					catch (err) {
										status = 'error';
						xhr.error = errMsg = (err || status);
					}

					if (xhr.aborted) {
										status = null;
					}

					if (xhr.status) { // we've set xhr.status
						status = (xhr.status >= 200 && xhr.status < 300 || xhr.status === 304) ? 'success' : 'error';
					}

					// ordering of these callbacks/triggers is odd, but that's how $.ajax does it
					if (status === 'success') {
						if (s.success) {
							s.success.call(s.context, data, 'success', xhr);
						}
						deferred.resolve(xhr.responseText, 'success', xhr);
						if (g) {
							$.event.trigger("ajaxSuccess", [xhr, s]);
						}
					}
					else if (status) {
						if (errMsg === undefined) {
							errMsg = xhr.statusText;
						}
						if (s.error) {
							s.error.call(s.context, xhr, status, errMsg);
						}
						deferred.reject(xhr, 'error', errMsg);
						if (g) {
							$.event.trigger("ajaxError", [xhr, s, errMsg]);
						}
					}

					if (g) {
						$.event.trigger("ajaxComplete", [xhr, s]);
					}

					if (g && ! --$.active) {
						$.event.trigger("ajaxStop");
					}

					if (s.complete) {
						s.complete.call(s.context, xhr, status);
					}

					callbackProcessed = true;
					if (s.timeout) {
						clearTimeout(timeoutHandle);
					}

					// clean up
					setTimeout(function() {
						if (!s.iframeTarget) {
							$io.remove();
						}
						else { //adding else to clean up existing iframe response.
							$io.attr('src', s.iframeSrc);
						}
						xhr.responseXML = null;
					}, 100);
				}

				var toXml = $.parseXML || function(s, doc) { // use parseXML if available (jQuery 1.5+)
					if (window.ActiveXObject) {
						doc = new ActiveXObject('Microsoft.XMLDOM');
						doc.async = 'false';
						doc.loadXML(s);
					}
					else {
						doc = (new DOMParser()).parseFromString(s, 'text/xml');
					}
					return (doc && doc.documentElement && doc.documentElement.nodeName != 'parsererror') ? doc : null;
				};
				var parseJSON = $.parseJSON || function(s) {
					/*jslint evil:true */
					return window['eval']('(' + s + ')');
				};

				var httpData = function( xhr, type, s ) { // mostly lifted from jq1.4.4

					var ct = xhr.getResponseHeader('content-type') || '',
						xml = type === 'xml' || !type && ct.indexOf('xml') >= 0,
						data = xml ? xhr.responseXML : xhr.responseText;

					if (xml && data.documentElement.nodeName === 'parsererror') {
						if ($.error) {
							$.error('parsererror');
						}
					}
					if (s && s.dataFilter) {
						data = s.dataFilter(data, type);
					}
					if (typeof data === 'string') {
						if (type === 'json' || !type && ct.indexOf('json') >= 0) {
							data = parseJSON(data);
						} else if (type === "script" || !type && ct.indexOf("javascript") >= 0) {
							$.globalEval(data);
						}
					}
					return data;
				};

				return deferred;
			}
		};

		// AJAX绑定表单事件
		$.fn.ajaxform = function(options) {
			options = options || {};
			options.delegation = options.delegation!==false && $.isFunction($.fn.on) && this.selector;

			// 立即提交
			if(options.submit){
				return this.ajaxsubmit(options);
			}

			// in jQuery 1.3+ we can fix mistakes with the ready state
			if (!options.delegation && this.length === 0) {
				var o = { s: this.selector, c: this.context };
				if (!$.isReady && o.s) {
								$(function() {
						$(o.s,o.c).ajaxform(options);
					});
					return this;
				}
				return this;
			}

			if ( options.delegation ) {
				$(document)
					.off('submit.form-plugin', this.selector, doAjaxSubmit)
					.off('click.form-plugin', this.selector, captureSubmittingElement)
					.on('submit.form-plugin', this.selector, options, doAjaxSubmit)
					.on('click.form-plugin', this.selector, options, captureSubmittingElement);
			}else{
				this.unbind('submit.form-plugin click.form-plugin')
					.bind('submit.form-plugin', options, doAjaxSubmit)
					.bind('click.form-plugin', options, captureSubmittingElement);
			}

			return this;
		};

		function doAjaxSubmit(e) {
			/*jshint validthis:true */
			var options = e.data;
			if (!e.isDefaultPrevented()) { // if event has been canceled, don't proceed
				if(!$(this).is('[target][target!=""]')){
					e.preventDefault();
					$(e.target).ajaxsubmit(options); // #365
				}
			}
		}

		function captureSubmittingElement(e) {
			/*jshint validthis:true */
			var target = e.target;
			var $el = $(target);
			if (!($el.is("[type=submit],[type=image]"))) {
				// is this a child element of the submit el?  (ex: a span within a button)
				var t = $el.closest('[type=submit]');
				if (t.length === 0) {
					return;
				}
				target = t[0];
			}
			var form = this;
			form.clk = target;
			if (target.type == 'image') {
				if (e.offsetX !== undefined) {
					form.clk_x = e.offsetX;
					form.clk_y = e.offsetY;
				} else if (typeof $.fn.offset == 'function') {
					var offset = $el.offset();
					form.clk_x = e.pageX - offset.left;
					form.clk_y = e.pageY - offset.top;
				} else {
					form.clk_x = e.pageX - target.offsetLeft;
					form.clk_y = e.pageY - target.offsetTop;
				}
			}
			// clear form vars
			setTimeout(function() { form.clk = form.clk_x = form.clk_y = null; }, 100);
		}

		
		// 序列化取表单元素值
		$.fn.formToArray = function(semantic, elements) {
			var a = [];
			if (this.length === 0) {
				return a;
			}

			var form = this[0];
			var formId = this.attr('id');
			var els = semantic ? form.getElementsByTagName('*') : form.elements;
			var els2;

			if (els && !/MSIE [678]/.test(navigator.userAgent)) { // #390
				els = $(els).get();  // convert to standard array
			}

			// #386; account for inputs outside the form which use the 'form' attribute
			if ( formId ) {
				els2 = $(':input[form="' + formId + '"]').get(); // hat tip @thet
				if ( els2.length ) {
					els = (els || []).concat(els2);
				}
			}

			if (!els || !els.length) {
				return a;
			}

			var i,j,n,v,el,max,jmax;
			for(i=0, max=els.length; i < max; i++) {
				el = els[i];
				n = el.name;
				if (!n || el.disabled) {
					continue;
				}

				if (semantic && form.clk && el.type == "image") {
					// handle image inputs on the fly when semantic == true
					if(form.clk == el) {
						a.push({name: n, value: $(el).val(), type: el.type });
						a.push({name: n+'.x', value: form.clk_x}, {name: n+'.y', value: form.clk_y});
					}
					continue;
				}

				v = $.fieldValue(el, true);
				if (v && v.constructor == Array) {
					if (elements) {
						elements.push(el);
					}
					for(j=0, jmax=v.length; j < jmax; j++) {
						a.push({name: n, value: v[j]});
					}
				}
				else if (feature.fileapi && el.type == 'file') {
					if (elements) {
						elements.push(el);
					}
					var files = el.files;
					if (files.length) {
						for (j=0; j < files.length; j++) {
							a.push({name: n, value: files[j], type: el.type});
						}
					}
					else {
						// #180
						a.push({ name: n, value: '', type: el.type });
					}
				}
				else if (v !== null && typeof v != 'undefined') {
					if (elements) {
						elements.push(el);
					}
					a.push({name: n, value: v, type: el.type, required: el.required});
				}
			}

			if (!semantic && form.clk) {
				// input type=='image' are not found in elements array! handle it here
				var $input = $(form.clk), input = $input[0];
				n = input.name;
				if (n && !input.disabled && input.type == 'image') {
					a.push({name: n, value: $input.val()});
					a.push({name: n+'.x', value: form.clk_x}, {name: n+'.y', value: form.clk_y});
				}
			}
			return a;
		};


		// 字段返回值
		$.fieldValue = function(el, successful) {
			var n = el.name, t = el.type, tag = el.tagName.toLowerCase();
			if (successful === undefined) {
				successful = true;
			}

			if (successful && (!n || el.disabled || t == 'reset' || t == 'button' ||
				(t == 'checkbox' || t == 'radio') && !el.checked ||
				(t == 'submit' || t == 'image') && el.form && el.form.clk != el ||
				tag == 'select' && el.selectedIndex == -1)) {
					return null;
			}

			if (tag == 'select') {
				var index = el.selectedIndex;
				if (index < 0) {
					return null;
				}
				var a = [], ops = el.options;
				var one = (t == 'select-one');
				var max = (one ? index+1 : ops.length);
				for(var i=(one ? index : 0); i < max; i++) {
					var op = ops[i];
					if (op.selected) {
						var v = op.value;
						if (!v) { // extra pain for IE...
							v = (op.attributes && op.attributes.value && !(op.attributes.value.specified)) ? op.text : op.value;
						}
						if (one) {
							return v;
						}
						a.push(v);
					}
				}
				return a;
			}
			return $(el).val();
		};
	})(window.jQuery || require('jquery'));
});