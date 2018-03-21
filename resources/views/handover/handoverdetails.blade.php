<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>变更详情</title>
    <link href="/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/css/style.css?v=4.1.0" rel="stylesheet">
    <link href="/css/font-awesome.css?v=4.4.0" rel="stylesheet">
    <link rel="stylesheet" href="/css/change_detail.css"/>
    <link rel="stylesheet" href="/js/plugins/kindeditor/themes/default/default.css"/>
    <link rel="stylesheet" href="/js/plugins/kindeditor/plugins/code/prettify.css"/>
    <link rel="stylesheet" href="/css/plugins/bootstrap-table/bootstrap-table.css">
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

        .info-content li, .info-content li span, .info-content li p {
            display: inline-block;
            font-size: 14px;
            line-height: 35px;
        }

        .info-content li {
            width: 49%;
        }

        input[type=checkbox] {
            width: 25px;
        }

        .eventDone {
            background-color: darkgreen;
        }

        .hasInvalidated {
            background-color: darkgreen;
        }
    </style>
</head>
<body>
<div class="job-detail clearfix" style="width: 98%;min-width: 720px;margin-left: 10px">
    <form id="myform">
        <p class="info-title" style="height: 32px;" id="detailsArea">交接单详情</p>
        <div class="job-record  module-style" style="width: 98%;margin-top: 0">
            <input type="hidden" name="ccIds" value="{{$ccIds}}"/>
            <input type="hidden" id="handoverId" name="handoverId" value="{{$handoverData->id}}"/>
            <div class="info-content">
                <ul>
                    <li><span>转交部门：</span>
                        {{\Itsm\Http\Helper\ThirdCallHelper::getDepartMeans($handoverData->groupId)}}
                    </li>
                    <li><span>分派任务负责人：</span>
                        {{\Itsm\Http\Helper\ThirdCallHelper::getStuffName($handoverData->chargerId)}}
                    </li>
                    <li><span>预约更换时间：</span>
                        <p>
                        </p>
                    </li>
                    <li><span>自动更换负责人：</span>
                        <p></p></li>
                    <li>
                        <span class="inline">提醒方式：</span>
                        {{$rMode}}
                    </li>
                    <li></li>
                    <li><span>抄送人：</span>
                        @foreach($ccIdsArray as $ccId)
                            {{\Itsm\Http\Helper\ThirdCallHelper::getStuffName($ccId)}}&nbsp;&nbsp;
                        @endforeach
                    </li>
                    <li></li>
                    <li><span>注意事项：</span>
                        <div style="word-break: break-all">
                            {{$handoverData->notes}}
                        </div>
                    </li>
                    <li></li>
                </ul>
            </div>
        </div>
        <div class="job-record module-style" style="width: 98%;padding:0;">
            <div class="label-title">
                <span id="relatedEvents" class="title_active">相关事件<span class="label_line"></span></span>
            </div>
            <div>
                <div id="relatedEventsList" class="record-list">
                    <div>
                        <table id="relatedEventsTable" class="table-no-bordered event-table" style="width: 100%;"
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
                               data-url="/handover/getEvents?handoverId={{$handoverData->id}}"
                               data-response-handler="responseHandler">
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div style="height: 15px"></div>
    </form>
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
<!-- 自定义js -->
<script src="/js/handover.js"></script>
<script>
    var url = "{{env("JOB_URL")}}";
    var relatedEventsTable = $('#relatedEventsTable'),
            selections = [],
            data = [];

    function initTable() {//加载数据
        relatedEventsTable.bootstrapTable({
            pageSize: 10,
            striped: true,
            cellStyle: function (row, index) {
                if (row.status == 1) {
                    return eventDone;
                } else if (row.isInValidate == 1) {
                    return hasInvalidated;
                }

            },
            columns: [
                [{
                    field: 'id',
                    title: '事件编号',
                    valign: 'middle',
                    align: 'left',
                    width: '5%'
                },{
                    field: 'supportId',
                    title: '工单编号',
                    valign: 'middle',
                    align: 'left',
                    width: '5%',
                    formatter: function (value, row, index) {
                        if (row.supportId) {
                            return row.supportId
                        } else {
                            return '无'
                        }
                    }
                }, {
                    field: 'usName',
                    title: '客户名称',
                    valign: 'middle',
                    align: 'left',
                    width: '12%',
                    formatter: function (value, row, index) {
                        if (row.CusName) {
                            return row.CusName
                        } else {
                            return '无'
                        }
                    }
                }, {
                    field: 'type',
                    title: '事件类型',
                    valign: 'middle',
                    align: 'left',
                    width: '5%'
                }, {
                    field: 'priority',
                    title: '优先级',
                    valign: 'middle',
                    align: 'left',
                    width: '4%',
                    formatter: function (value, row, index) {
                        if (row.priority == 1) {
                            return '重要';
                        } else {
                            return '一般';
                        }
                    }
                }, {
                    field: 'status',
                    title: '状态',
                    valign: 'middle',
                    align: 'left',
                    width: '5%',
                    formatter: function (value, row, index) {
                        if (row.isInValidate == 1) {
                            return '已转移'
                        } else if (row.status == 0) {
                            return '未完成'
                        } else {
                            return '已完成'
                        }
                    }
                }, {
                    field: 'remindTs',
                    title: '预约时间',
                    valign: 'middle',
                    align: 'left',
                    width: '10%'
                }, {
                    field: 'chargerId',
                    title: '负责人',
                    valign: 'middle',
                    align: 'left',
                    width: '5%'
                }, {
                    field: 'notes',
                    title: '事件说明',
                    valign: 'middle',
                    align: 'left',
                    width: '12%',
                    formatter: function (value, row, index) {
                        var s = stringText(row.notes);
                        return s;
                    },
                }, {
                    field: 'submitterId',
                    title: '提交人',
                    valign: 'middle',
                    align: 'left',
                    width: '5%'
                }]
            ],
            queryParamsType: "undefined",
            queryParams: function queryParams(params) {
                return {
                    pageSize: params.pageSize,
                    pageNumber: params.pageNumber
                }
            }
        });
        layer.config({
            extend: 'extend/layer.ext.js'
        });
    }
    initTable();
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

    $("#subEvent").click(function (value, row, index) {
        layer.open({
            type: 2,
            title: '新增交接单>新增事件',
            area: ['840px', '500px'],
            content: ['/handover/eventApply', 'no'],
            maxmin: true
        });
    });

    //交接单验证
    function validate(indexValidate) {
        if ($(this).hasClass("down-btn")) {
            validateMark = true;
            $('.btnSub').removeAttr('disabled');
            layer.close(indexValidate);
            return false;//防止重复提交
        }
        if (!validateMark) {
            $('.validate').each(function () {
                if ($(this).val() == '') {
                    layer.tips('请填写此项！', this, {time: 2000, tips: 2});
                    validateMark = true;
                    $('.btnSub').removeAttr('disabled');
                    layer.close(indexValidate);
                    return false;
                }
            });

            if (!$("input[type='checkbox']").is(':checked')) {
                layer.tips('请填写此项！', $("input[type='checkbox']"), {time: 2000, tips: 3});
                validateMark = true;
                $('.btnSub').removeAttr('disabled');
                layer.close(indexValidate);
                return false;
            }
        }
    }
    $(function(){
        $(".record-list .clearfix").remove();
    })
</script>
</body>
</html>
