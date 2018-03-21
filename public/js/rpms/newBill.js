$(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
})

$(document).ready(function () {
    $('#contractNo').focus(function () {
        var seq =$('#billSeq').val();
        if(null == seq || "" == seq){
            $('#hiddenDiv').removeClass('hiddenDiv');
            $('#cusname').focus();
            $('#cusname').addClass('hiddenDiv');
        }
    });
});

Date.prototype.Format = function (fmt) {
    var o = {
        "M+": this.getMonth() + 1, //月份
        "d+": this.getDate(), //日
        "h+": this.getHours(), //小时
        "m+": this.getMinutes(), //分
        "s+": this.getSeconds(), //秒
        "q+": Math.floor((this.getMonth() + 3) / 3), //季度
        "S": this.getMilliseconds() //毫秒
    };
    if (/(y+)/.test(fmt))
        fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o){
        if (new RegExp("(" + k + ")").test(fmt)) {
            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
        }
    }
    return fmt;
};


/*查询供应商*/
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
        Keyword: "供应商"
    },
    showHeader: false,
    url: '/rpms/resourceBill/findContractBySearch?code=utf-8&extras=1&name=',
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
                "Keyword": json[j].contractNo
            });
        }
        return data;
    }

}).on("onSetSelectValue", function (e, keyword) {
    $('#contractId').val(globaldata[keyword.id - 1].id);
    $('#contractNo').val(globaldata[keyword.id - 1].contractNo);
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
                layer.msg("供应商和合同编号不能为空!",{time: 3000});
                validateMark = true;
                $('.btnSub').removeAttr('disabled');
                layer.close(indexValidate);
                return false;
            }
        });

        if($('#status').val() == "doing"){
            $('.validateDoing').each(function () {
                if ($(this).val() == '') {
                    layer.msg("执行中合同需要完善合同开始结束日期,合同周期及付款信息!",{time: 3000});
                    validateMark = true;
                    $('.btnSub').removeAttr('disabled');
                    layer.close(indexValidate);
                    return false;
                }
            });
            if($("#endTs").val() < $("#startTs").val()){
                layer.msg("合同结束时间小于开始时间，请重新调整!",{time: 3000});
                validateMark = true;
                $('.btnSub').removeAttr('disabled');
                layer.close(indexValidate);
                return false;
            }
        }
    }
}