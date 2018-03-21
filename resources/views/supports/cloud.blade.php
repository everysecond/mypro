<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="UTF-8">
  <link href="/css/style.css?v=4.1.0" rel="stylesheet">
  <link rel="stylesheet" href="/css/job_detail.css" />
  <link rel="stylesheet" href="/css/event_charge.css" />
  <style>
      .icloud{
         padding:3px 0;
         margin: 3px 0;   
         vertical-align: middle;
      }
  </style>
  </head>
  <body>
          <div class="job-record module-style" style="width: 100%">
      <div class="label-title"  style="width: 100%">
        <span id="recordCommu" class="title_active">云计算<span class="label_line"></span></span>
        <span id="recordChange">CDN</span>
        <span id="recordRemark">Monitor</span>
      </div>
      <div id="recordCommuList" class="record-list">
         <table class="event-table" style="width: 100%">
                <thead>
                    <tr>
                        <th align="left" style="width:30%">Login</th>
                        <th align="left" style="width:20%">账号类型</th>
                        <th align="left" style="width:20%">联系人</th>
                        <th style="width:30%"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $row)
                    <tr>
                        <td>{{$row->LoginId}}</td>
                        <td>@if(isset($row->AccountType) && $row->AccountType == "admin")
                                主账号
                            @else
                                子账号
                            @endif</td>
                        <td>{{$row->Name}}</td>
                       {{-- <td>{{$row->project}}</td>--}}
                        <td><a href="{{$domain}}/crm/cloud_login.html?accountId={{$row->accId}}" target="_blank">
                                <img class="icloud" src="/img/icon_cloud.svg"/>
                            </a></td>
                        {{--<td><a href="{{$domain}}/cloud/support/cus/{{$row->cusinfid}}/{{$row->userid}}" target="_blank">
                                <img class="icloud" src="/img/icon_cloud.svg"/>
                            </a></td>--}}
                    </tr>
                    @endforeach{{--http://www.51idc.com/crm/cloud_login.html?accountId=4651--}}
                </tbody>
            </table>
      </div>
        
        <div id="recordChangeList" class="record-list hide">
            <table class="event-table" style="width: 100%">
                <thead>
                    <tr>
                        <th align="left" style="width:30%">Login</th>
                        <th align="left" style="width:20%">项目名称</th>
                        <th align="left" style="width:20%">项目类型</th>
                        <th style="width:30%"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @foreach($list as $row)
                        <tr>
                            <td>{{$row->LoginId}}</td>
                            <td>{{$row->name}}</td>
                            <td>{{$row->project}}</td>
                            <td>
                                <a href="{{$domain}}/cdn/support/cus/{{$row->cusinfid}}/{{$row->userid}}" target="_blank">
                                <img class="icloud" src="/img/icon_cloud.svg"/>
                                </a></td>
                        </tr>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
        
      <div id="recordRemarkList" class="record-list hide">
          <table class="event-table" style="width: 100%">
                <thead>
                    <tr>
                        <th align="left" style="width:30%">Login</th>
                        <th align="left" style="width:20%">项目名称</th>
                        <th align="left" style="width:20%">项目类型</th>
                        <th style="width:30%"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                       @foreach($list as $row)
                        <tr>
                            <td>{{$row->LoginId}}</td>
                            <td>{{$row->name}}</td>
                            <td>{{$row->project}}</td>
                            <td>
                                <a href="{{$domain}}/monitor/support/cus/{{$row->cusinfid}}/{{$row->userid}}" target="_blank">
                                    <img class="icloud" src="/img/icon_cloud.svg"/>
                                </a></td>
                        </tr>
                        @endforeach
                    </tr>
                </tbody>
            </table>
      </div>
    </div>
  </body>
  <script src="/js/jquery.min.js?v=2.1.4"></script>
  <script>
      // 切换标题标签
        function switchTitleLabel(currentElement, callback) {
          $(".job-record .label-title .title_active").children(".label_line").remove();
          $(".job-record .label-title .title_active").removeClass("title_active");
          currentElement.addClass("title_active").append('<span class="label_line"></span>');
          if (callback && {}.toString.call(callback) === "[object Function]") {
            callback();
          }
        };
        // 记录列表 标题切换
      $("#recordCommu").click(function () {
        switchTitleLabel($(this));
        $(".job-record .record-list").addClass("hide");
        $("#recordCommuList").removeClass("hide");
      });

      $("#recordQuestion").click(function () {
        switchTitleLabel($(this));
        $(".job-record .record-list").addClass("hide");
        $("#recordQuestionList").removeClass("hide");
      });

      $("#recordChange").click(function () {
        switchTitleLabel($(this));
        $(".job-record .record-list").addClass("hide");
        $("#recordChangeList").removeClass("hide");
      });

      $("#recordRemark").click(function () {
        switchTitleLabel($(this));
        $(".job-record .record-list").addClass("hide");
        $("#recordRemarkList").removeClass("hide");
      });
  </script>
</html>