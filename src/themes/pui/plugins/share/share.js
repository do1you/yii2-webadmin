/**
 @Name：share V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-9-29 
 */
define('pui/share',['jquery'],function(require,exports,moudles){
	(function($){
		window._bd_share_config={
			//share : [ // 分享按钮
				// tag 表示该配置只会应用于data-tag值一致的分享按钮。
				// bdSize 分享按钮的尺寸
				// bdCustomStyle // 自定义样式，引入样式文件
			//],
			//slide : [ // 浮窗
				// bdImg 分享浮窗图标的颜色。 0-8
				// bdPos 分享浮窗的位置 left or right
				// bdTop 分享浮窗与可是区域顶部的距离(px)
			//],
			//image : [ // 图片分享
				// tag 表示该配置只会应用于data-tag值一致的图片。如果不设置tag，该配置将应用于所有图片。
				// viewType 图片分享按钮样式。 list or collection
				// viewPos 图片分享展示层的位置。 top or bottom
				// viewColor 图片分享展示层的背景颜色。 black or white
				// viewSize 图片分享展示层的图标大小。 16 or 24 or 32
				// viewList 自定义展示层中的分享按钮类型和排列顺序。
			//],
			//selectShare : [ // 划词分享
				// bdSelectMiniList 自定义弹出浮层中的分享按钮类型和排列顺序。
				// bdContainerClass 自定义划词分享的激活区域
			//],
			common : { // 通用
				//"bdSnsKey":{}, // 分享目标的KEY设置
				//"bdText":"", // 自定义分享内容
				//"bdDesc":"", // 自定义分享摘要
				//"bdUrl":"", // 自定义分享地址
				//"bdPic":"", // 自定义分享图片
				//"bdMini":"2", // 下拉浮层中分享按钮的列数
				//"bdMiniList":false, // false or array // 自定义下拉浮层中的分享按钮类型和排列顺序
				//"onBeforeClick":function(cmd,config){}, // 在用户点击分享按钮时执行代码，更改配置。cmd为分享目标id，config为当前设置
				//"onAfterClick":function(cmd){}, // 用户点击分享按钮后执行代码，cmd为分享目标id。
				//"bdSign":"on", // 分享回流统计
				//"bdStyle":"0", // 分享按纽样式 0-2
				"bdSize":"32" // 分享图标大小 16 or 24 or 32
			}
		};
		with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src='http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion='+~(-new Date()/36e5)];
		var tt,tagIndex = 1;
		$.fn.share = function(opts){
			if(this.attr('shared')) return;
			this.attr('shared',1);
			tt && clearTimeout(tt);
			if(typeof opts=='string') opts = {type:opts};
			opts = $.extend({
				type : "share", // share：分享按纽 slide：浮窗 image：图片分享 selectShare：划词分享 btn：自定义分享按纽（增）
				target : '' // 自定义分享容器
			},opts);
			
			switch(opts.type){
				case 'share': // 分享按纽
				case 'image': // 图片分享
				case 'btn': // 自定义按纽
					var shareBox = this;
					if(opts.type == 'share' || opts.type == 'btn'){ // 分享按纽 or 自定义按纽
						if(opts.type == 'btn'){
							if(opts.target){
								shareBox = $(opts.target);
							}else{
								shareBox = $('<div></div>').appendTo('body').css({});
							}
							opts.type = 'share';
							this.bind('click',function(){
								shareBox.slideToggle();
							});
						}
						shareBox.addClass('bdsharebuttonbox').find('[data-cmd]').each(function(){
							$(this).addClass('bds_'+$(this).data('cmd'));
						});
					}
					shareBox.each(function(){
						var t = $(this),
							tag = t.data('tag');
						if(!tag){
							opts.tag = tag = 'share_' + tagIndex++;
							t.attr('data-tag',tag);
							window._bd_share_config[opts.type] = window._bd_share_config[opts.type] || [];
							window._bd_share_config[opts.type].push(opts);
						}
					});
				break;
				case 'selectShare': // 划词分享
				case 'slide': // 浮窗分享
					window._bd_share_config[opts.type] = window._bd_share_config[opts.type] || [];
					window._bd_share_config[opts.type].push(opts);
				break;
				default: // 原生百度分享
					window._bd_share_config=$.extend(window._bd_share_config,opts); // 实现原生的百度分享代码
				break;
			}
			tt = setTimeout(function(){
				window._bd_share_main && window._bd_share_main.init();
			},30);
		};
		/**  分享媒体对应表
		下拉框	popup
		更多	more
		回访记录数	count
		一键分享	mshare
		QQ空间	qzone
		新浪微博	tsina
		人人网	renren
		腾讯微博	tqq
		百度相册	bdxc
		开心网	kaixin001
		腾讯朋友	tqf
		百度贴吧	tieba
		豆瓣网	douban
		搜狐微博	tsohu
		百度新首页	bdhome
		QQ好友	sqq
		和讯微博	thx
		百度云收藏	bdysc
		美丽说	meilishuo
		蘑菇街	mogujie
		点点网	diandian
		花瓣	huaban
		堆糖	duitang
		和讯	hx
		飞信	fx
		有道云笔记	youdao
		麦库记事	sdo
		轻笔记	qingbiji
		人民微博	people
		新华微博	xinhua
		邮件分享	mail
		我的搜狐	isohu
		摇篮空间	yaolan
		若邻网	wealink
		天涯社区	ty
		Facebook	fbook
		Twitter	twi
		linkedin	linkedin
		复制网址	copy
		打印	print
		百度个人中心	ibaidu
		微信	weixin
		股吧	iguba
		*/
	})(window.jQuery || require('jquery'));
});