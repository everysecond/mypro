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
            border-radius: 4px;
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

        .fixed-table-body{overflow: inherit;}
        .table-responsive{overflow: inherit;}
    </style>
</head>
<body>
<div>
    <div class="col-sm-12" style="margin-left: 0px;padding-right: 0px;">
        <div class="ibox float-e-margins">
            <div id="mC1Q5" class="print_main">
                <div class="print_content" style="margin-top: -6px;border:0px;">
                    <form action="{{url('rpms/resourceContract/saveContract')}}" method="POST" id="newContract" enctype="multipart/form-data" class="myform">
                        <fieldset>
                            {{csrf_field()}}
                            <table class="table-edit" style="color: #000000;" bgcolor="#FFFFFF" border="1"
                                   cellpadding="0" cellspacing="0" width="95%">
                                <tbody>
                                <tr>
                                    <td colspan="2">
                                        <div style="float:left;height: 2px;font-weight: 700;">
                                            <input type="hidden" id="contractId" name="id" value="">
                                            <input type="hidden" id="oldContractId" name="oldContractId" value="@if($contract!="" && $contract->id){{$contract->id}}@endif">
                                            <input type="hidden" id="oneDiscount" name="oneDiscount"
                                                   value="@if($contract!="" && $contract->oneDiscount){{$contract->oneDiscount}}@endif">
                                            <input type="hidden" id="cycleDiscount" name="cycleDiscount"
                                                   value="@if($contract!="" && $contract->cycleDiscount){{$contract->cycleDiscount}}@endif">
                                            <input type="hidden" id="monthPrice"
                                                   value="@if($contract!="" && $contract->monthPrice){{$contract->monthPrice}}@endif">
                                            <input type="hidden" id="unitCyclePrice" name="unitCyclePrice"
                                                   value="@if($contract!="" && $contract->unitCyclePrice){{$contract->unitCyclePrice}}@endif">
                                        </div>
                                    </td>
                                </tr>
                                <tr><td colspan="4" ><span class="module">基础信息</span></td></tr>
                                <tr>
                                    <td align="right" width="15%">原合同编号：</td>
                                    <td colspan="3">
                                        @if($contract!="" && $contract->contractNo)
                                            <a href='/rpms/resourceContract/contractConfirm/{{$contract->id}}?type=detail&&contractId={{$contract->id}}'
                                               class="fontred ml3" target="_blank">
                                                {{$contract->contractNo}}</a>
                                        @endif
                                        <input name="oldContractNo" type="hidden"
                                               value="@if($contract!="" && $contract->contractNo){{$contract->contractNo}}@endif"/>
                                    </td>
                                </tr>
                                <tr>
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
                                </tr>
                                <tr>
                                    <td align="right" width="15%"><span style="color: red">*</span>合同编号：</td>
                                    <td>
                                        <input class="form-control validate ml3" id="contractNo" name="contractNo" placeholder="请输入合同编号"
                                               value="" style="width: 100%;"/>
                                    </td>
                                    <td align="right" width="15%"></span>合同类型：</td>
                                    <td width="30%">
                                        <select name="contractType" id="contractType" class="form-control ml3 " style="width:100%">
                                            @foreach($contractTypeList as $key=> $contractType)
                                                <option value="{{$key}}"
                                                        @if($contract=="" && $key == "changeAdd") selected @endif>
                                                    {{$contractType}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" width="15%"></span>合同周期：</td>
                                    <td width="30%">
                                        <input class="form-control validateDoing ml3" type="number" min="1" max="1000"
                                               name="contractCycle" id="contractCycle" placeholder="请输入合同周期(月)"
                                               value="@if($contract!="" && $contract->contractCycle){{$contract->contractCycle}}@endif" style="width: 100%;"/>
                                    </td>
                                    <td align="right" width="15%">数据中心：</td>
                                    <td width="30%">
                                        <select name="dataCenterId" id="dataCenterId" class="form-control ml3 " style="width:100%">
                                            <option value="">-请选择-</option>
                                            @foreach($dataCenterList as $dataCenter)
                                                <option value="{{$dataCenter->Id}}"
                                                        @if($contract!="" && $contract->dataCenterId == $dataCenter->Id) selected @endif>
                                                    {{$dataCenter->DataCenterName}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" ></span>开始日期：</td>
                                    <td >
                                        <input name="startTs" id="startTs" type="date"
                                               class="form-control validateDoing ml3 date-form"
                                               value="@if($contract!="" && $contract->startTs){{ $contract->startTs }}@endif" >
                                    </td>

                                    <td align="right" >结束日期：</td>
                                    <td >
                                        <input name="endTs" id="endTs" type="date" readonly="readonly"
                                               class="validateDoing form-control ml3 date-form"
                                               value="@if($contract!="" && $contract->endTs){{$contract->endTs}}@endif" >
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" ></span>归档编号：</td>
                                    <td >
                                        <input name="fileNo" id="fileNo"  type="text" class="form-control ml3"
                                               value="@if($contract!="" && $contract->fileNo){{$contract->fileNo}}@endif" >
                                    </td>

                                    <td align="right" >合同状态：</td>
                                    <td >
                                        <select name="status" id="status" class="form-control ml3 " style="width:100%">
                                            @foreach($statusList as $key=> $status)
                                                <option value="{{$key}}"
                                                        @if($key == "toDo") selected="selected" @endif>
                                                    {{$status}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" width="15%">合同说明：</td>
                                    <td colspan="3">
                                        <div style="height: 64px;" class="ml3">
                                            <textarea class="form-control" name="describe" placeholder="请输入说明信息"
                                                      style="height:64px !important;resize: none">@if($contract!="" && $contract->describe){!! $contract->describe !!}@endif</textarea>
                                        </div>
                                    </td>
                                </tr>
                                <tr><td colspan="4" ><span class="module">付费信息</span></td></tr>
                                <tr>
                                    <td align="right" ></span>出账编号：</td>
                                    <td >
                                        <input name="chargeOffNo" id="chargeOffNo"  type="text"
                                               class="validateDoing form-control ml3"
                                               value="@if($contract!="" && $contract->chargeOffNo){{$contract->chargeOffNo}}@endif" >
                                    </td>

                                    <td align="right" >货币类型：</td>
                                    <td >
                                        <select name="currencyType" id="currencyType"
                                                class="validateDoing form-control ml3 " style="width:100%">
                                            <option value="">-请选择-</option>
                                            @foreach($currencyList as $key=> $currency)
                                                <option value="{{$key}}"
                                                        @if($contract!="" && $contract->currencyType == $key) selected @endif>
                                                    {{$currency}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" ></span>付费方式：</td>
                                    <td >
                                        <select name="paymentCycle" id="paymentCycle"
                                                class="validateDoing form-control ml3 " style="width:100%">
                                            <option value="">-请选择-</option>
                                            @foreach($paymentList as $key=> $payment)
                                                <option value="{{$key}}"
                                                        @if(($contract!="" &&$contract->paymentCycle ==$key )||
                                                        (($contract=="" ||(!$contract->paymentCycle &&$contract->paymentCycle!="0")) &&$key == $defaultValue["paymentCycle"])) selected @endif>
                                                    {{$payment}}</option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td align="right" >结算周期：</td>
                                    <td >
                                        <select name="balanceCycle" id="balanceCycle"
                                                class="validateDoing form-control ml3 " style="width:100%">
                                            <option value="">-请选择-</option>
                                            @foreach($balanceList as $key=> $balance)
                                                <option value="{{$key}}"
                                                        @if($contract!="" && $contract->balanceCycle == $key) selected @endif>
                                                    {{$balance}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" ></span>付费类型：</td>
                                    <td >
                                        <select name="paymentMode" id="paymentMode"
                                                class="validateDoing form-control ml3 " style="width:100%">
                                            <option value="">-请选择-</option>
                                            @foreach($paymentMode as $key=> $mode)
                                                <option value="{{$key}}"
                                                        @if($contract!="" && $contract->paymentMode == $key) selected @endif>
                                                    {{$mode}}</option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td align="right" >预/后付费：</td>
                                    <td >
                                        <input name="days" id="days"  type="text"
                                               class="validateDoing form-control ml3"
                                               placeholder="请输入预付/后付费天数"
                                               value="@if($contract!="" && $contract->days){{$contract->days}}@endif" >
                                    </td>
                                </tr>
                                <tr><td colspan="4" ><span class="module">资源信息</span></td></tr>
                                <tr><td colspan="4"><a type="reset" class="btn btnpink ml15" onclick="pickResource()" style="width: 120px;"><i class="fa fa-plus mr4 fontred"></i>选择资源产品</a></td></tr>
                                <tr>
                                    <td colspan="4" >
                                        <div style="margin-top: -20px;margin-left: 15px;">
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
                                                                   data-page-size="10"
                                                                   data-id-field="Id"
                                                                   data-pagination-detail-h-align="right"
                                                                   data-page-list="[10, 20, 50, 100, ALL]"
                                                                   data-show-footer="false"
                                                                   data-side-pagination="server"
                                                                   data-url="/rpms/resourceContract/prodList"
                                                                   data-response-handler="responseHandler">
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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
    function editOneDiscount(obj){
        var a = $(obj).val()*1;
        a = a.toFixed(2);
        var b = $(obj).next().next().html()*1 +$("#oneDiscount").val()*1-a;
        if(b<0){
            layer.msg("优惠金额不得大于售价！");
            a = 0;
            b = $(obj).next().next().html()*1 +$("#oneDiscount").val()*1
        }
        $(obj).val(a);
        $(obj).next().next().html(b.toFixed(2));
        $("#oneDiscount").val(a);
    }
    function editCycleDiscount(){
        obj = document.getElementById("editCycle");//获取优惠金标签对象
        var a = $(obj).val()*1;
        a = a.toFixed(2);
        //获取单月价格
        var monthPrice = $("#monthPrice").val();
        //获取合同周期
        var contractCycle = ($("#contractCycle").val()&&$("#contractCycle").val())>0?$("#contractCycle").val():12;
        //获取付款方式
        var paymentCycle = ($("#paymentCycle").val()!=""&&$("#paymentCycle").val()>=0)?($("#paymentCycle").val()==0?contractCycle:$("#paymentCycle").val()):1;
        var b = monthPrice*paymentCycle-a;
        $("#unitCyclePrice").val(monthPrice*paymentCycle);
        if(b<0){
            layer.msg("优惠金额不得大于售价！");
            $(obj).val(0);
            b = monthPrice*paymentCycle;
            a = 0;
        }
        $(obj).val(a);
        $(obj).next().next().html(b.toFixed(2));
        $("#cycleDiscount").val(a);
    }

    $(function(){
        $("#paymentCycle").change(function(){
            editCycleDiscount();
        });
        $("#contractCycle").change(function(){
            editCycleDiscount();
        });
    });

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
        var prodData = $("#prodTable").bootstrapTable('getData');
        if(isEmpty(prodData)){
            layer.msg("请选择资源产品！");
            return false;
        }
        $(this).attr('disabled', 'disabled');
        var indexValidate = layer.load(0, {shade: false});
        var handoverData =$('#newContract').serializeArray(),formData={};

        for (var i in handoverData) {
            var name = handoverData[i]['name'];
            formData[name] = handoverData[i].value;
        }

        validateMark = false;
        validate(indexValidate);
        if (!validateMark) {
            $.ajax({
                type: "POST",
                data: {formData,
                    'prodData':prodData
                },
                url: "/rpms/resourceContract/saveContract",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                },
                success: function (arr) {
                    if (arr.status) {
                        layer.msg(arr.msg, {time: 2000},function(){
                            parent.$('#contractTable').bootstrapTable('refresh');
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


    //选择资源产品
    function pickResource() {
        var title = "选择资源产品";
        layer.open({
            type: 2,
            title: title,
            area: ['700px', '500px'],
            shade: 0.2,
            content: ['/rpms/resourceContract/pickResource']
        });
    }

    var $prodTable = $('#prodTable'),
        selections = [];

    function initTable() {
        $prodTable.bootstrapTable({
            pagination: false,
            striped: false,
            showFooter: true,
            columns: [
                [
                    {
                        title: '资源类型',
                        valign: 'middle',
                        width: '12%',
                        field: 'prodTypeOneName',
                        align: 'left'
                    },
                    {
                        title: '资源子类型',
                        valign: 'middle',
                        width: '12%',
                        field: 'prodTypeTwoName',
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
                        title: '数量',
                        valign: 'middle',
                        width: '4%',
                        field: 'amount',
                        align: 'left',
                        editable:{
                            type:'text',
                            title:'数量只能是正整数',
                            validate:function(v){
                                if($.trim(v) == '') return '数量不能为空';
                                if($.trim(v) <=0) return '数量只能是正整数';
                                var val=v.replace('¥','');
                                if (isNaN(val)) return '数量必须是数字';
                            },
                            display: function(value) {
                                $(this).text(value);
                            },
                            mode: 'popup'
                        }
                    },
                    {
                        title: '单位',
                        valign: 'middle',
                        width: '2%',
                        field: 'unit',
                        align: 'center'
                    },
                    {
                        title: '单价(元/月)',
                        valign: 'middle',
                        width: '10%',
                        field: 'unitPrice',
                        align: 'right',
                        editable:{
                            type:'text',
                            title:'单价必须大于0',
                            validate:function(v){
                                if($.trim(v) == '') return '单价不能为空';
                                if($.trim(v)<0) return '单价必须大于等于0';
                                var val=v.replace('¥','');
                                if (isNaN(val)) return '单价必须是数字';
                            },
                            display: function(value) {
                                $(this).text((value*1).toFixed(2));
                            }
                        },
                        footerFormatter:function (data) {
                            var cycleDiscountVal= $("#cycleDiscount").val()*1;
                            var total= data.reduce(function(sum, row) {
                                return sum + Math.round(row.unitPrice*100)/100 * row.amount;
                            }, 0);
                            $("#monthPrice").val(total);
                            //付款周期若为空则默认为月付
                            var paymentCycle = $("#paymentCycle").val() == ""?1: $("#paymentCycle").val();
                            //合同周期若为空默认为12月
                            var contractCycle = ($("#contractCycle").val()&&$("#contractCycle").val()>0)?$("#contractCycle").val():12;
                            total = total*(paymentCycle==0?contractCycle:paymentCycle);
                            $("#unitCyclePrice").val(total);
                            if(total!=0&&total-cycleDiscountVal<0){
                                cycleDiscountVal = 0;
                                $("#cycleDiscount").val(0);
                            }

                            total -= cycleDiscountVal;
                            var cycleDiscount = '周期优惠:<input onchange="editCycleDiscount()" type="number" ' +
                                    'min="0" style="border:0;width:60px;height:22px !important;text-align: right;" ' +
                                    'value='+cycleDiscountVal+' id="editCycle"/>';
                            return cycleDiscount+'<br>周期小计:<span class="red">'+total.toFixed(2)+'</span>';
                        }
                    },
                    {
                        title: '首次费用',
                        valign: 'middle',
                        width: '10%',
                        field: 'oneCost',
                        align: 'right',
                        editable:{
                            type:'text',
                            title:'首次费用必须大于0',
                            validate:function(v){
                                if($.trim(v) == '') return '首次费用不能为空';
                                if($.trim(v) <0) return '首次费用必须大于等于0';
                                var val=v.replace('¥','');
                                if (isNaN(val)) return '首次费用必须是数字';
                            },
                            display: function(value) {
                                $(this).text(value && value != 'undefined'?(value*1).toFixed(2):0);
                            }
                        },
                        footerFormatter:function (data) {
                            field = this.field;
                            var oneDiscountVal= $("#oneDiscount").val()*1;
                            var total= data.reduce(function(sum, row) {
                                return sum + row.oneCost * row.amount;
                            }, 0);
                            total -=oneDiscountVal;
                            var oneDiscount = '首次优惠:<input onchange="editOneDiscount(this)" type="number"' +
                                    ' min="0" style="border:0;width:45px;height:22px !important;text-align: right;"' +
                                    ' value="'+oneDiscountVal+'" />';
                            return oneDiscount+'<br>首次小计:<span class="red" id="oneTotal">'+total.toFixed(2)+'</span>';
                        }
                    },
                    {
                        title: '操作',
                        valign: 'middle',
                        width: '7%',
                        field: 'onePrice',
                        align: 'center',
                        formatter:function(value,row,index){
                            return '<a title="删除" onclick="delContract(' + row.id + ',' + row.contractId + ')"><i class="fa fa-trash hoverred"></i></a>';
                        },
                    },
                ]
            ],
            queryParamsType: "undefined",
            queryParams: function queryParams(params) {
                return {
                    contractId: $('#oldContractId').val()
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

    initTable();

    function delContract(delId,contractId){
        layer.confirm("是否确认删除?",{title: "提示", btn: ['确定', '取消']},function(){
            if(delId && contractId){
                $.ajax({
                    type:"GET",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    },
                    url: "/rpms/resourceContract/deleteResourceProd?delId="+delId,
                    success: function (data) {
                        if (data.status == 'success') {
                            layer.msg('已删除！', {time:2000});
                            $('#prodTable').bootstrapTable("remove", {field: 'id', values: [delId]});
                        }else{
                            layer.msg(data.msg, {time:2000});
                        }
                    }
                })
            }else{
                layer.msg('已删除！', {time:2000});
                $('#prodTable').bootstrapTable("remove", {field: 'id', values: [delId]});
            }
        })
    }

</script>

</body>
</html>