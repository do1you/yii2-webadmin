/**
 @Name：tooltip V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-5-4 
 */
define('pui/tooltip',['jquery'],function(require,exports,moudles){
	(function($){
		$.fn.tooltip = function(opts){
			if(typeof opts=='string') opts = {title:opts};
			opts = $.extend({
				align : 'bm', // 气泡提示的位置
				ajax : null, // 加载远程的内容
				event : 'mouseenter', // 事件类型,click
				color : '' // 包含提示文字的字体颜色
			},(opts||{}));
			this.unbind(opts.event+' '+'mouseleave clearHide')[opts.event](function(){
				if(opts.hide && !opts.show) return false;
				var tiptop,tipleft,top,left,css,_t=$(this),
					isHide=_t.is(':hidden'),
					offset=_t.show().offset(),
					width=_t.outerWidth(),height=_t.outerHeight(),
					title = opts.ajax ? 'Loading...' : (opts.title || _t.attr('title') || ''),
					tipEl = _t.data('tooltipDiv') || $('<div class="tips" style="left:0;top:0;z-index:1030;position:absolute;border:1px solid #FFBB76;border-radius:4px;background-color:#FFFCEF;padding:5px 8px;"><s style="width:0;height:0;font-size:0px;line-height:0px;position:absolute;top:-10px;left:10px;border:5px dashed transparent;"></s><div style="line-height:16px;color:#DE8734;font-size:12px;"></div></div>').appendTo('body'),
					s=tipEl.find('>div').html(title),
					tipwidth=tipEl.show().outerWidth(),
					tipheight=tipEl.outerHeight();
				isHide && _t.hide();
				_t.trigger('clearHide');
				if(opts.ajax){
					$.ajax({
						url : opts.ajax,
						global : false,
						success : function(html){
							opts.title = html;
							_t.trigger(opts.event);
						}
					});
					opts.ajax = null;
				}
				switch(opts.align){ // 计算位置
					case 'tl': // 上左
						top = offset.top-tipheight-13;
						left = offset.left-tipwidth+20;
						tiptop = tipheight-1;
						tipleft = tipwidth-20;
						css = {'border-top':'5px solid #FFBB76'};
					break;
					case 'tm': // 上中
						top = offset.top-tipheight-13;
						left = offset.left+width/2-tipwidth/2;
						tiptop = tipheight-1;
						tipleft = (tipwidth-10)/2;
						css = {'border-top':'5px solid #FFBB76'};
					break;
					case 'tr': // 上右
						top = offset.top-tipheight-13;
						left = offset.left+width-20;
						tiptop = tipheight-1;
						tipleft = 10;
						css = {'border-top':'5px solid #FFBB76'};
					break;
					case 'ml': // 中左
						top = offset.top+(height-tipheight)/2;
						left = offset.left-tipwidth-13;
						tiptop = (tipheight-10)/2;
						tipleft = tipwidth-1;
						css = {'border-left':'5px solid #FFBB76'};
					break;
					case 'mr': // 中右
						top = offset.top+(height-tipheight)/2;
						left = offset.left+width+13;
						tiptop = (tipheight-10)/2;
						tipleft = -10;
						css = {'border-right':'5px solid #FFBB76'};
					break;
					case 'bl': // 下左
						top = offset.top+height+13;
						left = offset.left-tipwidth+20;
						tiptop = -10;
						tipleft = tipwidth-20;
						css = {'border-bottom':'5px solid #FFBB76'};
					break;
					case 'bm': // 下中
						top = offset.top+height+13;
						left = offset.left+width/2-tipwidth/2;
						tiptop = -10;
						tipleft = (tipwidth-10)/2;
						css = {'border-bottom':'5px solid #FFBB76'};
					break;
					case 'br': // 下右
					default: // 默认
						top = offset.top+height+13;
						left = offset.left+width-20;
						tiptop = -10;
						tipleft = 10;
						css = {'border-bottom':'5px solid #FFBB76'};
					break;
				}
				tipEl.css({left:left,top:top}).find('>s').css({left:tipleft,top:tiptop}).css(css);
				_t.data('tooltipDiv',tipEl);
			}).mouseleave(function(){
				if(opts.show && !opts.hide) return false;
				var _t = $(this),
					tipEl = _t.data('tooltipDiv');
				_t.trigger('clearHide');
				if(tipEl){
					_t.data('tooltipAuto',setTimeout(function(){
						tipEl.hide();
					},200));
					tipEl.mousemove(function(){
						_t.trigger('clearHide');
					}).mouseleave(function(){
						_t.trigger('mouseleave');
					});
				}
			}).bind('clearHide',function(){
				var tooltipAuto = $(this).data('tooltipAuto');
				tooltipAuto && clearTimeout(tooltipAuto);
			});
			opts.color && this.css({'cursor':'default','color':opts.color});
			
			var _this = this;
			setTimeout(function(){
				opts.show && _this[opts.event]();
				opts.hide && _this.mouseleave();
			},50);

			return this.data('tooltipDiv');
		};

		$(document).bind('click',function(){
			$('body>.tips').hide();
		});
	})(window.jQuery || require('jquery'));
});