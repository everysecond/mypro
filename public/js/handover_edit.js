$(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
})


// 点击查看大图
$(".litle-img").click(function () {
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
})
$("#closeLargeImg").click(function () {
    $("#enlargeImage").addClass("hide");
})
$("#closeLargeImg").click(function () {
    $("#enlargeImage").addClass("hide");
})
$("#closeLargeImg").click(function () {
    $("#enlargeImage").addClass("hide");
})
//动态查询某部门所有员工（交接单）
function getStuff(data) {
    var obj = $('#chargerId');
    obj.empty().append('<option value="">-请选择-</option>');
    $.ajax({
        type: "GET",
        url: "/handover/getDepStuffs?depId=" + $(data).val(),
        success: function (arr) {
            if (arr) {
                for (var i = 0; i < arr.length; i++) {
                    obj.append('<option value="' + arr[i]['Id'] + '">' + arr[i]['Name'] + '</option>');
                }
            }
        }
    });
}
//动态查询某部门所有员工（事件）
function getStuffs(data) {
    if($(data).val()=='second_dept_23'){
        $("#dcHidden").removeClass('hiddenDiv').addClass('dcAttr');
        $("#groupId").removeClass('groupAttr');
        var dc=$("#dcGroup");
        dc.empty().append('<option value="">-数据中心-</option>');
        $.ajax({
            type:"GET",
            url:"/handover/getDCDept",
            success: function (arr) {
                if (arr) {
                    for (var i = 0; i < arr.length; i++) {
                        dc.append('<option value="' + arr[i]['UsersName'] + '">' + arr[i]['UsersName'].replace("数据中心组",'') + '</option>');
                    }
                }
            }
        });
    }
    else {
    $("#dcHidden").removeClass('dcAttr').addClass('hiddenDiv');
    $("#groupId").addClass('groupAttr');
    var obj = $('#chargerId');
    obj.empty().append('<option value="">-请选择-</option>');
    var obj1 = $('#chargerTwoId');
    obj1.empty().append('<option value="">-请选择-</option>');
        var ul = $(".hiddenUl");
        ul.empty();
    $.ajax({
        type: "GET",
        url: "/handover/getDepStuffs?depId=" + $(data).val(),
        success: function (arr) {
            if (arr) {
                $("#dcGroup").empty().append('<option value="">-请选择-</option>');
                for (var i = 0; i < arr.length; i++) {
                    obj.append('<option value="' + arr[i]['Id'] + '">' + arr[i]['Name'] + '</option>');
                    obj1.append('<option value="' + arr[i]['Id'] + '">' + arr[i]['Name'] + '</option>');
                    ul.append('<li onclick="copystf(this)" value="' + arr[i]['Id'] + '">' + arr[i]['Name'] + '</li>');
                }
            }
        }
    });
    }
}
function getDCStuffs(data) {
    var obj = $('#chargerId');
    obj.empty().append('<option value="">-请选择-</option>');
    var obj1 = $('#chargerTwoId');
    obj1.empty().append('<option value="">-请选择-</option>');
    var ul = $(".hiddenUl");
    ul.empty();
    $.ajax({
        type: "GET",
        url: "/handover/getDepStuffs?depId=" + $(data).val(),
        success: function (arr) {
            if (arr) {
                for (var i = 0; i < arr.length; i++) {
                    obj.append('<option value="' + arr[i]['Id'] + '">' + arr[i]['Name'] + '</option>');
                    obj1.append('<option value="' + arr[i]['Id'] + '">' + arr[i]['Name'] + '</option>');
                    ul.append('<li onclick="copystf(this)" value="' + arr[i]['Id'] + '">' + arr[i]['Name'] + '</li>');
                }
            }
        }
    });
}
function getStuffsTwo(data) {
    var obj = $('#chargerTwoId');
    obj.empty().append('<option value="">-请选择-</option>');
    $.ajax({
        type: "GET",
        url: "/handover/getDepStuffs?depId=" + $(data).val(),
        success: function (arr) {
            if (arr) {
                for (var i = 0; i < arr.length; i++) {
                    obj.append('<option value="' + arr[i]['Id'] + '">' + arr[i]['Name'] + '</option>');
                }
            }
        }
    });
}

$(function () {
    $("#tpye").change(function () {
        alert(122);
        if ($("#type").val() == 'Rebuild') {
            alert(111);
        }

    })
})

$(document).ready(function () {
    $('#supportId').focus(function () {
        $('#hiddenIdDiv').removeClass('hiddenDiv');
    });
    $('#support').blur(function(){
        if($(this).val() == ''){
            $('#supportId').val('');
            $('#cusName').val('').attr('disabled',false);
            $('#cusname').val('');
            $('#CusId').val('');
        }
    })
});
$(document).ready(function () {
    $('#cusName').focus(function () {
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