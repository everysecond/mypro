/**
 * Created by lidz on 2016/9/1.
 */
$.ajaxSetup({//ajax表单提交TOKEN
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

<!-- 富文本编辑器 -->
KindEditor.options.filterMode = false;
KindEditor.ready(function (K) {
    window.editor1 = K.create('#content', {
        resizeType: 0,
        urlType: "domain",
        uploadJson: "/kindeditor/uploadify",
        width: "100%",
        items: [
            'justifyleft', 'justifycenter', 'justifyright', 'forecolor', 'hilitecolor', 'bold',
            'italic', 'underline', 'image'
        ], afterBlur: function () {
            this.sync();
        }
    });
});


function closeFrame() {//关闭当前弹出层
    var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
    parent.layer.close(index); //再执行关闭
}

function validateAlert(msg) {//表单验证不通过提示方法
    layer.alert(msg, {icon: 2, closeBtn: false, area: '100px'});
}

<!--工单提交-->
var submitLock = false;
$('#createSubmit').click(function () {//验证提交内容
    if (submitLock) {
        return;
    }
    submitLock = true;
    var validateMark = false, isTel = /^((0\d{2,3})-)?(\d{7,8})(-(\d{3,}))?$/;
    $('.validate').each(function () {
        if ($(this).val() == '') {
            layer.tips('请填写此项！', this, {time: 2000, tips: 2});
            validateMark = true;
            submitLock = false;
            return false;
        }
    });

    if (!validateMark) {
        if (!isTel.test($('#ctel').val()) && $('#ctel').val().length != 0) {
            validateAlert("请正确填写电话号码，例如:0321-4816048");
            submitLock = false;
            return false;
        }

        if ($.trim($("#content").val()) == '') {
            validateAlert("请输入工单内容!");
            submitLock = false;
            return false;
        }

        $("#userId").removeAttr("disabled");
        $.ajax({
            data: $('#supform').serialize(),
            type: "POST",
            url: "/support/createSubmit",
            success: function (msg) {
                if (msg.status) {//提交成功
                    layer.alert(msg.statusMsg, {icon: 1, closeBtn: false, area: '100px'}, function () {
                        closeFrame();
                    });
                } else {//提交失败
                    submitLock = false;
                    $("#userId").attr("disabled","disabled");
                    validateAlert(msg.statusMsg);
                }
            }
        })

    }
});

var submitLock = false;
$('#onlyCreate').click(function () {//验证提交内容
    if (submitLock) {
        return;
    }
    submitLock = true;
    var validateMark = false;
    $('.validate').each(function () {
        if ($(this).val() == '') {
            layer.tips('请填写此项！', this, {time: 2000, tips: 2});
            validateMark = true;
            submitLock = false;
            return false;
        }
    });

    if (!validateMark) {
        if ($.trim($("#content").val()) == '') {
            validateAlert("请输入工单内容!");
            submitLock = false;
            return false;
        }

        $.ajax({
            data: $('#supform').serialize(),
            type: "POST",
            url: "/support/createSubmit",
            success: function (msg) {
                if (msg.status) {//提交成功
                    layer.alert(msg.statusMsg, {icon: 1, closeBtn: false, area: '100px'}, function () {
                        closeFrame();
                    });
                } else {//提交失败
                    validateAlert(msg.statusMsg);
                    submitLock = false;
                }
            }
        })
    }
});

$('#createAndAppoint').click(function () {//验证提交并直接指派的内容
    if (submitLock) {
        return;
    }
    submitLock = true;
    var validateMark = false;
    $('.validate').each(function () {
        if ($(this).val() == '') {
            layer.tips('请填写此项！', this, {time: 2000, tips: 2});
            validateMark = true;
            submitLock = false;
            return false;
        }
    });

    $('.appointValidate').each(function () {
        if ($(this).val() == '') {
            layer.tips('请填写此项！', this, {time: 2000, tips: 2});
            validateMark = true;
            submitLock = false;
            return false;
        }
    });

    if (!validateMark) {
        if ($.trim($("#content").val()) == '') {
            validateAlert("请输入工单内容!");
            submitLock = false;
            return false;
        }
        $.ajax({
            data: $('#supform').serialize(),
            type: "POST",
            url: "/support/createSubmit",
            success: function (msg) {
                if (msg.status) {//提交成功
                    $('input[name="sid"]').val(msg.status);
                    $.ajax({
                        type: "post",
                        dataType: 'json',
                        url: "/wo/csupport",
                        data: $("#supform").serializeArray(),
                        success: function (data) {//指派成功
                            if (data && data.status) {
                                layer.alert('工单拆分并指派成功!', {icon: 1, closeBtn: false, area: '100px'}, function () {
                                    closeFrame();
                                });
                            } else {//指派出错
                                validateAlert('指派出错!');
                                submitLock = false;
                            }
                        }
                    })
                } else {//提交失败
                    validateAlert(msg.statusMsg);
                    submitLock = false;
                }
            }
        })
    }
});
<!--动态查询指派人-->
$("select[name='group1'],select[name='group2']").on("change", function () {
    var name = $(this).attr("name");
    var id = $(this).find("option:selected").val();
    var obj = (name == "group1") ? "optuser1" : "optuser2";
    obj = $("select[name='" + obj + "']");
    obj.empty();
    if (id == "")return;
    $.ajax({
        type: "GET",
        url: "/wo/optusers/" + id,
        success: function (data) {
            if (data) {
                for (var i = 0; i < data.length; i++) {
                    obj.append('<option value="' + data[i].UserId + '">' + data[i].Name + '</option>');
                }
            }
        }
    });
});


$('#dataCenter').change(function () {
    $('#dataCenterName').val($('#dataCenter').val());
});

$('#selectEquipment').click(function () {
    var cid = $('#CustomerId').val();
    var serviceMode = $('#modeMark').val() ;
    if (cid != '') {
        $.ajax({
            type: "POST",
            url: "/support/getEquipmentList?mode="+serviceMode+"&pageSize=3&pageNumber=1&cusinfId=" + cid,
            success: function (msg) {
                if (msg.total > 0) {//表示该客户有自己的设备
                    layer.open({
                        type: 2,
                        title: '相关设备',
                        area: ['600px', '350px'],
                        skin: 'layui-layer-rim',
                        shade: 0,
                        content: ['/support/selectEquipment?mode='+ serviceMode +'&cusinfId=' + cid, 'no']
                    });
                } else {
                    serviceMode = serviceMode == 'IDC'?'IDC':'安畅云';
                    validateAlert('该客户还没有'+serviceMode+'的相关设备!');
                }
            }
        });
    } else {
        validateAlert('您还没有选择对应客户！');
    }

});

/*查询客户*/

$("#searchInfo").click(function(){
    $(".input-group").css("display","block");
    var se = $.trim($("#search").val());
    var info = $(".input-group");
    $.ajax({
        type:"get",
        url:"/support/getCusInf/" + se,
        headers: {'X-CSRF-Token': $('meta[name=csrf-token]').attr('content')},
        dataType:'json',
        success:function(data){
            info.empty();
            if (data.length>0) {
                var iright,display;
                for (var i = 0; i < data.length; i++) {
                    iright = "";
                    display = "  hidden";
                    if((i+1)%5 == 0 && i != 99){
                        iright = '<span class="iright"  onclick="showNextFive(this,event, '+(k+1)+')" ' +
                            'title="显示更多"><i class="fa fa-angle-double-down"></i></span>';
                    }
                    var k = Math.floor(i/5);
                    if(k==0)display = "";
                    info.append('<li class="li'+k+display+'" value="' + data[i]['Id'] + '" title="'+data[i]['Authorization']+
                        '">'+ (i+1)+"." + data[i]['CusName']+iright+'</li>');
                }
            }
            else{
                info.append('<li>无匹配</li>');
            }
        }
    })
})

function showNextFive(obj,e, id){
    $(obj).remove();
    e.stopPropagation();
    $(".li"+id).removeClass("hidden");
}

$(".tab_bq").on("click","li",function(e){
    e.stopPropagation();
    $("#CustomerId").val($(this).val());
    var cid = $("#CustomerId").val();
    if (cid != 0) {
        $("#Authorization").val('');
        if ($(this).attr('title') && $(this).attr('title')!= 'null') {
            $("#Authorization").val($(this).attr('title'));
        }
        $("#CustomerName").val($(this).text().split(".")[1]);
        $.ajax({
            type: "Get",
            url: "/support/create?cusId=" + cid,
            success: function (msg) {
                if (msg.length) {
                    $('#contactId').html('<option value="">请选择</option>');
                    $('#dataCenterName').removeAttr('disabled');
                    $('#EquipmentId').val('');
                    $('#dataCenter').val('');
                    $('#DevId').val('');
                    for (var key in msg) {
                        if (msg[key]['Name']) {
                            $('#contactId').append('<option value="' + msg[key]['Id'] + '"' + (msg[0]['Id'] ? "selected" : "") + '>' + msg[key]['Name'] + '</option>');
                        }
                    }
                    var dcid = $("#contactId").find("option:selected").val();
                    if (dcid != '') {
                        $("#contactId").trigger("change");
                    }
                    $('#userId').removeAttr("disabled");
                    $('#userId').html('<option value="">请选择</option>');
                    $.ajax({
                        type: "Get",
                        url: "/support/create?getLoginList=" + cid,
                        success: function (msg) {
                            if(msg.length){
                                for (var key in msg) {
                                    if (msg[key]['Id']) {
                                        $('#userId').append('<option value="' + msg[key]['Id'] + '"' + (msg[0]['Id'] ? "selected" : "") + '>' + msg[key]['LoginId'] + '</option>');
                                    }
                                }
                            }
                        }
                    })
                }
                else{
                    layer.msg('该客户无联系人！', {icon: 2, time: 1500}, function () {
                        location.reload();
                    })
                }
            }
        });
    }
})
$('#contactId').change(function () {
    $.ajax({
        type: "GET",
        url: "/support/create?contactId=" + $('#contactId').val(),
        success: function (msg) {
            $('#cmobile').val(msg.Mobile);
            $('#hiddenMobile').val(msg.Mobile);
            $('#hiddenEmail').val(msg.Email);
            $('#ctel').val(msg.Tel);
            $('#cemail').val(msg.Email);
            $('#ccredtype').val(msg.Means);
            $('#ccredno').val(msg.Credentials);
            if(null != msg.UserLoginId && ""!= msg.UserLoginId){
                $('#userId').val(msg.UserLoginId);
                $('#userId').attr("disabled","disabled");
            }else{
                $('#userId').removeAttr("disabled");
            }
        }
    })
});