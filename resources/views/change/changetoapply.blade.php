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
    <link rel="stylesheet" type="text/css" href="/css/change_detail.css">
    <style>
        .table-edit, .table-edit td {
            border: 3px solid #fff;
            height: 20px;
            font-size: 14px;
        }

        input {
            border: 0 solid #D4D5D6;
            vertical-align: middle;
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

        .pmOutput {
            font-size: 14px;
            color: #000000;
        }

        .reply-opt .reply-btn:hover {
            cursor: pointer;
            background-color: #1bcbab;
        }

        .reply-opt .reply-btn {
            display: inline-block;
            width: 80px;
            height: 35px;
            line-height: 0px;
            text-align: center;
            color: #ffffff;
            border-radius: 3px;
            background-color: #19b492;
            margin-bottom: 0px;
        }

        .reply-opt .down-btn {
            display: inline-block;
            width: 80px;
            height: 35px;
            line-height: 0px;
            text-align: center;
            color: #ffffff;
            border-radius: 3px;
            background-color: #aeb3b4;
            margin-bottom: 0px;
        }
    </style>
</head>
<body>
<div>
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div id="mC1Q5" class="print_main">
                <div class="print_content" style="border:0px;">
                    <form id="myform" enctype="multipart/form-data" style="width: 100%">
                        <input id="route" type="hidden" value="saveToapplydata">
                        <input type="hidden" name="changeId" value="{{$change->Id}}"/>
                        <input type="hidden" name="changeState" value="{{$change->changeState}}"/>
                        <input type="hidden" id="changeType" name="changeType" value="{{$change->changeType}}"/>
                        <input type="hidden" name="changeStateMeans"
                               value="{{ThirdCallHelper::getDictMeans('变更状态','changeState',$change->changeState)}}"/>
                        {{csrf_field()}}

                        <table class="table-edit" style="color: #000000;" bgcolor="#FFFFFF" border="1"
                               cellpadding="0" cellspacing="0" width="80%">
                            <div style="font-size: 16px;line-height: 55px;border-bottom:1px solid #f3f3f3;margin-bottom:20px; ;">
                                RFC编号:{{$change->RFCNO}}</div>
                            <input id="route" type="hidden" value="saveToapply">

                            <tr>
                                <td class="bold black" align="right">RFC编号
                                </td>
                                <td style="padding-left: 30px">{{$change->RFCNO}}
                                </td>
                                <td class="bold black" align="right">变更标题</td>
                                <td colspan="1">
                                    <input value="{{$change->changeTitle}}" maxlength="50"
                                           class="form-control input-sm validate" name="changeTitle" type="text"
                                           id="changetitle"
                                           style="width: 240px;margin-left: 10px">
                                </td>
                            </tr>
                            <td>
                            <td colspan="2"></td>
                            </td>
                            <tr>
                                <td class="bold black" align="right">变更对象
                                </td>
                                <td>
                                    <div>
                                        <input value="{{$change->changeObject}}" class="form-control input-sm validate"
                                               maxlength="50"
                                               name="changeObject" type="text" id="changeobject"
                                               style="width:240px;margin-left: 10px">
                                    </div>
                                </td>
                                <td class="bold black" align="right">期望完成时间
                                </td>
                                <td>
                                    <div>
                                        <input name='expectTs' value="{{$change->expectTs}}"
                                               class="form-control layer-date validate"
                                               placeholder=" YYYY-MM-DD  hh:mm:ss "
                                               onclick="laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss'})"
                                               style="max-width: 240px;margin-left: 10px">
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
                                        @foreach($changeTypeArr as $type)
                                            @if( $change->changeType== $type->Code)
                                                <label class="radio-inline" for="-NaN">
                                                    <input type="radio" checked="" value="{{$type->Code}}" id="-NaN"
                                                           name="changeType">
                                                    {{$type->Means}}</label>
                                            @else
                                                <label class="radio-inline" for="-NaN">
                                                    <input type="radio" value="{{$type->Code}}" id="-NaN"
                                                           name="changeType">
                                                    {{$type->Means}}</label>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                                <td class="bold black" align="right">触发条件</td>
                                <td>
                                    <select class="form-control validate validate" name="changeCondition"
                                            id="changeCondition"
                                            style="width:240px;margin-left: 10px">
                                        <option value="">==请选择==</option>
                                        @foreach($conditionList as $state)
                                            <option value="{{$state->Code}}"
                                                    @if($state->Code == $change->changeCondition) selected @endif
                                            >{{$state->Means}}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"></td>
                            </tr>
                            <tr colspan="1">
                                <td class="bold black" align="right">变更类别
                                </td>
                                <td>
                                    <select class="form-control validate" name="changeCategory" id="changeCategory"
                                            style="width:240px;margin-left: 10px">
                                        <option value="">==请选择==</option>
                                        @foreach($changeCategory as $d)
                                            <option value="{{$d->Code}}"
                                                    @if($d->Code == $change->changeCategory) selected @endif
                                            >{{$d->Means}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="bold black" align="right">变更子类
                                </td>
                                <td>
                                    <select class="form-control" name="changeSubCategory"
                                            id="changeSubCategory" style="width:240px;margin-left: 10px">
                                        <option value="">-请选择-</option>
                                        <option value="{{$change->changeSubCategory}}"
                                                selected>{{\Itsm\Http\Helper\ThirdCallHelper::getDictMeans('变更子类','changeSub',$change->changeSubCategory)}}</option>
                                    </select>
                                </td>

                            </tr>
                            <tr>
                                <td colspan="2"></td>
                            </tr>
                            <tr>
                                <td class="bold black" align="right">变更原因<br>详细描述</td>
                                <td colspan="3">
                                    <textarea name="changeReason" id="changereason"
                                              class="form-control input-sm contentValidate"
                                              data-name="变更原因"
                                              style="width:89%;margin-left: 10px">{{$change->changeReason}}</textarea>
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
                                                  class="form-control contentValidate" data-name="变更内容"
                                                  style="width:89%;margin-left: 10px">{{$change->changeContext}}</textarea>
                                    </div>
                                </td>
                            </tr>
                            <td colspan="2"></td>
                            <tr>
                                <td class="bold black" align="right">变更风险及<br>影响分析
                                </td>
                                <td colspan="3">
                                        <textarea name="changeRisk" id="changerisk" class="form-control contentValidate"
                                                  style="width:89%;margin-left: 10px"
                                                  data-name="变更风险及影响">{{$change->changeRisk}}</textarea>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"></td>
                            </tr>
                            <tr>
                                <td class="bold black" align="right">可行性审批部门</td>
                                <td>
                                    <select class="form-control validate" name="feasibilityGroupId"
                                            id="feasibilityGroupId" style="width:240px;margin-left: 10px">
                                        <option value="">-请选择-</option>
                                        @foreach($oneGroup as $key=>$item)
                                            @if($change->feasibilityGroupId && $change->feasibilityGroupId == $key)
                                                <option value="{{$key}}" selected="">{{$item['name']}}</option>
                                            @else
                                                <option value="{{$key}}">{{$item['name']}}</option> @endif
                                            @if(isset($item['child'])&&is_array($item['child']))
                                                @foreach($item['child'] as $k=>$value)
                                                    @if($change->feasibilityGroupId && $change->feasibilityGroupId == $k)
                                                        <option value="{{$k}}" selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$value}}</option>
                                                    @else
                                                        <option value="{{$k}}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$value}}</option> @endif @endforeach
                                            @endif
                                        @endforeach
                                    </select>
                                </td>
                                <td class="bold black" align="right">结果验收人</td>
                                <td>
                                    <input type="hidden" id="ciid" name="checkUserId"
                                           value="{{$change->checkUserId}}">
                                    <input type="text" style="margin-left: 10px;width: 240px" name="checkUser"
                                           class="form-control input-sm" id="checkId"
                                           value="{{\Itsm\Http\Helper\ThirdCallHelper::getStuffName($change->checkUserId)}}"
                                           autocomplete="off">
                                    <div class="input-group hiddenDiv" id="hiddenDiv"
                                         style="margin-top: -30px;margin-left:10px;background-color: white;width: 240px">
                                        <input type="text" class="form-control input-sm" id="check"
                                               name="checker" placeholder="查询验收人" autocomplete="off">
                                        <div class="input-group-btn">
                                            <ul style="overflow: auto;width: auto; transition: all 0.3s ease 0s;"
                                                class="dropdown-menu dropdown-menu-right" role="menu">
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                            </tr>
                            <tr>
                                <td class="bold black" align="right">变更申请人</td>
                                <td style="padding-left: 30px">
                                    @foreach($checkUser as $user)
                                        @if($user['Id'] == $change->applyUserId){{$user['Name']}} @endif @endforeach</td>
                                <td class="bold black" align="right">变更申请时间
                                </td>
                                <td style="padding-left: 30px">
                                    {{$change->applyTs}}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4">
                                    <hr/>
                                </td>
                            </tr>
                            <tr>
                                <td class="bold black" align="right">可行性审批人</td>
                                <td style="padding-left: 30px">
                                {{ThirdCallHelper::getStuffName($change->feasibilityUserId)}}
                                <td class="bold black" align="right">审批时间</td>
                                <td style="padding-left: 30px">{{$change->feasibilityTs}}</td>
                            </tr>
                            <tr>
                                <td></td>
                            </tr>
                            <tr>
                                <td class="bold black" align="right">可行性审批意见</td>
                                <td colspan="3">
                                    <div style="padding-left: 30px">
                                        {{$change->feasibilityOpinion}}
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td colspan="3">
                                    <div class="reply-opt">
                                        <input class="reply-btn btnSub" name="processVar"
                                               value="{!! $stepForm['variable'] !!}" type="hidden"/>
                                        {!! $stepForm['form'] !!}
                                        @if($stepForm['variable'] == "")
                                            {!! $stepForm['submit'] !!}
                                        @endif
                                    </div>
                                </td>

                            </tr>
                            <tr>
                                <td colspan="2"></td>
                            </tr>
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
        layer.msg('恭喜您!变更申请修改成功！', {
        icon: 1,
        time: 2000 //2秒关闭
    }, function () {
        closeFrame();
    });
    @endif

    $(document).ready(function () {
        $('#checkId').focus(function () {
            $('#hiddenDiv').removeClass('hiddenDiv');
            $('#check').focus();
        });

        $('#check').blur(function () {
            $('#hiddenDiv').addClass('hiddenDiv');
        });

    });
    /*查询结果验证人*/
    var globaldata;
    var contactBsSuggest = $("#check").bsSuggest({
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
        $('#checkId').val(globaldata[keyword.id - 1].Name);
        $('#ciid').val(globaldata[keyword.id - 1].Id);
    })
</script>
<!-- jQuery Validation plugin javascript-->
<script src="/js/plugins/validate/jquery.validate.min.js"></script>
<script src="/js/plugins/validate/messages_zh.min.js"></script>
<script src="/js/demo/form-validate-demo.js"></script>
</body>
</html>