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

        table tr td{
            border-bottom: 0 !important;
        }

        table:not(.table-no-bordered) tbody tr td {
            padding: 4px 6px !important;
            vertical-align: middle;
        }

        tr td:nth-child(1), tr td:nth-child(3) {
            font-family: 'PingFangSC-Regular', 'PingFang SC';
            font-weight: 400;
            font-style: normal;
            font-size: 12px;
            color: #666666;
        }

        input, .form-control {
            height: 28px !important;
        }

        .col-sm-12 {
            padding-left: 0;
            margin-left: -40px;
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

        .litle-img {
            width: 26px !important;
            height: 26px !important;
            margin: -5px 0 0 10px;
            vertical-align: middle;
            cursor: pointer;
            border:1px dashed #CA3838;
        }

        .bootstrap-table .table:not(.table-condensed){  padding:0;}

        table tbody tr td {  padding: 8px 0 8px 5px !important;  }

        tbody{max-height: 50px;}

        .pagination-detail{display: none}

        .fixed-table-pagination div.pagination{
            margin-top: 4px;
            margin-bottom: 0;
        }

        .fixed-table-container {
            border-radius: 2px; border: 1px dashed rgb(228, 228, 228) !important;
        }

        .pagination > .active > a{
            z-index: 0;
        }
    </style>
</head>
<body>
<div>
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div id="mC1Q5" class="print_main">
                <div class="print_content" style="margin-top: -6px;border:0px;">
                    <form action="{{url('rpms/resourceProvider/newProviderSub')}}" method="POST" id="newProvider"
                          enctype="multipart/form-data" style="width: 100%">
                        <fieldset>
                            {{csrf_field()}}
                            <table class="table-edit" style="color: #000000;" bgcolor="#FFFFFF" border="1"
                                   cellpadding="0" cellspacing="0" width="100%">
                                <tbody>
                                <tr>
                                    <td colspan="2">
                                        <div style="float:left;height: 2px;font-weight: 700;">
                                            <input type="hidden" name="providerId"
                                                   value="@if($provider!="" && $provider->id){{$provider->id}}@endif">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right"><span style="color: red">*</span>供应商名称：</td>
                                    <td colspan="3">
                                        <input type="text" class="form-control validate ml3" id="providerName"
                                               name="providerName" placeholder="请输入供应商名称，1到50个字符以内"
                                               value="@if($provider!="" && $provider->providerName){{$provider->providerName}}@endif"
                                               style="width: 92%;"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" width="25%">供应商类型：</td>
                                    <td width="27%">
                                        <select name="providerType" id="providerType"
                                                class="form-control ml3" style="width:100%">
                                            <option value="">-请选择-</option>
                                            @foreach($providerTypeList as $providerType)
                                                <option value="{{$providerType->Code}}"
                                                        @if($provider!="" && $provider->providerType == $providerType->Code) selected @endif>
                                                    {{$providerType->Means}}</option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td align="right" width="15%">内部负责人：</td>
                                    <td width="35%">
                                        <select name="innerCharger" id="innerCharger"
                                                class="form-control ml3" style="width:81%">
                                            <option value="">-请选择-</option>
                                            @foreach($stuffList as $stuff)
                                                <option value="{{$stuff['UserId']}}"
                                                        @if($provider!="" && $provider->innerCharger == $stuff['UserId']) selected @endif>
                                                    {{$stuff['Name']}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" width="25%">联系电话：</td>
                                    <td width="27%">
                                        <input type="text" class="form-control ml3"
                                               name="tell"
                                               value="@if($provider!="" && $provider->tell){{$provider->tell}}@endif"
                                               style="width: 100%;"/>
                                    </td>

                                    <td align="right" width="15%">传真号码：</td>
                                    <td width="35%">
                                        <input type="text" class="form-control ml3"
                                               name="fax"
                                               value="@if($provider!="" && $provider->fax){{$provider->fax}}@endif"
                                               style="width: 81%;"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" width="25%">邮编：</td>
                                    <td width="27%">
                                        <input type="text" class="form-control ml3"
                                               name="postCode"
                                               value="@if($provider!="" && $provider->postCode){{$provider->postCode}}@endif"
                                               style="width: 100%;"/>
                                    </td>

                                    <td align="right" width="15%">热线电话：</td>
                                    <td width="35%">
                                        <input type="text" class="form-control ml3"
                                               name="hotLine"
                                               value="@if($provider!="" && $provider->hotLine){{$provider->hotLine}}@endif"
                                               style="width: 81%;"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right">联系地址：</td>
                                    <td colspan="3">
                                        <input type="text" class="form-control ml3" id="address"
                                               name="address"
                                               value="@if($provider!="" && $provider->address){{$provider->address}}@endif"
                                               style="width: 92%;"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" width="25%">营业执照：</td>
                                    <td width="27%" id="imgTd">
                                        <input type="hidden" name="businessLicense"
                                               value="@if($provider!="" && $provider->businessLicense){{$provider->businessLicense}}@endif">
                                        <label class="btn btn-warning btn6 btnred ml3" for="uploadImage">上传营业执照</label>
                                        <input type="file" accept="image/*"
                                               style="position:absolute;clip:rect(0 0 0 0);"
                                               multiple="multiple" id="uploadImage" name="uploadImage"/>
                                        @if($provider!="" && $provider->businessLicense)
                                            <img src="{{$provider->businessLicense}}" class="litle-img"/>
                                        @endif
                                    </td>
                                    <td align="right" width="15%">注册资本：</td>
                                    <td width="35%">
                                        <input type="number" class="form-control ml3"
                                               name="registeredCapital" placeholder="RMB：万元"
                                               onkeypress="return(/^\d+(\.\d+)?$/.test(String.fromCharCode(event.keyCode)))"
                                               value="@if($provider!="" && $provider->registeredCapital){{$provider->registeredCapital}}@endif"
                                               style="width: 65%;display: inline"/><span class="input-group-addon form-control" style="display: inline;padding: 7px 4px;">万元</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" style="vertical-align: top;">备注说明：</td>
                                    <td colspan="3">
                                        <div style="height: 64px;width:92%;" class="ml3">
                                            <textarea class="form-control" name="describe"
                                                      placeholder="请输入说明信息"
                                                      style="height:64px !important;resize: none">@if($provider!="" && $provider->describe){!! $provider->describe !!}@endif</textarea>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            @if($provider!="" && $provider->id)
                            <div style="padding-left: 11%;width:93%;" class="mt10">
                                <div style="">
                                    <a class="btn btn-warning btn6 btnred mb1" onclick="newContact('new')">
                                        <span class="font12"></span>
                                        <i class="fa fa-plus mr3" style="color:white;"></i> 新增联系人</a>
                                </div>
                                <div class="tab-content">
                                    <div id="tab-1" class="tab-pane active">
                                        <div class="full-height-scroll">
                                            <table id="contactTable" class="table-no-bordered"
                                                   style="text-align: center;color:#6b7d86" bgcolor="#FFFFFF"
                                                   cellpadding="0"
                                                   cellspacing="0" width="100%"
                                                   data-pagination="false"
                                                   data-show-export="true"
                                                   data-page-size="100"
                                                   data-id-field="Id"
                                                   data-pagination-h-align="right"
                                                   data-pagination-v-align="top"
                                                   data-page-list="[10, 20, 50, 100, ALL]"
                                                   data-show-footer="false"
                                                   data-side-pagination="server"
                                                   data-url="/rpms/resourceProvider/getContactList?providerId={{$provider->id}}"
                                                   data-response-handler="responseHandler">
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            <div style="padding-left:55%">
                                <a type="reset" class="btn btndefault mar_top20 ml9" onclick="closeFrame()"
                                   style="width: 94px;">取消</a>
                                <button type="button" class="btn btnpink mar_top20 btnSub ml10"
                                        data-type="onlySave" style="width: 94px;">保存
                                </button>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="enlargeImage" class="hide">
    <div class="img-wrap">
        <i id="closeLargeImg" class="img-close"></i>
        <img class="large-img" src=""/>
    </div>
</div>
<!-- 全局js -->
<script src="/js/jquery.min.js?v=2.1.4"></script>
<script src="/js/plugins/layer/layer.min.js"></script>
<script src="/js/bootstrap.min.js?v=3.3.6"></script>
<!-- 第三方插件 -->
<script src="/render/hplus/js/content.js?v=1.0.0"></script>
<script type="text/javascript" src="/render/hplus/js/contabs.js"></script>
<script type="text/javascript" src="/js/common.js"></script>
<script src="/js/plugins/bootstrap-table/bootstrap-table.js"></script>
<script src="/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>
<!-- 自定义js -->
<script src="/js/rpms/newprovider.js?{{\Itsm\Http\Helper\ThirdCallHelper::getTime()}}"></script>
<script>
    //新建或编辑联系人
    function newContact(type) {
        var title = type == "new" ? "新增联系人" : "编辑联系人信息",
                area = ['560px', '340px'];
        layer.open({
            type: 2,
            title: title,
            area: area,
            shade: false,
            content: ['/rpms/resourceProvider/newContact?type=' + type]
        });
    }

    function delContact(contactId){
        layer.confirm('确定要删除该联系人吗?', {title: "提示", btn: ['确定', '取消']},function(){
            $.ajax({
                type: "GET",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                },
                url: "/rpms/resourceProvider/delContact?contactId="+contactId,
                success: function (data) {
                    if (data.status == 'success') {
                        layer.msg('已删除！', {time:2000});
                        $('#contactTable').bootstrapTable('refresh');
                    }
                }
            })
        });
    }

    // 遍历图片文件列表，插入到表单数据中
    $("#uploadImage").on("change", function () {
                var oFiles = document.querySelector("#uploadImage").files;
                //获取文件对象，files是文件选取控件的属性，存储的是文件选取控件选取的文件对象，类型是一个数组
                var fileObj = oFiles[0];
                //创建formdata对象，formData用来存储表单的数据，表单数据时以键值对形式存储的。
                var formData = new FormData();
                formData.append('imgFile', fileObj);
                var ajax = new XMLHttpRequest();
                //发送POST请求
                ajax.open("POST", "/kindeditor/uploadify", true);
                ajax.send(formData);
                ajax.onreadystatechange = function () {
                    if (ajax.readyState == 4) {
                        if (ajax.status >= 200 && ajax.status < 300 || ajax.status == 304) {
                            var obj = JSON.parse(ajax.responseText);
                            if (obj.error == 0) {
                                //上传成功后自动动创建img标签放在指定位置
                                var url = obj.url.substring(obj.url.indexOf('/upload'));
                                var img = $("<img class='litle-img' src='" + url + "' alt='' />"),
                                        tdObj = $("#imgTd");
                                tdObj.find(".litle-img").remove();
                                tdObj.append(img);
                                $("input[name='businessLicense']").val(url);
                                layer.msg("营业执照上传成功！");
                            } else {
                                layer.msg(obj.message);
                            }
                        }
                    }
                }
            }
    );

    var $contactTable = $('#contactTable'),
            selections = [];
    function initTable() {
        $contactTable.bootstrapTable({
            pageSize: 100,
            striped: false,
            columns: [
                [
                    {
                        title: '姓名',
                        valign: 'middle',
                        align: 'left',
                        width: '6%',
                        field: 'name'
                    },
                    {
                        title: '类别',
                        valign: 'middle',
                        width: '16%',
                        field: 'type',
                        align: 'left'
                    },
                    {
                        title: '联系电话',
                        valign: 'middle',
                        width: '15%',
                        field: 'tell',
                        align: 'left'
                    },
                    {
                        title: '邮箱',
                        valign: 'middle',
                        width: '16%',
                        field: 'email',
                        align: 'left'
                    },
                    {
                        title: '手机号码',
                        valign: 'middle',
                        width: '8%',
                        field: 'mobile',
                        align: 'left'
                    },
                    {
                        title: '操作',
                        valign: 'middle',
                        width: '10%',
                        field: 'mobile',
                        align: 'left',
                        formatter: function (value, row, index) {
                            return '<a onclick="delContact(' + row.id + ')">' +
                                    '<i class="fa fa fa-trash-o ml3 font16 hoverred"></i></a>'
                                    + '<a onclick="newContact(' + row.id + ')">' +
                                    '<i class="fa fa-edit ml10 font14 hoverred"></i></a>';
                        }
                    }
                ]
            ],
            queryParamsType: "undefined",
            queryParams: function queryParams(params) {
                return {
                    "providerId": $('#providerId').val()
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
    function responseHandler(res) {
        $.each(res.rows, function (i, row) {
            row.state = $.inArray(row.id, selections) !== -1;
        });
        return res;
    }

    function getHeight() {
        return $(window).height() - $('h1').outerHeight(true);
    }

    initTable();

</script>
<!-- jQuery Validation plugin javascript-->
<script src="/js/plugins/validate/jquery.validate.min.js"></script>
<script src="/js/plugins/validate/messages_zh.min.js"></script>
<script src="/js/demo/form-validate-demo.js"></script>
</body>
</html>