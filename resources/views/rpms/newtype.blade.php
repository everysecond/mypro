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
                    <form action="{{url('rpms/resourceType/newTypeSub')}}" method="POST" id="newType"
                          enctype="multipart/form-data" style="width: 630px">
                        <fieldset>
                            {{csrf_field()}}
                            <table class="table-edit" style="color: #000000;" bgcolor="#FFFFFF" border="1"
                                   cellpadding="0" cellspacing="0" width="620px">
                                <tbody>
                                <tr>
                                    <td colspan="2">
                                        <div style="float:left;height: 2px;font-weight: 700;">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right"><span style="color: red">*</span>资源类型编码：</td>
                                    <td colspan="3">
                                        <input type="text" class="form-control validate ml3" id="typeCode"
                                               name="typeCode" placeholder="请输入资源类型编码，1到50个字符以内（唯一不可重复）"
                                               value="@if($type!="" && $type->typeCode){{$type->typeCode}}@endif"
                                               @if($type != "") readonly @endif
                                               style="width: 83%;"/>
                                        <input type="hidden" name="typeId"
                                               value="@if($type!="" && $type->id){{$type->id}}@endif"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right"><span style="color: red">*</span>资源类型名称：</td>
                                    <td colspan="3">
                                        <input type="text" class="form-control validate ml3" id="typeName"
                                               name="typeName" placeholder="请输入资源类型名称，1到50个字符以内"
                                               value="@if($type!="" && $type->typeName){{$type->typeName}}@endif"
                                               style="width: 83%;"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right">上级类型：</td>
                                    <td colspan="3">
                                        <div>
                                            <input name="parentTypeCode" id="parentTypeCode" type="hidden"
                                                   value="@if($type!="" && $type->parentTypeCode){{$type->parentTypeCode}}@endif">
                                            <input class="form-control ml3" placeholder="请输入资源类型名称或编码检索"
                                                   id="parentTypeName" name="parentTypeName" type="text"
                                                   value="@if($type!="" && $type->parentTypeCode){{ \Itsm\Http\Helper\ThirdCallHelper::getProdTypeName($type->parentTypeCode)}}@endif"
                                                   style="width:83%;">
                                            <div class="input-group hiddenDiv ml3" id="hiddenDiv"
                                                 style="margin-top: -28px;background-color: white;width: 84%;">
                                                <input type="text" class="form-control" id="cusname" name="cusname"
                                                       autocomplete="off" placeholder="请输入资源类型名称或编码检索"
                                                       value="@if($type!="" && $type->parentTypeCode){{ \Itsm\Http\Helper\ThirdCallHelper::getProdTypeName($type->parentTypeCode)}}@endif">
                                                <div class="input-group-btn">
                                                    <ul style=" max-height: 375px; max-width: 83%; overflow: auto;
                                            width: auto; transition: all 0.3s ease 0s;"
                                                        class="dropdown-menu dropdown-menu-right" role="menu">
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr><td align="right">关系产品类型：</td>
                                    <td colspan="3">
                                        <select name="relateProdType" id="relateProdType"
                                                class="form-control ml3" style="width:83%">
                                            <option value="">-请选择关系产品类型-</option>
                                            @foreach($relateProdTypeList as $prodType)
                                                <option value="{{$prodType->TypeCode}}"
                                                        @if($type!="" && $type->relateProdType == $prodType->TypeCode) selected @endif>
                                                    {{$prodType->TypeName}}</option>
                                            @endforeach
                                        </select>
                                    </td></tr>
                                <tr>
                                    <td align="right" style="vertical-align: top;">资源类型描述：</td>
                                    <td colspan="3">
                                        <div style="height: 90px;width:83%;" class="ml3">
                                            <textarea class="form-control" name="describe"
                                                      placeholder="请输入资源类型描述"
                                                      style="height:90px !important;resize: none">@if($type!="" && $type->describe){!! $type->describe !!}@endif</textarea>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"></td>
                                    <td>
                                        <a type="reset" class="btn btndefault mar_top20 ml9" onclick="closeFrame()"
                                           style="width: 94px;">取消</a>
                                        <button type="button" class="btn btnpink mar_top20 btnSub"
                                           data-type="onlySave" style="width: 94px;margin-left: 30px">保存</button>
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
<script src="/js/rpms/newtype.js?{{\Itsm\Http\Helper\ThirdCallHelper::getTime()}}"></script>
<script>
    $("#cusname").change(function(){
        if($.trim($(this).val())==""){
            
        }
    })
</script>
<!-- jQuery Validation plugin javascript-->
<script src="/js/plugins/validate/jquery.validate.min.js"></script>
<script src="/js/plugins/validate/messages_zh.min.js"></script>
<script src="/js/demo/form-validate-demo.js"></script>
</body>
</html>