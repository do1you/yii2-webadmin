/* Set the defaults for DataTables initialisation */
$.extend(true, $.fn.dataTable.defaults, {
    "sDom": "<'row'<'col-xs-6'l><'col-xs-6'f>r>t<'row'<'col-xs-6'i><'col-xs-6'p>>",
    "sPaginationType": "bootstrap",
    "oLanguage": {
        "oAria": {
			"sSortAscending": ": activate to sort column ascending",
			"sSortDescending": ": activate to sort column descending"
		},
		"oPaginate": {
			"sFirst": "第一页",
			"sLast": "最后页",
			"sNext": "下一页",
			"sPrevious": "上一页"
		},
		"sEmptyTable": "没有查询到您要的数据",
		"sInfo": "第 _START_ 到 _END_ 条记录 — 总计 _TOTAL_ 条",
		"sInfoEmpty": "记录数为0",
		"sInfoFiltered": "(全部记录数 _MAX_ 条)",
		"sInfoPostFix": "",
		"sDecimal": "",
		"sThousands": ",",
		"sLengthMenu": "显示 _MENU_ 条 &nbsp; &nbsp;",
		"sLoadingRecords": "正在加载数据...",
		"sProcessing": "处理中...",
		"sSearch": "",
		"sSearchPlaceholder": "",
		"sUrl": "",
		"sZeroRecords": "没有您要搜索的内容"
    }
});




/* Default class modification */
$.extend($.fn.dataTableExt.oStdClasses, {
    "sWrapper": "dataTables_wrapper form-inline",
    "sFilterInput": "form-control input-sm",
    "sLengthSelect": "form-control input-sm"
});


/* API method to get paging information */
$.fn.dataTableExt.oApi.fnPagingInfo = function (oSettings) {
    return {
        "iStart": oSettings._iDisplayStart,
        "iEnd": oSettings.fnDisplayEnd(),
        "iLength": oSettings._iDisplayLength,
        "iTotal": oSettings.fnRecordsTotal(),
        "iFilteredTotal": oSettings.fnRecordsDisplay(),
        "iPage": oSettings._iDisplayLength === -1 ?
			0 : Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength),
        "iTotalPages": oSettings._iDisplayLength === -1 ?
			0 : Math.ceil(oSettings.fnRecordsDisplay() / oSettings._iDisplayLength)
    };
};


/* Bootstrap style pagination control */
$.extend($.fn.dataTableExt.oPagination, {
    "bootstrap": {
        "fnInit": function (oSettings, nPaging, fnDraw) {
            var oLang = oSettings.oLanguage.oPaginate;
            var fnClickHandler = function (e) {
                e.preventDefault();
                if (oSettings.oApi._fnPageChange(oSettings, e.data.action)) {
                    fnDraw(oSettings);
                }
            };

            $(nPaging).append(
				'<ul class="pagination">'+
					'<li class="first disabled"><a href="#">'+oLang.sFirst+'</a></li>'+
					'<li class="prev disabled"><a href="#">' + oLang.sPrevious + '</a></li>' +
					'<li class="next disabled"><a href="#">' + oLang.sNext + '</a></li>' +
					'<li class="last disabled"><a href="#">'+oLang.sLast+'</a></li>'+
					'<li class="goto"><input type="text" style="width:50px;padding:6px 12px;border-radius:0px 4px 4px 0px;text-align:center;" id="redirect" class="redirect"></li>'+
                '</ul>'
			);
			$(nPaging).find(".redirect").keyup(function(e){
                var ipage = parseInt($(this).val());
                var oPaging = oSettings.oInstance.fnPagingInfo();
        		if(isNaN(ipage) || ipage<1){
                    ipage = 1;
                }else if(ipage>oPaging.iTotalPages){
                    ipage=oPaging.iTotalPages;
                }
                $(this).val(ipage);
                ipage--;
                oSettings._iDisplayStart = ipage * oPaging.iLength;
                fnDraw( oSettings );
        	});
            var els = $('a', nPaging);
				$(els[0]).bind( 'click.DT', {
					action: "first"
				}, fnClickHandler );
				$(els[1]).bind( 'click.DT', {
					action: "previous"
				}, fnClickHandler );
				$(els[2]).bind( 'click.DT', {
					action: "next"
				}, fnClickHandler );
				$(els[3]).bind( 'click.DT', {
					action: "last"
				}, fnClickHandler );
        },

        "fnUpdate": function (oSettings, fnDraw) {
            var iListLength = 7;
            var oPaging = oSettings.oInstance.fnPagingInfo();
            var an = oSettings.aanFeatures.p;
            var i, ien, j, sClass, iStart, iEnd, iHalf = Math.floor(iListLength / 2);

            if (oPaging.iTotalPages < iListLength) {
                iStart = 1;
                iEnd = oPaging.iTotalPages;
            }
            else if (oPaging.iPage <= iHalf) {
                iStart = 1;
                iEnd = iListLength;
            } else if (oPaging.iPage >= (oPaging.iTotalPages - iHalf)) {
                iStart = oPaging.iTotalPages - iListLength + 1;
                iEnd = oPaging.iTotalPages;
            } else {
                iStart = oPaging.iPage - iHalf + 1;
                iEnd = iStart + iListLength - 1;
            }

            for (i = 0, ien = an.length ; i < ien ; i++) {
                $('li:gt(1)', an[i]).filter(':not(.next,.last,.goto)').remove();

                // Add the new list items and their event handlers
                for (j = iStart ; j <= iEnd ; j++) {
                    sClass = (j == oPaging.iPage + 1) ? 'class="active"' : '';
                    $('<li ' + sClass + '><a href="#">' + j + '</a></li>')
						.insertBefore($('li.next', an[i])[0])
						.bind('click', function (e) {
						    e.preventDefault();
						    oSettings._iDisplayStart = (parseInt($('a', this).text(), 10) - 1) * oPaging.iLength;
						    fnDraw(oSettings);
						});
                }

                // Add / remove disabled classes from the static elements
                if (oPaging.iPage === 0) {
                    $('li.first,li.prev', an[i]).addClass('disabled');
                } else {
                    $('li.first,li.prev', an[i]).removeClass('disabled');
                }

                if (oPaging.iPage === oPaging.iTotalPages - 1 || oPaging.iTotalPages === 0) {
                    $('li.next,li.last', an[i]).addClass('disabled');
                } else {
                    $('li.next,li.last', an[i]).removeClass('disabled');
                }
            }
        }
    }
});


/*
 * TableTools Bootstrap compatibility
 * Required TableTools 2.1+
 */
if ($.fn.DataTable.TableTools) {
    // Set the classes that TableTools uses to something suitable for Bootstrap
    $.extend(true, $.fn.DataTable.TableTools.classes, {
        "container": "DTTT btn-group",
        "buttons": {
            "normal": "btn btn-default",
            "disabled": "disabled"
        },
        "collection": {
            "container": "DTTT_dropdown dropdown-menu",
            "buttons": {
                "normal": "",
                "disabled": "disabled"
            }
        },
        "print": {
            "info": "DTTT_print_info modal"
        },
        "select": {
            "row": "active"
        }
    });

    // Have the collection use a bootstrap compatible dropdown
    $.extend(true, $.fn.DataTable.TableTools.DEFAULTS.oTags, {
        "collection": {
            "container": "ul",
            "button": "li",
            "liner": "a"
        }
    });
}
