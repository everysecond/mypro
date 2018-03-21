<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>问题详情</title>
    <link href="/css/style.css?v=4.1.0" rel="stylesheet">
    <link rel="stylesheet" href="/css/change_detail.css"/>
    <link rel="stylesheet" href="/css/event_charge.css"/>
    <link rel="stylesheet" href="/js/plugins/kindeditor/themes/default/default.css"/>
    <link rel="stylesheet" href="/js/plugins/kindeditor/plugins/code/prettify.css"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .hiddenDiv {
            display: none;
        }

        .relate-btn {
            display: inline-block;
            padding: 6px 15px;
            font-size: 14px;
            text-align: center;
            color: #ffffff;
            border-radius: 3px;
            background-color: #19b492;
            margin-left: 10px;
            margin-bottom: 12px;
        }

        .info-content span {
            font-weight: bold;
        }
        .stl {
            font-size: 14px;
            color: rgba(14, 17, 24, 1)
        }

        .cont p {
            font-size: 12px;
            color: #565656;
            margin: 0;
        }
        .event-table tr a {
            height: 0;
        }

    </style>
</head>
<body>
<div class="job-detail clearfix">
    <div class="job-detail-left">
        <form id="myform">
            <p class="info-title" title="问题标题" id="detailsArea">{{$issue->issueTitle}}</p>
            <div class="job-info module-style">
                <input type="hidden" name="Id" id="issueId" value="{{$issue->Id}}"/>
                <input type="hidden" name="issueState" value="{{$issue->issueState}}"/>
                <input type="hidden" id="issueSource" name="issueSource" value="{{$issue->issueSource}}"/>
                <input type="hidden" name="issueTitle" value="{{$issue->issueTitle}}"/>
                <input type="hidden" name="issueStateMeans"
                       value="{{ThirdCallHelper::getDictMeans('问题状态','issueState',$issue->issueState)}}"/>
                <div class="info-top">
                    <p>问题基本信息</p>
                </div>
                <div class="info-content">
                    <ul>
                        <li><span>问题单号：</span>
                            <p>{{$issue->issueNo}}</p></li>
                        <li><span>状态：</span>
                            <p>
                                {{ThirdCallHelper::getDictMeans('问题状态','issueState',$issue->issueState)}}
                                @if(!($issue->issueState == 'reject' || $issue->issueState== 'completed'))<span
                                        style="margin-left: -3px;font-weight: 100">中</span>
                                @endif
                                <img src="/img/flowchart.png" width="20" height="15" id="flowChart" title="查看流程图" style="vertical-align:middle">
                            </p></li>
                        <li><span>问题来源：</span>
                            <p>
                                {!! $issueSource->issueSource !!}
                            </p>
                        </li>
                        <li></li>
                        <li><span>问题分类：</span>
                            <p>{{ThirdCallHelper::getDictMeans('工单类型','WorksheetTypeOne',$issue->issueCategory)}}</p>
                        </li>
                        <li><span>优先级：</span>
                            <p>{{ThirdCallHelper::getDictMeans('优先级','issuePriority',$issue->issuePriority)}}
                            </p>
                        </li>
                        <li><span>问题提交人：</span>
                            <p>{{ThirdCallHelper::getStuffName($issue->issueSubmitUserId)}}
                            </p>
                        </li>
                        <li><span>问题提交时间：</span>
                            <p>{{$issue->ts}}</p>
                        </li>

                    </ul>
                </div>
                <div class="info-top">
                    <p>问题描述</p>
                </div>
                <div class="info-content">
                    <li>
                        <div class="info-body">
                            {{$issue->issueDescribe}}
                        </div>
                    </li>
                </div>
                @if($statusStep>1)
                    @include("issue/issueanalyse")
                @endif
                @if($statusStep>2)
                    @include("issue/issuecheck")
                @endif
                @if($statusStep>4)
                    @include("issue/issueclose")
                @endif
            </div>
            <div class="job-record module-style">
                <div class="label-title">
                    <span id="recordCommu" class="title_active">问题记录<span class="label_line"></span></span>
                    <span id="recordChange" class="title_active">相关变更</span>
                    <span id="recordSupport" class="title_active">相关工单</span>
                </div>
                <div>
                    <div id="recordCommuList" class="record-list">
                        @foreach($issueRecord as $record)
                                <div class="title-type-short">
                                    <div class="list-wrap-left">
                                        <p class="left-no-portrait issue-{{$record->issueStatusCode}}">
                                            <span class="portrait-text">{{$record->subName}}</span>
                                        </p>
                                    </div>
                                    于
                                    {{$record->ts}} <span style="color
                                    @if($record->passOrNo == '审核不通过')
                                            :red;
                                    @elseif($record->passOrNo == '审核通过')
                                            :#1ab394;
                                    @else
                                            :#666666;
                                    @endif
                                            ">{{$record->passOrNo}}</span> {{$record->issueState}}
                                </div>
                                <div class="title-content">
                                    <div class="info-body" style="margin-left: 55px">
                                        {!! $record->issuecontent !!}
                                    </div>
                                </div>
                        @endforeach
                        {{--匹配当前状态所需角色权限及人员Id,不符合则不显示操作form--}}
                        {{--问题审核由问题经理操作，只有问题经理才显示操作form--}}
                        @if($issue->issueState == 'approval' && $issue->issueCheckUserId==$userId && $hasRule)
                            @include("issue/approvalform")
                            {{--问题分析由问题分析专家操作，只有问题分析专家才显示操作form--}}
                        @elseif($issue->issueState == 'analyse' && $issue->issueChargeUserId == $userId && $hasRule)
                            @include("issue/analyseform")
                            {{--确认实施方案由问题经理操作，只有问题经理才显示操作form--}}
                        @elseif($issue->issueState == 'check' && $issue->issueCheckUserId==$userId && $hasRule)
                            @include("issue/checkform")
                            {{--问题关闭由问题经理操作，只有问题经理才显示操作form--}}
                        @elseif($issue->issueState == 'closed' && $issue->issueCheckUserId==$userId && $hasRule)
                            @include("issue/closedform")
                        @endif
                    </div>
                    <div id="recordChangeList" class="record-list hide">
                        <div>
                            <table id="relateChangeTable" class="event-table" style="width: 100%;"
                                   style="text-align: center;color:#6b7d86;" bgcolor="#FFFFFF"
                                   cellpadding="0"
                                   cellspacing="0" width="100%"
                                   data-pagination="true"
                                   data-show-export="true"
                                   data-page-size="10"
                                   data-id-field="Id"
                                   data-pagination-detail-h-align="right"
                                   data-page-list="[10]"
                                   data-show-footer="false"
                                   data-side-pagination="server"
                                   data-url="/issue/relateChangeData?id={{$issue->Id}}"
                                   data-response-handler="responseHandler">
                            </table>
                        </div>
                        <div>
                            <input type="button" id="changeClose" class="relate-btn" value="批量取消关联">
                            <input type="button" id="triggerChange" class="relate-btn" value="生成并提出变更申请">
                            <input type="button" id="toRelateChange" class="relate-btn" value="关联已有变更">
                            <input type="hidden" id="hiddenChangeId" value="">
                        </div>
                    </div>
                    <div id="recordSupportList" class="record-list hide">
                        <div>
                            <table id="relateSupportTable" class="event-table" style="width: 100%;"
                                   style="text-align: center;color:#6b7d86;" bgcolor="#FFFFFF"
                                   cellpadding="0"
                                   cellspacing="0" width="100%"
                                   data-pagination="true"
                                   data-show-export="true"
                                   data-page-size="10"
                                   data-id-field="Id"
                                   data-pagination-detail-h-align="right"
                                   data-page-list="[10]"
                                   data-show-footer="false"
                                   data-side-pagination="server"
                                   data-url="/issue/relateSupportData?id={{$issue->Id}}"
                                   data-response-handler="responseHandler">
                            </table>
                        </div>
                        <div>
                            <input type="button" id="supportClose" class="relate-btn" value="批量取消关联">
                            <input type="button" id="triggerSupport" class="relate-btn" value="生成并提出工单申请">
                            <input type="button" id="toRelateSupport" class="relate-btn" value="关联已有工单">
                            <input type="hidden" id="hiddenSupportId" value="">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<div id="enlargeImage" class="hide">
    <div class="img-wrap">
        <i id="closeLargeImg" class="img-close"></i>
        <img class="large-img" src=""/>
    </div>
</div>
<!-- 全局js -->

<script src="/js/jquery.min.js?v=2.1.4"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="/js/bootstrap.min.js?v=3.3.6"></script>
<!-- 第三方插件 -->
<script src="/render/hplus/js/content.js?v=1.0.0"></script>
<script src="/js/plugins/suggest/bootstrap-suggest.min.js"></script>
<script src="/js/plugins/bootstrap-table/bootstrap-table.js"></script>
<!-- put your locale files after bootstrap-table.js -->
<script src="/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>

<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/layer/layer.min.js"></script>


<script charset="utf-8" src="/js/plugins/kindeditor/kindeditor-min.js"></script>
<script charset="utf-8" src="/js/plugins/kindeditor/lang/zh_CN.js"></script>
<script src="/js/plugins/layer/laydate/laydate.js"></script>
<script>
    var url = "{{env("JOB_URL")}}";

    $(function () {
        $('#issueChargeUserId').focus(function () {
            $('#hiddenDiv').removeClass('hiddenDiv');
            $('#checkUser').focus();
        });

        $('#checkUser').blur(function () {
            $('#hiddenDiv').addClass('hiddenDiv');
        });
    });
    /*查询结果验证人*/
    var globaldata;
    var contactBsSuggest = $("#checkUser").bsSuggest({
        indexId: 0, //data.value 的第几个数据，作为input输入框的 data-id，设为 -1 且 idField 为空则不设置此值
        indexKey: 1, //data.value 的第几个数据，作为input输入框的内容
        idField: 'ID',//每组数据的哪个字段作为 data-id，优先级高于 indexId 设置（推荐）
        keyField: 'Keyword',//每组数据的哪个字段作为输入框内容，优先级高于 indexKey 设置（推荐）
        allowNoKeyword: false, //是否允许无关键字时请求数据
        showBtn: true,
        multiWord: false, //以分隔符号分割的多关键字支持
        getDataMethod: "url", //获取数据的方式，总是从 URL 获取
        effectiveFields: ["Keyword"],
        effectiveFieldsAlias: {
            Keyword: "员工"
        },
        showHeader: false,
        url: '/issue/issueapply?code=utf-8&extras=1&roleType=issue_analyse&Name=',
        processData: function (json) { // url 获取数据时，对数据的处理，作为 getData 的回调函数;
            globaldata = json;
            var i, len, data = {
                value: []
            };

            if (!json || json.length == 0) {
                return false;
            }

            len = json.length;

            for (var j = 0; j < len; j++) {
                data.value.push({
                    "Id": (j + 1),
                    "Keyword": json[j].Name
                });
            }
            return data;
        }

    }).on("onSetSelectValue", function (e, keyword) {
        $('#issueChargeUserId').val(globaldata[keyword.id - 1].Name);
        $('#cid').val(globaldata[keyword.id - 1].Id);
    })
    function getIdSelections() {
        return $.map($table.bootstrapTable('getSelections'), function (row) {
            return row.id
        });
    }
    var relateChangeTable = $('#relateChangeTable'),
        relateSupportTable = $('#relateSupportTable'),
            selections = [];

    function initTable() {//加载数据
        relateChangeTable.bootstrapTable({
            pageSize: 20,
            striped: true,
            columns: [
                [{
                    field: 'state',
                    checkbox: true,
                    align: 'left',
                    valign: 'middle',
                    width: '5%',
                }, {
                    title: 'RFC编号',
                    valign: 'middle',
                    field: 'RFCNO',
                    align: 'left',
                    width: '15%',
                    formatter: function (value, row, index) {

                        var s = '<a class="J_menuItem" title="变更RFC编号'+row.RFCNO+'" href="/change/details/' + row.Id + '">'+row.RFCNO +'</a>';
                        return s;
                    }
                }, {
                    title: '变更标题',
                    valign: 'middle',
                    field: 'changeTitle',
                    align: 'left',
                    width: '15%',
                    formatter: function (value, row, index) {
                        var s = substringLen(row.changeTitle);

                        return s;
                    },
                    events: 'operateEvents'
                }, {
                    field: 'issuePriority',
                    title: '变更对象',
                    valign: 'middle',
                    align: 'left',
                    width: '15%',
                    formatter: function (value, row, index) {
                        return substringLen(row.changeObject);
                    },
                    events: 'operateEvents'
                }, {
                    field: 'issueCategory',
                    title: '变更类别',
                    valign: 'middle',
                    align: 'left',
                    width: '10%',
                    formatter: function (value, row, index) {
                        return row.changeCategory;
                    }
                }, {
                    field: 'Ts',
                    title: '变更申请人<br/>申请时间',
                    valign: 'middle',
                    align: 'left',
                    width: '20%',
                    formatter: function (value, row, index) {
                        var s = row.applyUserId + '<br/>' + row.Ts;
                        return s;
                    }
                }]
            ],
            queryParamsType: "undefined",
            queryParams: function queryParams(params) {
                return {
                    pageSize: params.pageSize,
                    pageNumber: params.pageNumber,
                }
            }
        });
        relateSupportTable.bootstrapTable({
            pageSize: 20,
            striped: true,
            columns: [
                [{
                    field: 'state',
                    align: 'left',
                    valign: 'middle',
                    checkbox: true,
                    width: '5%',
                }, {
                    title: '工单编号',
                    valign: 'middle',
                    field: 'Id',
                    align: 'left',
                    width: '15%',
                    formatter: function (value, row, index) {
                        var s = '<a class="J_menuItem" title="工单编号'+row.Id+'" href="/wo/supportrefer/' + row.Id + '">'+row.Id +'</a>';
                        return s;
                    }
                }, {
                    title: '工单标题',
                    valign: 'middle',
                    field: 'Title',
                    align: 'left',
                    width: '15%',
                    formatter: function (value, row, index) {
                        var s = substringLen(row.Title);

                        return s;
                    },
                    events: 'operateEvents'
                }, {
                    field: 'CusName',
                    title: '客户',
                    valign: 'middle',
                    align: 'left',
                    width: '15%',
                    formatter: function (value, row, index) {
                        return row.CusName;
                    },
                    events: 'operateEvents'
                }, {
                    field: 'dataCenter',
                    title: '数据中心',
                    valign: 'middle',
                    align: 'left',
                    width: '10%',
                    formatter: function (value, row, index) {
                        return row.dataCenter;
                    },
                    events: 'operateEvents'
                }, {
                    field: 'Ts',
                    title: '创建人<br/>创建时间',
                    valign: 'middle',
                    align: 'left',
                    width: '15%',
                    formatter: function (value, row, index) {
                        var s = row.CreateUserId + '<br/>' + row.Ts;
                        return s;
                    }
                }]
            ],
            queryParamsType: "undefined",
            queryParams: function queryParams(params) {
                return {
                    pageSize: params.pageSize,
                    pageNumber: params.pageNumber,
                }
            }
        });
    }
    initTable();
    /*
     * 批量取消关联
     */

    layer.config({
        extend: 'extend/layer.ext.js'
    });
    $("#changeClose").click(function () {
        layer.confirm('确定要取消关联吗?', {
                    title: "提示",
                    btn: ['确定', '取消']
                },
                function () {
                    var selected = $('#relateChangeTable').bootstrapTable('getSelections');
                    if (selected.length < 1) {
                        layer.msg('请选择要取消关联的变更！', {icon: 2});
                        return false;
                    }
                    layer.prompt({
                        title: '请输入取消关联理由',
                        formType: 2 //prompt风格，支持0-2
                    }, function (text) {
                        $.ajax({
                            type: "POST",
                            data: {'Ids': selected, 'reason': text, 'issueId': $("#issueId").val()},
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                            },
                            url: "/correlation/closeIssueToChange",
                            success: function (data) {
                                if (data.status == 'success') {
                                    layer.msg('批量取消关联成功！', {icon: 1});
                                    $('#relateChangeTable').bootstrapTable('refresh');
                                }
                            }
                        })
                    });
                })
    });
    $("#supportClose").click(function () {
        layer.confirm('确定要取消关联吗?', {
                    title: "提示",
                    btn: ['确定', '取消']
                },
                function () {
                    var selected = $('#relateSupportTable').bootstrapTable('getSelections');
                    if (selected.length < 1) {
                        layer.msg('请选择要取消关联的变更！', {icon: 2});
                        return false;
                    }
                    layer.prompt({
                        title: '请输入取消关联理由',
                        formType: 2 //prompt风格，支持0-2
                    }, function (text) {
                        $.ajax({
                            type: "POST",
                            data: {'Ids': selected, 'reason': text, 'issueId': $("#issueId").val()},
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                            },
                            url: "/correlation/closeIssueToSupport",
                            success: function (data) {
                                if (data.status == 'success') {
                                    layer.msg('批量取消关联成功！', {icon: 1});
                                    $('#relateSupportTable').bootstrapTable('refresh');
                                }
                            }
                        })
                    });
                })
    });
    function responseHandler(res) {
        $.each(res.rows, function (i, row) {
            row.state = $.inArray(row.id, selections) !== -1;
        });
        return res;
    }
    function substringLen(text, length) {
        var length = arguments[1] ? arguments[1] : 16;
        suffix = "";
        if (text.length > length) {
            suffix = "..";
        }
        return text.substr(0, length) + suffix;
    }
    $('#flowChart').click(function () {
        flowChart = layer.open({
            type: 2,
            title: false,
            closeBtn: 0, //不显示关闭按钮
            shade: [0],
            shadeClose: true,
            area: ['690px', '440px'],

            content: ['/issue/flowChart?currentStatus=' + $('input[name="issueStateMeans"]').val(), 'no']
        });
    });
</script>
<script type="text/javascript" src="/js/issue_detail.js?66"></script>
</body>
</html>
