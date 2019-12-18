/**
 @Name：uploaddialog V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-3-3 
 */
define('pui/uploaddialog',['kindeditor','jquery'],function(require,exports,module){
	module.exports = (function(K,$){
		return function(options){
			// 定义目录变量
			var src = pui.resolve('kindeditor');
			K.options.basePath = src.substring(0, src.lastIndexOf('/') + 1);
			K.options.themesPath =  K.options.basePath + 'themes/';
			K.options.langPath = K.options.basePath + 'lang/';
			K.options.pluginsPath = K.options.basePath + 'plugins/';

			// 参数定义
			var selector = options&&options.dom ? options.dom : (this.tagName ? this : null);
			delete options.dom;
			options = $.extend({
				type : 'image',
				allowFileManager : true,
				showLocal : true,
				showRemote : true,
				autostart : true
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
					switch(options.type){ // 上传类型
						case 'media': // 视频
							editor.loadPlugin('media', function() {
								// 视频插件不支持编辑器外调用处理
								editor.insertHtml = function(html){ 
									attrs = K.mediaAttrs(K(html).attr('data-ke-tag'));
									options.after && options.after.call(editor, attrs.src, attrs);
									return editor;
								};
								editor.plugin.getSelectedMedia = function(){
									if(options.defUrl){
										return K(K.mediaImg(K.options.themesPath + 'common/blank.gif', {
											src : options.defUrl,
											type : K.mediaType(options.defUrl),
											width : (options.width||''),
											height : (options.height||''),
											autostart : options.autostart,
											loop : 'true'
										}));
									}
								};
								editor.plugin.media.edit.call(editor);
							});
						break;
						case 'flash': // FLASH
							editor.loadPlugin('flash', function() {
								// FLASH插件不支持编辑器外调用处理
								editor.insertHtml = function(html){ 
									attrs = K.mediaAttrs(K(html).attr('data-ke-tag'));
									options.after && options.after.call(editor, attrs.src, attrs);
									return editor;
								};
								editor.plugin.getSelectedFlash = function(){
									if(options.defUrl){
										return K(K.mediaImg(K.options.themesPath + 'common/blank.gif', {
											src : options.defUrl,
											type : K.mediaType('.swf'),
											width : (options.width||''),
											height : (options.height||''),
											quality : 'high'
										}));
									}
								};
								editor.plugin.flash.edit.call(editor);
							});
						break;
						case 'image': // 图片
							editor.loadPlugin('image', function() {
								editor.plugin.imageDialog({
									showLocal : options.showLocal,
									showRemote : options.showRemote,
									imageUrl : (options.defUrl || ''),
									clickFn : function(url, title, width, height, border, align) {
										options.after && options.after.apply(this, arguments);
										editor.hideDialog();
									}
								});
							});
						break;
						case 'file': // 文件
						default: // 默认
							options.type = options.type;
							editor.loadPlugin('insertfile', function() {
								editor.plugin.fileDialog({
									fileUrl : (options.defUrl || ''),
									type : options.type,
									clickFn : function(utl,title) {
										options.after && options.after.apply(this, arguments);
										editor.hideDialog();
									}
								});
							});
						break;
						
					}
				};
			return (selector ? K(selector).bind('click',loadfn) : loadfn());
		};
	})(require('kindeditor'),(window.jQuery || require('jquery')));
});