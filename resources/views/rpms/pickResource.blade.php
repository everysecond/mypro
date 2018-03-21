<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>安畅网络</title>
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
        .res-list-top{padding:0px;}
        .btndev{
            margin-top: 0;
            float: right;
            margin-right: 50px;
        }
        .select-list > li {
            line-height: 18px;
        }
    </style>
</head>
<body style="background-color: whitesmoke;padding: 10px;">
<div class=" wrapper-content" style="background-color: white;">
    <div class="row">
        <div class="col-sm-12" style="margin-left: 15px;width:98%;height: 95%;">
            <div class="ibox">
                <div class="res-list-top">
                    <a class="btn btn-warning btn6 btnwhitefr" onclick="newProd('new')">
                        <span class="font14"></span>
                        <i class="fa fa-plus mr4"></i> 新增资源产品</a>
                    <div class="btn-group ml10" style="border:1px solid #E4E4E4;padding:0;">
                        <i class="fa fa-search" style="margin: 0 10px;"></i>
                        <input type="text" class="form-control search-box" id="searchInfo"
                               style="width: 300px;border:0;height:26px;display:inline-block;padding: 3px 0;"
                               placeholder="请输入资源产品名称或资源类型、子类型编码">
                    </div>
                    <button id="searchAll" class="btn btn-warning  bigbtn4 btnwhitefr ml10" onclick="doNewSearch()">
                        查询
                    </button>
                    <input type="hidden" id="status" value="0">
                    <input type="hidden" id="statusType" value="commonType">
                    <input type="hidden" id="prodTypeOne" value="">
                    <input type="hidden" id="fromView" value="pickResource">
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
                                       data-page-size="5"
                                       data-id-field="Id"
                                       data-pagination-detail-h-align="right"
                                       data-page-list="[5, 10, 20]"
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
            <div class="btndev">
                <a type="reset" class="btn btndefault mar_top20 ml9" onclick="closeFrame()" style="width: 94px;">取消</a>
                <button type="button" class="btn btnpink mar_top20 ml10" onclick="addProd()" style="width: 94px;">确定已选</button>
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
            pageSize: 5,
            striped: false,
            columns: [
                [
                    {
                        field: 'state',
                        checkbox: true,
                        align: 'middle',
                        valign: 'middle',
                        width: '4%'
                    },
                    {
                        width: '6%',
                        title: '<div id="todo-type-list" class="select-wrap"><span class="current-title">' +
                        '<span class="current-select">资源类型</span><i class="fa fa-caret-down ml5"></i></span>' +
                        '<ul class="select-list"><li class="select-list-item" value="">全部</li>@foreach($prodTypeList as $prodType)<li class="select-list-item" value="{{$prodType->typeCode}}">{{$prodType->typeName}}</li>@endforeach'+
                        '</ul></div>',
                        valign: 'middle',
                        align: 'left',
                        field: 'typeName'
                    },
                    {
                        title: '资源子类型',
                        valign: 'middle',
                        width: '12%',
                        field: 'sonTypeName',
                        align: 'left'
                    },
                    {
                        title: '资源产品名称',
                        valign: 'middle',
                        align: 'left',
                        width: '14%',
                        field: 'prodName'
                    },
                    {
                        title: '单价',
                        valign: 'middle',
                        width: '7%',
                        field: 'unitPrice',
                        align: 'left'
                    },
                    {
                        title: '单位',
                        valign: 'middle',
                        width: '7%',
                        field: 'unit',
                        align: 'left'
                    },
                    {
                        title: '首次费用',
                        valign: 'middle',
                        width: '7%',
                        field: 'oneCost',
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
                    prodTypeOne: $('#prodTypeOne').val(),
                    fromView: $('#fromView').val(),
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
        if (data == "todo-type-list") {
            $("#prodTypeOne").val(values);
        }
        $('#prodTable').bootstrapTable('refresh', {
            query: {
                'prodTypeOne': values,
                'pageNumber': 1
            }
        });
    }

    function addProd() {
        var selected = $('#prodTable').bootstrapTable('getSelections');
        var $prodTable=parent.$("#prodTable");
        var old =$prodTable.bootstrapTable('getData');

        for(var i=0;i<selected.length;i++){
            var flag=false;
            for(var j in old){
                if(old[j]['prodId']==selected[i]['prodId']){
                    flag=true;
                    break;
                }
            }
            if(!flag){
                selected[i]['amount']=1;
                if(selected[i]['isSpecialLine'] == "yes"){
                    parent.$(".specialTable").removeClass("hidden");
                }
                $prodTable.bootstrapTable('insertRow', {index:selected.length+i, row:selected[i]});
            }
        }

        closeFrame();
    }

    function closeFrame() {
        var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
        parent.layer.close(index); //再执行关闭
    }

    //自定义下拉框触发机制
    $(function () {
        pullDownChoice("todo-type-list", function (param) {
            doNewSearch("todo-type-list", param);
        });
    })

</script>
</body>
</html>