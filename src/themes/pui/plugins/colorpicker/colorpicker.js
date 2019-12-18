/**
 @Name：colorpicker V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-3-2 
 */
define('pui/colorpicker',['kindeditor','jquery'],function(require,exports,module){
	module.exports = (function(K,$){
		return function(options){
			// 定义目录变量
			var src = pui.resolve('kindeditor');
			K.options.basePath = src.substring(0, src.lastIndexOf('/') + 1);
			K.options.themesPath =  K.options.basePath + 'themes/';
			K.options.langPath = K.options.basePath + 'lang/';
			K.options.pluginsPath = K.options.basePath + 'plugins/';

			// 加载资源
			K.loadStyle(K.options.themesPath + 'default/default.css');

			var selector = options&&options.dom ? options.dom : (this.tagName ? this : null);
			delete options.dom;

			// 参数
			var offsetEl = options&&options.offset ? options.offset : selector;
			var colorpickerPos = offsetEl ? K(offsetEl).pos() : {x:0,y:0};
			options = $.extend({
				x : colorpickerPos.x,
				y : colorpickerPos.y + (offsetEl ? K(offsetEl).height() : 0),
				z : 54901801,
				selectedColor : 'default',
				noColor : '无颜色'
			},options);

			// 拾色器面板
			var colorpicker,fn = options.click || null;
			options.click = function(color) {
					colorpicker.remove();
					colorpicker = null;
					fn && fn(color);
				};
			var runfn = function(e) {
				e && e.stopPropagation();
				if (colorpicker) {
					colorpicker.remove();
					colorpicker = null;
					return false;
				}
				if(this!==document) colorpicker = K.colorpicker(options);
				return false;
			};
			K(document).click(runfn);
			return (selector ? K(selector).bind('click',runfn) : runfn());
		};
	})(require('kindeditor'),(window.jQuery || require('jquery')));
});