<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>安畅网络 问题管理</title>
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
            border: 1px solid #fff;
            height: 16px;
            font-size: 14px;
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

        .error {
            color: red;
        }
        .mar_top20{
            margin-top: 20px;
        }
        .checkbox-inline input[type=checkbox]{
            margin-top: -1px;
        }

    </style>
</head>
<body>
<div>
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div id="mC1Q5" class="print_main">
                <div class="print_content" style="margin-top: -6px;border:0px;">
                    <form action="{{url('issue/issuepush')}}" method="POST" id="issueform"
                          enctype="multipart/form-data" style="width: 900px">
                        <input id="route" type="hidden" value="issuepush">
                        <fieldset>
                            {{csrf_field()}}
                            <table class="table-edit" style="color: #000000;" bgcolor="#FFFFFF" border="1"
                                   cellpadding="0" cellspacing="0" width="800px">
                                <tbody>
                                <tr>
                                    <td colspan="2">
                                        <div style="float:left;line-height: 20px;font-weight: 700;font-size: 1px;
                                        color: #3a4459;">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="bold black" align="right" style="min-width: 70px">问题单号</td>
                                    <td>
                                        <div>
                                            <input value="{{$issueNo}}" class="form-control input-sm"
                                                   name="issueNo" type="text" id="issueNo"
                                                   style="width:282px;margin-left: 10px;border: none" readonly>
                                        </div>
                                    </td>
                                    <td class="bold black" align="right">问题标题</td>
                                    <td>
                                        <div>
                                            <input class="form-control input-sm validate"
                                                   name="issueTitle" type="text" id="issueTitle"
                                                   style="width: 282px;margin-left: 10px">
                                        </div>
                                    </td>
                                </tr>
                                <td>
                                <td colspan="2"></td>
                                <tr>
                                    <td class="bold black" align="right" style="min-width: 70px">问题来源</td>
                                    <td colspan="3">
                                        <div>
                                            <div class="col-sm-10">
                                                @foreach($sourceList as $d)
                                                    <label class="checkbox-inline"><input class="sourceBox" type="checkbox" value="{{$d->Code}}" name="issueSource[]">{{$d->Means}}</label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <td>
                                <td colspan="2"></td>
                                </td>
                                <tr>
                                    <td class="bold black" align="right">问题分类
                                    </td>
                                    <td>
                                        <select class="form-control validate" name="issueCategory" id="issueCategory"
                                                style="width:282px;margin-left: 10px">
                                            <option value="">-请选择-</option>
                                            @foreach($categoryList as $d)
                                                <option value="{{$d->Code}}">{{$d->Means}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="bold black" align="right">优先级
                                    </td>
                                    <td>
                                        <select class="form-control validate" name="issuePriority" id="issuePriority"
                                                style="width:282px;margin-left: 10px">
                                            <option value="">-请选择-</option>
                                            @foreach($priorityList as $d)
                                                <option value="{{$d->Code}}">{{$d->Means}}</option>
                                            @endforeach
                                        </select>
                                    </td>

                                </tr>
                                <tr>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <td class="bold black" align="right" >问题描述</td>
                                    <td colspan="3">
                                    <textarea name="issueDescribe" id="issueDescribe" data-name='问题描述'
                                              class="form-control input-sm contentValidate validate" placeholder="请输入本次问题描述"
                                              style="width: 680px;margin-left: 10px"></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                <td class="bold black" align="right">问题审核人</td>
                                <td>
                                    <input type="hidden" id="cid" name="issueCheckUserId"　 value="">
                                    <input type="text" style="margin-left: 10px" class="form-control input-sm validate" id="issueCheckUserId" placeholder="问题审核人" autocomplete="off">
                                    <div class="input-group hiddenDiv" id="hiddenDiv" style="margin-top: -30px;margin-left:10px;background-color: white;width: 100%">
                                        <input type="text" class="form-control input-sm validate" id="checkUser" name="check" placeholder="问题审核人" autocomplete="off">
                                        <div class="input-group-btn">
                                            <ul style=" max-height: 375px; max-width: 800px; overflow: auto;width: auto; transition: all 0.3s ease 0s;" class="dropdown-menu dropdown-menu-right" role="menu"></ul>
                                        </div>
                                    </div>
                                </td>
                                </tr>
                                <tr>
                                    <td colspan="4"></td>
                                </tr>
                                <tr>
                                    <td class="bold black" align="right">问题提交人</td>
                                    <td style="padding-left: 30px">{{$issueSubmitUserId}}</td>
                                    <td class="bold black" align="right">问题提交时间</td>
                                    <td>
                                        <div>
                                            <input name='issueSubmitTs' class="form-control layer-date validate" value="{{$issueSubmitTs}}" placeholder=" YYYY-MM-DD"
                                                    style="min-width: 282px;margin-left: 10px" readonly>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <td colspan="1"></td>
                                    <td >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <input type="button" id="changesumit" class="btn btn-primary btnSub mar_top20" value="提交" style="height: 34px;">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <a type="reset" class="btn btn-primary mar_top20" onclick="closeFrame()">取消</a>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </fieldset>
                        <input type="hidden" id="triggerId" name="triggerId" value="{{isset($params["triggerId"])?$params["triggerId"]:''}}"/>
                        <input type="hidden" id="changeId" name="changeId" value="{{isset($params["changeId"])?$params["changeId"]:''}}"/>
                        <input type="hidden" id="supportId" name="supportId" value="{{isset($params["supportId"])?$params["supportId"]:''}}"/>
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
<script src="/js/change.js"></script>
{{--<script src="/js/change_detail.js"></script>--}}
<script>
    function closeFrame() {
        var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
        parent.layer.close(index); //再执行关闭
    }

    $(document).ready(function () {
        $('#issueCheckUserId').focus(function () {
            $('#hiddenDiv').removeClass('hiddenDiv');
            $('#checkUser').focus();
        });

        $('#checkUser').blur(function () {
            $('#hiddenDiv').addClass('hiddenDiv');
        });

    });
    /*查询问题审核人*/
    var globaldata;
    var contactBsSuggest = $("#checkUser").bsSuggest({
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
        url: '/issue/issueapply?code=utf-8&extras=1&roleType=issue_approval&Name=',
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
                    "Keyword": json[j].Name
                });
            }
            return data;
        }

    }).on("onSetSelectValue", function (e, keyword) {
        $('#issueCheckUserId').val(globaldata[keyword.id - 1].Name);
        $('#cid').val(globaldata[keyword.id - 1].Id);
    })

    //问题表单提交验证
    function validate(indexValidate) {
        if ($(this).hasClass("down-btn")) {
            validateMark = true;
            $('.btnSub').removeAttr('disabled');
            layer.close(indexValidate);
            return false;//防止重复提交
        }
        if (!validateMark) {//判断是否选择问题来源
            var isExist=false;
            var isChecked=false;
            $('.sourceBox').each(function () {
                isExist=true;
                if ($(this).is(':checked')) {
                    isChecked=true;
                    return false;
                }
            });
            if(!isChecked && isExist){
                layer.alert('请选择问题来源!', {icon: 2, closeBtn: false, area: '100px'});
                validateMark = true;
                $('.btnSub').removeAttr('disabled');
                layer.close(indexValidate);
                return false;
            }
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

    //问题表单提交
    var validateMark = false;
    $('.btnSub').unbind();
    $('.btnSub').click(function () {
        $(this).attr('disabled', 'disabled');
        var indexValidate = layer.load(0, {shade: false});
        var route = $('#route').val();
        validateMark = false;
        validate(indexValidate);
        //判断是只保存还是保存并审核通过或者审核不通过
        if (!validateMark) {
            $.ajax({
                type: "POST",
                data: $('#issueform').serialize(),
                url: "/issue/" + route,
                success: function (arr) {
                    if (arr.status=='ok') {
                        layer.msg('问题申请提交成功！', {icon: 1, time: 2000}, function () {
                                    closeFrame();
                                }
                        );
                    } else {
                        layer.msg('问题申请提交失败！', {icon: 2, time: 2000}, function () {
                                    closeFrame();
                                }
                        );
                    }

                }
            });
        }
    });
</script>
<!-- jQuery Validation plugin javascript-->
<script src="/js/plugins/validate/jquery.validate.min.js"></script>
<script src="/js/plugins/validate/messages_zh.min.js"></script>
<script src="/js/demo/form-validate-demo.js"></script>
</body>
</html>