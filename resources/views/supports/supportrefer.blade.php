<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>工单详情</title>
    <link href="/css/style.css?v=4.1.0" rel="stylesheet">
    <link href="/css/font-awesome.css?v=4.4.0" rel="stylesheet">
    <link rel="stylesheet" href="/css/job_detail.css?2"/>
    <link rel="stylesheet" href="/css/event_charge.css"/>
    <link rel="stylesheet" href="/js/plugins/kindeditor/themes/default/default.css"/>
    <link rel="stylesheet" href="/js/plugins/kindeditor/plugins/code/prettify.css"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .red {
            color: #fd0001
        }

        .relate-btn {
            display: inline-block;
            padding: 6px 15px;
            font-size: 14px;
            text-align: center;
            color: #ffffff;
            border-radius: 3px;
            background-color: #19b492;
            margin-left: 10px;
            margin-bottom: 12px;
        }

        .event-table tr a {
            height: 0;
        }

        .ke-icon-replymode {
            background-image: url(/img/speedreply.png);
            background-position: 0px 0px;
            width: 16px;
            height: 16px;
        }

        .ke-menu-item {
            border: 1px solid #F1F1F1;
            background-color: #F1F1F1;
            color: #222222;
            height: 300px;
        }

        .replygroup, .menuTitle ul li {
            line-height: 21px;
            font-size: 13px;
        }

        .hidden {
            display: none;
        }

        .word-pre{
            white-space: pre-line;
        }

    </style>
</head>
<body>
<div id="job-detail" class="job-detail clearfix">
    <div class="job-detail-left">
        <form id="myform" name="hideDiv">
            <div class="job-info module-style" style="position: relative;">
                <input type="hidden" name="sid" id="supportId" value="{{$data->Id}}"/>
                <input type="hidden" id="contents" value="{{$data->Body}}"/>
                <input type="hidden" id="mobile" value="@if(isset($contact['Mobile'])){{$contact['Mobile']}}
                @elseif(isset($data->mobile)&& !empty($data->mobile)){{$data->mobile}}@else''@endif"/>
                <input type="hidden" id="extension" value="{{$extension['extension']}}"/>
                <input type="hidden" id="type3" value="@if(!empty($class)){{$data->ClassInficationOne}}@endif"/>
                <div class="info-top">
                    @if(isset($data->InValidate) && $data->InValidate == 1)
                        <span class="info-title" style="display: inline;">
                            {{-- 工单编号：--}}<span title="工单编号:{{$data->Id}}"
                                style="margin-right: 6px;font-size: 18px;color:#c2c2c2;">{{$data->Id}}</span>
                            {{-- 工单主题：--}}
                            <span style="width: calc(100% - 450px);margin-right: 2px;color:#c2c2c2;"
                                  title="工单主题:{{$data->Title}}">{{$data->Title}}
                                <span title="{{$userName}}于{{$data->InValidateAt}}删除了工单"
                                      style="color: red;font-size: 14px;">(已删除)</span>
                            </span>
                        </span>
                    @else
                        <span class="info-title" style="display: inline;">
            {{-- 工单编号：--}}<span title="工单编号:{{$data->Id}}"
                                style="margin-right: 6px;color: red;font-size: 18px;">{{$data->Id}}</span>
                            {{-- 工单主题：--}}<span title="工单主题:{{$data->Title}}" style="width: calc(100% - 450px);margin-right: 2px;"
                                                class="">{{$data->Title}}</span></span>
                    @endif

                    @if($isadmin)
                        <a><img onclick="return false;" id="cloudReckon"
                                style="float:right;padding: 0 0 0 6px;margin: 10px 0; " src="/img/icon_cloud.svg"/></a>
                        @if(!empty($data->QuotaRecordId))
                            <button id="pepass" onclick="return false;">配额审核通过</button>
                        @endif
                        @if($isedit)
                            <button id="editData" onclick="return false;">编辑</button>
                        @endif
                        @if(($data->Status=="已指派"||$data->Status=="处理中")&$isedit)
                            <button id="hangup" onclick="return false;">工单挂起</button>
                        @endif
                        @if($data->Status=="挂起中" || $isSuspend)
                            <button id="release" onclick="return false;">释放挂起</button>
                        @endif
                        @if($data->Status=="待指派"||$data->Status=="待处理"||!$isedit)
                            <button id="distribute" onclick="return false;">确认指派</button>
                        @endif
                        @if(($data->Status=="已指派"||$data->Status=="处理中"||$data->Status=="已处理"||$data->Status=="已关闭")&&$isedit)
                            <button id="reassign" onclick="return false;">重新指派</button>
                        @endif
                        @if(!($data->Status=="已处理"))
                            <button id="supportSplit" onclick="return false;">拆分工单</button>
                        @endif
                    @endif
                    @if($isSuspend )
                        <button id="release" onclick="return false;">释放挂起</button>
                    @endif
                    <span onclick="return false;"><a id="yincang" title="收起详情">
                            <li class="fa fa-unlock" style="padding-right: 7px;float: right;padding-top: 16px"></li>
                        </a></span>
                    <div class="sk-spinner sk-spinner-fading-circle hide" id='processingbtn'
                         style="float:right;padding: 0 10px;margin: 10px 0; ">
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
                </div>
                <div class="info-content">
                    <ul id="sdetails" class=" @if(!$isedit) showborder @endif">
                        <li><span>设备编号：</span>
                            <p style="line-height:18px;">{{$data->EquipmentId}}</p></li>
                        <li><span>IP地址：</span>
                            <p style="line-height:18px;">{{strlen($data->devIPAddr)>33?substr($data->devIPAddr,0,16)."...":$data->devIPAddr}}</p>
                            @if(strlen($data->devIPAddr)>33)
                                <img  style="margin-bottom: -4px;" onmouseleave="javascript:layer.closeAll();"
                                      src="{{url("/img/details.png")}}" id="ipAddr"
                                      onmouseover="openIpDetail('{{$data->devIPAddr}}')" />
                            @endif
                        </li>
                        <li><span>工单状态：</span>
                            <p>{{$data->Status}}</p></li>
                        <li><span>优先级：</span>
                            <p>
                                @if($isedit)
                                    {{$data->priority}}
                                @else
                                    <select class="form-control" name="sorts" style="margin-left:22px;min-width: 80px;">
                                        <option>请选择</option>
                                        <option value="1" @if($data->priority==1) selected @endif>1</option>
                                        <option value="2"
                                                @if($data->priority==2||(($identity['isVIP']||$identity['isMAN'])&&!$data->priority)) selected @endif>
                                            2
                                        </option>
                                        <option value="3"
                                                @if($data->priority==3||(!$data->priority && !($identity['isVIP']||$identity['isMAN']))) selected @endif>
                                            3
                                        </option>
                                    </select>
                                @endif
                            </p>
                        </li>
                        <li>
                            <span>数据中心：</span>
                            <p>
                                @if($isedit)
                                    {{$data->dataCenter}}
                                @else
                                    <select class="form-control" name="datacenter" id="chooseDataCenter"
                                            style="min-width: 80px;">
                                        <option value="">请选择</option>
                                        @foreach(ThirdCallHelper::getDataCenter($data->ServiceModel) as $list)
                                            <option value="{{$list->DataCenterName}}"
                                                    @if($data->dataCenter==$list->DataCenterName) selected @endif>{{$list->DataCenterName}}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </p>
                        </li>
                        <li><span @if(!$isedit)style="margin: 0 -6px;"@endif>@if(!$isedit)<span
                                        class="red">*</span>@endif三级分类：</span>
                            <p>
                                @if($isedit)
                                    @if(!empty($class)){{$class->Means}}@endif
                                        &nbsp;&nbsp;
                                        {{$data->supportTag?"(标签:".ThirdCallHelper::getDictMeans("工单标签","supportTag","$data->supportTag").")":""}}
                                @else
                                    <select class="form-control" name="thirdclass" style="display: inline-block;width: 100px;margin-left:5px">
                                        <option value="">请选择</option>
                                        @foreach(ThirdCallHelper::getDictArray("工单类型","WorksheetTypeOne") as $list)
                                            <option value="{{$list->Code}}" @if($data->ClassInficationOne == $list->Code) selected @endif>{{$list->Means}}</option>
                                        @endforeach
                                    </select>
                                    <select class="form-control" name="supportTag" style="display: inline-block;width: 110px;margin-left:5px">
                                        <option value="">工单标签</option>
                                        @foreach(ThirdCallHelper::getDictArray("工单标签","supportTag","unemail") as $list)
                                            <option value="{{$list->Code}}" @if($data->supportTag == $list->Code) selected @endif>{{$list->Means}}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </p>
                        </li>
                        <li><span @if(!$isedit)style="margin: 0 -6px;"@endif>@if(!$isedit)<span
                                        class="red">*</span>@endif第一负责组：</span>
                            <p>
                                @if($isedit)
                                    @if($grp1){{$grp1->UsersName}}@endif
                                @else
                                    <select class="form-control" name="group1" style="width:100px;margin-left:6px">
                                        <option value="">请选择</option>
                                        @foreach(ThirdCallHelper::getWorkGroups() as $list)
                                            <option value="{{$list->Id}}">{{$list->UsersName}}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </p>
                        </li>
                        <li><span @if(!$isedit)style="margin: 0 -6px;"@endif>@if(!$isedit)<span
                                        class="red">*</span>@endif第一负责人：</span>
                            <p>
                                @if($isedit)
                                    @if(!empty($usr1)){{$usr1->Name}}@endif
                                @else
                                    <select class="form-control" name="optuser1" style="width:100px;margin-left:6px">
                                        <option value="">请选择</option>
                                    </select>
                                @endif
                            </p>
                        </li>
                        <li style="@if($isedit) display: none @endif" name='hidenode'><span>一级分类：</span>
                            <p>
                                @if($isedit)
                                    {{\Itsm\Http\Helper\ThirdCallHelper::getDictMeans("工单业务类型","serviceModel",$data->ServiceModel)}}
                                @else
                                    <select class="form-control" name="model" style="min-width: 80px"
                                            id="chooseOneType">
                                        @foreach(ThirdCallHelper::getDictArray("工单业务类型","serviceModel") as $item)
                                            <option value="{{$item->Code}}" @if($data->ServiceModel==$item->Code) selected @endif>
                                                {{$item->Means}}
                                            </option>
                                        @endforeach
                                    </select>
                                @endif
                            </p>
                        </li>
                        <li style="@if($isedit) display: none @endif" name='hidenode'><span>二级分类：</span>
                            <p>
                                @if(!empty($secondclass)) {{$secondclass->Means}} @endif
                            </p>
                        </li>
                        <li style="@if($isedit) display: none @endif" name='hidenode'><span>第二负责组：</span>
                            <p>
                                @if($isedit)
                                    @if(!empty($grp2)){{$grp2->UsersName}}@endif
                                @else
                                    <select class="form-control" name="group2" style="width:100px;">
                                        <option value="">请选择</option>
                                        @foreach(ThirdCallHelper::getWorkGroups() as $list)
                                            <option value="{{$list->Id}}">{{$list->UsersName}}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </p>
                        </li>
                        <li style="@if($isedit) display: none @endif" name='hidenode'><span>第二负责人：</span>
                            <p>
                                @if($isedit)
                                    @if(!empty($usr2)){{$usr2->Name}}@endif
                                @else
                                    <select class="form-control" name="optuser2" style="width:100px;">
                                        <option value="">请选择</option>
                                    </select>
                                @endif
                            </p>
                        </li>
                        <li style="@if($isedit) display: none @endif" name='hidenode'><span>工单来源：</span>
                            <p>
                                @if($isedit)
                                    @if(!empty($source)){{$source->Means}}@endif
                                @else
                                    <select class="form-control " name="usource" style="width:103px;">
                                        <option value="">请选择</option>
                                        @foreach(ThirdCallHelper::getDictArray("工单来源","supportSource") as $list)
                                            <option value="{{$list->Code}}"
                                                    @if ($data->Source==$list->Code) selected @endif>{{$list->Means}}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </p>
                        </li>
                        <li style="width:73%;@if($isedit) display: none @endif" name='hidenode'>
                            <span style="vertical-align: top;margin-left: 24px">备注：</span>
                            <p>
                                @if($isedit)
                                    {{$data->Memo}}
                                @else
                                    <textarea name="remark" class="form-control" cols="57"></textarea>
                                @endif
                            </p>
                        </li>
                    </ul>@if($isedit)
                        <div style='position: absolute;bottom: 0;right: 8px;'><a id='displayobj' class="fa-hover"
                                                                                 title="显示"><i
                                        class="fa fa-sort-desc"></i></a>
                        </div>@endif
                </div>

                <div class="list-normal" style="">
                    <div class="normal-title" style='word-wrap: break-word;'>
                        <div class="title-wrap">工单内容：
                            @if($data->jsonConfig)
                                <img  style="margin-bottom: -4px;" onmouseleave="javascript:layer.closeAll();"
                                      src="{{url("/img/supportConfig.png")}}" id="jsonConfig" width="20px"
                                      onmouseover="openConfigDetail({{$data->Id}})" />
                            @endif
                        </div>
                        <div class="title-content stress-title record-list" id="recordCommuList"
                             style="max-height: 110px">
                            {!! $data->Body !!}</div>
                        <div class="hidden" name="contents" style="width: 100%;">
                            <a title="查看全部内容" name="contents"
                               style="float: right;color:#1bcbab;font-size: 12px;margin-top: -17px;background-color: white">查看详情</a>
                        </div>
                    </div>
                </div>

            </div>
        </form>
        <div id="job-detail-content" class="job-detail-content">
            <div class="job-record module-style">
                <div class="label-title">
                    <span id="recordCommu" class="title_active">沟通记录<span class="label_line"></span></span>
                    <span id="relateChange" class="title_active">相关变更</span>
                    <span id="relateIssue" class="title_active">相关问题</span>
                    @if($hanguptask->count())<span id="recordChange">挂起记录</span>@endif
                    @if($remarks->count())<span id="recordRemark">备注及编辑详情</span>@endif
                </div>
                <div id="relateChangeList" class="record-list hide">
                    <div>
                        <table id="relateChangeTable" class="event-table" style="width: 100%;"
                               style="text-align: center;color:#6b7d86;" bgcolor="#FFFFFF"
                               cellpadding="0"
                               cellspacing="0" width="100%"
                               data-pagination="true"
                               data-show-export="true"
                               data-page-size="10"
                               data-id-field="Id"
                               data-pagination-detail-h-align="right"
                               data-page-list="[10]"
                               data-show-footer="false"
                               data-side-pagination="server"
                               data-url="/support/relateChangeData?supportId={{$data->Id}}"
                               data-response-handler="responseHandler">
                        </table>
                    </div>
                    <div>
                        <input type="button" id="changeClose" class="relate-btn" value="批量取消关联">
                        <input type="button" id="triggerChange" class="relate-btn" value="生成并提出变更申请">
                        <input type="button" id="toRelateChange" class="relate-btn" value="关联已有变更">
                        <input type="hidden" id="hiddenChangeId" value="">
                    </div>
                </div>
                <div id="relateIssueList" class="record-list hide">
                    <div>
                        <table id="relateIssueTable" class="event-table" style="width: 100%;"
                               style="text-align: center;color:#6b7d86;" bgcolor="#FFFFFF"
                               cellpadding="0"
                               cellspacing="0" width="100%"
                               data-pagination="true"
                               data-show-export="true"
                               data-page-size="10"
                               data-id-field="Id"
                               data-pagination-detail-h-align="right"
                               data-page-list="[10]"
                               data-show-footer="false"
                               data-side-pagination="server"
                               data-url="/support/relateIssueData?supportId={{$data->Id}}"
                               data-response-handler="responseHandler">
                        </table>
                    </div>
                    <div>
                        <input type="button" id="issueClose" class="relate-btn" value="批量取消关联">
                        <input type="button" id="triggerIssue" class="relate-btn" value="生成并提出问题申请">
                        <input type="button" id="toRelateIssue" class="relate-btn" value="关联已有问题">
                        <input type="hidden" id="hiddenIssueId" value="">
                    </div>
                </div>
                <div id="recordCommuListCont" class="record-list">

                    @if(!$optlist->count())暂无记录@endif

                    @foreach($optlist as $key=>$opt)
                        {{--客户字体加粗--}}
                        <div class="@if(!$isadmin&&$opt->ReplyUserId>500000)
                                list-stress
                            @elseif($isadmin=='1' && $opt->UCDis==0 && !$opt->ReplyId)
                                list-reply
                            @else
                                list-with-img
                                @endif

                        @if($opt->Datacenter||$opt->Operation||$opt->Datacenter2||$opt->Operation2 ||$opt->UCDis ==5)
                                list-special
                    @endif
                                "
                             @if($opt->ReplyUserId>500000) style="background-color: rgba(230, 239, 255, 0.99)" @endif>
                            <div class="list-wrap-left">
                                {{--头像显示--}}
                                @if($opt->ReplyUserId>500000)
                                    <img class="left-portrait" src="{{url("/img/portrait.png")}}"/>
                                @else
                                    <p class="left-no-portrait {{--L0/L1/数据中心颜色start --}}
                                    @if(!empty($opt->grpl0))
                                            left-no-portrait-grpl0
                                          @elseif(!empty($opt->grpl1))
                                            left-no-portrait-grpl1
                                          @elseif(!empty($opt->grpcenter))
                                            left-no-portrait-grpcenter
                                          @endif
                                    {{--L0/L1/数据中心颜色end --}}">
                                        <span class="portrait-text">{{$opt->ReplyUseri}}</span>
                                    </p>
                                @endif
                            </div>
                            <div class="list-wrap-right">
                                <div>
                                    <div class="normal-title">
                                        <div class=" @if($opt->ReplyUserId<500000) title-wrap @endif ">
                                            <div class="title-type-short">{{$opt->ReplyUser}}：</div>
                                            {{--操作按钮start --}}
                                            @if(($isadmin&& $opt->UCDis&&!$opt->ReplyId&&$opt->ReplyUserId<500000 && $opt->eight=='on')||(!$isadmin&&(!$opt->ReplyId)&&($key+1==count($optlist))&&$opt->ReplyUserId<500000 && $opt->eight=='off'))
                                                <span class="list-opt list-opt-right fa_mail_reply"
                                                      data_time="{{$opt->ReplyTs}}" data_id="{{$opt->Id}}">撤销回复</span>
                                            @endif
                                            @if($isadmin=='1' && $opt->UCDis==0 && !$opt->ReplyId)
                                                <span class="list-opt list-opt-right" name='del_reply'
                                                      data_id='{{$opt->Id}}'>删除</span>
                                                <span class="list-opt list-opt-left" name='edit_reply'
                                                      data_id='{{$opt->Id}}'><span>内容编辑</span></span>
                                                <span class="list-opt list-opt-left" name='sure_reply'
                                                      data_id='{{$opt->Id}}'><span>确认回复</span></span>
                                            @endif
                                            @if($opt->Datacenter||$opt->Operation||$opt->Datacenter2||$opt->Operation2 ||$opt->UCDis ==5)
                                                <span class="list-opt-left" style="float: right;padding-right: 25px"><pre
                                                            class="fa fa-eye-slash" id="eyeclose"></pre></span>
                                            @endif
                                            @if(!$isConfirm && $opt->Id==$lastAppointId && ($opt->OperationId == $userId ||$opt->ChargeUserTwoId == $userId))
                                                <span class="list-opt list-opt-left" name='sure_appoint'
                                                      data_id='{{$opt->Id}}'><span>确认接受指派</span></span>
                                            @endif
                                            {{--操作按钮end --}}
                                        </div>
                                    </div>

                                    <div class="title-content {{--客户内容加粗start --}}@if($opt->ReplyUserId>500000) stress-title @endif{{--客户内容加粗end --}}">
                                        {{--操作属性start --}}
                                        @if($opt->Datacenter||$opt->Operation||$opt->Datacenter2||$opt->Operation2)
                                            {{--分配了工作组 --}}
                                            指派任务
                                        @elseif(!$opt->DatacenterId && $opt->ReplyId)
                                            {{--其他操作 --}}
                                            将此工单设为
                                        @else
                                            {{--普通消息 --}}

                                        @endif
                                        {{--确认审核消息start--}}
                                        @if($opt->AuditUser)
                                            ({{$opt->AuditUser}} 于 {{$opt->AuditTs}} 审核确认 该回复信息)
                                        @endif
                                        {{--确认审核消息end--}}
                                        {{--操作属性end--}}
                                        {{--消息内容start--}}
                                        @if($opt->Datacenter||$opt->Operation||$opt->Datacenter2||$opt->Operation2)
                                            {{--分配了工作组 --}}
                                            @if($opt->Datacenter)
                                                第一工作组：
                                                {{$opt->Datacenter->UsersName}}
                                            @endif
                                            @if($opt->Operation)
                                                操作人：
                                                [{{$opt->Operation->Name}}]
                                            @endif
                                            @if($opt->Datacenter2)
                                                第二工作组：
                                                {{$opt->Datacenter2->UsersName}}
                                            @endif
                                            @if($opt->Operation2)
                                                操作人：
                                                [{{$opt->Operation2->Name}}]
                                            @endif
                                        @elseif(!$opt->DatacenterId && $opt->ReplyId)
                                            {{--其他操作 --}}
                                        <span style="color: red;">{{$opt->reply}}</span>
                                        @elseif($opt->UCDis == 5)
                                            {{--其他操作 --}}
                                            {{$opt->reply}}
                                        @else
                                            {{--普通消息 --}}
                                        <div class="@if($opt->ReplyUserId>500000) word-pre @endif">{!!$opt->reply!!}</div>
                                        @endif
                                        {{--消息内容end--}}
                                    </div>

                                </div>

                                <p class="list-time">{{$opt->ReplyTs}}</p>
                            </div>
                        </div>
                    @endforeach
                    @if( $data->Status != "已关闭")
                        <div class="job-reply">
                            <div class="reply-editor">
                                <textarea id="msg" style="height: 140px;width:100%" name="msg"></textarea>
                            </div>
                            <div class="reply-opt">
                                <button class="reply-btn" id="btnReply">回复</button>
                                <button class="reply-btn" id="resetBtn">重置</button>
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
                                @if(($isadmin&&($data->Status=="已指派"||$data->Status=="处理中")&&$data->Status!="已处理")|| ($isNocop &&($data->Status=="已指派"||$data->Status=="处理中")))
                                    <p class="reply-tips">若您已解决问题，请将工单设为
                                        <button class="reply-status" id="alreadyProc">已处理</button>
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
                @if($hanguptask->count())
                    <div id="recordChangeList" class="record-list hide">
                        <table class="event-table" style="width: 100%">
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
                            @if(!$hanguptask->count())
                                <tr>
                                    <td colspan="7" style="text-align:center">暂无记录</td>
                                </tr>@endif
                            @foreach($hanguptask as $row)
                                <tr style="text-align:center">
                                    <td>@if($row->State=="1")<span class="text_active"> 挂起中</span> @else  <span
                                                class="text_stopped">已释放</span> @endif</td>
                                    <td>@if($row->Remind=="1") 是 @else  否 @endif</td>
                                    <td>{{$row->RemindTs}}</td>
                                    <td>{{$row->remindusers}}</td>
                                    <td>{{$row->remindtypes}}</td>
                                    <td>@if(isset($row->ContinuityRemind)) @if($row->ContinuityRemind=="0")
                                            不需要 @else  {{$row->ContinuityRemind}}分钟 @endif  @endif</td>
                                    <td style='width:20%'>{{$row->HangupText}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if($remarks->count())
                    <div id="recordRemarkList" class="record-list hide">
                        @if(!$remarks->count())暂无记录@endif
                        @foreach($remarks as $row)
                            <div class="list-with-img">
                                <p class="normal-title">
                                    <span class="normal-title">{{ $row->replyUser($row["ReplyUserId"])}}
                                        ： {!! $row["reply"] !!}</span>
                                </p>
                                <p class="list-time">{{$row["ReplyTs"]}}</p>
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>

        </div>
    </div>
    <span onclick="return false;" style="margin-left: 170px"><a id="zhankai" title="展开工单详情" class="hidden">
            <li class="fa fa-unlock"></li>
        </a></span>
    <div class="job-detail-right module-style">
        <div class="client-info">
            <p class="label-title"><i class="label-icon client-icon"></i>本次工单联系人明细</p>
            <div class="client-info-content">
                <ul>
                    <li><span>客户名称：</span>
                        <a class="client-name showCusName" id="cusName"
                           style="white-space: nowrap;text-overflow: ellipsis;overflow: hidden;width: 150px;"
                           href="{{env('JOB_URL', 'http://www.51idc.cn')}}/crm/user/finance/customer_detail.html?cusinfid={{$customer['Id']}}"
                           target="_blank">@if(strlen($customer['CusName'])>10){{mb_substr($customer['CusName'],0,10)}}
                            .. @else {{$customer['CusName']}}@endif</a>
                    </li>
                    <li><span>工单要求：</span>
                        <a class="client-name showCusMemo" id="cusMemo"
                           style="white-space: nowrap;text-overflow: ellipsis;overflow: hidden;width: 150px;color:red">@if(strlen($customer['supportMemo'])>10){{mb_substr($customer['supportMemo'],0,10)}}
                            .. @else {{$customer['supportMemo']}}@endif</a>
                    </li>
                    <li><span>联系人员：</span>
                        <p>@if(!empty($contact)) {{$contact['Name']}} @endif</p></li>
                    <li><span>联系电话：</span>
                        <p>@if(!empty($contact)) {{$contact['Tel']}} @endif</p></li>
                    @if(isset($data->mobile)&& !empty($data->mobile))
                        <li><span>联系手机：</span>
                            <p>{{$data->mobile}}
                                <a title="发送短信">
                                    <i style="color: #20c582;font-size: 15px" class="fa fa-commenting-o"
                                       id="sendMsg"></i>
                                </a><a title="拨打电话"><i data-mobile="{{$contact['Mobile']}}"
                                            style="color: #20c582" class="mobile-icon" id="onCall"></i></a></p>
                        </li>
                    @elseif(!empty($contact['Mobile']) && isset($contact['Mobile']))
                        <li><span>联系手机：</span>
                            <p> {{$contact['Mobile']}}
                                <a title="发送短信">
                                    <i style="color: #20c582;font-size: 15px" class="fa fa-commenting-o"
                                       id="sendMsg"></i>
                                </a><a title="拨打电话"><i data-mobile="{{$contact['Mobile']}}"
                                            style="color: #20c582" class="mobile-icon" id="onCall"></i></a></p>
                        </li>
                    @endif
                    @if(isset($data->email) && !empty($data->email))
                        <li><span>电子邮件：</span>
                            <p style="white-space: nowrap;text-overflow: ellipsis;width: 160px;"
                               title="{{$data->email}}">
                                {{substr($contact['Email'],0,12).(strlen($contact['Email'])>12?"...":"") }}
                                <a title="发送邮件">
                                    <i style="color: #20c582;font-size: 14px" class="fa fa-envelope-o sendEmail"
                                       data-email="{{$data->email}}"></i></a></p>
                        </li>
                    @elseif(!empty($contact)&&isset($contact['Email']) &&!empty($contact['Email']) )
                        <li><span>电子邮件：</span>
                            <p style="white-space: nowrap;text-overflow: ellipsis;width: 160px;"
                               title="{{$contact['Email']}}">
                                {{substr($contact['Email'],0,12).(strlen($contact['Email'])>12?"...":"") }}
                                <a title="发送邮件">
                                    <i style="color: #20c582;font-size: 14px" class="fa fa-envelope-o sendEmail"
                                       data-email="{{$contact['Email']}}" ></i></a></p>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="client-info">
            <div class="client-info-content">
                <ul>
                    <li><span>创建人：</span>
                        <p style="white-space: nowrap;text-overflow: ellipsis;width: 160px;" title="{{$userinfo}}">
                            {{substr($userinfo,0,12).(strlen($userinfo)>12?"...":"") }}
                            @if($data->CreateUserId >500000)
                                <a title="发送邮件">
                                <i style="color: #20c582;font-size: 14px" class="fa fa-envelope-o sendEmail"
                                   data-email="{{$userinfo}}"></i></a>
                            @endif
                        </p></li>
                    <li><span>创建时间：</span>
                        <p>{{$data->Ts}}</p></li>
                </ul>
            </div>
        </div>
        <div id="similar-job" class="similar-job">
            <p class="label-title"><i class="label-icon list-icon"></i>当前客户工单Last10</p>
            <div class="similar-job-content">
                <ul>
                    @foreach ($toplist['list1'] as $row)
                        <li><a href='/wo/supportrefer/{{$row->Id}}' class="single-line-text similar-job-content-list"
                               menuname="{{$row->Id}}">{{$row->Id}} {{$row->Title}}</a></li>
                    @endforeach
                    @foreach ($toplist['list2'] as $row)
                        <li><a href='/wo/supportrefer/{{$row->Id}}' class="single-line-text similar-job-content-list"
                               menuname="{{$row->Id}}">{{$row->Id}} {{$row->Title}}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
<input/>
<div id="enlargeImage" class="hide">
    <div class="img-wrap">
        <i id="closeLargeImg" class="img-close"></i>
        <img class="large-img" src=""/>
    </div>
</div>
<!-- 全局js -->

<script src="/js/jquery.min.js?v=2.1.4"></script>
<script src="/js/jquery.lineline.js"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/layer/layer.min.js"></script>
<script src="/js/plugins/bootstrap-table/bootstrap-table.js"></script>
<!-- put your locale files after bootstrap-table.js -->
<script src="/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>

<script charset="utf-8" src="/js/plugins/kindeditor/kindeditor.js"></script>
<script charset="utf-8" src="/js/plugins/kindeditor/lang/zh_CN.js"></script>
<script>
    function openConfigDetail(id){
        $.ajax({
            type: "GET",
            dataType: 'json',
            url: '/wo/jsonConfig/' + $("#supportId").val(),
            success: function (data) {
                if(data){
                    var msg = '工单配置信息/<span>类型：'+data["supType"]+'</span><br>';
                    if(data["config"]){
                        for(var i in data["config"]){
                            msg += i+"："+data["config"][i]+"<br>";
                            /*if(isArray(data["config"][i])){
                                var subMsg =i+"："+data["config"][i];
                                for(var j in data["config"][i]){
                                    subMsg += data["config"][i][j]+",";
                                }
                                subMsg = subMsg.substr(0,subMsg.length -1);
                                msg += subMsg+'<br>';
                            }else{
                                msg += i+"："+data["config"][i]+"<br>"
                            }*/
                        }
                    }
                    layer.tips(msg,jsonConfig, {time: 10000, tips: [2, '#999999'], maxWidth: 400});
                }
            }
        });
    }

    function openIpDetail(ipDetails){
        console.log(ipDetails);
        layer.tips(ipDetails,ipAddr, {time: 10000, tips: [2, '#999999'], maxWidth: 400});
    }

    var isArray = function(obj) {
        return Object.prototype.toString.call(obj) === '[object Array]';
    }

    function closeFrame() {
        var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
        parent.layer.close(index); //再执行关闭
    }
    $(".showCusMemo").mouseover(function () {
        var memo = "{{preg_replace('/\\n/si', "", $customer['supportMemo'])}}";
        memotips = layer.tips(memo, cusMemo, {time: 0, tips: [2, '#999999'], maxWidth: 400});
    });
    $(".showCusMemo").mouseleave(function () {
        layer.close(memotips);
    });
    $(".showCusName").mouseover(function () {
        var name = "{{$customer['CusName']}}";
        nametips = layer.tips(name, cusName, {time: 0, tips: [2, '#999999'], maxWidth: 400});
    });
    $(".showCusName").mouseleave(function () {
        layer.close(nametips);
    });
    $("#eyeclose").mouseover(function () {
        var name = '此标识表示该条内容客户不可见';
        nametips = layer.tips(name, eyeclose, {time: 0, tips: [1, '#999999'], maxWidth: 400});
    });
    $("#eyeclose").mouseleave(function () {
        layer.close(nametips);
    });
    var timelimit ={{$timereply}};
    var url = "{{env("JOB_URL2")}}";
    var curl = "{{env("CALL_URL")}}";
    //拨打电话
    $("#onCall").click(function () {
        var mobile = $.trim($(this).data("mobile"));
        layer.confirm('您确定要拨打电话吗?', {icon: 3, title: '提示'}, function (index) {
            mobile = mobile.replace("+86","");
            mobile = mobile.replace("86-","");
            if(mobile.length != 11){
                layer.msg('拨打电话失败,非国内手机号暂不支持！', {
                    icon: 2,
                    time: 2000 //2秒关闭
                });
                return;
            }
            var extension = $.trim($("#extension").val());
            if (extension && mobile) {
                /*var script= document.createElement('script');
                script.type= 'text/javascript';
                script.src = curl + "/callengine/http/operation?json={'command':'dial','dest':'" + mobile + "','ext_field':'','src':'" + extension + "'}";
                $("body").append(script);*/
                $.ajax({
                    'type': 'get',
                    'dataType': 'json',
                    'url': '/wo/onCall?json='+"{'command':'dial','dest':'" + mobile + "','ext_field':'','src':'" + extension + "'}",
                    'success': function (data) {
                        console.log(data);
                        if(data.status == "OK"){
                            layer.msg('即将为您接通电话！', {
                                icon: 1,
                                time: 2000 //2秒关闭
                            });
                        }else{
                            layer.msg('接通电话失败!请重试或联系研发人员!', {
                                icon:2,
                                time: 2000 //2秒关闭
                            });
                        }
                    }
                })

            }
            else{
                layer.msg('拨打电话失败,暂无分机或电话号码无效！', {
                    icon: 2,
                    time: 2000 //2秒关闭
                });
            }
        })
    });
    $("a[name='contents']").click(function () {
        var sid = $("input[name='sid']").val();
        layer.open({
            type: 1,
            title: '工单内容',
            maxmin: false,
            skin: "layui-layer-molv",
            shade: [0.1, '#393D49'],
            area: ['80%', '80%'],
            zIndex: 10,
            content: "<div id='condet'>" + $("#contents").val() + "</div><style>.lineline {padding: 20px; text-align: left; } .lineline-numbers { width: 20px; border-right: 1px solid #ccc; padding-right: 5px; color: #777; } .lineline-lines { padding-left: 20px;white-space: -moz-pre; white-space: -pre; white-space: -o-pre; word-wrap: break-word; } .lineline-code { font-family: 'Consolas'; font-size: 12px; line-height: 18px; }\<\/style><script>jQuery(document).ready(function($) {$('#condet').lineLine();});\<\/script>"
        });
        $(".layui-layer-content img,#recordCommuListCont img:not(.left-portrait)").each(function () {
            var src = $(this).attr("src");
            if ((src.substr(0, 7).toLowerCase() != "http://") && (src.substr(0, 10).toLowerCase() != "data:image")) {
                url = typeof(url) != 'undefined' ? url : '';
                $(this).attr("src", url + src);
            }
            var objimg = $(this),
                    h = objimg.height(),
                    w = objimg.width();
            if (!(h > 0 && w > 0)) {
                objimg.remove();
            }
        });
    })

    if (document.getElementById('recordCommuList').scrollHeight > 110) {
        $("div[name='contents']").removeClass('hidden');
        document.getElementById('recordCommuList').style.overflow = 'hidden'
    }
    $("#chooseOneType").change(function () {
        var onchange = $.trim(this.value);
        $.ajax({
            'type': 'get',
            'url': '/wo/getdatacenter/' + onchange,
            'dataType': 'json',
            'success': function (data) {
                var html = '';
                $.each(data, function (commentIndex, comment) {
                    html += "<option value=" + comment.DataCenterName + ">" + comment.DataCenterName + "</option>";
                })
                $("#chooseDataCenter").html(html);
            }
        })
    });

    function getIdSelections() {
        return $.map($table.bootstrapTable('getSelections'), function (row) {
            return row.id
        });
    }
    var relateIssueTable = $('#relateIssueTable'),
            relateChangeTable = $('#relateChangeTable'),
            selections = [];

    function initTable() {//加载数据
        relateIssueTable.bootstrapTable({
            pageSize: 20,
            striped: true,
            columns: [
                [{
                    field: 'state',
                    checkbox: true,
                    align: 'left',
                    valign: 'middle',
                    width: '3%'
                }, {
                    title: '问题单号',
                    valign: 'middle',
                    field: 'issueNo',
                    align: 'left',
                    width: '15%',
                    formatter: function (value, row, index) {

                        var s = '<a class="J_menuItem" title="问题单号' + row.issueNo + '" href="/issue/details/' + row.Id + '">' + row.issueNo + '</a>';
                        return s;
                    }
                }, {
                    title: '问题主题',
                    valign: 'middle',
                    field: 'issueTitle',
                    align: 'left',
                    width: '15%',
                    formatter: function (value, row, index) {
                        var s = substringLen(row.issueTitle);
                        return s;
                    },
                    events: 'operateEvents'
                }, {
                    field: 'issueCategory',
                    title: '问题分类',
                    valign: 'middle',
                    align: 'left',
                    width: '10%',
                    formatter: function (value, row, index) {
                        return row.issueCategory;
                    }
                }, {
                    field: 'issuePriority',
                    title: '优先级',
                    valign: 'middle',
                    align: 'left',
                    width: '8%',
                    formatter: function (value, row, index) {
                        return row.issuePriority;
                    },
                    events: 'operateEvents'
                }, {
                    field: 'Ts',
                    title: '问题申请人<br/>申请时间',
                    valign: 'middle',
                    align: 'left',
                    width: '18%',
                    formatter: function (value, row, index) {
                        var s = row.issueSubmitUserId + '<br/>' + row.issueSubmitTs;
                        return s;
                    }
                }]
            ],
            queryParamsType: "undefined",
            queryParams: function queryParams(params) {
                return {
                    pageSize: params.pageSize,
                    pageNumber: params.pageNumber,
                    'timeOutIds': $('#timeOutIds').val(),
                    'cusType': $('#cusType').val(),
                    'Status': $('#Status').val()
                }
            }
        });
        relateChangeTable.bootstrapTable({
            pageSize: 20,
            striped: true,
            columns: [
                [{
                    field: 'state',
                    checkbox: true,
                    align: 'left',
                    valign: 'middle',
                    width: '3%'
                }, {
                    title: 'RFC编号',
                    valign: 'middle',
                    field: 'RFCNO',
                    align: 'left',
                    width: '15%',
                    formatter: function (value, row, index) {
                        var s = '<a class="J_menuItem" title="变更RFC编号' + row.RFCNO + '" href="/change/details/' + row.Id + '">' + row.RFCNO + '</a>';
                        return s;
                    }
                }, {
                    title: '变更标题',
                    valign: 'middle',
                    field: 'changeTitle',
                    align: 'left',
                    width: '20%',
                    formatter: function (value, row, index) {
                        var s = substringLen(row.changeTitle);

                        return s;
                    },
                    events: 'operateEvents'
                }, {
                    field: 'issuePriority',
                    title: '变更对象',
                    valign: 'middle',
                    align: 'left',
                    width: '15%',
                    formatter: function (value, row, index) {
                        return row.changeObject;
                    },
                    events: 'operateEvents'
                }, {
                    field: 'issueCategory',
                    title: '变更类别',
                    valign: 'middle',
                    align: 'left',
                    width: '10%',
                    formatter: function (value, row, index) {
                        return row.changeCategory;
                    }
                }, {
                    field: 'Ts',
                    title: '变更申请人<br/>申请时间',
                    valign: 'middle',
                    align: 'left',
                    width: '18%',
                    formatter: function (value, row, index) {
                        var s = row.applyUserId + '<br/>' + row.Ts;
                        return s;
                    }
                }]
            ],
            queryParamsType: "undefined",
            queryParams: function queryParams(params) {
                return {
                    pageSize: params.pageSize,
                    pageNumber: params.pageNumber,
                }
            }
        });
    }
    initTable();
    function responseHandler(res) {
        $.each(res.rows, function (i, row) {
            row.state = $.inArray(row.id, selections) !== -1;
        });
        return res;
    }
    /*
     * 批量取消关联
     */

    layer.config({
        extend: 'extend/layer.ext.js'
    });
    $("#changeClose").click(function () {
        layer.confirm('确定要取消关联吗?', {
                    title: "提示",
                    btn: ['确定', '取消']
                },
                function () {
                    var selected = $('#relateChangeTable').bootstrapTable('getSelections');
                    if (selected.length < 1) {
                        layer.msg('请选择要取消关联的变更！', {icon: 2});
                        return false;
                    }
                    layer.prompt({
                        title: '请输入取消关联理由',
                        formType: 2 //prompt风格，支持0-2
                    }, function (text) {
                        $.ajax({
                            type: "POST",
                            data: {'Ids': selected, 'reason': text, 'supportId': $("#supportId").val()},
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                            },
                            url: "/correlation/closeSupportToChange",
                            success: function (data) {
                                if (data.status == 'success') {
                                    layer.msg('批量取消关联成功！', {icon: 1});
                                    $('#relateChangeTable').bootstrapTable('refresh');
                                }
                            }
                        })
                    });
                })
    });
    $("#issueClose").click(function () {
        layer.confirm('确定要取消关联吗?', {
                    title: "提示",
                    btn: ['确定', '取消']
                },
                function () {
                    var selected = $('#relateIssueTable').bootstrapTable('getSelections');
                    if (selected.length < 1) {
                        layer.msg('请选择要取消关联的变更！', {icon: 2});
                        return false;
                    }
                    layer.prompt({
                        title: '请输入取消关联理由',
                        formType: 2 //prompt风格，支持0-2
                    }, function (text) {
                        $.ajax({
                            type: "POST",
                            data: {'Ids': selected, 'reason': text, 'supportId': $("#supportId").val()},
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                            },
                            url: "/correlation/closeSupportToIssue",
                            success: function (data) {
                                if (data.status == 'success') {
                                    layer.msg('批量取消关联成功！', {icon: 1});
                                    $('#relateIssueTable').bootstrapTable('refresh');
                                }
                            }
                        })
                    });
                })
    });
    function substringLen(text, length) {
        var length = arguments[1] ? arguments[1] : 16;
        suffix = "";
        if (text.length > length) {
            suffix = "..";
        }
        return text.substr(0, length) + suffix;
    }
    function subName(text, length) {
        var length = arguments[1] ? arguments[1] : 16;
        suffix = "";
        if (text.length > length) {
            suffix = "..";
        }
        return text.substr(0, length) + suffix;
    }
</script>
<script type="text/javascript" src="/js/job_detail.js?66"></script>
</body>
</html>
