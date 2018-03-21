<!DOCTYPE html>
<html>

<head>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
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

        .mr15 {
            margin-left: 15px;
            float: right;
        }

    </style>
</head>
<body>
<div>
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div id="mC1Q5" class="print_main">
                <div class="print_content" style="margin-top: -6px;border:0px;">
                    <form action="{{url('enquiry/offerSub')}}" method="POST" id="newOffer"
                          enctype="multipart/form-data" style="width: 700px">
                        <fieldset>
                            {{csrf_field()}}
                            <table class="table-edit" style="color: #000000;" bgcolor="#FFFFFF" border="1"
                                   cellpadding="0" cellspacing="0" width="600px">
                                <tbody>
                                <tr>
                                    <td colspan="2">
                                        <div style="float:left;height: 20px;font-weight: 700;">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right">
                                        <span style="color: red">*</span>产品名称:
                                    </td>
                                    <td colspan="5">
                                        <div class="input-group" style="width: 93%;margin-left: 10px;">
                                            <input type="text" class="form-control validate" id="prodName" name="prodName"
                                                    autocomplete="off"
                                                   value="@if($offer!="" && $offer->prodName){{$offer->prodName}}@endif">
                                            <div class="input-group-btn">
                                                <ul style=" max-height: 375px; max-width: 800px; overflow: auto;
                                            width: auto; transition: all 0.3s ease 0s;"
                                                    class="dropdown-menu dropdown-menu-right" role="menu">
                                                </ul>
                                            </div>
                                        </div>
                                        <input type="hidden" name="enquiryId"
                                               value="@if($enquiry!="" && $enquiry->id){{$enquiry->id}}@endif"/>
                                        <input type="hidden" name="offerId"
                                               value="@if($offer!="" && $offer->id){{$offer->id}}@endif"/>
                                    </td>
                                </tr>
                                <td colspan="2"></td>
                                <tr>
                                    <td align="right">产品型号:</td>
                                    <td colspan="5">
                                        <input type="text" class="form-control" id="prodPC" name="prodPC"
                                               value="@if($offer!="" && $offer->prodPC){{$offer->prodPC}}@endif"
                                               style="width: 92%;margin-left: 10px;"/>
                                    </td>
                                </tr>
                                <td colspan="2"></td>
                                <tr>
                                    <td align="right">数量:</td>
                                    <td>
                                        <div style="width:120px;margin-left: 10px;">
                                            <input name="amount" class="form-control" id="amount" type="number"
                                                   step="0"
                                                   value="@if($offer!="" && $offer->amount){{$offer->amount}}@endif"/>
                                        </div>
                                    </td>
                                    <td class="@if($isUnitPriceEdit) 'hiddenDiv' @endif">售价:</td>
                                    <td class="@if($isUnitPriceEdit) 'hiddenDiv' @endif">
                                        <div style="width:120px;">
                                            <input type="number" class="form-control" id="unitPrice"
                                                   name="unitPrice" step="0.01"
                                                   value="@if($offer!="" && $offer->unitPrice){{$offer->unitPrice}}@endif"/>
                                        </div>
                                    </td>
                                    <td>成本价:</td>
                                    <td width="168">
                                        <div style="width:135px;">
                                            <input type="number" class="form-control" id="costPrice"
                                                   name="costPrice" step="0.01"
                                                   value="@if($offer!="" && $offer->costPrice){{$offer->costPrice}}@endif"/>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3"></td>
                                </tr>
                                <tr>
                                    <td align="right">产品描述:</td>
                                    <td colspan="5">
                                        <div style="height: 180px;width:485px;margin-left: 10px;    margin-right: 33px;">
                                            <textarea class="form-control" id="describe" name="describe"
                                                      style="height:180px;">@if($offer!="" && $offer->describe){!! $offer->describe !!}@endif</textarea>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4"></td>
                                </tr>
                                <tr>
                                    <td colspan="1"></td>
                                    <td colspan="2">
                                        <a class="btn btn-primary btnSubmm mr15"
                                                data-type="onlySave" style="width: 70px;">保存</a>
                                        <a class="btn btn-default mr15" onclick="closeFrame()"
                                           style="width: 70px;">取消</a>
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
<script charset="utf-8" src="/js/bootstrap.min.js?v=3.3.6"></script>
<!-- 第三方插件 -->
<script charset="utf-8" src="/render/hplus/js/content.js?v=1.0.0"></script>
<script charset="utf-8" src="/js/plugins/suggest/bootstrap-suggest.min.js"></script>
<!-- kindeditor -->
<script charset="utf-8" src="/js/plugins/kindeditor/kindeditor-min.js"></script>
<script charset="utf-8" src="/js/plugins/kindeditor/lang/zh_CN.js"></script>
<script src="/js/plugins/layer/laydate/laydate.js"></script>
<!-- 自定义js -->
{{--<script charset="utf-8" src="/js/newoffer.js?{{\Itsm\Http\Helper\ThirdCallHelper::getTime()}}"></script>--}}
<script charset="utf-8">
    $(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    })

    //网页编辑器
    KindEditor.options.filterMode = false;

    KindEditor.ready(function (K) {
        window.editor1 = K.create('#describe', {
            resizeType: 0,
            uploadJson: "/kindeditor/uploadfile",
            width: "100%",
            urlType: "domain",
            items: [
                'justifyleft', 'justifycenter', 'justifyright', 'forecolor', 'hilitecolor', 'bold',
                'italic', 'underline', 'image', 'insertfile'
            ], afterBlur: function () {
                this.sync();
            }
        });
    })
    /**
     * 截取文本显示长度
     * @param text
     * @param length
     */
    function stringText(text, length) {
        var length = arguments[1] ? arguments[1] : 20;
        suffix = "";
        if (text.length > length) {
            suffix = "...";
        }
        return text.substr(0, length) + suffix;
    }

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
                    layer.tips('请填写此项！', this, {time: 2000, tips: 2});
                    validateMark = true;
                    $('.btnSub').removeAttr('disabled');
                    layer.close(indexValidate);
                    return false;
                }
            });
        }
    }


    //事件表单提交
    var validateMark = false;
    $('.btnSubmm').unbind();
    $('.btnSubmm').click(function () {
        $(this).unbind();
        $(this).attr('disabled', 'disabled');
        var indexValidate = layer.load(0, {shade: false});
        validateMark = false;
        validate(indexValidate);
        //判断是只保存还是保存并审核通过或者审核不通过
        if (!validateMark) {
            $.ajax({
                type: "POST",
                data: $('#newOffer').serialize(),
                dataType: "json",
                url: "/enquiry/offerSub",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                },
                success: function (arr) {
                    console.log(arr);
                    if (arr.status) {
                        layer.msg(arr.msg, {icon: 1, time: 2000}, function () {
                            parent.$('#offersTable').bootstrapTable('refresh');
                            closeFrame();
                        });
                    } else {
                        layer.msg(arr.msg, {icon: 2, time: 2000}, function () {
                            closeFrame();
                        });
                    }

                }
            });
        }
    });

    var contactBsSuggest = $("#prodName").bsSuggest({
        indexId: 0, //data.value 的第几个数据，作为input输入框的 data-id，设为 -1 且 idField 为空则不设置此值
        indexKey: 1, //data.value 的第几个数据，作为input输入框的内容
        idField: 'ID',//每组数据的哪个字段作为 data-id，优先级高于 indexId 设置（推荐）
        keyField: 'Keyword',//每组数据的哪个字段作为输入框内容，优先级高于 indexKey 设置（推荐）
        allowNoKeyword: false, //是否允许无关键字时请求数据
        showBtn: true,
        multiWord: false, //以分隔符号分割的多关键字支持
        getDataMethod: "url", //获取数据的方式，总是从 URL 获取
        effectiveFields: ["Keyword"],
        effectiveFieldsAlias: {
            Keyword: "员工"
        },
        showHeader: false,
        url: '/enquiry/newOffer?code=utf-8&extras=1&name=',
        processData: function (json) { // url 获取数据时，对数据的处理，作为 getData 的回调函数;
            globaldata = json;
            var i, len, data = {
                value: []
            };

            if (!json || json.length == 0) {
                return false;
            }

            len = json.length;

            for (var j = 0; j < len; j++) {
                data.value.push({
                    "Id": (j + 1),
                    "Keyword": json[j].prodName
                });
            }
            return data;
        }

    }).on("onSetSelectValue", function (e, keyword) {
        $('#prodPC').val(globaldata[keyword.id - 1].prodPC);
        $('#amount').val(globaldata[keyword.id - 1].amount);
        $('#unitPrice').val(globaldata[keyword.id - 1].unitPrice);
        $('#costPrice').val(globaldata[keyword.id - 1].costPrice);
        $('#describe').text(globaldata[keyword.id - 1].describe);
        $('iframe.ke-edit-iframe').contents().find("body").text(globaldata[keyword.id - 1].describe);
    })
</script>
<!-- jQuery Validation plugin javascript-->
<script src="/js/plugins/validate/jquery.validate.min.js"></script>
<script src="/js/plugins/validate/messages_zh.min.js"></script>
<script src="/js/demo/form-validate-demo.js"></script>
</body>
</html>