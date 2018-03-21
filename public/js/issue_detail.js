$(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
})

function validateAlert(msg) {//表单验证不通过提示方法
    layer.alert(msg, {icon: 2, closeBtn: false, area: '100px'});
}

if ($('#start').length > 0) {
    //变更事件窗口日期范围限制，默认开始时间最早为今天
    var start = {
        elem: '#start',
        format: 'YYYY/MM/DD hh:mm:ss',
        min: laydate.now(), //设定最小日期为当前日期
        max: '2099-06-16 23:59:59', //最大日期
        istime: true,
        istoday: false,
        choose: function (datas) {
            end.min = datas; //开始日选好后，重置结束日的最小日期
            expectDate.min = datas; //开始日选好后，重置结束日的最小日期
            end.start = datas;//将结束日的初始值设定为开始日
            expectDate.start = datas;//将预计完成时间的初始值设定为开始日
        }
    };
    var end = {
        elem: '#end',
        format: 'YYYY/MM/DD hh:mm:ss',
        min: laydate.now(),
        max: '2099-06-16 23:59:59',
        istime: true,
        istoday: false,
        choose: function (datas) {
            start.max = datas; //结束日选好后，重置开始日的最大日期
        }
    };
    var expectDate = {
        elem: '#expectDate',
        format: 'YYYY/MM/DD hh:mm:ss',
        min: laydate.now(),
        max: '2099-06-16 23:59:59',
        istime: true,
        istoday: false,
        choose: function (datas) {
            start.max = datas; //预计完成时间选好后，重置开始日的最大日期
        }
    };
    laydate(start);
    laydate(end);
    laydate(expectDate);
}

// 问题表单提交
var validateMark = false;
$('.btnSub').click(function () {
    var variableValue = $(this).val();
    $(this).attr('disabled', 'disabled');
    var indexValidate = layer.load(0, {shade: false});
    var route = $('#route').val(), s = $(this).attr('name'), saveOrPass = '';
    validateMark = false;
    validate(s, indexValidate, variableValue);
    variableValue = variableValue == "是"?"通过":variableValue;
    variable = variableValue == "通过" ? 1 : 0;
    //判断是只保存还是保存并审核通过或者审核不通过
    if (!validateMark) {
        $(this).removeClass("reply-btn").addClass("down-btn");
        if (s == 'onlySave') {
            saveOrPass = 'save';
        } else {
            saveOrPass = 'pass';
        }
        layer.confirm("确定要" + variableValue + "吗?", function () {
            $(".layui-layer-btn0").css("display", "none");
            $.ajax({
                type: "POST",
                data: $('#myform').serialize(),
                url: "/issue/" + route + "?" + $('input[name="processVar"]').val() + '=' + variable + "&passOrNo=" + saveOrPass,
                success: function (arr) {
                    if (arr.currentAction == 'editApproval') {
                        layer.msg('操作成功！', {icon: 1, closeBtn: false, area: '100px'});
                        window.location.href = "/issue/details/" + $('Input[name="Id"]').val();
                        layer.close(indexValidate);
                        return;
                    }
                    layer.msg('操作成功！', {icon: 1, closeBtn: false, area: '100px'});
                    layer.close(indexValidate);
                    setTimeout('location.reload()', 500);
                    return;
                }
            });
        }, function () {
            validateMark = true;
            $('.btnSub').removeAttr('disabled');
            layer.close(indexValidate);
            $('.btnSub').removeClass("down-btn").addClass("reply-btn");
        })
    }
});

//checkForm表单触发变更
$("#trigger").click(function () {
    if (!$("#trigger").prop("checked"))return;
    layer.open({
        type: 2,
        title: '变更管理>变更申请单 （<span style="color:#ff253d">以下全部必填</span>）',
        area: ['840px', '550px'],
        content: '/change/changerefer?triggerId=' + $("#issueId").val() + '&issueId=' + $("#issueId").val(),
        maxmin: true,
        end: function () {
            $('#relateChangeTable').bootstrapTable('refresh');
        }
    });
});

//在关联变更table发起新的变更
$("#triggerChange").click(function () {
    layer.open({
        type: 2,
        title: '变更管理>变更申请单 （<span style="color:#ff253d">以下全部必填</span>）',
        area: ['840px', '550px'],
        content: '/change/changerefer?source=problems&triggerId=' + $("#issueId").val() + '&issueId=' + $("#issueId").val(),
        maxmin: true,
        end: function () {
            $('#relateChangeTable').bootstrapTable('refresh');
        }
    });
});

//在关联变更table发起新的工单申请
$("#triggerSupport").click(function () {
    layer.open({
        type: 2,
        title: '工单管理>提交工单 （<span style="color:#ff253d">*表示必填项</span>）',
        area: ['800px', '640px'],
        content: '/support/create?triggerId=' + $("#issueId").val() + '&issueId=' + $("#issueId").val(),
        maxmin: true,
        end: function () {
            $('#relateSupportTable').bootstrapTable('refresh');
        }
    });
});

//选择关联变更
$("#toRelateChange").click(function () {
    var issueId = $("#issueId").val();
    layer.open({
        type: 2,
        title: '关联变更',
        area: ['790px', '580px'],
        skin: 'layui-layer-rim',
        shade: 0,
        content: ['/issue/relateChange?issueId=' + issueId],
        end: function () {
            $('#relateChangeTable').bootstrapTable('refresh');
        }
    });
});

//选择关联工单
$("#toRelateSupport").click(function () {
    var issueId = $("#issueId").val();
    layer.open({
        type: 2,
        title: '关联工单',
        area: ['790px', '580px'],
        skin: 'layui-layer-rim',
        shade: 0,
        content: ['/issue/relateSupport?issueId=' + issueId],
        end: function () {
            $('#relateSupportTable').bootstrapTable('refresh');
        }
    });
});
//消息所有图片都缩放
$("#recordCommuList img:not(.left-portrait)").each(function () {
    var src = $(this).attr("src");
    if ((src.substr(0, 7).toLowerCase() != "http://") &&
        (src.substr(0, 8).toLowerCase() != "https://") &&
        (src.substr(0, 21).toLowerCase() != "data:image/png;base64")) {
        $(this).attr("src", url + src);
    }
    $(this).attr("src",src.replace("http://itsm.51idc.com","https://itsm.anchnet.com"));

    $(this).addClass("litle-img");
});

//变更所有图片都缩放
$(".job-record img").each(function () {
    var src = $(this).attr("src");
    if ((src.substr(0, 7).toLowerCase() != "http://") &&
        (src.substr(0, 8).toLowerCase() != "https://") &&
        (src.substr(0, 21).toLowerCase() != "data:image/png;base64")) {
        $(this).attr("src", url + src);
    }
    $(this).attr("src",src.replace("http://itsm.51idc.com","https://itsm.anchnet.com"));
    $(this).addClass("litle-img");
});

//变更所有图片都缩放
$(".info-content:gt(0) img").each(function () {
    var src = $(this).attr("src");
    if ((src.substr(0, 7).toLowerCase() != "http://") &&
        (src.substr(0, 8).toLowerCase() != "https://") &&
        (src.substr(0, 21).toLowerCase() != "data:image/png;base64")) {
        $(this).attr("src", url + src);
    }
    $(this).attr("src",src.replace("http://itsm.51idc.com","https://itsm.anchnet.com"));
    $(this).addClass("litle-img");
});

$("a.ke-insertfile").each(function () {
    var href = $(this).attr("href");
    $(this).attr("href",href.replace("http://itsm.51idc.com","https://itsm.anchnet.com"));
});

// 切换标题标签
function switchTitleLabel(currentElement, callback) {
    $(".job-record .label-title .title_active").children(".label_line").remove();
    $(".job-record .label-title .title_active").removeClass("title_active");
    currentElement.addClass("title_active").append('<span class="label_line"></span>');
    if (callback && {}.toString.call(callback) === "[object Function]") {
        callback();
    }
};

// 记录列表 标题切换
$("#recordCommu").click(function () {
    switchTitleLabel($(this));
    $(".job-record .record-list").addClass("hide");
    $("#recordCommuList").removeClass("hide");
});

$("#recordChange").click(function () {
    switchTitleLabel($(this));
    $(".job-record .record-list").addClass("hide");
    $("#recordChangeList").removeClass("hide");
});

$("#recordSupport").click(function () {
    switchTitleLabel($(this));
    $(".job-record .record-list").addClass("hide");
    $("#recordSupportList").removeClass("hide");
});

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
    window.editor1 = K.create('#msg', {
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

//网页编辑器
KindEditor.options.filterMode = false;
KindEditor.ready(function (K) {
    window.editor1 = K.create('#msg1', {
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
//网页编辑器
KindEditor.options.filterMode = false;
KindEditor.ready(function (K) {
    window.editor1 = K.create('#msg2', {
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


//表单提交验证
function validate(s, indexValidate, variableValue) {
    if ($(this).hasClass("down-btn")) {
        validateMark = true;
        $('.btnSub').removeAttr('disabled');
        layer.close(indexValidate);
        return false;//防止重复提交
    }
    var passorno = $('input[name="processVar"]').val();
    var yesorno = passorno != '' ? (variableValue == "通过" ? 1 : 0) : '';
    if (s == 'onlySave') {//只保存数据,可以不填，但填写了需要验证
        $('.msgValidate').each(function () {
            if ($(this).val() != '' && $(this).val().length < 40) {
                validateAlert($(this).data('name') + '不得少于40字');
                validateMark = true;
                $('.btnSub').removeAttr('disabled');
                layer.close(indexValidate);
                return false;
            }
        });
    } else {//提交
        if (!validateMark) {//判断是否选择问题来源
            var isExist = false;
            var isChecked = false;
            $('.sourceBox').each(function () {
                isExist = true;
                if ($(this).is(':checked')) {
                    isChecked = true;
                    return false;
                }
            });
            if (!isChecked && isExist) {
                layer.alert('请选择问题来源!', {icon: 2, closeBtn: false, area: '100px'});
                validateMark = true;
                $('.btnSub').removeAttr('disabled');
                layer.close(indexValidate);
                return false;
            }
        }
        if (passorno == '' || yesorno == 1) {
            $('.msgValidate').each(function () {
                if ($(this).val().length < 40) {
                    if ($(this).data('name') != '问题根本原因' || $('#whetherAnalysis').val() == 1) {
                        validateAlert($(this).data('name') + '不得少于20字');
                        validateMark = true;
                        $('.btnSub').removeAttr('disabled');
                        layer.close(indexValidate);
                        return false;
                    }
                }
            });
            $('.contentValidate').each(function () {
                if ($(this).val().length < 20) {
                    if ($(this).data('name') != '变更风险及影响分析' || $('#changeType').val() == 'important') {
                        validateAlert($(this).data('name') + '不得少于20字');
                        validateMark = true;
                        $('.btnSub').removeAttr('disabled');
                        layer.close(indexValidate);
                        return false;
                    }
                }
            });

            $('.approvalValidate').each(function () {
                if ($(this).val().length == '') {
                    if ($(this).data('name') != '变更风险及影响分析' || $('#issuePriority').val() == 1) {
                        validateAlert($(this).data('name') + '不能为空');
                        validateMark = true;
                        $('.btnSub').removeAttr('disabled');
                        layer.close(indexValidate);
                        return false;
                    }
                }
            });
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
    }
}



