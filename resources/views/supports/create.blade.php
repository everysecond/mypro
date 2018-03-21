<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>安畅网络 工单系统</title>
    <!--[if lt IE 9]>
    <meta http-equiv="refresh" content="0;ie.html"/>
    <![endif]-->
    <link rel="shortcut icon" href="favicon.ico">
    <link href="/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/css/font-awesome.min.css?v=4.4.0" rel="stylesheet">
    <link href="/css/style.css?v=4.1.0" rel="stylesheet">
    <link href="/css/font.css" rel="stylesheet" type="text/css">

    <!-- 第三方插件 -->
    <link rel="stylesheet" href="/js/plugins/kindeditor/themes/default/default.css" />
    <link rel="stylesheet" href="/js/plugins/kindeditor/plugins/code/prettify.css" />
    <!-- 自定义css -->
    <link rel="stylesheet" type="text/css" href="/css/my.css">
    <style>
        .table-edit, .table-edit td {
            border: 3px solid #fff;
            height: 32px;
            font-size: 12px;
        }

        input {
            border: 0 solid #D4D5D6;
        }

        * {
            font-size: 12px;
        }
        .tab_bq {
            width: 386px;
            position: fixed;
            z-index: 100;
            border: 2px solid #e6e4e4;
            background: #d6d3d2;
        }

        .tab_bq input {
            vertical-align: middle;
            width: 255px;
            line-height: 15px;
            padding: 0;
            height: 30px;
            border: 0;
        }
        .tab_bq li {
            vertical-align: middle;
            line-height: 28px;
            padding:0 1px 0 5px;
        }
        .tab_bq li:nth-child(even){
            background-color: #f9f9f9;
        }
        .tab_bq li:hover {
            background-color: #E8E6E6;;
        }
        .hiddenDiv {
            display: none;
        }

        .bold {
            font-size: 14px;
            font-weight: 700;
            color: darkslategray;
        }
        .iright{
            float: right;
            font-size: 18px;
            line-height: 28px;
            width:26px;
            padding: 0 8px;
            background-color: #EFEFEF;
            border-radius: 2px;
        }
        .iright:hover{background-color: #1AB394}
    </style>

</head>
<body>
<div>
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div id="mC1Q5" class="print_main">
                <div class="print_content" style="margin-top: -6px;border:0px;">
                    <form action="" method="POST" id="supform" enctype="multipart/form-data">
                        <table class="table-edit" style="color: #000000;" bgcolor="#FFFFFF" border="1"
                               cellpadding="0" cellspacing="0" width="750px">
                            <tbody>
                            <tr>
                                <td colspan="2">
                                    <div style="float:left;line-height: 35px;font-weight: 700;font-size: 14px;
                                        color: #3a4459;">工单联系人明细
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="bold black" align="right" style="min-width: 70px"><span class="red">*</span>客户：
                                </td>
                                <td>
                                    <div id="CusNameArea">
                                        <input type="text" class="form-control" id="CustomerName" name="CustomerName" readonly=""value=""placeholder="点击搜索客户">
                                        <input type="hidden" id="CustomerId" name="CustomerId" value="">
                                        <div class="tab_bq" id="check1" style="display: none;">
                                            <input value="" class="form-control" style="display: inline-block" id="search" type="text">
                                            <span class="input-group-btn" style="display: inline-block;margin-left: 10px"><a id="searchInfo"><span style="font-size: 12px;">搜索(客户名称或IP)</span></a></span>
                                            <div id="tab-1" class="input-group"
                                                 style="margin-top: 2px;background-color: white;width: 100%;
                                                 display: none;max-height: 284px;overflow-y: scroll;">
                                            </div>

                                        </div>
                                    </div>
                                </td>
                                <td class="bold black" align="right" style="">服务授权码：</td>
                                <td>
                                    <div>
                                        <input value="" class="form-control input-sm"
                                               id="Authorization" disabled="disabled" name="Authorization" type="text">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="bold black" align="right"><span class="red">*</span>联系人：</td>
                                <td>
                                    <select class="form-control validate" name="contactId" id="contactId" required="required">
                                        <option value="">请选择</option>
                                    </select>
                                </td>
                                <td class="bold black" align="right">账号LoginId：</td>
                                <td>
                                    <select class="form-control" name="userId" id="userId">
                                        <option value="">请选择</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="bold black" align="right">手机：</td>
                                <td>
                                    <input value="" class="form-control input-sm"
                                           name="mobile" type="hidden" id="hiddenMobile">
                                    <input value="" class="form-control input-sm"
                                           disabled="disabled" name="" type="text" id="cmobile">
                                </td>
                            </tr>
                            <tr>
                                <td class="bold black" align="right">邮件：</td>
                                <td>
                                    <div>
                                        <input value="" class="form-control input-sm"
                                               name="email" type="hidden" id="hiddenEmail">
                                        <input value="" class="form-control input-sm"
                                               disabled="disabled" name="" type="text" id="cemail">
                                    </div>
                                </td>
                                <td class="bold black" align="right" style="width: 150px;">证件类型：</td>
                                <td>
                                    <div>
                                        <input value="" class="form-control input-sm"
                                               disabled="disabled" name="credtype" type="text" id="ccredtype">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="bold black" align="right">电话：</td>
                                <td>
                                    <div>
                                        <input value="" class="form-control input-sm"
                                               name="tel" type="text" id="ctel">
                                    </div>
                                </td>
                                <td class="bold black" align="right"style="width: 150px;">证件号码：</td>
                                <td>
                                    <div>
                                        <input value="" class="form-control input-sm"
                                               disabled="disabled" name="credno" type="text" id="ccredno">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div style="float:left;line-height: 30px;font-weight: 700;font-size: 14px;
                                        color: #3a4459;">工单内容明细
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="bold black" align="right"><span class="red">*</span>工单标题：</td>
                                <td colspan="3">
                                    <input value="{{old('title')}}" class="form-control input-sm validate"
                                           placeholder="请输入本次工单的主题" name="title" type="text">
                                </td>
                            </tr>
                            <tr>
                                <td class="bold black" align="right"><span class="red">*</span>产品类型：</td>
                                <td>
                                    <input type="hidden" value="IDC" id="modeMark">
                                    @foreach(ThirdCallHelper::getDictArray("工单业务类型","serviceModel") as $item)
                                        <input value="{{$item->Code}}" type="radio" class="chooseOneType"
                                               @if($item->Code == 'IDC')
                                                       checked=""
                                               @endif
                                               name="serviceModel"> {{$item->Means}}
                                        &nbsp;&nbsp;&nbsp;
                                    @endforeach
                                </td>
                            </tr>
                            <tr>
                                <td class="bold black" align="right">关联设备：</td>
                                <td>
                                    <input value="" class="form-control input-sm"
                                           name="equipmentId" type="hidden" id="EquipmentId">
                                    <input value="" class="form-control input-sm"
                                           name="DevId" type="hidden" id="hiddenDevId">
                                    <input value="" class="form-control input-sm" disabled="disabled"
                                           name="" type="text" id="DevId">
                                </td>
                                <td valign="top">
                                    <a class="btn btn-primary" id="selectEquipment">点击选择设备</a>
                                </td>
                            </tr>
                            <tr>
                                <td class="bold black" align="right">数据中心：</td>
                                <td>
                                    <input type="hidden" name="datacenter" id="dataCenterName">
                                    <select class="form-control validate" name="dataCenter" id="dataCenter">
                                        <option value="">请选择</option>
                                        @foreach($dataCenter as $value)
                                            <option value="{{$value->DataCenterName}}">
                                                {{$value->DataCenterName}}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td colspan="2"><span class="red">*如果您还没有设备请选择数据中心</span></td>
                            </tr>
                            <tr>
                                <td class="bold black" align="right"><span class="red">*</span>内容：</td>
                                <td colspan="4">
                                    <textarea id="content" style="resize: none" name="content"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"></td>
                                <td>
                                    <a id="createSubmit" class="btn btn-primary">
                                        提交工单
                                    </a>
                                    <a id="reset" class="btn btn-primary">重置</a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <input type="hidden" id="triggerId" name="triggerId" value="{{isset($params["triggerId"])?$params["triggerId"]:''}}"/>
                        <input type="hidden" id="changeId" name="changeId" value="{{isset($params["changeId"])?$params["changeId"]:''}}"/>
                        <input type="hidden" id="issueId" name="issueId" value="{{isset($params["issueId"])?$params["issueId"]:''}}"/>
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
<!-- 自定义js -->
<script charset="utf-8" src="/js/supportcreate.js?1"></script>
<!-- jQuery Validation plugin javascript-->
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="/js/plugins/validate/jquery.validate.min.js"></script>
<script src="/js/plugins/validate/messages_zh.min.js"></script>
<script src="/js/demo/form-validate-demo.js"></script>
<script>
    var _flag = false; // 全局变量，用于记住鼠标是否在DIV上
    $("#CusNameArea").mouseover(function(){
        _flag = true;
    });
    $("#CusNameArea").mouseout(function(){
        _flag = false;
    });
    document.body.onclick = function (){
        if(!_flag){
            $("#check1").css("display","none");
        }
    };
    $('#search').bind('keypress',function(event){
        var searchAll = document.getElementById("searchInfo");
        if(event.keyCode == "13")
        {
            searchAll.click();
        }
    });
    $("#CustomerName").click(function () {
        $("#check1").css("display","block");
    });
    $(".chooseOneType").click(function () {
        if(this.value != $('#modeMark').val()){
            $.ajax({
                'type':'get',
                'url':'/wo/getdatacenter/'+this.value,
                'dataType':'json',
                'success':function(data){
                    var html="<option value=''>请选择</option>";
                    $.each(data,function(commentIndex,comment){
                        html += "<option value="+comment.DataCenterName+">"+comment.DataCenterName+"</option>";
                    });
                    $('#DevId').val('');
                    $('#hiddenDevId').val('');
                    $('#EquipmentId').val('');
                    $("#dataCenter").html(html).attr('disabled',false);
                    $("#dataCenterName").val('');
                }
            })
            $('#modeMark').val(this.value);
        }
    });
    $('#reset').click(function(){
        location.reload();
    });
</script>
</body>
</html>