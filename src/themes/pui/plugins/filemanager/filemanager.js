/**
 @Name：filemanager V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-3-2 
 */
define('pui/filemanager',['kindeditor','jquery'],function(require,exports,module){
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
			K.loadScript(K.options.langPath + 'zh_CN.js');// 加载语言包

			// 定义编辑器对象
			var editor = K.editor({
					fileManagerJson : K.options.basePath+'php/file_manager_json.php'
				}),
				fn = options&&options.clickFn ? options.clickFn : null;
			options.clickFn = function(url, title) { // 回调
				editor.hideDialog();
				fn && fn(url, title);
			};
			options = $.extend({
				viewType : 'VIEW',
				dirName : 'image'
			}, options);
			
			var selector = options.dom || (this.tagName ? this : null);
			delete options.dom;

			// 展示文件浏览窗口
			var loadfn = function(){ // 加载样式和语言包
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
					editor.loadPlugin('filemanager', function() { // 加载插件
						editor.plugin.filemanagerDialog(options);
					});
					return editor;
				};
			return (selector ? K(selector).bind('click',loadfn) : loadfn());
		};
	})(require('kindeditor'),(window.jQuery || require('jquery')));
});