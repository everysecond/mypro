<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>安畅网络 询价申请</title>
    <!--[if lt IE 9]>
    <meta http-equiv="refresh" content="0;ie.html"/>
    <![endif]-->
    <link rel="shortcut icon" href="favicon.ico">
    <link href="/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/css/font-awesome.min.css?v=4.4.0" rel="stylesheet">
    <link href="/css/style.css?v=4.1.0" rel="stylesheet">
    <link href="/css/font.css" rel="stylesheet" type="text/css">

    <!-- 第三方插件 -->
    <link rel="stylesheet" href="/css/plugins/bootstrap-table/bootstrap-table.css">
    <!-- 自定义css -->
    <link rel="stylesheet" href="/css/change_detail.css"/>
    <link rel="stylesheet" type="text/css" href="/css/handover.css?{{\Itsm\Http\Helper\ThirdCallHelper::getTime()}}">
    <style>
        .litle-img {
            width: 30px !important;
            height: 30px !important;
            margin-left: 10px;
            vertical-align: middle;
            cursor: pointer;
        }


        .table-edit, .table-edit td {
            border: 1px solid #fff;
            height: 20px;
            font-size: 12px;
            padding: 6px 10px;
        }

        *, td {
            color: #666666;
            font-size: 12px;
        }

        .mar_top20 {
            margin-top: 20px;
        }

        .divBack2 {
            background-color: white;
            border: 1px solid #F2F2F2;
            border-radius: 4px;
        }

        #offersTable > thead > tr > th, #recordsTable > thead > tr > th {
            background-color: #F9F9F9;
            border-right: 1px solid #F9F9F9;
        }

        #offersTable > tbody > tr > td, #recordsTable > tbody > tr > td {
            border-top: 0;
            line-height: 2.7;
        }

        .fixed-table-container {
            border: 0px ! important;
        }

        .titleCss {
            font-size: 16px;
            font-family: '', '', '应用字体';
            margin-left: -120px;
            font-weight: 700
        }

        .titleCss2 {
            width: 125px;
            font-size: 14px;
            font-family: '', '', '应用字体';
            margin: 10px 0 0 25px;
            padding: 2px;
            color: #19B492;
            border-bottom: 4px solid #19B492;
        }

        .btn-xsm {
            height: 25px;
            padding: 2px 8px;
        }

        input[type=checkbox] {
            width: 25px;
        }

        .mar_lef10 {
            margin-left: 10px !important;
        }

        .form-control-process {
            width: 210px;
            position: absolute;
            top: -28px;
        }
    </style>
</head>
<body style="background-color: #FAFAFA;padding: 10px;">
<div class="col-sm-12 divBack2">
    <div class="ibox float-e-margins">
        <div id="mC1Q5" class="print_main">
            <div class="print_content" style="margin-top: -6px;border:0px;">
                <table class="table-edit" style="color: #000000;" bgcolor="#FFFFFF" border="1" width="100%">
                    <tbody>
                    <tr style="height:20px">
                        <input type="hidden" id="enquiryId"
                               value="@if($enquiry!="" && $enquiry->id){{$enquiry->id}}@endif"/>
                        <input type="hidden" id="route" value="{{$enquiry->steps}}Sub"/>
                    </tr>
                    <tr style="border-bottom: 1px solid #F2F2F2">
                        <td style="border-bottom: 1px solid #F2F2F2"></td>
                        <td colspan="3" style="border-bottom: 1px solid #F2F2F2">
                            <span class="titleCss">询价审核(产品)</span>
                        </td>
                    </tr>
                    <tr style="height:10px"></tr>
                    <tr>
                        <td align="right">询价编号：</td>
                        <td>
                            {{$enquiry->enquiryNo}}
                        </td>
                        <td align="right">状态：</td>
                        <td>
                            {{\Itsm\Http\Helper\ThirdCallHelper::getDictMeans('询价状态','EnquirySteps',$enquiry->steps)}}
                            <input type="hidden" name="changeStateMeans"
                                   value="{{\Itsm\Http\Helper\ThirdCallHelper::getDictMeans('询价状态','EnquirySteps',$enquiry->steps)}}"/>
                            <img src="/img/flowchart.png" width="20" height="15" id="flowChart" title="查看流程图">
                        </td>
                    </tr>
                    <tr>
                        <td align="right">询价主题：</td>
                        <td colspan="3">
                            @if($enquiry!="" && $enquiry->title){{$enquiry->title}}@endif
                        </td>
                    </tr>
                    <tr>
                        <td align="right">客户名称：</td>
                        <td>
                            @if($enquiry!=""&&$enquiry->cusName!=""){{$enquiry->cusName}}@endif
                        </td>
                        <td align="right">优先级：</td>
                        <td width="30%">
                            @if($enquiry!=""&&$enquiry->priority=="0")一般@elseif($enquiry->priority=="1")重要@endif
                        </td>
                    </tr>
                    <tr>
                        <td align="right" width="10%"><span style="color: red">*</span>预计使用日期：</td>
                        <td width="40%">
                            @if($enquiry!="" && $enquiry->expectTs){{$enquiry->expectTs}}@endif
                        </td>
                        <td align="right" width="10%">预计采购金额：</td>
                        <td width="20%">
                            @if($enquiry!="" && $enquiry->expectMoney){{$enquiry->expectMoney}}@endif
                        </td>
                    </tr>
                    <tr>
                        <td align="right" width="12%"><span style="color: red">*</span>询价内容：</td>
                        <td colspan="3" width="40%" class="divBack">
                            <div style="max-width: 80%;word-break: break-all;font-size: 12px !important;">
                                @if($enquiry!="" && $enquiry->body){!! $enquiry->body !!}@endif
                            </div>

                        </td>
                    </tr>
                    <tr>
                        <td align="right" width="10%">申请时间：</td>
                        <td width="40%">
                            @if($enquiry!="" && $enquiry->ts){{$enquiry->ts}}@endif
                        </td>
                        <td align="right" width="10%">申请人：</td>
                        <td width="20%">
                            @if($enquiry!="" && $enquiry->userId){{\Itsm\Http\Helper\ThirdCallHelper::getStuffName($enquiry->userId)}}@endif
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4"></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="col-sm-12 divBack2" style="margin-top: 10px">
    <div class="ibox float-e-margins">
        <div class="tab-content">
            <div id="tab-1" class="tab-pane active">
                <div class="full-height-scroll">
                    <div class="table-responsive" style="background-color: white">
                        <div style="border-bottom: 1px solid #F2F2F2;margin-bottom: 5px">
                            @if($enquiry->steps != "inquiryEnd")
                                <button style="float: right" class="btn btn-primary btn-sm newOffer">
                                    +添加产品报价
                                </button>
                            @endif
                            <div class="titleCss2">&nbsp;相关产品报价&nbsp;</div>
                        </div>

                        <table id="offersTable" class="table-no-bordered table-fixpadding"
                               style="text-align: center;color:#6b7d86;" bgcolor="#FFFFFF"
                               cellpadding="0"
                               cellspacing="0" width="100%"
                               data-pagination="false"
                               data-show-export="true"
                               data-page-size="5"
                               data-id-field="Id"
                               data-pagination-detail-h-align="right"
                               data-page-list="[10, 25, 50, 100, ALL]"
                               data-show-footer="false"
                               data-side-pagination="server"
                               data-url="/enquiry/getOfferList?enquiryId=@if($enquiry!="" && $enquiry->id){{$enquiry->id}}@endif"
                               data-response-handler="responseHandler">
                        </table>
                        @if($enquiry->steps != "inquiryEnd")
                            <div style="margin-top: 15px">
                                <form method="POST" id="productOffer" enctype="multipart/form-data">
                                    <fieldset>
                                        {{csrf_field()}}
                                        <div style="display: inline-block;width:60%">
                                <textarea placeholder="  如果对后续操作要做说明，请在此填写相应信息，审核说明"
                                          style="border-radius: 3px;resize: none;width: 100%;height:130px;
                                          border-color:rgba(204, 204, 204, 1); " name="instructions"></textarea>
                                        </div>
                                        @if($stepForm["variable"] !='')
                                            <div style="display: inline-block;line-height:12px;margin-left: 5%;width: 33%">
                                                <div style="display: inline-block;line-height: 6px;font-weight: 700">
                                                    提醒方式：<br/>&nbsp;
                                                </div>
                                                <input type="checkbox" value="sms" class="mar_lef10"
                                                       name="noticeType[]">
                                                <div style="display: inline-block;line-height: 6px;">
                                                    短信<br/>&nbsp;</div>
                                                <input type="checkbox" value="wechat" class="mar_lef10"
                                                       name="noticeType[]">
                                                <div style="display: inline-block;line-height: 6px;">
                                                    微信<br/>&nbsp;</div>
                                                <input type="checkbox" value="email" class="mar_lef10"
                                                       name="noticeType[]" checked="checked" >
                                                <div style="display: inline-block;line-height: 6px;">
                                                    邮件<br/>&nbsp;</div>
                                                <br/>
                                                <br/>
                                                <br/>
                                                <div style="display: inline-block;line-height: 6px;">
                                                    若提交请选择：<br/>&nbsp;</div>
                                                <div style="display: inline-block;line-height: 6px;">
                                                    {!! $stepForm["form"] !!}
                                                </div>
                                                <br/>
                                                <br/>
                                                <input type="hidden" id="processVar" value="{{$stepForm["variable"]}}"
                                                       name="processVar"/>
                                                <a class="btn btn-default" onclick="closeJmenu()"
                                                   style="width: 94px;">取消</a>
                                                <a class="btn btn-primary btnSub" data-type="saveAndSub"
                                                        style="width: 94px;margin-left: 4%">提交</a><br>&nbsp;
                                            </div>
                                        @else
                                            <div style="display: inline-block;line-height:0;margin-left: 5%;width: 33%">
                                                <input type="hidden" id="processVar" value="{{$stepForm["variable"]}}"
                                                       name="processVar"/>
                                                <span class="btn btn-default" onclick="closeJmenu()"
                                                   style="width: 94px;margin-bottom: 0">取消</span>
                                                <span class="btn btn-primary btnSub" data-type="saveAndSub"
                                                        style="width: 94px;margin-left: 4%;margin-bottom: 0">
                                                    提交
                                                </span>
                                                <br>&nbsp;
                                            </div>
                                        @endif
                                    </fieldset>
                                </form>
                            </div>
                        @else
                            <br/>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="col-sm-12 divBack2" style="margin-top: 10px">
    <div class="ibox float-e-margins">
        <div class="tab-content">
            <div id="tab-1" class="tab-pane active">
                <div class="full-height-scroll">
                    <div class="table-responsive" style="background-color: white">
                        <div style="border-bottom: 1px solid #F2F2F2;margin-bottom: 5px">
                            <div class="titleCss2">&nbsp;操作记录&nbsp;</div>
                        </div>
                        <table id="recordsTable" class="table-no-bordered table-fixpadding"
                               style="text-align: center;color:#6b7d86" bgcolor="#FFFFFF"
                               cellpadding="0"
                               cellspacing="0" width="100%"
                               data-pagination="false"
                               data-show-export="true"
                               data-page-size="5"
                               data-id-field="Id"
                               data-pagination-detail-h-align="right"
                               data-page-list="[10, 25, 50, 100, ALL]"
                               data-show-footer="false"
                               data-side-pagination="server"
                               data-url="/enquiry/getRecordList?enquiryId=@if($enquiry!="" && $enquiry->id){{$enquiry->id}}@endif"
                               data-response-handler="responseHandler">
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="enlargeImage" class="hide">
    <div class="img-wrap">
        <i id="closeLargeImg" class="img-close"></i>
        <img class="large-img" src=""/>
    </div>
</div>
<!-- 全局js -->
<script src="/js/jquery.min.js?v=2.1.4"></script>
<script src="/js/jquery.slimscroll.min.js"></script>
<script src="/js/plugins/layer/layer.min.js"></script>
<script src="/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script type="text/javascript" src="/render/hplus/js/contabs.js"></script>
<script type="text/javascript" src="/js/common.js?2"></script>
<script src="/js/plugins/bootstrap-table/bootstrap-table.js"></script>
<!-- put your locale files after bootstrap-table.js -->
<script src="/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>
<script src="/js/plugins/layer/laydate/laydate.js"></script>
<script>
    $('#searchInfo').bind('keypress', function (event) {
        var searchAll = document.getElementById("searchAll");
        if (event.keyCode == "13") {
            searchAll.click();
        }
    });

    var isPublic="";
    @if(!$isEdit || $enquiry->steps == 'inquiryEnd')
            location.href='/enquiry/enquiryDetail/' + $("#enquiryId").val();
    @endif

    @if($isUnitPriceEdit)
            isPublic = "/成本价";
    @endif

    $('#flowChart').click(function () {
        flowChart = layer.open({
            type: 2,
            title: false,
            closeBtn: 0, //不显示关闭按钮
            shade: [0],
            shadeClose: true,
            area: ['700px', '550px'],

            content: ['/enquiry/flowChart?currentStatus=' + $('input[name="changeStateMeans"]').val(), 'no']
        });
    });

    $(function () {
        $(".table-responsive").css({"overflow": "inherit"});
    });
    var url = "{{env('JOB_URL')}}";

    var $salesTable = $('#offersTable'), $recordsTable = $('#recordsTable'),
            $remove = $('.remove'),
            selections = [];
    function curTime() {
        return new Date().getTime();
    }

    function initTable() {
        $salesTable.bootstrapTable({
            pageSize: 5,
            striped: false,
            columns: [
                [
                    {
                        title: '序号',
                        valign: 'middle',
                        align: 'center',
                        width: '3%',
                        formatter: function (value, row, index) {
                            return index + 1;
                        }
                    },
                    {
                        title: '产品名称',
                        valign: 'middle',
                        width: '16%',
                        field: 'prodName',
                        align: 'left',
                        formatter: function (value, row, index) {
                            return "<span class='prodname'>" + substringText(row.prodName) + "</span>";
                        },
                        events: 'operateEvents'
                    },
                    {
                        title: '产品型号',
                        valign: 'middle',
                        width: '12%',
                        field: 'prodPC',
                        align: 'left',
                        formatter: function (value, row, index) {
                            return "<span class='prodPC'>" + substringText(row.prodPC) + "</span>";
                        },
                        events: 'operateEvents'
                    },
                    {
                        title: '产品描述',
                        valign: 'middle',
                        width: '20%',
                        field: 'describe',
                        align: 'left',
                        formatter: function (value, row, index) {
                            return "<span class='describe divBack'>" + substringText2(row.describe) + "</span>";
                        },
                        events: 'operateEvents'
                    },
                    {
                        field: 'amount',
                        width: '8%',
                        title: '数量/售价'+isPublic,
                        valign: 'middle',
                        align: 'left',
                        formatter:function(value, row, index){
                            var costPrice = isPublic!="" && row.costPrice!=null?"/"+row.costPrice:"";
                            return row.amount + "/" + row.unitPrice+costPrice;
                        }
                    },
                    {
                        field: 'userId',
                        width: '8%',
                        title: '最后更新人',
                        valign: 'middle',
                        align: 'left',
                        formatter:function(value, row, index){
                            return "<span class='lastUpTs'>" + row.userId + "</span>";
                        },
                        events: 'operateEvents'
                    },
                    {
                        field: '',
                        width: '17%',
                        title: '操作',
                        valign: 'middle',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return "<button class='btn btn-xsm btn-outline btn-primary'" +
                                    " onclick='editOffer(" + row.id + ")'>修改</button>" +
                                    "&nbsp;&nbsp;&nbsp;" +
                                    "<button class='btn btn-xsm btn-outline btn-primary'" +
                                    " onclick='del(" + row.id + ")'>删除</button>";
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
            },
            onLoadSuccess: function(result) { //加载成功时执行
                $(".divBack img").each(function () {
                    var src = $(this).attr("src");
                    if ((src.substr(0, 7).toLowerCase() != "http://") &&
                            (src.substr(0, 8).toLowerCase() != "https://") &&
                            (src.substr(0, 21).toLowerCase() != "data:image/png;base64")) {
                        $(this).attr("src", url + src);
                    }
                    $(this).addClass("litle-img");
                });
                var totalPrice = 0;
                if(result.total >0){
                    for (var i=0;i<result.total;i++) {
                        totalPrice += result.rows[i].amount *  result.rows[i].unitPrice;
                    }
                    var trHtml = '<tr><td></td><td></td><td></td><td align="right">总计:</td><td align="left">￥';
                        trHtml +=  totalPrice.toFixed(2)+'</td>';
                    trHtml += '<td></td><td></td></tr>';
                    $salesTable.append(trHtml);
                }
            }
        });
        $recordsTable.bootstrapTable({
            pageSize: 5,
            striped: false,
            columns: [
                [
                    {
                        title: '序号',
                        valign: 'middle',
                        align: 'center',
                        width: '3%',
                        formatter: function (value, row, index) {
                            return index + 1;
                        }
                    },
                    {
                        title: '事件',
                        valign: 'middle',
                        width: '20%',
                        field: 'recordType',
                        align: 'left',
                        events: 'operateEvents'
                    },
                    {
                        title: '事件说明',
                        valign: 'middle',
                        width: '40%',
                        field: 'instructions',
                        align: 'left',
                        formatter: function (value, row, index) {
                            return row.instructions ? "<span class='instructions'>" +
                            substringText(row.instructions) + "</span>" : "";
                        },
                        events: 'operateEvents'
                    },
                    {
                        title: '操作人',
                        valign: 'middle',
                        width: '10%',
                        field: 'userId',
                        align: 'left',
                        formatter: function (value, row, index) {
                            return "<span class='describe'>" + (row.userId) + "</span>";
                        },
                        events: 'operateEvents'
                    },
                    {
                        field: 'ts',
                        width: '20%',
                        title: '操作时间',
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
                }
            }
        });
        var custips;
        //bootstrap监听事件
        window.operateEvents = {
            'mouseover .prodname': function (e, value, row, index) {
                custips = layer.tips(row.prodName, this, {time: 0, tips: [2, '#999999'], maxWidth: 400});
            },
            'mouseleave .prodname': function (e, value, row, index) {
                layer.close(custips);
            },
            'mouseover .prodPC': function (e, value, row, index) {
                custips = layer.tips(row.prodPC, this, {time: 0, tips: [2, '#999999'], maxWidth: 400});
            },
            'mouseleave .prodPC': function (e, value, row, index) {
                layer.close(custips);
            },
            'mouseover .describe': function (e, value, row, index) {
                custips = layer.tips(row.describe, this, {time: 0, tips: [2, '#999999'], maxWidth: 400});
            },
            'mouseleave .describe': function (e, value, row, index) {
                layer.close(custips);
            },
            'mouseover .instructions': function (e, value, row, index) {
                custips = layer.tips(row.instructions, this, {time: 0, tips: [2, '#999999'], maxWidth: 400});
            },
            'mouseleave .instructions': function (e, value, row, index) {
                layer.close(custips);
            },
            'mouseover .lastUpTs': function (e, value, row, index) {
                custips = layer.tips("最后更新时间：<br>"+row.upTs, this, {time: 0, tips: [2, '#999999'], maxWidth: 400});
            },
            'mouseleave .lastUpTs': function (e, value, row, index) {
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
</script>
<script language="JavaScript" src="/js/enquirylist.js?{{\Itsm\Http\Helper\ThirdCallHelper::getTime()}}"></script>
</body>
</html>