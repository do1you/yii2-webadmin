/**
 @Name：uploadbtn V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-3-3 
 */
define('pui/uploadbtn',['kindeditor','jquery'],function(require,exports,module){
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
			
			// 参数定义
			var selector = options&&options.dom ? options.dom : (this.tagName ? this : null);
			delete options.dom;
			options = $.extend({
				button : selector,
				fieldName : 'imgFile', // 不可修改
				url : K.options.basePath+'php/upload_json.php?dir='+(options.type||'image'), //  image  media  flash  file excel
				afterUpload : function(data) {
					if (data.error === 0) {
						var url = K.formatUrl(data.url, 'absolute');
						options.after && options.after.call(this,url,data);
					} else {
						alert(data.message);
					}
				}
			},options);

			$(function(){
				if(selector && options.retain) $(selector).addClass('isRetain');
				var uploadbutton = K.uploadbutton(options);
				uploadbutton.fileBox.change(function(e) {
					uploadbutton.submit();
				});
				
				// 保留原样式
				if(selector && options.retain){
					//console.log(uploadbutton.div); // div button fileBox form iframe
					var zIndex = uploadbutton.button.css('zIndex') || 1,
						button = uploadbutton.button.show().css('zIndex',zIndex),
						width = button.width(),
						height = button.height();
					uploadbutton.div.width(width).height(height).css({position:'absolute',zIndex:(zIndex+1)}).opacity(0);
					uploadbutton.form.width(width).height(height);
					uploadbutton.fileBox.width(width+300).height(height);
				}
			});
		};
	})(require('kindeditor'),(window.jQuery || require('jquery')));
});