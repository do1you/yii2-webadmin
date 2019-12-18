/**
 @Name：slideimg V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-5-6 
 */
define('pui/slideimg',['jquery','pui/slideimg.css'],function(require,exports,moudles){
	(function($){
		$.fn.slideimg = function(opts){
			opts=$.extend({
				subs : 'a:has(img)', // 子项选择器
				showBtn : true, // 是否显示左右按纽
				showLabel : true, // 是否显示数字按纽
				showText : true, // 是否显示文本标题
				isImg : true, // 是否图片轮播
				anime : 1, // 动画类型 1 左右滑动，2 上下滑动，3 淡入淡出
				soon : false,  // 是否马上执行
				skip : 1, // 每次跳跃的元素个数
				time : 5000  // 每隔几秒切换
			},opts);
			this.each(function(){
				var arr,runobj,i,el,fl,box = $('<div class="slideimg_box"><ul></ul><div class="slideimg_btnbg"></div><div class="slideimg_text"></div><div class="slideimg_btn"></div><a class="slideimg_pre"></a><a class="slideimg_next"></a></div>'),
					_t = $(this),
					subs = _t.find(opts.subs),
					len = subs.length,
					skip = opts.skip>Math.floor(len/2) ? 1 : opts.skip,
					width = opts.isImg ? _t.outerWidth() : subs.outerWidth(),
					height = opts.isImg ? _t.outerHeight() : subs.outerHeight(),
					index = opts.soon ? 1 : 0,
					oldindex = index,
					setStyle = function(oldind,ind,type2){
						var isleft,els = box.find('ul li'),len = els.length-1,w = els.outerWidth(),h = els.outerHeight();
						var i,newindex,showNum = Math.ceil(type2 ? box.outerHeight()/h : box.outerWidth()/w);
						
						// 判断动画方向
						if(ind+skip>len && oldind<skip && (len-ind)+oldind+1==skip){ // 前面到最后
							isleft = true;
						}else if(oldind+skip>len && ind<skip && (len-oldind)+ind+1==skip){ // 最后到前面
							isleft = false;
						}else{
							isleft = oldind>ind ? true : (oldind<ind ? false : null);
						}

						// 预置动画开始样式
						type2 ? els.css('top',-20000) : els.css('left',-20000);
						fl = isleft===null ? 0 : (type2 ? (isleft ? -h : h) : (isleft ? -w : w))*skip;
						arr = $().add(type2 ? els.eq(oldind).css('top',0) : els.eq(oldind).css('left',0));
						arr = arr.add(type2 ? els.eq(ind).css('top',fl) : els.eq(ind).css('left',fl));
						// 旧后置元素
						for(i=1;i<=Math.max(showNum,skip);i++){ 
							newindex = oldind+i;
							newindex = newindex>len ? newindex-len-1 : newindex;
							if(arr.index(els.eq(newindex))==-1)
								arr = arr.add(type2 ? els.eq(newindex).css('top',i*h) : els.eq(newindex).css('left',i*w));
						}
						// 新后置元素
						for(i=1;i<=skip;i++){ 
							newindex = ind+i;
							newindex = newindex>len ? newindex-len-1 : newindex;
							if(arr.index(els.eq(newindex))==-1)
								arr = arr.add(type2 ? els.eq(newindex).css('top',i*h+fl) : els.eq(newindex).css('left',i*w+fl));
						}
						return isleft;
					};

				// 组装HTML
				for(i=0;i<len;i++){
					el = $(subs[i]).is('li') ? $(subs[i]) : $('<li></li>').append(subs[i]);
					box.find('ul').append(el);
					box.find('.slideimg_btn').append('<span>'+(i+1)+'</span>');
				}
				_t.after(box.width(_t.outerWidth()).height(_t.outerHeight()).addClass(_t.attr('class'))).remove();
				box.find('ul').addClass('anime'+opts.anime).find(opts.isImg ? 'li,a,img' : '').width(width).height(height);

				// 元素显示
				opts.showText || box.find('.slideimg_text,.slideimg_btnbg').hide();
				opts.showLabel || box.find('.slideimg_btn').hide();
				opts.showBtn || box.find('.slideimg_pre,.slideimg_next').hide();

				// 左右箭头动画
				box.hover(function(){
					box.find('.slideimg_pre').stop().animate({"opacity":"0.5","margin-left":"65"});
					box.find('.slideimg_next').stop().animate({"opacity":"0.5","margin-left":"-65px"});
				},function(){
					box.find('.slideimg_pre,.slideimg_next').stop().animate({"opacity":"0.1","margin-left":"0"});
				});

				// 切换动画
				box.bind('slideimg',function(e){
					runobj && clearTimeout(runobj);
					index = index<0 ? len+index : index;
					index = index>=len ? index-len : index;
					var left = index*width,
						top = index*height,
						currEl = box.find('li a').eq(index),
						imgEl = currEl.find('img');
					box.find(".slideimg_btn span").removeClass('active').eq(index).addClass('active');
					box.find('.slideimg_text').html(currEl.attr('title') || imgEl.attr('title') || imgEl.attr('alt') || '');
					switch(''+opts.anime){ // 执行动画
						case '2': // 上下滑动
							var istop = setStyle(oldindex,index,true);
							(istop===true || istop===false) && box.find('ul li').stop().animate({"top":((istop?'+=':'-=')+height*skip+'px')});
						break;
						case '3': // 淡入淡出
							box.find('ul li').stop().fadeOut(1500).eq(index).stop().fadeIn(1500);
						break;
						case '1': // 左右滑动
						default: // 默认
							var isleft = setStyle(oldindex,index);
							(isleft===true || isleft===false) && box.find('ul li').stop().animate({"left":((isleft?'+=':'-=')+width*skip+'px')});
						break;
					}
					
					oldindex = index;
					runobj = setTimeout(function(){
						index += (skip || 1);
						box.trigger('slideimg');
					},opts.time);
				});

				// 左右切换
				box.find('.slideimg_pre,.slideimg_next').click(function(){
					$(this).hasClass('slideimg_pre') ? (index-=skip) : (index+=skip);
					box.trigger('slideimg');
					return false;
				});

				// 数字切换
				box.find(".slideimg_btn span").hover(function(){
					$(this).toggleClass('on');
				}).click(function(){
					index = box.find(".slideimg_btn span").index(this);
					box.trigger('slideimg');
				});
		
				box.trigger('slideimg'); // 执行第一次动画
			});
		};
	})(window.jQuery || require('jquery'));
});