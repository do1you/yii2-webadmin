/**
 @Name：uploadmulti V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-3-4 
 */
define('pui/uploadmulti',['kindeditor','jquery'],function(require,exports,module){
	module.exports = (function(K,$){
		return function(options){
			// 定义目录变量
			var src = pui.resolve('kindeditor');
			K.options.basePath = src.substring(0, src.lastIndexOf('/') + 1);
			K.options.themesPath =  K.options.basePath + 'themes/';
			K.options.langPath = K.options.basePath + 'lang/';
			K.options.pluginsPath = K.options.basePath + 'plugins/';

			// 参数
			var selector = options&&options.dom ? options.dom : (this.tagName ? this : null);
			delete options.dom;
			options = $.extend({
				allowFileManager : true
			}, options);

			// 窗口对象
			var editor = K.editor(options),
				loadfn = function(){ // 加载样式和语言包
					setTimeout(function(){
						if(K.lang('insertfile','zh_CN')=='no language'){
							K.loadStyle(K.options.themesPath + 'default/default.css');
							K.loadScript(K.options.langPath + 'zh_CN.js',runfn);
						}else{
							runfn();
						}
					},70);
				},
				runfn = function() {
					editor.loadPlugin('multiimage', function() {
						editor.plugin.multiImageDialog({
							clickFn : function(urlList) {
								options.after && options.after.apply(this, arguments);
								editor.hideDialog();
							}
						});
					});
				};
			return (selector ? K(selector).bind('click',loadfn) : loadfn());
		};
	})(require('kindeditor'),(window.jQuery || require('jquery')));
});