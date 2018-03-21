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
    <link rel="stylesheet" href="/js/plugins/kindeditor/themes/default/default.css"/>
    <link rel="stylesheet" href="/js/plugins/kindeditor/plugins/code/prettify.css"/>
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

        .hiddenDiv {
            display: none;
        }

        .bold {
            font-size: 14px;
            font-weight: 700;
            color: darkslategray;
        }
    </style>

</head>
<body>
<div>
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div id="mC1Q5" class="print_main">
                <div class="print_content" style="margin-top: -6px;border:0px;">
                    <form action="" method="POST" id="supform" enctype="multipart/form-data">
                        <div style="display: none">
                            <input type="hidden" value="0" id="hid">
                            <input type="hidden" name="parentSupportId" value="{{$support->Id}}">
                            <input type="hidden" id="cid" name="CustomerId" value="{{$support->CustomerInfoId}}">
                            <input type="hidden" name="sid">
                            <input type="hidden" name="usource" value="{{$support->Source}}">
                            <input type="hidden" name="sorts" value="{{$support->priority}}">
                            <input type="hidden" name="model" value="{{$support->ServiceModel}}">
                            <input type="hidden" name="remark" value="">
                            <input type="hidden" name="contactId" value="{{$support->ContactId}}">
                            <input type="hidden" name="mobile" value="{{$support->mobile}}">
                            <input type="hidden" name="email" value="{{$support->email}}">
                            <input type="hidden" name="userId" value="{{$support->userId}}">
                            <input type="hidden" name="subType" value="split">
                        </div>
                        <table class="table-edit" style="color: #000000;" bgcolor="#FFFFFF" border="1"
                               cellpadding="0" cellspacing="0" width="700px">
                            <tbody>
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
                                    <input value="{{$support->Title}}{{$num}}" class="form-control input-sm validate"
                                           placeholder="请输入本次工单的主题" name="title" type="text">
                                </td>
                            </tr>
                            <tr>
                                <td class="bold black" align="right"><span class="red">*</span>产品类型：</td>
                                <td>
                                    <input type="hidden" value="IDC" id="modeMark">
                                    @foreach(ThirdCallHelper::getDictArray("工单业务类型","serviceModel") as $item)
                                        <input value="{{$item->Code}}" type="radio" class="chooseOneType"
                                               @if($item->Code ==$support->ServiceModel)
                                               checked=""
                                               @endif
                                               name="serviceModel"> {{$item->Means}}
                                        &nbsp;&nbsp;&nbsp;
                                    @endforeach
                                </td>
                                <td class="bold black" align="right">工单三级分类：</td>
                                <td>
                                    <select class="form-control appointValidate" name="thirdclass">
                                        <option value="">请选择</option>
                                        @foreach(ThirdCallHelper::getDictArray("工单类型","WorksheetTypeOne") as $list)
                                            <option value="{{$list->Code}}">{{$list->Means}}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="bold black" align="right">关联设备：</td>
                                <td>
                                    <input value="{{$support->EquipmentId}}" class="form-control input-sm"
                                           name="equipmentId" type="hidden" id="EquipmentId">
                                    <input value="{{$support->devIPAddr}}" class="form-control input-sm"
                                           name="DevId" type="hidden" id="hiddenDevId">
                                    <input value="{{$support->EquipmentId}}" class="form-control input-sm" disabled="disabled"
                                           name="" type="text" id="DevId">
                                </td>
                                <td  valign="top">
                                    <a class="btn btn-primary" id="selectEquipment">点击选择设备</a>
                                </td>
                            </tr>
                            <tr>
                                <td class="bold black" align="right">数据中心：</td>
                                <td>
                                    <input type="hidden" name="datacenter" id="dataCenterName"
                                           value="{{$support->dataCenter}}">
                                    <select class="form-control validate" name="dataCenter" id="dataCenter">
                                        <option value="">请选择</option>
                                        @foreach(ThirdCallHelper::getDataCenter() as $value)
                                            @if($support->dataCenter == $value->DataCenterName)
                                                <option value="{{$value->DataCenterName}}"
                                                        selected="selected">{{$value->DataCenterName}}</option>
                                            @else
                                                <option value="{{$value->DataCenterName}}">{{$value->DataCenterName}}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </td>
                                <td colspan="2"><span class="red">*如果您还没有设备请选择数据中心</span></td>
                            </tr>

                            <tr>
                                <td class="bold" align="right">第一负责人组：</td>
                                <td>
                                    <select class="form-control appointValidate" name="group1">
                                        <option value="">请选择</option>
                                        @foreach(ThirdCallHelper::getWorkGroups() as $list)
                                            <option value="{{$list->Id}}">{{$list->UsersName}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="bold" align="right" width="100px">第一负责人：</td>
                                <td>
                                    <select class="form-control" name="optuser1">
                                        <option value="">请选择</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="bold" align="right">第二负责人组：</td>
                                <td>
                                    <select class="form-control" name="group2">
                                        <option value="">请选择</option>
                                        @foreach(ThirdCallHelper::getWorkGroups() as $list)
                                            <option value="{{$list->Id}}">{{$list->UsersName}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="bold" align="right">第二负责人：</td>
                                <td>
                                    <select class="form-control" name="optuser2">
                                        <option value="">请选择</option>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td class="bold black" align="right"><span class="red">*</span>内容：</td>
                                <td colspan="4" style="">
                                    <textarea id="content" style="height: 200px;" name="content">{{$support->Body}}</textarea>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td align="right">
                                    <a id="onlyCreate" class="btn btn-primary">
                                        提交工单
                                    </a>
                                </td>
                                <td>
                                    <a id="createAndAppoint" class="btn btn-primary">
                                        提交并指派
                                    </a>
                                </td>
                                <td>
                                    <a onclick="closeFrame()" class="btn btn-primary">
                                        取消
                                    </a>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- 全局js -->
<script src="/js/jquery.min.js?v=2.1.4"></script>
<script src="/js/plugins/layer/layer.min.js"></script>
<!-- 第三方插件 -->
<script src="/render/hplus/js/content.js?v=1.0.0"></script>
<script>
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
</script>
<!-- kindeditor -->
<script charset="utf-8" src="/js/plugins/kindeditor/kindeditor-min.js"></script>
<script charset="utf-8" src="/js/plugins/kindeditor/lang/zh_CN.js"></script>
<!-- 自定义js -->
<script charset="utf-8" src="/js/supportcreate.js"></script>
</body>
</html>