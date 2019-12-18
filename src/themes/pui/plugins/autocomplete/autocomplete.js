/**
 @Name：autocomplete V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-5-29
 */
define('pui/autocomplete',['jquery','pui/autocomplete.css'],function(require,exports,moudles){
	(function($) {
		var timeer,
			searchFn = function(data,keyword){ // 搜索对象或数组
				var result = {};
				$.each(data,function(key,value){
					if((''+value).indexOf(keyword)!=-1){
						result[key] = value;
					}
				});
				return result;
			},
			boxEl = $('<div></div>').addClass('autocomplete_box');
		
		boxEl.mousemove(function(e){
			boxEl.mouseout();
			var el = $(e.target);
			el.is('li') ? el.addClass('hover') : el.parents('li:first').addClass('hover');
		}).mouseout(function(){
			boxEl.find('li').removeClass('hover');
		}).appendTo('body');

		$(document).click(function(){
			boxEl.slideUp('fast');
		});

		$.fn.autocomplete = function(options){
			if(typeof options=='string') options = {url:options};
			else if($.isArray(options)) options = {data:options};
			options=$.extend({
				url : null, // 请求远程的数据
				data : [], // 本地的数据源
				key : 'query', // 请求服务器的键值
				params : null, // 需要一起提交的元素
				afterFn : function(){}, // 选择完内容执行动作
				delay : 300, // 延时时间
				maxRecord : 10 // 最大记录数
			},options);

			var createFn = function($input){
					var valueInput = $('<input type="hidden">');
					valueInput.attr({'name':$input.attr('name'),'id':$input.attr('id')});
					$input.removeAttr('name id').data('valueInput',valueInput);
					$input.after(valueInput);
					return valueInput;
				},
				showFn = function(data,$input){ // 显示数据
					var num = 0,
						ul = $('<ul></ul>');
					$.each(data,function(index,item){
						if(num>=options.maxRecord) return false;
						$('<li></li>').html(item).attr('title',item).click(function(e){
							var valueInput = $input.data('valueInput');
							if(!valueInput) valueInput = createFn($input);
							$input.val(item).attr('oldValue',item);
							valueInput.val(index);
							boxEl.slideUp('fast');
							options.afterFn && options.afterFn.apply(this,[valueInput,$input]);
							return false;
						}).appendTo(ul);
						num++;
					});

					// 未搜索到数据
					if(num<=0){
						$('<li class="notfind">暂无记录</li>').appendTo(ul);
					}

					boxEl.html(ul).find('li:odd').addClass('trbg');
					boxEl.is(':hidden') && boxEl.slideDown('fast');
				},
				quickKeyFn = function(which,$input){ // 快捷键
					var selEl = boxEl.find('li.selected');
					switch(which) { 
						case 38: // Up
							if(boxEl.is(':hidden')) return false;
							if(selEl.length){
								selEl.removeClass('selected').prev().addClass('selected');
							}else{
								boxEl.find('li:last').addClass('selected');
							}
						break;
						case 40: // Down
							if(boxEl.is(':hidden')){
								boxEl.slideDown('fast');
							}else if(selEl.length){
								selEl.removeClass('selected').next().addClass('selected');
							}else{
								boxEl.find('li:first').addClass('selected');
							}
						break;
						case 13: // Enter
							if(boxEl.is(':visible')){
								selEl.length && selEl.click();
							}else{
								return true;
							}
						break;
						case 27: // Esc
							boxEl.is(':visible') && boxEl.slideUp('fast');
						break;
						default:
							return true;
						break;
					}
					return false;
				};

			// 绑定事件
			this.addClass('autocomplete_input').attr('autocomplete','off').unbind('change keyup').bind("keyup", function(e) {
				timeer && clearTimeout(timeer);
				var $input = $(this),
					value = $input.val();

				if(!quickKeyFn(e.which,$input)){
					return false;
				}

				// 定位
				var offset = $input.offset();
				offset.top += $input.outerHeight()
				boxEl.data('input',$input).css(offset).width($input.outerWidth()+10);

				if(options.url){ // 远程加载数据
					data = options.key + '=' + value;
					data += '&pageSize=' + options.maxRecord;
					if(options.params){
						data += '&'+$(options.params).serialize();
					}
					timeer = setTimeout(function(){
						if(boxEl.is(':hidden')){
							boxEl.html('<ul><li class="loading">加载数据...</li></ul>');
							boxEl.slideDown('fast');
						}
						$.ajax({
							url: options.url,
							dataType: "json",
							type: "post",
							global: false,
							data: data,
							success: function(json){
								showFn(json,$input);
							}
						});
					},options.delay);					
				}else{ // 本地数据
					timeer = setTimeout(function(){
						showFn(searchFn(options.data,value),$input);
					},options.delay);
				}
				return false;
			}).click(function(){
				$(this).select();
			}).change(function(){
				var valueInput = $(this).data('valueInput');
				valueInput && valueInput.val('');
			}).keydown(function(e){
				if(e.which==13) return false;
			}).blur(function(){
				var _this = $(this),
					oldValue = _this.attr('oldValue');
				_this.val(oldValue);
			}).each(function(){
				var $input = $(this),
					valueInput = $input.data('valueInput'),
					value = $input.val(),
					text = $input.attr('text') || $input.attr('title') || '';
				if(!valueInput){
					valueInput = createFn($input);
					valueInput.val(value).prop('defaultValue',value);
					$input.width($input.width()-17);
					$input.val(text).attr('oldValue',text).prop('defaultValue',text);
				}
			});
		};
	})(window.jQuery || require('jquery'));
});