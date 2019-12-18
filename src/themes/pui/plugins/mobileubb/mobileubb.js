/**
 @Name：mobileubb V0.1
 @Author：统一 54901801@qq.com
 @Date：2015-7-24 
 */
define('pui/mobileubb',['kindeditor','jquery'],function(require,exports,module){
	module.exports = (function(K,$){
		return function(options){
			var src = pui.resolve('kindeditor');
			options = $.extend({
				basePath : src.substring(0, src.lastIndexOf('/') + 1),
				resizeType : 1,
				allowPreviewEmoticons : false,
				allowImageUpload : true,
				allowFileManager : true,

				// 手机版网页编辑器设置
				width : '320px',
				height: '480px',
				minWidth : '320px',
				minHeight : '480px',
				newlineTag : 'p', // 换行标识
				urlType : 'domain', // 路径类型
				pasteType : 1, // 只能贴文字
				useContextmenu : false, //  禁止右链
				htmlTags : { // 保留标签
					font : ['color', 'size', 'face', '.background-color'],
					span : [
							'.color', '.background-color', '.font-size', '.font-family', '.background',
							'.font-weight', '.font-style', '.text-decoration', '.vertical-align', '.line-height'
					],
					div : [
							'align', '.border', '.margin', '.padding', '.text-align', '.color',
							'.background-color', '.font-size', '.font-family', '.font-weight', '.background',
							'.font-style', '.text-decoration', '.vertical-align', '.margin-left'
					],
					table: [
							'border', 'cellspacing', 'cellpadding', 'width', 'height', 'align', 'bordercolor',
							'.padding', '.margin', '.border', 'bgcolor', '.text-align', '.color', '.background-color',
							'.font-size', '.font-family', '.font-weight', '.font-style', '.text-decoration', '.background',
							'.width', '.height', '.border-collapse'
					],
					'td,th': [
							'align', 'valign', 'width', 'height', 'colspan', 'rowspan', 'bgcolor',
							'.text-align', '.color', '.background-color', '.font-size', '.font-family', '.font-weight',
							'.font-style', '.text-decoration', '.vertical-align', '.background', '.border'
					],
					a : ['href', 'target', 'name'],
					embed : ['src', 'width', 'height', 'type', 'loop', 'autostart', 'quality', '.width', '.height', 'align', 'allowscriptaccess'],
					img : ['src', 'width', 'height', 'border', 'alt', 'title', 'align', '.width', '.height', '.border'],
					'p,ol,ul,li,blockquote,h1,h2,h3,h4,h5,h6' : [
							'align', '.text-align', '.color', '.background-color', '.font-size', '.font-family', '.background',
							'.font-weight', '.font-style', '.text-decoration', '.vertical-align', '.text-indent', '.margin-left'
					],
					pre : ['class'],
					hr : ['class', '.page-break-after'],
					'br,tbody,tr,strong,b,sub,sup,em,i,u,strike,s,del' : []
				},
				items : ['formatblock', 'fontname', 'fontsize',  'forecolor', 'hilitecolor', 'bold', 'italic', 'underline', 'strikethrough', 'image', 'hr', 'undo', 'redo', 'preview', 'quickformat'] // 'source','preview','quickformat',
			}, (options || {}));

			if(options.plainText){ // 是否纯文本
				options.htmlTags = {
					img : ['src', 'width', 'height', 'alt', 'title'],
					'div,p,br,h1,h2,h3,h4,h5,h6' : ['align']
				};
				options.items = ['formatblock', 'image', 'undo', 'redo', 'preview']; // 'source','preview','quickformat',
			}

			var selector = options.dom || this;
			if($(selector).attr('readonly') || $(selector).attr('disabled')) options.readonlyMode = true;
			delete options.dom;
			K.create(selector, options);
		};
	})(require('kindeditor'),(window.jQuery || require('jquery')));
});