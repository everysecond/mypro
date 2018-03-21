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
    <link rel="stylesheet" href="/js/plugins/kindeditor/themes/default/default.css"/>
    <link rel="stylesheet" href="/js/plugins/kindeditor/plugins/code/prettify.css"/>
    <!-- 自定义css -->
    <link rel="stylesheet" type="text/css" href="/css/handover.css?{{\Itsm\Http\Helper\ThirdCallHelper::getTime()}}">
    <style>
        .table-edit, .table-edit td {
            border: 1px solid #fff;
            height: 20px;
            font-size: 14px;
        }

        .hiddenDiv {
            display: none;
        }

        .dcAttr {
            display: inline-block;
        }

        * {
            font-size: 12px;
        }

        .mar_top20 {
            margin-top: 20px;
        }

    </style>
</head>
<body>
<div>
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div id="mC1Q5" class="print_main">
                <div class="print_content" style="margin-top: -6px;border:0px;">
                    <form action="{{url('enquiry/enquirySub')}}" method="POST" id="newEnquiry"
                          enctype="multipart/form-data" style="width: 900px">
                        <fieldset>
                            {{csrf_field()}}
                            <table class="table-edit" style="color: #000000;" bgcolor="#FFFFFF" border="1"
                                   cellpadding="0" cellspacing="0" width="800px">
                                <tbody>
                                <tr>
                                    <td colspan="2">
                                        <div style="float:left;height: 20px;font-weight: 700;">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right">询价编号:</td>
                                    <td colspan="3">
                                        <input type="text" class="form-control" id="enquiryNo" name="enquiryNo"
                                               style="width: 92%;margin-left: 10px;" readonly="readonly"
                                               value="@if($enquiry!="" && $enquiry->enquiryNo){{$enquiry->enquiryNo}}@else{{$enquiryNo}}@endif"/>
                                        <input type="hidden" name="enquiryId"
                                               value="@if($enquiry!="" && $enquiry->id){{$enquiry->id}}@endif"/>
                                    </td>
                                </tr>
                                <td colspan="2"></td>
                                <tr>
                                    <td align="right">询价主题:</td>
                                    <td colspan="3">
                                        <input type="text" class="form-control" id="title" name="title"
                                               value="@if($enquiry!="" && $enquiry->title){{$enquiry->title}}@endif"
                                               style="width: 92%;margin-left: 10px;"/>
                                    </td>
                                </tr>
                                <td colspan="2"></td>
                                <tr>
                                    <td align="right">客户名称:</td>
                                    <td>
                                        <div>
                                            <input name="cusId" id="CusId" type="hidden"
                                                   value="@if($enquiry!="" && $enquiry->cusId){{$enquiry->cusId}}@endif">
                                            <input class="form-control" placeholder="输入客户名搜索"
                                                   id="cusName" name="cusName" type="text"
                                                   value="@if($enquiry!=""&&$enquiry->cusName!=""){{$enquiry->cusName}}@endif"
                                                   style="width:250px;margin-left: 10px;">
                                            <div class="input-group hiddenDiv" id="hiddenDiv"
                                                 style="margin-top: -34px;background-color: white;width: 250px;margin-left: 10px;">
                                                <input type="text" class="form-control" id="cusname" name="cusname"
                                                       value="@if($enquiry!=""&&$enquiry->cusName!=""){{$enquiry->cusName}}@endif"
                                                       autocomplete="off" placeholder="输入客户名搜索">
                                                <div class="input-group-btn">
                                                    <ul style=" max-height: 375px; max-width: 800px; overflow: auto;
                                            width: auto; transition: all 0.3s ease 0s;"
                                                        class="dropdown-menu dropdown-menu-right" role="menu">
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>优先级:</td>
                                    <td>
                                        <select name="priority" id="priority" class="form-control"
                                                style="width:230px;">
                                            <option value="">-请选择-</option>
                                            <option @if($enquiry==""||$enquiry->priority=="0")selected @endif
                                            value="0">一般
                                            </option>
                                            <option @if($enquiry!=""&&$enquiry->priority=="1")selected @endif
                                            value="1">重要
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                                <td colspan="2"></td>
                                <tr>
                                    <td align="right"><span style="color: red">*</span>预计使用日期:</td>
                                    <td>
                                        <input name="expectTs" class="form-control validate" id="expectTs"
                                               value="@if($enquiry!="" && $enquiry->expectTs){{$enquiry->expectTs}}@endif"
                                               style="width:250px;margin-left: 10px;" required="required">
                                    </td>
                                    <td>预计采购金额:</td>
                                    <td>
                                        <input type="number" class="form-control" id="expectMoney" name="expectMoney"
                                               value="@if($enquiry!="" && $enquiry->expectMoney){{$enquiry->expectMoney}}@endif"
                                               style="width:230px;"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3"></td>
                                </tr>
                                <tr>
                                    <td align="right"><span style="color: red">*</span>询价内容:</td>
                                    <td colspan="3">
                                        <div style="height: 180px;width:642px;margin-left: 10px;">
                                            <textarea class="form-control msgValidate" id="body" name="body"
                                                      style="height:180px;">@if($enquiry!="" && $enquiry->body){!! $enquiry->body !!}@endif</textarea>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4"></td>
                                </tr>
                                <tr>
                                    <td colspan="2"></td>
                                    <td>
                                        <a type="reset" class="btn btn-default mar_top20" onclick="closeFrame()"
                                           style="width: 94px;">取消</a>
                                    </td>
                                    <td>
                                        <button type="reset" class="btn btn-primary mar_top20 btnSub"
                                           data-type="onlySave" style="width: 94px;">保存</button>
                                        &nbsp;&nbsp;
                                        <button class="btn btn-primary mar_top20 btnSub" data-type="saveAndSub"
                                           type="button" id="eventsumit" style="width: 94px;">保存并提交</button>
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
<!-- kindeditor -->
<script charset="utf-8" src="/js/plugins/kindeditor/kindeditor-min.js"></script>
<script charset="utf-8" src="/js/plugins/kindeditor/lang/zh_CN.js"></script>
<script src="/js/plugins/layer/laydate/laydate.js"></script>
<!-- 自定义js -->
<script src="/js/newenquiry.js?{{\Itsm\Http\Helper\ThirdCallHelper::getTime()}}"></script>
<script>

</script>
<!-- jQuery Validation plugin javascript-->
<script src="/js/plugins/validate/jquery.validate.min.js"></script>
<script src="/js/plugins/validate/messages_zh.min.js"></script>
<script src="/js/demo/form-validate-demo.js"></script>
</body>
</html>