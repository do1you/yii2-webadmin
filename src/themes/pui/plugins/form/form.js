/**
 @Name：form V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-5-14 
 */
define('pui/form',['jquery','pui/form.css'],function(require,exports,moudles){
	(function($){
		var defaultOptions = {}; // 默认配置

		// 获取标签
		var jqTransformGetLabel = function(objfield){
			var selfForm = $(objfield.get(0).form);
			var oLabel = objfield.next();
			if(!oLabel.is('label')) {
				oLabel = objfield.prev();
				if(oLabel.is('label')){
					var inputname = objfield.attr('id');
					if(inputname){
						oLabel = selfForm.find('label[for="'+inputname+'"]');
					} 
				}
			}
			if(oLabel.is('label')){return oLabel.css('cursor','pointer');}
			return false;
		};

		// 获取单选框和多选框的容器
		var jqTransformGetLink = function(objfield,aLink){
			var oLabel,
				wrapSel = '.jqTransformCheckboxWrapper,.jqTransformRadioWrapper',
				linkSel = '>.jqTransformRadio,>.jqTransformCheckbox',
				selfForm = $(objfield).parents('form:first');
			if((oLabel = objfield.find(wrapSel)) && oLabel.length<=0){
				if((oLabel = objfield.parent()) && !oLabel.is(wrapSel)){
					if((oLabel = objfield.prev()) && !oLabel.is(wrapSel)) {
						if((oLabel = objfield.next()) && oLabel.is(wrapSel)){
							var inputname = objfield.attr('for');
							if(inputname){
								var wraper = selfForm.find('input#'+inputname).parents(wrapSel);
								oLabel = wraper.length ? wraper : oLabel;
							} 
						}
					}
				}
			}
			
			if(oLabel.is(wrapSel)){
				var link = oLabel.find(linkSel);
				aLink = link.length ? link : aLink;
			}
			return aLink;
		};
		
		// 隐藏所有下拉框
		var jqTransformHideSelect = function(oTarget){
			var ulVisible = $('.jqTransformSelectWrapper ul:visible');
			ulVisible.each(function(){
				var oSelect = $(this).parents(".jqTransformSelectWrapper:first").find("select").get(0);
				//do not hide if click on the label object associated to the select
				if( !(oTarget && oSelect.oLabel && oSelect.oLabel.get(0) == oTarget.get(0)) ){
					$(this).hide();
					$(this).parents('.jqTransformSelectWrapper_focus').removeClass('jqTransformSelectWrapper_focus');
				}
			});
		};

		// 文档监听事件
		var jqTransformAddDocumentListener = function(event) {
			if ($(event.target).parents('.jqTransformSelectWrapper').length === 0) { jqTransformHideSelect($(event.target)); }
		};	
				
		// reset事件
		var jqTransformReset = function(f){
			var sel;
			$('.jqTransformSelectWrapper select', f).each(function(){sel = (this.selectedIndex<0) ? 0 : this.selectedIndex; $('ul', $(this).parent()).each(function(){$('a:eq('+ sel +')', this).click();});});
			$('a.jqTransformCheckbox, a.jqTransformRadio', f).removeClass('jqTransformChecked');
			$('input:checkbox, input:radio', f).each(function(){if(this.checked){$('a', $(this).parent()).addClass('jqTransformChecked');}});
		};

		// 按纽美化
		$.fn.jqTransInputButton = function(){
			return this.each(function(){
				var _this = $(this),
					newBtn = $('<button id="'+ this.id +'" name="'+ this.name +'" type="'+ 'button' +'" class="'+ this.className +' jqTransformButton"><span><span>'+ $(this).attr('value') +'</span></span>')
					.hover(function(){newBtn.addClass('jqTransformButton_hover');},function(){newBtn.removeClass('jqTransformButton_hover')})
					.mousedown(function(){newBtn.addClass('jqTransformButton_click')})
					.mouseup(function(){newBtn.removeClass('jqTransformButton_click')})
					.click(function(){_this.trigger("click");})
					.mouseleave(function(){_this.trigger("mouseleave");})
					.mouseenter(function(){_this.trigger("mouseenter");});
				_this.hide().after(newBtn);
				_this.is('[readonly]') ? newBtn.addClass('readonly') : newBtn.removeClass('readonly');
				_this.is('[disabled]') ? newBtn.addClass('disabled') : newBtn.removeClass('disabled');
			});
		};
		
		// 文本框美化
		$.fn.jqTransInputText = function(){
			return this.each(function(){
				var $input = $(this);
		
				if($input.hasClass('jqtranformdone') || !$input.is('input')) {return;}
				$input.addClass('jqtranformdone');
		
				var oLabel = jqTransformGetLabel($(this));
				oLabel && oLabel.bind('click',function(){$input.focus();return false;});
		
				var padding = parseInt($input.css('padding-right')), // 右侧小图标
					inputSize=($input.is(':hidden') ? 150 : ($input.outerWidth() || parseInt($input.css('width')) || 150))-16;
				if($input.attr('size')){
					inputSize = $input.attr('size')*10-16;
				}
				$input.addClass("jqTransformInput").wrap('<div class="jqTransformInputWrapper"><div class="jqTransformInputInner"><div></div></div></div>');
				var $wrapper = $input.parent().parent().parent();
				$wrapper.css("width", inputSize+12);
				$input
					.width(inputSize-padding)
					.attr('autocomplete','off')
					.focus(function(){$wrapper.addClass("jqTransformInputWrapper_focus");})
					.blur(function(){$wrapper.removeClass("jqTransformInputWrapper_focus");})
					.hover(function(){$wrapper.addClass("jqTransformInputWrapper_hover");},function(){$wrapper.removeClass("jqTransformInputWrapper_hover");});
		
				$input.is('[readonly]') ? $wrapper.addClass('readonly') : $wrapper.removeClass('readonly');
				$input.is('[disabled]') ? $wrapper.addClass('disabled') : $wrapper.removeClass('disabled');

				/* If this is safari we need to add an extra class */
				$.browser.safari && $wrapper.addClass('jqTransformSafari');
				$.browser.safari && $input.css('width',$wrapper.width()+16);
				this.wrapper = $wrapper;
			});
		};
		
		// 多选框美化
		$.fn.jqTransCheckBox = function(){
			return this.each(function(){
				if($(this).hasClass('jqTransformHidden')) {return;}

				var $input = $(this);
				var inputSelf = this;

				//set the click on the label
				var aLink,oLabel=jqTransformGetLabel($input);
				oLabel && oLabel.click(function(){
					var link = jqTransformGetLink($(this),aLink);
					link && link.trigger('click');
					return false;
				});
				
				aLink = $('<a href="#" class="jqTransformCheckbox"></a>');
				$input.addClass('jqTransformHidden').wrap('<span class="jqTransformCheckboxWrapper"></span>').before(aLink);
				$input.change(function(){
					var link = jqTransformGetLink($(this),aLink);
					link && (this.checked ? link.addClass('jqTransformChecked') : link.removeClass('jqTransformChecked'));
					return true;
				});
				aLink.click(function(){
					var input = $(this).next();
					input = input.is('input[type=checkbox]') ? input : $input;
					if(input.attr('disabled')){return false;}
					input.trigger('click').trigger("change");	
					return false;
				});
				// set the default state
				this.checked && aLink.addClass('jqTransformChecked');
				$input.is('[readonly]') ? aLink.addClass('readonly') : aLink.removeClass('readonly');
				$input.is('[disabled]') ? aLink.addClass('disabled') : aLink.removeClass('disabled');
			});
		};

		// 单选框美化
		$.fn.jqTransRadio = function(){
			return this.each(function(){
				if($(this).hasClass('jqTransformHidden')) {return;}

				var $input = $(this);
				var inputSelf = this;
					
				var aLink,oLabel = jqTransformGetLabel($input);
				oLabel && oLabel.click(function(){
					var link = jqTransformGetLink($(this),aLink);
					link && link.trigger('click');
					return false;
				});
		
				aLink = $('<a href="#" class="jqTransformRadio" rel="'+ this.name +'"></a>');
				$input.addClass('jqTransformHidden').wrap('<span class="jqTransformRadioWrapper"></span>').before(aLink);
				
				$input.change(function(){
					var link = jqTransformGetLink($(this),aLink);
					link && (inputSelf.checked ? link.addClass('jqTransformChecked') : link.removeClass('jqTransformChecked'));
					return true;
				});
				aLink.click(function(){
					var input = $(this).next();
					input = input.is('input[type=radio]') ? input : $input;
					if(input.attr('disabled')){return false;}
					input.trigger('click').trigger('change');
					$('input[name="'+input.attr('name')+'"]',input[0].form).not(input).each(function(){
						$(this).attr('type')=='radio' && $(this).trigger('change');
					});
					return false;					
				});
				// set the default state
				inputSelf.checked && aLink.addClass('jqTransformChecked');
				$input.is('[readonly]') ? aLink.addClass('readonly') : aLink.removeClass('readonly');
				$input.is('[disabled]') ? aLink.addClass('disabled') : aLink.removeClass('disabled');
			});
		};
		
		// 多行文本框美化
		$.fn.jqTransTextarea = function(){
			return this.each(function(){
				var textarea = $(this);
		
				if(textarea.hasClass('jqtransformdone')) {return;}
				textarea.addClass('jqtransformdone');
		
				oLabel = jqTransformGetLabel(textarea);
				oLabel && oLabel.click(function(){textarea.focus();return false;});

				textarea.is(':hidden') || textarea.width(textarea.outerWidth()-16).height(textarea.outerHeight()-16);
				var strTable = '<table cellspacing="0" cellpadding="0" border="0" class="jqTransformTextarea">';
				strTable +='<tr><td id="jqTransformTextarea-tl"></td><td id="jqTransformTextarea-tm"></td><td id="jqTransformTextarea-tr"></td></tr>';
				strTable +='<tr><td id="jqTransformTextarea-ml">&nbsp;</td><td id="jqTransformTextarea-mm"><div></div></td><td id="jqTransformTextarea-mr">&nbsp;</td></tr>';	
				strTable +='<tr><td id="jqTransformTextarea-bl"></td><td id="jqTransformTextarea-bm"></td><td id="jqTransformTextarea-br"></td></tr>';
				strTable +='</table>';					
				var oTable = $(strTable)
						.insertAfter(textarea)
						.hover(function(){
							!oTable.hasClass('jqTransformTextarea-focus') && oTable.addClass('jqTransformTextarea-hover');
						},function(){
							oTable.removeClass('jqTransformTextarea-hover');					
						});
					
				textarea
					.focus(function(){oTable.removeClass('jqTransformTextarea-hover').addClass('jqTransformTextarea-focus');})
					.blur(function(){oTable.removeClass('jqTransformTextarea-focus');})
					.appendTo($('#jqTransformTextarea-mm div',oTable));
				textarea.is('[readonly]') ? oTable.addClass('readonly') : oTable.removeClass('readonly');
				textarea.is('[disabled]') ? oTable.addClass('disabled') : oTable.removeClass('disabled');
				
				this.oTable = oTable;
				if($.browser.safari){
					$('#jqTransformTextarea-mm',oTable)
						.addClass('jqTransformSafariTextarea')
						.find('div')
							.css('height',(textarea.height() || parseInt(textarea.css('width')) || 150))
							.css('width',(textarea.width() || parseInt(textarea.css('height')) || 50))
					;
				}
			});
		};
		
		// 下拉框美化
		$.fn.jqTransSelect = function(){
			$.fn.jqTransSelect.zIndex = !$.fn.jqTransSelect.zIndex||$.fn.jqTransSelect.zIndex<1 ? 89 : $.fn.jqTransSelect.zIndex;
			this.each(function(index){
				var $select = $(this);

				if($select.hasClass('jqTransformHidden')) {return;}
				if($select.attr('multiple')) {return;}

				var oLabel  =  jqTransformGetLabel($select);
				var $wrapper = $select
					.addClass('jqTransformHidden')
					.wrap('<div class="jqTransformSelectWrapper"></div>')
					.parent()
					.css({zIndex: Math.max(1,$.fn.jqTransSelect.zIndex--)});

				$wrapper.prepend('<div><span></span><a href="#" class="jqTransformSelectOpen"></a></div><ul></ul>');
				var $ul = $('ul', $wrapper).hide();
				$select.bind('resetOption',function(){
					$ul.empty();
					$('option', this).each(function(i){
						var oLi = $('<li><a href="#" index="'+ i +'">'+ $(this).html() +'</a></li>');
						$ul.append(oLi);
					});

					// 添加事件
					$ul.find('a').click(function(){
							$('a.selected', $wrapper).removeClass('selected');
							$(this).addClass('selected');	
							if ($select[0].selectedIndex != $(this).attr('index')){ 
								$select[0].selectedIndex = $(this).attr('index'); 
								$select.trigger("change").trigger("blur");
							}
							$('span:eq(0)', $wrapper).html($(this).html().replace(/[\||\—]+/,''));
							$ul.hide();
							$wrapper.removeClass('jqTransformSelectWrapper_focus');
							return false;
					});
					$('a:eq('+ this.selectedIndex +')', $ul).click();
				}).trigger('resetOption');

				$('span:first', $wrapper).click(function(){$("a.jqTransformSelectOpen",$wrapper).trigger('click');});
				oLabel && oLabel.click(function(){$("a.jqTransformSelectOpen",$wrapper).trigger('click');return false;});
				this.oLabel = oLabel;
				var oLinkOpen = $('a.jqTransformSelectOpen', $wrapper)
					.click(function(){
						//Check if box is already open to still allow toggle, but close all other selects
						if( $ul.css('display') == 'none' ) {jqTransformHideSelect();} 
						if($select.attr('disabled') || $select.attr('readonly')){return false;}

						$ul.is(':hidden') ? $wrapper.addClass('jqTransformSelectWrapper_focus') : $wrapper.removeClass('jqTransformSelectWrapper_focus');
						if($(document).height() - $wrapper.offset().top <= ($ul.height() || 150)){ // up
							$ul.addClass('upbox').slideToggle('fast', function(){					
								var offSet = ($('a.selected', $ul).offset().top - $ul.offset().top);
								$ul.animate({scrollTop: offSet});
							});
						}else{
							$ul.removeClass('upbox').slideToggle('fast', function(){					
								var offSet = ($('a.selected', $ul).offset().top - $ul.offset().top);
								$ul.animate({scrollTop: offSet});
							});
						}
						return false;
					});
				
				$wrapper.hover(function(){$wrapper.addClass("jqTransformSelectWrapper_hover");},function(){$wrapper.removeClass("jqTransformSelectWrapper_hover");});
				$select.is('[readonly]') ? $wrapper.addClass('readonly') : $wrapper.removeClass('readonly');
				$select.is('[disabled]') ? $wrapper.addClass('disabled') : $wrapper.removeClass('disabled');
				$select.bind({
					'show' : function(){$wrapper.show();$(this).hide();},
					'hide' : function(){$wrapper.hide();$(this).hide();},
					'remove' : function(){$wrapper.remove();$(this).remove();},
					'resetStyle' : function(){
						var iSelectWidth = ($select.show().outerWidth() || parseInt($select.css('width')) || 150)+8;
						var oSpan = $('span:first',$wrapper).css('width','auto');
						var newWidth = iSelectWidth+Math.max(oLinkOpen.outerWidth(),34);
						$wrapper.css('width',newWidth);
						$ul.css('width',newWidth-2);
						oSpan.css({width:iSelectWidth});
					
						$ul.css({display:'block',visibility:'hidden',height:280,overflow:'auto'});
						var iSelectHeight = ($('li',$ul).length)*($('li:first',$ul).height());//+1 else bug ff
						(iSelectHeight < $ul.height()) && $ul.css({height:iSelectHeight,'overflow':'hidden'});//hidden else bug with ff
						$ul.css({display:'none',visibility:'visible'});
						$select.hide();
					}
				}).trigger('resetStyle');
			});

			$(document).bind('mousedown',jqTransformAddDocumentListener);
			return this;
		};

		// 定义控件
		$.fn.form = function(options){
			var opt = $.extend({},defaultOptions,options);
			 return this.each(function(){
				var selfForm = $(this),maxIndex=88;
				if(selfForm.hasClass('jqtransformdone')) {return;}
				selfForm.addClass('jqtransformdone');
				selfForm.find('div').each(function(){
					maxIndex = maxIndex<=0 ? 88 : maxIndex;
					$(this).css('z-index',Math.max(maxIndex--,1));
				}); // fix zIndex
				
				$('input:submit, input:reset, input[type="button"]', selfForm).jqTransInputButton();			
				$('input:text, input:password', selfForm).jqTransInputText();			
				$('input:checkbox', selfForm).jqTransCheckBox();
				$('input:radio', selfForm).jqTransRadio();
				$('textarea', selfForm).jqTransTextarea();
				$('select', selfForm).jqTransSelect();
				selfForm.bind('reset',function(){var _this=this,action = function(){jqTransformReset(_this);}; window.setTimeout(action, 10);});
			});
		};
	})(window.jQuery || require('jquery'));
});