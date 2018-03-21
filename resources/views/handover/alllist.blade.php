<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="_token" content="{{ csrf_token() }}"/>
    <title>安畅网络 交接单系统——全部列表</title>

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
                        <span href="#tab-1" id="handoverSupport" class="title_active">全部交接单<span
                                    class="label_line"></span></span>
                        <span class="J_menuItem" menuName="全部事件" href="/handover/eventAllList"
                              id="eventSupport">全部事件</span>
                    </div>
                    <div id="tab-1" class="">
                        <div class="full-height-scroll">
                            <form class="form-inline" style="padding: 14px">
                                申请时间:
                                <input type="text" class="form-control" style="width: 12%" placeholder=""
                                       id="handStartTime">
                                至
                                <input type="text" class="form-control" style="width: 12%" placeholder=""
                                       id="handEndTime">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <div class="input-group" style="min-width:450px;">
                                    <input type="text" class="form-control" name="searchHand"
                                           id="searchHand" value=""
                                           placeholder="请输入交接单编号/负责人/注意事项 搜索">
                                    <span class="input-group-btn">
                            <a class="btn btn-info" style="background-color:#19b492" id="searchHandover"
                               onclick="doNewSearch(this,'')">
                                <span class="glyphicon glyphicon-search">搜索</span>
                            </a>
                        </span>

                                    <input type="hidden" value="" id="supportId">
                                    <input type="hidden" value="" id="cusId">
                                </div>
                            </form>
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
                                       data-page-list="[10, 25, 50, 100, ALL]"
                                       data-show-footer="false"
                                       data-side-pagination="server"
                                       data-url="/handover/allListData"
                                       data-response-handler="responseHandler">
                                </table>
                            </div>
                            <input type="hidden" value="" id="remind">
                            <input type="hidden" value="" id="priority">
                            <input type="hidden" value="" id="priOri">
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
    $(function () {
        $(".table-responsive").css({"overflow": "inherit"});
    });
    var url = "{{env('JOB_URL')}}";

    var $handoverTable = $('#handoverTable'),
            $remove = $('.remove'),
            selections = [];

    function initTable() {
        $handoverTable.bootstrapTable({
            pageSize: 10,
            striped: true,
            rowStyle: function (row, index) {
                return checkHandoverIsDoneStyle(row);
            },
            columns: [
                [
                    {
                        title: '交接单编号',
                        valign: 'middle',
                        align: 'center',
                        field: 'id',
                        width: '10%',
                        formatter: function (value, row, index) {
                            if (row.status == 0) {
                                var s = '<a class="showTitleTips J_menuItem"  title="交接单' + row.id + ' 编辑" ' +
                                        '  id="handover_' + row.id + '"  ' +
                                        'href="/handover/handoverEdit/' + row.id + '?handoverId=' + row.id + '">' + row.id + '</a>';
                            } else {
                                var s = '<a class="showTitleTips J_menuItem"  title="交接单' + row.id + ' 详情" ' +
                                        '  id="handover_' + row.id + '"  ' +
                                        'href="/handover/handoverDetails/' + row.id + '?handoverId=' + row.id + '">' + row.id + '</a>';
                            }
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
                            var s = '<span class="showTitleTips" id="title_' + row.id + '">' + stringText(row.notes) + '</span>';
                            return s;
                        },
                        events: 'operateEvents'
                    },
                    {
                        field: 'priority',
                        width: '10%',
                        title: '<div id="all-priority-list" class="select-wrap"><span class="current-title"><span class="current-select">优先级</span><i class="select-icon"></i></span>' +
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
                        title: '<div id="all-remind-list" class="select-wrap"><span class="current-title"><span class="current-select">提醒方式</span><i class="select-icon"></i></span>' +
                        '<ul class="select-list"><li class="select-list-item" value="">提醒方式</li><li class="select-list-item" value="sms">短信</li><li class="select-list-item" value="wechat">微信</li><li class="select-list-item" value="email">邮件</li></ul></div>',
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
                    },
                    {
                        field: 'ts',
                        width: '12%',
                        title: '提交时间',
                        valign: 'middle',
                        align: 'left',
                        formatter: function (value, row, index) {
                            var s = row.ts;
                            return s;
                        }
                    }, {
                    field: 'upTs',
                    width: '12%',
                    title: '最后更新时间',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var s = row.upTs ?row.upTs:"";
                        return s;
                    }
                }]
            ],
            queryParamsType: "undefined",
            queryParams: function queryParams(params) {
                return {
                    pageSize: params.pageSize,
                    pageNumber: params.pageNumber,
                    'priority': $('#priority').val(),
                    'remind': $('#remind').val(),
                    'handStartTime': $("#handStartTime").val(),
                    'handEndTime': $("#handEndTime").val(),
                    'searchHand': $("#searchHand").val(),
                }
            }
        });
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
        }
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
        if (data == "all-priority-list") {
            $("#priority").val(values);
        }
        if (data == "all-remind-list") {
            $("#remind").val(values);
        }
        $('#handoverTable').bootstrapTable('refresh', {
            query: {
                'priority': $('#priority').val(),
                'remind': $('#remind').val(),
                'handStartTime': $("#handStartTime").val(),
                'handEndTime': $("#handEndTime").val(),
                'searchHand': $("#searchHand").val(),
                'pageNumber': 1
            }
        });
    }

    initTable();
    $(function () {
        pullDownChoice("all-priority-list", function (param) {
            doNewSearch("all-priority-list", param);
        });
        pullDownChoice("all-remind-list", function (param) {
            doNewSearch("all-remind-list", param);
        });
        showToolTip($("._handle_tip"));
        showTableToolTip("._jobState");
        var handStartTime = {
            elem: '#handStartTime',
            format: 'YYYY-MM-DD hh:mm',
            istime: true,
            choose: function (datas) {
                handEndTime.min = datas;
                handEndTime.start = datas
            }
        }
        var handEndTime = {
            elem: '#handEndTime',
            format: 'YYYY-MM-DD hh:mm',
            istime: true,
            choose: function (datas) {
                handStartTime.max = datas;
            }
        }
        laydate(handStartTime);
        laydate(handEndTime);
    })
    function checkHandoverIsDoneStyle(row, index) {
        if (row.notDone == 0) {
            return {
                css: {"color": "#CCCCCF"}
            }
        }
        return {css: {"": ""}};
    }

    $('#searchHand').bind('keypress',function(event){
        var searchAll = document.getElementById("searchHandover");
        if(event.keyCode == "13")
        {
            searchAll.click();
        }
    });
</script>
</body>

</html>