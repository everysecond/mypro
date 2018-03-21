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
        .checkbox-inline input[type=checkbox]{
            margin-top: 3px;
        }
        .col-sm-10{
            margin-bottom: -8px;
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
                        <input type="hidden" name="Id" value="{{$issue->Id}}"/>
                        <input type="hidden" name="issueState" value="{{$issue->issueState}}"/>
                        <input type="hidden" name="issueStateMeans" value="{{ThirdCallHelper::getDictMeans('问题状态','issueState',$issue->issueState)}}"/>
                        {{csrf_field()}}

                        <table class="table-edit" style="color: #000000;" bgcolor="#FFFFFF" border="1" cellpadding="0" cellspacing="0" width="80%">
                            <div style="font-size: 16px;line-height: 55px;border-bottom:1px solid #f3f3f3;margin-bottom:20px; ;">问题单号:{{$issue->issueNo }}</div>
                            <tr>
                                <td class="bold black" align="right">问题单号</td>
                                <td style="padding-left: 30px">{{$issue->issueNo }}</td>
                                <td class="bold black" align="right">问题标题</td>
                                <td colspan="1">
                                    <input value="{{$issue->issueTitle}}" maxlength="50" class="form-control input-sm validate" name="issueTitle" type="text" id="issueTitle" style="width: 240px;margin-left: 10px">
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"></td>
                            </tr>
                            <tr>
                                <td class="bold black" align="right">问题来源</td>
                                <td colspan="3">
                                    <div>
                                        <div class="col-sm-10">
                                            @foreach($sourceList as $d)
                                                <label class="checkbox-inline"><input class="sourceBox" type="checkbox" value="{{$d->Code}}" name="issueSource[]"
                                                    @if(\Itsm\Http\Helper\ThirdCallHelper::isSubElement($issue->issueSource,$d->Code)) checked @endif>{{$d->Means}}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"></td>
                            </tr>
                            <tr>
                                <td class="bold black" align="right">问题分类</td>
                                <td>
                                    <select class="form-control validate validate" name="issueCategory" id="issueCategory" style="width:240px;margin-left: 10px">
                                        <option value="">==请选择==</option>
                                        @foreach($categoryList as $state)
                                            <option value="{{$state->Code}}" @if($state->Code == $issue->issueCategory) selected @endif>
                                                {{$state->Means}}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="bold black" align="right">优先级</td>
                                <td>
                                    <select class="form-control validate validate" name="issuePriority" id="issuePriority" style="width:240px;margin-left: 10px">
                                        <option value="">==请选择==</option>
                                        @foreach($priorityList as $state)
                                            <option value="{{$state->Code}}" @if($state->Code == $issue->issuePriority) selected @endif>
                                                {{$state->Means}}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                            <tr><td colspan="2"></td></tr>
                            <tr>
                                <td class="bold black" align="right" style="vertical-align:top">问题描述
                                </td>
                                <td colspan="3">
                                    <div>
                                        <textarea name="issueDescribe" id="issueDescribe" class="form-control" data-name="问题描述" style="width:89%;margin-left: 10px">{{$issue->issueDescribe}}</textarea>
                                    </div>
                                </td>
                            </tr>
                            <tr><td colspan="2"></td></tr>
                            <tr>
                                <td class="bold black" align="right">问题审核人</td>
                                <td>
                                    <input type="hidden" id="cid" name="issueCheckUserId"　 value="{{$issue->issueCheckUserId}}">
                                    <input type="text" style="margin-left: 10px" class="form-control input-sm validate" id="issueCheckUserId" placeholder="问题审核人" value="{{\Itsm\Http\Helper\ThirdCallHelper::getStuffName($issue->issueCheckUserId)}}" autocomplete="off">
                                    <div class="input-group hiddenDiv" id="hiddenDiv" style="margin-top: -30px;margin-left:10px;background-color: white;width: 100%">
                                        <input type="text" class="form-control input-sm " id="checkUser" name="check" placeholder="问题审核人" autocomplete="off">
                                        <div class="input-group-btn">
                                            <ul style=" max-height: 375px; max-width: 800px; overflow: auto;width: auto; transition: all 0.3s ease 0s;" class="dropdown-menu dropdown-menu-right" role="menu"></ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr><td colspan="2"></td></tr>
                            <tr>
                                <td class="bold black" align="right">问题提交人</td>
                                <td style="padding-left: 30px">{{$issue->issueSubmitUser}}</td>
                                <td class="bold black" align="right">问题提交时间</td>
                                <td style="padding-left: 30px">{{$issue->issueSubmitTs}}</td>
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
<script src="/js/issue_detail.js"></script>
<script>
    function closeFrame() {
        var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
        parent.layer.close(index); //再执行关闭
    }
    @if(session('status'))
        layer.msg('恭喜您!问题申请修改成功！', {
        icon: 1,
        time: 2000 //2秒关闭
    }, function () {
        closeFrame();
    });
    @endif

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
</script>
<!-- jQuery Validation plugin javascript-->
<script src="/js/plugins/validate/jquery.validate.min.js"></script>
<script src="/js/plugins/validate/messages_zh.min.js"></script>
<script src="/js/demo/form-validate-demo.js"></script>
</body>
</html>