<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="_token" content="{{ csrf_token() }}"/>
    <title>安畅网络 RPMS系统——供应商管理</title>

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
                    <a class="btn btn-warning btn6 btnwhitefr ml4" onclick="newProvider('new')">
                        <span class="font14"></span>
                        <i class="fa fa-plus mr4"></i> 新增供应商</a>
                    <a class="btn btn-warning refreshbtn btnwhitefr ml10" onclick="refreshTab()"><i
                                class="fa fa-refresh"></i></a>
                    {{--<div class="btn-group ml10" style="border:1px solid #E4E4E4;padding:0;">
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
                    </div>--}}
                    <div class="btn-group ml10" style="border:1px solid #E4E4E4;padding:0;">
                        <i class="fa fa-search" style="margin: 0 10px;"></i>
                        <input type="text" class="form-control search-box" id="searchInfo"
                               style="width: 300px;border:0;height:26px;display:inline-block;padding: 3px 0;"
                               placeholder="请输入供应商名称或ID">
                    </div>
                    <button id="searchAll" class="btn btn-warning  bigbtn4 btnwhitefr ml10" onclick="doNewSearch()">
                        查询
                    </button>
                    <input type="hidden" id="status">
                    <div style="float:right;margin-top: 12px;display: none;">
                        <label class="btn btn-warning btn6 btnred ml3" for="uploadProvider">上传供应商Excel</label>
                        <input type="file"
                               style="position:absolute;clip:rect(0 0 0 0);"
                               multiple="multiple" id="uploadProvider" name="uploadProvider"/>
                    </div>
                </div>
            </div>
            <div style="margin-top:0;">
                <div class="tab-content">
                    <div id="tab-1" class="tab-pane active">
                        <div class="full-height-scroll">
                            <div class="table-responsive" style="background-color: white">
                                <table id="providerTable" class="table-no-bordered"
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
                                       data-url="/rpms/resourceProvider/getProviderList"
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
<script src="/js/plugins/bootstrap-table/bootstrap-table.js"></script>
<!-- put your locale files after bootstrap-table.js -->
<script src="/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>
<script>
    $(function () {
        $(".table-responsive").css({"overflow": "inherit"});
    });

    //上传合同Excel文件
    $("#uploadProvider").on("change", function () {
                var oFiles = document.querySelector("#uploadProvider").files;
                //获取文件对象，files是文件选取控件的属性，存储的是文件选取控件选取的文件对象，类型是一个数组
                var fileObj = oFiles[0];
                //创建formdata对象，formData用来存储表单的数据，表单数据时以键值对形式存储的。
                var formData = new FormData();
                formData.append('excelFile', fileObj);
                var ajax = new XMLHttpRequest();
                //发送POST请求
                ajax.open("POST", "/kindeditor/uploadProvider", true);
                ajax.send(formData);
                ajax.onreadystatechange = function () {
                    if (ajax.readyState == 4) {
                        if (ajax.status >= 200 && ajax.status < 300 || ajax.status == 304) {
                            var obj = JSON.parse(ajax.responseText);
                            if (obj.error == 0) {
                                $('#providerTable').bootstrapTable('refresh', {
                                    query: {
                                        'pageNumber': 1
                                    }
                                });
                                layer.msg(obj.message);
                            } else {
                                layer.msg(obj.message);
                            }
                        }
                    }
                }
            }
    );

    var url = "{{env('JOB_URL')}}";

    var $providerTable = $('#providerTable'),
            selections = [];

    function initTable() {
        $providerTable.bootstrapTable({
            pageSize: 10,
            striped: false,
            columns: [
                [{
                    field: 'state',
                    checkbox: true,
                    align: 'middle',
                    valign: 'middle',
                    width: '5%'
                },
                    {
                        title: 'ID',
                        valign: 'middle',
                        field: 'id',
                        align: 'left',
                        width: '6%',
                        formatter: function (value, row, index) {
                            return '<a class="fontred" onclick="newProvider(' + row.id + ')"><i class="fa fa-edit mr5"></i>' + row.id + '</a>';
                        }
                    },
                    {
                        title: '供应商名称',
                        valign: 'middle',
                        width: '16%',
                        field: 'providerName',
                        align: 'left',
                        formatter: function (value, row, index) {
                            return '<a class="fontred" onclick="providerDetail(' + row.id + ')"><i class="fa fa-detail mr5"></i>' + row.providerName + '</a>';
                        }
                    },
                    {
                        title: '内部负责人',
                        valign: 'middle',
                        width: '8%',
                        field: 'innerCharger',
                        align: 'left'
                    },
                    {
                        title: '联系电话',
                        valign: 'middle',
                        width: '10%',
                        field: 'tell',
                        align: 'left'
                    },
                    {
                        field: 'describe',
                        width: '6%',
                        title: '备注',
                        valign: 'middle',
                        align: 'left',
                        formatter: function (value, row, index) {
                            return row.describe?'<span>' + stringText(row.describe,16) + '</span>':"";

                        }
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

    function stringText(text, length) {
        var length = arguments[1] ? arguments[1] : 20;
        suffix = "";
        if (text.length > length) {
            suffix = "...";
        }
        return text.substr(0, length) + suffix;
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
        $('#prodTable').bootstrapTable('refresh', {
            query: {
                'pageNumber': 1
            }
        });
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

    //新建或编辑供应商
    function newProvider(type) {
        var title = type == "new" ? "新增供应商" : "编辑供应商信息",
                area =type == "new" ? ['660px', '430px'] : ['660px', '580px'] ;
        layer.open({
            type: 2,
            title: title,
            area: area,
            shade: false,
            content: ['/rpms/resourceProvider/newProvider?type=' + type]
        });
    }

    //供应商详情
    function providerDetail(type) {
        var area = type == "new" ? ['660px', '430px'] : ['660px', '580px'];
        layer.open({
            type: 2,
            title: "供应商详情",
            area:  ['750px', '580px'],
            shade: 0.2,
            content: ['/rpms/resourceProvider/providerDetail?type=' + type]
        });
    }

    //页面筛选功能fun
    function doNewSearch(data, values) {
        if (data == "todo-state-list") {
            $("#status").val(values);
        }
        $('#providerTable').bootstrapTable('refresh', {
            query: {
                'pageNumber': 1
            }
        });
    }

    //批量启用资源类型
    /*$(".batchOperate").click(function () {
        var batchType = $(this).data("type");
        var str = (batchType == "up"?"启用":(batchType == "down"?"停用":"删除"));
        var selected = $('#providerTable').bootstrapTable('getSelections');
        var sIds = [];
        if (selected.length < 1) {
            layer.msg('请选择要操作的供应商！', {icon: 2});
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
                        url: "/rpms/resourceProvider/batchOperate",
                        success: function (data) {
                            if (data.status == 'success') {
                                layer.msg('批量操作成功！', {icon: 1,time:2000});
                                $('#providerTable').bootstrapTable('refresh');
                            }
                        }
                    })
                })
    });*/

    //自定义下拉框触发机制
   /* $(function () {
        pullDownChoice("todo-state-list", function (param) {
            doNewSearch("todo-state-list", param);
        });
        pullDownChoice("todo-type-list", function (param) {
            doNewSearch("todo-type-list", param);
        });
    })*/

    function pullDownChoice(element, callback, resetId) {
        var element = element;
        var currentEle;
        if (String(element).indexOf(0) != "$") {
            currentEle = "#" + element + " .current-title";
            element = $("#" + element);
        }

        $(document).on("click", currentEle, function (e) {
            if (e && e.stopPropagation) {
                e.stopPropagation();
            } else {
                window.event.cancelBubble = true;
            }

            /* 优化0830 start*/
            if (element.closest(".th-inner")) element.closest(".th-inner").css({"overflow": "inherit"});
            if (element.closest(".fixed-table-body")) element.closest(".fixed-table-body").css({"overflow": "inherit"});
            $(".select-list").css({"display": "none"});
            /* 优化0830 end*/

            if (element.find(".select-list").css("display") == "block") {
                element.find(".select-list").css({"display": "none"});
            } else {
                element.find(".select-list").css({"display": "block"});
            }
        });

        $(document).on("click", function () {
            element.find(".select-list").css({"display": "none"});
        });

        function itemClickFunc(element) {
            var currentValue = null;
            var currentText = $(this).text();
            element.find(".select-list-item").removeClass("selected-color");
            $(this).addClass("selected-color");

            if ($(this).attr("value")) currentValue = $(this).attr("value");

            element.find(".select-list").css({"display": "none"});
            element.find(".current-select").html(currentText);

            /* 优化0830 start*/
            if (element.closest(".th-inner")) element.closest(".th-inner").removeAttr("style");
            if (element.closest(".fixed-table-body")) element.closest(".fixed-table-body").removeAttr("style");
            /* 优化0830 end*/

            if (callback && {}.toString.call(callback) === "[object Function]") {
                callback(currentValue);
            }
        }

        // reset下拉菜单
        var currentItemArray = [];
        element.find(".select-list-item").each(function (key) {
            if ($(this).attr("value") || $(this).attr("value") == "") {
                var value = $(this).attr("value");
                if ($(this).attr("value") == "") value = "allList";
                currentItemArray.push({
                    "value": value,
                    "text": $(this).text()
                });
            }
        });
        if (resetId || resetId == "") {
            var currentValueReset = null;
            var currentTextReset = null;
            currentItemArray.forEach(function (item, key) {
                if (resetId == "" && item.value == "allList") {
                    currentValueReset = item.value;
                    currentTextReset = item.text;
                } else if (resetId == item.value) {
                    currentValueReset = item.value;
                    currentTextReset = item.text;
                }
            });
            element.find(".select-list").css({"display": "none"});
            element.find(".current-select").html(currentTextReset);
            if (callback && {}.toString.call(callback) === "[object Function]") {
                callback(currentValueReset);
            }
        } else {
            element.find(".select-list-item").click(function () {
                itemClickFunc.call(this, element);
            });
        }
    }
</script>
</body>
</html>