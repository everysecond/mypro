<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>安畅网络 问题系统</title>
    <!--[if lt IE 9]>
    <meta http-equiv="refresh" content="0;ie.html"/>
    <![endif]-->
    <link href="/css/usercenter.css" rel="stylesheet" type="text/css">
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
    <link href="/css/batch_style.css" rel="stylesheet" type="text/css">
    <style type="text/css">
        #devTable {
            text-align: center;
            color: #6b7d86;
        }

        .pagination {
            margin: 0 0;
        }

        .relateIssue td{font-size: 12px !important;font-family: "宋体";}
        .relateIssue a{color:#6b7d86;}
        .relateIssue a:hover{color:red;}
    </style>
</head>
<body>
<div>
    <div class="col-sm-6 relateIssue">
        <div class="" style="margin-top: 3px;border:0px;width:760px">
            <form class="form-inline" onsubmit="return false">
                &nbsp;&nbsp;&nbsp;&nbsp;
                <div class="input-group" style="min-width:550px; margin-left: -15px">
                    <input type="text" class="form-control" name="searchInfo"
                           id="searchInfo" value=""
                           placeholder="问题单号/标题搜索">
                    <span class="input-group-btn">
                            <a class="btn btn-info" style="background-color:#19b492" id="searchAll"
                               onclick="doNewSearch(this,'')">
                                <span class="glyphicon glyphicon-search">搜索</span>
                            </a>
                        </span>
                </div>
                <input id="supportId" value="{{$supportId}}" type="hidden"/>
            </form>
            <table id="devTable" class="table-no-bordered active"
                   style=" text-align: center;color:#6b7d86;" bgcolor="#FFFFFF"
                   cellpadding="0"
                   cellspacing="0" width="100%"
                   data-pagination="true"
                   data-show-export="true"
                   data-page-size="9"
                   data-page-list="[5]"
                   data-id-field="Id"
                   data-pagination-detail-h-align="right"
                   data-show-footer="false"
                   data-side-pagination="server"
                   data-url="/support/getRelateIssue"
                   data-response-handler="responseHandler">
            </table>
            <div class="batch">
                <button data-table="devTable"  class="batchRelate">批量关联</button>
            </div>
        </div>
    </div>
</div>
{{--<!-- 全局js -->--}}
<script src="/js/jquery.min.js?v=2.1.4"></script>
<script src="/js/plugins/layer/layer.min.js"></script>
<script src="/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/js/jquery.slimscroll.min.js"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/job_list.js"></script>
<script type="text/javascript" src="/render/hplus/js/contabs.js"></script>
<script type="text/javascript" src="/js/common.js"></script>
<script type="text/javascript" src="/js/job_list.js"></script>
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
    var $devTable = $('#devTable'),
            selections = [];
    function initTable() {
        $devTable.bootstrapTable({
            columns: [
                [{
                    field: 'state',
                    checkbox: true,
                    align: 'middle',
                    valign: 'middle',
                    width: '5%',
                },{
                    title: '问题单号',
                    valign: 'middle',
                    field: 'issueNo',
                    width: '15%',
                    align: 'left'
                }, {
                    title: '问题标题',
                    valign: 'middle',
                    field: 'issueTitle',
                    width: '15%',
                    align: 'center',
                    formatter: function (value, row, index) {
                        var s = '<a class="showTitleTips J_menuItem"  menuName="' + row.Id + '"  id="title_' + row.Id + '" ' +
                                '>' + substringLen(row.issueTitle,20) + '</a>';
                        return s;
                    },
                    events: 'operateEvents'
                }, {
                    title: '优先级',
                    field: 'issuePriority',
                    valign: 'middle',
                    width: '20%',
                    align: 'center',
                    formatter: function (value, row, index) {
                        var s = substringLen(row.issuePriority);
                        return s;
                    },
                }, {
                    field: 'issueCategory',
                    title: '问题类别',
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
                        return '<a style="color:green" >点击关联</a>';
                    }
                }
                ]
            ],
            queryParamsType: "undefined",
            queryParams: function queryParams(params) {
                return {
                    pageSize: params.pageSize,
                    pageNumber: params.pageNumber,
                    supportId: $("#supportId").val(),
                    searchInfo: $("#searchInfo").val(),
                }
            }
        });
        var custips;
        window.operateEvents = {
            'mouseover .showTitleTips': function (e, value, row, index) {
                custips = layer.tips('<div style="word-wrap: break-word;color: #ffffff">问题标题：' + row.issueTitle + '</div>'
                        , '#title_' + row.Id, {time: 0, tips: [2, '#999999'], maxWidth: 400});
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
        $devTable.on('click-cell.bs.table', function ($element, field, value, row) {
            if (field == 'operation') {
                parent.$('#hiddenIssueId').val(row.Id);
                if ($('#hiddenIssueId').val() != '') {
                    layer.confirm('您确定要关联此问题?', {icon: 3, title: '提示'}, function (index) {
                        $.ajax({
                            type: "post",
                            data: {
                                issueId: parent.$('#hiddenIssueId').val(),
                                supportId: parent.$('#supportId').val(),
                                triggerId: parent.$('#supportId').val()
                            },
                            dataType: 'json',
                            url: "/correlation/create",
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (data) {
                                if (data.status == 'ok') {
                                    layer.msg(data.msg, {
                                        icon: 1,
                                        time: 2000 //1秒关闭
                                    });
                                } else {
                                    layer.msg(data.msg, {
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
                    alert("关联失败！");
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
        $('#devTable').bootstrapTable('refresh', {
            query: {
                'searchInfo': $("#searchInfo").val(),
                'pageNumber': 1
            }
        });
    }
    initTable();
    $(function () {
        showToolTip($("._handle_tip"));
        showTableToolTip("._jobState");
    })
    /**
     * 批量关联
     */
    var url = "{{env('JOB_URL')}}";
    layer.config({
        extend: 'extend/layer.ext.js'
    });
    $('.batchRelate').click(function () {
        var table = $(this).data('table');
        console.log(table);
        layer.confirm('确定要批量关联问题吗?', {
                    title: "提示",
                    btn: ['确定', '取消']
                },
                function () {
                    var selected = $('#'+table).bootstrapTable('getSelections');
                    console.log(selected.length);
                    if (selected.length < 1) {
                        layer.msg('请选择要关联的问题！', {icon: 2});
                        return false;
                    }
                    $.ajax({
                        type: "POST",
                        data: {'issueIds': selected,'supportId': parent.$('#supportId').val(),
                            'triggerId': parent.$('#supportId').val()},
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: "/correlation/batchSupportIssue",
                        success: function (data) {
                            if (data.status == 'ok') {
                                layer.msg('批量关联成功！', {icon: 1});
                            }
                            else {
                                layer.msg('批量关联失败！', {
                                    icon: 0,
                                    time: 2000 //1秒关闭
                                });
                            }
                            setTimeout("closeFrame()", 2000);
                        }
                    })
                }
        )
    });
</script>
</body>
</html>