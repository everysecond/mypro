$("#newEquiry").click(function(){
    layer.open({
        type: 2,
        title: '询价管理>新增询价申请（<span style="color:#ff253d">*表示必填项</span>）',
        maxmin: true,
        shade: [0.1, '#393D49'],
        area: ['800px', '570px'],
        content: '/enquiry/newEnquiry',
        end: function () {
            $('#salesTable').bootstrapTable('refresh');
        }
    });
});

$(".newOffer").click(function(){
    layer.open({
        type: 2,
        title: '产品报价>新增编辑',
        maxmin: true,
        shade: [0.1, '#393D49'],
        area: ['600px', '470px'],
        content: '/enquiry/newOffer?enquiryId=' + $("#enquiryId").val()
    });
});

function editOffer(offerId){
    layer.open({
        type: 2,
        title: '产品报价>编辑修改',
        maxmin: true,
        shade: [0.1, '#393D49'],
        area: ['600px', '470px'],
        content: '/enquiry/newOffer?offerId=' + offerId + '&enquiryId=' + $("#enquiryId").val()
    });
}

function offerDetail(offerId){
    layer.open({
        type: 2,
        title: '产品报价>报价详情',
        maxmin: true,
        shade: [0.1, '#393D49'],
        area: ['600px', '350px'],
        content: '/enquiry/offerDetail?offerId=' + offerId
    });
}

function del(offerId) {//删除产品报价
    console.log($('meta[name="csrf-token"]').attr('content'));
    console.log($('meta[name="_token"]'));
    $.ajax({
        type: "POST",
        url: "/enquiry/delOffer?offerId=" + offerId,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (arr) {
            if (arr.status == true) {
                layer.msg(arr.msg, {icon: 1, time: 2000}, function () {
                    $('#offersTable').bootstrapTable('refresh');
                });
            } else {
                layer.msg(arr.msg, {icon: 2, time: 2000});
            }
        }
    });
}

function editEnquiry(enquiryId){
    layer.open({
        type: 2,
        title: '询价管理>询价申请编辑（<span style="color:#ff253d">*表示必填项</span>）',
        maxmin: true,
        shade: [0.1, '#393D49'],
        area: ['800px', '570px'],
        content: '/enquiry/newEnquiry?enquiryId='+enquiryId,
        end: function () {
            $('#salesTable').bootstrapTable('refresh');
        }
    });
}

$(".divBack img").each(function () {
    var src = $(this).attr("src");
    if ((src.substr(0, 7).toLowerCase() != "http://") &&
        (src.substr(0, 8).toLowerCase() != "https://") &&
        (src.substr(0, 21).toLowerCase() != "data:image/png;base64")) {
        $(this).attr("src", url + src);
    }
    $(this).addClass("litle-img");
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
});

var clickable = true
$('.btnSub').click(function () {
    if (!clickable) return;
    clickable = false;
    $(this).attr('disabled', 'disabled');
    var indexValidate = layer.load(0, {shade: false});
    var route = $("#route").val(),processVar = $("#processVar").val(),conf="",operate = '';
    if("quoteFlow" == processVar){//complexFlow
        operate = $("select[name='"+processVar+"']").val();
        conf = operate == 1?"转资源询价":(operate==2?"转采购询价":(operate==3?"询价完成":"退回销售"));
    }else if("complexFlow" == processVar){
        operate = $("select[name='"+processVar+"']").val();
        conf = operate == 1?"转资源询价":(operate==2?"转采购询价":(operate==3?"询价完成":""));
    }else{
        conf = "提交";
    }

    layer.confirm('是否确认'+conf, {icon: 3, title: '确认'}, function (index) {
        $.ajax({
            type: "POST",
            data:$("#productOffer").serialize(),
            url: "/enquiry/productOfferSub?conf="+conf+"&processVal="+operate+"&enquiryId="+$("#enquiryId").val(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (arr) {
                if (arr.status == true) {
                    layer.msg(arr.msg, {icon: 1, time: 3000}, function () {
                        location.reload();
                    });
                } else {
                    layer.msg(arr.msg, {icon: 2, time: 2000}, function () {
                        location.reload();
                    });
                }
            },
            complete: function() {
                setTimeout(function(){
                    console.log('')
                    clickable = true;
                }, 300)

            }
        });
        layer.close(index);
    },function(){
        $(".btnSub").removeAttr('disabled');
        layer.close(indexValidate);
        clickable = true;
    });
});

function closeJmenu(){
    parent.$('.J_menuItem[menuname="全部询价列表"]').click();
    parent.$('.J_menuTab[data-id="/enquiry/productOffer/'+ $.trim($('#enquiryId').val())+'"]').remove();
}

// 切换标题标签
function switchTitleLabel(currentElement, callback) {
    $(".job-list-content .label-title .title_active").children(".label_line").remove();
    $(".job-list-content .label-title .title_active").removeClass("title_active");
    currentElement.addClass("title_active").append('<span class="label_line"></span>');
    if (callback && {}.toString.call(callback) === "[object Function]") {
        callback();
    }
};

// hover显示提示文字
function showToolTip(element) {
    element.hover(function () {
        $(this).find(".tool-tips").css({"display": "inline-block"});
        fixToolTipPos.call(this, element);
    }, function () {
        $(this).find(".tool-tips").css({"display": "none"});
    });
}

// hover显示提示文字（table表格内）
function showTableToolTip(element) {
    $(document).on("mouseenter", element, function (e) {
        preventBubble(e);
        $(this).find(".tool-tips").css({"display": "inline-block"});
    }).on("mouseleave", element, function (e) {
        preventBubble(e);
        $(this).children(".tool-tips").css({"display": "none"});
    })
}

// 下拉菜单 20160830
function pullDownChoice(element, callback, resetId) {
    var element = element;
    var currentEle;
    if (String(element).indexOf(0) != "$") {
        currentEle = "#" + element + " .current-title";
        element = $("#" + element);
    }

    $(document).on("click", currentEle, function (e) {
        if (e && e.stopPropagation) {
            e.stopPropagation();
        } else {
            window.event.cancelBubble = true;
        }

        /* 优化0830 start*/
        if (element.closest(".th-inner")) element.closest(".th-inner").css({"overflow": "inherit"});
        if (element.closest(".fixed-table-body")) element.closest(".fixed-table-body").css({"overflow": "inherit"});
        $(".select-list").css({"display": "none"});
        /* 优化0830 end*/

        if (element.find(".select-list").css("display") == "block") {
            element.find(".select-list").css({"display": "none"});
        } else {
            element.find(".select-list").css({"display": "block"});
        }
    });

    $(document).on("click", function () {
        element.find(".select-list").css({"display": "none"});
    });

    function itemClickFunc(element) {
        var currentValue = null;
        var currentText = $(this).text();
        element.find(".select-list-item").removeClass("selected-color");
        $(this).addClass("selected-color");

        if ($(this).attr("value")) currentValue = $(this).attr("value");

        element.find(".select-list").css({"display": "none"});
        element.find(".current-select").html(currentText);

        /* 优化0830 start*/
        if (element.closest(".th-inner")) element.closest(".th-inner").removeAttr("style");
        if (element.closest(".fixed-table-body")) element.closest(".fixed-table-body").removeAttr("style");
        /* 优化0830 end*/

        if (callback && {}.toString.call(callback) === "[object Function]") {
            callback(currentValue);
        }
    }

    // reset下拉菜单
    var currentItemArray = [];
    element.find(".select-list-item").each(function (key) {
        if ($(this).attr("value") || $(this).attr("value") == "") {
            var value = $(this).attr("value");
            if ($(this).attr("value") == "") value = "allList";
            currentItemArray.push({
                "value": value,
                "text": $(this).text()
            });
        }
    });
    if (resetId || resetId == "") {
        var currentValueReset = null;
        var currentTextReset = null;
        currentItemArray.forEach(function (item, key) {
            if (resetId == "" && item.value == "allList") {
                currentValueReset = item.value;
                currentTextReset = item.text;
            } else if (resetId == item.value) {
                currentValueReset = item.value;
                currentTextReset = item.text;
            }
        });
        element.find(".select-list").css({"display": "none"});
        element.find(".current-select").html(currentTextReset);
        if (callback && {}.toString.call(callback) === "[object Function]") {
            callback(currentValueReset);
        }
    } else {
        element.find(".select-list-item").click(function () {
            itemClickFunc.call(this, element);
        });
    }
}



