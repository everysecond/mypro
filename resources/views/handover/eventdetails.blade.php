<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>变更详情</title>
    <link href="/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/css/style.css?v=4.1.0" rel="stylesheet">
    <link href="/css/font-awesome.css?v=4.4.0" rel="stylesheet">
    <link rel="stylesheet" href="/css/change_detail.css"/>
    <link rel="stylesheet" href="/js/plugins/kindeditor/themes/default/default.css"/>
    <link rel="stylesheet" href="/js/plugins/kindeditor/plugins/code/prettify.css"/>
    <link rel="stylesheet" href="/css/plugins/bootstrap-table/bootstrap-table.css">
    <link rel="stylesheet" href="/css/change_detail.css"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .info-content li span {
            font-weight: bold;
            font-size: 12px;
            line-height: 35px;
        }

        .info-content li {
            display: inline-block;
            font-size: 12px;
            line-height: 35px;
        }

        .info-content li {
            width: 33%;
        }
    </style>
</head>
<body style="background-color:  #F5F5F5">
<div class="job-detail clearfix">
    <div class="job-detail-left" style="background-color: white">
        <form>
            <p class="info-title" style="height: 32px;margin: 12px 0 0 40px" id="detailsArea">
                <span style="color: red">{{$event->id}}</span>事件详情
            </p>
            <div class="job-record  module-style" style="margin-left:2%;margin-top: 0;border-top: 1px solid #E4DFDF;">
                <input type="hidden" id="eventId" name="eventId" value="{{$event->id}}"/>
                <div class="info-content">
                    <p style="color:#26C4AA;font-weight: bold">基础信息</p>
                    <ul>
                        <li><span>交接单编号：{{$event->handoverId}}</span></li>
                        <li><span>交接单负责人：{{ThirdCallHelper::getStuffName($handover->chargerId)}}</span></li>
                        <li><span>提醒方式：{{$remindType}}</span></li>
                        <li><label>工单编号：</label>
                            @if($event->supportId != 0)<a class="J_menuItem" title="工单{{$event->supportId}}详情"
                                                          href="/wo/supportrefer/{{$event->supportId}}">{{$event->supportId}}</a>
                            @else 无
                            @endif
                            <p></p>
                        </li>
                        <li><label class="inline">事件类型：</label>
                            {{ThirdCallHelper::getDictMeans('工单类型','WorksheetTypeOne',$event->type)}}</li>
                        <li><label class="inline">优先级：</label><span>{{$priority}}</span></li>
                        <li><label>客户名称：</label>
                            @if($event->cusId !=0 )
                                <a title="客户详情"
                                   href="{{env('JOB_URL', 'http://www.51idc.cn')}}/crm/user/finance/customerDetailNew.html?cusinfid={{$event->cusId}}"
                                   target="_blank">{{ThirdCallHelper::getCusName($event->cusId)}}</a>
                            @else 无
                            @endif
                            <p></p>
                        </li>
                        <li><label class="inline">连续提醒：</label>
                            @if($type == "不需要"){{$type}}@else{{$type}}/次@endif</li>
                        <li>
                            <label class="inline">预约提醒时间：</label>
                            @if($event->remindTs == '0000-00-00 00:00:00') 无
                            @else{{$event->remindTs}}
                            @endif
                        </li>
                        <li><span>事件负责部门：{{ThirdCallHelper::getDepartMeans($event->groupId)}}</span></li>
                        <li><span>事件负责人：{{ThirdCallHelper::getStuffName($event->chargerId)}}</span></li>
                        <li><span>事件抄送人：@foreach($csIdsArray as $item){{ThirdCallHelper::getStuffName($item)}} @endforeach</span></li>
                        <li><span>提交人：{{ThirdCallHelper::getStuffName($event->submitterId)}}</span></li>
                        <li>
                            <label> </label>
                            <label class="inline">提交时间：</label>{{$event->ts}}</li>
                        <li style="width: 90%"><label class="inline">事件说明：</label>
                            {{$event->notes}}</li>
                    </ul>
                    <p style="color:#26C4AA;font-weight: bold">处理信息</p>
                    <ul>
                        <li><span>处理完成人：{{ThirdCallHelper::getStuffName($event->solvedId)}}</span></li>
                        <li><span>处理完成时间：{{$event->solvedTs}}</span></li>
                        <li style="width: 90%"><span>结果反馈：</span><span class="job-record">{!!$event->feedback!!}</span></li>
                        <li><span>更新人：{{ThirdCallHelper::getStuffName($event->upUserId)}}</span></li>
                        <li><span>更新时间：{{$event->upTs}}</span></li>
                    </ul>
                </div>
            </div>
        </form>
    </div>
    <div id="enlargeImage" class="hide">
        <div class="img-wrap">
            <i id="closeLargeImg" class="img-close"></i>
            <img class="large-img" src=""/>
        </div>
    </div>
</div>
<script src="/js/jquery.min.js?v=2.1.4"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/layer/layer.min.js"></script>
<script src="/js/plugins/bootstrap-table/bootstrap-table.js"></script>
<!-- put your locale files after bootstrap-table.js -->
<script src="/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>

<script charset="utf-8" src="/js/plugins/kindeditor/kindeditor-min.js"></script>
<script charset="utf-8" src="/js/plugins/kindeditor/lang/zh_CN.js"></script>
<script src="/js/plugins/layer/laydate/laydate.js"></script>
<script type="text/javascript" src="/js/change_detail.js"></script>
</body>
</html>
