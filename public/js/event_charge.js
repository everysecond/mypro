// 切换标题标签
function switchTitleLabel(currentEle, prevEle, callback) {
    prevEle.children(".label_line").remove();
    prevEle.removeClass("title_active");
    currentEle.addClass("title_active").append('<span class="label_line"></span>');
    if (callback && {}.toString.call(callback) === "[object Function]") {
        callback();
    }
};

// 切换表格标签
function switchTableLabel(currentEle, prevEle, callback) {
    prevEle.removeClass("label_active");
    currentEle.addClass("label_active");
    if (callback && {}.toString.call(callback) === "[object Function]") {
        callback();
    }
};

// 工单处理 标题切换
// 当前工单处理情况
$("#handleCurrent").click(function () {
    $("#jobCurrent").removeClass("hide");
    $("#jobMonth").addClass("hide");
    $("#jobPreMonth").addClass("hide");
    switchTitleLabel($(this), $(".job-handle .label-title .title_active"));
});
// 本月工单处理分析
$("#handleMonth").click(function () {
    $("#jobMonth").removeClass("hide");
    $("#jobCurrent").addClass("hide");
    $("#jobPreMonth").addClass("hide");
    switchTitleLabel($(this), $(".job-handle .label-title .title_active"));
});
// 本月工单处理分析
$("#handlePreMonth").click(function () {
    $("#jobPreMonth").removeClass("hide");
    $("#jobCurrent").addClass("hide");
    $("#jobMonth").addClass("hide");
    switchTitleLabel($(this), $(".job-handle .label-title .title_active"));
});

// 工单处理 label标签切换（部门）
$("#departmentL0").click(function () {
    switchTableLabel($(this), $(".job-handle ._department .label_active"));
});
$("#departmentL1").click(function () {
    switchTableLabel($(this), $(".job-handle ._department .label_active"));
});
$("#departmentL1scene").click(function () {
    switchTableLabel($(this), $(".job-handle ._department .label_active"));
});
$("#departmentL2").click(function () {
    switchTableLabel($(this), $(".job-handle ._department .label_active"));
});
$("#departmentL3").click(function () {
    switchTableLabel($(this), $(".job-handle ._department .label_active"));
});
// 工单处理 label标签切换（机房）
$("#roomNan").click(function () {
    switchTableLabel($(this), $(".job-handle ._room .label_active"));
});
$("#roomWu").click(function () {
    switchTableLabel($(this), $(".job-handle ._room .label_active"));
});
$("#roomChang").click(function () {
    switchTableLabel($(this), $(".job-handle ._room .label_active"));
});
$("#roomBei").click(function () {
    switchTableLabel($(this), $(".job-handle ._room .label_active"));
});
$("#roomHu").click(function () {
    switchTableLabel($(this), $(".job-handle ._room .label_active"));
});

// 工单类型统计 标题切换
// 当前工单类型统计
$("#statisticCurrent").click(function () {
    $("#typeCurrent").removeClass("hide");
    $("#typeMonth").addClass("hide");
    $("#typePreMonth").addClass("hide");
    switchTitleLabel($(this), $(".job-statistic .label-title .title_active"));
});
// 本月工单类型统计分析
$("#statisticMonth").click(function () {
    $("#typeMonth").removeClass("hide");
    $("#typePreMonth").addClass("hide");
    $("#typeCurrent").addClass("hide");
    switchTitleLabel($(this), $(".job-statistic .label-title .title_active"));
});
// 本月工单类型统计分析
$("#statisticPreMonth").click(function () {
    $("#typePreMonth").removeClass("hide");
    $("#typeMonth").addClass("hide");
    $("#typeCurrent").addClass("hide");
    switchTitleLabel($(this), $(".job-statistic .label-title .title_active"));
});

