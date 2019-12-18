/**
 @Name：startimg V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-9-28 
 */
define('pui/startimg',['jquery'],function(require,exports,moudles){
	(function($){
		$.fn.startimg = function(opts){
			if(typeof opts=='string') opts = {url:opts};
			opts=$.extend({
				url:'?', // 完成后跳转地址
				time:2500  // 每隔几秒切换
			},opts);
			this.each(function(){
				// 初始样式
				var box = $(this),
					childs = box.find('*'),
					imgs = box.find('img');
				childs.css({ // 子容器样式
					display:'block',width:'100%',height:'100%',margin:'0px',padding:'0px'
				});
				imgs.css({ // 图片样式
					position:'absolute',left:'0px',top:'0px',border:'0px'
				});

				// 按纽
				box.append('<div class="startimg_btn"></div>');
				for(i=0;i<imgs.length;i++){
					box.find('.startimg_btn').append('<span>'+(i+1)+'</span>');
				}
				box.find('.startimg_btn').css({
					position:'absolute',left:'0px',bottom:'10px',width:'100%',textAlign:'center'
				}).find('span').css({
					'display':'inline-block','zoom':'1','*display':'inline','background':'#337ab7','margin':'0 3px 0 3px','border-radius':'50%','font-size':'0px','line-height':'0px','width':'8px','height':'8px'
				});

				// 预定义变量
				var runobj,moveStatus,startX,endX,nextIndex=0,oldindex=0,
					width = box.width(),
					getx = function(e){
						return (e.originalEvent || e).changedTouches ? (e.originalEvent || e).changedTouches[0].clientX:e.clientX;
					},
					setStyle = function(oldind,ind){ // 预动画前样式
						var len = imgs.length;
						imgs.each(function(curr){
							if(oldind!=curr){
								$(this).css('left',-width*(oldind-curr));
							}
						});
						imgs.eq(oldind).css('left',0);
						imgs.eq(ind).css('left',width);
					};

				// 切换动画
				setStyle(oldindex,oldindex+1);
				box.bind('startimg',function(e){
					runobj && clearTimeout(runobj);
					if(nextIndex>=imgs.length){
						location.href = opts.url;
						return false;
					}

					if(nextIndex!=oldindex){
						// 计算动画
						var floatLeft = -parseFloat(imgs.eq(oldindex).css('left'));
						imgs.stop().animate({"left":('-='+(width-floatLeft)+'px')});
						oldindex = nextIndex;
					}
					box.find(".startimg_btn span").css('background','#337ab7').eq(oldindex).css('background','#ffffff');
					runobj = setTimeout(function(){
						nextIndex++;
						box.trigger('startimg');
					},opts.time);
				});

				// 绑定事件
				box.bind('touchstart mousedown',function(e){
					runobj && clearTimeout(runobj);
					moveStatus = true;
					startX = getx(e);
					return false;
				}).bind('touchmove mousemove',function(e){
					if(!moveStatus) return;
					endX = getx(e);
					var x = endX-startX;
					if(x<0){
						nextIndex = oldindex+1;
						if(nextIndex<imgs.length){
							setStyle(oldindex,nextIndex);
							imgs.css('left',('+='+x));
						}
					}
					return false;
				});
				$(document).bind('touchend mouseup',function(e){
					if(!moveStatus) return;
					moveStatus = false;
					box.trigger('startimg');
					var a = imgs.eq(oldindex).parents('a:first');
					if(a.length){
						if(a.attr('target')=='_blank'){
							window.open(a.attr('href'));
						}else{
							location.href = a.attr('href');
						}
					}
					return false;
				});
				box.find('a').click(function(){return false;});
				box.trigger('startimg');
			}).css({ // box 容器样式
				display:'block',
				overflow:'hidden',
				position:'relative',
				padding:'0px'
			});
		};
	})(window.jQuery || require('jquery'));
});