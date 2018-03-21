$(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
});


// 点击查看大图
$(".litle-img").click(function () {
    $(".large-img").attr("src", $(this).attr("src"));
    $("#enlargeImage").removeClass("hide");
});

$("body").on("click", ".litle-img", function () {
    $(".large-img").attr("src", $(this).attr("src"));
    $("#enlargeImage").removeClass("hide");
});
$("#questionListLitleImg").click(function () {
    $("#enlargeImage").removeClass("hide");
});
$("#changeListLitleImg").click(function () {
    $("#enlargeImage").removeClass("hide");
});
$("#remarkListLitleImg").click(function () {
    $("#enlargeImage").removeClass("hide");
});

// 点击关闭大图
$("#closeLargeImg").click(function () {
    $("#enlargeImage").addClass("hide");
});

$(document).ready(function () {
    $('#parentTypeName').focus(function () {
        $('#hiddenDiv').removeClass('hiddenDiv');
        $('#cusname').focus();
        $('#cusname').addClass('hiddenDiv');
    });
});


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
        $('.btnSubContact').removeAttr('disabled');
        layer.close(indexValidate);
        return false;//防止重复提交
    }
    if (!validateMark) {
        $('.validate').each(function () {
            if ($(this).val() == '') {
                layer.tips('请填写此项！', this, {time: 2000, tips: 2});
                validateMark = true;
                $('.btnSub').removeAttr('disabled');
                $('.btnSubContact').removeAttr('disabled');
                layer.close(indexValidate);
                return false;
            }
        });
    }
}

//提交
var validateMark = false;
$('.btnSub').unbind();
$('.btnSub').click(function () {
    $(this).attr('disabled', 'disabled');
    var indexValidate = layer.load(0, {shade: false});
    validateMark = false;
    validate(indexValidate);
    //判断是只保存还是保存并审核通过或者审核不通过
    if (!validateMark) {
        $.ajax({
            type: "POST",
            data: $('#newProvider').serialize(),
            url: "/rpms/resourceProvider/newProviderSub",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            },
            success: function (arr) {
                if (arr.status) {
                    layer.msg(arr.msg, {time: 2000},function(){
                        parent.$('#providerTable').bootstrapTable('refresh');
                        closeFrame();
                    });
                } else {
                    layer.msg(arr.msg, {icon: 2, time: 2000},function(){
                        layer.close(indexValidate);
                        $(".btnSub").removeAttr('disabled');
                    });
                }
            }
        });
    }
});

$('.btnSubContact').unbind();
$('.btnSubContact').click(function () {
    $(this).attr('disabled', 'disabled');
    var indexValidate = layer.load(0, {shade: false});
    validateMark = false;
    validate(indexValidate);
    //判断是只保存还是保存并审核通过或者审核不通过
    if (!validateMark) {
        var providerId = parent.$("input[name='providerId']").val();
        $.ajax({
            type: "POST",
            data: $('#newContact').serialize(),
            url: "/rpms/resourceProvider/newContactSub?providerId="+providerId,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            },
            success: function (arr) {
                if (arr.status) {
                    layer.msg(arr.msg, {time: 2000},function(){
                        parent.$('#contactTable').bootstrapTable('refresh');
                        closeFrame();
                    });
                } else {
                    layer.msg(arr.msg, {icon: 2, time: 2000},function(){
                        layer.close(indexValidate);
                        $(".btnSubContact").removeAttr('disabled');
                    });
                }
            }
        });
    }
});