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
            padding: 2px 6px !important;
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
            color: #F8AC59;
        }
        .ml15{margin-left: 15px;}
        .myform{
            width: 100%;
            padding-left: 15px;
            padding-right: 15px;
        }

    </style>
</head>
<body>
<div>
    <div class="col-sm-12" style="margin-left: 0px;padding-right: 0px;">
        <div class="ibox float-e-margins">
            <div id="mC1Q5" class="print_main">
                <div class="print_content" style="margin-top: -6px;border:0px;">
                    <form  method="POST"
                          id="newRecord" enctype="multipart/form-data" class="myform">
                        <fieldset>
                            {{csrf_field()}}
                            <table class="table-edit" style="color: #000000;" bgcolor="#FFFFFF" border="1"
                                   cellpadding="0" cellspacing="0" width="95%">
                                <tbody>
                                <tr>
                                    <td colspan="2">
                                        <div style="float:left;height: 2px;font-weight: 700;">
                                            <input type="hidden" id="contractId" name="contractId"
                                                   value="@if($prod!="" && $prod->contractId){{$prod->contractId}}@endif">
                                            <input type="hidden" id="prodId" name="prodId"
                                                   value="@if($prod!="" && $prod->id){{$prod->id}}@endif">
                                            <input type="hidden" id="prodName" name="prodName"
                                                   value="@if($prod!="" && $prod->prodName){{$prod->prodName}}@endif">
                                            <input type="hidden" id="recordId" name="recordId"
                                                   value="@if($record!="" && $record->id){{$record->id}}@endif">
                                        </div>
                                    </td>
                                </tr>
                                {{--<tr>
                                    <td align="right" width="15%"><span style="color: red">*</span>供应商：</td>
                                    <td colspan="3">
                                        <div>
                                            <input name="supplierId" id="supplierId" type="hidden"
                                                   value="@if($contract!="" && $contract->supplierId){{$contract->supplierId}}@endif">
                                            <input class="form-control ml3" placeholder="请输入供应商名称检索"
                                                   id="supplierName" name="supplierName" type="text"
                                                   value="@if($contract!="" && $contract->supplierId){{ \Itsm\Http\Helper\ThirdCallHelper::getSupplierName($contract->supplierId)}}@endif"
                                                   style="width:100%;">
                                            <div class="input-group hiddenDiv ml3" id="hiddenDiv"
                                                 style="margin-top: -28px;background-color: white;width: 100%;">
                                                <input type="text" class="form-control" id="cusname" name="cusname"
                                                       autocomplete="off" placeholder="请输入供应商名称检索"
                                                       value="@if($contract!="" && $contract->supplierId){{ \Itsm\Http\Helper\ThirdCallHelper::getSupplierName($contract->supplierId)}}@endif">
                                                <div class="input-group-btn">
                                                    <ul style=" max-height: 375px; max-width: 100%; overflow: auto;
                                            width: auto; transition: all 0.3s ease 0s;"
                                                        class="dropdown-menu dropdown-menu-right" role="menu">
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>--}}
                                {{--<tr>
                                    <td align="right" width="15%"><span style="color: red">*</span>合同编号：</td>
                                    <td>
                                        <input class="form-control validate ml3" id="contractNo" name="contractNo" placeholder="请输入合同编号"
                                               value="@if($contract!="" && $contract->contractNo){{$contract->contractNo}}@endif" style="width: 100%;"/>
                                    </td>
                                    <td align="right" width="15%"></span>合同类型：</td>
                                    <td width="30%">
                                        <select name="contractType" id="contractType" class="form-control ml3 " style="width:100%">
                                            @foreach($contractTypeList as $key=> $contractType)
                                                <option value="{{$key}}"
                                                        @if(($contract=="" && $key == "add")||($contract!="" && $contract->contractType == $key)) selected @endif>
                                                    {{$contractType}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>--}}
                                <tr>
                                    <td align="right" width="15%">专线产品：</td>
                                    <td colspan="3">
                                        <input class="form-control ml3" style="width: 100%;" name="prodName" readonly="readonly"
                                               value="@if($prod!="" && $prod->prodName){{$prod->prodName}}@endif"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" width="15%">施工方：</td>
                                    <td width="30%">
                                        <input class="form-control ml3" style="width: 100%;" name="contractor"
                                               value="@if($record!="" && $record->contractor){{$record->contractor}}@endif"/>
                                    </td>
                                    <td align="right" width="15%">施工联系人：</td>
                                    <td width="30%">
                                        <input class="form-control ml3" style="width: 100%;" name="contactName"
                                               value="@if($record!="" && $record->contactName){{$record->contactName}}@endif"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td align="right" width="15%">客户：</td>
                                    <td width="30%">
                                        <input class="form-control ml3" style="width: 100%;" name="cusName"
                                               value="@if($record!="" && $record->cusName){{$record->cusName}}@endif"/>
                                    </td>
                                    <td align="right" width="15%">报障电话：</td>
                                    <td width="30%">
                                        <input class="form-control ml3" style="width: 100%;" name="hotLine"
                                               value="@if($record!="" && $record->hotLine){{$record->hotLine}}@endif"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td align="right" width="15%">订单编号：</td>
                                    <td width="30%">
                                        <input class="form-control ml3" style="width: 100%;" name="contractNo"
                                               value="@if($record!="" && $record->contractNo){{$record->contractNo}}@endif"/>
                                    </td>
                                    <td align="right" width="15%">订单状态：</td>
                                    <td width="30%">
                                        <input class="form-control ml3" style="width: 100%;" name="contractStatus"
                                               value="@if($record!="" && $record->contractStatus){{$record->contractStatus}}@endif"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td align="right" width="15%">机房：</td>
                                    <td width="30%">
                                        <input class="form-control ml3" style="width: 100%;" name="dataCenter"
                                               value="@if($record!="" && $record->dataCenter){{$record->dataCenter}}@endif"/>
                                    </td>
                                    <td align="right" width="15%">专线运营商：</td>
                                    <td width="30%">
                                        <input class="form-control ml3" style="width: 100%;" name="provider"
                                               value="@if($record!="" && $record->provider){{$record->provider}}@endif"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" width="15%">专线类型：</td>
                                    <td width="30%">
                                        <input class="form-control ml3" style="width: 100%;" name="specialType"
                                               value="@if($record!="" && $record->specialType){{$record->specialType}}@endif"/>
                                    </td>
                                    <td align="right" width="15%">设备编号：</td>
                                    <td width="30%">
                                        <input class="form-control ml3" style="width: 100%;" name="equipmentNo"
                                               value="@if($record!="" && $record->equipmentNo){{$record->equipmentNo}}@endif"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" width="15%">数量：</td>
                                    <td width="30%">
                                        <input class="form-control ml3" style="width: 100%;" name="amount"
                                               value="@if($record!="" && $record->amount){{$record->amount}}@endif"/>
                                    </td>
                                    <td align="right" width="15%">速率：</td>
                                    <td width="30%">
                                        <input class="form-control ml3" style="width: 100%;" name="speed"
                                               value="@if($record!="" && $record->speed){{$record->speed}}@endif"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td align="right" width="15%">机房端地址：</td>
                                    <td colspan="3">
                                        <input class="form-control ml3" style="width: 100%;" name="dataCenterAddress"
                                               value="@if($record!="" && $record->dataCenterAddress){{$record->dataCenterAddress}}@endif"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td align="right" width="15%">客户端地址：</td>
                                    <td colspan="3">
                                        <input class="form-control ml3" style="width: 100%;" name="clientAddress"
                                               value="@if($record!="" && $record->clientAddress){{$record->clientAddress}}@endif"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td align="right" width="15%">计费日期：</td>
                                    <td colspan="3">
                                        <input class="form-control ml3" style="width: 100%;" name="billingDate"
                                               value="@if($record!="" && $record->billingDate){{$record->billingDate}}@endif"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td align="right" width="15%">月租：</td>
                                    <td width="30%">
                                        <input class="form-control ml3" style="width: 100%;" name="monthRental"
                                               value="@if($record!="" && $record->monthRental){{$record->monthRental}}@endif"/>
                                    </td>
                                    <td align="right" width="15%">初装：</td>
                                    <td width="30%">
                                        <input class="form-control ml3" style="width: 100%;" name="firstRental"
                                               value="@if($record!="" && $record->firstRental){{$record->firstRental}}@endif"/>
                                    </td>
                                </tr>

                                <tr>
                                    <td align="right" width="15%" style="vertical-align: top">备注：</td>
                                    <td colspan="3">
                                        <div style="height: 64px;" class="ml3">
                                            <textarea class="form-control" name="memo" placeholder="请输入备注信息"
                                                      style="height:64px !important;resize: none">@if($record!="" && $record->memo){!! $record->memo !!}@endif</textarea>
                                        </div>
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
<script src="/js/rpms/newContract.js?{{\Itsm\Http\Helper\ThirdCallHelper::getTime()}}"></script>


<script src="/js/plugins/bootstrap-table/bootstrap-table.js"></script>
<!-- put your locale files after bootstrap-table.js -->
<script src="/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>

<script type="text/javascript" src="/js/plugins/bootstrap3-editable/js/bootstrap-editable.js"></script>
<script type="text/javascript" src="/js/plugins/bootstrap-table/bootstrap-table-editable.js"></script>



<script>
    $("#contractId").val(parent.$("#contractId").val());

    //提交
    var validateMark = false;
    $('.btnSub').unbind();
    $('.btnSub').click(function () {
        $(this).attr('disabled', 'disabled');
        var indexValidate = layer.load(0, {shade: false});

        validateMark = false;
        validate(indexValidate);

        if (!validateMark) {
            $.ajax({
                type: "POST",
                data: $("#newRecord").serialize(),
                url: "/rpms/resourceContract/saveRecord",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                },
                success: function (arr) {
                    if (arr.status) {
                        layer.msg(arr.msg, {time: 2000},function(){
                            parent.$('#specialRocordTable').bootstrapTable('refresh');
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
</script>

</body>
</html>