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
                                            <input type="hidden" id="contractId" name="contractId" value="@if($contract!="" && $contract->id){{$contract->id}}@endif">
                                        </div>
                                    </td>
                                </tr>
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
                                    <td align="right" width="15%">合同编号：</td>
                                    <td colspan="3">
                                        <input class="form-control validate ml3" id="contractNo"
                                               name="contractNo" style="width: 100%;" disabled="disabled"
                                               value="@if($contract!="" && $contract->contractNo){{$contract->contractNo}}@endif"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" width="15%"><span style="color: red">*</span>终止类型：</td>
                                    <td width="30%">
                                        <select name="destoryType" id="destoryType" class="form-control ml3 " style="width:100%">
                                            @foreach($stopTypeList as $stopType)
                                                <option value="{{$stopType->Code}}"
                                                        @if($stopType->Code == "endReasonType") selected @endif>
                                                    {{$stopType->Means}}</option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td align="right" width="15%"><span style="color: red">*</span>终止时间：</td>
                                    <td width="30%">
                                        <input name="destoryTs" id="destoryTs" type="date"
                                               class="form-control validate ml3 date-form" >
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" width="15%">终止说明：</td>
                                    <td colspan="3">
                                        <div style="height: 64px;" class="ml3">
                                            <textarea class="form-control" name="destoryMemo" placeholder="请输入说明信息"
                                                      style="height:64px !important;resize: none"></textarea>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="1"></td>
                                    <td>
                                        <a class="btn btndefault mar_top20 ml9" onclick="closeFrame()" style="width: 94px;">取消</a>
                                    </td>
                                    <td colspan="2">
                                        <button type="button" class="btn btnpink mar_top20 btnSub ml10" style="width: 94px;">确定</button>
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
    $(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    })


    function closeFrame() {
        var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
        parent.layer.close(index); //再执行关闭
    }

    //事件表单提交验证
    function validate(indexValidate) {
        if ($(this).hasClass("down-btn")) {
            validateMark = true;
            $('.btnSub').removeAttr('disabled');
            layer.close(indexValidate);
            return false;//防止重复提交
        }
        if (!validateMark) {
            $('.validate').each(function () {
                if ($(this).val() == '') {
                    layer.msg("终止时间不能为空!",{time: 3000});
                    validateMark = true;
                    $('.btnSub').removeAttr('disabled');
                    layer.close(indexValidate);
                    return false;
                }
            });
        }
    }

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
                data: $('#newContract').serializeArray(),
                url: "/rpms/resourceContract/stopContractSub",
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



</script>

</body>
</html>