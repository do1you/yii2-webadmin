/**
 @Name：message V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-5-15
 */
define('pui/message',['jquery','pui/message.css'],function(require,exports,moudles){
	(function($){
		$.fn.message = function(opts){
			opts = typeof opts=='string' ? {type:opts} : opts;
			opts=$.extend({
				type : 'info', // 信息类型
				close : true,  // 关闭按纽
				time : 3000  // 显示时间
			},opts);
			this.each(function(){
				var _this = $(this).addClass('message_box'),
					html = _this.html(),
					box = $('<div></div>');
				_this.addClass(opts.type).empty();
				box.html(html).appendTo(_this);
				_this.bind('message.remove',function(){
					_this.stop().slideUp('slow',function(){
						_this.remove();
						$.fn.message.nums--;
					});
				});
				if(opts.close){
					var close = $('<a href="###" class="close">×</a>');
					_this.append(close);
					close.click(function(){
						_this.trigger('message.remove');
					});
				}
				setTimeout(function(){
					_this.trigger('message.remove');
				},(opts.time+$.fn.message.nums++*1000));
			});
		};
		$.fn.message.nums = 0;
	})(window.jQuery || require('jquery'));
});