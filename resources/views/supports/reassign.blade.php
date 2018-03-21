<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>安畅网络 ITSM系统V1.0</title>
    <link rel="shortcut icon" href="favicon.ico"> <link href="/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/css/font-awesome.css?v=4.4.0" rel="stylesheet">
    <link href="/css/animate.css" rel="stylesheet">
    <link href="/css/style.css?v=4.1.0" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .red{color: #fd0001}
    </style>
</head>

<body class="gray-bg">
    <div class=" animated">
        <div class="row">
            <div class="col-sm-12">
                <div class="float-e-margins">
                    <div class="ibox-content">
                         <form  class="form-horizontal" id="myform">
                            <input type="hidden" name="sid" value="{{$data->id}}"/>
                            <div class="row">
                                    <div class="col-sm-7">
                                        <div class="col-sm-10">
                                            <div style="margin:0 -10px;">
                                                <label class="control-label form-inline"><span class="red">* </span>第一负责人工作组：</label>
                                                <span class="m-b-none form-inline">
                                                    <select class="form-control" name="group1" style='margin: 0 10px;'>
                                                        <option value="">请选择</option>
                                                         @foreach(ThirdCallHelper::getWorkGroups() as $list)
                                                            <option value="{{$list->Id}}" @if($data->DatacenterId==$list->Id)selected @endif>{{$list->UsersName}}</option>
                                                         @endforeach
                                                    </select>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-5">
                                            <div style="margin:0 -10px;">
                                                <label class="control-label form-inline"><span class="red">* </span>第一负责人：</label>
                                                <span class="m-b-none form-inline">
                                                    <select class="form-control" name="optuser1" style="width:134px;margin: 0 10px;">
                                                            <option value="">请选择</option>
                                                    </select>
                                                </span>
                                            </div>
                                    </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                             <div class="row">
                                    <div class="col-sm-7">
                                        <div class="col-sm-10">
                                            <div >
                                                <label class="control-label form-inline">第二负责人工作组：</label>
                                                <span class="m-b-none form-inline">
                                                    <select class="form-control" name="group2" style="margin-left: 9px">
                                                        <option value="">请选择</option>
                                                         @foreach(ThirdCallHelper::getWorkGroups() as $list)
                                                            <option value="{{$list->Id}}" @if($data->DatacenterTwoId==$list->Id)selected @endif>{{$list->UsersName}}</option>
                                                         @endforeach
                                                    </select>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-5">
                                            <div >
                                                <label class="control-label form-inline">第二负责人：</label>
                                                <span class="m-b-none form-inline">
                                                    <select class="form-control" name="optuser2" style="margin-left: 9px;width:135px">
                                                            <option value="">请选择</option>
                                                    </select>
                                                </span>
                                            </div>
                                    </div>
                                </div>
                         </form>
                        <div class="hr-line-dashed"></div>

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
                                           <button class="btn btn-primary" id="distribute">重新指派</button>
                                           <button class="btn btn-primary" id="cancel">取消</button>
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
            <!--动态查询指派人-->
            $("select[name='group1'],select[name='group2']").on("change",function(event,uid){
                var name = $(this).attr("name");
                var id = $(this).find("option:selected").val();
                var obj = (name=="group1")?"optuser1":"optuser2";
                obj = $("select[name='"+obj+"']");
                obj.empty().append('<option value="">请选择</option>');
                if(id=="")return;
                $.ajax({
                    type:"GET",
                    url:"/wo/optusers/"+id,
                    success:function(data){
                        if(data){
                            for(var i=0;i<data.length;i++){
                                obj.append('<option value="'+data[i].UserId+'"  '+(uid==data[i].UserId?"selected":"")+'>'+data[i].Name+'</option>');
                            }
                        }
                    }
                });
            });
            var gid = $("select[name='group1']").find("option:selected").val();
            var gid2 = $("select[name='group2']").find("option:selected").val();;
            if(gid!=''){
                var uid = '{{$data->ChargeUserId}}';
                $("select[name='group1']").trigger("change",[uid]);
            }
            if(gid2!=''){
                var uid = '{{$data->ChargeUserTwoId}}';
                $("select[name='group2']").trigger("change",[uid]);
            }
            <!--取消-->
            $("#cancel").click(function(){
                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                parent.layer.close(index); //再执行关闭
            });
            <!--确认重新指派-->
            $("#distribute").click(function(){
                var grp1="{{$data->DatacenterId}}";
                      grp1= parseInt(grp1,10);
                var opt1="{{$data->ChargeUserId}}";
                     opt1=parseInt(opt1,10);
                var grp2="{{$data->DatacenterTwoId}}";
                     grp2 = parseInt(grp2,10);
                var opt2="{{$data->ChargeUserTwoId}}";
                     opt2= parseInt(opt2,10);

                if($("select[name='group1']").val()==""){
                    lalert("请选择第一负责人工作组");
                    return false;
                }
                if($("select[name='optuser1']").val()==""){
                    lalert("请选择第一负责人");
                    return false;
                }
                 if($("select[name='group2']").val()!=""){
                        if($("select[name='optuser2']").val()==""){
                            lalert("请选择第二负责人");
                            return false;
                        }
                        if(($("select[name='group1']").val()==$("select[name='group2']").val()) &&($("select[name='optuser1']").val()==$("select[name='optuser2']").val())){
                            lalert("请不要重复选择");
                            return false;
                        }
                 }
                if((grp1==$("select[name='group1']").val())
                        &&(opt1==$("select[name='optuser1']").val())
                        &&(grp2==$("select[name='group2']").val())
                        &&(opt2==$("select[name='optuser2']").val())){//没有任何改变
                            layer.msg('工单指派成功！', {
                                icon: 1,
                                time: 1000 //1秒关闭
                            },function(){
                                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                                parent.location.reload();
                                parent.layer.close(index); //再执行关闭
                            });
                }else{
                        if($(this).hasClass("btn-default")){
                            return false;//防止重复提交
                        }
                        $("#processing").addClass("inline").removeClass("hide");
                        $(this).removeClass("btn-primary").addClass("btn-default");

                        $.ajax({
                            type:"post",
                            dataType:'json',
                            url:"/wo/preassign",
                            data:$("#myform").serializeArray(),
                            success:function(data){
                                console.log(data)
                                if(data.status==true){
                                    layer.msg('重新指派成功！', {
                                        icon: 1,
                                        time: 1000 //1秒关闭
                                    },function(){
                                        var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                                        parent.location.reload();
                                        parent.layer.close(index); //再执行关闭
                                    });
                                }
                                else if(data.status==false){
                                    layer.msg('重新指派失败！', {
                                        icon: 2,
                                        time: 1000 //1秒关闭
                                    },function(){
                                        var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                                        parent.location.reload();
                                        parent.layer.close(index); //再执行关闭
                                    });
                                }
                            }
                        });
                }


            });
        })

    </script>
</body>