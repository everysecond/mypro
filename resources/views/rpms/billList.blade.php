<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="_token" content="{{ csrf_token() }}"/>
    <title>安畅网络 RPMS系统——账单管理</title>

    <!--[if lt IE 9]>
    <meta http-equiv="refresh" content="0;ie.html"/>
    <![endif]-->

    <link rel="shortcut icon" href="favicon.ico">
    <link href="/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/css/font-awesome.min.css?v=4.4.0" rel="stylesheet">
    <link href="/css/style.css?v=4.1.0" rel="stylesheet">
    <link rel="stylesheet" href="/css/plugins/bootstrap-table/bootstrap-table.css">
    <link rel="stylesheet" href="/css/job_list.css">
    <link rel="stylesheet" href="/css/hplusnew.css?{{\Itsm\Http\Helper\ThirdCallHelper::getTime()}}">
    <style>
        body {
            font-size: 12px !important;
        }

        .layui-layer-tips i.layui-layer-TipsB, .layui-layer-tips i.layui-layer-TipsT {
            border-right-color: #2F323A !important;
        }

        .layui-layer.layui-anim.layui-layer-tips {
            top: 48px !important;
        }

        .layui-layer-tips .layui-layer-content {
            background-color: #2F323A !important;
            color: #FFFFFF !important;
            padding: 0 13px !important;
            font-size: 12px !important;
            font-weight: 400;
        }

        .dropdown-menu {
            top: 31px;
            min-width: 125px !important;
        }

        .open > .dropdown-menu.selfoperate {
            right: 0;
            left: auto;
        }

        .fixed-table-body{overflow: inherit;}
        .table-responsive{overflow: inherit;}
    </style>
</head>
<body style="background-color: whitesmoke;padding: 10px">
<div class=" wrapper-content" style="background-color: white;">
    <div class="row">
        <div class="col-sm-12" style="margin-left: 15px;width:98%">
            <div class="ibox">
                <div class="res-list-top">
                    <span>请选择:</span>
                    <a class="btn btn-warning btn4 btnwhite ml4 btnTitle" data-status="expired">已过期</a>
                    <a class="btn btn-warning btn4 btnwhite btnTitle" data-status="current">本月账期</a>
                    <a class="btn btn-warning btn4 btnred ml4 btnTitle" data-status="all">全部账单</a>
                    <br/>
                    <a class="btn btn-warning btn6 btnwhitefr ml4" onclick="newBill('new')">
                        <span class="font14"></span>
                        <i class="fa fa-plus mr4"></i>新增账单
                    </a>
                    <a class="btn btn-warning refreshbtn btnwhitefr ml10" onclick="refreshTab()">
                        <i class="fa fa-refresh"></i>
                    </a>
                    <div class="btn-group ml10" style="border:1px solid #E4E4E4;padding:0;">
                        <button data-toggle="dropdown" class="btn-warning btn2 btnwhitefr dropdown-toggle" style="" aria-expanded="false">
                            <i class="fa fa-ellipsis-h mr10"></i>更多操作
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" style="">
                            <li><a class="batchOperate" data-type="audit"><i class="fa fa-check-square mr8 font13"></i>批量审核</a></li>
                            <li><a class="batchOperate" data-type="application"><i class="fa fa-dot-circle-o mr8 font13"></i>批量申请</a></li>
                            <li><a class="batchOperate" data-type="payment"><i class="fa fa-paypal mr8 font13"></i>批量支付</a></li>
                            <li><a class="batchOperate" data-type="delete"><i class="fa fa-remove mr8 font14"></i>批量删除</a>
                            </li>
                        </ul>
                    </div>
                    <div class="btn-group ml10" style="border:1px solid #E4E4E4;padding:0;">
                        <i class="fa fa-search" style="margin: 0 10px;"></i>
                        <input type="text" class="form-control search-box" id="searchInfo" style="width: 300px;border:0;height:26px;display:inline-block;padding: 3px 0;" placeholder="请输入账单编号或合同编号或供应商">
                    </div>
                    <button id="searchAll" class="btn btn-warning  bigbtn4 btnwhitefr ml10" onclick="doNewSearch()">
                        查询
                    </button>
                    <input type="hidden" id="billStatus" value="all">
                    <input type="hidden" id="statusType" value="all">
                    <input type="hidden" id="billType" value="all">
                </div>
            </div>
            <div style="margin-top:0;">
                <div class="tab-content">
                    <div id="tab-1" class="tab-pane active">
                        <div class="full-height-scroll">
                            <div class="table-responsive" style="background-color: white">
                                <table id="billTable" class="table-no-bordered"
                                       style="text-align: center;color:#6b7d86" bgcolor="#FFFFFF"
                                       cellpadding="0"
                                       cellspacing="0" width="100%"
                                       data-pagination="true"
                                       data-show-export="true"
                                       data-page-size="10"
                                       data-id-field="Id"
                                       data-pagination-detail-h-align="right"
                                       data-page-list="[10, 20, 50, 100, ALL]"
                                       data-show-footer="false"
                                       data-side-pagination="server"
                                       data-url="/rpms/resourceBill/getBillList"
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
<script language="JavaScript" src="/js/enquirylist.js?{{\Itsm\Http\Helper\ThirdCallHelper::getTime()}}"></script>
<script src="/js/plugins/bootstrap-table/bootstrap-table.js"></script>
<!-- put your locale files after bootstrap-table.js -->
<script src="/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>
<script src="/js/plugins/layer/laydate/laydate.js"></script>


<script>
    $(function () {
        $(".table-responsive").css({"overflow": "inherit"});
    });
    var url = "{{env('JOB_URL')}}";

    var $billTable = $('#billTable'),
            selections = [];

    function initTable() {
        $billTable.bootstrapTable({
            striped: false,
            columns: [
                [{
                    field: 'state',
                    checkbox: true,
                    align: 'middle',
                    valign: 'middle',
                    width: '2%'
                },
                    {
                        title: 'ID',
                        valign: 'middle',
                        align: 'left',
                        width: '3%',
                        field: 'id',
                        formatter: function (value, row, index) {
                            return '<a class="fontred" onclick="billInfo(' + row.id + ')"><i class="fa fa-info mr5"></i>' + row.id + '</a>';
                        }
                    },
                    {
                        title: '账单编号',
                        valign: 'middle',
                        width: '11%',
                        align: 'left',
                        formatter: function (value, row, index) {
                            if(row.payStatus != "success"){
                                return '<a class="fontred" onclick="newBill(' + row.id + ')"><i class="fa fa-edit mr5"></i>' + row.billNo + '</a>';
                            }else{
                                return row.billNo;
                            }
                        }
                    },
                    {
                        title: '供应商',
                        valign: 'middle',
                        width: '14%',
                        field: 'supplierId',
                        align: 'left'
                    },
                    {
                        title: '账单金额',
                        valign: 'middle',
                        width: '5%',
                        field: 'billAmount',
                        align: 'left',
                        formatter: function (value, row, index) {
                           return value.toFixed(2)
                        }
                    },
                    {
                        title: '<div id="todo-type-list" class="select-wrap"><span class="current-title">' +
                        '<span class="current-select">账单状态</span><i class="fa fa-caret-down ml5"></i></span>' +
                        '<ul class="select-list"><li class="select-list-item" value="all">全部</li>' +
                        '<li class="select-list-item" value="new">未支付</li>' +
                        '<li class="select-list-item" value="success">已支付</li>' +
                        '</ul></div>',
                        valign: 'middle',
                        align: 'left',
                        width: '5%',
                        field: 'payStatus',
                        formatter: function (value, row, index) {
                            if(row.payStatus == "new") {
                                return "<span style='color:#F8AC59'>未支付</span>";
                            }else if(row.payStatus == "audit") {
                                return "审核中";
                            }else if(row.payStatus == "application") {
                                return "申请中";
                            }else if(row.payStatus == "success") {
                                return "<span style='color:#008000'>已支付</span>";
                            }else{
                                return "<span style='color:#FF0000'>已过期</span>"+"("+row.payStatus+"天)";
                            }
                        }
                    },
                    {
                        title: '开始结束日期',
                        valign: 'middle',
                        width: '6%',
                        field: 'billStart',
                        align: 'left',
                        formatter: function (value, row, index) {
                            return (row.billStart != null ? row.billStart : '') + "<br>" + (row.billEnd != null ? row.billEnd:'');
                        }
                    },
                    {
                        title: '账单日期',
                        valign: 'middle',
                        width: '6%',
                        field: 'billExpire',
                        align: 'left'
                    },
                    {
                        title: '创建人/创建时间',
                        valign: 'middle',
                        width: '6%',
                        field: 'createdAt',
                        align: 'left',
                        formatter: function (value, row, index) {
                            return  row.createdBy + "<br>" + row.createdAt ;
                        }
                    }
                ]
            ],
            queryParamsType: "undefined",
            queryParams: function queryParams(params) {
                return {
                    pageSize: params.pageSize,
                    pageNumber: params.pageNumber,
                    statusType: $('#statusType').val(),
                    billStatus: $('#billStatus').val(),
                    searchInfo: $('#searchInfo').val()
                }
            }
        });

        var custips;
        //bootstrap监听事件
        window.operateEvents = {
            'mouseover .etitle': function (e, value, row, index) {
                if (row.title) {
                    custips = layer.tips(row.title, this, {
                        time: 0,
                        tips: [1, '#999999'],
                        maxWidth: 400
                    });
                }
            },
            'mouseleave .etitle': function (e, value, row, index) {
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


    initTable();

    //切换资源类型标题
    $(".btnTitle").click(function () {
        $(".btnTitle").removeClass("btnred btnwhite").addClass("btnwhite");
        $(this).removeClass("btnwhite").addClass("btnred");
        $("#statusType").val($(this).data("status"));
        $('#billTable').bootstrapTable('refresh', {
            query: {
                'pageNumber': 1
            }
        });
    });

    //刷新btn
    $(".refreshbtn").mouseover(function () {
        layer.tips('刷新', this, {time: 2000, tips: 1});
    }).mouseout(function () {
        layer.closeAll();
    });

    //本页刷新
    function refreshTab() {
        window.location.reload()
    }

    //搜素框任意键盘动作触发搜索
    $('#searchInfo').bind('keypress', function (event) {
        var searchAll = document.getElementById("searchAll");
        if (event.keyCode == "13") {
            searchAll.click();
        }
    });

    //新建或编辑账单
    function newBill(type) {
        var title = type == "new" ? "新建账单" : "编辑账单";
        layer.open({
            type: 2,
            title: title,
            area: ['760px', '580px'],
            shade: 0.2,
            content: ['/rpms/resourceBill/newBill?type=' + type]
        });
    }

    function billInfo(type) {
        layer.open({
            type: 2,
            title: '账单详情',
            area: ['760px', '580px'],
            shade: 0.2,
            content: ['/rpms/resourceBill/billInfo?type=' + type]
        });
    }

    //页面筛选功能fun
    function doNewSearch(data, values) {
        if (data == "todo-type-list") {
            $("#billStatus").val(values);
        }
        $('#billTable').bootstrapTable('refresh', {
            query: {
                'pageNumber': 1
            }
        });
    }

    //自定义下拉框触发机制
    $(function () {
        pullDownChoice("todo-state-list", function (param) {
            doNewSearch("todo-state-list", param);
        });
        pullDownChoice("todo-type-list", function (param) {
            doNewSearch("todo-type-list", param);
        });
    })

    //批量支付或批量删除
    $(".batchOperate").click(function () {
        var batchType = $(this).data("type");
        var str = "";
        if(batchType == "audit"){
            str = "审核";
        }else if(batchType == "application"){
            str = "申请";
        }else if(batchType == "payment"){
            str = "支付";
        }else if(batchType == "delete"){
            str = "删除";
        }else {
            layer.msg('未知操作类型,请刷新后重试！', {icon: 2});
        }
        var selected = $('#billTable').bootstrapTable('getSelections');
        var sIds = [];
        if (selected.length < 1) {
            layer.msg('请选择要操作的账单！', {icon: 2});
            return false;
        }

        for (var key in selected) {
            sIds[key] = selected[key].id;
        }

        var tips='确定要批量'+str+'账单吗?';
        layer.confirm(tips, {title: "提示", btn: ['确定', '取消']},
            function () {
                $.ajax({//查询是否包含上级类型
                    type: "POST",
                    data: {'billIds': sIds,'optType':batchType},
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    },
                    url: "/rpms/resourceBill/batchOperate",
                    success: function (data) {
                        if (!data.status) {
                            layer.msg(data.msg, {time: 3000});
                        } else {
                            layer.msg('批量操作成功！', {icon: 1, time: 2000});
                            $('#billTable').bootstrapTable('refresh');
                        }
                    }
                });
            }
        );
    });

</script>
</body>
</html>