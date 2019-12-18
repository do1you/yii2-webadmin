/**
 @Name：page V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-5-11 
 */
define('pui/page',['jquery','pui/form','pui/page.css'],function(require,exports,moudles){
	(function($){
		$.fn.page = function(opts){
			this.each(function(){
				var _this = $(this).addClass('page_box'),
					reg = /([\?|\&]page\=(\d+))|(\/page\/(\d+))/ig,
					psizeReq = /([\?|\&]pageSize\=(\d+))|(\/pageSize\/(\d+))/ig,
					href = location.href.replace(/\#.*/g,""),
					options = $.extend({
						limits : [10,20,50,100,200], // 每页记录数可选值
						pageSize  : null, // 默认每页记录数
						itemCount : 0,  // 总的记录数
						currentPage : null, // 当前页数，默认从地址中提取
						pageCount : null, // 总页数，默认总记录数/每页记录数
						pageUrl : null,  // 分页链接地址，默认提取当前地址
						max : 8, // 显示可链接的页码数量
						pathInfo : false, // 是否以PATHINO的方式传递参数
						ajax : false  // 是否以AJAX方式请求分页
					},(opts||{})),
					pages = $('<div class="pages"><span>显示</span> <select name="pageSize"></select> <span>条，共 '+options.itemCount+' 条</span></div>'),
					selectEl = pages.find('select'),
					pagination = $('<div class="pagination"><ul></ul></div>'),
					ulEl = pagination.find('ul'),
					createUrl = function(p){
						if(options.pathInfo){
							var lastIndex = options.pageUrl.lastIndexOf('.');
							return (options.pageUrl.substring(0,lastIndex) + '/page/' + p + '/pageSize/' + selectEl.val() + options.pageUrl.substring(lastIndex));
						}else{
							return (options.pageUrl + (options.pageUrl.indexOf('?')>-1 ? '&' : '?') + 'page=' + p + '&pageSize=' + selectEl.val());
						}
					},
					createPage = function(att){
						return ('<li class="'+(att.className||'')+(att.page==options.currentPage&&att.className=='num' ? ' selected' : '')+'"><a href="'+createUrl(att.page)+'">'+att.text+'</a></li>');
					},
					goPage = function(p,url){
						url = url || createUrl(p||options.currentPage);
						if(options.ajax){
							$.get(url,function(html){
								var moreEl = $();
								$('body').children().each(function(){
									var position = ($(this).css('position')||'').toLocaleLowerCase();
									(position!='absolute' && position!='fixed') && (moreEl=moreEl.add(this));
								});
								if($.isFunction(options.ajax)){
									var el = options.ajax.call(this,html);
								}else{
									var el = $('body').prepend(html);
								}
								el && $(el).is('body') && moreEl.remove();
								$(window).resize();
								pui.parseHtml && pui.parseHtml(el || null); // 控件调用
								$.isFunction(options.ajax) ? $(el).animate({scrollTop:0}) : $('html,body').animate({scrollTop:0});
							});
							_this.empty().page($.extend(options,{
								currentPage : (url ? getPage(url) : (p||options.currentPage))
							}));
						}else{
							location.href = url;
						}
					},
					getPage = function(href){
						var pageParams = href.match(reg);
						return (pageParams&&pageParams.length ? (pageParams[0].match(/\d+/ig)[0]||1) : 1);						
					};

				if(options.pageSize===null){
					var sizeParams = href.match(psizeReq);
					options.pageSize = sizeParams&&sizeParams.length ? (sizeParams[0].match(/\d+/ig)[0]||20) : 1;
				}

				if(options.pageCount===null)
					options.pageCount = Math.ceil(options.itemCount/options.pageSize);
				
				if(options.currentPage===null){
					options.currentPage = getPage(href);
				}
				options.currentPage = parseInt(options.currentPage);
				options.currentPage = Math.min(options.pageCount,options.currentPage);
				options.currentPage = Math.max(1,options.currentPage);

				if(options.pageUrl===null)
					options.pageUrl = href;
				options.pageUrl = options.pageUrl.replace(reg,"").replace(psizeReq,"");

				if(options.limits&&options.limits.length){
					$.each(options.limits,function(index,value){
						selectEl.append('<option value="'+value+'">'+value+'</option>');
					})
				}else{
					pages.hide();
				}
				selectEl.val(options.pageSize);

				if(options.itemCount<=0){ // 没有记录
					_this.append('<div class="pages">暂无记录</div>');
					return;
				}

				ulEl.append(createPage({"className":"first","page":"1","text":"首页"}))
					.append(createPage({"className":"prev","page":(options.currentPage-1),"text":"上一页"}));
				
				var startNum = options.currentPage-Math.floor(options.max/2);
				startNum = options.pageCount-startNum<options.max ? options.pageCount-options.max : startNum;
				startNum = Math.max(1,startNum);
				var endNum = Math.min(options.pageCount,options.currentPage+(options.max-(options.currentPage-startNum)));
				for(;startNum<=endNum;startNum++){
					ulEl.append(createPage({"className":"num","page":startNum,"text":startNum}));
				}
				ulEl.append(createPage({"className":"next","page":(options.currentPage+1),"text":"下一页"}))
					.append(createPage({"className":"last","page":options.pageCount,"text":"尾页"}))
					.append('<li class="goto"><input type="text" size="4" value="'+options.currentPage+'"><button>确定</button></li>');
				
				// 绑定事件
				selectEl.change(function(){goPage();}); // 选记录数
				pagination.find('.goto button').click(function(){ // 跳页数
					var page = parseInt(pagination.find('.goto input').val());
					if(page>=1 && page<=options.pageCount){
						goPage(page);
					}else{
						alert('请填写正确的页数，不能超出总页数：'+options.pageCount);
					}
				});
				pagination.find('.goto input').click(function(){ // 自动选中页码数
					$(this).select();
				}).bind('keydown',function(e){ // 回车跳转
					if (e.keyCode==13){
						pagination.find('.goto button').click();
						return false;
					}
				});
				pagination.find('a').unbind('click').click(function(){ // 数字分页数
					var parel = $(this).parent();
					if(!parel.hasClass('selected') && !parel.hasClass('disabled')){
						goPage('',$(this).attr('href'));
					}
					return false;
				});

				// 隐藏页码
				options.currentPage<=1 && pagination.find('.first,.prev').addClass('disabled');
				options.currentPage>=options.pageCount && pagination.find('.last,.next').addClass('disabled');

				_this.append(pages).append(pagination);
				selectEl.jqTransSelect();
			});
		};
	})((window.jQuery || require('jquery')),require('pui/form'));
});