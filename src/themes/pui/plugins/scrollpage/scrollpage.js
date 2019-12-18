/**
 @Name：scrollpage V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-6-2 
 */
define('pui/scrollpage',['jquery'],function(require,exports,moudles){
	(function($){
		var loadingBox = $('<div>数据加载中...</div>'),
			src = pui.resolve('pui/scrollpage'),
			pluginSrc = src.substring(0, src.lastIndexOf('/') + 1);
		loadingBox.attr('rel','loaded').css({
			'background' : 'url('+pluginSrc+'loading.gif) no-repeat 5px center',
			'font-size' : '12px',
			'padding' : '8px 8px 8px 26px'
		});

		// 定义滚动页面插件
		$.fn.scrollpage = function(options) {
			var opts = $.extend({},$.fn.scrollpage.defaults, options); 
			return this.each(function() {
				$.fn.scrollpage.init($(this), opts);
			});
		};
		
		// 停止滚动加载页面
		$.fn.stopscrollpage = function(){
			return this.attr('scrollpage', 'disabled');
		};
  
		// 加载内容
		$.fn.scrollpage.loadContent = function(obj, opts){
			var target = $(opts.scrollTarget);
			var mayLoadContent = opts.heightOffset >= (target[0]&&target[0].scrollHeight ? target[0].scrollHeight : $(document).height()) - target.height()-target.scrollTop();
			if (mayLoadContent && !$(obj).data('loadLock')){
				$(obj).data('loadLock',true).children().attr('rel', 'loaded');
				var contentPage = typeof opts.contentPage=='function' ? opts.contentPage() : opts.contentPage,
					contentData = typeof opts.contentData=='function' ? opts.contentData() : opts.contentData;
				if(opts.contentNext){ // 下一页的选择器
					var nextEl = $(obj).find(opts.contentNext).eq(-1);
					contentPage = nextEl.attr('href');
					nextEl.remove();
				}
				if(contentPage && contentPage.substring(0,1)!='#' && contentPage.substring(0,10)!='javascript'){
					opts.beforeLoad ? opts.beforeLoad() : ($(obj).append(loadingBox)&&loadingBox.show()); // 预处理
					$.ajax({
						type: 'POST',
						url: contentPage,
						data: contentData,
						global: false,
						success: function(data){
							loadingBox.hide();
							if(data && data!='false'){
								$(obj).append(data); 
								var objectsRendered = $(obj).children('[rel!=loaded]:visible');
								objectsRendered.hide().fadeIn();
								opts.afterLoad && opts.afterLoad(objectsRendered); // 加载完成后处理
							}else{
								$(obj).stopscrollpage();
							}
							$(obj).data('loadLock',false);
							$.fn.scrollpage.loadContent(obj, opts);
						},
						dataType: 'html'
					});
				}else{
					$(obj).stopscrollpage();
				}				
			}
		};
  
		// 初始化滚动插件
		$.fn.scrollpage.init = function(obj, opts){
			var target = $(opts.scrollTarget);
			$(obj).attr('scrollpage', 'enabled');
			target.scroll(function(e){
				if ($(obj).attr('scrollpage') == 'enabled'){
					$.fn.scrollpage.loadContent(obj, opts);		
				}else{
					e.stopPropagation();	
				}
			});
			$.fn.scrollpage.loadContent(obj, opts);
		};
	
		// 默认配置
		$.fn.scrollpage.defaults = {
			'contentPage' : null, // 异步加载页面地址
			'contentNext' : null, // 动态的异步加载页面地址
			'contentData' : {}, // 异步请求数据
			'beforeLoad': null, // 初始化动作
			'afterLoad': null, // 完成后动作
			'scrollTarget': window, // 滚动对象
			'heightOffset': 0 // 高度偏移量
		};
	})(window.jQuery || require('jquery'));
});