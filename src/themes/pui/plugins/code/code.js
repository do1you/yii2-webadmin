/**
 @Name：code V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-1-12 
 */
define('pui/code',['jquery','pui/code.css'],function(require,exports,moudles){
	(function($){
		$.fn.code = function(opts){
			opts=$.extend({
				title : "代码",
				copyRight : "code - v0.1", /* 版权,显示在容器右上角 */
				height : "auto"
			},opts);
			this.each(function(){
				var _this = $(this).addClass('code_code'),
					codeEl = $('<div class="code_box"></div>'),
					titleEl = $('<span class="code_title">'+opts.title+'<span>'+opts.copyRight+'</span></span>'),
					html,parentEl;
				
				// 组装HTML	
				var prevEl = _this.prev();
				prevEl.length ? prevEl.after(codeEl) : _this.parent().append(codeEl);
				codeEl.append(_this).append(titleEl); 	
				
				// 行数
				var html = '',
					line = _this.html().split("\n").length,
					hline = Math.ceil(_this.height()/parseInt(_this.css("line-height")));
				line = Math.max(line,hline,1);
				for(var i=1;i<=line;i++){
					html += '<li>'+i+'</li>';
				}
				_this.append('<ol class="code_nums">'+html+'</ol>');
				if(opts.height!='auto'){
					_this.height(opts.height).css('overflow-y','auto').find('ol').height(Math.max(_this.innerHeight(true),parseInt(opts.height)));
				}
			});
		};
	})(window.jQuery || require('jquery'));
});