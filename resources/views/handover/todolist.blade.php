<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="_token" content="{{ csrf_token() }}"/>
    <title>安畅网络 交接单系统——待办列表</title>

    <!--[if lt IE 9]>
    <meta http-equiv="refresh" content="0;ie.html"/>
    <![endif]-->

    <link rel="shortcut icon" href="favicon.ico">
    <link href="/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/css/font-awesome.min.css?v=4.4.0" rel="stylesheet">
    <link href="/css/style.css?v=4.1.0" rel="stylesheet">
    <link rel="stylesheet" href="/css/plugins/bootstrap-table/bootstrap-table.css">
    <link rel="stylesheet" href="/css/job_list.css">
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
        .nav-tabs > li > a {
            padding: 10px;
        }
    </style>
</head>
<body>
<div class=" wrapper-content" style="background-color: whitesmoke">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="job-list-top">
                    <button class="submit-btn J_menuItem" menuname="新增交接单" href="/handover/handoverApply"> 新增交接单
                    </button>
                    <input type="hidden" value="" id="cusType">
                    <input type="hidden" value="" id="Status">
                    <input type="hidden" value="" id="timeOutIds">
                    <input type="hidden" value="" id="allTimeOutIds">
                </div>
            </div>
            <div style="margin-top:5px;">
                <div class="tab-content" style="background-color: white">
                    <ul class="nav nav-tabs" id="nav-tabs">
                        <span class="pull-right small text-muted"></span>
                        <li class="active">
                            <a aria-expanded="true" data-toggle="tab" href="#tab-1" id="handoverSupport">
                                <i class="fa fa-codepen"></i>待办交接单 <span id="span1"></span></a></li>
                        <li class="">
                            <a aria-expanded="false" data-toggle="tab" href="#tab-2"
                               id="eventSupport"><i class="fa fa-print"></i>待办事件 <span id="span2"></span></a>

                        </li>
                    </ul>
                    <div id="tab-1" class="">
                        <div class="full-height-scroll">
                            <div class="table-responsive" style="background-color: white">
                                <table id="handoverTable" class="table-no-bordered table-fixpadding"
                                       style="text-align: center;color:#6b7d86" bgcolor="#FFFFFF"
                                       cellpadding="0"
                                       cellspacing="0" width="100%"
                                       data-pagination="true"
                                       data-show-export="true"
                                       data-page-size="10"
                                       data-id-field="Id"
                                       data-pagination-detail-h-align="right"
                                       data-page-list="[10, 25, 50, ALL]"
                                       data-show-footer="false"
                                       data-side-pagination="server"
                                       data-url="/handover/todoListData"
                                       data-response-handler="responseHandler">
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="tab-2" class="hiddenTable">
                        <div class="full-height-scroll">
                            <form class="form-inline" style="padding: 14px">
                                预约处理时间:
                                <input type="text" class="form-control" style="width: 12%" placeholder=""
                                       id="eventStartTime">
                                至
                                <input type="text" class="form-control" style="width: 12%" placeholder=""
                                       id="eventEndTime">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <div class="input-group" style="min-width:500px;">
                                    <input type="text" class="form-control" name="searchInfo"
                                           id="searchInfo" value=""
                                           placeholder="请输入事件编号/交接单编号/工单编号/客户名 搜索">
                                    <span class="input-group-btn">
                            <a class="btn btn-info" style="background-color:#19b492" id="searchAll"
                               onclick="doNewSearch(this,'')">
                                <span class="glyphicon glyphicon-search">搜索</span>
                            </a>
                        </span>
                                    <input type="hidden" id="type">
                                    <input type="hidden" id="priority">
                                    <input type="hidden" id="handoverpriority">
                                    <input type="hidden" id="remindType">
                                    <input type="hidden" id="supportId">
                                    <input type="hidden" id="cusId">
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
                                       data-page-list="[10, 25, 50, ALL]"
                                       data-show-footer="false"
                                       data-side-pagination="server"
                                       data-url="/handover/getEventTodoList"
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
<script type="text/javascript" src="/js/handover.js"></script>
<script src="/js/plugins/bootstrap-table/bootstrap-table.js"></script>
<!-- put your locale files after bootstrap-table.js -->
<script src="/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>
<script src="/js/plugins/layer/laydate/laydate.js"></script>

<script>
    $('#searchInfo').bind('keypress',function(event){
        var searchAll = document.getElementById("searchAll");
        if(event.keyCode == "13")
        {
            searchAll.click();
        }
    });
    $(function () {
        $(".table-responsive").css({"overflow": "inherit"});
    });
    var url = "{{env('JOB_URL')}}";

    var $handoverTable = $('#handoverTable'),
            $eventTable = $('#eventTable'),
            $remove = $('.remove'),
            selections = [];

    function initTable() {
        $handoverTable.bootstrapTable({
            pageSize: 10,
            striped: true,
            columns: [
                [
                    {
                        title: '交接单编号',
                        valign: 'middle',
                        align: 'center',
                        field: 'id',
                        width: '10%',
                        formatter: function (value, row, index) {
                            var s = '<a class="showTitleTips J_menuItem"  title="交接单' + row.id + ' 编辑" ' +
                                    '  id="handover_' + row.id + '"  ' +
                                    'href="/handover/handoverEdit/' + row.id + '?handoverId=' + row.id + '">' + row.id + '</a>';
                            return s;
                        }
                    },
                    {
                        title: '负责人',
                        valign: 'middle',
                        width: '10%',
                        field: 'chargerId',
                        align: 'left',
                        formatter: function (value, row, index) {
                            var s = row.chargerId;
                            return s;
                        }
                    },
                    {
                        field: 'notes',
                        width: '12%',
                        title: '注意事项',
                        valign: 'middle',
                        align: 'left',
                        formatter: function (value, row, index) {
                            var s = '<span class="showTitleTips" id="title_' + row.id + '">' + stringText(row.notes) +'</span>';
                            return s;
                        },
                        events: 'operateEvents'
                    },
                    {
                        field: 'priority',
                        width: '10%',
                        title: '<div id="allhandover-priority-list" class="select-wrap"><span class="current-title"><span class="current-select">优先级</span><i class="select-icon"></i></span>' +
                        '<ul class="select-list"><li class="select-list-item" value="">优先级</li><li class="select-list-item" value="0">一般</li><li class="select-list-item" value="1">重要</li></ul></div>',
                        valign: 'middle',
                        align: 'left',
                        events: 'operateEvents',
                        formatter: function (value, row, index) {
                            var s = row.priority > 0 ? '重要' : '一般';
                            return s;
                        }
                    },
                    {
                        field: 'remindType',
                        width: '10%',
                        title: '<div id="allhandover-type-list" class="select-wrap"><span class="current-title">' +
                        '<span class="current-select">提醒方式</span><i class="select-icon"></i></span>' +
                        '<ul class="select-list"><li class="select-list-item" value="">提醒方式</li>' +
                        '<li class="select-list-item" value="email">邮件</li>' +
                        '<li class="select-list-item" value="sms">短信</li>' +
                        '<li class="select-list-item" value="wechat">微信</li></ul></div>',
                        valign: 'middle',
                        align: 'left',
                    },
                    {
                        field: 'expectTs',
                        width: '15%',
                        title: '事件数量',
                        valign: 'middle',
                        align: 'left',
                        formatter: function (value, row, index) {
                            var s = row.notDone + '/' + row.allEvents;
                            return '<a class="J_menuItem" Title="交接单' + row.id + '待办事件" href="/handover/eventTodoList?handoverId=' + row.id + '">' + '<u>' + row.notDone + '</u>' + '</a>' + '/' + '<a class="J_menuItem" Title="交接单' + row.id + '全部事件" href="/handover/eventAllList?handoverId=' + row.id + '">' + '<u>' + row.allEvents + '</u>' + '</a>';
                        }
                    },
                    {
                        field: 'submitterId',
                        width: '8%',
                        title: '提交人',
                        valign: 'middle',
                        align: 'left',
                        formatter: function (value, row, index) {
                            var s = row.submitterId;
                            return s;
                        }
                    }, {
                    field: 'ts',
                    width: '15%',
                    title: '提交时间',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var s = row.ts;
                        return s;
                    }
                },
                    {
                        field: 'operate',
                        width: '14%',
                        title: '操作',
                        valign: 'middle',
                        align: 'left',
                        formatter: function (value, row, index) {
                            var s = '<a class="showTitleTips J_menuItem" title="交接单' + row.id + ' 编辑" ' +
                                    'href="/handover/handoverEdit/' + row.id + '?handoverId=' + row.id + '">编辑</a>';
                            return s;
                        }
                    }
                ]
            ],
            queryParamsType: "undefined",
            queryParams: function queryParams(params) {
                return {
                    pageSize: params.pageSize,
                    pageNumber: params.pageNumber
                }
            }
        });
        $eventTable.bootstrapTable({
            pageSize: 10,
            striped: true,
            columns: [
                [{
                    title: '事件编号',
                    valign: 'middle',
                    field: 'id',
                    width: '10%',
                    align: 'center',
                    events: 'operateEvents',
                    formatter: function (value, row, index) {
                        var s = '<a class="J_menuItem"  title="事件' + row.id + ' 详情"' + 'href="/handover/eventDetails/' + row.id +'">' + row.id + '</a>';
                        return s;

                    }
                }, {
                    title: '交接单编号',
                    valign: 'middle',
                    align: 'center',
                    field: 'handoverId',
                    width: '10%',
                    formatter: function (value, row, index) {
                        var s = '<a class="showTitleTips J_menuItem"  title="交接单' + row.handoverId + ' 编辑" ' +
                                '  id="handover_' + row.handoverId + '"  ' +
                                'href="/handover/handoverEdit/' + row.handoverId + '?handoverId=' + row.handoverId + '">' + row.handoverId + '</a>';

                        return s;
                    }
                }, {
                    title: '工单编号',
                    valign: 'middle',
                    field: 'supportId',
                    width: '10%',
                    align: 'left',
                    events: 'operateEvents',
                    formatter: function (value, row, index) {
                        if (row.supportId) {
                            return '<a class="J_menuItem" title="工单'+ row.supportId +'详情"' +
                                    'href="/wo/supportrefer/' + row.supportId + '">' + row.supportId + '</a>';
                        } else {
                            return '无';
                        }
                    }
                }, {
                    title: '客户名称',
                    width: '14%',
                    field: 'CusName',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
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
                }, {
                    field: 'type',
                    width: '8%',
                    title: '<div id="all-type-list" class="select-wrap"><span class="current-title"><span class="current-select">事件类型</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list"><li class="select-list-item" value="">事件类型</li>@foreach($typeList as $type) <li class="select-list-item" value="{{$type->Code}}">{{$type->Means}}</li>@endforeach</ul></div>',
                    valign: 'middle',
                    align: 'left',
                    events: 'operateEvents'
                }, {
                    field: 'priority',
                    width: '8%',
                    title: '<div id="all-priority-list" class="select-wrap"><span class="current-title"><span class="current-select">优先级</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list"><li class="select-list-item" value="">优先级</li><li class="select-list-item" value="0">一般</li><li class="select-list-item" value="1">重要</li></ul></div>',
                    valign: 'middle',
                    align: 'left',
                    events: 'operateEvents'
                }, {
                    field: 'chargerId',
                    width: '6%',
                    title: '负责人',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        return row.chargerId;
                    }
                }, {
                    field: 'remindTs',
                    width: '10%',
                    title: '预约处理时间',
                    valign: 'middle',
                    align: 'left',
                }, {
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
                }, {
                    title: '操作',
                    field: 'operation',
                    valign: 'middle',
                    width: '14%',
                    align: 'center',
                    formatter: function (value, row, index) {
                        var deleted = '<a class="eventDelete" href="javascript:void(0)" title="删除">' +
                                '<i class="fa fa-trash-o"></i></a>&nbsp;&nbsp';
                        var edit = '<a class="eventEdit" href="javascript:void(0)" title="编辑">' +
                                '<i class="fa fa-edit"></i></a>&nbsp;&nbsp';
                        var transfer = '<a class="eventTransfer" href="javascript:void(0)" title="转移">' +
                                '<i class="fa fa-share"></i></a>&nbsp;&nbsp';
                        var complete = '<a class="eventComplete" href="javascript:void(0)" title="完成">' +
                                '<i class="fa fa-check-square-o"></i></a>';
                        var todo = deleted + edit + transfer + complete;
                        return todo;
                    },
                    events: 'operateEvents'
                }]
            ],
            queryParamsType: "undefined",
            queryParams: function queryParams(params) {
                return {
                    pageSize: params.pageSize,
                    pageNumber: params.pageNumber,
                    'priority': $('#priority').val(),
                    'eventStartTime': $("#eventStartTime").val(),
                    'eventEndTime': $("#eventEndTime").val(),
                    'searchInfo': $("#searchInfo").val(),
                    'supportId': $("#supportId").val(),
                    'cusId': $("#cusId").val(),
                    'type': $('#type').val(),
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
            },
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
                    area: ['800px', '570px'],
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
                    area: ['790px', '500px'],
                    content: ['/handover/eventTransfer/' + eventId, 'no'],
                    end: function () {
                        $('#eventTable').bootstrapTable('refresh');
                    }
                });
            },
            'click .eventComplete': function (e, value, row, index) {
                console.log(row)
                if(row.feedback) {
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
                else {
                   var confirmDex = layer.confirm('结果反馈为空，请填写！', {
                    }, function () {
                        layer.prompt({
                            title: '请填写事件'+row.id+'的结果反馈：',
                            formType: 2
                        }, function (text) {
                            $.ajax({
                                type: "POST",
                                data: {'feedback': text, 'eventId': row.id},
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                                },
                                url: "/handover/eventComplete/" + row.id,
                                success: function (data) {
                                    if (data.status == 'success') {
                                        layer.msg('该事件已完成！', {icon: 1});
                                        $('#eventTable').bootstrapTable('refresh');
                                    }
                                    else{
                                        layer.msg('操作失败！', {icon: 2});
                                        $('#eventTable').bootstrapTable('refresh');
                                    }
                                }
                            });
                        });
                    });
                }
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


    function doNewSearch(data, values) {
        if (data == "all-type-list") {
            $("#type").val(values);
        }
        if (data == "all-priority-list") {
            $("#priority").val(values);
        }
        $('#eventTable').bootstrapTable('refresh', {
            query: {
                'priority': $('#priority').val(),
                'eventStartTime': $("#eventStartTime").val(),
                'eventEndTime': $("#eventEndTime").val(),
                'searchInfo': $("#searchInfo").val(),
                'supportId': $("#supportId").val(),
                'cusId': $("#cusId").val(),
                'type': $('#type').val(),
                'pageNumber': 1
            }
        });
    }
    function doNewSearch2(data, values) {
        if (data == "allhandover-priority-list") {
            $("#handoverpriority").val(values);
        }
        if (data == "allhandover-type-list") {
            $("#remindType").val(values);
        }
        $('#handoverTable').bootstrapTable('refresh', {
            query: {
                'priority': $('#handoverpriority').val(),
                'type': $('#remindType').val(),
                'pageNumber': 1
            }
        });
    }
    initTable();
    $('#handoverSupport').click(function () {
        $('#tab-1').removeClass('hiddenTable');
        $('#span1').removeClass('hidden');
        $('#tab-2').addClass('hiddenTable');
        $('#span2').addClass('hidden');
    });
    $('#eventSupport').click(function () {
        $('#tab-2').removeClass('hiddenTable');
        $('#span2').removeClass('hidden');
        $('#tab-1').addClass('hiddenTable');
        $('#span1').addClass('hidden');
    });
    $(function () {
        pullDownChoice("all-type-list", function (param) {
            doNewSearch("all-type-list", param);
        });
        pullDownChoice("all-priority-list", function (param) {
            doNewSearch("all-priority-list", param);
        });
        pullDownChoice("allhandover-priority-list", function (param) {
            doNewSearch2("allhandover-priority-list", param);
        });
        pullDownChoice("allhandover-type-list", function (param) {
            doNewSearch2("allhandover-type-list", param);
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
</script>
</body>

</html>