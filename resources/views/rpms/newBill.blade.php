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
    <link href="/css/font.css" rel="stylesheet" type="text/css">

    <link href="/js/plugins/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
    <!-- 自定义css -->
    <link rel="stylesheet" href="/css/hplusnew.css?{{\Itsm\Http\Helper\ThirdCallHelper::getTime()}}">
    <style>
        .table-edit, .table-edit td {
            border: 1px solid #fff;
            height: 20px;
            font-size: 14px;
        }

        .hiddenDiv {
            display: none;
        }

        * {
            font-size: 12px !important;
            font-family: 'PingFangSC-Regular', 'PingFang SC';
            color: #4b556a;
        }

        .mar_top20 {
            margin-top: 10px;
        }

        table tr td,table th{
            border-bottom: 0 !important;
        }

        table tbody tr td {
            padding: 8px 6px !important;
            vertical-align: middle;
        }

        tr td:nth-child(1){color:#999999;}
        tr td:nth-child(3){color:#999999;}

        input,.form-control{height:28px !important;}

        .col-sm-12{padding-left: 0;margin-left: -40px;}

        .layui-layer-tips i.layui-layer-TipsL, .layui-layer-tips i.layui-layer-TipsR {
            border-bottom-color: #fbeff2 !important;
        }

        .layui-layer-tips .layui-layer-content {
            background-color:#fbeff2 !important;
            color:#e2003b !important;
        }

        .table > tbody > tr > td, .table > tfoot > tr > td {
            border-top: 1px solid #e7eaec;
            line-height: 1.02 !important;
        }
        .table > tbody > tr:hover {
            background-color: #E4E4E4 !important;
        }

        .layui-layer-content{
            color:#e2003b !important;
        }

        .date-form{
            display: inline-block;
            width: 100%;
        }
        .module{
            margin-left: 10px;
            font-size: 14px !important;
        }
        .ml15{margin-left: 15px;}
        .myform{
            width: 100%;
            padding-left: 15px;
            padding-right: 15px;
        }

        .fixed-table-body {
            overflow: inherit;
        }

        .table-responsive {
            overflow: inherit;
        }
    </style>
</head>
<body>
<div>
    <div class="col-sm-12" style="margin-left: 0px;padding-right: 0px;">
        <div class="ibox float-e-margins">
            <div id="mC1Q5" class="print_main">
                <div class="print_content" style="margin-top: -6px;border:0px;">
                    <form method="POST" id="newBill" enctype="multipart/form-data" class="myform">
                        <fieldset>
                            {{csrf_field()}}
                            <table class="table-edit" style="color: #000000;" bgcolor="#FFFFFF" border="1"
                                   cellpadding="0" cellspacing="0" width="95%">
                                <tbody>
                                <tr>
                                    <td colspan="2">
                                        <div style="float:left;height: 2px;font-weight: 700;">
                                            <input type="hidden" id="billId" name="id" value="@if($bill!="" && $bill->id){{$bill->id}}@endif">
                                            <input type="hidden" id="billSeq" name="billSeq" value="@if($bill!="" ){{$bill->seq}}@endif">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" width="15%"><span style="color: red">*</span>合同编号：</td>
                                    <td colspan="3">
                                        <div>
                                            <input name="contractId" id="contractId" type="hidden"
                                                   value="@if($bill!="" && $bill->contractId){{$bill->contractId}}@endif">
                                            <input class="form-control ml3" placeholder="请输入合同编号检索" @if($bill!="" && ($bill->seq || $bill->seq=="0" )) readonly="true" @endif
                                                   id="contractNo" name="contractNo" type="text"
                                                   value="@if($bill!="" && $bill->contractNo){{ $bill->contractNo}}@endif"
                                                   style="width:100%;">
                                            <div class="input-group hiddenDiv ml3" id="hiddenDiv"
                                                 style="margin-top: -28px;background-color: white;width: 100%;">
                                                <input type="text" class="form-control" id="cusname" name="cusname"
                                                       autocomplete="off" placeholder="请输入合同编号检索"
                                                       value="@if($bill!="" && $bill->contractNo){{ $bill->contractNo}}@endif">
                                                <div class="input-group-btn">
                                                    <ul style=" max-height: 375px; max-width: 100%; overflow: auto;
                                            width: auto; transition: all 0.3s ease 0s;"
                                                        class="dropdown-menu dropdown-menu-right" role="menu">
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" width="15%"><span style="color: red">*</span>账单编号：</td>
                                    <td colspan="3">
                                        <input class="form-control validate ml3" id="billNo" name="billNo"  @if($bill!="" && ($bill->seq || $bill->seq=="0" )) readonly="true" @endif
                                               placeholder="请输入账单编号"
                                               value="@if($bill!="" && $bill->billNo){{$bill->billNo}}@endif" style="width: 100%;"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" ></span>开始日期：</td>
                                    <td >
                                        <input name="billStart" id="billStart" type="date"
                                               class="form-control validateDoing ml3 date-form" @if($bill!="" && ($bill->seq || $bill->seq=="0" )) readonly="true" @endif
                                               value="@if($bill!="" && $bill->billStart){{ $bill->billStart }}@endif" >
                                    </td>

                                    <td align="right" >结束日期：</td>
                                    <td >
                                        <input name="billEnd" id="billEnd" type="date"
                                               class="validateDoing form-control ml3 date-form" @if($bill!="" && ($bill->seq || $bill->seq=="0" )) readonly="true" @endif
                                               value="@if($bill!="" && $bill->billEnd){{$bill->billEnd}}@endif" >
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" ></span>账单日期：</td>
                                    <td >
                                        <input name="billExpire" id="billExpire"  type="date"
                                               class="validateDoing form-control ml3 date-form"
                                               value="@if($bill!="" && $bill->billExpire){{$bill->billExpire}}@endif" >
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <td align="right" ></span>首次费用：</td>
                                    <td >
                                        <input name="oneCost" id="oneCost"  type="text" class="form-control ml3" onblur="calculateSum()"
                                               value="@if($bill!=""){{$bill->oneCost}}@endif" >
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <td align="right" ></span>周期金额：</td>
                                    <td >
                                        <input name="cycleCost" id="cycleCost" type="text" onblur="calculateSum()"
                                               class="validateDoing form-control ml3"
                                               value="@if($bill!="" && $bill->cycleCost){{$bill->cycleCost}}@endif" >
                                    </td>

                                    <td align="right" ></span>折扣金额：</td>
                                    <td >
                                        <input name="discount" id="discount" type="text" onblur="calculateSum()"
                                               class="validateDoing form-control ml3"
                                               value="@if($bill!="" && $bill->discount){{$bill->discount}}@endif" >
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" ></span>其他类型：</td>
                                    <td >
                                        <select name="otherType" id="otherType"
                                                class="validateDoing form-control ml3 " style="width:100%">
                                            <option value="">-请选择-</option>
                                            @foreach($otherTypeList as $key=> $otherType)
                                                <option value="{{$key}}"
                                                        @if($bill!="" && $bill->otherType == $key) selected @endif>
                                                    {{$otherType}}</option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td align="right" >其他金额：</td>
                                    <td >
                                        <input name="otherAmount" id="otherAmount" type="text" onblur="calculateSum()"
                                               class="validateDoing form-control ml3"
                                               value="@if($bill!="" && $bill->otherAmount){{$bill->otherAmount}}@endif" >
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" width="15%" style="vertical-align: top">账单说明：</td>
                                    <td colspan="3">
                                        <div style="height: 64px;" class="ml3">
                                            <textarea class="form-control" name="describe" placeholder="请输入账单说明"
                                                      style="height:64px !important;resize: none">@if($bill!="" && $bill->describe){!! $bill->describe !!}@endif</textarea>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4">
                                        <span class="module">总账单金额：</span>
                                        <input name="billAmount" id="billAmount" type="hidden" value="@if($bill!="" && $bill->billAmount){{$bill->billAmount}}@endif">
                                        <span id="billTotal" class="module">@if($bill!="" && $bill->billAmount){!! $bill->billAmount !!}@endif</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="1"></td>
                                    <td>
                                        <a class="btn btndefault mar_top20 ml9" onclick="closeFrame()" style="width: 94px;">取消</a>
                                    </td>
                                    <td colspan="2">
                                        <button type="button" class="btn btnpink mar_top20 btnSub ml10" style="width: 94px;">保存</button>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- 全局js -->
<script src="/js/jquery.min.js?v=2.1.4"></script>
<script src="/js/plugins/layer/layer.min.js"></script>
<script src="/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/js/common.js"></script>
<!-- 第三方插件 -->
<script src="/render/hplus/js/content.js?v=1.0.0"></script>
<script src="/js/plugins/suggest/bootstrap-suggest.min.js"></script>
<!-- 自定义js -->
<script src="/js/rpms/newBill.js?{{\Itsm\Http\Helper\ThirdCallHelper::getTime()}}"></script>


<script src="/js/plugins/bootstrap-table/bootstrap-table.js"></script>
<!-- put your locale files after bootstrap-table.js -->
<script src="/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>

<script type="text/javascript" src="/js/plugins/bootstrap3-editable/js/bootstrap-editable.js"></script>
<script type="text/javascript" src="/js/plugins/bootstrap-table/bootstrap-table-editable.js"></script>



<script>
    var hasOwnProperty = Object.prototype.hasOwnProperty;

    function isEmpty(obj) {
        // 本身为空直接返回true
        if (obj == null) return true;

        // 然后可以根据长度判断，在低版本的ie浏览器中无法这样判断。
        if (obj.length > 0)    return false;
        if (obj.length === 0)  return true;

        //最后通过属性长度判断。
        for (var key in obj) {
            if (hasOwnProperty.call(obj, key)) return false;
        }

        return true;
    }

    //提交
    var validateMark = false;
    $('.btnSub').unbind();
    $('.btnSub').click(function () {
        $(this).attr('disabled', 'disabled');
        var indexValidate = layer.load(0, {shade: false});
        /*var handoverData =$('#newContract').serializeArray(),formData={};

        for (var i in handoverData) {
            var name = handoverData[i]['name'];
            formData[name] = handoverData[i].value;
        }*/

        validateMark = false;
        validate(indexValidate);

        if (!validateMark) {
            $.ajax({
                type: "POST",
                data: $('#newBill').serialize(),
                url: "/rpms/resourceBill/saveBill",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                },
                success: function (arr) {
                    if (arr.status) {
                        layer.msg(arr.msg, {time: 2000},function(){
                            parent.$('#billTable').bootstrapTable('refresh');
                            closeFrame();
                        });
                    } else {
                        layer.msg(arr.msg, {icon: 2, time: 2000},function(){
                            layer.close(indexValidate);
                            $(".btnSub").removeAttr('disabled');
                        });
                    }
                }
            });
        }
    });

    function calculateSum() {
        var v_oneCost =$('#oneCost').val();
        var v_cycleCost =$('#cycleCost').val();
        var v_discount =$('#discount').val();
        var v_otherAmount =$('#otherAmount').val();
        var sum = (null==v_oneCost?0:Number(v_oneCost))+(null==v_cycleCost?0:Number(v_cycleCost))-(null==v_discount?0:Number(v_discount))+(null==v_otherAmount?0:Number(v_otherAmount));

        $('#billAmount').val((null==sum?0:sum.toFixed(2)));
        $('#billTotal').html((null==sum?0:sum.toFixed(2)));
    }
</script>

</body>
</html>