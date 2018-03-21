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
                    <button class="submit-btn" id="subchange"> + 提交变更</button>
                    <input type="hidden" value="" id="cusType">
                    <input type="hidden" value="" id="Status">
                    <input type="hidden" value="" id="timeOutIds">

                    <input type="hidden" value="" id="approval">
                    <input type="hidden" value="" id="testApproval">
                    <input type="hidden" value="" id="approved">
                    <input type="hidden" value="" id="allTimeOutIds">

                    <div class="job-handle-wrap">
                        <div class="job-handle">
                            <div class="_handle_tip handle-tips">
                                <a href="javascript:void(0);" name="approval"
                                   onclick="doNewSearch(this,'approval')"><i class="icon-time"></i></a><span
                                        id="approvalHtml" class="tips-huang"></span>
                                <div class="tool-tips" style="display: none;"><p class="tool-title">可行性审批超时</p><i
                                            class="tool-arrow"></i></div>
                            </div>
                            <div class="_handle_tip handle-tips">
                                <a href="javascript:void(0);" name="testApproval"
                                   onclick="doNewSearch(this,'testApproval')"><i class="icon-time"></i></a><span
                                        id="testApprovalHtml" class="tips-yellow"></span>
                                <div class="tool-tips" style="display: none;"><p class="tool-title">测试结果审批超时</p><i
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
                                       data-pagination-detail-h-align="right"
                                       data-page-list="[10, 25, 50, 100, ALL]"
                                       data-show-footer="false"
                                       data-side-pagination="server"
                                       data-url="/change/todoListData"
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
    $("#subchange").click(function () {
        layer.open({
            type: 2,
            title: '变更管理>变更申请单 （<span style="color:#ff253d">以下全部必填</span>）',
            area: ['840px', '620px'],
            content: '/change/changerefer?triggerId=' + '&issueId=' + '&supportId=' ,
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
                [
                    {
                        title: 'RFC编号',
                        valign: 'middle',
                        align: 'center',
                        field: 'RFCNO',
                        width: '10%',
                        formatter: function (value, row, index) {
                            var s = '<a class="showTitleTips J_menuItem" style="display: inline-block; min-width: 130px"  id="RFCNO_' + row.Id + '"    ' +
                                    'href="/change/details/' + row.Id + '">' + row.RFCNO + '</a>';

                            return s;
                        }
                    },
                    {
                        title: '变更标题',
                        valign: 'middle',
                        width: '10%',
                        field: 'changeTitle',
                        align: 'left',
                        formatter: function (value, row, index) {
                            if (row.changeState == '变更驳回') {
                                var s = '<a class="showTitleTips J_menuItem" style="color:#19B492;"  menuName="' + row.Id + '"  id="title_' + row.Id + '"    ' +
                                        'href="/change/saveToapply/' + row.Id + '">' + substringText(row.changeTitle) + '</a>';
                            }
                            else {
                                var s = '<a class="showTitleTips J_menuItem" style="color:#19B492;" menuName="' + row.Id + '"  id="title_' + row.Id + '"    ' +
                                        'href="/change/details/' + row.Id + '">' + substringText(row.changeTitle) + '</a>';
                            }
                            return s;
                        },
                        events: 'operateEvents'
                    },
                    {
                        field: 'changeState',
                        width: '12%',
                        title: '<div id="todo-state-list" class="select-wrap"><span class="current-title"><span class="current-select">变更状态</span><i class="select-icon"></i></span>' +
                        '<ul class="select-list"><li class="select-list-item" value="">变更状态</li>@foreach($statusList as $status) <li class="select-list-item" value="{{$status->Code}}">{{$status->Means}}</li>@endforeach</ul></div>',
                        valign: 'middle',
                        align: 'left',
                        events: 'operateEvents'
                    },
                    {
                        field: 'changeType',
                        width: '10%',
                        title: '<div id="todo-type-list" class="select-wrap"><span class="current-title"><span class="current-select">变更类型</span><i class="select-icon"></i></span>' +
                        '<ul class="select-list"><li class="select-list-item" value="">变更类型</li>@foreach($typeList as $type) <li class="select-list-item" value="{{$type->Code}}">{{$type->Means}}</li>@endforeach</ul></div>',
                        valign: 'middle',
                        align: 'left',
                        formatter: function (value, row, index) {
                            var titleStyle = "";
                            if (row.changeType == "重大变更") {
                                titleStyle = "font-weight:bold";
                                var s = '<span style="' + titleStyle + '"  menuName="' + row.Id + '"  id="title_' + row.Id + '"  ' +
                                        'href="/change/details/' + row.Id + '">' + row.changeType + '</span>';
                            }
                            else {
                                var s = '<span style="' + titleStyle + '"  menuName="' + row.Id + '"  id="title_' + row.Id + '"  ' +
                                        'href="/change/details/' + row.Id + '">' + row.changeType + '</span>';
                            }
                            return s;
                        },
                        events: 'operateEvents'
                    },
                    {
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
                    },
                    {
                        field: 'expectTs',
                        width: '15%',
                        title: '期望完成时间<br/>最后更新时间',
                        valign: 'middle',
                        align: 'left',
                        formatter: function (value, row, index) {
                            var upTs = row.UpTs != null ? substrTime(row.UpTs) : '';
                            var s = substrTime(row.expectTs) + '<br/>' + upTs;
                            return s;
                        }
                    },
                    {
                        field: 'proDesigerId',
                        width: '8%',
                        title: '负责人',
                        valign: 'middle',
                        align: 'left',
                        formatter: function (value, row, index) {
                            var s = row.proDesigerId;
                            return s;
                        }
                    },
                    {
                        field: 'applyUserId',
                        width: '14%',
                        title: '变更申请人<br/>变更申请时间',
                        valign: 'middle',
                        align: 'left',
                        formatter: function (value, row, index) {
                            var s = row.applyUserId + '<br/>' + substrTime(row.applyTs);
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
                    'timeOutId': $('#timeOutId').val(),
                    'cusType': $('#cusType').val(),
                    'Status': $('#Status').val(),
                }
            }
        });

        var custips;
        //bootstrap监听事件
        window.operateEvents = {
            'mouseover .showTitleTips': function (e, value, row, index) {
                custips = layer.tips('<div style="word-wrap: break-word;color: #ffffff">变更标题：' + row.changeTitle + '<br/>' +
                        '变更原因：<div class="supportBody" style="color: #ffffff">' + row.changeReason + '</div>' +
                        '变更内容：<div class="supportBody" style="color: #ffffff">' + row.changeContext + '</div>' +
                        '变更风险及影响分析：<div class="supportBody" style="color: #ffffff">' + row.changeRisk + '</div>' +
                        '触发条件：' + row.changeCondition + '<br/>' +
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
            'mouseleave .showRFCNOTips': function (e, value, row, index) {
                layer.close(custips);
            },
            'mouseout .showTitleTips': function (e, value, row, index) {
                layer.close(custips);
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


    function doNewSearch(data, values) {//普通工单检索
        if (data.name == 'approval' || data.name == 'testApproval' || data.name == 'approved') {
            timeOutIds = $("#" + values).val();
            //没数据则直接返回
            if (timeOutIds == "") {
                return
            }
            $("#timeOutIds").val(timeOutIds);
            getTodoTimeChanged();

            //重置下拉框
            pullDownChoice("todo-state-list", function (param) {
                $("#Status").val("");
            }, "");
            pullDownChoice("todo-type-list", function (param) {
                $("#cusType").val("");
            }, "");

        }
        if (data == "todo-type-list") {
            $("#cusType").val(values);
        }
        if (data == "todo-state-list") {
            $("#Status").val(values);
        }

        $('#changeTable').bootstrapTable('refresh', {
            query: {
                'user': $("#mySupport").val(),
                'SuppOptGroup': $('#SuppOptGroup').val(),
                'priority': $('#priority').val(),
                'changeType': $('#cusType').val(),
                'changeState': $('#Status').val(),
                'dataCenter': $('#dataCenter').val(),
                'replyTime': $('#replyTime').val(),
                'Evaluation': $('#Evaluation').val(),
                'searchInfo': $('#searchInfo').val(),
                'timeOutIds': $("#timeOutIds").val(),
                'pageNumber': 1
            }
        });
    }
    /**
     * 获取超时的数据
     */
    function getTodoTimeChanged() {
        $.ajax({//查询待处理工单数量
            type: "GET",
            async: false,
            url: "/change/getTodoTimeChanged",
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
    getTodoTimeChanged();

    $('#ordinarySupport').click(function () {
        $('#supT1').removeClass('hiddenTable');
        $('#supT2').addClass('hiddenTable');
    });
    $('#emailSupport').click(function () {
        $('#supT2').removeClass('hiddenTable');
        $('#supT1').addClass('hiddenTable');
    });
    initTable();
    $(function () {
        pullDownChoice("todo-state-list", function (param) {
            doNewSearch("todo-state-list", param);
        });
        pullDownChoice("todo-type-list", function (param) {
            doNewSearch("todo-type-list", param);
        });
        pullDownChoice("mail-todo-status-list", function (param) {
            doNewSearch2(param);
        });
        showToolTip($("._handle_tip"));
        showTableToolTip("._jobState");
    })
</script>
</body>

</html>