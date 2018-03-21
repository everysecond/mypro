<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="_token" content="{{ csrf_token() }}"/>
    <title>安畅网络 变更系统——相关变更列表</title>

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
                        变更窗口时间:
                        <input type="text" class="form-control" style="width: 12%" placeholder="" id="changeStartTime">
                        至
                        <input type="text" class="form-control" style="width: 12%" placeholder="" id="changeEndTime">

                        &nbsp;&nbsp;实际完成时间:
                        <input type="text" class="form-control" style="width: 12%" placeholder="" id="actualStartTime">
                        至
                        <input type="text" class="form-control" style="width: 12%" placeholder="" id="actualEndTime">

                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <div class="input-group" style="min-width:250px">
                            <input type="text" class="form-control" name="searchInfo"
                                   id="searchInfo" value=""
                                   placeholder="RFC编号/标题搜索">
                                                    <span class="input-group-btn">
                            <a class="btn btn-info" style="background-color:#19b492" id="searchAll"
                               onclick="doNewSearch(this,'')">
                                <span class="glyphicon glyphicon-search">搜索</span>
                            </a>
                        </span>
                            <input type="hidden" value="" id="cusType">
                            <input type="hidden" value="" id="Status">
                            <input type="hidden" value="" id="changeCondition">
                        </div>
                    </form>
                </div>
            </div>
            <div style="margin-top:5px;">
                <div class="tab-content">
                    <div id="tab-1" class="tab-pane active">
                        <div class="full-height-scroll">
                            <div class="table-responsive" style="background-color: white">
                                <table id="changeTable" class="table-no-bordered table-fixpadding"
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
                                       data-url="/change/getMyList"
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
    $(function () {
        $(".table-responsive").css({"overflow": "inherit"});
        $("#changeTable").on("click", ".test", function (value, row, index) {
            layer.open({
                type: 2,
                title: '变更管理>变更申请单 （<span style="color:#ff253d">*表示必填项</span>）',
                area: ['840px', '750px'],
                content: '/change/saveToapply/' + row.Id,
                maxmin: true,
                end: function () {
                    $('#changeTable').bootstrapTable('refresh');
                }
            });
        });
    });

    var url = "{{env('JOB_URL')}}";
    $("#subchange").click(function (value, row, index) {
        layer.open({
            type: 2,
            title: '变更管理>变更申请单 （<span style="color:#ff253d">*表示必填项</span>）',
            area: ['840px', '750px'],
            content: ['/change/changerefer', 'no'],
            maxmin: true,
            end: function () {
                $('#changeTable').bootstrapTable('refresh');
            }
        });
    });

    var $changeTable = $('#changeTable'),
            $remove = $('.remove'),
            selections = [];

    function initTable() {
        $changeTable.bootstrapTable({
            pageSize: 20,
            striped: true,
            columns: [
                [{
                    title: 'RFC编号',
                    valign: 'middle',
                    align: 'left',
                    field: 'RFCNO',
                    width:'10%',
                    formatter: function (value, row, index) {
                        var s = '<a class="showTitleTips J_menuItem" style="display: inline-block; min-width: 130px"  id="RFCNO_' + row.Id + '"    ' +
                                'href="/change/details/' + row.Id + '">' + row.RFCNO + '</a>';

                        return s;
                    }
                }, {
                    title: '变更标题',
                    valign: 'middle',
                    field: 'changeTitle',
                    width: '12%',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var titleStyle = "color:#19B492;";
                        if (row.changeType == '紧急变更') {
                        } else {
                            titleStyle = "color:#19B492;";
                        }

                        if (row.changeState == '待申请') {
                            var s = '<a  class="test">' + substringText(row.changeTitle) + '</a>'
                        } else {
                            var s = '<a class="showTitleTips J_menuItem" style="' + titleStyle + '"  menuName="' + row.Id + '"  id="title_' + row.Id + '"    ' +
                                    'href="/change/details/' + row.Id + '">' + substringText(row.changeTitle) + '</a>';
                        }


                        return s;
                    },
                    events: 'operateEvents'
                }, {
                    field: 'changeCondition',
                    width: '5%',
                    title: '<div id="all-condition-list" class="select-wrap"><span class="current-title"><span class="current-select">触发条件</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list"><li class="select-list-item" value="">触发条件</li>@foreach($conditionList as $type) <li class="select-list-item" value="{{$type->Code}}">{{$type->Means}}</li>@endforeach</ul></div>',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var s = '<span  style="color:#19B492;">' + row.changeCondition + '</span>';

                        return s;
                    }
                }, {
                    field: 'changeState',
                    width: '6%',
                    title: '<div id="operate-status-list" class="select-wrap"><span class="current-title"><span class="current-select">变更状态</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list"><li class="select-list-item" value="">变更状态</li>@foreach($statusList as $status) <li class="select-list-item" value="{{$status->Code}}">{{$status->Means}}</li>@endforeach</ul></div>',
                    valign: 'middle',
                    align: 'left',
                    events: 'operateEvents'
                }, {
                    field: 'changeType',
                    width: '4%',
                    title: '<div id="operate-type-list" class="select-wrap"><span class="current-title"><span class="current-select">变更类型</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list"><li class="select-list-item" value="">变更类型</li>@foreach($typeList as $type) <li class="select-list-item" value="{{$type->Code}}">{{$type->Means}}</li>@endforeach</ul></div>',
                    valign: 'middle',
                    align: 'left',
                    events: 'operateEvents'
                }, {
                    field: 'changeCategory',
                    width: '4%',
                    title: '变更类别<br/>变更子类',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var changeCategory = row.changeCategory != null ? row.changeCategory : '';
                        var changeSub = row.changeSubCategory != null ? row.changeSubCategory : '';
                        var s = changeCategory + '<br/>' + changeSub;
                        return s;
                    }
                }, {
                    field: 'Ts',
                    width: '12%',
                    title: '期望完成时间<br/>最后更新时间',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var upTs = row.UpTs != null ? substrTime(row.UpTs) : '无';
                        var expectTs = row.expectTs != null ? substrTime(row.expectTs) : '无';
                        return upTs + '<br/>' + expectTs;
                    }
                }, {
                    field: 'feasibilityUserId',
                    width: '4%',
                    title: '负责人<br/>审核人/部门',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var designer = row.proDesigerId != 0 ? row.proDesigerId : '无';
                        var s = designer + '<br><label style="">' + row.approver + '</label>';
                        return s;
                    }
                }, {
                    field: 'Evaluation',
                    width: '12%',
                    title: '变更时间窗口',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var s = row.changeTimeStart != null ? substrTime(row.changeTimeStart) : "无";
                        var e = row.changeTimeEnd != null ? substrTime(row.changeTimeEnd) : "无";
                        return s + '<br/>' + e;
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
            'mouseover .showTitleTips': function (e, value, row, index) {
                custips = layer.tips('<div style="word-wrap: break-word;color: #ffffff">变更标题：' + row.changeTitle + '<br/>' +
                        '变更原因：<div class="supportBody" style="color: #ffffff">' + row.changeReason + '</div>' +
                        '变更内容：<div class="supportBody" style="color: #ffffff">' + row.changeContext + '</div>' +
                        '变更风险及影响分析：<div class="supportBody" style="color: #ffffff">' + row.changeRisk + '</div>' +
                        '最后一次操作人：' + (row.UpUserId ? row.UpUserId :'无') + '</div>'
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
        if (data == "operate-type-list") {
            $("#cusType").val(values);
        }
        if (data == "operate-status-list") {
            $("#Status").val(values);
        }
        if (data == "all-condition-list") {
            $("#changeCondition").val(values);
        }
        $('#changeTable').bootstrapTable('refresh', {
            query: {
                'changeStartTime': $("#changeStartTime").val(),
                'changeEndTime': $("#changeEndTime").val(),
                'actualStartTime': $("#actualStartTime").val(),
                'actualEndTime': $("#actualEndTime").val(),
                'searchInfo': $("#searchInfo").val(),
                'changeType': $('#cusType').val(),
                'changeState': $('#Status').val(),
                'changeCondition': $('#changeCondition').val(),
                'pageNumber': 1
            }
        });
    }
    initTable();
    $(function () {
        //operate list
        pullDownChoice("operate-type-list", function (param) {
            doNewSearch("operate-type-list", param);
        });
        pullDownChoice("operate-status-list", function (param) {
            doNewSearch("operate-status-list", param);
        });
        pullDownChoice("all-condition-list", function (param) {
            doNewSearch("all-condition-list", param);
        });
        showToolTip($("._handle_tip"));
        showTableToolTip("._jobState");

        var changeStartTime = {
            elem: '#changeStartTime',
            format: 'YYYY-MM-DD hh:mm',
            istime: true,
            choose: function (datas) {
                changeEndTime.min = datas
            }
        }
        var changeEndTime = {
            elem: '#changeEndTime',
            format: 'YYYY-MM-DD hh:mm',
            istime: true,
            choose: function (datas) {
                changeStartTime.max = datas;
            }
        }
        var actualStartTime = {
            elem: '#actualStartTime',
            format: 'YYYY/MM/DD hh:mm',
            istime: true,
            choose: function (datas) {
                actualEndTime.min = datas
            }
        }
        var actualEndTime = {
            elem: '#actualEndTime',
            format: 'YYYY/MM/DD hh:mm',
            istime: true,
            choose: function (datas) {
                actualStartTime.max = datas
            }
        }
        laydate(changeStartTime);
        laydate(changeEndTime);
        laydate(actualStartTime);
        laydate(actualEndTime);
    })


</script>
</body>

</html>