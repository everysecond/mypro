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
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-sm-9">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>{{$data->Title}}</h5>
                </div>
                <div class="ibox-content">
                    <form class="form-horizontal" id="myform">
                        <input type="hidden" name="sid" value="{{$data->Id}}"/>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="col-sm-10">
                                    <div>
                                        <label class="control-label">工单编号：</label>
                                        <span class="m-b-none">{{$data->Id}}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="col-sm-10">
                                    <div>
                                        <label class="control-label">工单状态：</label>
                                        <span class="m-b-none">{{$data->Status}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="col-sm-10">
                                    <div>
                                        <label class="control-label">提交人：</label>
                                        <span class="m-b-none">{{$userinfo}}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="col-sm-10">
                                    <div>
                                        <label class="control-label">提交时间：</label>
                                        <span class="m-b-none">{{$data->Ts}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="col-sm-10">
                                    <div>
                                        <label class="control-label form-inline">工单来源：</label>
                                                <span class="m-b-none form-inline"> 
                                                    @if($isedit)
                                                        @if(!empty($source)){{$source->Means}}@endif
                                                    @else
                                                    <select class="form-control " name="usource">
                                                        <option value="">请选择</option>
                                                        @foreach(ThirdCallHelper::getDictArray("工单来源","supportSource") as $list)
                                                            <option value="{{$list->Code}}" @if ($data->Source==$list->Code) selected @endif>{{$list->Means}}</option>
                                                        @endforeach
                                                    </select>
                                                    @endif
                                                </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="col-sm-10">
                                    <div>
                                        <label class="control-label form-inline">优先级：</label>
                                                <span class="m-b-none form-inline">
                                                     @if($isedit)
                                                    {{$data->priority}}
                                                    @else
                                                    <select class="form-control" name="sorts">
                                                        <option>请选择</option>
                                                        <option value="1" @if($data->priority==1) selected  @endif>1</option>
                                                        <option value="2" @if($data->priority==2) selected  @endif>2</option>
                                                        <option value="3"@if($data->priority==3) selected  @endif>3</option>
                                                    </select>
                                                    @endif
                                                </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="hr-line-dashed"></div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="col-sm-10">
                                    <div>
                                        <label class="control-label form-inline">一级分类：</label>
                                                <span class="m-b-none form-inline"> 
                                                    @if($isedit)
                                                        @if($data->ServiceModel=="IDC")
                                                        IDC
                                                        @else
                                                        安畅云
                                                        @endif
                                                    @else
                                                    <select class="form-control" name="model">
                                                        <option value="IDC" @if($data->ServiceModel=="IDC") selected  @endif>IDC</option>
                                                        <option value="ACCloud" @if($data->ServiceModel=="ACCloud") selected  @endif>安畅云</option>
                                                    </select>
                                                    @endif
                                                </span>
                                                
                                    </div>
                                </div>
                            </div>
                           
                            <div class="col-sm-6">
                                <div class="col-sm-10">
                                    <div>
                                        <label class="control-label form-inline"><span
                                                    class="red">* </span>三级分类：</label>
                                                <span class="m-b-none form-inline">
                                                    @if($isedit)
                                                            @if(!empty($class)){{$class->Means}}@endif
                                                    @else
                                                        <select class="form-control" name="thirdclass">
                                                            <option value="">请选择</option>
                                                            @foreach(ThirdCallHelper::getDictArray("工单类型","WorksheetTypeOne") as $list)
                                                                <option value="{{$list->Code}}">{{$list->Means}}</option>
                                                            @endforeach
                                                        </select>
                                                    @endif
                                                </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="col-sm-10">
                                    <div>
                                        <label class="control-label">数据中心：</label>
                                        <span class="m-b-none">{{$data->dataCenter}}</span>
                                    </div>
                                </div>
                            </div>
                             <div class="col-sm-6">
                                <div class="col-sm-10">
                                    <div>
                                        <label class="control-label form-inline">二级分类：</label>
                                                <span class="m-b-none form-inline"> 
                                                   @if(!empty($secondclass)) {{$secondclass->Means}} @endif
                                                </span>
                                                
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="col-sm-10">
                                    <div>
                                        <label class="control-label form-inline"><span
                                                    class="red">* </span>第一负责人工作组：</label>
                                                <span class="m-b-none form-inline"> 
                                                   @if($isedit)
                                                         @if($grp1){{$grp1->UsersName}}@endif
                                                    @else
                                                        <select class="form-control" name="group1">
                                                            <option value="">请选择</option>
                                                            @foreach(ThirdCallHelper::getWorkGroups() as $list)
                                                                <option value="{{$list->Id}}">{{$list->UsersName}}</option>
                                                            @endforeach
                                                        </select>
                                                   @endif
                                                </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="col-sm-10">
                                    <div>
                                        <label class="control-label form-inline"><span
                                                    class="red">* </span>第一负责人：</label>
                                                <span class="m-b-none form-inline">
                                                     @if($isedit)
                                                         @if(!empty($usr1)){{$usr1->Name}}@endif
                                                    @else
                                                        <select class="form-control" name="optuser1">
                                                            <option value="">请选择</option>
                                                        </select>
                                                     @endif
                                                </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="hr-line-dashed"></div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="col-sm-10">
                                    <div>
                                        <label class="control-label form-inline">第二负责人工作组：</label>
                                                <span class="m-b-none form-inline"> 
                                                    @if($isedit)
                                                        @if(!empty($grp2)){{$grp2->UsersName}}@endif
                                                    @else
                                                        <select class="form-control" name="group2">
                                                             <option value="">请选择</option>
                                                             @foreach(ThirdCallHelper::getWorkGroups() as $list)
                                                                <option value="{{$list->Id}}">{{$list->UsersName}}</option>
                                                            @endforeach
                                                        </select>
                                                    @endif
                                                </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="col-sm-10">
                                    <div>
                                        <label class="control-label form-inline">第二负责人：</label>
                                                <span class="m-b-none form-inline">
                                                     @if($isedit)
                                                        @if(!empty($usr2)){{$usr2->Name}}@endif
                                                    @else
                                                        <select class="form-control" name="optuser2">
                                                            <option value="">请选择</option>
                                                        </select>
                                                   @endif
                                                </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="hr-line-dashed"></div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="col-sm-10">
                                    <div class="form-inline">
                                        <label class="control-label">添加备注：</label>
                                        <span class="m-b-none">
                                            @if($isedit)
                                                {{$data->Memo}}
                                            @else
                                            <textarea name="remark" class="form-control" style="width:80%"></textarea>
                                            @endif
                                        </span>
                                    </div>
                             </div>
                            </div>
                        </div>
                       </form>
                    @if($isadmin)
                      <div class="text-right">
                                   <ul class="sortable-list connectList agile-list">
                                       <li class="info-element">
                                           @if($isedit)
                                           <button class="btn btn-primary" id="editData">编辑</button>
                                           @endif
                                           @if($data->Status=="待指派"||$data->Status=="待处理")
                                           <button class="btn btn-primary" id="distribute">确认指派</button>
                                           @endif
                                           @if(($data->Status=="已指派"||$data->Status=="处理中")&&$data->Status!="已处理")
                                           <button class="btn btn-primary" id="alreadyProc">已处理</button>
                                           @endif
                                           @if($data->Status=="已指派"||$data->Status=="处理中")
                                           <button class="btn btn-primary" id="reassign">重新指派</button>
                                           @endif
                                           @if(!($data->Status=="已处理"||$data->Status=="已关闭"))
                                           <button class="btn btn-primary" id="supportSplit">拆分工单</button>
                                           @endif
                                           @if($data->Status=="已指派"||$data->Status=="处理中")
                                           <button class="btn btn-primary" id="hangup">工单挂起</button>
                                           @endif
                                            @if($data->Status=="挂起中")
                                            <button class="btn btn-primary" id="release">释放挂起</button>
                                           @endif
                                           <a href="https://uc.51idc.com/cloud/support/cus/{{$data->CustomerInfoId}}/@if($data->CreateUserId>500000){{$data->CreateUserId}}@endif"><button class="btn btn-primary">云计算</button></a>
                                           @if(!empty($data->QuotaRecordId))
                                           <button class="btn btn-primary" id="pepass">配额审核通过</button>
                                           @endif
                                       </li>
                                   </ul>
                       </div>
                        @endif
                        <div class="panel blank-panel">
                            <div class="panel-heading" style="padding:0px;">
                                <div class="panel-options">
                                    <ul class="nav nav-tabs">
                                        <li class="active">
                                            <a href="#tab-1" data-toggle="tab" aria-expanded="true">消息记录</a>
                                        </li>
                                         <li class="">
                                             <a data-toggle="tab" href="#tab-2">挂起记录</a>
                                        </li>
                                         <li class="">
                                             <a data-toggle="tab" href="#tab-3">备注记录</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="tab-content">
                                    <div class="tab-pane active" id="tab-1">
                                        
                                        <div class="ibox float-e-margins">
                                            <h3 class="m-t-lg">工单内容</h3>
                                                <div class="alert alert-success">
                                                    {!! $data['Body'] !!}
                                                </div>
                                         </div>
                                        <div class="hr-line-dashed"></div>
                                        <div class="feed-activity-list" style="overflow-y: auto;max-height:400px;padding: 15px">
                                             <!--消息内容-->
                                               @foreach($optlist as $key=>$opt)
                                               <div class=" feed-element">
                                                    <div style="background-color: white; float:right;text-align:right;">
                                                        <div>
                                                               <a class="message-author" href="javascript:void(0)"style="color: #00aa00">{{$opt->ReplyUser}}</a>
                                                                于{{$opt->ReplyTs}}
                                                                 @if($opt->Datacenter||$opt->Operation||$opt->Datacenter2||$opt->Operation2)
                                                                        {{--分配了工作组 --}}
                                                                        指派任务
                                                                  @elseif(!$opt->DatacenterId && $opt->ReplyId)
                                                                        {{--其他操作 --}}
                                                                        将此工单设为：
                                                                   @else
                                                                        {{--普通消息 --}}
                                                                        回复 
                                                                              {{--撤销按钮 --}}
                                                                            @if(($isadmin)||(!$isadmin&&($key+1==count($optlist)))) 
                                                                            <a class="fa-hover" title="撤销回复"><i class="fa fa-mail-reply" data_time="{{$opt->ReplyTs}}" data_id="{{$opt->Id}}"></i> </a> 
                                                                            @endif
                                                                   @endif
                                                                   
                                                                  @if($opt->AuditUser)
                                                                  ({{$opt->AuditUser}} 于 {{$opt->AuditTs}} 审核确认 该回复信息)
                                                                  @endif
                                                         </div>
                                                        <div class="well" 
                                                             @if($opt->Datacenter||$opt->Operation||$opt->Datacenter2||$opt->Operation2) 
                                                             style="background: gray;color: white;" 
                                                             @endif
                                                             >
                                                            <span class="message-content" id="reply_{{$opt->Id}}">
                                                                @if($opt->Datacenter||$opt->Operation||$opt->Datacenter2||$opt->Operation2)
                                                                        {{--分配了工作组 --}}
                                                                        @if($opt->Datacenter)
                                                                            第一工作组：
                                                                            {{$opt->Datacenter->UsersName}}
                                                                        @endif
                                                                        @if($opt->Operation)
                                                                            操作人：
                                                                            {{$opt->Operation->Name}}
                                                                        @endif
                                                                        @if($opt->Datacenter2)
                                                                            第二工作组：
                                                                            {{$opt->Datacenter2->UsersName}}
                                                                        @endif
                                                                        @if($opt->Operation2)
                                                                            操作人：
                                                                            {{$opt->Operation2->Name}}
                                                                        @endif
                                                                @elseif(!$opt->DatacenterId && $opt->ReplyId)
                                                                    {{--其他操作 --}}
                                                                    <span style="color: red;">
                                                                                    {{$opt->reply}}
                                                                     </span>
                                                                @else
                                                                     {{--普通消息 --}}
                                                                         {!!$opt->reply!!}
                                                                @endif
                                                            </span>
                                                        </div>
                                                        @if($isadmin=='1' && $opt->UCDis==0 && !$opt->ReplyId)
                                                         <div style="float: right;">
                                                                        <button  name='sure_reply' data_id ='{{$opt->Id}}'>确认回复</button>
                                                                        <button name='edit_reply' data_id ='{{$opt->Id}}'>内容编辑</button>
                                                                        <button  name='del_reply' data_id ='{{$opt->Id}}'>删除</button>
                                                         </div>
                                                        @endif
                                                    </div>
                                               </div>
                                               @endforeach
                                        </div>
                                        <div style='padding: 5px'>
                                             <textarea id="msg" style="height: 200px;" name="msg"></textarea>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-primary btn-sm pull-right" id="btnReply"><i class="fa fa-envelope"></i> 发送消息</button>
                                </div>
                            </div>
                                <!--挂起记录start-->
                                 <div class="tab-pane" id="tab-2">
                                            <div class="">
                                                    <table class="table table-hover">
                                                        <thead>
                                                            <tr>
                                                                <th>状态</th>
                                                                <th>是否提醒</th>
                                                                <th>通知时间</th>
                                                                <th>通知人员</th>
                                                                <th>提醒方式</th>
                                                                <th>连续提醒(分钟)</th>
                                                                <th>挂起说明</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($hanguptask as $row)
                                                            <tr>
                                                                <td>@if($row->State=="1") 挂起中 @else  已释放 @endif</td>
                                                                <td>@if($row->Remind=="1") 是 @else  否 @endif</td>
                                                                <td>{{$row->RemindTs}}</td>
                                                                <td>{{$row->remindusers}}</td>
                                                                <td>{{$row->remindtypes}}</td>
                                                                <td>@if(isset($row->ContinuityRemind)) @if($row->ContinuityRemind=="0") 不需要 @else  {{$row->ContinuityRemind}}分钟 @endif  @endif</td>
                                                                <td>{{$row->HangupText}}</td>
                                                            </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                            </div>
                                 </div>
                                     <!--挂起记录end-->
                                
                                 
                                 <!--备注记录start-->
                                 <div class="tab-pane" id="tab-3">
                                     
                                                         <div class="">
                                                             @foreach($remarks as $row)
                                                            <div class="timeline-item">
                                                                <div class="row">
                                                                    <div class="col-xs-3 date">
                                                                        <i class="fa fa-file-text"></i> {{$row["ReplyTs"]}}
                                                                        <br>
                                                                    </div>
                                                                    <div class="col-xs-7 content">
                                                                        <p class="m-b-xs"><strong>{{ $row->replyUser($row["ReplyUserId"])}}</strong> 添加了备注
                                                                        </p>
                                                                        <p>{{$row["reply"]}}</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                             @endforeach
                                                        </div>
                                     
                                 </div>
                                 <!--备注记录end-->
                                    
                                </div>
                                
                                </div>

                </div>
            </div>


        </div>
        </div>
        <!--右-->
        <div class="col-sm-3">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>联系方式</h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="col-sm-10">
                                <div>
                                    <label class="control-label form-inline">客户名称：</label>
                                    <span class="m-b-none form-inline">{{$customer['CusName']}}</span>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="col-sm-10">
                                <div>
                                    <label class="control-label form-inline">联系人：</label>
                                    <span class="m-b-none form-inline">@if(!empty($contact)) {{$contact['Name']}} @endif</span>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="row">

                        <div class="col-sm-6">
                            <div class="col-sm-10">
                                <div>
                                    <label class="control-label form-inline">邮　　件：</label>
                                    <span class="m-b-none form-inline">@if(!empty($contact)) {{$contact['Email']}} @endif</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="col-sm-10">
                                <div>
                                    <label class="control-label">电　话：</label>
                                    <span class="m-b-none">@if(!empty($contact)) {{$contact['Tel']}} @endif</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="col-sm-10">
                                <div>
                                    <label class="control-label">手　　机：</label>
                                    <span class="m-b-none">@if(!empty($contact['Mobile'])) {{$contact['Mobile']}} @endif</span>
                                    @if(!empty($contact['Mobile']))<span><button type="button" class="btn btn-primary btn-sm pull-right" id="sendMsg"><i class="fa fa-envelope"></i> 发送短信</button></span>@endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>当前工单Last10</h5>
                </div>
                <div class="ibox-content">

                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>工单编号</th>
                            <th>工单标题</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($toplist as $row)
                        <tr>
                            <td>{{$row->Id}}</td>
                            <td>
                                <a href='/wo/supportrefer/{{$row->Id}}'><span class="line">{{$row->Title}}</span></a>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
</div>
</body>

</html>

    <!-- 全局js -->
    <script src="/js/jquery.min.js?v=2.1.4"></script>
    <script src="/js/bootstrap.min.js?v=3.3.6"></script>
    <script src="/js/plugins/layer/layer.min.js"></script>
    <script charset="utf-8" src="/js/plugins/kindeditor/kindeditor-min.js"></script>
    <script charset="utf-8" src="/js/plugins/kindeditor/lang/zh_CN.js"></script>
    <!-- 自定义js -->
    <script src="/js/content.js?v=1.0.0"></script>
    <!-- iCheck -->
    <script src="/js/plugins/iCheck/icheck.min.js"></script>
    <script>
        <!--网页编辑器-->
        KindEditor.options.filterMode = false;
        KindEditor.ready(function(K) {
	    window.editor1 = K.create('#msg',{                 
	      	resizeType : 1,
	      	uploadJson : "/kindeditor/uploadify",
	      	width : "100%",
	      	items : [
	          	'justifyleft', 'justifycenter', 'justifyright', 'forecolor', 'hilitecolor', 'bold',
	          	'italic', 'underline','image', 'link', 'fullscreen'
	      	],afterBlur:function(){this.sync();}
	    });
	})
        <!--消息提示-->
        function lalert(txt){
            if(txt!='')
            layer.alert(txt, {icon: 2,closeBtn:false,area: '100px'});
        }
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
             
             <!--动态查询指派人-->
            $("select[name='group1'],select[name='group2']").on("change",function(){
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
                                obj.append('<option value="'+data[i].UserId+'">'+data[i].Name+'</option>');
                            }
                        }
                    }
                });
            });
            
            <!--编辑-->
            $("#editData").click(function(){
                var sid = $("input[name='sid']").val();
                 layer.open({
                        type: 2,
                        title: '工单相关项编辑',
                        shade: false,
                        maxmin: false,
                        shadeClose: true, //点击遮罩关闭层
                        area : ['800px' , '500px'],
                        content: '/wo/editsupport/'+sid
                    });
            });
            
            <!--确认指派-->
            $("#distribute").click(function(){
                if($("select[name='thirdclass']").val()==""){
                    lalert("请选择三级分类");
                    return false;
                }
                if($("select[name='group1']").val()==""){
                    lalert("请选择第一负责人工作组");
                    return false;
                }
                if($("select[name='optuser1']").val()==""){
                    lalert("请选择第一负责人");
                    return false;
                }
                $.ajax({
                    type:"post",
                    dataType:'json',
                    url:"/wo/csupport",
                    data:$("#myform").serializeArray(),
                    success:function(data){
                        if(data&&data.status){
                            layer.msg('工单指派成功！', {
                                icon: 1,
                                time: 1000 //1秒关闭
                            },function(){
                                location.reload();
                            });
                        }
                    }
                })
            })
            
            <!--重新指派-->
            $("#reassign").click(function(){
                var sid = $("input[name='sid']").val();
                 layer.open({
                        type: 2,
                        title: '重新指派',
                        shade: false,
                        maxmin: false,
                        shadeClose: true, //点击遮罩关闭层
                        area : ['800px' , '400px'],
                        content: '/wo/reassign/'+sid
                    });
            })
            
            <!--已处理-->
            $("#alreadyProc").click(function(){
                layer.confirm('您要将工单设置为已处理吗?', {icon: 3, title:'提示'}, function(index){
                        var id =  $("input[name='sid']").val();
                        layer.open({
                                type: 2,
                                title: '工单已处理操作',
                                shade: false,
                                maxmin: false,
                                shadeClose: true, //点击遮罩关闭层
                                area : ['800px' , '400px'],
                                content: '/wo/alreadyproc/'+id
                            });
                        layer.close(index);
                });
            });
            
            <!--消息回复->
            $("#btnReply").click(function(){
                var msg = $("#msg").val();
                    msg = $.trim(msg);
                if(msg==''){
                    lalert("请输入回复内容!");
                    return false;
                 }
                 $("#msg").val("");
                 var sid = $("input[name='sid']").val();
                 $.ajax({
                    type:"post",
                    dataType:'json',
                    url:"/wo/reply",
                    data:{'msg':msg,'sid':sid},
                    success:function(data){
                        if(data&&data.status){
                            layer.msg('回复成功！', {
                                icon: 1,
                                time: 1000 //1秒关闭
                            },function(){
                                location.reload();
                            });
                        }
                    }
             });
         });
         
            <!--确认回复-->
            $("button[name='sure_reply']").click(function(){
                var id = $(this).attr("data_id");
                layer.confirm('您确定要执行该操作？', {icon: 3, title:'确认回复'}, function(index){
                        $.ajax({
                        type:"post",
                        dataType:'json',
                        url:"/wo/surereply/"+id,
                        success:function(data){
                           
                            if(data&&data.status){
                                layer.msg('操作成功！', {
                                    icon: 1,
                                    time: 1000 //1秒关闭
                                },function(){
                                    location.reload();
                                });
                            }
                             layer.close(index);
                        }
                    });
                   
                  });
            });
            
            <!--编辑回复-->
            $("button[name='edit_reply']").click(function(){
                var id = $(this).attr("data_id");
                layer.open({
                        type: 2,
                        title: '工单回复内容编辑',
                        shade: false,
                        maxmin: false,
                        shadeClose: true, //点击遮罩关闭层
                        area : ['800px' , '400px'],
                        content: '/wo/editreply/'+id
                    });
            });
            
            <!--删除回复-->
            $("button[name='del_reply']").click(function(){
                var id = $(this).attr("data_id");
                 layer.confirm('您确定要删除该回复信息么？', {icon: 3, title:'删除'}, function(index){
                     $.ajax({
                        type:"post",
                        dataType:'json',
                        url:"/wo/delreply/"+id,
                        success:function(data){
                            
                            if(data&&data.status){
                                layer.msg('操作成功！', {
                                    icon: 1,
                                    time: 1000 //1秒关闭
                                },function(){
                                    location.reload();
                                });
                            }
                            layer.close(index);
                        }
                    });
                    
                  });
            });
            
            <!--工单拆分-->
           $('#supportSplit').click(function(){
               var sid = $("input[name='sid']").val();
                layer.open({
                type: 2,
                title: '◆工单管理>工单详情>工单拆分',
                area: ['800px', '550px'],
                skin: 'layui-layer-rim',
                content: ['/support/supportsplit?supId=' + sid, 'no']
            });
           });
           
           <!--发送短信-->
           $("#sendMsg").click(function(){
                var sid = $("input[name='sid']").val();
                 layer.open({
                        type: 2,
                        title: '短信发送详情',
                        shade: false,
                        maxmin: false,
                        shadeClose: true, //点击遮罩关闭层
                        area : ['800px' , '400px'],
                        content: '/wo/sendsms/'+sid
                    });
           });
           
           <!--工单挂起-->
           $("#hangup").click(function(){
               var sid = $("input[name='sid']").val();
               layer.open({
                        type: 2,
                        title: '工单挂起',
                        shade: false,
                        maxmin: false,
                        shadeClose: true, //点击遮罩关闭层
                        area : ['500px' , '600px'],
                        content: '/wo/hangup/'+sid
                    });
           });
           
          <!--释放挂起-->
          $("#release").click(function(){
              layer.confirm('您确定要释放此工单?', {icon: 3, title:'提示'}, function(index){
                    var sid = $("input[name='sid']").val();
                    $.ajax({
                        type:"post",
                        dataType:'json',
                        url:"/wo/release/"+sid,
                        success:function(data){
                            if(data&&data.status){
                                layer.msg('操作成功！', {
                                    icon: 1,
                                    time: 1000 //1秒关闭
                                },function(){
                                    location.reload();
                                });
                            }
                            layer.close(index);
                        }
                  });
                });
          });
          
         <!--撤销消息-->
         $(".fa-mail-reply").click(function(){
             var timelimit ={{$timereply}};
                   timelimits = timelimit*1000;
             var replytime = $(this).attr("data_time");
                   replytime = Date.parse(replytime);
             var now = Date.parse(new Date());
             var id = $(this).attr("data_id");
             if((now-replytime)>timelimits){
                  lalert("回复时间超过"+parseInt(timelimit/60,10)+"分钟的消息，不能被撤回。");
                  return false;
             }
             layer.confirm('您确定要撤回该回复信息么？', {icon: 3, title:'提示'}, function(index){
                    $.ajax({
                        type:"post",
                        dataType:'json',
                        url:"/wo/prescind/"+id,
                        success:function(data){
                            if(data&&data.status){
                                layer.msg('操作成功！', {
                                    icon: 1,
                                    time: 1000 //1秒关闭
                                },function(){
                                    location.reload();
                                });
                            }
                            layer.close(index);
                        }
                  });
                });
         });
         
         <!--配额审核通过-->
         $("#pepass").click(function(){
              layer.confirm('配额确定审核通过吗?', {icon: 3, title:'提示'}, function(index){
                    var sid = $("input[name='sid']").val();
                    $.ajax({
                        type:"post",
                        dataType:'json',
                        url:"/wo/postquota/"+sid,
                        success:function(data){
                            if(data){
                                layer.msg(data.status?'审核成功！':"审核失败", {
                                    icon: 1,
                                    time: 1000 //1秒关闭
                                },function(){
                                    location.reload();
                                });
                            }
                            layer.close(index);
                        }
                  });
                });
         });
        })
    </script>
