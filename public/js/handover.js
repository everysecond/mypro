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

//网页编辑器
KindEditor.options.filterMode = false;

KindEditor.ready(function (K) {
    window.editor1 = K.create('#feedback', {
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

$("#addusers").click(function () {

});

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
/*查询工单编号*/
var globaldata;
var contactBsSuggest = $("#support").bsSuggest({
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
    url: '/handover/eventApply?code=utf-8&extras=1&Id=',
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
                "Keyword": json[j].Id
            });
        }
        return data;
    }

}).on("onSetSelectValue", function (e, keyword) {
    $('#supportId').val(globaldata[keyword.id - 1].Id);
    $('#hiddenDiv').addClass('hiddenDiv');
    $('#cusName').val(globaldata[keyword.id - 1].cusName).attr('disabled','disabled');
    $('#cusname').val(globaldata[keyword.id - 1].cusName);
    $('#CusId').val(globaldata[keyword.id - 1].CustomerInfoId);

    var supportId = globaldata[keyword.id - 1].Id;
    if (supportId != '') {
        $.ajax({
            type: "Get",
            url: "/handover/eventApply?supportId=" + supportId,
            success: function (msg) {
                for (var key in msg) {
                    if (msg[key]['ClassInficationOne']!=null) {
                        $('#type').val(msg[key]['ClassInficationOne']);
                        $('#typeName').val(msg[key]['typeName']);
                    }
                    if (msg[key]['priority']!=null) {
                        $('#priority').val(msg[key]['priority']);
                    }

                }
            }
        });
    }
})

$(document).ready(function () {
    $('#cusName').focus(function () {
        $('#hiddenDiv').removeClass('hiddenDiv');
        $('#cusname').focus();
        $('#cusname').addClass('hiddenDiv');
    });
});
/*查询客户名*/
var globaldata;
var contactBsSuggest = $("#cusname").bsSuggest({
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
    url: '/handover/eventApply?code=utf-8&extras=1&name=',
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
                "Keyword": json[j].CusName
            });
        }
        return data;
    }

}).on("onSetSelectValue", function (e, keyword) {
    $('#CusId').val(globaldata[keyword.id - 1].Id);
    $('#cusId').val(globaldata[keyword.id - 1].Id);
    $('#cusname').val(globaldata[keyword.id - 1].CusName);
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