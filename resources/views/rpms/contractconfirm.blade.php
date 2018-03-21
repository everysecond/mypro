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
            color: #F8AC59;
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
                    <form action="{{url('rpms/resourceContract/saveContract')}}" method="POST"
                          id="newContract" enctype="multipart/form-data" class="myform">
                        <fieldset>
                            {{csrf_field()}}
                            <table class="table-edit" style="color: #000000;" bgcolor="#FFFFFF" border="1"
                                   cellpadding="0" cellspacing="0" width="95%">
                                <tbody>
                                <tr>
                                    <td colspan="2">
                                        <div style="float:left;height: 2px;font-weight: 700;">
                                            <input type="hidden" id="contractId" name="id"
                                                   value="@if($contract!="" && $contract->id){{$contract->id}}@endif">
                                            <input type="hidden" id="thisType"
                                                   value="@if($type){{$type}}@endif">
                                            <input type="hidden" id="oneDiscount" name="oneDiscount"
                                                   value="@if($contract!="" && $contract->oneDiscount){{$contract->oneDiscount}}@endif">
                                            <input type="hidden" id="cycleDiscount" name="cycleDiscount"
                                                   value="@if($contract!="" && $contract->cycleDiscount){{$contract->cycleDiscount}}@endif">
                                            <input type="hidden" id="monthPrice"
                                                   value="@if($contract!="" && $contract->monthPrice){{$contract->monthPrice}}@endif">
                                        </div>
                                    </td>
                                </tr>
                                <tr><td colspan="4" ><span class="module">基础信息</span></td></tr>
                                @if($contract!="" && $contract->oldContractNo)
                                    <tr>
                                        <td align="right" width="15%">原合同编号：</td>
                                        <td colspan="3">
                                            <span class="fontred ml3">@if($contract!="" && $contract->oldContractNo){{$contract->oldContractNo}}@endif</span>
                                            <input name="oldContractNo" type="hidden"
                                                   value="@if($contract!="" && $contract->oldContractNo){{$contract->oldContractNo}}@endif"/>
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td align="right" width="15%">供应商：</td>
                                    <td colspan="3">
                                        <div>
                                            <input class="form-control ml3" placeholder="请输入供应商名称检索"
                                                   id="supplierName" name="supplierName" type="text"
                                                   value="@if($contract!="" && $contract->supplierId){{ \Itsm\Http\Helper\ThirdCallHelper::getSupplierName($contract->supplierId)}}@endif"
                                                   style="width:100%;" disabled="disabled">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" width="15%"><span style="color: red">*</span>合同编号：</td>
                                    <td>
                                        <input class="form-control validate ml3" id="contractNo"
                                               disabled="disabled" name="contractNo" placeholder="请输入合同编号"
                                               value="@if($contract!="" && $contract->contractNo){{$contract->contractNo}}@endif" style="width: 100%;"/>
                                    </td>
                                    <td align="right" width="15%"></span>合同类型：</td>
                                    <td width="30%">
                                        <select name="contractType" id="contractType" disabled="disabled"
                                                class="form-control ml3 " style="width:100%">
                                            @foreach($contractTypeList as $key=> $contractType)
                                                <option value="{{$key}}"
                                                        @if(($contract=="" && $key == "add")||($contract!="" && $contract->contractType == $key)) selected @endif>
                                                    {{$contractType}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" width="15%"></span>合同周期：</td>
                                    <td width="30%">
                                        <input class="form-control validateDoing ml3" type="number" min="1" max="1000"
                                               name="contractCycle" placeholder="请输入合同周期(月)" disabled="disabled"
                                               id="contractCycle"
                                               value="@if($contract!="" && $contract->contractCycle){{$contract->contractCycle}}@endif" style="width: 100%;"/>
                                    </td>
                                    <td align="right" width="15%">数据中心：</td>
                                    <td width="30%">
                                        <select name="dataCenterId" id="dataCenterId" class="form-control ml3 "
                                                disabled="disabled" style="width:100%">
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
                                        <input name="startTs" id="startTs" type="date" disabled="disabled"
                                               class="form-control validateDoing ml3 date-form"
                                               value="@if($contract!="" && $contract->startTs){{ $contract->startTs }}@endif" >
                                    </td>

                                    <td align="right" >结束日期：</td>
                                    <td >
                                        <input name="endTs" id="endTs" type="date" readonly="readonly"
                                               class="validateDoing form-control ml3 date-form" disabled="disabled"
                                               value="@if($contract!="" && $contract->endTs){{$contract->endTs}}@endif" >
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" ></span>归档编号：</td>
                                    <td >
                                        <input name="fileNo" id="fileNo"  type="text" class="form-control ml3"
                                               disabled="disabled"
                                               value="@if($contract!="" && $contract->fileNo){{$contract->fileNo}}@endif" >
                                    </td>

                                    <td align="right" >合同状态：</td>
                                    <td >
                                        <select name="status" id="status" class="form-control ml3 "
                                                disabled="disabled" style="width:100%">
                                            @foreach($statusList as $key=> $status)
                                                <option value="{{$key}}"
                                                        @if(($contract=="" && $key == "toDo")||($contract!="" && $contract->status == $key)) selected="selected" @endif>
                                                    {{$status}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" width="15%" style="vertical-align: top">合同说明：</td>
                                    <td colspan="3">
                                        <div style="height: 64px;" class="ml3">
                                            <textarea class="form-control" name="describe"
                                                      disabled="disabled" placeholder="请输入说明信息"
                                                      style="height:64px !important;resize: none">@if($contract!="" && $contract->describe){!! $contract->describe !!}@endif</textarea>
                                        </div>
                                    </td>
                                </tr>
                                <tr><td colspan="4" ><span class="module">付费信息</span></td></tr>
                                <tr>
                                    <td align="right" ></span>出账编号：</td>
                                    <td >
                                        <input name="chargeOffNo" id="chargeOffNo" type="text"
                                               class="validateDoing form-control ml3" disabled="disabled"
                                               value="@if($contract!="" && $contract->chargeOffNo){{$contract->chargeOffNo}}@endif" >
                                    </td>

                                    <td align="right" >货币类型：</td>
                                    <td >
                                        <select name="currencyType" id="currencyType" disabled="disabled"
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
                                        <select name="paymentCycle" id="paymentCycle" disabled="disabled"
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
                                        <select name="balanceCycle" id="balanceCycle" disabled="disabled"
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
                                        <select name="paymentMode" id="paymentMode" disabled="disabled"
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
                                        <input name="days" id="days" type="text" disabled="disabled"
                                               class="validateDoing form-control ml3"
                                               placeholder="请输入预付/后付费天数"
                                               value="@if($contract!="" && $contract->days){{$contract->days}}@endif" >
                                    </td>
                                </tr>
                                <tr><td colspan="4" ><span class="module">资源信息</span></td></tr>
                                <tr>
                                    <td colspan="4" >
                                        <div style="margin-top: -5px;margin-left: 15px;">
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
                                <tr class="specialTable @if($contract=='' || $count==0) hidden @endif">
                                    <td colspan="4"><span class="module">专线记录</span></td>
                                </tr>
                                <tr class="specialTable @if($contract=='' || $count==0) hidden @endif">
                                    <td colspan="4">
                                        <div style="margin-left: 15px;">
                                            <div class="tab-content">
                                                <div id="tab-1" class="tab-pane active">
                                                    <div class="full-height-scroll">
                                                        <div class="table-responsive" style="background-color: white">
                                                            <table id="specialRocordTable" class="table-no-bordered"
                                                                   style="text-align: center;color:#6b7d86"
                                                                   bgcolor="#FFFFFF"
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
                                                                   data-url="/rpms/resourceContract/specialRocordList"
                                                                   data-response-handler="responseHandler">
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                @if($contract!="" && ($contract->status == "end"||$contract->status == "toStop"))
                                    <tr><td colspan="4" ><span class="module">终止信息</span></td></tr>
                                    <tr>
                                        <td align="right" width="15%"><span style="color: red">*</span>终止类型：</td>
                                        <td width="30%">
                                            <select name="destoryType" id="destoryType" class="form-control ml3"
                                                    disabled="disabled" style="width:100%">
                                                @foreach($stopTypeList as $stopType)
                                                    <option value="{{$stopType->Code}}"
                                                            @if($stopType->Code == $contract->destoryType) selected @endif>
                                                        {{$stopType->Means}}</option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td align="right" width="15%"><span style="color: red">*</span>终止时间：</td>
                                        <td width="30%">
                                            <input name="destoryTs" id="destoryTs" type="date" disabled="disabled"
                                                   class="form-control validate ml3 date-form"
                                                   value="@if($contract!="" && $contract->destoryTs){{$contract->destoryTs}}@endif">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="right" style="vertical-align: top" width="15%">终止说明：</td>
                                        <td colspan="3">
                                            <div style="height: 64px;" class="ml3">
                                            <textarea class="form-control" name="destoryMemo" placeholder="请输入说明信息"
                                                      style="height:64px !important;resize: none" disabled="disabled" >@if($contract!="" && $contract->destoryMemo){!! $contract->destoryMemo !!}@endif</textarea>
                                            </div>
                                        </td>
                                    </tr>
                                @endif

                                @if($billCounts>0)
                                    <tr><td colspan="4" ><span class="module">相关账单</span></td></tr>
                                    <tr>
                                        <td colspan="4" >
                                            <div style="margin-top: -5px;margin-left: 15px;">
                                                <div class="tab-content">
                                                    <div id="tab-1" class="tab-pane active">
                                                        <div class="full-height-scroll">
                                                            <div class="table-responsive" style="background-color: white">
                                                                <table id="billTable" class="table-no-bordered"
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
                                                                       data-url="/rpms/resourceBill/getBillList?contractId={{$contract->id}}"
                                                                       data-response-handler="responseHandler">
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @if($confirmStr!=""&&$type == "confirm")
                                        <tr>
                                            <td colspan="4" style="padding:0 20px;word-break: break-all;word-wrap: break-word;">
                                                <span class="red">提示：</span>{{$confirmStr}}</td></tr>
                                    @endif
                                @endif

                                @if($type == "confirm")
                                <tr>
                                    <td colspan="2"></td>
                                    <td colspan="2">
                                        <a class="btn btndefault mar_top20 ml9" onclick="closeFrame()" style="width: 94px;">离开此页</a>
                                        <a class="btn btnpink mar_top20 btnSub ml10" onclick="confirmContract('cancel')" style="width: 94px;">取消终止</a>
                                        <button type="button" class="btn btnpink mar_top20 ml10" onclick="confirmContract('sure')" style="width: 94px;">审核闭单</button>
                                    </td>
                                </tr>
                                @endif
                                <tr><td colspan="4" ><span class="module">操作记录</span></td></tr>
                                <tr>
                                    <td colspan="4" >
                                        <div style="margin-top: -5px;margin-left: 15px;">
                                            <div class="tab-content">
                                                <div id="tab-1" class="tab-pane active">
                                                    <div class="full-height-scroll">
                                                        <div class="table-responsive" style="background-color: white">
                                                            <table id="recordTable" class="table-no-bordered"
                                                                   style="text-align: center;color:#6b7d86" bgcolor="#FFFFFF"
                                                                   cellpadding="0"
                                                                   cellspacing="0" width="100%"
                                                                   data-pagination="true"
                                                                   data-show-export="true"
                                                                   data-page-size="1"
                                                                   data-id-field="Id"
                                                                   data-pagination-detail-h-align="right"
                                                                   data-page-list="[10, 20, 50, 100, ALL]"
                                                                   data-show-footer="false"
                                                                   data-side-pagination="server"
                                                                   data-url="/rpms/resourceContract/recordList"
                                                                   data-response-handler="responseHandler">
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
            $(obj).val(0);
            return false;
        }
        $(obj).val(a);
        $(obj).next().next().html(b.toFixed(2));
        $("#oneDiscount").val(a);
    }

    function editCycleDiscount(obj){
        var a = $(obj).val()*1;
        a = a.toFixed(2);
        var b = $(obj).next().next().html()*1 +$("#cycleDiscount").val()*1-a;
        if(b<0){
            layer.msg("优惠金额不得大于售价！");
            $(obj).val(0);
            return false;
        }
        $(obj).val(a);
        $(obj).next().next().html(b.toFixed(2));
        $("#cycleDiscount").val(a);
    }

    //提交
    function confirmContract(type){
        var contractId = $("#contractId").val();
        var str = type == "sure"?"确定审核闭单吗":"取消终止后合同状态将改为执行中，是否确定取消终止合同？";
        layer.confirm(str, {title: "提示", btn: ['确定', '取消']},function(){
            $.ajax({
                type: "GET",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                },
                url: "/rpms/resourceContract/confirmContract?type="+type+"&&contractId="+contractId,
                success: function (data) {
                    if (data.status == 'success') {
                        parent.$('#contractTable').bootstrapTable('refresh');
                        layer.msg('操作成功！', {icon: 1,time:2000},function(){
                            closeFrame();
                        });
                    }
                }
            })
        })
    }

    var $prodTable = $('#prodTable'),
            $specialRocordTable = $('#specialRocordTable'),
            $recordTable = $('#recordTable'),
            $billTable = $('#billTable'),
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
                        align: 'left'
                    },
                    {
                        title: '单价(元/月)',
                        valign: 'middle',
                        width: '10%',
                        field: 'unitPrice',
                        align: 'right',
                        footerFormatter:function (data) {
                            var cycleDiscountVal= $("#cycleDiscount").val()*1;
                            var total= data.reduce(function(sum, row) {
                                return sum + Math.round(row.unitPrice*100)/100 * row.amount;
                            }, 0);
                            //付款周期若为空则默认为月付
                            var paymentCycle = $("#paymentCycle").val() == ""?1: $("#paymentCycle").val();
                            //合同周期若为空默认为12月
                            var contractCycle = ($("#contractCycle").val()&&$("#contractCycle").val()>0)?$("#contractCycle").val():12;
                            total = total*(paymentCycle==0?contractCycle:paymentCycle);
                            if(total!=0&&total-cycleDiscountVal<0){
                                cycleDiscountVal = 0;
                                $("#cycleDiscount").val(0);
                            }

                            total -= cycleDiscountVal;
                            var cycleDiscount = '周期优惠:'+cycleDiscountVal;
                            return cycleDiscount+'<br>周期小计:<span class="red">'+total.toFixed(2)+'</span>';
                        }
                    },
                    {
                        title: '首次费用',
                        valign: 'middle',
                        width: '10%',
                        field: 'oneCost',
                        align: 'right',
                        footerFormatter:function (data) {
                            field = this.field;
                            var oneDiscountVal= $("#oneDiscount").val()*1;
                            var total= data.reduce(function(sum, row) {
                                return sum + row.oneCost * row.amount;
                            }, 0);
                            total -=oneDiscountVal;
                            var oneDiscount = '首次优惠:'+oneDiscountVal;
                            return oneDiscount+'<br>首次小计:<span class="red" id="oneTotal">'+total.toFixed(2)+'</span>';
                        }
                    },
                ]
            ],
            queryParamsType: "undefined",
            queryParams: function queryParams(params) {
                return {
                    contractId: $('#contractId').val()
                }
            }
        });

        $specialRocordTable.bootstrapTable({
            pagination: false,
            striped: false,
            showFooter: true,
            columns: [
                [
                    {
                        title: '施工联系人',
                        valign: 'middle',
                        width: '12%',
                        field: 'contactName',
                        align: 'left'
                    },
                    {
                        title: '报障电话',
                        valign: 'middle',
                        width: '12%',
                        field: 'hotLine',
                        align: 'left'
                    },
                    {
                        title: '客户',
                        valign: 'middle',
                        align: 'left',
                        width: '14%',
                        field: 'cusName'
                    },
                    {
                        title: '订单编号',
                        valign: 'middle',
                        align: 'left',
                        width: '14%',
                        field: 'contractNo'
                    },
                    {
                        title: '订单状态',
                        valign: 'middle',
                        align: 'left',
                        width: '14%',
                        field: 'contractStatus'
                    }
                ]
            ],
            queryParamsType: "undefined",
            queryParams: function queryParams(params) {
                return {
                    contractId: $('#contractId').val()
                }
            }
        });

        $billTable.bootstrapTable({
            pageSize:5,
            striped: false,
            columns: [
                [
                    {
                        title: 'ID',
                        valign: 'middle',
                        align: 'left',
                        width: '3%',
                        field: 'id'
                    },
                    {
                        title: '账单编号',
                        valign: 'middle',
                        width: '11%',
                        align: 'left',
                        field: 'billNo'
                    },
                    {
                        title: '开始结束日期',
                        valign: 'middle',
                        width: '6%',
                        field: 'billStart',
                        align: 'center',
                        formatter: function (value, row, index) {
                            return (row.billStart != null ? row.billStart : '') + "<br>" + (row.billEnd != null ? row.billEnd:'');
                        }
                    },
                    {
                        title: '账单状态',
                        valign: 'middle',
                        align: 'center',
                        width: '4%',
                        field: 'payStatus',
                        formatter: function (value, row, index) {
                            if(row.payStatus == "new") {
                                return "<span style='color:#F8AC59'>未支付</span>";
                            }else if(row.payStatus == "success") {
                                return "<span style='color:#008000'>已支付</span>";
                            }else{
                                return "<span style='color:#FF0000'>已过期</span>"+"("+row.payStatus+"天)";
                            }
                        }
                    },
                    {
                        title: '账单金额',
                        valign: 'middle',
                        width: '6%',
                        field: 'billAmount',
                        align: 'left'
                    },
                    {
                        title: '付款时间',
                        valign: 'middle',
                        width: '6%',
                        field: 'payTs',
                        align: 'left'
                    },
                    {
                        title: '操作',
                        valign: 'middle',
                        width: '6%',
                        field: 'billExpire',
                        align: 'left',
                        formatter: function (value, row, index) {
                            var operateString = '';
                            if(row.payStatus =="new" && $("#thisType").val() == "confirm"){
                                operateString = '<a title="删除" onclick="delBill(' + row.id +')"><i class="fa fa-trash hoverred"></i></a>'+
                                        '<a onclick="newBill(' + row.id + ')"><i class="fa fa-edit hoverred ml10"></i></a>';
                            }
                            return operateString;
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

        $recordTable.bootstrapTable({
            pagination: false,
            striped: false,
            columns: [
                [
                    {
                        title: '序号',//标题  可不加
                        width: '2%',
                        align:'center',
                        formatter: function (value, row, index) {
                            return index+1;
                        }
                    },
                    {
                        title: '操作类型',
                        valign: 'middle',
                        width: '5%',
                        field: 'optType',
                        align: 'center'
                    },
                    {
                        title: '操作内容',
                        valign: 'middle',
                        align: 'center',
                        width: '50%',
                        field: 'optContent'
                    },
                    {
                        title: '操作人',
                        valign: 'middle',
                        width: '10%',
                        field: 'optId',
                        align: 'left'
                    },
                    {
                        title: '操作时间',
                        valign: 'middle',
                        width: '20%',
                        field: 'optTs',
                        align: 'left'
                    }
                ]
            ],
            queryParamsType: "undefined",
            queryParams: function queryParams(params) {
                return {
                    contractId: $('#contractId').val()
                }
            }
        });

        //bootstrap监听事件
        window.operateEvents = {
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

    function newBill(type) {
        var title = type == "new" ? "新建账单" : "编辑账单";
        layer.open({
            type: 2,
            title: title,
            area: ['700px', '500px'],
            shade: 0.2,
            content: ['/rpms/resourceBill/newBill?type=' + type]
        });
    }

    function delBill(billId) {
        layer.confirm("是否确认删除账单?",{title: "提示", btn: ['确定', '取消']}, function () {
            $.ajax({
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                },
                url: "/rpms/resourceBill/delBill/" + billId,
                success: function (data) {
                    if (data.status) {
                        layer.msg(data.msg,{time:2000});
                        $('#billTable').bootstrapTable("refresh");
                    }
                }
            })
        });
    }

</script>

</body>
</html>