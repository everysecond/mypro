<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>变更详情</title>
    <link href="/css/style.css?v=4.1.0" rel="stylesheet">
    <link rel="stylesheet" href="/css/change_detail.css"/>
    <link rel="stylesheet" href="/css/event_charge.css"/>
    <link rel="stylesheet" href="/js/plugins/kindeditor/themes/default/default.css"/>
    <link rel="stylesheet" href="/js/plugins/kindeditor/plugins/code/prettify.css"/>
    <link rel="stylesheet" href="/css/plugins/bootstrap-table/bootstrap-table.css">
    <link href="/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .red {
            color: #fd0001
        }

        .info-content span {
            font-weight: bold;
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
            <p class="info-title" title="变更标题" id="detailsArea">{{$change->changeTitle}}</p>
            <div class="job-info module-style">
                <input type="hidden" name="changeId" Id="changeId" value="{{$change->Id}}"/>
                <input type="hidden" name="changeState" value="{{$change->changeState}}"/>
                <input type="hidden" name="changeNo" value="{{$change->RFCNO}}"/>
                <input type="hidden" id="changeType" name="changeType" value="{{$change->changeType}}"/>
                <input type="hidden" name="changeTitle" value="{{$change->changeTitle}}"/>
                <input type="hidden" name="feasibilityGroupId" value="{{$change->feasibilityGroupId}}"/>
                <input type="hidden" name="changeStateMeans"
                       value="{{ThirdCallHelper::getDictMeans('变更状态','changeState',$change->changeState)}}"/>
                <div class="info-top">
                    <p>变更申请信息</p>
                </div>
                <div class="info-content">
                    <ul>
                        <li><span>RFC编号：</span>
                            <p>{{$change->RFCNO}}</p></li>
                        <li><span>状态：</span>
                            <p id="">
                                {{ThirdCallHelper::getDictMeans('变更状态','changeState',$change->changeState)}}
                                @if(!($change->changeState == 'reject' || $change->changeState== 'completed'))<span
                                        style="margin-left: -3px;font-weight:100;">中</span>
                                @endif
                                <img src="/img/flowchart.png" width="20" height="15" id="flowChart" title="查看流程图">
                            </p></li>
                        <li><span>变更对象：</span>
                            <p>
                                {{$change->changeObject}}
                            </p>
                        </li>
                        <li><span>期望完成时间：</span>
                            <p>{{$change->expectTs}}</p></li>
                        <li><span>变更类型：</span>
                            <p>{{ThirdCallHelper::getDictMeans('变更类型','changeType',$change->changeType)}}
                            </p>
                        </li>
                        <li><span>触发条件：</span>
                            <p>{{ThirdCallHelper::getDictMeans('触发条件','changeCondition',$change->changeCondition)}}
                            </p>
                        </li>
                        <li><span>变更类别：</span>
                            <p>{{ThirdCallHelper::getDictMeans('变更类别','changeCategory',$change->changeCategory)}}</p>
                        </li>
                        <li><span>变更子类：</span>
                            <p>{{ThirdCallHelper::getDictMeans('变更子类','changeSub',$change->changeSubCategory)}}</p></li>

                        <li><span>变更原因详细描述：</span>
                            <div class="info-body">
                                {{$change->changeReason}}
                            </div>
                        </li>
                        <li></li>
                        <li><span>变更内容详细描述：</span>
                            <div class="info-body">
                                {{$change->changeContext}}
                            </div>
                        </li>
                        <li></li>
                        <li><span>变更风险及影响分析：</span>
                            <div class="info-body">
                                {{$change->changeRisk}}
                            </div>
                        </li>
                        <li>
                        </li>
                        <li><span>变更申请人：</span>
                            <p style='margin-left: 10px'>{{ThirdCallHelper::getStuffName($change->applyUserId)}}</p>
                        </li>
                        <li><span>申请时间：</span>
                            <p>
                                {{$change->Ts}}
                            </p>
                        </li>
                    </ul>
                </div>
                @if($statusStep>1)
                    @include("change/changesteptwo")
                @endif
                @if($statusStep>3)
                    @include("change/changestepthree")
                @endif
                @if($statusStep>5)
                    @include("change/changestepfive")
                @endif
                @if($statusStep>6)
                    @include("change/changestepsix")
                @endif
            </div>
            <div class="job-record module-style">
                <div class="label-title">
                    <span id="recordCommu" class="title_active">变更记录<span class="label_line"></span></span>
                    <span id="recordIssue" class="title_active">相关问题</span>
                    <span id="recordSupport" class="title_active">相关工单</span>
                </div>
                <div>
                    <div id="recordCommuList" class="record-list">
                        @foreach($changeRecord as $record)
                            <div class="title-type-short">
                                <div class="list-wrap-left">
                                    <p class="left-no-portrait change-{{$record->changeStatusCode}}">
                                        <span class="portrait-text">{{$record->subName}}</span>
                                    </p>
                                </div>
                                于
                                {{$record->ts}} <span style="color:
                                @if($record->passOrNo == '审核不通过')
                                        red;
                                @elseif($record->passOrNo == '审核通过')
                                        #1ab394;
                                @else
                                        #666666;
                                @endif
                                        ">{{$record->passOrNo}}</span> {{$record->changeState}}
                            </div>
                            <div class="title-content">
                                <div class="info-body" style="margin-left: 55px">
                                    {!! $record->replycontent !!}
                                </div>
                            </div>
                        @endforeach
                        {{--匹配当前状态所需角色权限及人员Id,不符合则不显示操作form--}}
                        @if($change->changeState == 'approval' && $feasibilityPermission && $hasRule)
                            {{--可行性审批需要在该部门--}}
                            @include("change/changefeasibility")
                        @elseif($change->changeState == 'design' && $change->proDesigerId == $user->Id && $hasRule)
                            {{--需要方案责任人可以填写方案规划--}}
                            @include("change/changeprogramme")
                        @elseif($change->changeState == 'actualize' && $designPermission  && $hasRule)
                            {{--方案组成员可操作--}}
                            @include("change/changeprodesign")
                        @elseif($change->changeState == 'test' && $testPermission  && $hasRule)
                            {{--测试组成员可操作--}}
                            @include("change/changetesting")
                        @elseif($change->changeState == 'testApproval' && $leaderId == $user->Id  && $hasRule)
                            {{--方案负责人领导可操作--}}
                            @include("change/changeexam")
                        @elseif($change->changeState == 'release' && $change->changeImplementUserId == $user->Id  && $hasRule)
                            {{--变更实施人可操作--}}
                            @include("change/changeimplement")
                        @elseif($change->changeState == 'approved' && $hasRule)
                            {{--验收人可操作--}}
                            @include("change/changeverificating")
                        @endif
                    </div>
                    <div id="recordIssueList" class="record-list hide">
                        <div>
                            <table id="relateIssueTable" class="event-table" style="width: 100%;"
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
                                   data-url="/change/relateIssueData?changeId={{$change->Id}}"
                                   data-response-handler="responseHandler">
                            </table>
                        </div>
                        <div>
                            <input type="button" id="relateClose" class="relate-btn" value="批量取消关联">
                            <input type="button" id="triggerIssue" class="relate-btn" value="生成并提出问题申请">
                            <input type="button" id="toRelateIssue" class="relate-btn" value="关联已有问题">
                            <input type="hidden" id="hiddenIssueId" value="">
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
                                   data-url="/change/relateSupportData?changeId={{$change->Id}}"
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
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/layer/layer.min.js"></script>
<script src="/js/plugins/bootstrap-table/bootstrap-table.js"></script>
<!-- put your locale files after bootstrap-table.js -->
<script src="/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>

<script charset="utf-8" src="/js/plugins/kindeditor/kindeditor-min.js"></script>
<script charset="utf-8" src="/js/plugins/kindeditor/lang/zh_CN.js"></script>
<script src="/js/plugins/layer/laydate/laydate.js"></script>
<script>
    var url = "{{env("JOB_URL")}}";
    function getIdSelections() {
        return $.map($table.bootstrapTable('getSelections'), function (row) {
            return row.id
        });
    }
    var relateIssueTable = $('#relateIssueTable'),
            relateSupportTable = $('#relateSupportTable'),
            selections = [];

    function initTable() {//加载数据
        relateIssueTable.bootstrapTable({
            pageSize: 20,
            striped: true,
            columns: [
                [{
                    field: 'state',
                    checkbox: true,
                    align: 'middle',
                    valign: 'middle',
                    width: '5%',
                }, {
                    title: '问题单号',
                    valign: 'middle',
                    field: 'issueNo',
                    align: 'left',
                    width: '15%',
                    formatter: function (value, row, index) {

                        var s = '<a class="J_menuItem" title="问题单号'+row.issueNo+'" href="/issue/details/' + row.Id + '">'+row.issueNo +'</a>';
                        return s;
                    }
                }, {
                    title: '问题主题',
                    valign: 'middle',
                    field: 'issueTitle',
                    align: 'left',
                    width: '15%',
                    formatter: function (value, row, index) {
                        var s = substringLen(row.issueTitle);
                        return s;
                    },
                    events: 'operateEvents'
                }, {
                    field: 'issueCategory',
                    title: '问题分类',
                    valign: 'middle',
                    align: 'left',
                    width: '10%',
                    formatter: function (value, row, index) {
                        return row.issueCategory;
                    }
                }, {
                    field: 'issuePriority',
                    title: '优先级',
                    valign: 'middle',
                    align: 'left',
                    width: '10%',
                    formatter: function (value, row, index) {
                        return row.issuePriority;
                    },
                    events: 'operateEvents'
                }, {
                    field: 'Ts',
                    title: '问题申请人<br/>申请时间',
                    valign: 'middle',
                    align: 'left',
                    width: '15%',
                    formatter: function (value, row, index) {
                        var s = row.issueSubmitUserId + '<br/>' + row.issueSubmitTs;
                        return s;
                    }
                }]
            ],
            queryParamsType: "undefined",
            queryParams: function queryParams(params) {
                return {
                    pageSize: params.pageSize,
                    pageNumber: params.pageNumber,
                    'timeOutIds': $('#timeOutIds').val(),
                    'cusType': $('#cusType').val(),
                    'Status': $('#Status').val()
                }
            }
        });
        relateSupportTable.bootstrapTable({
            pageSize: 20,
            striped: true,
            columns: [
                [{
                    field: 'state',
                    checkbox: true,
                    align: 'middle',
                    valign: 'middle',
                    width: '5%',
                }, {
                    title: '工单编号',
                    valign: 'middle',
                    field: 'Id',
                    align: 'left',
                    width: '10%',
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
    $("#relateClose").click(function () {
        layer.confirm('确定要取消关联吗?', {
                    title: "提示",
                    btn: ['确定', '取消']
                },
                function () {
                    var selected = $('#relateIssueTable').bootstrapTable('getSelections');
                    if (selected.length < 1) {
                        layer.msg('请选择要取消关联的问题！', {icon: 2});
                        return false;
                    }
                    layer.prompt({
                        title: '请输入取消关联理由',
                        formType: 2 //prompt风格，支持0-2
                    }, function (text) {
                        $.ajax({
                            type: "POST",
                            data: {'Ids': selected, 'reason': text, 'changeId': $("#changeId").val()},
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                            },
                            url: "/correlation/closeChangeToIssue",
                            success: function (data) {
                                if (data.status == 'success') {
                                    layer.msg('批量取消关联成功！', {icon: 1});
                                    $('#relateIssueTable').bootstrapTable('refresh');
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
                            data: {'Ids': selected, 'reason': text, 'changeId': $("#changeId").val()},
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                            },
                            url: "/correlation/closeChangeToSupport",
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
            area: ['700px', '450px'],

            content: ['/change/flowChart?changeId='+$("#changeId").val()+'&currentStatus=' + $('input[name="changeStateMeans"]').val(), 'no']
        });
    });
</script>
<script type="text/javascript" src="/js/change_detail.js?6"></script>
</body>
</html>
