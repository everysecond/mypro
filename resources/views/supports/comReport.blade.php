<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>安畅网络 工单系统——工单业务报表</title>

    <!--[if lt IE 9]>
    <meta http-equiv="refresh" content="0;ie.html"/>
    <![endif]-->

    <link rel="shortcut icon" href="favicon.ico">
    <link href="/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/css/font-awesome.min.css?v=4.4.0" rel="stylesheet">
    <link href="/css/style.css?v=4.1.0" rel="stylesheet">
    <link rel="stylesheet" href="/css/plugins/bootstrap-table/bootstrap-table.css">
    <link rel="stylesheet" href="/css/report_list.css">
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

        .form-control{
            display: inline-block;
            vertical-align: middle;
            width: 90px;
            margin: 10px 10px;
        }
    </style>
</head>
<body class="gray-bg">
<div class=" wrapper-content" style="background-color: whitesmoke;font-family: '微软雅黑'">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="report-list-title">
                        　◆ 工单管理　>　工单业务统计表
                    &nbsp;&nbsp;&nbsp;<a href="evaReport">工单处理满意度</a>
                    &nbsp;&nbsp;&nbsp;<a style="text-decoration: underline" href="comReport">工单完成超时比率</a>
                    &nbsp;&nbsp;&nbsp;<a href="repReport">工单响应超时比率</a>
                    &nbsp;&nbsp;&nbsp;<a href="sucReport">工单成功解决率</a>
                    &nbsp;&nbsp;&nbsp;<a href="supportKZList?year=2016&month=12">工单快照数据查询</a>
                </div>

                <div class="report-list-content">
                    快速查询
                    <input type="hidden" name="workOrder" value="satisfactioin">
                    &nbsp;&nbsp;&nbsp;<span> 工单创建年度：</span>
                    <select class="form-control" onchange="doNewSearch(this,'')" name="year" id="year" style="display: inline-block">
                        @foreach($yearList as $y)
                            <option value="{{$y['y']}}">{{$y['y']}}</option>
                        @endforeach
                    </select>
                    &nbsp;&nbsp;&nbsp;<span> 工单负责人群组：</span>
                    <select class="form-control" onchange="doNewSearch(this,'')" name="charge" id="charge" style="display: inline-block">
                        <option value="">全部</option>
                        @foreach($chargeGroup as $charge)
                            <option value="{{$charge->Code}}">{{$charge->Means}}</option>
                        @endforeach
                        <option value="other">其他</option>
                    </select>
                    &nbsp;&nbsp;&nbsp;<span> 工单优先级：</span>
                    <select class="form-control" onchange="doNewSearch(this,'')" name="priority" id="priority" style="display: inline-block">
                        <option value="">请选择</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                    &nbsp;&nbsp;&nbsp;<span> 工单类型：</span>
                    <select class="form-control" onchange="doNewSearch(this,'')" name="supportType" id="supportType" style="width: 150px">
                        <option value="">请选择</option>
                        @foreach($type as $k)
                            <option value="{{$k->Code}}">{{$k->Means}}</option>
                        @endforeach
                    </select>
                    &nbsp;&nbsp;&nbsp;<span> 工单来源：</span>
                    <select class="form-control" onchange="doNewSearch(this,'')" name="supportSource" id="supportSource" style="width: 110px">
                        <option value="">请选择</option>
                        @foreach($source as $k)
                            <option value="{{$k->Code}}">{{$k->Means}}</option>
                        @endforeach
                    </select>
                    <button class="submit-btn" id="report2">生成统计报表</button>
                </div>
            </div>
            <div style="margin-top: 5px;">
                <div class="tab-content" style="padding: 5px 5px 5px 5px;background-color: white">
                    <div class="supTable" id="supT2">
                        <table class="table table-striped table-hover dataTables-example dataTable">
                            <thead>
                            <tr>
                                <td class="td1" style="text-align:center">&nbsp;&nbsp;序号</td>
                                <td class="td1" style="text-align:center">&nbsp;&nbsp;分组</td>
                                <td class="td1" style="text-align:center">&nbsp;&nbsp;1月</td>
                                <td class="td1" style="text-align:center">&nbsp;&nbsp;2月</td>
                                <td class="td1" style="text-align:center">&nbsp;&nbsp;3月</td>
                                <td class="td1" style="text-align:center">&nbsp;&nbsp;4月</td>
                                <td class="td1" style="text-align:center">&nbsp;&nbsp;5月</td>
                                <td class="td1" style="text-align:center">&nbsp;&nbsp;6月</td>
                                <td class="td1" style="text-align:center">&nbsp;&nbsp;7月</td>
                                <td class="td1" style="text-align:center">&nbsp;&nbsp;8月</td>
                                <td class="td1" style="text-align:center">&nbsp;&nbsp;9月</td>
                                <td class="td1" style="text-align:center">&nbsp;&nbsp;10月</td>
                                <td class="td1" style="text-align:center">&nbsp;&nbsp;11月</td>
                                <td class="td1" style="text-align:center">&nbsp;&nbsp;12月</td>
                            </tr>
                            </thead>
                            <tbody id="comTable">
                            </tbody>
                        </table>
                        <div style="padding: 10px;border-top: 4px solid rgba(74, 74, 74, 0.18);">
                            <span style="font-weight:bold;">   解释说明 </span>
                            <br>
                            <span style="color:gray;font-size:10px; border-left-width: 120px; margin-left: 60px;">超时工单数量都是指按条件（工单负责人群组，工单优先级等）过滤后得到的数量.</span>
                            <br>
                            <span style="color:gray;font-size:10px; border-left-width: 120px; margin-left: 60px;">工单总数是指按条件（工单负责人群组，工单优先级等）过滤后得到的数量.</span>
                            <br>
                            <span style="color:gray;font-size:10px; border-left-width: 120px; margin-left: 60px;">工单优先级：全部　-&gt;　完成超时率 = （1+2+3）级超时完成的工单数量之和/工单总数 × 100 %； 工单优先级：1　-&gt;　完成超时率 = 1级超时完成的工单数量/工单总数 × 100 %....</span>
                            <br>
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
<script type="text/javascript" src="/js/report_list.js"></script>
<script type="text/javascript" src="/js/job_list.js"></script>
<script src="/js/plugins/bootstrap-table/bootstrap-table.js"></script>
<!-- put your locale files after bootstrap-table.js -->
<script src="/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>


<script>
    var url = "{{env('JOB_URL2')}}";
    layer.config({
        extend: 'extend/layer.ext.js'
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

    function getHeight() {
        return $(window).height() - $('h1').outerHeight(true);
    }

    function doNewSearch(data, values) {
        $('#comTable').bootstrapTable('refresh', {
            query: {
                'year': $('#year').val(),
                'charge': $('#charge').val(),
                'priority': $('#priority').val(),
                'supportType': $('#supportType').val(),
                'supportSource': $('#supportSource').val(),
            }
        });
    }
</script>
</body>

</html>