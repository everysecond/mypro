<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="_token" content="{{ csrf_token() }}"/>
    <title>安畅网络 工单系统——统计工单列表</title>

    <!--[if lt IE 9]>
    <meta http-equiv="refresh" content="0;ie.html"/>
    <![endif]-->

    <link rel="shortcut icon" href="favicon.ico">
    <link href="/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/css/font-awesome.min.css?v=4.4.0" rel="stylesheet">
    <link href="/css/animate.css" rel="stylesheet">
    <link href="/css/style.css?v=4.1.0" rel="stylesheet">
    <link rel="stylesheet" href="/css/plugins/bootstrap-table/bootstrap-table.css">
    <link rel="stylesheet" href="/css/job_list.css">
    <link rel="stylesheet" href="/css/handover.css">
    <style>
        .hiddenTable {
            display: none;
        }

        .tab_bq {
            width: 700px;
            position: absolute;
            left: 0;
            top: 34px;
            z-index: 100;
            border: 1px solid #e6e4e4;
            background: #fff;
            padding: 10px;
        }

        .tab_bq input {
            vertical-align: middle;
            width: 15px;
            margin: -3px;
            line-height: normal;
            box-sizing: border-box;
            padding: 0;
            height: 20px;
            border: 0;
        }
        .tab_bq label {
            font-weight: 100;
            font-size: 10px;
            margin-bottom: 0;
        }
        #r2HQ22{
            margin-right: 9px;
        }
    </style>
</head>
<body>
<div class=" wrapper-content">
    <div class="row">
        <div class="col-sm-12">
            <input type="hidden" value="mysupport" id="mysupport">
            {{--条件存储--}}
            <input type="hidden" value="" id="lestThanOneVal">
            <input type="hidden" value="" id="moreThanOneVal">
            <input type="hidden" value="" id="moreThanTwoVal">
            <input type="hidden" value="" id="timeOutIds">
            <input type="hidden" value="" id="allTimeOutIds">
            {{--跳转临时存值--}}
            <input type="hidden" value="" id="monthClassInficationOne">
            <input type="hidden" value="" id="monthStatus">
            <input type="hidden" value="" id="month">
            <input type="hidden" value="" id="source">
            <input type="hidden" value="" id="sources">
            <input type="hidden" value="" id="sourceStatus">
            <input type="hidden" value="" id="userId">

            <div style="margin-top:5px;">
                <div class="tab-content">
                    <div class="hand-title" style="height: auto">
                        <div style="margin-left: 20px">
                            <div class="fa fa-book"></div>
                            统计工单
                        </div>
                        <span aria-expanded="true" data-toggle="tab" href="#faForm" id="faQuery" class="title_active">快速查询<span
                                    id="span1" class="label_line"></span></span>
                        <span aria-expanded="false" data-toggle="tab" href="#adForm" id="adQuery" class="">高级查询<span
                                    id="span2" class="label_line hidden"></span></span>
                    </div>
                    <div class="">
                        <div class="full-height-scroll">
                            <div class="ibox-content"
                                 style="padding: 1px 2px 2px 10px;border-top-width: 0px;margin-top: 5px">
                                <div class="row m-b-sm m-t-sm">
                                    <div class="col-md-12">
                                        <form id="faForm" class="form-inline" style="padding: 4px">
                                            创建时间:
                                            <input type="text" class="form-control" style="width: 12%" placeholder=""
                                                   id="faStartTime">
                                            至
                                            <input type="text" class="form-control" style="width: 12%" placeholder=""
                                                   id="faEndTime">
                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            <div class="input-group" style="min-width:450px;">
                                                <input type="text" class="form-control" name="searchInfo"
                                                       id="searchInfo" value=""
                                                       placeholder="请输入客户名称/工单标题/工单编号/IP地址">
                                                <span class="input-group-btn">
                                                 <a class="btn btn-info" style="background-color:#19b492" id="searchAll"
                                                    onclick="doNewSearch(this,'')">
                                                     <span class="glyphicon glyphicon-search">查询</span>
                                                 </a>
                                                </span>
                                            </div>
                                            @if($sources!='dash')
                                            <div class="input-group">
                                                <span class="fa fa-file-text-o"></span>
                                                <a onclick="doExport(this,'true')">筛选导出Excel</a>
                                                <input type="checkbox" id="noEmail" onchange="doNewSearch(this,'')"
                                                       style="margin: -2px 0 0 5px;">邮件告警
                                            </div>
                                            @endif
                                        </form>
                                        <form id="adForm" class="form-inline hiddenTable" style="padding: 4px">
                                            创建时间:
                                            <input type="text" class="form-control" style="width: 12%" placeholder=""
                                                   id="startTime">
                                            至
                                            <input type="text" class="form-control" style="width: 12%" placeholder=""
                                                   id="endTime">
                                            &nbsp;
                                            <span>工单负责人群组：</span>
                                            <div class="input-group" style="display: inline-block">
                                                <select class="form-control" onchange="doNewSearch(this,'')"
                                                        id="SuppOptGroup" name="SuppOptGroup">
                                                    <option value="">全部</option>
                                                    @foreach($chargeList as $charge)
                                                        <option value="{{$charge->Code}}">{{$charge->Means}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            &nbsp;
                                            <span>工单优先级：</span>
                                            <select class="form-control" onchange="doNewSearch(this,'')"
                                                    id="priority" name="priority">
                                                <option value="">全部</option>
                                                @foreach($priorityList as $priority)
                                                    <option value="{{$priority}}">{{$priority}}</option>
                                                @endforeach
                                            </select>
                                            &nbsp;
                                            <span>二级部门：</span>
                                            <div style="display: inline-block;" id="secondDeptArea">
                                                <input type="text" class="form-control" id="secondDept" readonly="">
                                                <input style="display: none" type="text" id="secondDeptCode">
                                                <div class="tab_bq undis" id="check1" dir="ltr" style="display: none;">
                                                    @foreach($secondDeptList as $dep)
                                                        <span id="r2HQ22" class="z-checkbox">
                                                            <input value="{{$dep->Code}}" type="checkbox" class="cked">
                                                            <label class="z-checkbox-cnt">
                                                                {{$dep->Means}}</label></span>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="input-group">
                                            <span class="input-group-btn">
                                                <a class="btn btn-info" style="background-color:#19b492" id="searchAll"
                                                   onclick="doNewSearch(this,'')">
                                                    <span class="glyphicon glyphicon-search">搜索</span>
                                                </a>
                                            </span>
                                            </div>
                                        </form>
                                        <table>
                                            <tr>
                                                <td width="4px"></td>
                                                <td width="60px">筛选查询</td>

                                                <td width="5px"></td>
                                                <td>
                                                    <select class="form-control" onchange="doNewSearch(this,'')"
                                                            id="Status" name="Status">
                                                        <option value="">工单状态</option>
                                                        @foreach($statusList as $k=>$status)
                                                            <option value="{{$k}}">{{$status}}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td width="5px"></td>
                                                <td>
                                                    <select class="form-control" onchange="doNewSearch(this,'')"
                                                            id="dataCenter" name="dataCenter">
                                                        <option value="">数据中心</option>
                                                        @foreach($dataCenterList as $dataCenter)
                                                            <option value="{{$dataCenter->DataCenterName}}">{{$dataCenter->DataCenterName}}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td width="5px"></td>
                                                <td>
                                                    <select class="form-control" onchange="doNewSearch(this,'')"
                                                            id="ChargeUserId" name="ChargeUserId">
                                                        <option value="">负责人</option>
                                                        @foreach($chargeUserList as $k=>$p)
                                                            <option value="{{$p->Id}}">{{$p->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td width="5px"></td>
                                                <td>
                                                    <select class="form-control" onchange="doNewSearch(this,'')"
                                                            id="AsUserId" name="AsUserId">
                                                        <option value="">跟踪人</option>
                                                        @foreach($AsUserList as $k=>$p)
                                                            <option value="{{$p['Id']}}">{{$p['Name']}}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td width="5px"></td>
                                                <td>
                                                    <select class="form-control" onchange="doNewSearch(this,'')"
                                                            id="replyTime" name="replyTime">
                                                        <option value="">响应时长</option>
                                                        @foreach($responseTimeList as $k=>$item)
                                                            <option value="{{$k}}">{{$item}}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td width="5px"></td>
                                                <td>
                                                    <select class="form-control" onchange="doNewSearch(this,'')"
                                                            id="handleTime" name="handleTime">
                                                        <option value="">处理时长</option>
                                                        <option value="overTimeIds">超时工单</option>
                                                        <option value="onTimeIds">未超时工单</option>
                                                    </select>
                                                </td>
                                                <td width="5px"></td>
                                                <td>
                                                    <select class="form-control" onchange="doNewSearch(this,'')"
                                                            id="supportSource" name="supportSource">
                                                        <option value="">工单来源</option>
                                                        @foreach($supSourceList as $k)
                                                            <option value="{{$k->Code}}">{{$k->Means}}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td width="5px"></td>
                                                <td>
                                                    <select class="form-control" onchange="doNewSearch(this,'')"
                                                            id="classifyOne" name="classifyOne">
                                                        <option value="">一级分类</option>
                                                        @foreach(ThirdCallHelper::getDictArray("工单业务类型","serviceModel") as $item)
                                                            <option value="{{$item->Code}}">
                                                                {{$item->Means}}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td width="5px"></td>
                                                <td>
                                                    <select class="form-control" onchange="doNewSearch(this,'')"
                                                            id="classifyTwo" name="classifyTwo">
                                                        <option value="">二级分类</option>
                                                        @foreach($classifyTwo as $item)
                                                            <option value="{{$item->Code}}">{{$item->Means}}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td width="5px"></td>
                                                <td>
                                                    <select class="form-control" onchange="doNewSearch(this,'')"
                                                            id="classifyThree" name="classifyThree">
                                                        <option value="">三级分类</option>
                                                        @foreach($classifyThree as $k)
                                                            <option value="{{$k->Code}}">{{$k->Means}}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td width="5px"></td>
                                                <td>
                                                    <select class="form-control" onchange="doNewSearch(this,'')"
                                                            id="Evaluation" name="Evaluation">
                                                        <option value="">服务评价</option>
                                                        <option value="notEvaluate">未评价</option>
                                                        @foreach($evaluationList as $evaluation)
                                                            <option value="{{$evaluation->Code}}">{{$evaluation->Means}}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td height="10px"></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive" style="background-color: white">
                                <table id="faTable" class="table-no-bordered table-fixpadding"
                                       style="text-align: center;color:#6b7d86" bgcolor="#FFFFFF"
                                       cellpadding="0"
                                       cellspacing="0" width="100%"
                                       data-pagination="true"
                                       data-show-export="true"
                                       data-page-size="10"
                                       data-id-field="Id"
                                       data-pagination-detail-h-align="right"
                                       data-page-list="[10, 25, 50, 100, ALL]"
                                       data-show-footer="false"
                                       data-side-pagination="server"
                                       data-url="/support/getStatisticList"
                                       data-response-handler="responseHandler">
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
<div id="test"></div>

<!-- 全局js -->
<script src="/js/jquery.min.js?v=2.1.4"></script>
<script src="/js/jquery.slimscroll.min.js"></script>
<script src="/js/plugins/layer/layer.min.js"></script>
<script src="/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script type="text/javascript" src="/render/hplus/js/contabs.js"></script>
<script type="text/javascript" src="/js/common.js"></script>
<script src="/js/plugins/pace/pace.min.js"></script>
<script src="/js/job_list.js"></script>
<script src="/js/plugins/layer/laydate/laydate.js"></script>
<script src="/js/plugins/bootstrap-table/bootstrap-table.js"></script>
<!-- put your locale files after bootstrap-table.js -->
<script src="/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>


<script>
    $('#searchInfo').bind('keypress',function(event){
        var searchAll = document.getElementById("searchAll");
        if(event.keyCode == "13")
        {
            searchAll.click();
        }
    });
    var _flag = false; // 全局变量，用于记住鼠标是否在DIV上
    $("#secondDeptArea").mouseover(function(){
        _flag = true;
    });
    $("#secondDeptArea").mouseout(function(){
        _flag = false;
    });
    document.body.onclick = function (){
        if(!_flag){
            $("#check1").css("display","none");
        }
    };

    $("#secondDept").click(function () {
        $("#check1").css("display","block");
    });

    $(".cked").change(function(){
        var cked = "",ckedcode = "";
        $(".cked:checked").each(function(){
            var la = $.trim($(this).next("label").html());
            cked += la+";";
            ckedcode += ckedcode != ""? ","+$(this).val():$(this).val();
        });
        $("#secondDeptCode").val(ckedcode);
        $("#secondDept").val(cked);
    });

    var url = "{{env('JOB_URL')}}";
    var $faTable = $('#faTable'),
            $remove = $('.remove'),
            selections = [];

    function initTable() {
        $faTable.bootstrapTable({
            pageSize: 20,
            striped: true,
            columns: [
                [{
                    title: '工单编号<br/>数据中心<br/>负责人',
                    valign: 'middle',
                    align: 'left',
                    field: 'Id',
                    width: '10%',
                    formatter: function (value, row, index) {
                        var v = row.dataCenter != null ? row.dataCenter : '';
                        var s = row.Id + '<br/>' + v + '<br/>' + row.ChargeUserId;
                        var t = row.ChargeUserTwoId ? '/' + row.ChargeUserTwoId : '';
                        return s + t;
                    }
                }, {
                    title: '&nbsp;&nbsp;&nbsp;&nbsp;工单标题',
                    valign: 'middle',
                    field: 'Title',
                    width: '20%',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var titleStyle = "";
                        if (row.identity.isVIP) {
                            titleStyle = "color:red";
                        }
                        var timeoutIcon = "&nbsp;&nbsp;&nbsp;&nbsp;";
                        timeoutRel = checkTimeOutType(row);
                        if (timeoutRel != "") {
                            timeoutIcon = timeoutRel;
                        }
                        var menuName = row.CusName;
                        if (null != menuName && menuName.length > 8) {
                            menuName = menuName.substring(0, 8) + "...";
                        }
                        var title = row.Title.length > 10 ? row.Title.substr(0, 10) + '...' : row.Title;
                        var s = timeoutIcon + '<a class="showTitleTips J_menuItem" style="' + titleStyle + '"  title="' + menuName + '"  id="title_' + row.Id + '"    ' +
                                'href="/wo/supportrefer/' + row.Id + '">' + title + '</a>';

                        return s;
                    },
                    events: 'operateEvents'
                }, {
                    field: 'ClassInfication',
                    width: '10%',
                    title: '工单状态<br/>工单分类<br/>指派人',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var s = statusFormatter(row) + '<br/>' + row.ClassInficationOne + '<a href="" title=已处理时长:' + timeStamp(row.overTime) + "&nbsp;预计处理时长:" + timeStamp(row.predictTs * 60) + '><i class="msg-icon"></i></a>' + '<br/>' + row.AsuserId;
                        return s;
                    }
                }, {
                    field: 'CusName',
                    width: '15%',
                    title: '客户名称',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var s = '<a class="showCusTips"  id="cusInfo_' + row.Id + '"    ' +
                                'href="{{env('JOB_URL', 'http://www.51idc.cn')}}/crm/user/finance/customerDetailNewdetail.html?cusinfid=' + row.CustomerInfoId + '" target="_blank">' + row.CusName + '</a>';
                        var identity = s + formatterIdentity(row);
                        if (row.agentName) {
                            identity = identity + '<br><h5>代理商：' + row.agentName + '</h5>';
                        }
                        return identity;
                    },
                    events: 'operateEvents'
                }, {
                    field: 'devIPAddr',//'EquipmentId',
                    width: '15%',
                    title: 'IP地址<br/>关联设备',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var IPAddr = row.devIPAddr != null ? row.devIPAddr : '';
                        var EquipmentId = row.EquipmentId != null ? row.EquipmentId : '';
                        var s = IPAddr + '<br/>' + EquipmentId;
                        return s;
                    }
                },   {
                    field: 'ServiceModel',
                    width: '5%',
                    title: '一级分类',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var s = row.ServiceModel;
                        return s;
                    }
                },{
                    field: 'devIPAddr',
                    width: '10%',
                    title: '二级分类',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var s = row.ClassInfication;
                        return s;
                    }
                },{
                    field: 'Ts',
                    width: '15%',
                    title: '创建时间<br/>更新时间<br/>最后更新人',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var upTs = row.UpTs != null ? row.UpTs : '';
                        var s = row.Ts + '<br/>' + upTs + '<br/>' + row.OperationId;
                        return s;
                    }
                }, {
                    field: 'Evaluation',//'ChargeUserId',
                    width: '10%',
                    title: '服务评价<br/>跟踪人',
                    valign: 'middle',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var s = '<b style="color:blue">' + row.Evaluation + '</b>' + '<br/>' + row.AsuserId;
                        return s;
                    }
                }
                ]
            ],
            queryParamsType: "undefined",
            queryParams: function queryParams(params) {
                return {
                    pageSize: params.pageSize,
                    pageNumber: params.pageNumber,
                    'user': $("#mySupport").val(),
                    'timeOutIds': $('#timeOutIds').val(),
                    'SuppOptGroup': $('#SuppOptGroup').val(),
                    'priority': $('#priority').val(),
                    'cusType': $('#cusType').val(),
                    'Status': $('#Status').val(),
                    'dataCenter': $('#dataCenter').val(),
                    'replyTime': $('#replyTime').val(),
                    'Evaluation': $('#Evaluation').val(),
                    'searchInfo': $('#searchInfo').val(),
                    //跳转url
                    'monthClassInficationOne': $('#monthClassInficationOne').val(),
                    'monthStatus': $('#monthStatus').val(),
                    'monthType': $('#month').val(),
                    'source': $('#source').val(),
                    'sourceStatus': $('#sourceStatus').val(),
                    'userId': $('#userId').val(),
                    'faStartTime': $('#faStartTime').val(),
                    'faEndTime': $('#faEndTime').val(),
                    'classifyOne': $('#classifyOne').val(),
                    'classifyTwo': $('#classifyTwo').val(),
                    'classifyThree': $('#classifyThree').val(),
                    'ChargeUserId': $('#ChargeUserId').val(),
                    'AsUserId': $('#AsUserId').val(),
                    'handleTime': $('#handleTime').val(),
                    'sources': $('#sources').val(),
                    'supportSource': $('#supportSource').val(),
                    'noEmail': $("#noEmail").is(':checked')?"yes":"no",
                }
            }
        });
        var custips;
        window.operateEvents = {
            'mouseenter .showCusTips': function (e, value, row, index) {
                $.ajax({
                    type: "GET",
                    async: false,
                    data: {'cusId': row.CustomerInfoId},
                    url: "/customer/cusDetail/" + row.CustomerInfoId,
                    success: function (data) {
                        if (data['cusInfo']) {
                            var contacts = '';
                            for (var contact in data['contacts']) {
                                var type = data['contacts'][contact].ConType;
                                type == '' ? type = '其它类型联系人' : type = type;
                                contacts = type + ':<br/>' +
                                        '<span style="text-indent: 2em;line-height: 7px">姓名：' + data['contacts'][contact].Name + '</span>' + '<br>' +
                                        '<span style="text-indent: 2em;line-height: 7px">联系电话：' + data['contacts'][contact].Mobile + '</span><br>';
                            }
                            custips = layer.tips('<div style="word-wrap: break-word; color: #ffffff">客户名称：' + data['cusInfo'].CusName + '<br/>' +
                                    '客户经理：' + data['cusInfo'].SellName + '<br/>' +
                                    '客户类型：' + data['cusInfo'].CusTypeName + '<br/>' +
                                    '联系电话：' + data['cusInfo'].Tel + '<br/>' +
                                    '邮件：' + data['cusInfo'].EMAIL + '<br/>' +
                                    '地址：' + data['cusInfo'].Address + '<br/><hr>' + contacts + '</div>'
                                    , '#cusInfo_' + row.Id, {time: 0, tips: [1, '#999999'], maxWidth: 400});
                        }
                    }
                });
            },
            'mouseover .showTitleTips': function (e, value, row, index) {
                var content = row.Body,
                        lastOperation = row.OperationId != '无' ? '于' + row.lastOperationTs + '进行了：<br/>' + row.lastOperation : '';
                content = content.replace(new RegExp("<img", "gm"), "<img style='width:100%'");
                custips = layer.tips('<div style="word-wrap: break-word;color: #ffffff">工单标题：<br/>' + row.Title + '<br/>' +
                        '工单内容：<br/><div class="supportBody" style="color: #ffffff;max-height: 150px;overflow: hidden;">' + content + '</div><br/>' +
                        '数据中心：' + row.dataCenter + '<br/><hr>' +
                        '最后一次操作：' + row.OperationId + lastOperation + '</div>'
                        , this, {time: 0, tips: [1, '#999999'], maxWidth: 400});
                $('.supportBody img').each(function () {
                    var src = $(this).attr("src");
                    if ((src.substr(0, 7).toLowerCase() != "http://") && (src.substr(0, 10).toLowerCase() != "data:image")) {
                        $(this).attr("src", url + src);
                    }
                });
            },
            'mouseover .icon-client-state.icon-manage': function (e, value, row, index) {
                if (row.identity.MANdetails) {
                    custips = layer.tips(row.identity.MANdetails, this, {
                        time: 0,
                        tips: [1, '#999999'],
                        maxWidth: 400
                    });
                }
            },
            'mouseleave .icon-client-state.icon-manage': function (e, value, row, index) {
                if (row.identity.MANdetails) {
                    layer.close(custips);
                }
            },
            'mouseover .icon-client-state.icon-vip': function (e, value, row, index) {
                if (row.identity.isVIP) {
                    custips = layer.tips('VIP重要客户', this, {
                        time: 0,
                        tips: [1, '#999999'],
                        maxWidth: 400
                    });
                }
            },
            'mouseleave .icon-client-state.icon-vip': function (e, value, row, index) {
                if (row.identity.isVIP) {
                    layer.close(custips);
                }
            },
            'mouseover .icon-client-state.icon-A': function (e, value, row, index) {
                if (row.identity.isAType) {
                    custips = layer.tips('A类客户', this, {
                        time: 0,
                        tips: [1, '#999999'],
                        maxWidth: 400
                    });
                }
            },
            'mouseleave .icon-client-state.icon-A': function (e, value, row, index) {
                if (row.identity.isAType) {
                    layer.close(custips);
                }
            },
            'mouseover .icon-client-state.icon-three': function (e, value, row, index) {
                if (row.identity.DSFdetails) {
                    custips = layer.tips(row.identity.DSFdetails, this, {
                        time: 0,
                        tips: [1, '#999999'],
                        maxWidth: 400
                    });
                }
            },
            'mouseleave .icon-client-state.icon-three': function (e, value, row, index) {
                if (row.identity.DSFdetails) {
                    layer.close(custips);
                }
            },
            'mouseleave .showCusTips': function (e, value, row, index) {
                layer.close(custips);
            },
            'mouseout .showTitleTips': function (e, value, row, index) {
                layer.close(custips);
            }
        }

    }
    function getIdSelections() {
        return $.map($table.bootstrapTable('getSelections'), function (row) {
            return row.id
        });
    }

    function responseHandler(res) {
        $.each(res.rows, function (i, row) {
            row.state = $.inArray(row.id, selections) !== -1;
        });
        return res;
    }


    function getHeight() {
        return $(window).height() - $('h1').outerHeight(true);
    }

    $(function () {
        $(".table-responsive").css({"overflow": "inherit"});
        showToolTip($("._handle_tip"));
        showTableToolTip("._jobState");
    });
    function doNewSearch(data, values) {//检索筛选
        if (data.name == 'mysupport') {
            $("#mySupport").val("mySupport");
        }
        if (data.name == 'lestThanOne' || data.name == 'moreThanOne' || data.name == 'moreThanTwo') {
            timeOutIds = $("#" + values).val();
            $("#timeOutIds").val(timeOutIds);
            getOverTime();
        }
        $('#faTable').bootstrapTable('refresh', {
            query: {
                'user': $("#mySupport").val(),
                'timeOutIds': $("#timeOutIds").val(),
                'SuppOptGroup': $('#SuppOptGroup').val(),
                'priority': $('#priority').val(),
                'cusType': $('#cusType').val(),
                'Status': $('#Status').val(),
                'dataCenter': $('#dataCenter').val(),
                'replyTime': $('#replyTime').val(),
                'Evaluation': $('#Evaluation').val(),
                'searchInfo': $('#searchInfo').val(),
                'faStartTime': $('#faStartTime').val(),
                'faEndTime': $('#faEndTime').val(),
                'startTime': $('#startTime').val(),
                'endTime': $('#endTime').val(),
                'secondDeptCode': $("#secondDeptCode").val(),
                'classifyOne': $('#classifyOne').val(),
                'classifyTwo': $('#classifyTwo').val(),
                'classifyThree': $('#classifyThree').val(),
                'ChargeUserId': $('#ChargeUserId').val(),
                'handleTime': $('#handleTime').val(),
                'AsUserId': $('#AsUserId').val(),
                'supportSource': $('#supportSource').val(),
                'noEmail': $("#noEmail").is(':checked')?"yes":"no",
                'pageNumber': 1
            }
        });
    }
    /**
     * 获取超时的数据
     */
    function getOverTime() {
        $.ajax({//查询待处理工单数量
            type: "GET",
            async: false,
            url: "/support/getOverTimeNum",
            success: function (data) {
                if (data) {//待处理工单大于0则播放音乐
                    $("#lestThanOne").html(data.oneHour.count);
                    $("#moreThanOne").html(data.twoHour.count);
                    $("#moreThanTwo").html(data.overTwo.count);

                    $("#lestThanOneVal").val(data.oneHour.ids);
                    $("#moreThanOneVal").val(data.twoHour.ids);
                    $("#moreThanTwoVal").val(data.overTwo.ids);
                    //全部ids
                    $("#allTimeOutIds").val(data.allIds);
                }
            }
        });
        //根据超时的数据遍历将表单变红
    }
    getOverTime();
    //获取queryString参数
    function checkRequest() {
        var url = location.search;
        //判断是否有参数
        if (url.indexOf("?") != -1) {
            var ClassInficationOne;
            var Status;
            if (getQueryString("classInficationOne") != null) {
                ClassInficationOne = getQueryString("classInficationOne");
                $("#monthClassInficationOne").val(ClassInficationOne);
            }
            if (getQueryString("Status") != null) {
                Status = getQueryString("Status");
                $("#monthStatus").val(Status);
            }
            month = getQueryString("month");
            if (month == "now") {
                $("#month").val("now");
            }
            if (month == "prev") {
                $("#month").val("prev");
            }
            if (getQueryString("source") == null) {
                $("#source").val("month");
            } else {
                $("#source").val(getQueryString("source"));
            }
            if (getQueryString("sourceStatus") != null) {
                Status = getQueryString("sourceStatus");
                $("#sourceStatus").val(Status);
            }
            if (getQueryString("userId") != null) {
                userId = getQueryString("userId");
                $("#userId").val(userId);
            }
        }
    }
    checkRequest();
    function doExport(data, values) {//检索筛选
        if (data.name == 'mysupport') {
            $("#mySupport").val("mySupport");
        }
        var params = {
            'user': $("#mySupport").val(),
            'timeOutIds': $("#timeOutIds").val(),
            'SuppOptGroup': $('#SuppOptGroup').val(),
            'priority': $('#priority').val(),
            'cusType': $('#cusType').val(),
            'Status': $('#Status').val(),
            'dataCenter': $('#dataCenter').val(),
            'replyTime': $('#replyTime').val(),
            'Evaluation': $('#Evaluation').val(),
            'searchInfo': $('#searchInfo').val(),
            'faStartTime': $('#faStartTime').val(),
            'faEndTime': $('#faEndTime').val(),
            'startTime': $('#startTime').val(),
            'endTime': $('#endTime').val(),
            'classifyOne': $('#classifyOne').val(),
            'classifyTwo': $('#classifyTwo').val(),
            'classifyThree': $('#classifyThree').val(),
            'ChargeUserId': $('#ChargeUserId').val(),
            'AsUserId': $('#AsUserId').val(),
            'sources':$('#sources').val(),
            'handleTime':$('#handleTime').val(),
            'supportSource':$('#supportSource').val(),
            'noEmail': $("#noEmail").is(':checked')?"yes":"no",
            'pageNumber': 1
        };
        queryString = $.param(params);
        console.log($.param(params));
        window.location.href = "/support/exportStaList?" + queryString;
    }
    initTable();
    $('#faQuery').click(function () {
        $('#faForm').removeClass('hiddenTable');
        $('#adQuery').removeClass('title_active');
        $('#faQuery').addClass('title_active');
        $('#adForm').addClass('hiddenTable', 'title_active');
        $('#span1').removeClass('hidden');
        $('#span2').addClass('hidden');
    });
    $('#adQuery').click(function () {
        $('#adForm').removeClass('hiddenTable', 'title_active');
        $('#faForm').addClass('hiddenTable', 'title_active');
        $('#adQuery').addClass('title_active');
        $('#faQuery').removeClass('title_active');
        $('#span2').removeClass('hidden');
        $('#span1').addClass('hidden');
    });
    var faStartTime = {
        elem: '#faStartTime',
        format: 'YYYY-MM-DD hh:mm',
        istime: true,
        choose: function (datas) {
            faEndTime.min = datas;
            faEndTime.start = datas
        }
    }
    var faEndTime = {
        elem: '#faEndTime',
        format: 'YYYY-MM-DD hh:mm',
        istime: true,
        choose: function (datas) {
            faStartTime.max = datas;
        }
    }

    var startTime = {
        elem: '#startTime',
        format: 'YYYY-MM-DD hh:mm',
        istime: true,
        choose: function (datas) {
            endTime.min = datas;
            endTime.start = datas
        }
    }
    var endTime = {
        elem: '#endTime',
        format: 'YYYY-MM-DD hh:mm',
        istime: true,
        choose: function (datas) {
            startTime.max = datas;
        }
    }
    laydate(startTime);
    laydate(endTime);
    laydate(faStartTime);
    laydate(faEndTime);
</script>
</body>

</html>