/**
 @Name：slidebox V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-6-8 
 */
define('pui/slidebox',['jquery','pui/mask'],function(require,exports,moudles){
	(function($,U){
		var getx = function(e){
				return (e.originalEvent || e).changedTouches ? (e.originalEvent || e).changedTouches[0].clientX : e.clientX;
			},
			gety = function(e){
				return (e.originalEvent || e).changedTouches ? (e.originalEvent || e).changedTouches[0].clientY : e.clientY;
			},
			slideEl,slideTypes={isleft:1,isright:2,istop:3,isbottom:4},
			donefn1 = function(){
				slideEl=null;
				$('.slideboxOpen').hide().removeClass('slideboxOpen');
				U.unmask('body');
			};

		$.fn.slidebox = function(options){
			if(this.length<=0) return;
			if(typeof options=='string') options={position:options};
			options=$.extend({
				destroy : false, // 销毁以外
				target : document, // 触发对象
				position : 'isleft' // 位置，isleft isright istop isbottom
			},options);

			// 销毁其他的容器
			if(options.destroy){
				$('.slideboxClass').trigger('slideboxDestroy');
			}

			var boxEl = $(this).addClass('slideboxClass'),
				targetEl = $(options.target || document),
				start_x,start_y,move_x,move_y,xytype,moveStatus,slideStatus,
				winwidth = $(window).width(),
				winheight = $(window).height();

			// 默认样式
			boxEl.css({'position':'fixed','overflow':'auto','margin':'0','padding':'0','zIndex':'999991'}).hide().appendTo('body');
			switch(options.position){
				case 'isleft': // 左侧
					boxEl.css({'left':-boxEl.outerWidth(),'height':winheight,'top':0});
				break;
				case 'isright': // 右侧
					boxEl.css({'right':-boxEl.outerWidth(),'height':winheight,'top':0});
				break;
				case 'istop': // 顶部
					boxEl.css({'top':-boxEl.outerHeight(),'width':winwidth,'left':0});
				break;
				case 'isbottom': // 底部
					boxEl.css({'bottom':-boxEl.outerHeight(),'width':winwidth,'left':0});
				break;
			}

			// 绑定事件
			targetEl.bind('touchstart mousedown',function(e){
				if(!$(e.target).is('input,textarea,select')){
					moveStatus = true;
					start_x = getx(e);
					start_y = gety(e);
				}
			}).bind('touchmove mousemove',function(e){
				if(!moveStatus || (slideEl && slideEl!=boxEl)) return;
				move_x = getx(e)-start_x;
				move_y = gety(e)-start_y;
				slideStatus = false;
				if(Math.abs(move_x)>0 || Math.abs(move_y)>0){
					try{window.getSelection ? window.getSelection().removeAllRanges() : document.selection.empty()}catch(e){}
					xytype = Math.abs(move_x)>Math.abs(move_y); // 横竖向的滑动幅度比较
					if(boxEl.hasClass('slideboxOpen')){ // 属于打开状态
						switch(options.position){
							case 'isleft': // 左侧
								if(xytype && move_x<0){
									boxEl.show().css('left',Math.max(move_x,-boxEl.outerWidth())).css('top',0);
									slideStatus = 1;
								}
							break;
							case 'isright': // 右侧
								if(xytype && move_x>0){
									boxEl.show().css('right',Math.max(-move_x,-boxEl.outerWidth())).css('top',0);
									slideStatus = 2;
								}
							break;
							case 'istop': // 顶部
								if(!xytype && move_y<0){
									boxEl.show().css('top',Math.max(move_y,-boxEl.outerHeight())).css('left',0);
									slideStatus = 3;
								}
							break;
							case 'isbottom': // 底部
								if(!xytype && move_y>0){
									boxEl.show().css('bottom',Math.max(-move_y,-boxEl.outerHeight())).css('left',0);
									slideStatus = 4;
								}
							break;
						}
					}else{
						switch(options.position){
							case 'isleft': // 左侧
								if(xytype && move_x>0){
									boxEl.show().css('left',Math.min(-boxEl.outerWidth()+move_x,0)).css('top',0);
									slideStatus = 5;
								}
							break;
							case 'isright': // 右侧
								if(xytype && move_x<0){
									boxEl.show().css('right',Math.min(-boxEl.outerWidth()-move_x,0)).css('top',0);
									slideStatus = 6;
								}
							break;
							case 'istop': // 顶部
								if(!xytype && move_y>0){
									boxEl.show().css('top',Math.min(-boxEl.outerHeight()+move_y,0)).css('left',0);
									slideStatus = 7;
								}
							break;
							case 'isbottom': // 底部
								if(!xytype && move_y<0){
									boxEl.show().css('bottom',Math.min(-boxEl.outerHeight()-move_y,0)).css('left',0);
									slideStatus = 8;
								}
							break;
						}
					}
				}
				if(slideStatus){
					slideEl = boxEl;
					$( U('body','')).on('click',function(){boxEl.trigger('slideboxHide');});
					return false;
				}
				return;
			});
			$(document).bind('touchend mouseup',function(e){
				if(!moveStatus || !slideEl || slideEl!=boxEl){
					if(slideEl==boxEl) boxEl.trigger('slideboxHide');
					slideStatus = moveStatus = false;return;
				}
				
				if(Math.max(Math.abs(move_x),Math.abs(move_y))<(xytype ? boxEl.width() : boxEl.height())/5){ // 未达到五分之一关闭
					boxEl.hasClass('slideboxOpen') ? boxEl.trigger('slideboxShow') : boxEl.trigger('slideboxHide');
				}else{
					switch(slideStatus){
						case 1: // 左侧关闭
						case 2: // 右侧关闭
						case 3: // 上侧关闭
						case 4: // 下侧关闭
							boxEl.trigger('slideboxHide'); break;
						case 5: // 左侧打开
						case 6: // 右侧打开
						case 7: // 上侧打开
						case 8: // 下侧打开
							boxEl.trigger('slideboxShow'); break;
						default: // 恢复
							boxEl.hasClass('slideboxOpen') ? boxEl.trigger('slideboxShow') : boxEl.trigger('slideboxHide');
						break;
					}
				}
			});
			boxEl.bind('slideboxShow',function(){ // 展示元素
				$( U('body','')).on('click',function(){boxEl.trigger('slideboxHide');});
				boxEl.show().addClass('slideboxOpen').stop();
				switch(options.position){
					case 'isleft': // 左侧
						boxEl.animate({left : 0});
					break;
					case 'isright': // 右侧
						boxEl.animate({right : 0});
					break;
					case 'istop': // 顶部
						boxEl.animate({top : 0});
					break;
					case 'isbottom': // 底部
						boxEl.animate({bottom : 0});
					break;
				}
				slideStatus = moveStatus = false;
			}).bind('slideboxHide',function(){ // 隐藏元素
				boxEl.stop();
				switch(options.position){
					case 'isleft': // 左侧
						boxEl.animate({left : -boxEl.outerWidth()},donefn1);
					break;
					case 'isright': // 右侧
						boxEl.animate({right : -boxEl.outerWidth()},donefn1);
					break;
					case 'istop': // 顶部
						boxEl.animate({top : -boxEl.outerHeight()},donefn1);
					break;
					case 'isbottom': // 底部
						boxEl.animate({bottom : -boxEl.outerHeight()},donefn1);
					break;
				}
				slideStatus = moveStatus = false;
			}).bind('slideboxToggle',function(){
				if(boxEl.hasClass('slideboxOpen')){
					boxEl.trigger('slideboxHide');
				}else{
					boxEl.trigger('slideboxShow');
				}
			}).bind('slideboxDestroy',function(){
				$('.slideboxClass').trigger('slideboxHide').unbind().remove();
				targetEl.unbind('touchstart mousedown touchmove mousemove');
			});
			return this;
		};
	})(require('jquery'),(window.jQuery || require('jquery')));
});