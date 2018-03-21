<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="_token" content="{{ csrf_token() }}"/>
    <title>安畅网络 RPMS系统——合同管理</title>

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
                    <a class="btn btn-warning btn4 btnwhite ml4 btnTitle" data-status="toDo">待执行合同</a>
                    <a class="btn btn-warning btn4 btnwhite btnTitle" data-status="doing">执行中合同</a>
                    <a class="btn btn-warning btn4 btnwhite ml4 btnTitle" data-status="toStop">终止待审核</a>
                    <a class="btn btn-warning btn4 btnred ml4 btnTitle" data-status="all">全部合同</a>
                    <br/>
                    <a class="btn btn-warning btn6 btnwhitefr ml4" onclick="newContract('new')">
                        <span class="font14"></span>
                        <i class="fa fa-plus mr4"></i>新增合同
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
                            {{--<li><a class="batchOperate" data-type="up"><i class="fa fa-dot-circle-o mr8 font13"></i>生成账单</a></li>--}}
                            <li><a class="batchOperate" data-type="delete"><i class="fa fa-remove mr8 font14"></i>批量删除</a>
                            </li>
                        </ul>
                    </div>
                    <div class="btn-group ml10" style="border:1px solid #E4E4E4;padding:0;">
                        <i class="fa fa-search" style="margin: 0 10px;"></i>
                        <input type="text" class="form-control search-box" id="searchInfo" style="width: 300px;border:0;height:26px;display:inline-block;padding: 3px 0;" placeholder="请输入合同编号">
                    </div>
                    <button id="searchAll" class="btn btn-warning  bigbtn4 btnwhitefr ml10" onclick="doNewSearch()">
                        查询
                    </button>
                    <input type="hidden" id="statusType" value="all">
                    <input type="hidden" id="contractStatus" value="all">
                    <input type="hidden" id="contractType" value="">
                    <div style="float:right;margin-top: 12px;">
                        <label class="btn btn-warning btn6 btnred ml3" for="uploadExcel">上传合同Excel</label>
                        <input type="file"
                               style="position:absolute;clip:rect(0 0 0 0);"
                               multiple="multiple" id="uploadExcel" name="uploadExcel"/>
                    </div>
                </div>
            </div>
            <div style="margin-top:0;">
                <div class="tab-content">
                    <div id="tab-1" class="tab-pane active">
                        <div class="full-height-scroll">
                            <div class="table-responsive" style="background-color: white">
                                <table id="contractTable" class="table-no-bordered"
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
                                       data-url="/rpms/resourceContract/getContractList"
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

    //上传合同Excel文件
    $("#uploadExcel").on("change", function () {
                var oFiles = document.querySelector("#uploadExcel").files;
                //获取文件对象，files是文件选取控件的属性，存储的是文件选取控件选取的文件对象，类型是一个数组
                var fileObj = oFiles[0];
                //创建formdata对象，formData用来存储表单的数据，表单数据时以键值对形式存储的。
                var formData = new FormData();
                formData.append('excelFile', fileObj);
                var ajax = new XMLHttpRequest();
                //发送POST请求
                ajax.open("POST", "/kindeditor/uploadExcel", true);
                ajax.send(formData);
                ajax.onreadystatechange = function () {
                    if (ajax.readyState == 4) {
                        if (ajax.status >= 200 && ajax.status < 300 || ajax.status == 304) {
                            var obj = JSON.parse(ajax.responseText);
                            if (obj.error == 0) {
                                layer.msg(obj.message,function(){
                                    window.location.reload();
                                });
                            } else {
                                layer.msg(obj.message,function(){
                                    window.location.reload();
                                });
                            }
                        }
                     }
                }
            }
    );

    var url = "{{env('JOB_URL')}}";

    var $contractTable = $('#contractTable'),
            selections = [];

    function initTable() {
        $contractTable.bootstrapTable({
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
                        field: 'id'
                    },
                    {
                        title: '合同编号',
                        valign: 'middle',
                        width: '12%',
                        align: 'left',
                        formatter: function (value, row, index) {
                            if(row.status != "toStop" && row.status != "end" && row.billCounts ==0){
                                return '<a class="fontred" onclick="newContract(' + row.id + ')"><i class="fa fa-edit mr5"></i>' + row.contractNo + '</a>';
                            }else{
                                return row.contractNo;
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
                        title: '<div id="todo-state-list" class="select-wrap"><span class="current-title">' +
                        '<span class="current-select">合同类型</span><i class="fa fa-caret-down ml5"></i></span>' +
                        '<ul class="select-list"><li class="select-list-item" value="all">全部</li>' +
                        '<li class="select-list-item" value="add">新增</li>' +
                        '<li class="select-list-item" value="renewal">续约</li>' +
                        '<li class="select-list-item" value="changeAdd">变更新增</li>' +
                        '<li class="select-list-item" value="changeRenewal">变更续约</li>' +
                        '</ul></div>',
                        valign: 'middle',
                        align: 'left',
                        width: '4%',
                        field: 'contractTypeName'
                    },
                    {
                        title: '<div id="todo-type-list" class="select-wrap"><span class="current-title">' +
                        '<span class="current-select">合同状态</span><i class="fa fa-caret-down ml5"></i></span>' +
                        '<ul class="select-list"><li class="select-list-item" value="all">全部</li>' +
                        '<li class="select-list-item" value="toDo">待执行</li>' +
                        '<li class="select-list-item" value="doing">执行中</li>' +
                        '<li class="select-list-item" value="toStop">终止待审核</li>' +
                        '<li class="select-list-item" value="end">审核已闭单</li>' +
                        '</ul></div>',
                        valign: 'middle',
                        align: 'left',
                        width: '4%',
                        field: 'status',
                        formatter: function (value, row, index) {
                            if(row.status == "toDo")return "待执行";
                            if(row.status == "doing")return "执行中";
                            if(row.status == "toStop")return "终止待审核";
                            if(row.status == "end")return "审核已闭单";
                        }
                    },
                    {
                        title: '起止日期',
                        valign: 'middle',
                        width: '6%',
                        field: 'supplierId',
                        align: 'left',
                        formatter: function (value, row, index) {
                            return (row.startTs != null ? substrTime(row.startTs,10) : '') + "<br>" + (row.endTs != null ? substrTime(row.endTs,10):'');
                        }
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
                    },
                    {
                        title: '操作',
                        field: '',
                        width: '10%',
                        valign: 'middle',
                        align: 'center',
                        formatter:function(value,row,index){
                            var operateStr = "";
                            if(row.status == 'doing'){
                                if(row.billCounts ==0){
                                    operateStr ='<li><a onclick="newContract('+row.id+')"><i class="fa fa-edit mr8 font13"></i>合同修改</a> '
                                }
                                operateStr += '<li><a onclick="changeAdd('+row.id+')"><i class="fa fa-clone mr8 font13"></i>变更合同</a> ' +
                                        '<li><a onclick="stopContract('+row.id+')"><i class="fa fa-ban mr8 font13"></i>终止合同</a> '+
                                        '<li><a onclick="createBill('+row.id+')"><i class="fa fa-edit mr8 font13"></i>下期账单</a> ';
                            }else if(row.status == 'toStop'){
                                operateStr = '<li><a onclick="contractDetail('+row.id+',\'confirm\')"><i class="fa fa-ban mr8 font13"></i>审核待终止</a> ';
                            }else if(row.status == 'toDo'){
                                operateStr = '<li><a onclick="newContract('+row.id+')"><i class="fa fa-edit mr8 font13"></i>合同修改</a> ';
                            }
                            operateStr += '<li><a class="J_menuItem" href="/rpms/resourceContract/contractConfirm/'+ row.id+'?type=detail&contractId='+ row.id+'"><i class="fa fa-file-text-o mr8 font13"></i>合同详情</a> ';
                            return '<div class="btn-group" style="border:1px solid white;padding:0;"> ' +
                                    '<button data-toggle="dropdown" class="btn-warning btn2 btnwhitefr dropdown-toggle" style="" aria-expanded="false"> ' +
                                    '相关操作<span class="caret"></span></button> ' +
                                    '<ul class="dropdown-menu selfoperate" style="">'+operateStr+'</ul> </div>';
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
                    status: $('#contractStatus').val(),
                    contractType: $('#contractType').val(),
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
        pullDownChoice("todo-type-list", function (param) {
            $("#contractStatus").val($(this).data("status"));
        }, $(this).data("status"));
        pullDownChoice("todo-state-list", function (param) {
            $("#contractType").val("all");
        }, "all");
        $('#contractTable').bootstrapTable('refresh', {
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

    //新建或编辑合同
    function newContract(type) {
        var title = type == "new" ? "新建合同" : "编辑合同";
        layer.open({
            type: 2,
            title: title,
            area: ['760px', '580px'],
            shade: 0.2,
            content: ['/rpms/resourceContract/newContract?type=' + type]
        });
    }

    //新建或编辑合同
    function changeAdd(type) {
        layer.open({
            type: 2,
            title: "变更合同",
            area: ['760px', '580px'],
            shade: 0.2,
            content: ['/rpms/resourceContract/changeContract?type=' + type]
        });
    }
    //审核待终止
    function contractDetail(contractId,type) {
        var title = type=="detail"?"合同详情":"终止合同审核";
        layer.open({
            type: 2,
            title: title,
            area: ['760px', '580px'],
            shade: 0.2,
            content: ['/rpms/resourceContract/contractConfirm/'+ type+'?type='+type+'&&contractId=' + contractId]
        });
    }

    //终止合同
    function stopContract(contractId) {
        layer.open({
            type: 2,
            title: "终止合同",
            area: ['760px', '380px'],
            shade: 0.2,
            content: ['/rpms/resourceContract/stopContract?contractId=' + contractId]
        });
    }

    //页面筛选功能fun
    function doNewSearch(data, values) {
        if (data == "todo-state-list") {
            $("#contractType").val(values);
        }
        if (data == "todo-type-list") {
            $("#contractStatus").val(values);
        }
        $('#contractTable').bootstrapTable('refresh', {
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

    //下期账单
    function createBill($contractId) {
        $.ajax({
            type: "POST",
            data: {'contractId':$contractId},
            url: "/rpms/resourceBill/createBillByAjax",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            },
            success: function (arr) {
                if (arr.status) {
                    layer.msg(arr.msg, {time: 2000});
                } else {
                    layer.msg(arr.msg, {icon: 2, time: 2000});
                }
            }
        });
    };

    //批量启用资源类型
    $(".batchOperate").click(function () {
        var batchType = $(this).data("type");
        var str = (batchType == "up"?"启用":(batchType == "down"?"停用":"删除"));
        var selected = $('#contractTable').bootstrapTable('getSelections');
        var sIds = [];
        if (selected.length < 1) {
            layer.msg('请选择要操作的合同！', {icon: 2});
            return false;
        }

        for (var key in selected) {
            if(selected[key].status !="toDo"){
                layer.msg('所选合同包含非待执行状态，请重新选择！', {icon: 2});
                return false;
            }
        }

        var tips='确定要批量'+str+'合同吗?';

        if(batchType == "delete"){
            $.ajax({
                type: "POST",
                data: {'supIds': selected, 'batchType': batchType},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                },
                url: "/rpms/resourceContract/batchOperate",
                success: function (data) {
                    if (data.status == 'success') {
                        layer.msg('批量操作成功！', {icon: 1, time: 2000});
                        $('#contractTable').bootstrapTable('refresh');
                    }else{
                        layer.msg(data.msg, {icon: 2, time: 2000});
                        $('#contractTable').bootstrapTable('refresh');
                    }
                }
            });
        }
    });
</script>
</body>
</html>