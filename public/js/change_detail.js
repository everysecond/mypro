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
            expectDate.min = datas;
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

//变更表单提交验证
function validate(s, indexValidate,variableValue) {
    if ($(this).hasClass("down-btn")) {
        validateMark = true;
        $('.btnSub').removeAttr('disabled');
        layer.close(indexValidate);
        return false;//防止重复提交
    }
    var passorno = $('input[name="processVar"]').val();
    var yesorno = passorno != '' ?(variableValue == "通过"?1:0) : '';
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
        if (passorno == '' || yesorno == 1) {
            $('.msgValidate').each(function () {//普通文本需要四十字至少
                if ($(this).val().length < 40) {
                    if ($(this).data('name') != '变更风险及影响分析' || $('#changeType').val() == 'important') {
                        validateAlert($(this).data('name') + '不得少于40字');
                        validateMark = true;
                        $('.btnSub').removeAttr('disabled');
                        layer.close(indexValidate);
                        return false;
                    }
                }
            });
            $('.contentValidate').each(function () {//编辑变更驳回的文本需20字
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
                    if ($(this).data('name') != '变更风险及影响分析' || $('#changeType').val() == 'important') {
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

//变更表单提交
var validateMark = false;
$('.btnSub').click(function () {
    var variableValue = $(this).val();
    $(this).attr('disabled', 'disabled');
    var indexValidate = layer.load(0, {shade: false});
    var route = $('#route').val(), s = $(this).attr('name'), saveOrPass = '';
    validateMark = false;
            validate(s, indexValidate,variableValue);
    var variable = variableValue == "通过"?1:0;
            //判断是只保存还是保存并审核通过或者审核不通过
    if (!validateMark) {
        $(this).removeClass("reply-btn").addClass("down-btn");
        if (s == 'onlySave') {
            saveOrPass = 'save';
        } else {
            saveOrPass = 'pass';
        }
        layer.confirm("确定要"+variableValue+"吗?",function(){
            $(".layui-layer-btn0").css("display","none");
            $.ajax({
                type: "POST",
                data: $('#myform').serialize(),
                url: "/change/" + route + "?"+ $('input[name="processVar"]').val() +'=' +variable+"&passOrNo=" + saveOrPass,
                success: function (arr) {
                    if (arr.currentAction == 'editApproval') {
                        layer.msg('操作成功！', {icon: 1, closeBtn: false, area: '100px'});
                        window.location.href = "/change/details/" + $('Input[name="changeId"]').val();
                        layer.close(indexValidate);
                        return;
                    }
                    layer.msg('操作成功！', {icon: 1, closeBtn: false, area: '100px'});
                    layer.close(indexValidate);
                    setTimeout('location.reload()', 500);
                    return;
                }
            });
        },function(){
            validateMark = true;
            $('.btnSub').removeAttr('disabled');
            layer.close(indexValidate);
            $('.btnSub').removeClass("down-btn").addClass("reply-btn");
        })

    }
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

$("#recordSupport").click(function () {
    switchTitleLabel($(this));
    $(".job-record .record-list").addClass("hide");
    $("#recordSupportList").removeClass("hide");
});

$("#recordIssue").click(function () {
    switchTitleLabel($(this));
    $(".job-record .record-list").addClass("hide");
    $("#recordIssueList").removeClass("hide");
});

$("#recordRemark").click(function () {
    switchTitleLabel($(this));
    $(".job-record .record-list").addClass("hide");
    $("#recordRemarkList").removeClass("hide");
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

//动态查询某部门所有员工
function getStuff(data) {
    var obj = $('#changeImplementUserId');
    obj.empty().append('<option value="">-请选择-</option>');
    $.ajax({
        type: "GET",
        url: "/change/getDepStuffs?role=" + $(data).data('role') + "&depId=" + $(data).val(),
        success: function (arr) {
            if (arr) {
                for (var i = 0; i < arr.length; i++) {
                    obj.append('<option value="' + arr[i]['Id'] + '">' + arr[i]['Name'] + '</option>');
                }
            }
        }
    });
}
//获取
function getStuffMenber(data) {
    var obj = $('#proDesigerId');
    obj.empty().append('<option value="">-请选择-</option>');
    $.ajax({
        type: "GET",
        url: "/change/getDepStuffs?depId=" + $(data).val(),
        success: function (arr) {
            if (arr) {
                for (var i = 0; i < arr.length; i++) {
                    obj.append('<option value="' + arr[i]['Id'] + '">' + arr[i]['Name'] + '</option>');
                }
            }
        }
    });
}

//消息提示
function lalert(txt) {
    if (txt != '')
        layer.alert(txt, {icon: 2, closeBtn: false, area: '100px'});
}
$(function () {
    //TOKEN验证
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    //动态查询指派人
    $("select[name='group1'],select[name='group2']").on("change", function () {
        var name = $(this).attr("name");
        var id = $(this).find("option:selected").val();
        var obj = (name == "group1") ? "optuser1" : "optuser2";
        obj = $("select[name='" + obj + "']");
        obj.empty().append('<option value="">-请选择-</option>');
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

    //消息回复
    $("#btnReply").click(function () {
        if ($(this).hasClass("btn-default")) {
            return false;//防止重复提交
        }
        var msg = $("#msg").val();
        msg = $.trim(msg);
        if (msg == '') {
            lalert("请输入回复内容!");
            return false;
        }
        $("#msg").val("");
        var sid = $("input[name='sid']").val();

        $("#processing").addClass("inline").removeClass("hide");
        $(this).text("回复中...");
        $(this).removeClass("btn-primary").addClass("btn-default");
        $.ajax({
            type: "post",
            dataType: 'json',
            url: "/wo/reply",
            data: {'msg': msg, 'sid': sid},
            success: function (data) {
                if (data && data.status) {
                    layer.msg('回复成功！', {
                        icon: 1,
                        time: 1000 //1秒关闭
                    }, function () {
                        location.reload();
                    });
                }
            }
        });
    });

    //确认回复
    $("span[name='sure_reply']").click(function () {
        var id = $(this).attr("data_id");
        layer.confirm('您确定要执行该操作？', {icon: 3, title: '确认回复'}, function (index) {
            $.ajax({
                type: "post",
                dataType: 'json',
                url: "/wo/surereply/" + id,
                success: function (data) {

                    if (data && data.status) {
                        layer.msg('操作成功！', {
                            icon: 1,
                            time: 1000 //1秒关闭
                        }, function () {
                            location.reload();
                        });
                    }
                    layer.close(index);
                }
            });

        });
    });

    //编辑回复
    $("span[name='edit_reply']").click(function () {
        var id = $(this).attr("data_id");
        layer.open({
            type: 2,
            title: '工单回复内容编辑',
            maxmin: false,
            skin: "layui-layer-molv",
            shade: [0.1, '#393D49'],
            area: ['800px', '330px'],
            content: '/wo/editreply/' + id
        });
    });

    //删除回复
    $("span[name='del_reply']").click(function () {
        var id = $(this).attr("data_id");
        layer.confirm('您确定要删除该回复信息么？', {icon: 3, title: '删除'}, function (index) {
            $.ajax({
                type: "post",
                dataType: 'json',
                url: "/wo/delreply/" + id,
                success: function (data) {

                    if (data && data.status) {
                        layer.msg('操作成功！', {
                            icon: 1,
                            time: 1000 //1秒关闭
                        }, function () {
                            location.reload();
                        });
                    }
                    layer.close(index);
                }
            });

        });
    });

    //工单拆分
    $('#supportSplit').click(function () {
        var sid = $("input[name='sid']").val();
        layer.open({
            type: 2,
            title: '◆工单管理>工单详情>工单拆分',
            area: ['800px', '550px'],
            skin: "layui-layer-molv",
            shade: [0.1, '#393D49'],
            content: ['/support/supportSplit?supId=' + sid, 'no']
        });
    });

    //发送短信
    $("#sendMsg").click(function () {
        var sid = $("input[name='sid']").val();
        layer.open({
            type: 2,
            title: '短信发送详情',
            maxmin: false,
            skin: "layui-layer-molv",
            shade: [0.1, '#393D49'],
            area: ['800px', '385px'],
            content: '/wo/sendsms/' + sid
        });
    });

    //工单挂起
    $("#hangup").click(function () {
        var sid = $("input[name='sid']").val();
        layer.open({
            type: 2,
            title: '工单挂起',
            skin: "layui-layer-molv",
            shade: [0.1, '#393D49'],
            maxmin: false,
            scrollbar: false,
            area: ['500px', '630px'],
            content: '/wo/hangup/' + sid
        });
    });

    //释放挂起
    $("#release").click(function () {
        layer.confirm('您确定要释放此工单?', {icon: 3, title: '提示'}, function (index) {
            var sid = $("input[name='sid']").val();
            if ($(".layui-layer-btn0").attr("isload") == 1) {
                return false;
            }
            $(".layui-layer-btn0").attr("isload", 1);
            $.ajax({
                type: "post",
                dataType: 'json',
                url: "/wo/release/" + sid,
                success: function (data) {
                    if (data && data.status) {
                        layer.msg('操作成功！', {
                            icon: 1,
                            time: 1000 //1秒关闭
                        }, function () {
                            location.reload();
                        });
                    }
                    layer.close(index);
                }
            });
        });
    });

    //撤销消息
    $(".fa_mail_reply").click(function () {
        var timelimits = timelimit * 1000;
        var replytime = $(this).attr("data_time");
        replytime = Date.parse(replytime);
        var now = Date.parse(new Date());
        var id = $(this).attr("data_id");
        if ((now - replytime) > timelimits) {
            lalert("回复时间超过" + parseInt(timelimit / 60, 10) + "分钟的消息，不能被撤回。");
            return false;
        }
        layer.confirm('您确定要撤回该回复信息么？', {icon: 3, title: '提示'}, function (index) {
            $.ajax({
                type: "post",
                dataType: 'json',
                url: "/wo/prescind/" + id,
                success: function (data) {
                    if (data && data.status) {
                        layer.msg('操作成功！', {
                            icon: 1,
                            time: 1000 //1秒关闭
                        }, function () {
                            location.reload();
                        });
                    }
                    layer.close(index);
                }
            });
        });
    });

//配额审核通过
    $("#pepass").click(function () {
        layer.confirm('配额确定审核通过吗?', {icon: 3, title: '提示'}, function (index) {
            var sid = $("input[name='sid']").val();
            $.ajax({
                type: "post",
                dataType: 'json',
                url: "/wo/postquota/" + sid,
                success: function (data) {
                    if (data) {
                        layer.msg(data.status ? '审核成功！' : "审核失败", {
                            icon: 1,
                            time: 1000 //1秒关闭
                        }, function () {
                            location.reload();
                        });
                    }
                    layer.close(index);
                }
            });
        });
    });
    //云列表
    $("#cloudReckon").click(function () {
        var sid = $("input[name='sid']").val();
        layer.open({
            type: 2,
            title: ' ',
            maxmin: false,
            skin: "layui-layer-molv",
            shade: [0.1, '#393D49'],
            area: ['800px', '400px'],
            content: '/wo/cloud/' + sid
        });
    });
    //显示/隐藏
    $("#displayobj").click(function () {
        $("[name='hidenode']").toggle();
        if ($(".fa-sort-up").length > 0) {
            $(".fa-sort-up").removeClass("fa-sort-up").addClass("fa-sort-desc").parent("a").attr("title", "显示");
        } else {
            $(".fa-sort-desc").removeClass("fa-sort-desc").addClass("fa-sort-up").parent("a").attr("title", "隐藏");
        }
    })
    //在关联问题table发起新的问题申请
    $("#triggerIssue").click(function () {
        layer.open({
            type: 2,
            title: '问题管理>问题申请单 （<span style="color:#ff253d">以下全部必填</span>）',
            area: ['840px', '550px'],
            content: '/issue/issueapply?triggerId=' + $("#changeId").val() + '&changeId=' + $("#changeId").val(),
            maxmin: true,
            end: function () {
                $('#relateIssueTable').bootstrapTable('refresh');
            }
        });
    });
    //在关联工单table发起新的工单申请
    $("#triggerSupport").click(function () {
        layer.open({
            type: 2,
            title: '工单管理>提交工单 （<span style="color:#ff253d">*表示必填项</span>）',
            area: ['800px', '640px'],
            content: '/support/create?triggerId=' + $("#changeId").val() + '&changeId=' + $("#changeId").val(),
            maxmin: true,
            end: function () {
                $('#relateSupportTable').bootstrapTable('refresh');
            }
        });
    });
    //选择关联问题
    $("#toRelateIssue").click(function () {
        var changeId = $("#changeId").val();
        layer.open({
            type: 2,
            title: '关联问题',
            area: ['790px', '570px'],
            shade: 0,
            content: ['/change/relateIssue?changeId=' + changeId],
            end: function () {
                $('#relateIssueTable').bootstrapTable('refresh');
            }
        });
    });
    //选择关联工单
    $("#toRelateSupport").click(function () {
        var changeId = $("#changeId").val();
        layer.open({
            type: 2,
            title: '关联工单',
            area: ['790px', '570px'],
            shade: 0,
            content: ['/change/relateSupport?changeId=' + changeId],
            end: function () {
                $('#relateSupportTable').bootstrapTable('refresh');
            }
        });
    });
})
