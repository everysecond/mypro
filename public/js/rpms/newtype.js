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

$(document).ready(function () {
    $('#parentTypeName').focus(function () {
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
    url: '/rpms/resourceType/getProdType?code=utf-8&extras=1&selfName='+$("#typeName").val()+'&selfType='+$("#typeCode").val()+'&name=',
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
                "Keyword": json[j].typeName
            });
        }
        return data;
    }

}).on("onSetSelectValue", function (e, keyword) {
    $('#parentTypeCode').val(globaldata[keyword.id - 1].typeCode);
    $('#parentTypeName').val(globaldata[keyword.id - 1].typeName);
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
            data: $('#newType').serialize(),
            url: "/rpms/resourceType/newTypeSub",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            },
            success: function (arr) {
                if (arr.status) {
                    layer.msg(arr.msg, {time: 2000},function(){
                        parent.$('#typeTable').bootstrapTable('refresh');
                        closeFrame();
                    });
                } else {
                    if (arr.typeId) {
                        layer.confirm(arr.msg,{title: "提示", btn: ['确定', '取消']},function(){
                            var sIds = [{id: arr.typeId}];
                            $.ajax({
                                type: "POST",
                                data: {'supIds': sIds,'batchType':"up"},
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                                },
                                url: "/rpms/resourceType/batchOperate",
                                success: function (data) {
                                    if (data.status == 'success') {
                                        layer.msg('启用成功！', {icon: 1,time:1500},function(){
                                            parent.$('#typeTable').bootstrapTable('refresh');
                                            closeFrame();
                                        });
                                    }
                                }
                            })
                        },function(){
                            layer.close(indexValidate);
                            $(".btnSub").removeAttr('disabled');
                        });
                    } else {
                        layer.msg(arr.msg, {icon: 2, time: 2000},function(){
                            layer.close(indexValidate);
                            $(".btnSub").removeAttr('disabled');
                        });
                    }
                }

            }
        });
    }
});