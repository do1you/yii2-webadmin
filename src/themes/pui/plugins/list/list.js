/**
 @Name：list V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-5-11 
 */
define('pui/list',['jquery','pui/list.css'],function(require,exports,moudles){
	(function($){
		$.fn.list = function(){
			this.each(function(){
				var _this = $(this).addClass('list_box');
				_this.find('>tbody>tr:odd').addClass('trbg');
				_this.mousemove(function(e){
					_this.mouseout();
					$(e.target).parents('tr:first').addClass('hover');
				}).mouseout(function(){
					_this.find('tr.hover').removeClass('hover');
				});
				_this.find('>tbody>tr>td').each(function(){
					var t = $(this),
						html = t.contents(),
						div = $('<div class="tdcon"></div>');
					t.html(div.html(html));
					div.width()>400 && div.width(400);
					div.height()>66 && div.height(66);
				});
			});
		};
	})(window.jQuery || require('jquery'));
});