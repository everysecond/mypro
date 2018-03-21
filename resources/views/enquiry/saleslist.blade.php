<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="_token" content="{{ csrf_token() }}"/>
    <title>安畅网络 工单系统——产品询价管理</title>

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
        .hiddenTable {
            display: none;
        }

        .table-fixpadding tr {
            height: 55px;
        }

        .table-fixpadding th, .table-fixpadding td {
            padding: 0 8px !important;
        }

        .date-form{
            display: inline-block;
            width: 160px;
            border-radius: 4px;
        }

        .select-form{
            display: inline-block;
            width: 300px;
            border-radius: 4px 0 0 4px;
        }
        .select-form2{
            display: inline-block;
            width: 50px;
            border-radius: 0 4px 4px 0;
            margin-left: -10px;
        }
        td{
            font-size: 13px !important;
        }
    </style>
</head>
<body>
<div class=" wrapper-content" style="background-color: whitesmoke">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="job-list-top">
                    <button class="btn btn-primary" id="newEquiry"> 新增询价申请
                    </button>

                    <span style="margin-left: 20px;">创建时间</span>
                    <input type="date" class="form-control date-form" id="beginTs">至
                    <input type="date" class="form-control date-form" id="endTs">
                    <input type="text" class="form-control select-form" id="searchInfo" style="width: 300px"
                           placeholder="  询价编号/询价主题/相关客户">
                    <button class="btn btn-primary select-form2" onclick="doNewSearch()"> 搜索</button>
                    <input type="hidden" id="priority" value="">
                    <input type="hidden" id="steps" value="">
                    <input type="hidden" id="status">
                </div>
            </div>
            <div style="margin-top:5px;">
                <div class="tab-content">
                    <div id="tab-1" class="tab-pane active">
                        <div class="full-height-scroll">
                            <div class="table-responsive" style="background-color: white">
                                <table id="salesTable" class="table-no-bordered table-fixpadding"
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
                                       data-url="/enquiry/getSalesList"
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
    $('#searchInfo').bind('keypress',function(event){
        var searchAll = document.getElementById("searchAll");
        if(event.keyCode == "13")
        {
            searchAll.click();
        }
    });

    $(function () {
        $(".table-responsive").css({"overflow": "inherit"});
    });
    var url = "{{env('JOB_URL')}}";

    var $salesTable = $('#salesTable'),
            $remove = $('.remove'),
            selections = [];
    function curTime(){
        return new Date().getTime();
    }

    function timestamp(url){
        //  var getTimestamp=Math.random();
        var getTimestamp=new Date().getTime();
        if(url.indexOf("?")>-1){
            url=url+"&timestamp="+getTimestamp
        }else{
            url=url+"?timestamp="+getTimestamp
        }
        return url;
    }

    function initTable() {
        $salesTable.bootstrapTable({
            pageSize: 20,
            striped: true,
            columns: [
                [
                    {
                        title: '序号',
                        valign: 'middle',
                        align: 'center',
                        width: '2%',
                        formatter: function (value, row, index) {
                            return index+1;
                        }
                    },
                    {
                        title: '询价编号',
                        valign: 'middle',
                        width: '14%',
                        field: 'enquiryNo',
                        align: 'left',
                        formatter: function (value, row, index) {
                            return row.steps == "销售申请" && row.isEdit?"<a onclick='editEnquiry("+row.id+")'>" +
                            "<i class='fa fa-edit'></i> "+row.enquiryNo+"</a>":
                            "<a class='J_menuItem' href='/enquiry/enquiryDetail/" + row.id + "' >" +
                            "<i class='fa fa-file-text'></i> " + row.enquiryNo + "</a>";
                        }
                    },
                    {
                        title: '询价主题',
                        valign: 'middle',
                        width: '20%',
                        field: 'title',
                        align: 'left',
                        formatter: function (value, row, index) {
                            return "<span class='etitle'>"+substringText(row.title,12)+"</span>";
                        },
                        events: 'operateEvents'
                    },
                    {
                        field: 'priority',
                        width: '4%',
                        title: '<div id="todo-state-list" class="select-wrap"><span class="current-title">' +
                        '<span class="current-select">优先级</span><i class="select-icon"></i></span>' +
                        '<ul class="select-list"><li class="select-list-item" value="">优先级</li>' +
                        '<li class="select-list-item" value="0">一般</li>' +
                        '<li class="select-list-item" value="1">重要</li>' +
                        '</ul></div>',
                        valign: 'middle',
                        align: 'left',
                        formatter: function (value, row, index) {
                            return row.priority == 1?"重要":(row.priority == 0?"一般":"");
                        },
                        events: 'operateEvents'
                    },
                    {
                        field: 'expectTs',
                        width: '9%',
                        title: '预计使用日期',
                        valign: 'middle',
                        align: 'left',
                        formatter: function (value, row, index) {
                            return row.expectTs != null ? substrTime(row.expectTs,10) : '';
                        }
                    },
                    {
                        field: 'expectMoney',
                        width: '6%',
                        title: '预计金额',
                        valign: 'middle',
                        align: 'left',
                        formatter: function (value, row, index) {
                            return row.expectMoney != null ? row.expectMoney : '';
                        }
                    },
                    {
                        field: 'steps',
                        width: '8%',
                        title: '<div id="todo-type-list" class="select-wrap"><span class="current-title"><span class="current-select">状态</span><i class="select-icon"></i></span>' +
                        '<ul class="select-list"><li class="select-list-item" value="">状态</li>@foreach($stepsList as $type) <li class="select-list-item" value="{{$type->Code}}">{{$type->Means}}</li>@endforeach</ul></div>',
                        valign: 'middle',
                        align: 'left'
                    },
                    {
                        field: 'CusName',
                        width: '20%',
                        title: '相关客户',
                        valign: 'middle',
                        align: 'left',
                        formatter: function (value, row, index) {
                            return  row.CusName?'<a href="{{env('JOB_URL', 'http://www.51idc.cn')}}/crm/user/finance/customerDetailNew.html?cusinfid='
                                    + row.cusId + '" target="_blank">' + substringText(row.CusName,12) + '</a>':"";
                        }
                    },
                    {
                        field: 'applyUserId',
                        width: '10%',
                        title: '申请人<br/>申请时间',
                        valign: 'middle',
                        align: 'left',
                        formatter: function (value, row, index) {
                            var s = row.userId + '<br/>' + substrTime(row.ts,10);
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
                    steps: $('#steps').val(),
                    priority: $('#priority').val(),
                    beginTs: $('#beginTs').val(),
                    endTs: $('#endTs').val(),
                    searchInfo: $('#searchInfo').val()
                }
            }
        });

        var custips;
        //bootstrap监听事件
        window.operateEvents = {
            'mouseover .etitle': function (e, value, row, index) {
                if (row.title) {
                    custips = layer.tips(row.title,this, {
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
    function doNewSearch(data, values) {//普通工单检索
        if (data == "todo-type-list") {
            $("#steps").val(values);
        }
        if (data == "todo-state-list") {
            $("#priority").val(values);
        }

        $('#salesTable').bootstrapTable('refresh', {
            query: {
                'steps': $('#steps').val(),
                'priority': $('#priority').val(),
                'beginTs': $('#beginTs').val(),
                'endTs': $('#endTs').val(),
                'searchInfo': $('#searchInfo').val(),
                'pageNumber': 1
            }
        });
    }
    $(function () {
        pullDownChoice("todo-state-list", function (param) {
            doNewSearch("todo-state-list", param);
        });
        pullDownChoice("todo-type-list", function (param) {
            doNewSearch("todo-type-list", param);
        });
        /*showToolTip($("._handle_tip"));
        showTableToolTip("._jobState");*/
    })
</script>
</body>

</html>