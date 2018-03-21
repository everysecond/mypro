<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>安畅网络 ITSM系统V1.0</title>
    <link rel="shortcut icon" href="favicon.ico">
    <link href="/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/css/font-awesome.css?v=4.4.0" rel="stylesheet">
    <link href="/css/plugins/iCheck/custom.css" rel="stylesheet">
    <link href="/css/animate.css" rel="stylesheet">
    <link href="/css/style.css?v=4.1.0" rel="stylesheet">
    <link rel="stylesheet" href="/js/plugins/kindeditor/themes/default/default.css"/>
    <link rel="stylesheet" href="/js/plugins/kindeditor/plugins/code/prettify.css"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .red {
            color: #fd0001
        }
    </style>
</head>

<body class="gray-bg">
<div class=" animated">
    <div class="row">
        <div class="col-sm-12">
            <div class="float-e-margins">
                  <form class="form-horizontal" id="myform">
                      <input type='hidden' id="sid" value="{{$sid}}" name="sid"/>
                        <div class="ibox-content">
                            <div><h3>编辑发送内容：</h3></div>
                            <div style='padding: 5px'>
                                <textarea id="msg" style="height: 200px;width:100%" name="msg">@if(!empty($data)) {{$data->reply}} @endif</textarea>
                             </div>
                            <div class="text-right" style="margin:4px 0;">
                                @if((!empty($contact))&&(!empty($contact->Mobile)))
                                <div style='padding-right:16px;' class="inline">
                                是否发送短信通知客户：
                                    <input type="checkbox" id="sureSendMsg" checked="checked" name="sureSendMsg"
                                           style="zoom:130%">
                                </div>
                                @endif
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
                                <button class="btn btn-primary inline" id="sure_reply" onclick="return false">确认提交</button>
                                <button class="btn btn-primary inline" id="cancel" onclick="return false">取消</button>
                             </div>
                        </div>
                    
                  </form>
            </div>
        </div>
    </div>
</div>
    <!-- 全局js -->
    <script src="/js/jquery.min.js?v=2.1.4"></script>
    <script src="/js/bootstrap.min.js?v=3.3.6"></script>
    <script src="/js/plugins/layer/layer.min.js"></script>
    <script>
        
        $(function(){
           <!--TOKEN验证-->
            $.ajaxSetup({
                     headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
             });
             <!--消息提示-->
            function lalert(txt){
                if(txt!='')
                layer.alert(txt, {icon: 2,closeBtn:false,area: '100px'});
            }
            $("#sureSendMsg").click(function(){
                if(!$(this).is(':checked')){
                    lalert("您选择不发送短信通知客户。");
                }
            });
            
              <!--取消-->
            $("#cancel").click(function(){
                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                parent.layer.close(index); //再执行关闭   
            });
            $("#sure_reply").click(function(){
                var oid = $("#oid").val();
                var msg = $("#msg").val();
                      msg = $.trim(msg);
                if($("#sureSendMsg").is(':checked')&&msg==""){
                    lalert("短信消息不能为空！")
                    return false;
                }
                 if($(this).hasClass("btn-default")){
                    return false;//防止重复提交
                }
                $("#processing").addClass("inline").removeClass("hide");
                $(this).removeClass("btn-primary").addClass("btn-default");
               $.ajax({
                    type:"post",
                    dataType:'json',
                    url:"/wo/sureproc",
                    data:$("#myform").serializeArray(),
                    success:function(data){
                        if(data){
                            layer.msg(data.status?'操作成功！':data.msg, {
                                icon: data.status?1:2,
                                time: 2000 //1秒关闭
                            },function(){
                                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                                parent.location.reload();
                                parent.layer.close(index); //再执行关闭 
                            });
                        }
                    }
                });
            });
         })
    </script>
</body>
</html>
