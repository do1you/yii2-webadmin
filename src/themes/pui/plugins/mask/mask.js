/**
 @Name：mask V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-5-4
 */
define('pui/mask',['jquery'],function(require,exports,module){
	var overflowVal = 'visible',
		src = pui.resolve('pui/mask'),
		$ = (window.jQuery || require('jquery')),
		Mask = function(){ 
			var args = Array.prototype.splice.call(arguments,0);
			this.tagName && args.splice(0,0,this);
			return Mask.mask.apply(this,args);
		},
		CLS_MASK = 'pui-ext-mask',
		CLS_MASK_MSG = CLS_MASK + '-msg',
		maskStyle = {
			left : '0',
			top : '0',
			opacity : "0.7",
			position : "absolute",
			height : "100%",
			width : "100%",
			zIndex : "999990",
			backgroundColor : "#333333"
		},
		maskMsgStyle = {
			left : '0',
			top : '0',
			position : "absolute",
			border : '1px solid #c3c3d6',
			background : 'none repeat-x scroll 0 -16px #e8e9ef',
			zIndex : "999999",
			fontSize : '12px',
			padding : '2px'
		},
		maskTextStyle = {
			background : 'none no-repeat scroll 5px 5px #ffffff',
			padding : '5px 10px 5px 25px',
			lineHeight : '18px',
			backgroundImage : 'url('+src.substring(0, src.lastIndexOf('/') + 1)+'load-16-16.gif)'
		};

    $.extend(Mask,
    {
		// 屏蔽元素
        mask:function (element, msg, msgCls) {
            var maskedEl = $(element),
                maskDiv = maskedEl.children('.' + CLS_MASK),
                msgDiv = null,
                top = null,
                left = null;
            if (!maskDiv.length) {
				overflowVal = maskedEl.css('overflow-y');
                maskDiv = $('<div class="' + CLS_MASK + '"></div>').css(maskStyle).hide().fadeIn('normal').appendTo(maskedEl);
                maskedEl.css({"position":"relative",'overflow-y':'hidden'}).addClass('pui-masked');
                //屏蔽整个窗口
				if(maskedEl[0]===$('body')[0]){
					maskDiv.height(Math.max(maskedEl.css('margin','0').innerHeight(),$(window).innerHeight()));
				}else{
					maskDiv.height(maskedEl.innerHeight());
				}
                if (msg) {
                    msgDiv = $('<div class="' + CLS_MASK_MSG + '"><div>'+ msg + '</div></div>').css(maskMsgStyle).appendTo(maskedEl);
                    msgCls && msgDiv.addClass(msgCls);
					msgDiv.find('>div').css(maskTextStyle);

					if(maskedEl[0]===$('body')[0]){
						top = $(window).scrollTop() + ($(window).height() - msgDiv.outerHeight()) / 2;
					}else{
						top = maskDiv.scrollTop() + (maskDiv.height() - msgDiv.outerHeight()) / 2;
					}
					left = (maskDiv.innerWidth() - msgDiv.innerWidth()) / 2;     
                    msgDiv.css({ left:left, top:top, marginTop:-80, opacity:0 }).stop().animate({marginTop:0,opacity:1},'normal');
                }
            }
            return maskDiv;
        },
       // 解除元素的屏蔽
        unmask:function (element) {
            var maskedEl = $(element),
                msgEl = maskedEl.children('.' + CLS_MASK_MSG),
                maskDiv = maskedEl.children('.' + CLS_MASK);
            if (msgEl) {
				msgEl.stop().animate({marginTop:-80,opacity:0},'normal',function(){
					msgEl.remove();
				});
            }
            if (maskDiv) {
				maskDiv.fadeOut('normal',function(){
					maskDiv.remove();
					maskedEl.css({'overflow-y':overflowVal});
				});
            }
            maskedEl.removeClass('pui-masked');
        }
    });
    
    module.exports = Mask;
});