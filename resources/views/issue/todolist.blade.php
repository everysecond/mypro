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
    <style>
        .hiddenTable {
            display: none;
        }

        tr {
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
            <div class="ibox">
                <div class="job-list-top">
                    <button class="submit-btn" id="subIssue"> + 提交问题</button>
                    <input type="hidden" value="" id="issuePriority">
                    <input type="hidden" value="" id="issueCategory">
                    <input type="hidden" value="" id="issueState">
                    <input type="hidden" value="" id="issueSource">
                </div>
            </div>


            <div style="margin-top:5px;">

                <div class="tab-content">
                    <ul class="nav nav-tabs" id="nav-tabs">

                        <li class="active" style="background-color: white">
                            <a aria-expanded="true" data-toggle="tab" href="#tab-1">待办问题</a></li>
                    </ul>
                    <div id="tab-1" class="tab-pane active">
                        <div class="full-height-scroll">
                            <div class="table-responsive" style="background-color: white">
                                <table id="issueTable" class="table-no-bordered table-fixpadding"
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
                                       data-url="/issue/todoListData"
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
<script type="text/javascript" src="/render/hplus/js/contabs.js"></script>
<script type="text/javascript" src="/js/common.js"></script>
<script type="text/javascript" src="/js/job_list.js"></script>
<script src="/js/plugins/bootstrap-table/bootstrap-table.js"></script>
<!-- put your locale files after bootstrap-table.js -->
<script src="/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>

<script>
    $(function () {
        $(".table-responsive").css({"overflow": "inherit"});
    });
    var url = "{{env('JOB_URL')}}";
    $("#subIssue").click(function () {
        layer.open({
            type: 2,
            title: '问题管理>问题申请单 （<span style="color:#ff253d">全部必填</span>）',
            area: ['840px', '580px'],
            content: '/issue/issueapply',
            maxmin: true,
            end: function () {
                $('#issueTable').bootstrapTable('refresh');
            }
        });
    });


    var $issueTable = $('#issueTable'),
            $remove = $('.remove'),
            selections = [];

    function initTable() {
        $issueTable.bootstrapTable({
            pageSize: 20,
            columns: [
                [
                    {
                        title: '问题单号',
                        valign: 'middle',
                        align: 'left',
                        field: 'issueNo',
                        width: '10%',
                        formatter: function (value, row, index) {
                            if (row.issueState == '驳回问题') {
                                var s = '<a class="showIssueTips J_menuItem" style="color:#19B492;display: inline-block; min-width: 130px"  menuName="' + row.Id + '"  id="issueNo_' + row.Id + '"    ' +
                                        'href="/issue/saveToapply/' + row.Id + '">' + row.issueNo + '</a>';
                            }
                            else {
                                var s = '<a class="showIssueTips J_menuItem" style="color:#19B492;display: inline-block; min-width: 130px" menuName="' + row.Id + '"  id="issueNo_' + row.Id + '"    ' +
                                        'href="/issue/details/' + row.Id + '">' + row.issueNo + '</a>';
                            }
                            return s;
                        }
                    },
                    {
                        title: '问题标题',
                        valign: 'middle',
                        field: 'issueTitle',
                        width: '12%',
                        align: 'left',
                        formatter: function (value, row, index) {
                            if (row.issueState == '驳回问题') {
                                var s = '<a class="showIssueTips J_menuItem" style="color:#19B492;"  menuName="' + row.Id + '"  id="title_' + row.Id + '"    ' +
                                        'href="/issue/saveToapply/' + row.Id + '">' + substringText(row.issueTitle) + '</a>';
                            }
                            else {
                                var s = '<a class="showIssueTips J_menuItem" style="color:#19B492;" menuName="' + row.Id + '"  id="title_' + row.Id + '"    ' +
                                        'href="/issue/details/' + row.Id + '">' + substringText(row.issueTitle) + '</a>';
                            }
                            return s;
                        },
                        events: 'operateEvents'
                    },
                    {
                        field: 'issuePriority',
                        width: '6%',
                        title: '<div id="todo-priority-list" class="select-wrap"><span class="current-title"><span class="current-select">优先级</span><i class="select-icon"></i></span>' +
                        '<ul class="select-list"><li class="select-list-item" value="">优先级</li>@foreach($priorityList as $pri) <li class="select-list-item" value="{{$pri->Code}}">{{$pri->Means}}</li>@endforeach</ul></div>',
                        valign: 'middle',
                        align: 'left',
                        events: 'operateEvents'
//
                    },
                    {
                        field: 'issueState',
                        width: '4%',
                        title: '<div id="todo-state-list" class="select-wrap"><span class="current-title"><span class="current-select">问题状态</span><i class="select-icon"></i></span>' +
                        '<ul class="select-list"><li class="select-list-item" value="">问题状态</li>@foreach($stateList as $state) <li class="select-list-item" value="{{$state->Code}}">{{$state->Means}}</li>@endforeach</ul></div>',
                        valign: 'middle',
                        align: 'left',
                        events: 'operateEvents',
                        formatter: function (value, row, index) {
                            return row.issueState + "中";
                        }
                    },
                    {
                        field: 'issueSource',
                        width: '10%',
                        title: '<div id="todo-source-list" class="select-wrap"><span class="current-title"><span class="current-select">问题来源</span><i class="select-icon"></i></span>' +
                        '<ul class="select-list"><li class="select-list-item" value="">问题来源</li>@foreach($sourceList as $state) <li class="select-list-item" value="{{$state->Code}}">{{$state->Means}}</li>@endforeach</ul></div>',
                        valign: 'middle',
                        align: 'left',

                    },
                    {
                        field: 'issueCategory',
                        width: '10%',
                        title: '<div id="todo-type-list" class="select-wrap"><span class="current-title"><span class="current-select">问题分类</span><i class="select-icon"></i></span>' +
                        '<ul class="select-list"><li class="select-list-item" value="">全部类型</li>@foreach($categoryList as $status)<li class="select-list-item" value="{{$status->Code}}">{{$status->Means}}</li>@endforeach</ul></div>',
                        valign: 'middle',
                        align: 'left',
                        formatter: function (value, row, index) {
                            var titleStyle = "";
                            if (row.issueCategory == "紧急") {
                                titleStyle = "font-style:italic;font-weight:bold";
                                var s = '<img src="/img/dot.png" width="6" /><span style="' + titleStyle + '"  menuName="' + row.Id + '"  id="title_' + row.Id + '"  ' +
                                        'href="/issue/details/' + row.Id + '">' + '&nbsp;&nbsp;' + row.issueCategory + '</span>';
                            }
                            else {
                                var s = '<span style="' + titleStyle + '"  menuName="' + row.Id + '"  id="title_' + row.Id + '"  ' +
                                        'href="/issue/details/' + row.Id + '">' + row.issueCategory + '</span>';
                            }
                            return s;
                        },
                        events: 'operateEvents'
                    },
                    {
                        field: 'issueChargeUserId',//'issueCheckUserId',
                        width: '10%',
                        title: '问题分析专家<br/>问题经理',
                        valign: 'middle',
                        align: 'left',
                        formatter: function (value, row, index) {
                            var issueChargeUserId = row.issueChargeUserId != 0 ? row.issueChargeUserId : "未指定";
                            var s = issueChargeUserId + '<b style="color:blue">' + '<br/>' + row.issueCheckUserId + '</b>';
                            return s;
                        }
                    },
                    {
                        field: 'issueSubmitUserId',//'ChargeUserId',
                        width: '12%',
                        title: '申请人<br/>申请时间',
                        valign: 'middle',
                        align: 'left',
                        formatter: function (value, row, index) {
                            var issueSubmitUserId = row.issueSubmitUserId != 0 ? row.issueSubmitUserId : '无';
                            var s = issueSubmitUserId + '<br/>' + row.issueSubmitTs;
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
                    'issueCategory': $('#issueCategory').val(),
                    'issuePriority': $('#issuePriority').val(),
                    'issueState': $('#issueState').val(),
                    'issueSource': $('#issueSource').val(),
                }
            }
        });

        var custips;
        window.operateEvents = {
            'mouseover .showIssueTips': function (e, value, row, index) {
                custips = layer.tips('<div style="word-wrap: break-word;color: #ffffff">问题单号：' + row.issueNo + '<br/>' +
                        '问题来源：<div class="supportBody" style="color: #ffffff">' + row.issueSource + '</div>' +
                        '问题详细描述：<div class="supportBody" style="color: #ffffff">' + row.issueDescribe + '</div>' +
                        '最后一次操作人：' + (row.upUserId ? row.upUserId : '无') + '</div>'
                        , '#title_' + row.Id, {time: 0, tips: [2, '#999999'], maxWidth: 400});
                $('.supportBody img').each(function () {
                    var src = $(this).attr("src");
                    if (src.substr(0, 7).toLowerCase() != "http://") {
                        $(this).attr("src", url + src);
                    }
                });
            },
            'mouseout .showIssueTips': function (e, value, row, index) {
                layer.close(custips);
            }
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
        if (data == "todo-priority-list") {
            $("#issuePriority").val(values);
        }
        if (data == "todo-state-list") {
            $("#issueState").val(values);
        }
        if (data == "todo-source-list") {
            $("#issueSource").val(values);
        }
        if (data == "todo-type-list") {
            $("#issueCategory").val(values);
        }

        $('#issueTable').bootstrapTable('refresh', {
            query: {
                'issueCategory': $('#issueCategory').val(),
                'issuePriority': $('#issuePriority').val(),
                'issueState': $('#issueState').val(),
                'issueSource': $('#issueSource').val(),
                'pageNumber': 1
            }
        });
    }


    initTable();
    $(function () {
        pullDownChoice("todo-priority-list", function (param) {
            doNewSearch("todo-priority-list", param);
        });
        pullDownChoice("todo-state-list", function (param) {
            doNewSearch("todo-state-list", param);
        });
        pullDownChoice("todo-type-list", function (param) {
            doNewSearch("todo-type-list", param);
        });
        pullDownChoice("todo-source-list", function (param) {
            doNewSearch("todo-source-list", param);
        });
        showToolTip($("._handle_tip"));
        showTableToolTip("._jobState");
    })
</script>
</body>

</html>