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
    <link rel="stylesheet" type="text/css" href="/css/handover.css?222">
    <style>
        .table-edit, .table-edit td {
            border: 1px solid #fff;
            height: 10px;
            font-size: 14px;
        }
        .hiddenDiv {
            display: none;
        }
        .dcAttr{
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
                    <form action="{{url('handover/eventPush')}}" method="POST" id="eventform"
                          enctype="multipart/form-data" style="width: 900px">
                        <input id="route" type="hidden" value="eventPush">
                        <input type="hidden" name="csIds" value=""/>
                        <input type="hidden" name="csNames" value=""/>
                        <input id="handoverId" type="hidden" name = "handoverId">
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
                                    <td align="right">工单编号:</td>
                                    <td>
                                        <input type="hidden" name="status" value="未完成">
                                        <input type="text" class="form-control  " id="supportId" name="supportId"
                                               style="background-color: white;width: 250px;margin-left: 10px;"
                                               required="required" placeholder="输入工单编号">
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
                                                    <option value="{{$d->Code}}">{{$d->Means}}</option>
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
                                            <input name="cusId" id = "CusId" type="hidden" value="">
                                            <input value="" class="form-control input-sm" placeholder="输入客户名搜索"
                                                   id="cusName" name="cusName" type="text" style="width:250px;margin-left: 10px;">
                                            <div class="input-group hiddenDiv" id="hiddenDiv"
                                                 style="margin-top: -34px;background-color: white;width: 250px;margin-left: 10px;">
                                                <input type="text" class="form-control" id="cusname" name="cusname"
                                                       autocomplete="off" placeholder="输入客户名搜索" value="">
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
                                            <option value="0">一般</option>
                                            <option value="1">重要</option>
                                        </select>
                                    </td>
                                </tr>
                                <td colspan="2"></td>
                                <tr>
                                    <td align="right"><span style="color: red">*</span>预约提醒时间:</td>
                                    <td>
                                        <input name="remindTs" class="form-control validate" id="remindTs"
                                               style="width:250px;margin-left: 10px;">
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3"></td>
                                </tr>
                                <tr>
                                    <td align="right">连续提醒:</td>
                                    <td>
                                        <div style="display: inline-block;padding-left: 20px;" class="radio">
                                            <label><input type="radio" checked="checked" value="no" name="remindType">不需要</label>
                                            <label><input type="radio" value="two" name="remindType">2分钟</label>
                                            <label><input type="radio" value="five" name="remindType">5分钟</label>
                                            <label><input type="radio" value="ten" name="remindType">10分钟</label>
                                            <label><input type="radio" value="fifteen" name="remindType">15分钟 </label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right"><span style="color: red">*</span>部门:</td>
                                    <td>
                                        <div style="display: inline-block">
                                            <select name="groupId" id="groupId" class="form-control groupAttr validate"
                                                    onchange="getStuffs(this)"  style="margin-left: 10px;">
                                                <option value="">请选择</option>
                                                @foreach($eventGroups as $key=>$item)
                                                    <option value="{{$key}}">{{$item['name']}}</option>
                                                    @if(isset($item['child'])&&is_array($item['child']))
                                                        @foreach($item['child'] as $k=>$value)
                                                                <option value="{{$k}}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$value}}</option>
                                                        @endforeach
                                                    @endif
                                                @endforeach
                                            </select></div>
                                        <div class="hiddenDiv" id="dcHidden">
                                            <select name="dcGroup" id="dcGroup" onchange="getDCStuffs(this)" class="form-control" style="margin-left: 30px;">
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
                                               id="selectId" value="" style="width: 594px;
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
                                                  name="notes" style="margin-left: 10px;width: 650px;height:100px;
                                                  resize:none;overflow-y: scroll"></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4"></td>
                                </tr>
                                <tr align="left">
                                    <td align="right">提交人:</td>
                                    <td style="padding-left: 30px">{{$submitUser['Name']}}</td>
                                    <td>提交时间:</td>
                                    <td style="padding-left: 30px">{{$submitTime}}</td>
                                </tr>
                                <tr>
                                    <td colspan="2"></td>
                                </tr>
                                <tr>
                                    <td colspan="1"></td>
                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <a type="button" id="eventsumit"
                                           class="btn btn-primary mar_top20 btnSub">提交信息</a>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <a type="reset" class="btn btn-primary mar_top20" onclick="closeFrame()">取消</a>
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
<script src="/js/handover.js?222"></script>
<script>
    var start = {
        elem: '#remindTs',
        format: 'YYYY-MM-DD hh:mm',
        min: laydate.now(), //设定最小日期为当前日期
        max: '2099-06-16 23:59', //最大日期
        istime: true,
        istoday: false,
        choose: function (datas) {
            if(Date.parse(datas)/1000 < Date.parse(laydate.now())/1000){
                $('#remindTs').val(laydate.now()) ;
            }
        }
    };
    laydate(start);


    $("#selectId").click(function(){
        $(".hiddenUl").toggle(500);
    });

    $(".hiddenUl").hide();

    $("body").bind("click",function(evt) {
        if (evt.target != $("#selectId").get(0) && $(evt.target).parent().get(0) != $(".hiddenUl").get(0)) {
            $(".hiddenUl").hide();
        }
    });

    function clearStfs(){
        $("#selectId").val("");
        $("input[name='csIds']").val("");
    }

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

    //选择添加成员
    $("#addusers").click(function () {
        var uid = $("[name='chargerTwoId']").val();
        var uname = $("[name='chargerTwoId'] option:selected").text();
        var csIds = "";
        var csNames = "";
        if (!uid) {
            layer.alert("请选择组成员");
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
            csNames += (csNames != "" ? "," : "") + $(this).html();
        });
        $("input[name='csIds']").val(csIds);
        $("input[name='csNames']").val(csNames);
    });
    <!--移除成员-->
    $("#removeusers").click(function () {
        var roption = $("[name='cuids'] option:selected"), csIds = "";
        if (roption.length > 0) {
            $("[name='cuids'] option[value='" + roption.val() + "']").remove();
            $("[name='cuids'] option").each(function () {
                csIds += (csIds != "" ? "," : "") + $(this).val();
            });
            $("input[name='csIds']").val(csIds);
        }
    });

    $("#handoverId").val(parent.$("#handoverId").val());
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
                data: $('#eventform').serialize(),
                url: "/handover/" + route,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                },
                success: function (arr) {
                    if (arr.status==true) {
                        layer.msg('事件申请提交成功！', {icon: 1, time: 2000}, function () {
                            parent.$('#relatedEventsTable').bootstrapTable('refresh');
                            closeFrame();
                        });
                    } else {
                        layer.msg('事件申请提交失败！', {icon: 2, time: 2000}, function () {
                            closeFrame();
                        });
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