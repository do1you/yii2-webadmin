/**
 @Name：date V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-1-13 
 */
define('pui/date',['jquery','pui/date.css'],function(require,exports,moudles){
	(function($){
		// 选择框
		var dateEl = $(
				'<div class="date_box">'+
					// head
					'<div class="date_head">'+
						'<div class="date_head_year">'+
							'<a class="date_year_left" title="前一年"><i></i></a>'+
							'<a class="date_year_label" title="选择年份"><i></i></a>'+
							'<a class="date_year_right" title="后一年"><i></i></a>'+
						'</div>'+
						'<div class="date_head_month">'+
							'<a class="date_month_left" title="前一月"><i></i></a>'+
							'<a class="date_month_label" title="选择月份"><i></i></a>'+
							'<a class="date_month_right" title="后一月"><i></i></a>'+
						'</div>'+
					'</div>'+
					// body
					'<table class="date_table">'+
						'<thead><tr>'+
						'</tr></thead>'+
						'<tbody>'+
						'</tbody>'+
					'</table>'+
					// footer
					'<div class="date_foot">'+
						'<ul><li class="date_time_label">时间</li><li><input readonly="" class="date_hours">:</li><li><input readonly="" class="date_minutes">:</li><li><input readonly="" class="date_seconds"></li></ul>'+
						'<div class="date_btn"><a class="date_clear">清空</a><a class="date_today">现在</a><a class="date_ok">确认</a></div>'+
					'</div>'+
					// select year
					'<div class="date_year_list date_hide"><a class="date_year_up"><i></i></a><ul class="date_cf"></ul><a class="date_year_down"><i></i></a></div>'+
					// select month
					'<div class="date_month_list date_hide"><ul class="date_cf"></ul></div>'+
					// select hours
					'<div class="date_hours_list date_hide"><div>小时</div><ul class="date_cf"></ul></div>'+
					// select minutes
					'<div class="date_minutes_list date_hide"><div>分钟</div><ul class="date_cf"></ul></div>'+
					// select seconds
					'<div class="date_seconds_list date_hide"><div>秒数</div><ul class="date_cf"></ul></div>'+
				'</div>'
			),
			weekNames = ["日","一","二","三","四","五","六"],
			monthNames = ["01","02","03","04","05","06","07","08","09","10","11","12"],
			configs={},
			i,currEl,currDate,tempYear;
		
		// 操作方法
		var methods = {
			// 表单赋值
			putVal : function(value){
				if(typeof value=='undefined'){
					var year = currDate.getFullYear(),
						month = methods.fill(currDate.getMonth()+1,2),
						date = methods.fill(currDate.getDate(),2),
						hours = methods.fill(currDate.getHours(),2),
						minutes = methods.fill(currDate.getMinutes(),2),
						seconds = methods.fill(currDate.getSeconds(),2);
					switch(configs.type){
						case 'date':
							value = ''+year+'-'+month+'-'+date;
						break;
						case 'time':
							value = ''+hours+':'+minutes+':'+seconds;   
						break;
						default:
							value = ''+year+'-'+month+'-'+date+' '+hours+':'+minutes+':'+seconds; 
						break;
					}
				}
				$(currEl).val(value).trigger("change");
				methods.hideEl();
				dateEl.trigger("hide");
			},
			// 设置时间
			setVal : function(value){
				currDate = new Date;
				currDate.setSeconds(0);
				if(value){
					var newDate = new Date(Date.parse((''+value).replace(/\-/g,"/")));
					if(newDate instanceof Date && newDate != 'Invalid Date' && newDate.getDate()){
						currDate = newDate;
					}else if(newDate = (''+value).match(/([0-2]?\d):([0-5]?\d):([0-5]?\d)/)){
						(''+newDate[1]).length && currDate.setHours(newDate[1]);
						(''+newDate[2]).length && currDate.setMinutes(newDate[2]);
						(''+newDate[3]).length && currDate.setSeconds(newDate[3]);
					}
				}
				methods.autoHtml();
			},
			// 重置HTML
			autoHtml : function(){
				var year = currDate.getFullYear(),
					month = currDate.getMonth(),
					date = currDate.getDate(),
					temp = new Date(year,(month+1),0),
					maxDay = temp.getDate(),
					html='<tr>',box = dateEl.find('.date_table tbody').empty(),
					i,l=0;
				dateEl.find(".date_year_label").html(year+'年<i></i>');
				dateEl.find(".date_month_label").html(monthNames[month]+'月<i></i>');
				
				// 组装日期
				temp.setDate(1);
				var maxDate,startDay = temp.getDay();
				if(startDay>0){
					temp.setDate(0); // 获取上个月最后一天
					maxDate = temp.getDate();
					for(i=startDay-1;i>=0;i--){
						html += '<td class="more_date" y="'+temp.getFullYear()+'" m="'+temp.getMonth()+'" d="'+(maxDate-i)+'">'+(maxDate-i)+'</td>';
						l++;
					}
					
				}
				for(i=1;i<=maxDay;i++){
					html += '<td'+(i==date ? ' class="date_checked"' : '')+' y="'+year+'" m="'+month+'" d="'+i+'">'+i+'</td>';
					if(++l%7 == 0){
						html+="</tr><tr>";
					}				
				}
				for(i=l,l=1;i<42;i++){
					temp.setFullYear(year);
					temp.setMonth(month+1);				
					if(i%7==0){
						html+="</tr><tr>";
					}
					html += '<td class="more_date" y="'+temp.getFullYear()+'" m="'+temp.getMonth()+'" d="'+l+'">'+(l++)+'</td>';
				}
				html+="</tr>";
				box.html(html);
				
				// 时分秒
				var inputs = dateEl.find('.date_foot li input').val(methods.fill(currDate.getHours(),2));
				inputs.eq(1).val(methods.fill(currDate.getMinutes(),2));
				inputs.eq(2).val(methods.fill(currDate.getSeconds(),2));
				
				// 隐藏浮动窗
				methods.hideEl();
			},
			// 隐藏浮窗
			hideEl : function(){
				dateEl.find('.date_year_list,.date_month_list,.date_hours_list,.date_minutes_list,.date_seconds_list').stop().slideUp('fast');
				dateEl.find('.date_year_label,.date_month_label').removeClass('expanded');			
			},
			// 重置选择年份HTML
			autoYear : function(year,isPage){			
				tempYear = year;
				methods.hideEl();
				var label = dateEl.find('.date_year_label');
				if(dateEl.find('.date_year_list').is(':visible') && !isPage){
					dateEl.find('.date_year_list').stop().slideUp('fast');
					label.removeClass('expanded');
				}else{
					for(var html='',currYear=currDate.getFullYear(),i=year-7;i<=year+6;i++){
						if(i>0) html += '<li y="'+i+'"'+(i==currYear ? ' class="date_checked"' : '')+'>'+i+'年</li>';
					}
					dateEl.find('.date_year_list ul').empty().html(html);
					dateEl.find('.date_year_list').stop().slideDown('fast');	
					label.addClass('expanded');
				}
			},
			// 填充长度
			fill : function(str,len,p){
				p = p || '0';
				str = ''+str;
				if(len>str.length){
					for(var i=str.length;i<len;i++){
						str = p+str;
					}
				}
				return str;
			}
		};
		
		// 星期行头
		for(i=0;i<7;i++) dateEl.find('.date_table thead tr').append("<th>"+weekNames[i]+"</th>");
		// 月
		for(i=0;i<12;i++) dateEl.find('.date_month_list ul').append('<li m="'+i+'">'+monthNames[i]+"月</li>");
		// 小时
		for(i=0;i<24;i++) dateEl.find('.date_hours_list ul').append('<li h="'+i+'">'+methods.fill(i,2)+"</li>");
		// 分 // 秒
		for(i=0;i<60;i++){
			dateEl.find('.date_minutes_list ul').append('<li i="'+i+'">'+methods.fill(i,2)+"</li>");
			dateEl.find('.date_seconds_list ul').append('<li s="'+i+'">'+methods.fill(i,2)+"</li>");
		}
		dateEl.find('a').attr('href','javascript:void(0)');
		
		// 事件
		dateEl.click(function(){
			return false;
		}).find('.date_table,.date_year_list,.date_month_list,.date_hours_list,.date_minutes_list,.date_seconds_list').mousemove(function(e){ // 日期移动到效果
			dateEl.find('td,li').removeClass('date_hover');
			if($(e.target).is('li,td')){
				$(e.target).addClass('date_hover');
			}
		}).mouseleave(function(){ // 移开日期范围
			dateEl.find('td,li').removeClass('date_hover');
		}).click(function(e){ // 选择年月日
			var temp,target = $(e.target);
			if(!target.is('a,li,td')){
				target = target.parents('a:first,li:first,td:first');
			}
			
			if(target.hasClass('date_year_up')){ // 上一组年份
				methods.autoYear(tempYear-14,1);
			}else if(target.hasClass('date_year_down')){ // 下一组年份
				methods.autoYear(tempYear+14,1);
			}else{ // 设置日期时间
				(temp=''+(target.attr('y')||'')).length && currDate.setFullYear(temp);
				(temp=''+(target.attr('d')||'')).length && currDate.setDate(temp);			
				(temp=''+(target.attr('m')||'')).length && currDate.setMonth(temp);
				(temp=''+(target.attr('h')||'')).length && currDate.setHours(temp);
				(temp=''+(target.attr('i')||'')).length && currDate.setMinutes(temp);
				(temp=''+(target.attr('s')||'')).length && currDate.setSeconds(temp);
				
				if(target.is('td[y][m][d]') && configs.type=='date'){
					methods.putVal();
				}else{
					methods.autoHtml();
				}
			}
			return false;
		});
		dateEl.find('.date_head a').click(function(){ // 年月选择
			var _this = $(this);
			if(_this.hasClass('date_year_left')){ // 上一年
				currDate.setFullYear(currDate.getFullYear()-1);
			}else if(_this.hasClass('date_year_label')){ // 选择年
				methods.autoYear(currDate.getFullYear());
				return false;
			}else if(_this.hasClass('date_year_right')){ // 下一年
				currDate.setFullYear(currDate.getFullYear()+1);
			}else if(_this.hasClass('date_month_left')){ // 上一月
				currDate.setMonth(currDate.getMonth()-1);
			}else if(_this.hasClass('date_month_label')){ // 选择月
				methods.hideEl();
				dateEl.find('.date_month_label')[dateEl.find('.date_month_list').is(':visible') ? 'removeClass' : 'addClass']('expanded');
				dateEl.find('.date_month_list').stop().slideToggle('fast').find('li').removeClass('date_checked').filter('li[m="'+currDate.getMonth()+'"]').addClass('date_checked');
				return false;
			}else if(_this.hasClass('date_month_right')){ // 下一月
				currDate.setMonth(currDate.getMonth()+1);
			}
			methods.autoHtml();
			return false;
		});
		dateEl.find('.date_foot a,.date_foot input').click(function(){
			var _this = $(this);
			if(_this.hasClass('date_clear')){ // 清空
				methods.putVal('');
			}else if(_this.hasClass('date_today')){ // 现在
				methods.setVal();
			}else if(_this.hasClass('date_ok')){ // 确认
				methods.putVal();
			}else{
				methods.hideEl();
				if(_this.hasClass('date_hours')){ // 时
					dateEl.find('.date_hours_list').stop().slideToggle('fast').find('li').removeClass('date_checked').filter('li[h="'+currDate.getHours()+'"]').addClass('date_checked');
				}else if(_this.hasClass('date_minutes')){ // 分
					dateEl.find('.date_minutes_list').stop().slideToggle('fast').find('li').removeClass('date_checked').filter('li[i="'+currDate.getMinutes()+'"]').addClass('date_checked');
				}else if(_this.hasClass('date_seconds')){ // 秒
					dateEl.find('.date_seconds_list').stop().slideToggle('fast').find('li').removeClass('date_checked').filter('li[s="'+currDate.getSeconds()+'"]').addClass('date_checked');
				}
			}
			return false;
		});
		
		// 效果
		dateEl.bind('show',function(){ // 下拉弹出效果
			if(dateEl.is(':animated')){
				return false;
			}
			
			dateEl.slideDown('fast', function(){dateEl.css('overflow','visible');});
		}).bind('hide',function(){ // 下拉隐藏效果
			if(dateEl.is(':animated')){
				return false;
			}
			methods.hideEl(); // 隐藏浮动窗
			dateEl.slideUp('fast');	
		});
		
		// 初始化
		$.fn.date = function(opts){
			opts=$.extend({
				type : "datetime" /* date  time  datetime*/
			},opts);
			
			// 选择
			var dateFn = function(){
				var _this = $(this),
					offset = _this.offset();
					
				if(dateEl.is(':visible') && currEl==this){ // 隐藏
					dateEl.trigger("hide");
					return false;
				}
				
				// 判断显示内容
				dateEl.find('.date_head,.date_table,.date_foot ul li').show();
				dateEl.find('.date_foot').css('border-radius','0 0 4px 4px');
				switch(opts.type){
					case 'date':
						dateEl.find('.date_foot ul li').hide();
					break;
					case 'time':
						dateEl.find('.date_head,.date_table').hide();
						dateEl.find('.date_foot').css('border-radius','4px');
					break;
				}
				
				// 计算位置			
				if(offset.top+_this.outerHeight()+dateEl.show().outerHeight()>=$(document).height()){ // 出界，显示在上方
					offset.top -= dateEl.outerHeight();
				}else{ // 显示在下方
					offset.top += _this.outerHeight();
				}
				
				// 设置时间
				methods.setVal(_this.val());
				
				// 展示
				dateEl.hide().css(offset).removeClass().addClass('date_box').trigger("show");
				currEl = this;
				configs = opts;
			};
			this.addClass('date_input').each(function(){
				$(this).width($(this).width()-20);
			});
			this.unbind('click',dateFn).bind('click',dateFn);
			return this;
		};
		
		$(document).click(function(){
			dateEl.trigger('hide');
		});
		$(function(){
			$('body').append(dateEl);
		});
	})(window.jQuery || require('jquery'));
});