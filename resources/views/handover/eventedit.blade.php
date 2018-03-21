<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>安畅网络 交接单管理</title>
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
    <link rel="stylesheet" type="text/css" href="/css/handover.css?333">
    <style>
        .table-edit, .table-edit td {
            border: 1px solid #fff;
            height: 0;
            font-size: 14px;
        }

        .hiddenDiv {
            display: none;
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
                    <form action="{{url('handover/eventEdit')}}" method="POST" id="eventform"
                          enctype="multipart/form-data" style="width: 900px">
                        <fieldset>
                            {{csrf_field()}}
                            <table class="table-edit" style="color: #000000;" bgcolor="#FFFFFF" border="1"
                                   cellpadding="0" cellspacing="0" width="800px">
                                <tbody>
                                <input type="hidden" name="eventId" id="eventId" value="{{$event->id}}"/>
                                <input type="hidden" name="csIds" value="{{$csIds}},"/>
                                <tr>
                                    <td colspan="2">
                                        <div style="float:left;height: 10px;font-weight: 700;">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right">工单编号:</td>
                                    <td>
                                        <input type="hidden" name="status" value="未完成">
                                        <input type="text" class="form-control" id="supportId" name="supportId"
                                               style="background-color: white;width: 250px;margin-left: 10px;"
                                               required="required" placeholder="输入工单编号"
                                               @if($event->supportId==0){value="" }
                                               @else value="{{$event->supportId}}" @endif>
                                        <div class="input-group hiddenDiv" id="hiddenIdDiv"
                                             style="margin-top: -34px;background-color: white;width: 250px;margin-left: 10px;">
                                            <input type="text" class="form-control" id="support" name="support"
                                                   autocomplete="off" placeholder="输入工单编号">
                                            <div class="input-group-btn">
                                                <ul style=" max-height: 375px; max-width: 800px; overflow: auto;
                                            width: auto; transition: all 0.3s ease 0s;"
                                                    class="dropdown-menu dropdown-menu-right" role="menu">
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span style="color: red">*</span>事件类型:</td>
                                    <td>
                                        <div style="display: inline-block">
                                            <select name="type" id="type" class="form-control validate"
                                                    style="width:200px;margin-left: 10px;">
                                                <option value="">请选择</option>
                                                    @foreach($eventType as $d)
                                                        <option value="{{$d->Code}}"
                                                                @if($d->Code == $event->type) selected @endif>{{$d->Means}}</option>
                                                    @endforeach
                                            </select></div>
                                    </td>
                                </tr>
                                <td>
                                <td colspan="2"></td>
                                <tr>
                                    <td align="right">客户名称:</td>
                                    <td>
                                        <div>
                                            <input name="cusId" id="CusId" type="hidden" value="{{$event->cusId}}">
                                            <input value="{{ThirdCallHelper::getCusName($event->cusId)}}"
                                                   class="form-control input-sm" placeholder="输入客户名搜索"
                                                   id="cusName" name="cusName" type="text"
                                                   style="width:250px;margin-left: 10px;">
                                            <div class="input-group hiddenDiv" id="hiddenDiv"
                                                 style="margin-top: -34px;background-color: white;width: 250px;margin-left: 10px;">
                                                <input type="text" class="form-control" id="cusname" name="cusname"
                                                       autocomplete="off" placeholder="输入客户名搜索"
                                                       value="{{ThirdCallHelper::getCusName($event->cusId)}}">
                                                <div class="input-group-btn">
                                                    <ul style=" max-height: 375px; max-width: 800px; overflow: auto;
                                            width: auto; transition: all 0.3s ease 0s;"
                                                        class="dropdown-menu dropdown-menu-right" role="menu">
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span style="color: red">*</span>优先级:</td>
                                    <td>
                                        <select name="priority" id="priority" class="form-control validate"
                                                style="width:200px;margin-left: 10px;">
                                            <option value="">-请选择-</option>
                                            <option value="0" @if($event->priority == 0) selected @endif>一般</option>
                                            <option value="1" @if($event->priority == 1) selected @endif>重要</option>
                                        </select>
                                    </td>
                                </tr>
                                <td colspan="2"></td>
                                <tr>
                                    <td align="right"><span style="color: red">*</span>预约提醒时间:</td>
                                    <td>
                                        <input name="remindTs" class="form-control validate" id="remindTs"
                                               style="width:250px;margin-left: 10px;" value="{{$event->remindTs}}">
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <td align="right">连续提醒:</td>
                                    <td>
                                        <div style="display: inline-block;padding-left: 20px;" class="radio">
                                            <label><input type="radio" checked="checked" value="no" name="remindType">不需要</label>
                                            <label><input type="radio" value="two" name="remindType"
                                                          @if($event->remindType == 'two') checked="" @endif >2分钟</label>
                                            <label><input type="radio" value="five" name="remindType"
                                                          @if($event->remindType == 'five') checked="" @endif>5分钟</label>
                                            <label><input type="radio" value="ten" name="remindType"
                                                          @if($event->remindType == 'ten') checked="" @endif>10分钟</label>
                                            <label><input type="radio" value="fifteen" name="remindType"
                                                          @if($event->remindType == 'fifteen') checked="" @endif>15分钟
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right"><span style="color: red">*</span>部门:</td>
                                    <td>
                                        <div style="display: inline-block">
                                            <select name="groupId" id="groupId" class="form-control groupAttr validate"
                                                    style="margin-left: 10px;">
                                                <option value="">请选择</option>
                                                @foreach($eventGroups as $key=>$item)
                                                    <option value="{{$key}}"
                                                            @if($event->groupId && $event->groupId == $key) selected @endif>{{$item['name']}}</option>
                                                    @if(isset($item['child'])&&is_array($item['child']))
                                                        @foreach($item['child'] as $k=>$value)
                                                             <option value="{{$k}}"
                                                                 @if($event->groupId && $event->groupId == $k) selected @endif>
                                                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$value}}</option>
                                                        @endforeach
                                                    @endif
                                                @endforeach
                                            </select></div>
                                        <div class="hiddenDiv" id="dcHidden">
                                            <select name="dcGroup" id="dcGroup"  class="form-control" style="margin-left: 30px;">
                                                <option value="">请选择</option>
                                            </select></div>
                                    </td>
                                    <td><span style="color: red">*</span>负责人:</td>
                                    <td colspan="3">
                                        <div style="display: inline-block">
                                            <select name="chargerId" id="chargerId" class="form-control"
                                                    style="width:200px;margin-left: 10px;">
                                                <option>请选择</option>
                                            </select></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <td align="right">抄送人:</td>
                                    <td colspan="3">
                                        <input class="form-control" type="text" readonly="readonly"
                                               placeholder="现场运维部若无负责人默认抄送人为该部门所有人"
                                               id="selectId" value="{{$csNames}}" style="width: 594px;
                                               display:inline-block;background-color: white;margin-left: 10px;"/>
                                        <a class="btn btn-primary" title="清空所有已选抄送人" onclick="clearStfs()"
                                           style="border-radius: 0px;margin-bottom: 3px;margin-left: -5px;">清空</a>
                                        <ul class="hiddenUl">
                                        </ul>
                                        {{--<div>
                                            <div style="display: inline-block;width: 50%">
                                                <select id="chargerTwoId" class="form-control" name="chargerTwoId"
                                                        style="margin-left: 10px;">
                                                    <option value="">请选择</option>
                                                </select>
                                            </div>
                                            <a class="btn btn-xs btn-primary" style="margin-left: 10px"
                                               onclick="return false;" id="addusers">添加</a>
                                        </div>
                                        <div >
                                            <select class="form-control" multiple=""
                                                    style="display: inline-block;margin-left:10px;width: 50%" name="cuids">
                                                @foreach($csIdsArray as $csId)
                                                    <option value="{{$csId}}">
                                                        {{\Itsm\Http\Helper\ThirdCallHelper::getStuffName($csId)}}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button class="btn btn-xs btn-primary" onclick="return false;"
                                                    style="display: inline-block;" id="removeusers">移除
                                            </button>
                                        </div>--}}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <td align="right"><span style="color: red">*</span>事件说明:</td>
                                    <td colspan="3">
                                        <textarea class="form-control validate" id="notes"
                                                  name="notes"
                                                  style="margin-left: 10px;width: 650px;resize: none;
                                                  height:100px;overflow-y: scroll"> {{$event->notes}}</textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4"></td>
                                </tr>
                                <tr>
                                    <td align="right">结果反馈:</td>
                                    <td colspan="3">
                                        <div style="height: 140px;width:650px;margin-left: 10px;">
                                            <textarea class="validate" id="feedback"  name="feedback"
                                                      data-name="结果反馈">{!! $event->feedback !!}</textarea>
                                        </div>
                                        {{--@if($event->feedback)
                                            <textarea class="form-control validate" id="feedback"
                                                      name="feedback"
                                                      style="margin-left: 10px;width: 650px;height:50px"> {{$event->feedback}}</textarea>
                                        @else
                                            <textarea class="form-control validate" id="feedback"
                                                      name="feedback"
                                                      style="margin-left: 10px;width: 650px;height:50px"></textarea>
                                        @endif--}}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4"></td>
                                </tr>
                                <tr align="left">
                                    <td align="right">提交人:</td>
                                    <td style="padding-left: 30px">{{ThirdCallHelper::getStuffName($event->submitterId)}}</td>
                                    <td>提交时间:</td>
                                    <td style="padding-left: 30px">{{$event->ts}}</td>
                                </tr>
                                <tr>
                                    <td colspan="1"></td>
                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <a type="button" id="eventsumit"
                                           class="btn btn-primary  btnSub">提交信息</a>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <a type="reset" class="btn btn-primary " onclick="closeFrame()">取消</a>
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
<script src="/js/handover.js?333"></script>
<script>
    function closeFrame() {
        var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
        parent.layer.close(index); //再执行关闭
    }

    $("#selectId").click(function(){
       $(".hiddenUl").toggle(500);
    });

    $(".hiddenUl").hide();

    $("body").bind("click",function(evt) {
        if (evt.target != $("#selectId").get(0) && $(evt.target).parent().get(0) != $(".hiddenUl").get(0)) {
            $(".hiddenUl").hide();
        }
    });


    $(function () {
        <!--TOKEN验证-->
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $("select[name='groupId']").on("change", function (event, uid,dcgrp) {
            var name = $(this).attr("name");
            var id = $(this).find("option:selected").val();
            if (id == 'second_dept_23') {
                $("#dcHidden").removeClass('hiddenDiv').addClass('dcAttr');
                $("#groupId").removeClass('groupAttr');
                var dc = $("select[name='dcGroup']");
                dc.empty().append('<option value="">-数据中心-</option>');
                $.ajax({
                    type: "GET",
                    url: "/handover/getDCDept",
                    success: function (arr) {
                        if (arr) {
                            for (var i = 0; i < arr.length; i++) {
                                dc.append('<option value="' + arr[i]['UsersName'] + '"' + (dcgrp == arr[i]['UsersName'] ? "selected" : "") + '>' + arr[i]['UsersName'].replace("数据中心组", '') + '</option>');
                            }
                        }
                        var dcid = $("select[name='dcGroup']").find("option:selected").val();
                        if (dcid != '') {
                            var uiid = '{{$event->chargerId}}';
                            $("select[name='dcGroup']").trigger("change", [uiid]);
                        }
                    }
                });
            } else {
                $("#dcHidden").removeClass('dcAttr').addClass('hiddenDiv');
                $("#groupId").addClass('groupAttr');
                var obj = $("select[name='chargerId']");
                obj.empty().append('<option value="">-请选择-</option>');
                var obj1 = $("select[name='chargerTwoId']");
                obj1.empty().append('<option value="">-请选择-</option>');
                var ul = $(".hiddenUl");
                ul.empty();
                if (id == "")return;
                $.ajax({
                    type: "GET",
                    url: "/handover/getDepStuffs?depId=" + id,
                    success: function (arr) {
                        if (arr) {
                            for (var i = 0; i < arr.length; i++) {
                                obj.append('<option value="' + arr[i]['Id'] + '"' + (uid == arr[i]['Id'] ? "selected" : "") + '>' + arr[i]['Name'] + '</option>');
                                obj1.append('<option value="' + arr[i]['Id'] + '">' + arr[i]['Name'] + '</option>');
                                ul.append('<li onclick="copystf(this)" value="' + arr[i]['Id'] + '">' + arr[i]['Name'] + '</li>');
                            }
                        }
                    }
                });
            }
        });
        var gid = $("select[name='groupId']").find("option:selected").val();

        if (gid != '') {
            var uid = '{{$event->chargerId}}';
            var dcgrp = '{{$event->dcGroup}}'
            $("select[name='groupId']").trigger("change", [uid,dcgrp]);
        }
        $("select[name='dcGroup']").on("change", function (event, uiid) {
            var id = $(this).find("option:selected").val();
            var obj = $('#chargerId');
            obj.empty().append('<option value="">-请选择-</option>');
            var obj1 = $('#chargerTwoId');
            obj1.empty().append('<option value="">-请选择-</option>');
            var ul = $(".hiddenUl");
            ul.empty();
            $.ajax({
                type: "GET",
                url: "/handover/getDepStuffs?depId=" + id,
                success: function (arr) {
                    if (arr) {
                        for (var i = 0; i < arr.length; i++) {
                            obj.append('<option value="' + arr[i]['Id'] + '"' + (uiid == arr[i]['Id'] ? "selected" : "") + '>' + arr[i]['Name'] + '</option>');
                            obj1.append('<option value="' + arr[i]['Id'] + '">' + arr[i]['Name'] + '</option>');
                            ul.append('<li onclick="copystf(this)" value="' + arr[i]['Id'] + '">' + arr[i]['Name'] + '</li>');
                        }
                    }
                }
            });
        });

    });
    //选择添加成员
    $("#addusers").click(function () {
        var uid = $("[name='chargerTwoId']").val();
        var uname = $("[name='chargerTwoId'] option:selected").text();
        var csIds = "";
        if (!uid) {
            lalert("请选择组成员");
            return false;
        }
        if ($("[name='cuids'] option[value='" + uid + "']").length)return false;
        if (uid == $('#chargerId').val()) {
            layer.alert('该人员已被选择为负责人', {tips: 2});
            return false;
        }
        $("[name='cuids']").append('<option value="' + uid + '">' + uname + '</option>');
        $("[name='cuids'] option").each(function () {
            csIds += (csIds != "" ? "," : "") + $(this).val();
        });
        $("input[name='csIds']").val(csIds);
    });

    function copystf(cur){
        var csNames = $("#selectId").val(),csIds=$("input[name='csIds']").val();
        if(csNames.indexOf($(cur).html()+",")>=0){
            csNames = csNames.replace($(cur).html()+",","");
            csIds = csIds.replace($(cur).val()+",","");
            $("#selectId").val(csNames);
            $("input[name='csIds']").val(csIds);
        }else{
            $("#selectId").val(csNames+$(cur).html()+",");
            $("input[name='csIds']").val(csIds+$(cur).val()+",");
        }

    };

    function clearStfs(){
        $("#selectId").val("");
        $("input[name='csIds']").val("");
    }

    <!--移除成员-->
    $("#removeusers").click(function () {
        var roption = $("[name='cuids'] option:selected"), uids = "";
        if(roption.length>0){
            $("[name='cuids'] option[value='"+roption.val()+"']").remove();
            $("[name='cuids'] option").each(function(){
                uids += (uids!=""?",":"")+$(this).val();
            });
            $("input[name='csIds']").val(uids);
        }
    });
    //事件表单提交验证
    function validate(indexValidate) {
        if ($(this).hasClass("down-btn")) {
            validateMark = true;
            $('.btnSub').removeAttr('disabled');
            layer.close(indexValidate);
            return false;//防止重复提交
        }
        if (!validateMark) {
            if($('#groupId').val() == "second_dept_23" && $("#dcGroup").val() == ""){
                layer.tips('请填写此项！',"#dcGroup" , {time: 2000, tips: 2});
                validateMark = true;
                $('.btnSub').removeAttr('disabled');
                layer.close(indexValidate);
                return false;
            }else if($("#chargerId").val() == "" && $('#groupId').val() != "second_dept_23"){
                layer.tips('请填写此项！',"#chargerId" , {time: 2000, tips: 2});
                validateMark = true;
                $('.btnSub').removeAttr('disabled');
                layer.close(indexValidate);
                return false;
            }
            $('.validate').each(function () {
                if ($(this).val() == '') {
                    if($(this).data("name") == "结果反馈"){
                        layer.alert("结果反馈不能为空！", {icon: 2, closeBtn: false, area: '100px'});
                        validateMark = true;
                        $('.btnSub').removeAttr('disabled');
                        layer.close(indexValidate);
                        return false;
                    }else{
                        layer.tips('请填写此项！', this, {time: 2000, tips: 2});
                        validateMark = true;
                        $('.btnSub').removeAttr('disabled');
                        layer.close(indexValidate);
                        return false;
                    }
                }
            });
        }
    }

    //事件表单提交
    var validateMark = false;
    $('.btnSub').unbind();
    $('.btnSub').click(function () {
        $(this).attr('disabled', 'disabled');
        var indexValidate = layer.load(0, {shade: false});
        var eventId = $('#eventId').val();
        validateMark = false;
        validate(indexValidate);
        //判断是只保存还是保存并审核通过或者审核不通过
        if (!validateMark) {
            $.ajax({
                type: "POST",
                data: $('#eventform').serialize(),
                url: "/handover/eventEditPush/" + eventId,
                success: function (arr) {
                    if (arr.status == true) {
                        layer.msg('事件编辑成功！', {icon: 1, time: 2000}, function () {
                                    closeFrame();
                                }
                        );
                    } else {
                        layer.msg('事件编辑失败！', {icon: 2, time: 2000}, function () {
                                    closeFrame();
                                }
                        );
                    }
                }
            });
        }
    });
    var start = {
        elem: '#remindTs',
        format: 'YYYY-MM-DD hh:mm',
        min: laydate.now(), //设定最小日期为当前日期
        max: '2099-06-16 23:59', //最大日期
        istime: true,
        istoday: false,
        choose: function (datas) {
            if (Date.parse(datas) / 1000 < Date.parse(laydate.now()) / 1000) {
                $('#remindTs').val(laydate.now());
            }
        }
    };
    laydate(start);

</script>
<!-- jQuery Validation plugin javascript-->
<script src="/js/plugins/validate/jquery.validate.min.js"></script>
<script src="/js/plugins/validate/messages_zh.min.js"></script>
<script src="/js/demo/form-validate-demo.js"></script>

</body>
</html>