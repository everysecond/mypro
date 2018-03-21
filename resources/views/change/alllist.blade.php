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
        .wd12 {
            width: 12%;
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
                <div class="job-list-top" style="height: 95px;">
                    <form class="form-inline">
                        <div style="height: 40px;">
                            <div class="job-handle-wrap">
                                <div class="job-handle">
                                    <div class="_handle_tip handle-tips">
                                        <a href="javascript:void(0);" name="approval"
                                           onclick="doNewSearch(this,'approval')"><i class="icon-time"></i></a><span
                                                id="approvalHtml" class="tips-huang"></span>
                                        <div class="tool-tips" style="display: none;"><p class="tool-title">可行性审批超时</p>
                                            <i
                                                    class="tool-arrow"></i></div>
                                    </div>
                                    <div class="_handle_tip handle-tips">
                                        <a href="javascript:void(0);" name="testApproval"
                                           onclick="doNewSearch(this,'testApproval')"><i class="icon-time"></i></a><span
                                                id="testApprovalHtml" class="tips-yellow"></span>
                                        <div class="tool-tips" style="display: none;"><p class="tool-title">测试结果审批超时</p>
                                            <i
                                                    class="tool-arrow"></i></div>
                                    </div>
                                    <div class="_handle_tip handle-tips">
                                        <a href="javascript:void(0);" name="approved"
                                           onclick="doNewSearch(this,'approved')"><i class="icon-time"></i></a><span
                                                id="approvedHtml" class="tips-red"></span>
                                        <div class="tool-tips" style="display: none;"><p class="tool-title"
                                                                                         style="right: 15%;">
                                                结果验证超时</p><i class="tool-arrow"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top: 10px">
                        变更窗口时间:
                        <input type="text" class="form-control" style="width: 12%" placeholder="" id="changeStartTime">
                        至
                        <input type="text" class="form-control" style="width: 12%" placeholder="" id="changeEndTime">

                        &nbsp;&nbsp;实际完成时间:
                        <input type="text" class="form-control" style="width: 12%" placeholder="" id="actualStartTime">
                        至
                        <input type="text" class="form-control" style="width: 12%" placeholder="" id="actualEndTime">

                            &nbsp;&nbsp;
                        <div class="input-group" style="min-width:100px">
                            <input type="text" class="form-control" name="searchInfo"
                                   id="searchInfo" value=""
                                   placeholder="请输入RFC编号/变更标题关键字搜索">
                                                    <span class="input-group-btn">
                            <a class="btn btn-info" style="background-color:#19b492" id="searchAll"
                               onclick="doNewSearch(this,'')">
                                <span class="glyphicon glyphicon-search">搜索</span>
                            </a>
                        </span>
                        </div>
                        {{--条件存储--}}
                        <input type="hidden" value="" id="changeType">
                        <input type="hidden" value="" id="changeState">
                        <input type="hidden" value="" id="changeCondition">

                        <input type="hidden" value="" id="approval">
                        <input type="hidden" value="" id="testApproval">
                        <input type="hidden" value="" id="approved">
                        <input type="hidden" value="" id="allTimeOutIds">
                        <input type="hidden" value="" id="timeOutIds">
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
                                       cellpadding="0"
                                       cellspacing="0" width="100%"
                                       data-pagination="true"
                                       data-show-export="true"
                                       data-page-size="10"
                                       data-id-field="Id"
                                       data-page-list="[10, 25, 50, 100, ALL]"
                                       data-show-footer="false"
                                       data-side-pagination="server"
                                       data-url="/change/getAllList"
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
<script src="/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>
<!-- put your locale files after bootstrap-table.js -->
<script src="/js/plugins/layer/laydate/laydate.js"></script>


<script>
    var $changeTable = $('#changeTable'),
            $remove = $('.remove'),
            selections = [];

    function initTable() {
        $changeTable.bootstrapTable({
            pageSize: 20,
            striped: true,
            sortable: true,
            columns: [
                [{
                    title: 'RFC编号',
                    valign: 'middle',
                    align: 'left',
                    field: 'RFCNO',
                    formatter: function (value, row, index) {

                        var s = '<a class="showTitleTips J_menuItem" style="display: inline-block; min-width: 130px"  id="RFCNO_' + row.Id + '"    ' +
                                'href="/change/details/' + row.Id + '">' + row.RFCNO + '</a>';

                        return s;
                    },
                    sortName: 'RFCNO',
                    sortable: true
                }, {
                    title: '变更标题',
                    valign: 'middle',
                    field: 'changeTitle',
                    width: '15%',
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
                    width: '10%',
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
                    width: '10%',
                    title: '<div id="all-status-list" class="select-wrap"><span class="current-title"><span class="current-select">变更状态</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list"><li class="select-list-item" value="">变更状态</li>@foreach($statusList as $status) <li class="select-list-item" value="{{$status->Code}}">{{$status->Means}}</li>@endforeach</ul></div>',
                    valign: 'middle',
                    align: 'left',
                    events: 'operateEvents'
                }, {
                    field: 'changeType',
                    width: '10%',
                    title: '<div id="all-type-list" class="select-wrap"><span class="current-title"><span class="current-select">变更类型</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list"><li class="select-list-item" value="">变更类型</li>@foreach($typeList as $type) <li class="select-list-item" value="{{$type->Code}}">{{$type->Means}}</li>@endforeach</ul></div>',
                    valign: 'middle',
                    align: 'left',
                    events: 'operateEvents'
                }, {
                    field: 'changeCategory',
                    width: '10%',
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
                    width: '15%',
                    title: '预计完成时间<br/>实际完成时间',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var upTs = row.actualTs != null ? substrTime(row.actualTs) : '无';
                        var s = substrTime(row.expectTs) + '<br/>' + upTs;
                        return s;
                    }
                }, {
                    field: 'feasibilityUserId',
                    width: '10%',
                    title: '负责人<br/>审核人',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var designer = row.proDesigerId != 0 ? row.proDesigerId : '无';
                        var s = designer + '<br><label style="color:blue">' + row.feasibilityUserId + '</label>';
                        return s;
                    }
                }, {
                    field: 'Evaluation',
                    width: '10%',
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
                    sortName: params.sortName,
                    sortOrder: params.sortOrder
                }
            }
        });
        var custips;
        window.operateEvents = {
            'mouseenter .showCusTips': function (e, value, row, index) {
                $.ajax({
                    type: "GET",
                    async: false,
                    data: {'cusId': row.CustomerInfoId},
                    url: "/customer/cusDetail/" + row.CustomerInfoId,
                    success: function (data) {
                        if (data['cusInfo']) {
                            var contacts = '';
                            for (var contact in data['contacts']) {
                                var type = data['contacts'][contact].ConType;
                                type == '' ? type = '其它类型联系人' : type = type;
                                contacts += type + ':<br/>' +
                                        '<span style="text-indent: 2em;line-height: 7px">姓名：' + data['contacts'][contact].Name + '</span>' + '<br>' +
                                        '<span style="text-indent: 2em;line-height: 7px">联系电话：' + data['contacts'][contact].Mobile + '</span><br>';
                            }
                            custips = layer.tips('<div style="word-wrap: break-word; color: #ffffff">客户名称：' + data['cusInfo'].CusName + '<br/>' +
                                    '客户经理：' + data['cusInfo'].SellName + '<br/>' +
                                    '客户类型：' + data['cusInfo'].CusTypeName + '<br/>' +
                                    '联系电话：' + data['cusInfo'].Tel + '<br/>' +
                                    '邮件：' + data['cusInfo'].EMAIL + '<br/>' +
                                    '地址：' + data['cusInfo'].Address + '<br/><hr>' + contacts + '</div>'
                                    , '#cusInfo_' + row.Id, {time: 0, tips: [2, '#999999'], maxWidth: 400});
                        }
                    }
                });
            },
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
            'mouseover .icon-client-state.icon-manage': function (e, value, row, index) {
                if (row.identity.MANdetails) {
                    custips = layer.tips(row.identity.MANdetails, '#cusInfo_' + row.Id, {
                        time: 0,
                        tips: [1, '#999999'],
                        maxWidth: 400
                    });
                }
            },
            'mouseleave .icon-client-state.icon-manage': function (e, value, row, index) {
                if (row.identity.MANdetails) {
                    layer.close(custips);
                }
            },
            'mouseover .icon-client-state.icon-three': function (e, value, row, index) {
                if (row.identity.DSFdetails) {
                    custips = layer.tips(row.identity.DSFdetails, '#cusInfo_' + row.Id, {
                        time: 0,
                        tips: [1, '#999999'],
                        maxWidth: 400
                    });
                }
            },
            'mouseleave .icon-client-state.icon-three': function (e, value, row, index) {
                if (row.identity.DSFdetails) {
                    layer.close(custips);
                }
            },
            'mouseleave .showCusTips': function (e, value, row, index) {
                layer.close(custips);
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
        if (data.name == 'approval' || data.name == 'testApproval' || data.name == 'approved') {
            timeOutIds = $("#" + values).val();
            //没数据则直接返回
            if (timeOutIds == "") {
                return
            }

            $("#timeOutIds").val(timeOutIds);
            getTimeChanged();
            //重置下拉框
            pullDownChoice("all-type-list", function (param) {
                $("#changeType").val("");
            }, "");
            pullDownChoice("all-status-list", function (param) {
                $("#changeState").val("");
            }, "");
            pullDownChoice("all-condition-list", function (param) {
                $("#changeCondition").val("");
            }, "");
        }

        if (data == "all-type-list") {
            $("#changeType").val(values);
        }
        if (data == "all-status-list") {
            $("#changeState").val(values);
        }
        if (data == "all-condition-list") {
            $("#changeCondition").val(values);
        }
        $('#changeTable').bootstrapTable('refresh', {
            query: {
                'changeType': $('#changeType').val(),
                'changeState': $('#changeState').val(),
                'changeCondition': $('#changeCondition').val(),
                'changeStartTime': $("#changeStartTime").val(),
                'changeEndTime': $("#changeEndTime").val(),
                'actualStartTime': $("#actualStartTime").val(),
                'actualEndTime': $("#actualEndTime").val(),
                'searchInfo': $("#searchInfo").val(),
                'timeOutIds': $("#timeOutIds").val(),
                'pageNumber': 1
            }
        });
    }
    initTable();
    /**
     * 获取超时的数据
     */
    function getTimeChanged() {
        $.ajax({//查询待处理工单数量
            type: "GET",
            async: false,
            url: "/change/getTimeChanged",
            success: function (data) {
                if (data) {
                    $("#approvalHtml").html(data.approval.count);
                    $("#testApprovalHtml").html(data.testApproval.count);
                    $("#approvedHtml").html(data.approved.count);

                    $("#approval").val(data.approval.ids);
                    $("#testApproval").val(data.testApproval.ids);
                    $("#approved").val(data.approved.ids);
                    //全部ids
                    $("#allTimeOutIds").val(data.allIds);

                }
            }
        });
    }
    getTimeChanged();
    $(function () {
        //operate list
        pullDownChoice("all-type-list", function (param) {
            doNewSearch("all-type-list", param);
        });
        pullDownChoice("all-status-list", function (param) {
            doNewSearch("all-status-list", param);
        });
        pullDownChoice("all-condition-list", function (param) {
            doNewSearch("all-condition-list", param);
        });

        showToolTip($("._handle_tip"));
        showTableToolTip("._jobState");
    })
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
</script>
</body>

</html>