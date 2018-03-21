<!DOCTYPE html>
<html>

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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .red {
            color: #fd0001
        }
        .hr-line-dashed{
            margin: 6px 0;
        }
        .form-group {
            margin-bottom: 6px;
        }
    </style>
</head>

<body class="gray-bg">
    <div class="animated">
    <div class="row">
        <div class="col-sm-12">
            <div class=" float-e-margins">
                <form  class="form-horizontal" id="myform">
                    <div class="ibox-content">
                                 <input type='hidden' name="sid" value="{{$sid}}"/>
                                 <input type='hidden' name="uids" value=""/>
                                 <div class="form-group" style='height:80px'>
                                     <label class="col-sm-2 inline" style="vertical-align: middle;height:95%;padding:0 5px;"><span class="red">* </span>挂起说明</label>
                                    <div class="col-sm-10" style=" display: inline-block;">
                                        <textarea id="explain" style="height: 74px;width:355px;max-width:355px;max-height:74px;margin:0 9px;" name="explain" maxlength="70"></textarea>
                                    </div>
                                </div>
                                <div class="hr-line-dashed"></div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">需要提醒</label>
                                    <div class="col-sm-10 inline">
                                       <div class="radio">
                                           <label><input type="radio" checked value="1" id="isremind1" name="isremind">是</label>
                                        <label><input type="radio"  value="2" id="isremind2" name="isremind">否</label>
                                        </div>
                                    </div>
                                </div>
                                 <div class="hr-line-dashed"></div>
                                 <!--提醒相关项-->
                                <div id="showremind">

                                            <div class="form-group">
                                            <label class="col-sm-2 inline">提醒方式</label>

                                                <div class="col-sm-10 inline">
                                                    <label class="checkbox-inline i-checks">
                                                        <input type="checkbox" value="1" name="rmode[]">邮件</label>
                                                    <label class="checkbox-inline i-checks">
                                                        <input type="checkbox" value="2" name="rmode[]">微信</label>
                                                    <label class="checkbox-inline i-checks">
                                                        <input type="checkbox" value="3" name="rmode[]">短信</label>
                                                </div>
                                             </div>
                                             <div class="hr-line-dashed"></div>
                                             <div class="form-group">
                                                <label class="col-sm-2 control-label">连续提醒</label>
                                                <div class="col-sm-10 inline">
                                                   <div class="radio">
                                                    <label><input type="radio" checked="checked" value="0"  name="conRemind">不需要</label>
                                                    <label><input type="radio"  value="2"  name="conRemind">2分钟</label>
                                                    <label><input type="radio"  value="5" name="conRemind">5分钟</label>
                                                    <label><input type="radio"  value="10"  name="conRemind">10分钟</label>
                                                    <label><input type="radio"  value="15"  name="conRemind">15分钟 </label>
                                                    </div>
                                                </div>
                                            </div>
                                             <div class="hr-line-dashed"></div>
                                            <div class="form-group has-success">
                                                <label class="col-sm-2 control-label">通知时间</label>
                                                <div class="col-sm-10 inline" style="width:270px">
                                                        <input class="form-control layer-date" placeholder="YYYY-MM-DD hh:mm" onclick="laydate({istime: true, format: 'YYYY-MM-DD hh:mm'})" name="remindTime">
                                                </div>
                                            </div>
                                            <div class="hr-line-dashed"></div>
                                            <div class="form-group">
                                            <label class="col-sm-2 control-label">通知人员列表</label>

                                            <div class="col-sm-10">
                                                <div>
                                                    <span class="control-label" style="display: inline-block;">选择工作组：</span>
                                                    <select class="form-control m-b " name="group" style="width:54%;display: inline-block;margin:5px">
                                                        <option value="">请选择</option>
                                                       @foreach(ThirdCallHelper::getWorkGroups() as $list)
                                                            <option value="{{$list->Id}}" >{{$list->UsersName}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <span class="control-label" style="display: inline-block;">选择组成员：</span>
                                                    <select class="form-control m-b" name="optuser" style="width:54%;display: inline-block;margin:5px">
                                                        <option value="">请选择</option>
                                                    </select>
                                                    <button class="btn btn-primary"  onclick="return false;" id="addusers">添加</button>
                                                </div>
                                                <div >
                                                        <span class="control-label" style="display: inline-block;">已选择成员：</span>
                                                        <select class="form-control" multiple="" style="width:54%;display: inline-block;margin:5px" name="cuids">

                                                        </select>
                                                        <button class="btn btn-primary"  onclick="return false;" style="display: inline-block;" id="removeusers">移除</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="hr-line-dashed"></div>

                                </div>

                                <div class="form-group">
                                    <div class="col-sm-4 col-sm-offset-2">
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
                                        <button class="btn btn-primary"  onclick="return false;" id="saveform">保存内容</button>
                                        <button class="btn btn-white"  onclick="return false;" id="cancel">取消</button>
                                    </div>
                                </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
</body>
    <!-- 全局js -->
    <script src="/js/jquery.min.js?v=2.1.4"></script>
    <script src="/js/bootstrap.min.js?v=3.3.6"></script>
    <script src="/js/plugins/layer/layer.min.js"></script>
    <!-- iCheck -->
    <script src="/js/plugins/iCheck/icheck.min.js"></script>
     <!-- layerDate plugin javascript -->
    <script src="/js/plugins/layer/laydate/laydate.js"></script>
    <script>
        $(document).ready(function () {
            $('.i-checks').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green',
            });
        });
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
            <!--取消-->
            $("#cancel").click(function(){
                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                parent.layer.close(index); //再执行关闭
            });
            <!--动态查询指派人-->
            $("select[name='group']").on("change",function(event,uid){
                var name = $(this).attr("name");
                var id = $(this).find("option:selected").val();
                var obj ="optuser";
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
            <!--是否需要提醒-->
            $("input[name='isremind']").click(function(){
                $(this).val()=="1"?$("#showremind").show():$("#showremind").hide();
            });
           <!--选择添加成员-->
           $("#addusers").click(function(){
               var uid = $("[name='optuser']").val();
               var uname = $("[name='optuser'] option:selected").text();
               var uids = "";
               if(!uid){
                   lalert("请选择组成员");
                   return false;
               }
               if($("[name='cuids'] option[value='"+uid+"']").length)return false;
               $("[name='cuids']").append('<option value="'+uid+'">'+uname+'</option>');
               $("[name='cuids'] option").each(function(){
                   uids += (uids!=""?",":"")+$(this).val();
               });
               $("input[name='uids']").val(uids);
           });
           <!--移除成员-->
           $("#removeusers").click(function(){
               var roption = $("[name='cuids'] option:selected"), uids = "";
               if(roption.length>0){
                   $("[name='cuids'] option[value='"+roption.val()+"']").remove();
                   $("[name='cuids'] option").each(function(){
                       uids += (uids!=""?",":"")+$(this).val();
                   });
                   $("input[name='uids']").val(uids);
               }
           });
           <!--保存-->
           $("#saveform").click(function(){
               var explain = $("#explain").val();
               explain = $.trim(explain);
               var isremind = $("input[name='isremind']:checked").val();
               if(explain==""){
                   lalert("请输入挂起说明");
                   return false;
               }
               if(isremind=="1"){//需要提醒
                   var rmode = "";
                   var remindTime = $("input[name='remindTime']").val();
                   var cuids = $("[name='cuids']").find("option").length;
                   var now = Date.parse(new Date());
                   $(".checked").each(function(){
                       rmode += (rmode!=""?",":"")+$(this).find("input[name='rmode[]']").val();
                   });
                  if(rmode==""){
                       lalert("请选择至少一种提醒方式");
                       return false;
                  }
                  if(remindTime==""){
                      lalert("请选择通知时间");
                      return false;
                  }else{
                      remindTime = Date.parse(remindTime);
                      if(remindTime<now){
                          lalert("请选择正确的通知时间");
                          return false;
                      }
                  }
                  if(!cuids){
                      lalert("请添加通知人员");
                      return false;
                  }
               }
                if($(this).hasClass("btn-default")){
                       return false;//防止重复提交
                }
               $("#processing").addClass("inline").removeClass("hide");
               $(this).removeClass("btn-primary").addClass("btn-default");

               $.ajax({
                    type:"post",
                    dataType:'json',
                    url:"/wo/posthangup",
                    data:$("#myform").serializeArray(),
                    success:function(data){
                        if(data.status==true){
                            layer.msg('挂起成功！', {
                                icon: 1,
                                time: 1000 //1秒关闭
                            },function(){
                                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                                parent.location.reload();
                                parent.layer.close(index); //再执行关闭
                            });
                        }
                        else if(data.status=='closed'){
                            layer.confirm(data.msg, {btn:['确定']
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
</html>