<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="_token" content="{{ csrf_token() }}"/>
    <title>安畅网络 工单系统——工单统计快照列表</title>

    <!--[if lt IE 9]>
    <meta http-equiv="refresh" content="0;ie.html"/>
    <![endif]-->

    <link rel="shortcut icon" href="favicon.ico">
    <link href="/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/css/font-awesome.min.css?v=4.4.0" rel="stylesheet">
    <link href="/css/animate.css" rel="stylesheet">
    <link href="/css/style.css?v=4.1.0" rel="stylesheet">
    <link rel="stylesheet" href="/css/plugins/bootstrap-table/bootstrap-table.css">
    <link rel="stylesheet" href="/css/job_list.css">
    <link rel="stylesheet" href="/css/handover.css">
    <style>
        .hiddenTable {
            display: none;
        }

        .tab_bq {
            width: 700px;
            position: absolute;
            left: 0;
            top: 34px;
            z-index: 100;
            border: 1px solid #e6e4e4;
            background: #fff;
            padding: 10px;
        }

        .tab_bq input {
            vertical-align: middle;
            width: 15px;
            margin: -3px;
            line-height: normal;
            box-sizing: border-box;
            padding: 0;
            height: 20px;
            border: 0;
        }
        .tab_bq label {
            font-weight: 100;
            font-size: 10px;
            margin-bottom: 0;
        }
        #r2HQ22{
            margin-right: 9px;
        }

    </style>
</head>
<body>
<div class=" wrapper-content">
    <div class="row">
        <div class="col-sm-12">
            <input type="hidden" value="" id="Status">
            <input type="hidden" value="{{isset($params["charge"])? $params["charge"]:''}}" id="chargeGroup">
            <input type="hidden" value="{{isset($params["priority"])? $params["priority"]:''}}" id="priority">
            <input type="hidden" value="{{isset($params["timeOut"])? $params["timeOut"]:''}}" id="timeOut">
            <input type="hidden" value="{{isset($params["responseTime"])? $params["responseTime"]:''}}" id="responseTime">
            <input type="hidden" value="{{isset($params["successNum"])? $params["successNum"]:''}}" id="successNum">
            <input type="hidden" value="{{isset($params["processTime"])? $params["processTime"]:''}}" id="processTime">
            <input type="hidden" value="{{isset($params["supportSource"])? $params["supportSource"]:''}}" id="supportSource">
            <input type="hidden" value="{{isset($params["supportType"])? $params["supportType"]:''}}" id="class3">
            <input type="hidden" value="{{isset($params["evaluate"])? $params["evaluate"]:''}}" id="evaluate">
            {{--条件存储--}}

            <div style="margin-top:5px;">
                <div class="tab-content">
                    <div class="hand-title" style="height: auto">
                        <div style="margin-left: 20px">
                            <div class="fa fa-book"></div>
                            工单快照数据查询
                        </div>
                    </div>
                    <div class="">
                        <div class="full-height-scroll">
                            <div class="ibox-content"
                                 style="padding: 0 2px 2px 10px;border-top-width: 0px;">
                                <div class="row m-b-sm m-t-sm">
                                    <div class="col-md-11">
                                        <form id="kzForm" class="form-inline" style="padding: 4px">
                                            快速查询&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            <span>工单创建年月：</span>
                                            <select style="width: 85px" class="form-control"name="year" id="year">
                                                @foreach($yearList as $year)
                                                    <option value="{{$year['y']}}" @if($params["year"]==$year['y']) selected @endif>{{$year['y']}}</option>
                                                @endforeach
                                            </select>
                                            <select style="width: 70px" class="form-control" name="month" id="month">
                                                @foreach($monthList as $month)
                                                    <option value="{{$month}}" @if($params["month"]==$month) selected @endif>{{$month}}</option>
                                                @endforeach
                                            </select>
                                            <div class="input-group" style="min-width:450px;margin-left: -5px;">
                                                 <a class="btn btn-info" style="background-color:#19b492" id="searchAll"
                                                    onclick="doNewSearch(this,'')">
                                                     <span class="glyphicon glyphicon-search">查询</span>
                                                 </a>
                                                </span>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive" style="background-color: white">
                                <table id="kzTable" class="table-no-bordered table-fixpadding"
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
                                       data-url="/support/getSupportKZData"
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
<div id="test"></div>

<!-- 全局js -->
<script src="/js/jquery.min.js?v=2.1.4"></script>
<script src="/js/jquery.slimscroll.min.js"></script>
<script src="/js/plugins/layer/layer.min.js"></script>
<script src="/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script type="text/javascript" src="/render/hplus/js/contabs.js"></script>
<script type="text/javascript" src="/js/common.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>
<script src="/js/job_list.js"></script>
<script src="/js/plugins/layer/laydate/laydate.js"></script>
<script src="/js/plugins/bootstrap-table/bootstrap-table.js"></script>
<!-- put your locale files after bootstrap-table.js -->
<script src="/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>


<script>
    var url = "{{env('JOB_URL')}}";
    var $kzTable = $('#kzTable'),
            $remove = $('.remove'),
            selections = [];

    function initTable() {
        $kzTable.bootstrapTable({
            pageSize: 20,
            striped: true,
            columns: [
                [{
                    title: '工单编号',
                    valign: 'middle',
                    align: 'left',
                    field: 'Id',
                    width: '6%',
                    formatter: function (value, row, index) {
                        var s = '<a class="J_menuItem" href="support/allList?searchInfo=' + row.supportId + '">' + row.supportId + '</a>';
                        return s ;
                    }
                },  {
                    field: 'CusName',
                    width: '15%',
                    title: '相关客户',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var s = '<a class="showCusTips"  id="cusInfo_' + row.Id + '"    ' +
                                'href="{{env('JOB_URL', 'http://www.51idc.cn')}}/crm/user/finance/customer_detail.html?cusinfid=' + row.CustomerInfoId + '" target="_blank">' + row.CusName + '</a>';
                        var identity = s + formatterIdentity(row);
                        if (row.agentName) {
                            identity = identity + '<br><h5>代理商：' + row.agentName + '</h5>';
                        }
                        return identity;
                    },
                    events: 'operateEvents'
                }, {
                    field: 'Status',
                    title: '<div id="status-list" class="select-wrap"><span class="current-title"><span class="current-select">工单状态</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list"><li class="select-list-item" value="">全部状态</li>@foreach($statusList as $k=>$status) <li class="select-list-item" value="{{$k}}">{{$status}}</li>@endforeach</ul></div>',
                    valign: 'middle',
                    align: 'left',
                    width: '5%',
                    formatter: function (value, row, index) {
                        return statusFormatter(row);
                    }
                }, {
                    title: '数据中心',
                    valign: 'middle',
                    field: 'dataCenter',
                    width: '5%',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var s = '';
                        if(row.dataCenter!=null) s=row.dataCenter;
                        return s;
                    },
                    events: 'operateEvents'
                }, {
                    field: 'ChargeUserId',
                    width: '5%',
                    title: '负责人',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var s = '';
                        if(row.ChargeUserId!='无') s=row.ChargeUserId;
                        return s;
                    }
                }, {
                    field: 'chargeGroup',
                    width: '4%',
                    title: '<div id="charge-list" class="select-wrap"><span class="current-title"><span class="current-select">群组</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list"><li class="select-list-item" value="">群组</li>@foreach($chargeList as $k=>$charge) <li class="select-list-item" value="{{$charge->Code}}">{{$charge->Means}}</li>@endforeach<li class="select-list-item" value="other">其他</li></ul></div>',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var s = '';
                        if(row.chargeGroup!=null)
                            s=row.chargeGroup;
                        return s;
                    }
                },   {
                    field: 'priority',
                    width: '4%',
                    title: '<div id="priority-list" class="select-wrap"><span class="current-title"><span class="current-select">优先级</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list"><li class="select-list-item" value="">优先级</li>@foreach($priorityList as $k) <li class="select-list-item" value="{{$k}}">{{$k}}</li>@endforeach</ul></div>',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var s = '';
                        if(row.priority!=null) s=row.priority;
                        return s;
                    }
                },{
                    field: 'FirstReplyTs',
                    width: '10%',
                    title: '<div id="response-list" class="select-wrap"><span class="current-title"><span class="current-select">响应时长</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list"><li class="select-list-item" value="">响应时长</li>@foreach($responseTimeList as $k=>$item) <li class="select-list-item" value="{{$k}}">{{$item}}</li>@endforeach</ul></div>',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var s = '';
                        if(row.FirstReplyTs!=null) s=row.FirstReplyTs;
                        return s;
                    }
                },{
                    field: 'ProcessTs',
                    width: '10%',
                    title: '<div id="process-list" class="select-wrap"><span class="current-title"><span class="current-select">处理时长</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list"><li class="select-list-item" value="">处理时长</li>@foreach($processList as $k=>$item) <li class="select-list-item" value="{{$k}}">{{$item}}</li>@endforeach</ul></div>',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var s = '';
                        if(row.ProcessTs!=null) s=row.ProcessTs;
                        return s;
                    }
                },{
                    field: 'Source',
                    width: '10%',
                    title: '<div id="source-list" class="select-wrap"><span class="current-title"><span class="current-select">工单来源</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list"><li class="select-list-item" value="">工单来源</li>@foreach($supSourceList as $k=>$item) <li class="select-list-item" value="{{$item->Code}}">{{$item->Means}}</li>@endforeach</ul></div>',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var s = '';
                        if(row.Source!='无') s=row.Source;
                        return s;
                    }
                },{
                    field: 'ClassInficationOne',
                    width: '10%',
                    title: '<div id="class3-list" class="select-wrap"><span class="current-title"><span class="current-select">三级分类</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list"><li class="select-list-item" value="">三级分类</li>@foreach($class3List as $k=>$item) <li class="select-list-item" value="{{$item->Code}}">{{$item->Means}}</li>@endforeach</ul></div>',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var s = '';
                        if(row.ClassInficationOne!='无') s=row.ClassInficationOne;
                        return s;
                    }
                }, {
                    field: 'Evaluation',
                    width: '8%',
                    title: '<div id="evaluate-list" class="select-wrap"><span class="current-title"><span class="current-select">服务评价</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list"><li class="select-list-item" value="">服务评价</li>@foreach($evaluationList as $k=>$item) <li class="select-list-item" value="{{$k}}">{{$item}}</li>@endforeach</ul></div>',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var s = '';
                        if(row.Evaluation!='无') s=row.Evaluation;
                        return s;
                    }
                }, {
                    field: 'Ts',
                    width: '10%',
                    title: '创建时间',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var s = row.Ts;
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
                    'month': $("#month").val(),
                    'year': $('#year').val(),
                    'timeOut': $('#timeOut').val(),
                    'priority': $('#priority').val(),
                    'Status': $('#Status').val(),
                    'evaluate': $('#evaluate').val(),
                    'source': $('#source').val(),
                    'class3': $('#class3').val(),
                    'responseTime': $('#responseTime').val(),
                    'chargeGroup': $('#chargeGroup').val(),
                    'processTime': $('#processTime').val(),
                    'supportSource': $('#supportSource').val(),
                    'successNum': $('#successNum').val(),
                }
            }
        });

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

    $(function () {
        $(".table-responsive").css({"overflow": "inherit"});
        showToolTip($("._handle_tip"));
        showTableToolTip("._jobState");
    });
    function doNewSearch(data, values) {//检索筛选

        if (data == "status-list") {
            $("#Status").val(values);
        }
        if (data == "charge-list") {
            $("#chargeGroup").val(values);
        }
        if (data == "response-list") {
            $("#responseTime").val(values);
        }
        if (data == "process-list") {
            $("#processTime").val(values);
        }
        if (data == "source-list") {
            $("#supportSource").val(values);
        }
        if (data == "class3-list") {
            $("#class3").val(values);
        }
        if (data == "evaluate-list") {
            $("#evaluate").val(values);
        }
        $('#kzTable').bootstrapTable('refresh', {
            query: {
                'chargeGroup': $("#chargeGroup").val(),
                'month': $("#month").val(),
                'year': $("#year").val(),
                'priority': $('#priority').val(),
                'Status': $('#Status').val(),
                'dataCenter': $('#dataCenter').val(),
                'evaluate': $('#evaluate').val(),
                'supportSource': $('#supportSource').val(),
                'successNum': $('#successNum').val(),
                'pageNumber': 1
            }
        });
    }
    initTable();
    $(function () {
        pullDownChoice("charge-list", function (param) {
            doNewSearch("charge-list", param);
        });
        pullDownChoice("status-list", function (param) {
            doNewSearch("status-list", param);
        });
        pullDownChoice("priority-list", function (param) {
            doNewSearch("priority-list", param);
        });
        pullDownChoice("response-list", function (param) {
            doNewSearch("response-list", param);
        });
        pullDownChoice("process-list", function (param) {
            doNewSearch("process-list", param);
        });
        pullDownChoice("source-list", function (param) {
            doNewSearch("source-list", param);
        });
        pullDownChoice("class3-list", function (param) {
            doNewSearch("class3-list", param);
        });
        pullDownChoice("evaluate-list", function (param) {
            doNewSearch("evaluate-list", param);
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