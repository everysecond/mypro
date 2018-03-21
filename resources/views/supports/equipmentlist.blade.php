<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <title>安畅网络 工单系统</title>
    <!--[if lt IE 9]>
    <meta http-equiv="refresh" content="0;ie.html"/>
    <![endif]-->
    {{--<link href="/css/usercenter.css" rel="stylesheet" type="text/css">--}}
    <link rel="shortcut icon" href="favicon.ico">
    <link href="/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/css/font-awesome.min.css?v=4.4.0" rel="stylesheet">
    <link href="/css/animate.css" rel="stylesheet">
    <link href="/css/style.css?v=4.1.0" rel="stylesheet">

    {{--<link href="http://www.51idc.cn/51idc3.0/css/user_module.css" rel="stylesheet" type="text/css">
    <link href="http://www.51idc.cn/51idc3.0/css/font.css" rel="stylesheet" type="text/css">--}}
    <link href="/css/font.css" rel="stylesheet" type="text/css">
    <link href="/css/print.css" rel="stylesheet" type="text/css">
    <!-- 第三方插件 -->
    <link type="text/css" rel="stylesheet" href="/js/plugins/layer/laydate/need/laydate.css">
    <link type="text/css" rel="stylesheet" href="/js/plugins/layer/laydate/skins/default/laydate.css" id="LayDateSkin">
    <!-- 自定义css -->
    <link rel="stylesheet" type="text/css" href="/css/my.css">
    <link href="/css/user.css" rel="stylesheet" type="text/css">
    <style type="text/css">
        #devTable {
            text-align: center;
            color: #6b7d86;
        }

        .pagination {
            margin: 0 0;
        }
        body{
            font-size: 12px !important;
        }
    </style>

</head>
<body>
<div>
    <div class="col-sm-6">
        <div class="" style="margin-top: -6px;border:0px;width:550px">
            <div class="input-group" style="margin:0 auto;margin-top: 10px;width:300px">
                <input placeholder="IP地址" name="IPaddr" value="" id="IPaddr" class="input form-control" type="text">
                <span class="input-group-btn">
                    <button class="btn btn btn-primary" id="IPsearch">
                        <i class="fa fa-search"></i> 搜索
                    </button>
                </span>
            </div>
            <table id="devTable" class="table-no-bordered active"
                   style=" text-align: center;color:#6b7d86;" bgcolor="#FFFFFF"
                   cellpadding="0"
                   cellspacing="0" width="100%"
                   data-pagination="true"
                   data-page-size="5"
                   data-page-list="[5]"
                   data-id-field="Id"
                   data-show-footer="false"
                   data-side-pagination="server"
                   data-url="/support/getEquipmentList?mode={{$mode}}&cusinfId={{$cusinfId}}"
                   data-response-handler="responseHandler">
            </table>
        </div>
    </div>
</div>
{{--<!-- 全局js -->--}}
<script src="/js/jquery.min.js?v=2.1.4"></script>
<script src="/js/plugins/layer/layer.min.js"></script>
<script src="/js/bootstrap.min.js?v=3.3.6"></script>
{{--<!-- 第三方插件 -->--}}
<script src="/js/plugins/bootstrap-table/bootstrap-table.js"></script>
<!-- put your locale files after bootstrap-table.js -->
<script src="/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>

<!-- 自定义js -->
<script>
    function closeFrame() {
        var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
        parent.layer.close(index); //再执行关闭
    }
    var $equipmentTable = $('#devTable'),
            selections = [];
    function initTable() {
        $equipmentTable.bootstrapTable({
            columns: [
                [{
                    title: '设备编号',
                    valign: 'middle',
                    field: 'DevId',
                    height: 100,
                    align: 'center'
                }, {
                    title: '设备类型',
                    valign: 'middle',
                    field: 'Means',
                    align: 'center',
                    events: 'operateEvents'
                }, {
                    field: 'devIpaddrone',
                    title: 'IP地址',
                    valign: 'middle',
                    align: 'center'
                }, {
                    field: 'DataCenterName',
                    title: '数据中心',
                    valign: 'middle',
                    align: 'center'
                }, {
                    title: '操作',
                    field: 'operation',
                    valign: 'middle',
                    align: 'center',
                    formatter: function (value, row, index) {
                        return '<a style="color:green">点击选择此设备</a>';
                    }
                }
                ]
            ],
            queryParamsType: "undefined",
            queryParams: function queryParams(params) {
                return {
                    pageSize: params.pageSize,
                    pageNumber: params.pageNumber
                }
            }
        });
        $equipmentTable.on('click-cell.bs.table', function ($element, field, value, row) {
            if (field == 'operation') {
                parent.$('#DevId').val(row.DevId);
                parent.$('#hiddenDevId').val(row.devIpaddrone);
                parent.$('#EquipmentId').val(row.DevId);
                parent.$('#dataCenterName').val(row.DataCenterName);
                parent.$('#dataCenter').val(row.DataCenterName).attr('disabled', 'disabled');
                closeFrame();
            }
        })
    }

    $('#IPsearch').click(function () {
        $('#devTable').bootstrapTable('refresh', {
            query: {
                'IPaddr': $('#IPaddr').val(),
                pageNumber: 0
            }
        });
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

</script>

<script>
    initTable();
</script>
</body>
</html>