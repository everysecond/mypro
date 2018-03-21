<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="_token" content="{{ csrf_token() }}"/>
    <title>安畅网络 RPMS系统——资源产品管理</title>

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
    </style>
</head>
<body style="background-color: whitesmoke;padding: 10px">
<div class=" wrapper-content" style="background-color: white;">
    <div class="row">
        <div class="col-sm-12" style="margin-left: 15px;width:98%">
            <div class="ibox">
                <div class="res-list-top">
                    <span>请选择:</span>
                    <a class="btn btn-warning btn4 btnred btnTitle" data-status="commonType">常用资源产品</a>
                    <a class="btn btn-warning btn4 btnwhite ml4 btnTitle" data-status="overTime">过期资源产品</a>
                    <a class="btn btn-warning btn4 btnwhite ml4 btnTitle" data-status="all">全部资源产品</a>
                    <br/>
                    <a class="btn btn-warning btn6 btnwhitefr ml4" onclick="newProd('new')">
                        <span class="font14"></span>
                        <i class="fa fa-plus mr4"></i> 新增资源产品</a>
                    <a class="btn btn-warning refreshbtn btnwhitefr ml10" onclick="refreshTab()"><i
                                class="fa fa-refresh"></i></a>
                    <div class="btn-group ml10" style="border:1px solid #E4E4E4;padding:0;">
                        <button data-toggle="dropdown" class="btn-warning btn2 btnwhitefr dropdown-toggle"
                                style="" aria-expanded="false"><i class="fa fa-ellipsis-h mr10"></i>更多操作 <span
                                    class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" style="">
                            <li><a class="batchOperate" data-type="up"><i class="fa fa-dot-circle-o mr8 font13"></i>批量启用</a>
                            </li>
                            <li><a class="batchOperate" data-type="down"><i class="fa fa-ban mr8 font13"></i>批量停用</a>
                            <li><a class="batchOperate" data-type="delete"><i class="fa fa-remove mr8 font14"></i>批量删除</a>
                            </li>
                        </ul>
                    </div>
                    <div class="btn-group ml10" style="border:1px solid #E4E4E4;padding:0;">
                        <i class="fa fa-search" style="margin: 0 10px;"></i>
                        <input type="text" class="form-control search-box" id="searchInfo"
                               style="width: 300px;border:0;height:26px;display:inline-block;padding: 3px 0;"
                               placeholder="请输入资源产品名称或资源类型、子类型编码">
                    </div>
                    <button id="searchAll" class="btn btn-warning  bigbtn4 btnwhitefr ml10" onclick="doNewSearch()">
                        查询
                    </button>
                    <input type="hidden" id="status">
                    <input type="hidden" id="statusType" value="commonType">
                </div>
            </div>
            <div style="margin-top:0;">
                <div class="tab-content">
                    <div id="tab-1" class="tab-pane active">
                        <div class="full-height-scroll">
                            <div class="table-responsive" style="background-color: white">
                                <table id="prodTable" class="table-no-bordered"
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
                                       data-url="/rpms/resourceProd/getProdList"
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

    var $prodTable = $('#prodTable'),
            selections = [];

    function initTable() {
        $prodTable.bootstrapTable({
            pageSize: 10,
            striped: false,
            columns: [
                [{
                    field: 'state',
                    checkbox: true,
                    align: 'middle',
                    valign: 'middle',
                    width: '4%'
                },
                    {
                        title: '资源产品名称',
                        valign: 'middle',
                        align: 'left',
                        width: '14%',
                        formatter: function (value, row, index) {
                            return '<a class="fontred" onclick="newProd(' + row.id + ')"><i class="fa fa-edit mr5"></i>' + row.prodName + '</a>';
                        }
                    },
                    {
                        title: '资源类型',
                        valign: 'middle',
                        width: '12%',
                        field: 'typeName',
                        align: 'left'
                    },
                    {
                        title: '资源子类型',
                        valign: 'middle',
                        width: '12%',
                        field: 'sonTypeName',
                        align: 'left'
                    },
                    {
                        field: 'status',
                        width: '6%',
                        title: '<div id="todo-state-list" class="select-wrap"><span class="current-title">' +
                        '<span class="current-select">状态</span><i class="fa fa-caret-down ml5"></i></span>' +
                        '<ul class="select-list"><li class="select-list-item" value="">全部</li>' +
                        '<li class="select-list-item" value="0">在使用</li>' +
                        '<li class="select-list-item" value="1">已停用</li>' +
                        '<li class="select-list-item" value="2">已使用</li>' +
                        '</ul></div>',
                        valign: 'middle',
                        align: 'left',
                        formatter: function (value, row, index) {
                            var str = row.status == 0 ? "在使用" : (row.status == 1 ? "已停用" : "已使用");
                            var spanClass = row.status == 0 ? "statusZero" : (row.status == 1 ? "statusOne" : "statusTwo");
                            return '<span class="' + spanClass + '">' + str + '</span>';

                        }
                    },
                    {
                        title: '单价',
                        valign: 'middle',
                        width: '7%',
                        field: 'unitPrice',
                        align: 'left'
                    },
                    {
                        title: '首次费用',
                        valign: 'middle',
                        width: '7%',
                        field: 'onePrice',
                        align: 'left'
                    },
                    {
                        field: 'createdAt',
                        width: '12%',
                        title: '创建时间',
                        valign: 'middle',
                        align: 'left'
                    },
                    {
                        field: 'createdBy',
                        width: '6%',
                        title: '创建人',
                        valign: 'middle',
                        align: 'left'
                    }
                ]
            ],
            queryParamsType: "undefined",
            queryParams: function queryParams(params) {
                return {
                    pageSize: params.pageSize,
                    pageNumber: params.pageNumber,
                    status: $('#status').val(),
                    statusType: $('#statusType').val(),
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
        pullDownChoice("todo-state-list", function (param) {
            $("#status").val("");
        },  "");
        $('#prodTable').bootstrapTable('refresh', {
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

    //新建或编辑资源类型
    function newProd(type) {
        var title = type == "new" ? "新建资源产品" : "编辑资源产品";
        layer.open({
            type: 2,
            title: title,
            area: ['630px', '420px'],
            shade: 0.2,
            content: ['/rpms/resourceProd/newProd?type=' + type]
        });
    }

    //页面筛选功能fun
    function doNewSearch(data, values) {
        if (data == "todo-state-list") {
            $("#status").val(values);
        }
        $('#prodTable').bootstrapTable('refresh', {
            query: {
                'pageNumber': 1
            }
        });
    }

    //批量启用资源类型
    $(".batchOperate").click(function () {
        var batchType = $(this).data("type");
        var str = (batchType == "up"?"启用":(batchType == "down"?"停用":"删除"));
        var selected = $('#prodTable').bootstrapTable('getSelections');
        var sIds = [];
        if (selected.length < 1) {
            layer.msg('请选择要操作的资源产品！', {icon: 2});
            return false;
        }
        layer.confirm('确定要批量'+str+'资源产品吗?', {title: "提示", btn: ['确定', '取消']},
                function () {
                    for (var key in selected) {
                        sIds[key] = {id: selected[key].id};
                    }
                    $.ajax({
                        type: "POST",
                        data: {'supIds': sIds,'batchType':batchType},
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                        },
                        url: "/rpms/resourceProd/batchOperate",
                        success: function (data) {
                            if (data.status == 'success') {
                                layer.msg('批量操作成功！', {icon: 1,time:2000});
                                $('#prodTable').bootstrapTable('refresh');
                            }
                        }
                    })
                })
    });

    //自定义下拉框触发机制
    $(function () {
        pullDownChoice("todo-state-list", function (param) {
            doNewSearch("todo-state-list", param);
        });
        pullDownChoice("todo-type-list", function (param) {
            doNewSearch("todo-type-list", param);
        });
    })
</script>
</body>
</html>