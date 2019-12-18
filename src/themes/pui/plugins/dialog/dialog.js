/**
 @Name：dialog V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-3-3 
 */
define('pui/dialog',['kindeditor','jquery'],function(require,exports,module){
	module.exports = (function(K,$){
		return function(options){
			// 定义目录变量
			var src = pui.resolve('kindeditor');
			K.options.basePath = src.substring(0, src.lastIndexOf('/') + 1);
			K.options.themesPath =  K.options.basePath + 'themes/';
			K.options.langPath = K.options.basePath + 'lang/';
			K.options.pluginsPath = K.options.basePath + 'plugins/';

			// 加载资源
			K.loadStyle(K.options.themesPath + 'default/default.css'); // 加载样式
			
			// 定义窗口
			var dialog;
			options = $.extend({
				title : '&nbsp;',
				body : '',
				closeBtn : {
					name : (options.closeName ||'关闭'),
					click : function(e) {
						options.close && options.close.call(this, e, dialog);
						dialog.remove();
					}
				},
				yesBtn : {
					name : (options.yesName || '确定'),
					click : function(e) {
						options.yes && options.yes.call(this, e, dialog);
						dialog.remove();
					}
				},
				noBtn : {
					name : (options.noName || '取消'),
					click : function(e) {
						options.close && options.close.call(this, e, dialog);
						dialog.remove();
					}
				}
			}, options);
			options.body = '<div style="margin:5px 10px;">'+(options.body||'&nbsp;')+'</div>';

			var selector = options.dom || (this.tagName ? this : null);
			delete options.dom;

			var runfn = function() {
				dialog = K.dialog(options);
				if(options.ajax){
					$(dialog.bodyDiv).load(options.ajax,function(){
						pui.parseHtml && pui.parseHtml(dialog.bodyDiv);
						options.callback && options.callback.call(this,dialog);
					});
				}else{
					options.callback && options.callback.call(this,dialog);
				}				
				return dialog;
			};
			return (selector ? K(selector).bind('click',runfn) : runfn());
		};
	})(require('kindeditor'),(window.jQuery || require('jquery')));
});