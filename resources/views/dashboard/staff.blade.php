<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="_token" content="{{ csrf_token() }}"/>
    <title>安畅网络 资源系统——DashBoard</title>

    <!--[if lt IE 9]>
    <meta http-equiv="refresh" content="0;ie.html"/>
    <![endif]-->

    <link rel="shortcut icon" href="favicon.ico">
    <link href="/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/css/font-awesome.min.css?v=4.4.0" rel="stylesheet">
    <link href="/css/animate.css" rel="stylesheet">
    <link href="/css/event_charge.css" rel="stylesheet">
    <link href="/css/style.css?v=4.1.0" rel="stylesheet">
    <link rel="stylesheet" href="/css/plugins/bootstrap-table/bootstrap-table.css">
    <meta charset="utf-8">
    <!-- 引入 ECharts 文件 -->
    <script src="/js/echarts.min.js"></script>
    <script src="/js/jquery.min.js"></script>
    <script src="/js/common.js"></script>
    <script src="/render/hplus/js/content.js"></script>
    <script src="/js/plugins/layer/layer.min.js"></script>
    <style>
        html,body { height:100%; width:100%; margin:0; padding:0;}
        div { height:100%; width:100%;}
    </style>
</head>
<body>
<style type="text/css">
    .notDone {
        width: 60%;
        height: 400px;
        margin: 10px;
        padding: 10px;
        border: 2px solid #e4e4e4;
        float: left;
    }

    .todoDone {
        width: 37%;
        margin: 10px 0px 10px 10px;
        padding: 10px;
        border: 2px solid #e4e4e4;
        height: 400px;
        float: left;
    }

    .years {
        width: 98%;
        height: 400px;
        margin: 10px;
        padding: 10px;
        border: 2px solid #e4e4e4;
        float: left;
    }

    .month {
        width: 99%;
        max-height: 100%;
        height:auto !important;
        margin-top: 10px;
        border: 1px;
        float: left;
    }

</style>
    <div style="background:#55A3CA">
        <img src="/img/waitingPage.jpg" style="left:15%;top:10%;width: 75%;height: 90%;margin: 0 auto;display: block">
    </div>
</body>
<script type="text/javascript">
    function refreshCache(tdom,data){
        var txt = $(tdom).attr("title");
        $.ajax({
            type: "GET",
            url: "/dashboard/refresh/"+data,
            success: function (data) {
                if(data.status){
                    layer.alert(txt+"成功!", {icon: 1, closeBtn: false, area: '100px'},function(){
                        location.reload();
                    });
                }
            }
        });
    }

    // 当前工单类型统计
    $("#statisticCurrent").click(function () {
        $("#typeCurrent").removeClass("hide");
        $("#typeMonth").addClass("hide");
        switchTitleLabel($(this), $(".job-statistic .label-title .title_active"));
    });
    // 本月工单类型统计分析
    $("#statisticMonth").click(function () {
        $("#typeMonth").removeClass("hide");
        $("#typeCurrent").addClass("hide");
        switchTitleLabel($(this), $(".job-statistic .label-title .title_active"));
    });
    // 切换标题标签
    function switchTitleLabel(currentEle, prevEle, callback) {
        prevEle.children(".label_line").remove();
        prevEle.removeClass("title_active");
        currentEle.addClass("title_active").append('<span class="label_line"></span>');
        if (callback && {}.toString.call(callback) === "[object Function]") {
            callback();
        }
    }
</script>
</html>