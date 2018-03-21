<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link rel="shortcut icon" href="favicon.ico">
    <link href="/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    {{--<link href="/css/font-awesome.css?v=4.4.0" rel="stylesheet">--}}
    {{--<link href="/css/plugins/iCheck/custom.css" rel="stylesheet">--}}
    {{--<link href="/css/animate.css" rel="stylesheet">--}}
    <link href="/css/style.css?v=4.1.0" rel="stylesheet">
    {{--<link rel="stylesheet" href="/js/plugins/kindeditor/themes/default/default.css"/>--}}
    {{--<link rel="stylesheet" href="/js/plugins/kindeditor/plugins/code/prettify.css"/>--}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .red {
            color: #fd0001
        }
    </style>
</head>

<body class="gray-bg">
<div class="animated">
    <div class="row">
        <div class="col-sm-12">
            <div class="float-e-margins">
                <input type='hidden' id="sid" value="{{$sid}}"/>
                <div class="ibox-content">
                    <div style='padding: 5px'>
                        <select id="collectReason" class="form-control" style="width: 50%; padding:5px;">
                            <option value="-">原因分类</option>
                            @foreach($arr as $v)
                                <option value="{{$v}}">{{$v}}</option>
                            @endforeach
                        </select>
                        <textarea id="msg" style="height: 100px;width: 100%; padding:20px;resize: none;margin-top: 10px"
                                  class="form-control" placeholder="请输入收藏原因"></textarea>
                    </div>
                    <div class="text-right">
                        <div class="sk-spinner sk-spinner-fading-circle hide" id='processing'>
                            <div class="sk-circle1 sk-circle"></div>
                            <div class="sk-circle2 sk-circle"></div>
                            <div class="sk-circle3 sk-circle"></div>
                            <div class="sk-circle4 sk-circle"></div>
                            <div class="sk-circle5 sk-circle"></div>
                            <div class="sk-circle6 sk-circle"></div>
                            <div class="sk-circle7 sk-circle"></div>
                            <div class="sk-circle8 sk-circle"></div>
                            <div class="sk-circle9 sk-circle"></div>
                            <div class="sk-circle10 sk-circle"></div>
                            <div class="sk-circle11 sk-circle"></div>
                            <div class="sk-circle12 sk-circle"></div>
                        </div>
                        <button class="btn btn-sm btn-primary" id="sure_reply">确认收藏</button>
                        <button class="btn btn-sm btn-primary" id="cancel">取消</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- 全局js -->
<script src="/js/jquery.min.js?v=2.1.4"></script>
<script src="/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/js/plugins/layer/layer.min.js"></script>
<script charset="utf-8" src="/js/plugins/kindeditor/kindeditor-min.js"></script>
<script charset="utf-8" src="/js/plugins/kindeditor/lang/zh_CN.js"></script>
<!-- iCheck -->
<script src="/js/plugins/iCheck/icheck.min.js"></script>
<script>
//    KindEditor.options.filterMode = false;
//    KindEditor.ready(function (K) {
//        window.editor1 = K.create('#msg', {
//            resizeType: 0,
//            uploadJson: "/kindeditor/uploadify",
//            width: "100%",
//            items: [
//                'justifyleft', 'justifycenter', 'justifyright', 'forecolor', 'hilitecolor', 'bold',
//                'italic', 'underline', 'image', 'link', 'fullscreen'
//            ], afterBlur: function () {
//                this.sync();
//            }
//        });
//    })
    $(function () {
        <!--TOKEN验证-->
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        <!--消息提示-->
        function lalert(txt) {
            if (txt != '')
                layer.alert(txt, {icon: 2, closeBtn: false, area: '100px'});
        }

        var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引

        <!--取消-->
        $("#cancel").click(function () {
            parent.layer.close(index); //再执行关闭
        });
        $("#sure_reply").click(function () {
            var sid = $("#sid").val();
            var msg = $("#msg").val(),collectReason = $("#collectReason").val();
            if (collectReason == "" ||collectReason=="-") {
                lalert("请选择原因分类！");
                return false;
            }
            if (msg == "") {
                lalert("收藏原因不能为空！");
                return false;
            }
            if ($(this).hasClass("btn-default")) {
                return false;//防止重复提交
            }
            $("#processing").addClass("inline").removeClass("hide");
            $(this).removeClass("btn-primary").addClass("btn-default");
            $.ajax({
                type: "POST",
                async: false,
                data: {'supportId': sid, 'notes': msg,'collectReason':collectReason},
                url: "/support/addCollection",
                headers: {'X-CSRF-Token': $('meta[name=_token]').attr('content')},
                dataType: 'json',
                success: function (res) {
                    if (res.status == 'successful') {
                        layer.msg(res.msg, {icon: 6},function(){
                            parent.$('#supportTable').bootstrapTable('refresh');
                            parent.layer.closeAll();
                        });
                    } else {
                        layer.msg(res.msg, {icon: 2},function(){
                            parent.layer.closeAll();
                        });
                    }
                }
            });
        });
    })
</script>
</body>
</html>