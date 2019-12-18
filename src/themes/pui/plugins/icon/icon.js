/**
 @Name：icon V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-6-24 
 */
define('pui/icon',['jquery','pui/icon.css'],function(require,exports,moudles){
	(function($){
		$.fn.icon = function(opt){
			opt = opt || 'default';
			var src = pui.resolve('pui/icon'),
				bashPath = src.substring(0, src.lastIndexOf('/') + 1)+'images/';

			this.addClass('puiicon');
			opt.indexOf('.')==-1 ? this.addClass('i_'+opt) :  this.css('background-image','url('+bashPath+opt+')');
		};
	})(window.jQuery || require('jquery'));
});