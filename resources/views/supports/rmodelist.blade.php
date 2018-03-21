<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="_token" content="{{ csrf_token() }}"/>
    <title>安畅网络 工单系统——快速回复模板列表</title>

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
        tr {
            height: 55px;
        }

        .table-fixpadding th, .table-fixpadding td {
            padding: 0 8px !important;
        }

        .rmodecontent{
            width: 100%;
            word-break: break-all
        }
    </style>
</head>
<body class="gray-bg">
<div class=" wrapper-content" style="background-color: whitesmoke">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="job-list-top">
                    <button class="submit-btn" id="subsupport"> + 新增模板</button>
                    <div style="float: right;;margin-top: 10px">
                        <input type="text" class="form-control" name="searchTxt"
                               id="searchTxt" value="" placeholder="模板名称查询" style="display: inline;width: 70%">
                        <a class="btn btn-info" style="background-color:#19b492;margin-left: -5px;" id="searchAll"
                           onclick="doNewSearch(this,'')">
                            <span class="glyphicon glyphicon-search">搜索</span>
                        </a>
                    </div>

                    {{--条件存储--}}
                    <input type="hidden" value="" id="Type">
                    <input type="hidden" value="" id="supportType">
                </div>
            </div>
            <div style="margin-top:5px;">
                <div class="tab-content" style="padding: 5px 5px 5px 5px;background-color: white">
                    <div class="" id="supT1">
                        <table id="rmodeTable" class="table-no-bordered table-fixpadding active"
                               style="text-align: center;color:#6b7d86;" bgcolor="#FFFFFF"
                               cellpadding="0"
                               cellspacing="0" width="100%"
                               data-pagination="true"
                               data-page-size="10"
                               data-id-field="Id"
                               data-page-list="[10, 25, 50, 100, ALL]"
                               data-show-footer="false"
                               data-side-pagination="server"
                               data-url="/support/getrmodeListData"
                               data-response-handler="responseHandler">
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- 全局js -->
<script src="/js/jquery.min.js?v=2.1.4"></script>
<script src="/js/plugins/layer/layer.min.js"></script>
<script src="/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script type="text/javascript" src="/js/common.js"></script>
<script type="text/javascript" src="/js/job_list.js"></script>
<script src="/js/plugins/bootstrap-table/bootstrap-table.js"></script>
<!-- put your locale files after bootstrap-table.js -->
<script src="/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>

<script>
    var url = "{{env('JOB_URL')}}";
    layer.config({
        extend: 'extend/layer.ext.js'
    });
    $("#subsupport").click(function () {
        layer.open({
            type: 2,
            title: '工单管理>快速回复模板>新增模板 （<span style="color:#ff253d">*表示必填项</span>）',
            area: ['550px', '400px'],
            shade: 0.2,
            content: ['/support/newRmode', 'no']
        });
    });

    var $rmodeTable = $('#rmodeTable'),
            selections = [];

    function initTable() {//加载数据
        $rmodeTable.bootstrapTable({
            pageSize: 20,
            columns: [
                [{
                    title: '模板编号',
                    valign: 'middle',
                    field: 'Id',
                    width: '5%',
                    align: 'left',
                    formatter: function (value, row, index) {
                        return "<span id='mode"+row.Id+"'>"+row.Id+"</span>";
                    }
                }, {
                    field: 'Type',
                    title: '<div id="todo-status-list" class="select-wrap"><span class="current-title"><span class="current-select">分组</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list"><li class="select-list-item" value="">全部分组</li>@foreach($groupList as $type) <li class="select-list-item" value="{{$type->Type}}">{{$type->Type}}</li>@endforeach</ul></div>',
                    valign: 'middle',
                    align: 'left',
                    width: '7%'
                },  {
                    field: 'supportType',
                    title: '<div id="todo-Type-list" class="select-wrap"><span class="current-title"><span class="current-select">工单类型</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list"><li class="select-list-item" value="">工单类型</li>@foreach($typeList  as $type) <li class="select-list-item" value="{{$type->Code}}">{{$type->Means}}</li>@endforeach</ul></div>',
                    valign: 'middle',
                    align: 'left',
                    width: '8%',
                    formatter: function (value, row, index) {
                        return row.supportType!='无'?row.supportType:"<span style='color: #1f18ff' >"+'通用'+"</span>";
                    }
                }, {
                    field: 'Title',
                    title: '名称',
                    valign: 'middle',
                    align: 'left',
                    width: '11%'
                }, {
                    field: 'Content',
                    title: '内容',
                    valign: 'middle',
                    width: '60%',
                    align: 'left',
                    formatter:function(value, row, index){
                        return "<div class='rmodecontent'>"+row.Content+"</div>"
                    }
                }, {
                    title: '操作',
                    valign: 'middle',
                    width: '7%',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var edit = '<a class="rmodeEdit" href="javascript:void(0)" title="修改编辑">' +
                                '<img src="/img/icon/edit.png" /></a>&nbsp;&nbsp;';
                        var deleted = '<a class="rmodeDelete" href="javascript:void(0)" title="删除">' +
                                '<img src="/img/icon/delete.png" /></a>';
                        return edit + deleted;
                    },
                    events: 'operateEvents'
                }
                ]
            ],
            queryParamsType: "undefined",
            queryParams: function queryParams(params) {
                return {
                    pageSize: params.pageSize,
                    pageNumber: params.pageNumber,
                    'Type': $('#Type').val(),
                    'supportType': $('#supportType').val(),
                    'searchTxt': $('#searchTxt').val()
                }
            }
        });
    }
    layer.config({
        extend: 'extend/layer.ext.js'
    });
    window.operateEvents = {
        'click .rmodeDelete': function (e, value, row, index) {
            var rmodeId = row.Id;
            layer.confirm('您确定要删除该模板吗?',function () {
                $.ajax({
                    type: "GET",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    },
                    url: "/support/rmodeDelete/" + rmodeId,
                    success: function (data) {
                        if (data.status) {
                            layer.msg('删除成功！', {icon: 1});
                            $('#rmodeTable').bootstrapTable('refresh');
                        }
                    }
                });
            });
        },
        'click .rmodeEdit': function (e, value, row, index) {
            var rmodeId = row.Id;
            showEdit = layer.open({
                type: 2,
                title: '回复模板/编辑',
                area: ['550px', '400px'],
                content: ['/support/rmodeEdit/' + rmodeId, 'no']
            });
        }
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


    function doNewSearch(data, values) {//模板检索
        if (data == "todo-status-list") {
            $("#Type").val(values);
        }
        if (data == "todo-Type-list") {
            $("#supportType").val(values);
        }

        $('#rmodeTable').bootstrapTable('refresh');
    }
    function doExport(data, values) {
        var params = {
            'Type': $("#Type").val(),
            'supportType': $("#supportType").val(),
            'searchTxt': $('#searchTxt').val(),
            'priority': $('#priority').val(),
            'pageNumber': 1
        };
        queryString = $.param(params);
        window.location.href = "/support/exportModeList?" + queryString;
    }
    initTable();
    $(function () {
        pullDownChoice("todo-status-list", function (param) {
            doNewSearch("todo-status-list", param);
        });
        pullDownChoice("todo-Type-list", function (param) {
            doNewSearch("todo-Type-list", param);
        });
        showToolTip($("._handle_tip"));
        showTableToolTip("._jobState");
    })
</script>
</body>

</html>