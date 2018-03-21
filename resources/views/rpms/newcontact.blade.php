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

        table tr td, table th {
            border-bottom: 0 !important;
        }

        table tbody tr td {
            padding: 8px 6px !important;
            vertical-align: middle;
        }

        tr td:nth-child(1) {
            color: #999999;
        }

        tr td:nth-child(3) {
            color: #999999;
        }

        input, .form-control {
            height: 28px !important;
        }

        .col-sm-12 {
            padding-left: 0;
            /*margin-left: -40px;*/
        }

        .layui-layer-tips i.layui-layer-TipsL, .layui-layer-tips i.layui-layer-TipsR {
            border-bottom-color: #fbeff2 !important;
        }

        .layui-layer-tips .layui-layer-content {
            background-color: #fbeff2 !important;
            color: #e2003b !important;
        }

        .table > tbody > tr > td, .table > tfoot > tr > td {
            border-top: 1px solid #e7eaec;
            line-height: 1.02 !important;
        }

        .table > tbody > tr:hover {
            background-color: #E4E4E4 !important;
        }

        .layui-layer-content {
            color: #e2003b !important;
        }
        .form-control{line-height: 2}
    </style>
</head>
<body>
<div>
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div id="mC1Q5" class="print_main">
                <div class="print_content" style="margin-top: -6px;border:0px;">
                    <form action="{{url('rpms/resourceProd/newProdSub')}}" method="POST" id="newContact"
                          enctype="multipart/form-data" style="width: 100%">
                        <fieldset>
                            {{csrf_field()}}
                            <table class="table-edit" style="color: #000000;" bgcolor="#FFFFFF" border="1"
                                   cellpadding="0" cellspacing="0" width="100%">
                                <tbody>
                                <tr>
                                    <td colspan="2">
                                        <div style="float:left;height: 2px;font-weight: 700;">
                                            <input type="hidden" name="contactId"
                                                   value="@if($contact!="" && $contact->id){{$contact->id}}@endif">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" width="18%"><span style="color: red">*</span>姓名：</td>
                                    <td width="30%">
                                        <input type="text" class="form-control validate " id="name"
                                               name="name" placeholder="联系人姓名"
                                               value="@if($contact!="" && $contact->name){{$contact->name}}@endif"
                                               style="width: 100%;"/>
                                    </td>

                                    <td align="right" width="18%"><span style="color: red">*</span>联系人类型：</td>
                                    <td width="30%">
                                        <select name="type" id="type"
                                                class="form-control validate " style="width:100%">
                                            <option value="">-请选择-</option>
                                            @foreach($contactTypeList as $contactType)
                                                <option value="{{$contactType->Code}}"
                                                        @if($contact!="" && $contact->type == $contactType->Code) selected @endif>
                                                    {{$contactType->Means}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right"><span style="color: red">*</span>手机号码：</td>
                                    <td>
                                        <select name="mobileType" id="mobileType" class="form-control "
                                                style="width: 51px;padding: 0;display: inline-block;">
                                            <option value="+86" @if(!$contact || strlen($contact->mobile) == 11) selected="true" @endif>大陆</option>
                                            <option value="" @if($contact&& strlen($contact->mobile) != 11)selected="true"@endif>海外</option>
                                        </select>
                                        <input type="text" class="form-control validate" id="mobile"
                                               name="mobile"
                                               value="@if($contact!="" && $contact->mobile){{$contact->mobile}}@endif"
                                                   style="width: 67%;display: inline-block;margin-left: -7px"/>
                                    </td>
                                    <td align="right">联系电话：</td>
                                    <td>
                                        <input type="text" class="form-control " id="tell"
                                               name="tell"
                                               value="@if($contact!="" && $contact->tell){{$contact->tell}}@endif"
                                               style="width: 100%;"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right">邮箱：</td>
                                    <td colspan="3">
                                        <input type="email" class="form-control " id="email"
                                               name="email"
                                               value="@if($contact!="" && $contact->email){{$contact->email}}@endif"
                                               style="width: 100%;">
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" style="vertical-align: top;">备注说明：</td>
                                    <td colspan="3">
                                        <div style="height: 64px;width:100%;" class="">
                                            <textarea class="form-control" name="memo"
                                                      placeholder="请输入说明信息"
                                                      style="height:64px !important;resize: none">@if($contact!="" && $contact->memo){!! $contact->memo !!}@endif</textarea>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="1"></td>
                                    <td>
                                        <a type="reset" class="btn btndefault mar_top20 ml9" onclick="closeFrame()"
                                           style="width: 94px;">取消</a>
                                    </td>
                                    <td colspan="2">
                                        <button type="button"
                                                class="btn btnpink mar_top20 btnSubContact ml10" style="width: 94px;">
                                            保存
                                        </button>
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
<script src="/js/rpms/newprovider.js?{{\Itsm\Http\Helper\ThirdCallHelper::getTime()}}"></script>
<script>

</script>
<!-- jQuery Validation plugin javascript-->
<script src="/js/plugins/validate/jquery.validate.min.js"></script>
<script src="/js/plugins/validate/messages_zh.min.js"></script>
<script src="/js/demo/form-validate-demo.js"></script>
</body>
</html>