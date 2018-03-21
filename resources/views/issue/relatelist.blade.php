<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="_token" content="{{ csrf_token() }}"/>
    <title>安畅网络 问题系统——相关问题列表</title>

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
        #changeTable {
            padding: 0px;
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
<div class=" wrapper-content" style="background-color:whitesmoke">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="job-list-top">
                    <form class="form-inline">
                        问题申请时间:
                        <input type="text" class="form-control" style="width: 12%" placeholder="" id="issueStartTime">
                        至
                        <input type="text" class="form-control" style="width: 12%" placeholder="" id="issueEndTime">

                        &nbsp;&nbsp;
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <div class="input-group" style="min-width:250px;">
                            <input type="text" class="form-control" name="searchInfo"
                                   id="searchInfo" value=""
                                   placeholder="问题编号/标题搜索">
                            <span class="input-group-btn">
                            <a class="btn btn-info" style="background-color:#19b492" id="searchAll"
                               onclick="doNewSearch(this,'')">
                                <span class="glyphicon glyphicon-search">搜索</span>
                            </a>
                        </span>
                            <input type="hidden" value="" id="issueCategory">
                            <input type="hidden" value="" id="issuePriority">
                            <input type="hidden" value="" id="issueState">
                            <input type="hidden" value="" id="issueSource">
                        </div>
                    </form>
                </div>
            </div>
            <div style="margin-top:5px;">
                <div class="tab-content">
                    <div id="tab-1" class="tab-pane active">
                        <div class="full-height-scroll">
                            <div class="table-responsive" style="background-color: white">
                                <table id="issueTable" class="table-no-bordered table-fixpadding"
                                       style="text-align: center;color:#6b7d86" bgcolor="#FFFFFF"
                                       cellpadding="0px"
                                       cellspacing="0px" width="100%"
                                       data-pagination="true"
                                       data-show-export="true"
                                       data-page-size="10"
                                       data-id-field="Id"
                                       data-page-list="[10, 25, 50, 100, ALL]"
                                       data-show-footer="false"
                                       data-side-pagination="server"
                                       data-url="/issue/getMyList"
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
</div>

<!-- 全局js -->
<script src="/js/jquery.min.js?v=2.1.4"></script>
<script src="/js/jquery.slimscroll.min.js"></script>
<script src="/js/plugins/layer/layer.min.js"></script>
<script src="/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/job_list.js"></script>
<script type="text/javascript" src="/render/hplus/js/contabs.js"></script>
<script type="text/javascript" src="/js/common.js"></script>
<script type="text/javascript" src="/js/job_list.js"></script>
<script src="/js/plugins/bootstrap-table/bootstrap-table.js"></script>
<!-- put your locale files after bootstrap-table.js -->
<script src="/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>
<script src="/js/plugins/layer/laydate/laydate.js"></script>

<script>

    var url = "{{env('JOB_URL')}}";
    var $issueTable = $('#issueTable'),
            $remove = $('.remove'),
            selections = [];

    function initTable() {
        $issueTable.bootstrapTable({
            pageSize: 20,
            striped: true,
            columns: [
                [{
                    title: '问题单号',
                    valign: 'middle',
                    align: 'left',
                    field: 'issueNo',
                    width: '6%',
                    formatter: function (value, row, index) {
                        if (row.issueState == '驳回问题') {
                            var s = '<a class="showIssueTips J_menuItem" style="display: inline-block; min-width: 130px"  menuName="' + row.Id + '"  id="issueNo_' + row.Id + '"    ' +
                                    'href="/issue/saveToapply/' + row.Id + '">' + row.issueNo + '</a>';
                        }
                        else {
                            var s = '<a class="showIssueTips J_menuItem" style="display: inline-block; min-width: 130px" menuName="' + row.Id + '"  id="issueNo_' + row.Id + '"    ' +
                                    'href="/issue/details/' + row.Id + '">' + row.issueNo + '</a>';
                        }
                        return s;
                    }

                }, {
                    title: '问题标题',
                    valign: 'middle',
                    field: 'issueTitle',
                    width: '8%',
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
                }, {
                    field: 'issueSource',
                    width: '10%',
                    title: '<div id="todo-source-list" class="select-wrap"><span class="current-title"><span class="current-select">问题来源</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list"><li class="select-list-item" value="">问题来源</li>@foreach($sourceList as $state) <li class="select-list-item" value="{{$state->Code}}">{{$state->Means}}</li>@endforeach</ul></div>',
                    valign: 'middle',
                    align: 'left',
                }, {
                    field: 'issuePriority',
                    width: '6%',
                    title: '<div id="all-Priority-list" class="select-wrap"><span class="current-title"><span class="current-select">优先级</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list"><li class="select-list-item" value="">优先级</li>@foreach($priorityList as $pri) <li class="select-list-item" value="{{$pri->Code}}">{{$pri->Means}}</li>@endforeach</ul></div>',
                    valign: 'middle',
                    align: 'left',
                    events: 'operateEvents'
                }, {
                    field: 'issueState',
                    width: '4%',
                    title: '<div id="all-State-list" class="select-wrap"><span class="current-title"><span class="current-select">问题状态</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list"><li class="select-list-item" value="">问题状态</li>@foreach($stateList as $state) <li class="select-list-item" value="{{$state->Code}}">{{$state->Means}}</li>@endforeach</ul></div>',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        if (row.issueState == '完成' || row.issueState == '驳回问题') {
                        return row.issueState;}
                        else return row.issueState + "中";
                    },
                    events: 'operateEvents'
                }, {
                    field: 'issueCategory',
                    width: '4%',
                    title: '<div id="all-Type-list" class="select-wrap"><span class="current-title"><span class="current-select">问题分类</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list"><li class="select-list-item" value="">问题分类</li>@foreach($categoryList as $type) <li class="select-list-item" value="{{$type->Code}}">{{$type->Means}}</li>@endforeach</ul></div>',
                    valign: 'middle',
                    align: 'left',

                }, {
                    field: 'issueChargeUserId',
                    width: '8%',
                    title: '问题分析专家<br/>问题经理',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        issueChargeUserId = row.issueChargeUserId != '0' ? row.issueChargeUserId : "未指定";
                        var s = issueChargeUserId + '<b style="color:blue">' + '<br/>' + row.issueCheckUserId + '</b>';
                        return s;
                    }
                }, {
                    field: 'issueSubmitUserId',//'ChargeUserId',
                    width: '10%',
                    title: '申请人<br/>申请时间',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var designer = row.issueSubmitUserId != 0 ? row.issueSubmitUserId : '无';
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
                    'cusType': $('#cusType').val()
                }
            }
        });
        var custips;
        window.operateEvents = {
            'mouseover .showIssueTips': function (e, value, row, index) {
                custips = layer.tips('<div style="word-wrap: break-word;color: #ffffff">问题单号：' + row.issueTitle + '<br/>' +
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
    function doNewSearch(data, values) {//检索筛选
        if (data == "all-Type-list") {
            $("#issueCategory").val(values);
        }
        if (data == "all-Priority-list") {
            $("#issuePriority").val(values);
        }
        if (data == "todo-source-list") {
            $("#issueSource").val(values);
        }
        if (data == "all-State-list") {
            $("#issueState").val(values);
        }
        $('#issueTable').bootstrapTable('refresh', {
            query: {
                'issueStartTime': $("#issueStartTime").val(),
                'issueEndTime': $("#issueEndTime").val(),
                'actualStartTime': $("#actualStartTime").val(),
                'actualEndTime': $("#actualEndTime").val(),
                'searchInfo': $("#searchInfo").val(),
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
        //issue list
        pullDownChoice("all-Type-list", function (param) {
            doNewSearch("all-Type-list", param);
        });
        pullDownChoice("all-Priority-list", function (param) {
            doNewSearch("all-Priority-list", param);
        });
        pullDownChoice("all-State-list", function (param) {
            doNewSearch("all-State-list", param);
        });
        pullDownChoice("todo-source-list", function (param) {
            doNewSearch("todo-source-list", param);
        });
        showToolTip($("._handle_tip"));
        showTableToolTip("._jobState");

        var issueStartTime = {
            elem: '#issueStartTime',
            format: 'YYYY-MM-DD hh:mm',
            istime: true,
            choose: function (datas) {
                issueEndTime.min = datas;
                issueEndTime.start = datas
            }
        }
        var issueEndTime = {
            elem: '#issueEndTime',
            format: 'YYYY-MM-DD hh:mm',
            istime: true,
            choose: function (datas) {
                issueStartTime.max = datas;
            }
        }
        laydate(issueStartTime);
        laydate(issueEndTime);
        laydate(issueStartTime);
        laydate(issueEndTime);
    })


</script>
</body>

</html>