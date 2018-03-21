//消息所有图片都缩放
$("#recordCommuList img:not(.left-portrait),#recordCommuListCont img:not(.left-portrait)").each(function () {
    var src = $(this).attr("src"),
        viewHeight = $(window).height(),
        viewWidth = $(window).width();

    if ((src.substr(0, 7).toLowerCase() != "http://") &&
        (src.substr(0, 8).toLowerCase() != "https://") && (src.substr(0, 10).toLowerCase() != "data:image")) {
        url = typeof(url) != 'undefined' ?url:'';
        $(this).attr("src", url + src);
    }
    var objimg = $(this),
        h = objimg.height(),
        w = objimg.width();
    if(h>0&&w>0){
        var nobjimg = AutoResizeImage((w > viewWidth) ? viewWidth * 0.9 : w, (h > viewHeight) ? viewHeight * 0.9 : h, objimg);
        objimg.attr("data-wRatio", nobjimg.width);
        objimg.attr("data-hRatio", nobjimg.height);
        $(this).addClass("litle-img");
    }else{
        objimg.remove();
    }
});
//图片等比例缩放
function AutoResizeImage(maxWidth, maxHeight, objImg) {
    var img = objImg;
    var hRatio;
    var wRatio;
    var Ratio = 1;
    var w = img.width();
    var h = img.height();
    wRatio = maxWidth / w;
    hRatio = maxHeight / h;
    if (maxWidth == 0 && maxHeight == 0) {
        Ratio = 1;
    } else if (maxWidth == 0) {//
        if (hRatio < 1) Ratio = hRatio;
    } else if (maxHeight == 0) {
        if (wRatio < 1) Ratio = wRatio;
    } else if (wRatio < 1 || hRatio < 1) {
        Ratio = (wRatio <= hRatio ? wRatio : hRatio);
    }
    if (Ratio < 1) {
        w = w * Ratio;
        h = h * Ratio;
    }
    objImg.height = h;
    objImg.width = w;
    return objImg;
}
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
    $("#recordCommuListCont").removeClass("hide");
});

$("#relateChange").click(function () {
    switchTitleLabel($(this));
    $(".job-record .record-list").addClass("hide");
    $("#relateChangeList").removeClass("hide");
});

$("#relateIssue").click(function () {
    switchTitleLabel($(this));
    $(".job-record .record-list").addClass("hide");
    $("#relateIssueList").removeClass("hide");
});

$("#recordQuestion").click(function () {
    switchTitleLabel($(this));
    $(".job-record .record-list").addClass("hide");
    $("#recordQuestionList").removeClass("hide");
});

$("#recordChange").click(function () {
    switchTitleLabel($(this));
    $(".job-record .record-list").addClass("hide");
    $("#recordChangeList").removeClass("hide");
});

$("#recordRemark").click(function () {
    switchTitleLabel($(this));
    $(".job-record .record-list").addClass("hide");
    $("#recordRemarkList").removeClass("hide");
});
/*$("body").on("click",".litle-img",function(){
    $(".large-img").attr("src", $(this).attr("src"));
    if ($(this).attr("data-wRatio")) {
        $(".large-img").css("height", $(this).attr("data-hRatio"));
        $(".large-img").css("width", $(this).attr("data-wRatio"));
    }
    $("#enlargeImage").removeClass("hide");
})*/
// 点击查看大图
$(".litle-img").click(function () {
    $(".large-img").attr("src", $(this).attr("src"));
    if ($(this).attr("data-wRatio")) {
        $(".large-img").css("height", $(this).attr("data-hRatio"));
        $(".large-img").css("width", $(this).attr("data-wRatio"));
    }
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


function mouseover(data) {
    $(".ke-menu-item-right .menuTitle").css("display", "none");
    $(data).next("div:eq(0)").css("display", "block");
    /*self.hideMenu();*/
}

var layerIndex;
function mouseovert(data) {
    $(".ke-menu-item-right li p").css('background-color', '#F1F1F1');
    //$(".ke-menu-item-right div").css('background-color', '#F1F1F1');
    $(".ke-menu-item-right span").css('color', '#74787c');
    //$(data).css('background-color', '#74787c');
    $(data).children('span').css('color', 'black');
    layerIndex = layer.tips($(data).data('value'), $(data), {time: 0, tips: [2, '#74787c'], maxWidth: 400})
}

function layerClose() {
    layer.close(layerIndex);
}

function addContent(data) {
    var addContent = $("#msg").val() + $(data).data('value');
    editor1.insertHtml($(data).data('value'));
    selfMenu.hideMenu();
    layer.close(layerIndex);
}

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

function jumpTo() {
    parent.$('.J_menuItem[menuname="快速回复模版"]').click();
    //layer.open({
    //    type: 2,
    //    title: '工单管理>快速回复模板>新增模板 （<span style="color:#ff253d">*表示必填项</span>）',
    //    area: ['550px', '400px'],
    //    shade: 0.2,
    //    content: ['/support/newRmode', 'no'],
    //    end: function () {
    //        $('.replygroup').bootstrapTable('refresh');
    //    }
    //});
}

var replyList = '', selfMenu = "", mode = '',last='',last2='';
var type3 = $("#type3").val();
$.ajax({
    type: "POST",
    data: {type3: type3},
    url: "/wo/getreplymode",
    success: function (data) {
        //if(Object.keys(data).length>0) $str = str_replace(array("/r/n", "/r", "/n"), "", $str);
        if (Object.keys(data).length>0) {
            replyList += '<div><span style="font-size: 14px;"><a onclick="jumpTo()" href="javascript:void(0)" title="前往模板列表">' +
                '<img src="/img/icon/edit.png" height="12px" width="16px" /></a>回复模板</span></div>';
            replyList +='<div style="max-height: 250px;width:188px;overflow-x: hidden;overflow-y: auto;font-size: 12px"><ul>';
            for (var i in data) {
                if (i == '服务台组') {
                    var num = 1;
                    var num1=1;
                    for (var j in data[i]) {
                        if(data[i][j]['type']){
                            num = num.toString().length > 1 ? num : '0' + num.toString();
                        replyList += '<li onmouseover="mouseovert(this)" onmouseout="layerClose()" style="width: 188px"' +
                            'onclick="addContent(this)" data-value="' + data[i][j]['content'].replace(/\r\n/,'<br>').replace(/[ ]/g,'&nbsp;') + '"><span style="font-size: 10px;">' +
                            '<div style="width:50px; height:50px;border-radius:25px;display: inline;font-size: x-small;color: #74787c">' + num + '</div>'
                            + '&nbsp;&nbsp;&nbsp;' + data[i][j]['title'] + '</span></li>';
                        num++;
                        }
                        else{
                            num1 = num1.toString().length > 1 ? num1 : '0' + num1.toString();
                            last += '<li onmouseover="mouseovert(this)" onmouseout="layerClose()" style="width: 188px"' +
                                'onclick="addContent(this)" data-value="' + data[i][j]['content'].replace(/\r\n/,'<br>').replace(/[ ]/g,'&nbsp;') + '"><span style="font-size: 10px;">' +'<a class="fa fa-gg" style="color: #1f18ff"></a>'+
                                '<div style="width:50px; height:50px;border-radius:25px;display: inline;font-size: x-small;color: #1f18ff">' + num1 + '</div>'
                                + '&nbsp;&nbsp;&nbsp;' + data[i][j]['title'] + '</span></li>';
                            num1++;
                        }
                    }
                    replyList += last;
                }
                if (i == '数据中心组') {
                    var num = 1;
                    var num2 = 1;
                    for (var k in data[i]) {
                        if(data[i][k]['type']){
                        num = num.toString().length > 1 ? num : '0' + num.toString();
                        replyList += '<li onmouseover="mouseovert(this)" onmouseout="layerClose()" style="width: 188px;"' +
                            'onclick="addContent(this)" data-value="' + data[i][k]['content'].replace(/\r\n/,'<br>').replace(/[ ]/g,'&nbsp;') + '"><span style="font-size: 10px;">' +'<span class="fa fa-database"></span>'+
                            '<div style="width:50px; height:50px; display: inline;font-size: x-small;color:#74787c;">' + num + '</div>'
                            + '&nbsp;&nbsp;&nbsp;' + data[i][k]['title'] + '</span></li>';
                        num++;
                        }
                        else{
                            num2 = num2.toString().length > 1 ? num2 : '0' + num2.toString();
                            last2 += '<li onmouseover="mouseovert(this)" onmouseout="layerClose()" style="width: 188px"' +
                                'onclick="addContent(this)" data-value="' + data[i][k]['content'].replace(/\r\n/,'<br>').replace(/[ ]/g,'&nbsp;') + '"><span style="font-size: 10px;">' +'<span class="fa fa-dot-circle-o"></span>'+
                                '<div style="width:50px; height:50px;border-radius:25px;display: inline;font-size: x-small;color: #74787c">' + num2 + '</div>'
                                + '&nbsp;&nbsp;&nbsp;' + data[i][k]['title'] + '</span></li>';
                            num2++;
                        }
                    }
                    replyList += last2+'</ul></div>';
                }
            }

            //网页编辑器
            KindEditor.options.filterMode = false;
            // 自定义插件 #2
            KindEditor.plugin('replymode', function (K) {
                var name = 'replymode';
                selfMenu = this;
                selfMenu.clickToolbar(name, function () {
                    var menu = selfMenu.createMenu({
                        name: name
                    });
                    menu.addItem({
                        title: replyList
                    });
                });
            });
        }
        else {
            $(".reply-editor").find("span[data-name='replymode']").remove()
        }
    }
});
KindEditor.lang({
    replymode: '回复模板'
});


KindEditor.ready(function (K) {
    window.editor1 = K.create('#msg', {
        resizeType: 0,
        uploadJson: "/kindeditor/uploadify",
        width: "100%",
        urlType: "domain",
        items: [
            'justifyleft', 'justifycenter', 'justifyright', 'forecolor', 'hilitecolor', 'bold',
            'italic', 'underline', 'image', 'clearhtml', 'replymode'
        ], afterBlur: function () {
            this.sync();
        }
    });
})

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
        obj.empty().append('<option value="">请选择</option>');
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

    //编辑
    $("#editData").click(function () {
        var sid = $("input[name='sid']").val();
        layer.open({
            type: 2,
            title: '工单相关项编辑',
            maxmin: false,
            skin: "layui-layer-molv",
            shade: [0.1, '#393D49'],
            area: ['780px', '448px'],
            content: '/wo/editsupport/' + sid
        });
    });

    //确认指派
    $("#distribute").click(function () {
        if ($("select[name='thirdclass']").val() == "") {
            lalert("请选择三级分类");
            return false;
        }
        if ($("select[name='datacenter']").val() == "") {
            lalert("请选择数据中心");
            return false;
        }
        if ($("select[name='model']").val() == "") {
            lalert("请选择一级分类,服务模式");
            return false;
        }
        if ($("select[name='group1']").val() == "") {
            lalert("请选择第一负责人工作组");
            return false;
        }
        if ($("select[name='optuser1']").val() == "") {
            lalert("请选择第一负责人");
            return false;
        }
        if ($("select[name='group2']").val() != "") {
            if ($("select[name='optuser2']").val() == "") {
                lalert("请选择第二负责人");
                return false;
            }
            if (($("select[name='group1']").val() == $("select[name='group2']").val()) && ($("select[name='optuser1']").val() == $("select[name='optuser2']").val())) {
                lalert("请不要重复选择");
                return false;
            }
        }
        if ($(this).hasClass("btn-default")) {
            return false;//防止重复提交
        }
        $("#processingbtn").addClass("inline").removeClass("hide");
        $(this).removeClass("btn-primary").addClass("btn-default");
        $.ajax({
            type: "post",
            dataType: 'json',
            url: "/wo/csupport",
            data: $("#myform").serializeArray(),
            success: function (data) {
                if (data.status == true) {
                    layer.msg(data.msg, {
                        icon: 1,
                        time: 1000 //1秒关闭
                    }, function () {
                        location.reload();
                    });
                }
                else if ((data.status == 'appointed')) {
                    layer.confirm(data.msg, {
                        btn: ['确定']
                    }, function () {
                        location.reload();
                    });
                }
                else if ((data.status == 'closed')) {
                    layer.confirm(data.msg, {
                        btn: ['确定']
                    }, function () {
                        location.reload();
                    });
                }
            }
        })
    });

    //重新指派
    $("#reassign").click(function () {
        var sid = $("input[name='sid']").val();
        $.ajax({
            type: "post",
            url: "/wo/getspts/" + sid,
            success: function (data) {
                var currentts = new Date(),
                    spts = new Date(data.SpTs),
                    lastUser = data.Asuser,
                    charger = data.charger,
                    alarmTs = data.alarmTs;

                if (currentts.getTime() - spts.getTime() < alarmTs * 60 * 1000) {
                    var confirmIndex = layer.confirm("该工单在" + alarmTs + "分钟内已被" + lastUser + "指派给" + charger + ",您确定还要重新指派吗?", function () {
                        layer.open({
                            type: 2,
                            title: '重新指派',
                            maxmin: false,
                            skin: "layui-layer-molv",
                            shade: [0.1, '#393D49'],
                            area: ['800px', '275px'],
                            content: '/wo/reassign/' + sid
                        });
                        layer.close(confirmIndex);
                    })
                } else {
                    layer.open({
                        type: 2,
                        title: '重新指派',
                        maxmin: false,
                        skin: "layui-layer-molv",
                        shade: [0.1, '#393D49'],
                        area: ['800px', '275px'],
                        content: '/wo/reassign/' + sid
                    });
                }

            }
        });
    });

    //已处理
    $("#alreadyProc").click(function () {
        layer.confirm('您要将工单设置为已处理吗?', {icon: 3, title: '提示'}, function (index) {
            var id = $("input[name='sid']").val();
            layer.open({
                type: 2,
                title: '工单已处理操作',
                maxmin: false,
                skin: "layui-layer-molv",
                shade: [0.1, '#393D49'],
                area: ['800px', '380px'],
                content: '/wo/alreadyproc/' + id
            });
            layer.close(index);
        });
    });

    $("#resetBtn").click(function () {
        editor1.html("");
        $("#msg").val('');
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

        var replyMark="first";
        $.ajax({
            type: "post",
            dataType: 'json',
            url: "/wo/reply",
            data: {'msg': msg, 'sid': sid,'replyMark':replyMark},
            success: function (data) {
                if(data.status == "confirm"){
                    layer.confirm(data.msg, {
                        btn: ['继续回复','取消']
                    },function(){
                        replyMark="confirmReply";
                        $.ajax({
                            type: "post",
                            dataType: 'json',
                            url: "/wo/reply",
                            data: {'msg': msg, 'sid': sid,'replyMark':replyMark},
                            success: function (data) {
                                if (data.status == true) {
                                    layer.msg('回复成功！', {
                                        icon: 1,
                                        time: 1000 //1秒关闭
                                    }, function () {
                                        location.reload();
                                    });
                                }
                                if (data.status == 'closed') {
                                    layer.confirm(data.msg, {
                                        btn: ['确定']
                                    }, function () {
                                        location.reload();
                                    });
                                }
                            }
                        });
                    }, function () {
                        location.reload();
                    });
                }
                if (data.status == true) {
                    layer.msg('回复成功！', {
                        icon: 1,
                        time: 1000 //1秒关闭
                    }, function () {
                        location.reload();
                    });
                }
                if (data.status == 'closed') {
                    layer.confirm(data.msg, {
                        btn: ['确定']
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

    //确认接受指派
    $("span[name='sure_appoint']").click(function () {
        var id = $('#supportId').val();
        layer.confirm('您确定要执行该操作？', {icon: 3, title: '确认接受?'}, function (index) {
            $.ajax({
                type: "post",
                dataType: 'json',
                url: "/wo/sureappoint/" + id,
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
    //发送短信
    $(".sendEmail").click(function () {
        var email = $(this).data("email");
        layer.open({
            type: 2,
            title: '邮件发送详情',
            maxmin: false,
            skin: "layui-layer-molv",
            shade: [0.1, '#393D49'],
            area: ['800px', '385px'],
            content: '/wo/sendEmail?email='+email
        });
    });

    //工单挂起
    $("#hangup").click(function () {
        var sid = $("input[name='sid']").val();
        layer.open({
            type: 2,
            title: '工单挂起',
            shade: [0.1, '#393D49'],
            maxmin: false,
            skin: "layui-layer-molv",
            scrollbar: false,
            area: ['580px', '570px'],
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
                    if (data.status == true) {
                        layer.msg('操作成功！', {
                            icon: 1,
                            time: 1000 //1秒关闭
                        }, function () {
                            location.reload();
                        });
                    }
                    else if (data.status == 'closed') {
                        layer.confirm(data.msg, {
                            btn: ['确定']
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
                    }else if(data && !data.status){
                        layer.msg(data.msg, {
                            icon: 2,
                            time: 3000 //1秒关闭
                        })
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
            title: '云配额列表',
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
            $("#sdetails").removeClass("showborder");
            $(".fa-sort-up").removeClass("fa-sort-up").addClass("fa-sort-desc").parent("a").attr("title", "显示");
        } else {
            $("#sdetails").addClass("showborder");
            $(".fa-sort-desc").removeClass("fa-sort-desc").addClass("fa-sort-up").parent("a").attr("title", "隐藏");
        }
    });
    $("#yincang").click(function () {
        $("[name='hideDiv']").toggle();
        $("#zhankai").removeClass('hidden');
        $(".fa-unlock").removeClass("fa-unlock").addClass("fa-lock").attr('style','padding-top:15px').parent("a").attr("title", "展开工单详情");
    });
    $("#zhankai").click(function () {
        $("[name='hideDiv']").toggle();
        $("#zhankai").addClass('hidden');
        $(".fa-lock").removeClass("fa-lock").addClass("fa-unlock").attr('style','padding-top:16px;padding-right:7px;float:right').parent("a").attr("title", "收起详情");

    });
});

$(function () {
    var winHeight;
    if (window.innerHeight) winHeight = window.innerHeight;
    else if ((document.body) && (document.body.clientHeight)) winHeight = document.body.clientHeight;
    var fixHeightInterval = setInterval(function () {
        var parentNodeHeight = $("#job-detail-content").closest(".job-detail").height();
        var sibilingNodeHeight = $(".job-info").height();
        var fixHeight = parentNodeHeight - sibilingNodeHeight + "px";
        var fixRightHeight = winHeight - 260 + "px";
        $("#job-detail-content").css({"height": fixHeight});
        $("#similar-job").css({"height": fixRightHeight});
    }, 0);
    setTimeout(function () {
        $("#job-detail-content").scrollTop($("#job-detail-content").prop("scrollHeight"));
    }, 0);//滚动底部
    $(".job-detail").closest("body").css({"boxSizing": "border-box"});
});

//选择关联变更
$("#toRelateChange").click(function () {
    var supportId = $("#supportId").val();
    layer.open({
        type: 2,
        title: '关联变更',
        skin: "layui-layer-molv",
        area: ['790px', '570px'],
        shade: 0,
        content: ['/support/relateChange?supportId=' + supportId],
        end: function () {
            $('#relateChangeTable').bootstrapTable('refresh');
        }
    });
});
//选择关联问题
$("#toRelateIssue").click(function () {
    var supportId = $("#supportId").val();
    layer.open({
        type: 2,
        title: '关联问题',
        skin: "layui-layer-molv",
        area: ['790px', '570px'],
        shade: 0,
        content: ['/support/relateIssue?supportId=' + supportId],
        end: function () {
            $('#relateIssueTable').bootstrapTable('refresh');
        }
    });
});

//在关联变更table发起新的变更申请
$("#triggerChange").click(function () {
    layer.open({
        type: 2,
        title: '变更管理>变更申请单 （<span style="color:#ff253d">以下全部必填</span>）',
        area: ['840px', '550px'],
        content: '/change/changerefer?source=suptrigger&triggerId=' + $("#supportId").val() + '&supportId=' + $("#supportId").val(),
        maxmin: true,
        end: function () {
            $('#relateChangeTable').bootstrapTable('refresh');
        }
    });
});

//在关联问题table发起新的问题申请
$("#triggerIssue").click(function () {
    layer.open({
        type: 2,
        title: '问题管理>问题申请单 （<span style="color:#ff253d">以下全部必填</span>）',
        area: ['840px', '550px'],
        content: '/issue/issueapply?triggerId=' + $("#supportId").val() + '&supportId=' + $("#supportId").val(),
        maxmin: true,
        end: function () {
            $('#relateIssueTable').bootstrapTable('refresh');
        }
    });
});
