/**
 @Name：tabs V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-9-17 
 */
define('pui/tabs',['jquery','pui/tabs.css'],function(require,exports,moudles){
	(function($){
		$.fn.tabs = function(options){
			options=$.extend({
				tabDom : '>ul>li:not(.tabs_link)', // 寻找标签元素
				divDom : '>div', // 寻找内容元素
				eventType : 'click', // 事件 click or mouseenter / mouseover
				tabClass : 'on' // 标签焦点样式
			},options);
			this.each(function(){
				var box = $(this).addClass('tabs_box'),
					lis = box.find(options.tabDom).addClass('tabs_header'),
					divs = box.find(options.divDom).addClass('tabs_content');
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
	})(window.jQuery || require('jquery'));
});