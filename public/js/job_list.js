// 切换标题标签
function switchTitleLabel(currentElement, callback) {
    $(".job-list-content .label-title .title_active").children(".label_line").remove();
    $(".job-list-content .label-title .title_active").removeClass("title_active");
    currentElement.addClass("title_active").append('<span class="label_line"></span>');
    if (callback && {}.toString.call(callback) === "[object Function]") {
        callback();
    }
};

// 工单列表 标题切换
$("#commonJob").click(function () {
    switchTitleLabel($(this));
    $(".table-wrap .job-list-table").addClass("hide");
    $("#bottomOption").addClass("hide");
    $("#commonJobTable").removeClass("hide");
});

$("#requireEmail").click(function () {
    switchTitleLabel($(this));
    $(".table-wrap .job-list-table").addClass("hide");
    $("#bottomOption").removeClass("hide");
    $("#requireEmailTable").removeClass("hide");
});

//复选框（全选）
$("#selectedAll").click(function () {
    var isChecked = $(this).prop("checked");
    if (isChecked) {
        $(".checkbox").children("input").prop({"checked": true});
    } else {
        $(".checkbox").children("input").prop({"checked": false});
    }
})

// hover显示提示文字
function showToolTip(element) {
    element.hover(function () {
        $(this).find(".tool-tips").css({"display": "inline-block"});
        fixToolTipPos.call(this, element);
    }, function () {
        $(this).find(".tool-tips").css({"display": "none"});
    });
}
// 修正tooltips位置
function fixToolTipPos(element) {
    if ($(this).css("display") != "none") {
        var originPos = $(window).width() - ($(this).find(".tool-tips").offset().left + $(this).find(".tool-tips").outerWidth());
        if (Number(originPos) <= 0) {
            $(this).find(".tool-title").css({"right": "15%"});
        }
    }
}
// 阻止冒泡
function preventBubble(e) {
    if (e && e.stopPropagation) {
        e.stopPropagation();
    } else {
        window.event.cancelBubble = true;
    }
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


