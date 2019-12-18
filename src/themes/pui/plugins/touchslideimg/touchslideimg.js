/**
 @Name：touchslideimg V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-5-6 
 */
define('pui/touchslideimg',['jquery','pui/touchslideimg.css'],function(require,exports,moudles){
	(function($){
		$.fn.touchslideimg = function(opts){
			opts=$.extend({
				soon : false,  // 是否马上执行
				time : 5000  // 每隔几秒切换
			},opts);
			this.each(function(){
				var runobj,i,el,box = $('<div class="touchslideimg_box"><ul></ul><div class="touchslideimg_btnbg"></div><div class="touchslideimg_text"></div><div class="touchslideimg_btn"></div><a class="touchslideimg_pre"></a><a class="touchslideimg_next"></a></div>'),
					_t = $(this),
					width = _t.outerWidth(),
					height = _t.outerHeight(),
					subs = _t.find('a:has(img)'),
					len = subs.length,
					index = opts.soon ? 1 : 0,
					oldindex = index,
					setStyle = function(oldind,ind,goon){
						var isleft,els = box.find('ul li'),len = els.length-1,
							defleft=goon?parseFloat(els.eq(oldind).css('left')):0;
						els.each(function(curr){
							if(oldind!=curr){
								$(this).css('left',-width*(oldind-curr)+defleft);
							}
						});
						els.eq(oldind).css('left',defleft);
						if(oldind==0 && ind==len){ // 第一张到最后一张
							isleft = true;
							els.eq(ind).css('left',-width+defleft);
						}else if(oldind==len && ind==0){ // 最后一张到第一张
							isleft = false;
							els.eq(ind).css('left',width+defleft);
						}else{
							isleft = oldind>ind ? true : (oldind<ind ? false : null);
							if(isleft!==null) els.eq(ind).css('left',(isleft ? -width : width)+defleft);
						}
						return isleft;
					};
		

				// 组装HTML
				for(i=0;i<len;i++){
					el = $('<li></li>');
					box.find('ul').append(el.append(subs[i]));
					box.find('.touchslideimg_btn').append('<span>'+(i+1)+'</span>');
				}
				_t.after(box.width(width).height(height)).remove();
				box.find('ul').find('li,a,img').width(width).height(height);

				// 切换动画
				box.bind('touchslideimg',function(e,goon){
					runobj && clearTimeout(runobj);
					index = index<0 ? len-1 : index;
					index = index>=len ? 0 : index;

					// 设置btn和text
					var currEl = box.find('li a').eq(index),
						imgEl = currEl.find('img');
					box.find(".touchslideimg_btn span").removeClass('active').eq(index).addClass('active');
					box.find('.touchslideimg_text').html(currEl.attr('title') || imgEl.attr('title') || imgEl.attr('alt') || '');
					
					// 计算动画
					var isleft = setStyle(oldindex,index,goon),
						floatLeft = goon ? -parseFloat(box.find('ul li').eq(oldindex).css('left')) : 0;
					(isleft===true || isleft===false) && box.find('ul li').stop().animate({"left":((isleft?'+=':'-=')+(isleft ? width+floatLeft : width-floatLeft)+'px')});

					oldindex = index;
					runobj = setTimeout(function(){
						index++;
						box.trigger('touchslideimg');
					},opts.time);
				});

				// 左右切换
				box.find('.touchslideimg_pre,.touchslideimg_next').click(function(){
					$(this).hasClass('touchslideimg_pre') ? index-- : index++;
					box.trigger('touchslideimg');
					return false;
				});

				// 数字切换
				box.find(".touchslideimg_btn span").click(function(){
					index = box.find(".touchslideimg_btn span").index(this);
					box.trigger('touchslideimg');
					return false;
				});
				box.find('>div,>a').bind('touchstart mousedown touchmove mousemove touchend mouseup',function(){return false;});

				// 拖动切换
				var moveStatus,startX,endX,nextIndex,
					getx = function(e){
						return (e.originalEvent || e).changedTouches ? (e.originalEvent || e).changedTouches[0].clientX : e.clientX;
					};
				box.bind('touchstart mousedown',function(e){
					runobj && clearTimeout(runobj);
					moveStatus = true;
					startX = getx(e);
					return false;
				}).bind('touchmove mousemove',function(e){
					if(!moveStatus) return;
					endX = getx(e);
					var x = endX-startX,
						els = box.find('ul li');
					nextIndex = x>0 ? oldindex-1 : oldindex+1;
					nextIndex = nextIndex<0 ? els.length-1 : nextIndex;
					nextIndex = nextIndex>=els.length ? 0 : nextIndex;
					setStyle(oldindex,nextIndex);
					els.css('left',('+='+x));
					return false;
				});
				$(document).bind('touchend mouseup',function(e){
					if(!moveStatus) return;
					moveStatus = false;
					endX = getx(e);
					var x = Math.abs(endX-startX);
					if(x>2){ // 切换
						index = nextIndex;
						box.trigger('touchslideimg',[true]);
					}else{ // 跳转
						var a = box.find('ul li a').eq(oldindex);
						if(a.attr('target')=='_blank'){
							window.open(a.attr('href'));
						}else{
							location.href = a.attr('href');
						}						
					}
					return false;
				});

				box.find('a').click(function(){return false;});
				box.trigger('touchslideimg'); // 执行第一次动画
			});
			
		};
	})(window.jQuery || require('jquery'));
});