/**
 @Name：asyncpage V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-5-19 
 */
define('pui/asyncpage',['jquery','pui/ajaxform'],function(require,exports,moudles){
	(function($){
		$.fn.asyncpage = function(opts){
			if(opts && typeof opts=='string') opts={url:opts};
			opts=$.extend({
				url : '',
				callback : null,
				data : {}
			},opts);

			var stateTitle,stateUrl,
				_this = $(this),
				success = function(html){
					if(history.pushState){
						stateTitle = stateTitle || document.title;
						stateUrl = stateUrl || location.href;
						history.pushState({title: stateTitle}, stateTitle, stateUrl);
					}
					var moreEl = $();
					$('body').children().each(function(){
						var position = ($(this).css('position')||'').toLocaleLowerCase();
						(position!='absolute' && position!='fixed') && (moreEl=moreEl.add(this));
					});
					if($.isFunction(opts.callback)){
						var el = opts.callback.call(this,html);
					}else{
						var el = $('body').prepend(html);
					}
					el && $(el).is('body') && moreEl.remove();
					$(window).resize();
					pui.parseHtml && pui.parseHtml(el || null); // 控件调用
					$.isFunction(opts.callback) ? $(el).animate({scrollTop:0}) : $('html,body').animate({scrollTop:0});
				};

			// 直接链接跳转
			if(opts.url){
				stateUrl = opts.url;
				return $.post(opts.url,opts.data,success);
			}

			// 链接异步加载
			_this.find('a:not([target])').click(function(e){
				var _t = $(this),
					url = _t.attr('href');
				if(!e.isDefaultPrevented() && url && url.substring(0,1)!='#' && url.substring(0,10)!='javascript'){
					$(document).trigger('click').trigger('mousedown');
					stateUrl = url;
					stateTitle = _t.text();
					$.post(url,opts.data,success);
					return false;
				}
			});

			// 表单异步提交
			_this.find('form').ajaxform({
				beforeSubmit : function(){
					$(document).trigger('click').trigger('mousedown');
					stateUrl = this.url;
				},
				data : opts.data,
				success : success
			});

			// history
			history.pushState && $(window).off('popstate').on('popstate',function(target){
				stateUrl = location.href;
				$.post(location.href,opts.data,success);
			});

			$.fn.asyncpage.success = success;
		};
		
	})((window.jQuery || require('jquery')),require('pui/ajaxform'));
});




