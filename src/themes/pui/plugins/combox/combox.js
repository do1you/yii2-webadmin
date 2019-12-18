/**
 @Name：combox V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-1-13 
 */
define('pui/combox',['jquery','pui/combox.css'],function(require,exports,moudles){
	(function($){
		$.fn.combox = function(options){
			this.each(function(){
				var opts=$.extend({
						width : "auto",
						height : "auto",
						className : ""
					},options),
					docHeight = $('body').outerHeight(),
					_this = $(this),
					comboxEl = $('<div class="combox_box"></div>').css('z-index',parseInt((docHeight-_this.offset().top)/10+$.fn.combox.index--)).addClass(opts.className),
					selectEl = $('<div class="combox_select"></div>').html(_this.find('option:selected').text()+'<i></i>'),
					optionEl = $('<ul></ul>');
				if(this.tagName!='SELECT') return;
				
				// 选项
				_this.find('option').each(function(){
					var option = $(this);
					$("<li>"+ $(this).text() +"</li>").click(function(){
						selectEl.html(option.text()+'<i></i>'); // 选中效果
						optionEl.trigger('hide'); // 隐藏下拉
						var val = _this.val();
						_this.val(option.val());
						if(val!=_this.val()) _this.trigger("change"); // 赋值并触发事件
						return false;
					}).appendTo(optionEl);				
				});
				
				// 计算容器宽高
				opts.width = opts.width=='auto' ? _this.outerWidth()+15 : parseInt(opts.width);
				opts.height = opts.height=='auto' ? _this.outerHeight() : parseInt(opts.height);
				opts.height = Math.max(opts.height,28);
							
				// 组装HTML
				comboxEl.width(opts.width).height(opts.height).css('line-height',opts.height+'px').append(selectEl).append(optionEl).insertAfter(_this.hide());
				
				// 事件
				selectEl.click(function(){ // 弹出下拉选项
					$('.combox_box ul').trigger('hide');
					optionEl.trigger('toggle');
					return false;
				});
				optionEl.bind('show',function(){ // 下拉弹出效果
					if(optionEl.is(':animated')) return false;
					optionEl.slideDown('fast');	
					comboxEl.addClass('expanded');
				}).bind('hide',function(){ // 下拉隐藏效果
					if(optionEl.is(':animated')) return false;
					optionEl.slideUp('fast');	
					comboxEl.removeClass('expanded');
				}).bind('toggle',function(){
					if(comboxEl.hasClass('expanded'))
						optionEl.trigger('hide');
					else 
						optionEl.trigger('show');
				});
			});
		};

		$.fn.combox.index = 900;
		
		// 取消选择
		$(document).click(function(){
			$('.combox_box ul').trigger('hide');
		});
	})(window.jQuery || require('jquery'));
});