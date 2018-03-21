<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>安畅网络 变更系统</title>
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

    </style>
</head>
<body>
<div>
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div id="mC1Q5" class="print_main">
                <div class="print_content" style="margin-top: -6px;border:0px;">
                    <form action="{{url('change/changepush')}}" method="POST" id="changeform"
                          enctype="multipart/form-data" style="width: 900px">
                        <input id="route" type="hidden" value="changepush">
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
                                    <td class="bold black" align="right" style="min-width: 70px">RFC编号</td>
                                    <td>
                                        <div>
                                            <input value="{{$changeNo}}" class="form-control input-sm"
                                                   name="RFCNO" type="hidden" id="RFCNO"
                                                   style="width:282px;margin-left: 10px;border: none">
                                            <input value="{{substr($changeNo,0,strlen($changeNo)-2)}}XX" type="text"
                                                   class="form-control input-sm" title="编号按实际写入数据库顺序生成"
                                                   style="width:282px;margin-left: 10px;border: none" readonly>
                                        </div>
                                    </td>
                                    <td class="bold black" align="right">变更标题</td>
                                    <td>
                                        <div>
                                            <input class="form-control input-sm validate"
                                                   name="changeTitle" type="text" id="changetitle"
                                                   style="width: 282px;margin-left: 10px">
                                        </div>
                                    </td>
                                </tr>
                                <td>
                                <td colspan="2"></td>
                                </td>
                                <tr>
                                    <td class="bold black" align="right" style="min-width: 70px">变更对象
                                    </td>
                                    <td>
                                        <div>
                                            <input value="" class="form-control input-sm validate"
                                                   name="changeObject" type="text" id="changeobject"
                                                   style="width:282px;margin-left: 10px">
                                        </div>
                                    </td>
                                    <td class="bold black" align="right" style="min-width: 100px">期望完成时间
                                    </td>
                                    <td>
                                        <div>
                                            <input name='expectTs' id="start" class="form-control layer-date validate"
                                                   placeholder=" YYYY-MM-DD  hh:mm:ss "
                                                   value="{!! date('Y-m-d H:i:s')  !!}"
                                                   onclick="laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss',min:laydate.now()})"
                                                   style="min-width: 282px;margin-left: 10px">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <td class="bold black" align="right">变更类型</td>
                                    <td>
                                        <div class="col-sm-12">
                                            <label class="radio-inline" for="-NaN">
                                                <input type="radio" checked="" value="general" id="-NaN"
                                                       name="changeType">一般</label>
                                            <label class="radio-inline" for="-NaN">
                                                <input type="radio" value="important" id="-NaN" name="changeType"
                                                >重大</label>
                                            <label class="radio-inline" for="-NaN">
                                                <input type="radio" value="instancy" id="-NaN" name="changeType"
                                                >紧急</label>
                                        </div>
                                    </td>
                                    <td class="bold black" align="right">触发条件</td>
                                    <td>
                                        <select class="form-control validate" name="changeCondition"
                                                id="changeCondition"
                                                style="width:282px;margin-left: 10px">
                                            <option value="">-请选择-</option>
                                            @foreach($conditionList as $state)
                                                <option value="{{$state->Code}}" @if($source==$state->Code) selected @endif>{{$state->Means}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <td class="bold black" align="right">变更类别
                                    </td>
                                    <td>
                                        <select class="form-control validate" name="changeCategory" id="changeCategory"
                                                style="width:282px;margin-left: 10px">
                                            <option value="">-请选择-</option>
                                            @foreach($changeCategory as $d)
                                                <option value="{{$d->Code}}">{{$d->Means}}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="bold black" align="right">变更子类
                                    </td>
                                    <td>
                                        <select class="form-control" name="changeSubCategory"
                                                id="changeSubCategory" style="width:282px;margin-left: 10px">
                                            <option value="">-请选择-</option>
                                        </select>
                                    </td>

                                </tr>
                                <tr>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <td class="bold black" align="right">变更原因<br>详细描述</td>
                                    <td colspan="3">
                                    <textarea name="changeReason" id="changereason" data-name='变更原因'
                                              class="form-control input-sm contentValidate" placeholder="请输入本次变更原因"
                                              style="width: 680px;margin-left: 10px"></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <td class="bold black" align="right">变更内容<br>详细描述
                                    </td>
                                    <td colspan="3">
                                        <div>
                                        <textarea name="changeContext" id="changecontext"
                                                  class="form-control contentValidate" placeholder="请输入本次变更内容"
                                                  data-name='变更内容' style="width:680px;margin-left: 10px"></textarea>
                                        </div>
                                    </td>
                                </tr>
                                <td colspan="2"></td>
                                <tr>
                                    <td class="bold black" align="right">变更风险<br>及影响分析
                                    </td>
                                    <td colspan="3">
                                        <textarea name="changeRisk" id="changerisk"
                                                  class="form-control contentValidate" data-name='变更风险及影响分析'
                                                  placeholder="请输入本次变更可能存在的风险及影响"
                                                  style="width:680px;margin-left: 10px"></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="bold black" align="right">可行性审批部门</td>
                                    <td>
                                        <select class="form-control validate" name="feasibilityGroupId"
                                                id="feasibilityGroupId" style="width:282px;margin-left: 10px">
                                            <option value="">-请选择-</option>
                                            @foreach($oneGroup as $key=>$item)
                                                <option value="{{$key}}">{{$item['name']}}</option>
                                                @if(isset($item['child'])&&is_array($item['child']))
                                                    @foreach($item['child'] as $k=>$value)
                                                        <option value="{{$k}}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$value}}</option>@endforeach
                                                @endif
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="bold black" align="right">结果验收人</td>
                                    <td>
                                        <input type="hidden" id="cid" name="checkUserId" value="">
                                        <input type="text" style="margin-left: 10px" name="checkUser"
                                               class="form-control input-sm validate" id="checkUserId"
                                               placeholder="查询验收人" autocomplete="off">
                                        <div class="input-group hiddenDiv" id="hiddenDiv"
                                             style="margin-top: -30px;margin-left:10px;background-color: white;width: 100%">
                                            <input type="text" class="form-control input-sm validate" id="checkUser"
                                                   name="check" placeholder="查询验收人" autocomplete="off">
                                            <div class="input-group-btn">
                                                <ul style=" max-height: 375px; max-width: 800px; overflow: auto;
                                            width: auto; transition: all 0.3s ease 0s;"
                                                    class="dropdown-menu dropdown-menu-right" role="menu">
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <td class="bold black" align="right">变更申请人</td>
                                    <td style="padding-left: 30px">{{$applyName}}</td>
                                    <td class="bold black" align="right">变更申请时间
                                    </td>
                                    <td>
                                        <div>
                                            <input class="form-control input-sm" value="{{$applyTime}}" name="applyTs"
                                                   style="width:282px;margin-left: 10px; border: none" autocomplete="off"  readonly >
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4"></td>
                                </tr>
                                <tr>
                                    <td colspan="1"></td>
                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <input type="button" id="changesumit" class="btn btn-primary btnSub" value="提交" style="height: 34px;">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <a type="reset" class="btn btn-primary" onclick="closeFrame()">
                                            取消
                                        </a>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </fieldset>
                        <input type="hidden" id="triggerId" name="triggerId" value="{{isset($params["triggerId"])?$params["triggerId"]:''}}"/>
                        <input type="hidden" id="issueId" name="issueId" value="{{isset($params["issueId"])?$params["issueId"]:''}}"/>
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
<script src="/js/change_detail.js"></script>
<script>
    function closeFrame() {
        var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
        parent.layer.close(index); //再执行关闭
    }
    @if(session('status'))
   layer.msg('恭喜您!变更申请提交成功！', {
        icon: 1,
        time: 2000 //2秒关闭
    }, function () {
        closeFrame();
    });
    @endif


    $(document).ready(function () {
        $('#checkUserId').focus(function () {
            $('#hiddenDiv').removeClass('hiddenDiv');
            $('#checkUser').focus();
        });

        $('#checkUser').blur(function () {
            $('#hiddenDiv').addClass('hiddenDiv');
        });

    });
    /*查询结果验证人*/
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
        url: '/change/changerefer?code=utf-8&extras=1&Name=',
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
        $('#checkUserId').val(globaldata[keyword.id - 1].Name);
        $('#cid').val(globaldata[keyword.id - 1].Id);
    })

    //变更表单提交验证
    function validate(indexValidate) {
        if ($(this).hasClass("down-btn")) {
            validateMark = true;
            $('.btnSub').removeAttr('disabled');
            layer.close(indexValidate);
            return false;//防止重复提交
        }
        $('.contentValidate').each(function () {//编辑变更驳回的文本需20字
            if ($(this).val().length < 20) {
                validateAlert($(this).data('name') + '不得少于20字');
                validateMark = true;
                $('.btnSub').removeAttr('disabled');
                layer.close(indexValidate);
                return false;
            }
        });
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

    //变更表单提交
    var validateMark = false;
    $('.btnSub').click(function () {
        $(this).attr('disabled', 'disabled');
        var indexValidate = layer.load(0, {shade: false});
        var route = $('#route').val();
        validateMark = false;
        validate(indexValidate);
        var data =$('#changeform').serialize();
        //判断是只保存还是保存并审核通过或者审核不通过
        if (!validateMark) {
            $.ajax({
                type: "GET",
                data: data,
                url: "/change/" + route,
                success: function (arr) {
                    console.log(arr);
                    if (arr.status=='ok') {
                        layer.msg('变更申请提交成功！', {icon: 1, time: 2000}, function () {
                                    closeFrame();
                                }
                        );
                    } else {
                        layer.msg(arr.msg, {icon: 2, time: 2000}, function () {
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