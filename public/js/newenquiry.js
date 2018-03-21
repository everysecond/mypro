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
    window.editor1 = K.create('#body', {
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

var start = {
    elem: '#expectTs',
    format: 'YYYY-MM-DD hh:mm',
    min: laydate.now(), //设定最小日期为当前日期
    max: '2099-06-16 23:59', //最大日期
    istime: true,
    istoday: false,
    choose: function (datas) {
        if (Date.parse(datas) / 1000 < Date.parse(laydate.now()) / 1000) {
            $('#expectTs').val(laydate.now());
        }
    }
};
laydate(start);

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
    if ($(".msgValidate").val()=="") {
        layer.msg("询价内容不得为空", {icon: 2, time: 2000});
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

//提交
var validateMark = false;
$('.btnSub').unbind();
$('.btnSub').click(function () {
    $(this).attr('disabled', 'disabled');
    var indexValidate = layer.load(0, {shade: false});
    var route = $('#route').val();
    validateMark = false;
    var type = $(this).data("type");
    validate(indexValidate);
    //判断是只保存还是保存并审核通过或者审核不通过
    if (!validateMark) {
        $.ajax({
            type: "POST",
            data: $('#newEnquiry').serialize(),
            url: "/enquiry/enquirySub?saveType="+type,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
            },
            success: function (arr) {
                if (arr.status) {
                    layer.msg(arr.msg, {icon: 1, time: 2000},function(){
                        parent.$('#salesTable').bootstrapTable('refresh');
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