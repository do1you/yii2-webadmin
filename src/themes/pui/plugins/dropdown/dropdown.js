/**
 @Name：dropdown V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-9-21 
 */
define('pui/dropdown',['jquery','pui/dropdown.css'],function(require,exports,moudles){
	(function($){
		var activeClass='',hidefn = function(dbox,tbox){ // 隐藏全局下拉框
			$('.dropdown_menu').not(dbox).hide();
			$('.dropdown_btn').not(tbox).removeClass(activeClass);
		};
		$.fn.dropdown = function(options){
			options=$.extend({
				targetDom : '', // 触发移动到选择器
				dropDom : '>ul', // 下拉容器选择器
				eventType : 'toggle', // 触发事件 hover or toggle
				activeClass : 'on' // 触发容器焦点样式
			},options);
			this.each(function(){
				var box = $(this),
					dbox = box.find(options.dropDom),
					tbox = options.targetDom ? box.find(options.targetDom) : box,
					fn = function(){
						dbox.is(':hidden') ? tbox.removeClass(options.activeClass) : tbox.addClass(options.activeClass);
					};
				if(tbox.length && dbox.length){
					box.addClass('dropdown_box');
					dbox.addClass('dropdown_menu');
					tbox.addClass('dropdown_btn');
					tbox[options.eventType](function(){
						hidefn(dbox,tbox);
						dbox.stop().slideToggle('fast',fn);
					},function(){
						hidefn(dbox,tbox);
						dbox.stop().slideToggle('fast',fn);
					});
					fn();
				}
			});
			activeClass += options.activeClass+' ';
		};

		// 点击其它位置时隐藏
		$(document).bind('click',hidefn);
	})(window.jQuery || require('jquery'));
});