// 列表数据
(function($){
	$.extend({
		getX : function(e){
			return (e.originalEvent || e).changedTouches ? (e.originalEvent || e).changedTouches[0].clientX : e.clientX;
		},
		getY : function(e){
			return (e.originalEvent || e).changedTouches ? (e.originalEvent || e).changedTouches[0].clientY : e.clientY;
		}
	});

	// 全选
	$(document).on('change','.checkAll',function() {
		var els = $(this).closest('.checkAllBox').find("input[type=checkbox]").not(this);
		var checked = $(this).is(":checked");
		els.prop('checked',checked).trigger("change");
	});

	// 选中焦点
	$(document).on('change','.checkActive,.radioActive',function() {
		if($(this).is('.radioActive')){
			$(this).closest('table').find('tr').removeClass('active');
		}

		var checked = $(this).is(":checked");
		$(this).closest('tr')[checked ? 'addClass' : 'removeClass']("active");
	});

	// 批量提交判断记录
	$(document).on('click','.checkSubmit',function(){
		var form = $(this).closest('form,.checkForm'),
			action = $(this).attr('reaction');
		if(form.find('input[type=checkbox][value]:checked').length <= 0){
			Notify('请至少选择一条记录', 'top-right', '5000', 'danger', 'fa-bolt', true);
			return false;
		}else{
			bootbox.confirm("确认要执行这项操作吗 ?", function (result) {
                if (result){
					if(!form.is('form')){
						$(document).find('form.check-submit-form').remove();
						var $form = $('<form/>', {
							action: (action || '?'),
							method: 'post',
							'class': 'check-submit-form',
							style: 'display:none'
						}).appendTo(form);
						form.find('input[type=checkbox][value]:checked,input[type=hidden]').each(function(name, values) {
							$form.append($('<input/>').attr({type: 'hidden', name: $(this).attr('name'), value: $(this).attr('value')}));
						});
						$form.submit();
					}else{
						action && form.attr('action',action);
						form.submit();
					}
                }
            });
			return false;
		}
	});

	// 确认操作
	$(document).on('click','a.checkConfirm',function(){
		var el = $(this);
		bootbox.confirm("确认要执行这项操作吗 ?", function (result) {
			if (result){
				location.href = el.attr('href');
			}
		});
		return false;
	});

	// 单选/多选弹出选择框
	$.fn.dialogLoad = function(url,fn,data,selEl){
		var fnName = '_dialogCallfn_'+Date.parse(new Date())/1000;
		window[fnName] = fn;
		var f = function(){
			var params = data ? ($.isFunction(data) ? data() : data) : {};
			params['fn'] = 'window.'+fnName;
			$.post(url,params,function(html){
				$('#hiddenDiv').html(html);
			});
		};
		selEl ? (selEl===true ? f() : $(this).on('click',selEl,f)) : $(this).on('click',f);
	};

	// 表单内容转换为JSON格式
    $.fn.serializeJson=function(isPush){  
        var serializeObj={};  
        var array=this.serializeArray();
        $(array).each(function(){  
            if(typeof serializeObj[this.name]!="undefined" && isPush){  
                if($.isArray(serializeObj[this.name])){  
                    serializeObj[this.name].push(this.value);  
                }else{  
                    serializeObj[this.name]=[serializeObj[this.name],this.value];  
                }  
            }else{  
                serializeObj[this.name]=this.value;   
            }  
        });  
        return serializeObj;  
    };

	// 根据thead重新组装tbody的表格
	$.fn.reCreate = function(){
		var tb = $(this);
		if(tb.is('table')){
			var len = tb.find('>thead>tr>th,>thead>tr>td').length;
			if(len>0){
				var tbody = tb.find('>tbody'),
					tds = tbody.find('>tr>td,>tr>th'),
					tr = null,
					n = 0;
				tbody.empty();
				tds.each(function(i,obj){
					if(n%len == 0){
						tr = $('<tr></tr>');
						tbody.append(tr);
					}
					if(tr){
						tr.append(obj);
						n++;
					}
				});
			}
		}
	};

	// 拾色器
	 $.fn.minicolors && $('.colorpicker').each(function () {
		$(this).minicolors({
			control: $(this).attr('data-control') || 'hue',
			defaultValue: $(this).attr('data-defaultValue') || '',
			inline: $(this).attr('data-inline') === 'true',
			letterCase: $(this).attr('data-letterCase') || 'lowercase',
			opacity: $(this).attr('data-opacity'),
			position: $(this).attr('data-position') || 'bottom left',
			change: function (hex, opacity) {
				if (!hex) return;
				if (opacity) hex += ', ' + opacity;
				try {
					console.log(hex);
				} catch (e) { }
			},
			theme: 'bootstrap'
		});
	});
	 
	// 表格行头固定
 	if($('table.table').not('.notFix').length){
 		$(window).add('.grid-view').on('scroll',function(){
 			var scrollTop = $(window).scrollTop();
 			$('table.table').not('.notFix').each(function(){
 				var box = $(this),
 					width = box.width(),
 					fixhdiv = box.data('fixhdiv'),
					scrollObj = box.data('scrollObj'),
 					offset = box.offset();
 				if(!fixhdiv){
 					box.find('>thead th').each(function(){
 						var w = $(this).width();
 						$(this).width(w)
 					});
 					fixhdiv = box.clone().css({
 						'position' : 'fixed',
 						'left' : offset.left,
 						'width' : (width+2),
 						'z-index' : '13',
 						'display' : 'none'
 					});
 					fixhdiv.addClass('notFix').find('>tbody,>tfoot').remove();
 					box.after(fixhdiv);
 					box.data('fixhdiv',fixhdiv);
 				}
				scrollObj && clearTimeout(scrollObj);
 				if(offset.top < scrollTop){
 					fixhdiv.css({
						'top' : ($('.page-header-fixed').length ? '85px' : ($('.navbar-fixed-top').length ? '45px' : '0')),
						'left' : offset.left
					}).show();
					scrollObj = setTimeout(function(){
						fixhdiv.hide().show();
					},100);
 				}else{
 					fixhdiv.hide();
					scrollObj = setTimeout(function(){
						fixhdiv.show().hide();
					},100);
 				}
				box.data('scrollObj',scrollObj);
 			});
 		}).scroll();
 	}

	// 筛选对象
	$.grepObj = function(obj,fn){
		var i,f={};
		for(i in obj) if($.isFunction(fn) && fn(i,obj[i])===true) f[i] = obj[i];
		return f;
	};

	// 默认风格
	if(!readCookie("current-skin")&&$("#skin-changer li a").length){
		createCookie("current-skin", $("#skin-changer li a:eq(0)").attr('rel'), 10);
		window.location.reload();
	}

	// 解决select2和modal的冲突
	$.fn.modal.Constructor.prototype.enforceFocus = function () {};

	// 关闭提示窗口
	setTimeout(function(){
		$('.page-body>.alert:not(.exceldown)').fadeOut();
	},3000);

	$(document).ajaxError(function(event,xhr,options,exc){
		if(xhr.statusText!='abort'){
			if(xhr.responseJSON&&xhr.responseJSON.msg){
				console.log(xhr.responseJSON.msg);
			}else{
				console.log(xhr.responseText || xhr.statusText);
			}
		}
	});
})(jQuery);

$(function(){
	if($.fn.select2) $('select.select2').select2();
});