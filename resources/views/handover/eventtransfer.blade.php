<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>安畅网络 交接单系统</title>
    <!--[if lt IE 9]>
    <meta http-equiv="refresh" content="0;ie.html"/>
    <![endif]-->
    <link href="/css/usercenter.css?2" rel="stylesheet" type="text/css">
    <link rel="shortcut icon" href="favicon.ico">
    <link href="/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/css/font-awesome.min.css?v=4.4.0" rel="stylesheet">
    <link href="/css/animate.css" rel="stylesheet">
    <link href="/css/style.css?v=4.1.0" rel="stylesheet">
    <link href="/css/font.css" rel="stylesheet" type="text/css">
    <link href="/css/print.css" rel="stylesheet" type="text/css">
    <!-- 第三方插件 -->
    <link type="text/css" rel="stylesheet" href="/js/plugins/layer/laydate/need/laydate.css">
    <link type="text/css" rel="stylesheet" href="/js/plugins/layer/laydate/skins/default/laydate.css" id="LayDateSkin">
    <!-- 自定义css -->
    <link rel="stylesheet" type="text/css" href="/css/my.css">
    <link href="/css/user.css" rel="stylesheet" type="text/css">
    <style type="text/css">
        #handoverTable {
            text-align: center;
            color: #6b7d86;
        }

        .pagination {
            margin: 0 0;
        }

    </style>

</head>
<body>
<div>
    <div class="col-sm-6">
        <div class="" style="margin-top: 3px;border:0px;width:760px">
            <form class="form-inline" onsubmit="return false">
                &nbsp;&nbsp;&nbsp;&nbsp;
                <div class="input-group" style="min-width:550px; margin-left: -15px;" >
                    <input type="text" class="form-control" name="searchTransfer"
                           id="searchTransfer" value=""
                           placeholder="交接单编号/负责人搜索">
                    <span class="input-group-btn">
                            <a class="btn btn-info" style="background-color:#19b492" id="searchTrans"
                               onclick="doNewSearch(this,'')">
                                <span class="glyphicon glyphicon-search">搜索</span>
                            </a>
                        </span>
                </div>
                <input id="eventId" value="{{$eventId}}" type="hidden"/>
            </form>
            <table id="handoverTable" class="table-no-bordered active"
                   style=" text-align: center;color:#6b7d86;" bgcolor="#FFFFFF"
                   cellpadding="0"
                   cellspacing="0" width="100%"
                   data-pagination="true"
                   data-show-export="true"
                   data-page-size="9"
                   data-page-list="[5]"
                   data-id-field="Id"
                   data-show-footer="false"
                   data-side-pagination="server"
                   data-url="/handover/getHandoverList"
                   data-response-handler="responseHandler">
            </table>
            <input type="hidden" id="hiddenHandoverId" value="">
            <input type="hidden" id="hiddenOldHandoverId" value="{{$handoverId}}">
        </div>
    </div>
</div>
{{--<!-- 全局js -->--}}
<script src="/js/jquery.min.js?v=2.1.4"></script>
<script src="/js/plugins/layer/layer.min.js"></script>
<script src="/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/js/jquery.slimscroll.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script type="text/javascript" src="/render/hplus/js/contabs.js"></script>
<script type="text/javascript" src="/js/common.js"></script>
<script type="text/javascript" src="/js/job_list.js"></script>
<script type="text/javascript" src="/js/handover_edit.js?1"></script>
{{--<!-- 第三方插件 -->--}}
<script src="/js/plugins/bootstrap-table/bootstrap-table.js"></script>
<!-- put your locale files after bootstrap-table.js -->
<script src="/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>
<script src="/js/plugins/layer/laydate/laydate.js"></script>


<!-- 自定义js -->
<script>
    function closeFrame() {
        var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
        parent.layer.close(index); //再执行关闭
    }
    var $handoverTable = $('#handoverTable'),
            selections = [];
    function initTable() {
        $handoverTable.bootstrapTable({
            columns: [
                [{
                    title: '交接单编号',
                    valign: 'middle',
                    field: 'id',
                    width: '15%',
                    align: 'center'
                }, {
                    title: '负责人',
                    valign: 'middle',
                    field: 'chargerId',
                    width: '15%',
                    align: 'center',
                    events: 'operateEvents'
                }, {
                    title: '注意事项',
                    field: 'notes',
                    valign: 'middle',
                    width: '20%',
                    align: 'center',
                    formatter: function (value, row, index) {
                        var s = '<a class="showTitleTips" id="title_' + row.id + '" ' +
                                '>' + stringText(row.notes) + '</a>';
                        return s;
                    },
                    events: 'operateEvents'
                }, {
                    field: 'submitterId',
                    title: '提交人',
                    valign: 'middle',
                    align: 'center',
                    width: '10%',

                }, {
                    title: '操作',
                    field: 'operation',
                    valign: 'middle',
                    width: '15%',
                    align: 'center',
                    formatter: function (value, row, index) {
                        return '<a style="color:green" >转移</a>';
                    }
                }
                ]
            ],
            queryParamsType: "undefined",
            queryParams: function queryParams(params) {
                return {
                    pageSize: params.pageSize,
                    pageNumber: params.pageNumber,
                    eventId: $("#eventId").val(),
                    searchTransfer: $("#searchTransfer").val(),
                }
            }
        });
        var custips;
        window.operateEvents = {
            'mouseover .showTitleTips': function (e, value, row, index) {
                custips = layer.tips('<div style="word-wrap: break-word;color: #ffffff">注意事项：' + row.notes + '</div>'
                        , '#title_' + row.id, {time: 0, tips: [2, '#999999'], maxWidth: 400});
                $('.supportBody img').each(function () {
                    var src = $(this).attr("src");
                    if (src.substr(0, 7).toLowerCase() != "http://") {
                        $(this).attr("src", url + src);
                    }
                });
            },
            'mouseout .showTitleTips': function (e, value, row, index) {
                layer.close(custips);
            }
        }
        $handoverTable.on('click-cell.bs.table', function ($element, field, value, row) {
            if (field == 'operation') {
                $('#hiddenHandoverId').val(row.id);
                if ($('#hiddenHandoverId').val() != 'undefined') {
                    layer.confirm('您确定要转移到此交接单?', {icon: 3, title: '提示'}, function (index) {
                        $.ajax({
                            type: "POST",
                            data: {
                                'handoverId': $('#hiddenHandoverId').val(),
                                'handEventId': $('#eventId').val(),
                                'oldId':$('#hiddenOldHandoverId').val()
                            },
                            url: "/handover/createTransfer",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (arr) {
                                if (arr.status == 'success') {
                                    layer.msg(arr.msg, {
                                        icon: 1,
                                        time: 2000 //1秒关闭
                                    });
                                    layer.close(index);
                                } else {
                                    layer.msg(arr.msg, {
                                        icon: 0,
                                        time: 2000 //1秒关闭
                                    });
                                }
                                setTimeout("closeFrame()", 2000);
                            }
                        });
                    });
                }
                else {
                    alert("转移失败！");
                }
            }
        })
    }

    function getIdSelections() {
        return $.map($table.bootstrapTable('getSelections'), function (row) {
            return row.id
        });
    }

    function responseHandler(res) {
        $.each(res.rows, function (i, row) {
            row.state = $.inArray(row.id, selections) !== -1;
        });
        return res;
    }
    function substringLen(text, length) {
        var length = arguments[1] ? arguments[1] : 7;
        suffix = "";
        if (text.length > length) {
            suffix = "..";
        }
        return text.substr(0, length) + suffix;
    }
    function doNewSearch(data, values) {//检索筛选
        $('#handoverTable').bootstrapTable('refresh', {
            query: {
                'searchTransfer': $("#searchTransfer").val(),
                'pageNumber': 1
            }
        });
    }
    initTable();
    $(function () {
        showToolTip($("._handle_tip"));
        showTableToolTip("._jobState");
    })
</script>
</body>
</html>