/**
 @Name：ubb V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-3-2 
 */
define('pui/ubb',['kindeditor','jquery'],function(require,exports,module){
	module.exports = (function(K,$){
		return function(options){
			var src = pui.resolve('kindeditor');
			options = $.extend({
				basePath : src.substring(0, src.lastIndexOf('/') + 1),
				resizeType : 1,
				allowPreviewEmoticons : false,
				allowImageUpload : false,
				items : [
					'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',
					'removeformat', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist',
					'insertunorderedlist', '|', 'emoticons', 'image', 'link']
			}, (options || {}));

			var selector = options.dom || this;
			if($(selector).attr('readonly') || $(selector).attr('disabled')) options.readonlyMode = true;
			delete options.dom;
			K.create(selector, options);
		};
	})(require('kindeditor'),(window.jQuery || require('jquery')));
});