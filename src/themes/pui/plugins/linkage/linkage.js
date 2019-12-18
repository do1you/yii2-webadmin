/**
 @Name：linkage V0.1
 @Author：统一 54901801@qq.com
 @Date：2016-8-16 
 */
define('pui/linkage',['jquery','pui/linkage.css'],function(require,exports,moudles){
	(function($){
		var box_tpl = 
				'<div class="linkage_box">' +
					'<div class="linkage_bg"></div>' +
					'<div class="linkage_main">' +
						'<div class="linkage_title">' +
							'<a href="###">取消</a>' +
							'<a href="###" class="ok">完成</a>' +
						'</div>' +
						'<div class="linkage_list">' +
							'<div class="linkage_selected"></div>' +
						'</div>' +
					'</div>' +
				'</div>';
			sel_tpl = 
				'<div class="linkage_flex">' + 
					'<div>' + 
						'<ul>' + 
						'</ul>' + 
					'</div>' + 
				'</div>';

		// 创建下拉元素
		var create_select = function(s,box){
			var s = $(s),
				opts = s.find('option'),
				sed = s.find(':selected'),
				el = $(sel_tpl),
				obox = s.data('linkage_el');

			opts.each(function(){
				var o = $(this);
				el.find('ul').append($('<li>'+o.text()+'</li>').attr('value',o.val()));
			});
			if(obox){
				obox.after(el);
				obox.remove();
			}else{
				el.appendTo(box.find('.linkage_list'));
			}
			el.data('linkage_source',s);
			selected_fn(el,sed.val());
			return el;
		},
		// 选中下拉元素	
		selected_fn = function(sel,val,_s,box){
			if(!sel) return;
			var li = sel.find('li[value='+val+']'),
				lis = sel.find('li');
				index = lis.index(li),
				hei = lis.height();
			if(index>=0){
				lis.removeAttr('linkage_sel');
				li.attr('linkage_sel','1');
				opacityFn(lis,li);
				var top = -(index - 2) * hei;
				if(_s){
					sel.find('>div').animate({'top':top},'fast');
					var currEl = sel.data('linkage_source').val(val).trigger('change');
					_s.not(currEl).each(function(){
						$(this).data('linkage_el',create_select(this,box));
					});
				}else{
					sel.find('>div').css({'top':top});
				}
			}
		},
		// 透明度
		opacityFn = function(lis,li){
			lis.css('opacity',1);
			li.prev().css('opacity',0.6).prev().css('opacity',0.4);
			li.next().css('opacity',0.6).next().css('opacity',0.4);
		};
		// 获取纵向坐标
		getY = function(e){
			return (e.originalEvent || e).changedTouches ? (e.originalEvent || e).changedTouches[0].clientY : e.clientY;
		};

		$.fn.linkage = function(){
			this.each(function(){
				var box = $(box_tpl),
					_t = $(this),
					_s = _t.find('select').hide();
				_t.append(box);
				_s.on('reset',function(){
					$(this).data('defaultOptions',$(this).find('option')).data('defaultValue',$(this).val()).data('linkage_el',create_select(this,box));
				}).trigger('reset');

				
				// 显示
				box.hide();
				_t.css('cursor','pointer').on('touchend click',function(e){
					if($(e.target).closest('.linkage_box').length<=0){
						_s.trigger('reset');
						box.find('.linkage_main').hide();
						box.fadeIn('fast',function(){
							box.find('.linkage_main').slideDown();
						});
						e.preventDefault();
						e.stopPropagation();
					}
				});

				// 取消 / 完成
				box.find('.linkage_title a').on('touchend click',function(e){
					box.find('.linkage_main').slideUp(function(){
						box.fadeOut('fast');
					});
					if($(this).hasClass('ok')){
						_s.each(function(){
							var el = $(this).data('linkage_el');
							if(el.length){
								$(this).val(el.find('li[linkage_sel=1]').attr('value')).trigger('change');								
							}
						});
					}else{
						_s.each(function(){
							$(this).empty().append($(this).data('defaultOptions')).val($(this).data('defaultValue'));
						});
					}
					e.preventDefault();
					e.stopPropagation();
				});

				// 滑动
				var moveEl,startTop,startY,endY
					hei = box.find('li').height();
				box.find('.linkage_list').on('touchstart mousedown',function(e){
					moveEl = $(e.target).closest('.linkage_flex').find('>div');
					startTop = parseInt(moveEl.css('top')) || 0;
					startY = getY(e);
					return false;
				}).on('touchmove mousemove',function(e){
					if(!moveEl) return;
					endY = getY(e);
					var top = (startTop+(endY-startY)),
						lis = moveEl.find('li'),
						len = lis.length;
					top = Math.max(top,(-(len-3)*hei));
					top = Math.min(top,(2*hei));
					moveEl.css('top',top);
					var index = Math.abs(Math.round(top/hei)-2);
					opacityFn(lis,lis.eq(index));
					return false;
				});
				$(document).on('touchend mouseup',function(e){
					if(!moveEl) return;
					var index = Math.abs(Math.round(parseInt(moveEl.css('top'))/hei)-2);
					selected_fn(moveEl.closest('.linkage_flex'),moveEl.find('li').eq(index).attr('value'),_s,box);
					moveEl = null;
				});
			});
			return this;
		};
	})(window.jQuery || require('jquery'));
});