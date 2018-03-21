<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="_token" content="{{ csrf_token() }}"/>
    <title>安畅网络 工单系统——待办工单列表</title>

    <!--[if lt IE 9]>
    <meta http-equiv="refresh" content="0;ie.html"/>
    <![endif]-->

    <link rel="shortcut icon" href="favicon.ico">
    <link href="/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/css/font-awesome.min.css?v=4.4.0" rel="stylesheet">
    <link href="/css/style.css?v=4.1.0" rel="stylesheet">
    <link rel="stylesheet" href="/css/plugins/bootstrap-table/bootstrap-table.css">
    <link rel="stylesheet" href="/css/job_list.css">
    <link rel="stylesheet" href="/css/handover.css">
    <style>
        .hiddenTable {
            display: none;
        }

        .table-fixpadding tr {
            height: 55px;
        }

        .table-fixpadding th, .table-fixpadding td {
            padding: 0 8px !important;
        }
    </style>
</head>
<body>
<div class=" wrapper-content" style="background-color: whitesmoke">
    <div class="row">
        <div class="col-sm-12">
            <div style="margin-top:5px;">
                <div class="tab-content" style="background-color: white">
                    <div class="hand-title">
                        <span class="J_menuItem" menuName="全部交接单" href="/handover/allList"
                              id="handoverSupport">全部交接单</span>
                        <span href="#tab-2" id="eventSupport" class="title_active">全部事件<span class="label_line"></span></span>
                    </div>
                    <div id="tab-2" class="">
                        <div class="full-height-scroll">
                            <form class="form-inline" style="padding: 14px">
                                预约处理时间:
                                <input type="text" class="form-control" style="width: 12%" placeholder=""
                                       id="eventStartTime">
                                至
                                <input type="text" class="form-control" style="width: 12%" placeholder=""
                                       id="eventEndTime">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <div class="input-group" style="min-width:560px;">
                                    <input type="text" class="form-control" name="searchInfo"
                                           id="searchInfo" value=""
                                           placeholder="请输入交接单编号/事件编号/工单编号/负责人／客户名 搜索">
                                    <span class="input-group-btn">
                            <a class="btn btn-info" style="background-color:#19b492" id="searchAll"
                               onclick="doNewSearch(this,'')">
                                <span class="glyphicon glyphicon-search">搜索</span>
                            </a>
                        </span>
                                    <input type="hidden" value="" id="type">
                                    <input type="hidden" value="" id="priority">
                                    <input type="hidden" value="" id="status">
                                    <input type="hidden" value="" id="supportId">
                                    <input type="hidden" value="" id="cusId">
                                    <input type="hidden" value="{{$handoverId}}" id="handoverId">
                                </div>
                            </form>
                            <div class="table-responsive" style="background-color: white">
                                <table id="eventTable" class="table-no-bordered table-fixpadding"
                                       style="text-align: center;color:#6b7d86" bgcolor="#FFFFFF"
                                       cellpadding="0px"
                                       cellspacing="0px" width="100%"
                                       data-pagination="true"
                                       data-show-export="true"
                                       data-page-size="10"
                                       data-id-field="Id"
                                       data-pagination-detail-h-align="right"
                                       data-page-list="[10, 25, 50, 100, ALL]"
                                       data-show-footer="false"
                                       data-side-pagination="server"
                                       data-url="/handover/getEventAllList?handoverId={{$handoverId}}"
                                       data-response-handler="responseHandler">
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- 全局js -->
<script src="/js/jquery.min.js?v=2.1.4"></script>
<script src="/js/jquery.slimscroll.min.js"></script>
<script src="/js/plugins/layer/layer.min.js"></script>
<script src="/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script type="text/javascript" src="/js/common.js"></script>
<script type="text/javascript" src="/js/job_list.js"></script>
<script src="/js/plugins/bootstrap-table/bootstrap-table.js"></script>
<!-- put your locale files after bootstrap-table.js -->
<script src="/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>
<script src="/js/plugins/layer/laydate/laydate.js"></script>

<script>
    $(function () {
        $(".table-responsive").css({"overflow": "inherit"});
    });
    var url = "{{env('JOB_URL')}}";

    var $eventTable = $('#eventTable'),
            $remove = $('.remove'),
            selections = [];

    function initTable() {
        $eventTable.bootstrapTable({
            pageSize: 10,
            striped: true,
            rowStyle: function (row, index) {
                return checkIsDoneStyle(row, index);
            },
            columns: [
                [
                    {
                        title: '事件编号',
                        valign: 'middle',
                        align: 'center',
                        field: 'id',
                        width: '10%',
                        formatter: function (value, row, index) {
                            var s = '<a class="J_menuItem"  title="事件' + row.id + ' 详情"' + 'href="/handover/eventDetails/' + row.id +'">' + row.id + '</a>';
                            return s;
                        }
                    },
                    {
                        title: '交接单编号',
                        valign: 'middle',
                        align: 'center',
                        field: 'handoverId',
                        width: '10%',
                        formatter: function (value, row, index) {
                            if (row.handoverId) {
                                if (row.status == '未处理') {
                                    var s = '<a class="showTitleTips J_menuItem"  title="交接单' + row.handoverId + ' 编辑" ' +
                                            '  id="handover_' + row.handoverId + '"  ' +
                                            'href="/handover/handoverEdit/' + row.handoverId + '?handoverId=' + row.handoverId + '">' + row.handoverId + '</a>';
                                } else {
                                    var s = '<a class="showTitleTips J_menuItem"  title="交接单' + row.handoverId + ' 详情" ' +
                                            '  id="handover_' + row.handoverId + '"  ' +
                                            'href="/handover/handoverDetails/' + row.handoverId + '?handoverId=' + row.handoverId + '">' + row.handoverId + '</a>';
                                }
                                return s;
                            } else {
                                return '无';
                            }
                        }
                    },
                    {
                        title: '工单编号',
                        valign: 'middle',
                        field: 'supportId',
                        width: '10%',
                        align: 'left',
                        formatter: function (value, row, index) {
                            if (row.supportId) {
                                return '<a class="J_menuItem" title="工单'+ row.supportId +'详情"' +
                                        'href="/wo/supportrefer/' + row.supportId + '">' + row.supportId + '</a>';
                            } else {
                                return '无';
                            }
                        }
                    },
                    {
                        title: '客户名称',
                        width: '15%',
                        field: 'CusName',
                        valign: 'middle',
                        align: 'left', formatter: function (value, row, index) {
                        if (row.CusName) {
                            var cusName = row.CusName.length > 10 ? row.CusName.substr(0, 10) + '...' : row.CusName;
                            var s = '<a title="客户详情"' +
                                    'href="{{env('JOB_URL', 'http://www.51idc.cn')}}/crm/user/finance/customer_detail.html?cusinfid=' + row.cusId + '" target="_blank">' + cusName + '</a>';
                            var identity = s + formatterIdentity(row);
                            return identity;
                        } else {
                            return '无';
                        }
                    }
                    },
                    {
                        field: 'type',
                        width: '8%',
                        title: '<div id="all-type-list" class="select-wrap"><span class="current-title"><span class="current-select">事件类型</span><i class="select-icon"></i></span>' +
                        '<ul class="select-list"><li class="select-list-item" value="">事件类型</li>@foreach($typeList as $type) <li class="select-list-item" value="{{$type->Code}}">{{$type->Means}}</li>@endforeach</ul></div>',
                        valign: 'middle',
                        align: 'left',
                        events: 'operateEvents'
                    },
                    {
                        title: '<div id="all-status-list" class="select-wrap"><span class="current-title"><span class="current-select">状态</span><i class="select-icon"></i></span>' +
                        '<ul class="select-list"><li class="select-list-item" value="">状态</li><li class="select-list-item" value="0">未处理</li><li class="select-list-item" value="1">已处理</li></ul></div>',
                        field: 'status',
                        valign: 'middle',
                        width: '10%',
                        align: 'center',
                        formatter: function (value, row, index) {
                            if (row.isInValidate == 1) {
                                return '已转移';
                            } else {
                                return row.status;
                            }
                        }
                    },
                    {
                        field: 'priority',
                        width: '6%',
                        title: '<div id="all-priority-list" class="select-wrap"><span class="current-title"><span class="current-select">优先级</span><i class="select-icon"></i></span>' +
                        '<ul class="select-list"><li class="select-list-item" value="">优先级</li><li class="select-list-item" value="0">一般</li><li class="select-list-item" value="1">重要</li></ul></div>',
                        valign: 'middle',
                        align: 'left',
                        events: 'operateEvents'
                    },
                    {
                        field: 'chargerId',
                        width: '8%',
                        title: '负责人',
                        valign: 'middle',
                        align: 'left',
                        formatter: function (value, row, index) {
                            return row.chargerId;
                        }
                    },
                    {
                        field: 'remindTs',
                        width: '10%',
                        title: '预约处理时间',
                        valign: 'middle',
                        align: 'left',
                    },
                    {
                        field: 'submitterId',
                        width: '10%',
                        title: '申请人<br/>申请时间',
                        valign: 'middle',
                        align: 'left',
                        formatter: function (value, row, index) {
                            var designer = row.submitterId != 0 ? row.submitterId : '无';
                            var s = designer + '<br/>' + row.ts;
                            return s;
                        }
                    }
                ]
            ],
            queryParamsType: "undefined",
            queryParams: function queryParams(params) {
                return {
                    pageSize: params.pageSize,
                    pageNumber: params.pageNumber,
                    'handoverId': $('#handoverId').val(),
                    'eventStartTime': $("#eventStartTime").val(),
                    'eventEndTime': $("#eventEndTime").val(),
                    'searchInfo': $("#searchInfo").val(),
                    'searchHand': $("#searchHand").val(),
                    'supportId': $("#supportId").val(),
                    'cusId': $("#cusId").val(),
                    'type': $('#type').val(),
                    'priority': $('#priority').val(),
                    'status': $('#status').val(),
                }
            }

        });

        layer.config({
            extend: 'extend/layer.ext.js'
        });
        var showDelete;
        var showEdit;
        var showComplete;
        var showTransfer;
        window.operateEvents = {
            'click .eventDelete': function (e, value, row, index) {
                var eventId = row.id;
                showDelete = layer.confirm('您确定要删除该事件吗?', {
                    title: "提示",
                    btn: ['确定', '取消']
                }, function () {
                    layer.prompt({
                        title: '请输入删除理由',
                        formType: 2
                    }, function (text) {
                        $.ajax({
                            type: "POST",
                            data: {'reason': text, 'eventId': eventId},
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                            },
                            url: "/handover/eventDelete/" + eventId,
                            success: function (data) {
                                if (data.status == 'success') {
                                    layer.msg('删除事件成功！', {icon: 1});
                                    $('#eventTable').bootstrapTable('refresh');
                                }
                            }
                        });
                    });
                });
            },
            'click .eventEdit': function (e, value, row, index) {
                var eventId = row.id;
                showEdit = layer.open({
                    type: 2,
                    title: '事件/编辑',
                    area: ['860px', '60%'],
                    content: ['/handover/eventEdit/' + eventId, 'no'],
                    end: function () {
                        $('#eventTable').bootstrapTable('refresh');
                    }
                });
            },
            'click .eventTransfer': function (e, value, row, index) {
                var eventId = row.id;
                showTransfer = layer.open({
                    type: 2,
                    title: '事件/转移',
                    area: ['860px', '60%'],
                    content: ['/handover/eventTransfer/' + eventId, 'no']
                });
            },
            'click .eventComplete': function (e, value, row, index) {
                var eventId = row.id;
                showComplete = layer.confirm('事件完成后将不得修改，确定完成吗?', {
                    title: "提示",
                    btn: ['确定', '取消']
                }, function (text) {
                    $.ajax({
                        type: "POST",
                        data: {'reason': text, 'eventId': eventId},
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                        },
                        url: "/handover/eventComplete/" + eventId,
                        success: function (data) {
                            if (data.status == 'success') {
                                layer.msg('该事件已完成！', {icon: 1});
                                $('#eventTable').bootstrapTable('refresh');
                            }
                        }
                    });
                });
            }
        };
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


    function getHeight() {
        return $(window).height() - $('h1').outerHeight(true);
    }

    function checkIsDoneStyle(row, index) {
        if (row.status == "已处理" || row.isInValidate == 1) {
            return {
                css: {"color": "#CCCCCF"}
            }
        }
        return {css: {"": ""}};
    }
    function doNewSearch(data, values) {
        if (data == "all-type-list") {
            $("#type").val(values);
        }
        if (data == "all-priority-list") {
            $("#priority").val(values);
        }
        if (data == "all-status-list") {
            $("#status").val(values);
        }

        $('#eventTable').bootstrapTable('refresh', {
            query: {
                'eventStartTime': $("#eventStartTime").val(),
                'eventEndTime': $("#eventEndTime").val(),
                'searchInfo': $("#searchInfo").val(),
                'searchHand': $("#searchHand").val(),
                'supportId': $("#supportId").val(),
                'cusId': $("#cusId").val(),
                'type': $('#type').val(),
                'priority': $('#priority').val(),
                'status': $('#status').val(),
                'handoverId': $('#handoverId').val(),
                'pageNumber': 1
            }
        });
    }

    initTable();
    $(function () {
        pullDownChoice("all-type-list", function (param) {
            doNewSearch("all-type-list", param);
        });
        pullDownChoice("all-priority-list", function (param) {
            doNewSearch("all-priority-list", param);
        });
        pullDownChoice("all-status-list", function (param) {
            doNewSearch("all-status-list", param);
        });
        showToolTip($("._handle_tip"));
        showTableToolTip("._jobState");
        var eventStartTime = {
            elem: '#eventStartTime',
            format: 'YYYY-MM-DD hh:mm',
            istime: true,
            choose: function (datas) {
                eventEndTime.min = datas;
                eventEndTime.start = datas
            }
        }
        var eventEndTime = {
            elem: '#eventEndTime',
            format: 'YYYY-MM-DD hh:mm',
            istime: true,
            choose: function (datas) {
                eventStartTime.max = datas;
            }
        }
        laydate(eventStartTime);
        laydate(eventEndTime);
    })

    $('#searchInfo').bind('keypress',function(event){
        var searchAll = $("#searchAll");
        if(event.keyCode == "13")
        {
            searchAll.click();
        }
    });
</script>
</body>

</html>