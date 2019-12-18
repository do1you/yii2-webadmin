/**
 @Name：editor V0.2
 @Author：统一 54901801@qq.com
 @Date：2015-3-2 
 */
define('pui/editor',['kindeditor','jquery'],function(require,exports,module){
	module.exports = (function(K,$){
		return function(options){
			var src = pui.resolve('kindeditor');
			options = $.extend({
				basePath : src.substring(0, src.lastIndexOf('/') + 1),
				allowFileManager : true
			}, (options || {}));

			var selector = options.dom || this;
			if($(selector).attr('readonly') || $(selector).attr('disabled')) options.readonlyMode = true;
			delete options.dom;
			K.create(selector, options);
		};
	})(require('kindeditor'),(window.jQuery || require('jquery')));
});