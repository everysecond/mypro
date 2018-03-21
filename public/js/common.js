/**
 * Created by aaron on 8/16/16.
 */


/**
 *  check inArray
 */
function isInArray(arr, str) {
    var arrLen = arr.length;
    while (arrLen--) {
        if (arr[arrLen] == str) {
            return true;
        }
    }
    return false;
}
/**
 *  校验数据中心的延时工单的背景色
 */
function checkTimeOutStyle(value, row, index, field) {
    var allIds = $("#allTimeOutIds").val();
    if (allIds != undefined) {
        arrIds = allIds.split(",");
        if (isInArray(arrIds, row.Id)) {
            return {
                css: {"color": "#FFFFFF", "background-color": "red"}
            }
        }
    }
    return {css: {"": ""}};
}
function checkTimeOutType(row) {

    var lestThanOneVal = $("#lestThanOneVal").val();
    var moreThanOneVal = $("#moreThanOneVal").val();
    var moreThanTwoVal = $("#moreThanTwoVal").val();
    if (lestThanOneVal != "") {
        lestThanOneValArr = lestThanOneVal.split(",");
        if (isInArray(lestThanOneValArr, row.Id)) {
            return "<i class=\"_jobState table-icon icon-alarm-huang\"><div class=\"tool-tips table-tool-tips\" style=\"display: none;\"><p class=\"tool-title\">处理超时且不足1小时</p><i class=\"tool-arrow table-tool-arrow\"></i></div></i>";
        }
    }
    if (moreThanOneVal != "") {
        moreThanOneValArr = moreThanOneVal.split(",");
        if (isInArray(moreThanOneValArr, row.Id)) {
            return "<i class=\"_jobState table-icon icon-alarm-yellow\"><div class=\"tool-tips table-tool-tips\" style=\"display: none;\"><p class=\"tool-title\">处理超时1小时且不足2小时</p><i class=\"tool-arrow table-tool-arrow\"></i></div></i>";
        }
    }
    if (moreThanTwoVal != "") {
        moreThanTwoValArr = moreThanTwoVal.split(",");
        if (isInArray(moreThanTwoValArr, row.Id)) {
            return "<i class=\"_jobState table-icon icon-alarm-red\"><div class=\"tool-tips table-tool-tips\" style=\"display: none;\"><p class=\"tool-title\">处理超时2小时</p><i class=\"tool-arrow table-tool-arrow\"></i></div></i>";
        }
    }
    return "";
}

/**
 * 时间转换
 * @param second_time
 * @returns {string}
 */
function timeStamp(second_time) {
    if (second_time != "" && second_time != null) {
        var time = parseInt(second_time) + "秒";
        if (parseInt(second_time) > 60) {

            var second = parseInt(second_time) % 60;
            var min = parseInt(second_time / 60);
            time = min + "分" + second + "秒";

            if (min > 60) {
                min = parseInt(second_time / 60) % 60;
                var hour = parseInt(parseInt(second_time / 60) / 60);
                time = hour + "小时" + min + "分" + second + "秒";

                if (hour > 24) {
                    hour = parseInt(parseInt(second_time / 60) / 60) % 24;
                    var day = parseInt(parseInt(parseInt(second_time / 60) / 60) / 24);
                    time = day + "天" + hour + "小时" + min + "分" + second + "秒";
                }
            }


        }
        return time;
    }
    return "无";

}
function statusFormatter(row) {
    var v;
    switch (row.Status) {
        case 'Todo':
            v = '<div class="label label-danger">待处理</div>';
            break;
        case 'Doing':
            v = '<div class="label label-success">处理中</div>';
            break;
        case 'Done':
            v = '<div class="label label-primary">已处理</div>';
            break;
        case 'Closed':
            v = '<div class="label label-default">已关闭</div>';
            break;
        case 'Suspend':
            v = '<div class="label label-warning">挂起中</div>';
            break;
        case 'Appointed':
            v = '<div class="label label-info">已指派</div>';
            break;
        case 'ReAppoint':
            v = '<div class="label" style="background:#4D3370;color: #fffeff ">待指派</div>';
            break;
        default:
            v = '';
    }
    return v;
}
/**
 * 客户类型格式化
 * @param row
 * @returns {string}
 */
function formatterIdentity(row) {
    var returns = "";
    var context = "<span class=icon-wrap style='font-size: 0'>";
    identity = row.identity;
    if (identity.isVIP) {
        context += " <i class=\"icon-client-state icon-vip\"></i>";
    }
    if (identity.isAType) {
        context += " <i class=\"icon-client-state icon-A\"></i>";
    }
    if (identity.isMAN) {
        context += " <i class=\"icon-client-state icon-manage\"></i>";
    }
    if (identity.isDSF) {
        context += " <i class=\"icon-client-state icon-three\"></i>";
    }
    if (identity.isNewCus) {
        context += " <img style='margin: -12px 0px 0px 5px;width:14px' title='17年5月后新客户' src='/img/newcus.png'/>";
    }
    returns = context + "</span>";
    return returns;
}
/**
 * 截取文本显示长度
 * @param text
 * @param length
 */
function substringText(text, length) {
    var length = arguments[1] ? arguments[1] : 16;
    suffix = "";
    if (text.length > length) {
        suffix = "...";
    }
    return text.substr(0, length) + suffix;
}

/**
 * 截取文本显示长度
 * @param text
 * @param length
 */
function substringText2(text, length) {
    if(text.indexOf("<img") >= 0 || text.indexOf("\"")>=0|| text.indexOf("'")>=0){
        return text;
    }else{
        var length = arguments[1] ? arguments[1] : 16;
        suffix = "";
        if (text.length > length) {
            suffix = "...";
        }
        return text.substr(0, length) + suffix;
    }

}
/**
 * 截取时间格式
 */
function substrTime(text, length) {
    var length = arguments[1] ? arguments[1] : 16;
    suffix = "";
    return text.substr(0, length);
}
/**
 * 获取url信息
 * @param name
 * @returns {null}
 */
function getQueryString(name) {
    var reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)', 'i');
    var r = window.location.search.substr(1).match(reg);
    if (r != null) {
        return unescape(r[2]);
    }
    return null;
}
/**
 * 截取文本显示长度
 * @param text
 * @param length
 */
function subByText(text, length) {
    var length = arguments[1] ? arguments[1] : 16;
    suffix = "";
    if (text.length > length) {
        suffix = "...";
    }
    return text.substr(0, length) + suffix;
}