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
    <link href="/css/font.css" rel="stylesheet" type="text/css">
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
    </style>
</head>
<body>
<div>
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div id="mC1Q5" class="print_main">
                <div class="print_content" style="margin-top: -6px;border:0px;">
                    <form action="{{url('rpms/resourceProd/newProdSub')}}" method="POST" id="newProd"
                          enctype="multipart/form-data" style="width: 660px">
                        <fieldset>
                            {{csrf_field()}}
                            <table class="table-edit" style="color: #000000;" bgcolor="#FFFFFF" border="1"
                                   cellpadding="0" cellspacing="0" width="620px">
                                <tbody>
                                <tr>
                                    <td colspan="2">
                                        <div style="float:left;height: 2px;font-weight: 700;">
                                            <input type="hidden" name="prodId" value="@if($prod!="" &&$prod->id){{$prod->id}}@endif">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" width="30%"><span style="color: red">*</span>资源类型：</td>
                                    <td width="25%">
                                        <select name="prodType" id="prodType"
                                                class="form-control ml3 validate" style="width:100%">
                                            <option value="">-请选择-</option>
                                            @foreach($prodTypeList as $prodType)
                                                <option value="{{$prodType->typeCode}}"
                                                        @if($prod!="" && $prod->prodType == $prodType->typeCode) selected @endif>
                                                    {{$prodType->typeName}}</option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td align="right" width="15%">资源子类型：</td>
                                    <td width="35%">
                                        <select name="sonType" id="sonType"
                                                class="form-control ml3" style="width:66%">
                                            <option value="">-请选择-</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right"><span style="color: red">*</span>资源产品名称：</td>
                                    <td colspan="3">
                                        <input type="text" class="form-control validate ml3" id="prodName"
                                               name="prodName" placeholder="请输入资源产品名称，1到50个字符以内"
                                               value="@if($prod!="" && $prod->prodName){{$prod->prodName}}@endif"
                                               style="width: 86%;"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" style="vertical-align: top;">资源产品描述：</td>
                                    <td colspan="3">
                                        <div style="height: 90px;width:86%;" class="ml3">
                                            <textarea class="form-control" name="describe"
                                                      placeholder="请输入资源产品描述"
                                                      style="height:90px !important;resize: none">@if($prod!="" && $prod->describe){!! $prod->describe !!}@endif</textarea>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right">单位：</td>
                                    <td>
                                        <input type="text" class="form-control ml3" id="unit"
                                               name="unit" placeholder="如：个/台/件"
                                               value="@if($prod!="" && $prod->unit){{$prod->unit}}@endif"
                                               style="width: 100%;"/>
                                    </td>
                                    <td align="right">是否记录：</td>
                                    <td>
                                        <select name="isSpecialLine" id="isSpecialLine" class="form-control ml3 " style="width:66%">
                                            @foreach($isSpecialLineList as $key=> $status)
                                                <option value="{{$key}}"
                                                        @if(($prod=="" && $key == "no")||($prod!="" && $prod->isSpecialLine == $key)) selected="selected" @endif>
                                                    {{$status}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right">单价：</td>
                                    <td>
                                        <input type="number" class="form-control ml3"
                                               id="unitPrice"
                                               name="unitPrice" placeholder="0.00"
                                               value="@if($prod!="" &&($prod->unitPrice || $prod->unitPrice==0)){{$prod->unitPrice}}@endif"
                                               style="width: 100%;"/>
                                    </td>
                                    <td align="right">首次费用：</td>
                                    <td>
                                        <input type="number" class="form-control ml3"
                                               id="onePrice"
                                               name="onePrice" placeholder="0.00"
                                               value="@if($prod!="" && ($prod->onePrice || $prod->onePrice==0)){{$prod->onePrice}}@endif"
                                               style="width: 66%;"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="1"></td>
                                    <td>
                                        <a type="reset" class="btn btndefault mar_top20 ml9"
                                           onclick="closeFrame()"
                                           style="width: 94px;">取消</a>
                                    </td>
                                    <td colspan="2">
                                        <button type="button" data-type="onlySave" style="width: 94px;"
                                                            class="btn btnpink mar_top20 btnSub ml10" >保存
                                        </button></td>
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
<script src="/js/rpms/newprod.js?{{\Itsm\Http\Helper\ThirdCallHelper::getTime()}}"></script>
<script>
    $(function(){
        $("select[name='prodType']").on("change",function(event,uid){
            var code = $(this).find("option:selected").val();
            $("select[name='sonType']").empty().append('<option value="">请选择</option>');
            if(code=="")return;
            $.ajax({
                type:"GET",
                url:"/rpms/resourceProd/getSonType?type="+encodeURIComponent(code),
                success:function(data){
                    if(data){
                        for(var i=0;i<data.length;i++){
                            $("select[name='sonType']").append('<option value="'+data[i].typeCode+'"'+
                                    (uid==data[i].typeCode?"selected":"")+'>'+data[i].typeName+'</option>');
                        }
                    }
                }
            });
        });

        var gid = $("select[name='prodType']").find("option:selected").val();
        if(gid!=''){
            var uid = '{{$prod && $prod->sonType?$prod->sonType:""}}';
            console.log(uid);
            $("select[name='prodType']").trigger("change",[uid]);
        }
    });

</script>
<!-- jQuery Validation plugin javascript-->
<script src="/js/plugins/validate/jquery.validate.min.js"></script>
<script src="/js/plugins/validate/messages_zh.min.js"></script>
<script src="/js/demo/form-validate-demo.js"></script>
</body>
</html>