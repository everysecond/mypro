<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="_token" content="{{ csrf_token() }}"/>
    <title>安畅网络 资源系统——人员列表</title>
    <!--[if lt IE 9]>
    <meta http-equiv="refresh" content="0;ie.html"/>
    <![endif]-->
    <link rel="shortcut icon" href="favicon.ico">
    <link href="/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/css/font-awesome.min.css?v=4.4.0" rel="stylesheet">
    <link href="/css/style.css?v=4.1.0" rel="stylesheet">
    <link rel="stylesheet" href="/css/job_list.css">
    <link rel="stylesheet" href="/css/plugins/bootstrap-table/bootstrap-table.css">

</head>
<body>
<div class=" wrapper-content" style="background-color: whitesmoke">
    <div class="row">
        <div class="col-sm-12">
            <div style="margin-top:5px;">
                <div class="tab-content">
                    <div id="tab-1" class="tab-pane active">
                        <div class="full-height-scroll">
                            <form class="form-inline" style="padding: 14px;border: 1px solid #e4dddd;">
                                <div class="input-group" style="min-width:450px;">
                                    <input type="text" class="form-control" name="searchInfo"
                                           id="searchInfo" value=""
                                           placeholder="输入姓名/Id/联系电话/手机号 搜索">
                                    <span class="input-group-btn">
                            <a class="btn btn-info" style="background-color:#19b492" id="searchAll"
                               onclick="doNewSearch(this,'')">
                                <span class="fa fa-search">搜索</span>
                            </a></span>
                                </div>
                                <img src="/img/refresh.png" onclick="refreshCache()" class="btn btn-default pull-right"
                                     title="刷新"/>
                            </form>
                            <div class="table-responsive" style="background-color: white">
                                <table id="memberTable" class="table-no-bordered table-fixpadding"
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
                                       data-url="/memberData"
                                       data-response-handler="responseHandler">
                                </table>
                            </div>
                        </div>
                        <input type="hidden" value="" id="groupList">
                        <input type="hidden" value="" id="Depart">
                        <input type="hidden" value="" id="second_dept">
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
<script src="/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script type="text/javascript" src="/render/hplus/js/contabs.js"></script>
<script src="/js/plugins/bootstrap-table/bootstrap-table.js"></script>
<script type="text/javascript" src="/js/job_list.js"></script>
<!-- put your locale files after bootstrap-table.js -->
<script src="/js/plugins/layer/layer.min.js"></script>
<script src="/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>

<script>
    var url = "{{env('JOB_URL')}}";
    var list = '{!!$json!!}';
    var $memberTable = $('#memberTable'),
            selections = [];

    function responseHandler(res) {
        $.each(res.rows, function (i, row) {
            row.state = $.inArray(row.id, selections) !== -1;
        });
        return res;
    }
    function initTable() {
        $memberTable.bootstrapTable({
            pageSize: 20,
            striped: true,
            columns: [
                [{
                    title: '人员Id',
                    valign: 'middle',
                    align: 'center',
                    field: 'Id',
                    width: '5%',
                }, {
                    title: '姓名',
                    valign: 'middle',
                    align: 'left',
                    field: 'memberIds',
                    width: '5%'
                }, {
                    title: '联系电话/手机号',
                    valign: 'middle',
                    field: 'Mobile',
                    width: '12%',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var s = '';
                        if (row.Mobile && row.Tel) s = '&nbsp;/&nbsp;';
                        var contact = '<br>' + row.Mobile + s + row.Tel + '<br>';
                        return contact;
                    },
                    events: 'operateEvents'
                }, {
                    title: '邮箱',
                    width: '8%',
                    field: 'Email',
                    valign: 'middle',
                    align: 'left',
                    events: 'operateEvents'
                }, {
                    field: '微信',
                    width: '5%',
                    title: '微信',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row) {
                        var name= row.nickname1 ? row.nickname1 : row.nickname2;
                        return name;
                    },
                    events: 'operateEvents',
                },
                    {
                        field: 'Depart',
                        width: '8%',
                        title: '<div id="todo-groupOne-list" class="select-wrap"><span class="current-title"><span class="current-select">一级部门</span><i class="select-icon"></i></span>' +
                        '<ul class="select-list"><li class="select-list-item" value="">一级部门</li>@foreach($group as $key=>$item) <li class="select-list-item" value="{{$key}}">{{$item['name']}}</li>@endforeach</ul></div>',
                        valign: 'middle',
                        align: 'left',

                    }, {
                    field: 'second_dept',
                    width: '10%',
                    title: '<div id="todo-groupTwo-list" class="select-wrap"><span class="current-title"><span class="current-select">二级部门</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list"><li class="select-list-item" value="">二级部门</li>@foreach($group as $key=>$item)@if(isset($item['child'])&&is_array($item['child']))@foreach($item['child'] as $k=>$value) <li class="select-list-item" value="{{$k}}">{{$value}}</li>@endforeach @endif
                            @endforeach</ul></div>',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {

                        if (row.second_dept == '无') {
                            return '&nbsp;';
                        }
                        else return row.second_dept;
                    },
                    events: 'operateEvents'
                }, {
                    field: 'chargeGroup',
                    width: '10%',
                    title: '<div id="todo-group-list" class="select-wrap"><span class="current-title"><span class="current-select">所在组</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list"><li class="select-list-item" value="">所在组</li>@foreach($chargeGroupList as $charge) <li class="select-list-item" value="{{$charge->Code}}">{{$charge->Means}}</li>@endforeach</ul></div>',
                    valign: 'middle',
                    align: 'left',
                }
                ]
            ],
            queryParamsType: "undefined",
            queryParams: function queryParams(params) {
                return {
                    pageSize: params.pageSize,
                    pageNumber: params.pageNumber,
                    'groupList': $('#groupList').val(),
                    'Depart': $('#Depart').val(),
                    'second_dept': $('#second_dept').val(),
                    'searchInfo': $('#searchInfo').val(),
                }
            }
        });
    }
    initTable();
    function doNewSearch(data, values) {//普通检索

        if (data == "todo-group-list") {
            $("#groupList").val(values);
        }
        if (data == "todo-groupOne-list") {
            $("#Depart").val(values);
            $("#todo-groupTwo-list .current-select").text("二级部门");
            var slist = JSON.parse(list);
            $("#todo-groupTwo-list .select-list li").each(function (index, items) {
                if (!values || (values && slist[values].child[$(items).attr("value")]) || !$(items).attr("value")) {
                    $(this).show()
                } else {
                    $(this).hide()
                }
            });
        }
        if (data == "todo-groupTwo-list") {
            $("#second_dept").val(values);
        }

        $('#memberTable').bootstrapTable('refresh', {
            query: {
                'groupList': $("#groupList").val(),
                'Depart': $("#Depart").val(),
                'second_dept': $("#second_dept").val(),
                'searchInfo': $("#searchInfo").val(),
            }
        });
    }
    $(function () {
        pullDownChoice("todo-group-list", function (param) {
            doNewSearch("todo-group-list", param);
        });
        pullDownChoice("todo-groupOne-list", function (param) {
            doNewSearch("todo-groupOne-list", param);
        });
        pullDownChoice("todo-groupTwo-list", function (param) {
            doNewSearch("todo-groupTwo-list", param);
        });
        showToolTip($("._handle_tip"));
        showTableToolTip("._jobState");
    })
    function refreshCache() {
        $.ajax({
            type: "GET",
            url: "/support/cleanMemCache",
            success: function (data) {
                if (data.status == 'ok') {
                    layer.alert('清除缓存成功！', {icon: 1, closeBtn: 0, area: '100px'});
                }
                $('#memberTable').bootstrapTable('refresh');
            }
        });
    }
</script>
</body>

</html>