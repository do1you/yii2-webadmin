/**
 @Name：placeholder V0.1
 @Author：统一 54901801@qq.com
 @Date：2016-10-8 
 */
define('pui/placeholder',['jquery'],function(require,exports,moudles){
	(function($){
		$.fn.placeholder = function(txt){
			var i = document.createElement('input'),
				placeholdersupport = 'placeholder' in i;
			if(!placeholdersupport){
				var inputs = $(this);
				inputs.each(function(){
					var input = $(this),
						text = txt || input.attr('placeholder'),
						pdl = 0,
						height = input.outerHeight(),
						width = input.outerWidth(),
						placeholder = $('<span class="phTips">'+text+'</span>');
					try{
						pdl = input.css('padding-left').match(/\d*/i)[0] * 1;
					}catch(e){
						pdl = 5;
					}
					placeholder.css({'margin-left': -(width-pdl),'height':height,'line-height':height+"px",'position':'absolute', 'color': "#cecfc9", 'font-size' : "12px"});
					placeholder.click(function(){
						input.focus();
					});
					if(input.val() != ""){
						placeholder.css({display:'none'});
					}else{
						placeholder.css({display:'inline'});
					}
					placeholder.insertAfter(input);
					input.keyup(function(e){
						if($(this).val() != ""){
							placeholder.css({display:'none'});
						}else{
							placeholder.css({display:'inline'});
						}
					});
				});
			}else{
				$(this).attr('placeholder',(txt || input.attr('placeholder')));
			}
			return this;
		};
	})(window.jQuery || require('jquery'));
});