<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="_token" content="{{ csrf_token() }}"/>
    <title>安畅网络 工单系统——全部工单列表</title>

    <!--[if lt IE 9]>
    <meta http-equiv="refresh" content="0;ie.html"/>
    <![endif]-->

    <link rel="shortcut icon" href="favicon.ico">
    <link href="/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/css/font-awesome.min.css?v=4.4.0" rel="stylesheet">
    {{--<link href="/css/animate.css" rel="stylesheet">--}}
    <link href="/css/style.css?v=4.1.0" rel="stylesheet">
    <link rel="stylesheet" href="/css/plugins/bootstrap-table/bootstrap-table.css">
    <link rel="stylesheet" href="/css/job_list.css">
    <style>
        .table-fixpadding th, .table-fixpadding td {
            padding: 0 8px !important;
        }
    </style>
</head>
<body>
<div class=" wrapper-content" style="background-color:whitesmoke">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="job-list-top">
                    <button class="submit-btn" id="subsupport"> + 提交工单</button>
                    <button class="submit-btn J_menuItem" href="/support/todoList" title="待办工单"> 我的工单</button>
                    <button class="submit-btn J_menuItem" href="/support/operateList" title="操作工单"> 操作工单</button>
                    <button class="submit-btn J_menuItem" href="/support/collectionList?ismine=yes" title="我的收藏"> 我的收藏</button>
                    <input type="hidden" value="mysupport" id="mysupport">
                    <div class="job-handle-wrap">
                        <div class="job-handle">
                            <div class="_handle_tip handle-tips">
                                <a href="javascript:void(0);" name="lestThanOne"
                                   onclick="doNewSearch(this,'lestThanOneVal')"><i class="icon-time"></i></a><span
                                        id="lestThanOne" class="tips-huang"></span>
                                <div class="tool-tips" style="display: none;"><p class="tool-title">处理超时且不足1小时</p><i
                                            class="tool-arrow"></i></div>
                            </div>
                            <div class="_handle_tip handle-tips">
                                <a href="javascript:void(0);" name="moreThanOne"
                                   onclick="doNewSearch(this,'moreThanOneVal')"><i class="icon-time"></i></a><span
                                        id="moreThanOne" class="tips-yellow"></span>
                                <div class="tool-tips" style="display: none;"><p class="tool-title">处理超时1小时且不足2小时</p><i
                                            class="tool-arrow"></i></div>
                            </div>
                            <div class="_handle_tip handle-tips">
                                <a href="javascript:void(0);" name="moreThanTwo"
                                   onclick="doNewSearch(this,'moreThanTwoVal')"><i class="icon-time"></i></a><span
                                        id="moreThanTwo" class="tips-red"></span>
                                <div class="tool-tips" style="display: none;"><p class="tool-title" style="right: 15%;">
                                        处理超时2小时</p><i class="tool-arrow"></i></div>
                            </div>
                            <div class="job-handle-text">
                                <span class="handle-text-orther">&lt;1h</span>
                                <span class="handle-text-center">1h-2h</span>
                                <span class="handle-text-orther">&gt;2h</span>
                            </div>
                        </div>
                    </div>
                    {{--条件存储--}}
                    <input type="hidden" value="" id="lestThanOneVal">
                    <input type="hidden" value="" id="moreThanOneVal">
                    <input type="hidden" value="" id="moreThanTwoVal">
                    <input type="hidden" value="" id="timeOutIds">
                    <input type="hidden" value="" id="allTimeOutIds">
                </div>
            </div>
            <div class="ibox-content" style="padding: 1px 2px 2px 10px;border-top-width: 0px;margin-top: 5px">
                <div class="row m-b-sm m-t-sm">
                    <div class="col-md-11">
                        <form id="searchForm" action="" onsubmit="return false">
                            <div class="input-group">
                                <table>
                                    <tr>
                                        <td><label>筛选查询</label></td>
                                        <td width="30px"></td>
                                        <td>
                                            <select class="form-control" onchange="doNewSearch(this,'')"
                                                    id="SuppOptGroup" name="SuppOptGroup">
                                                <option value="">工单负责人群组</option>
                                                @foreach($chargeList as $charge)
                                                    <option value="{{$charge->Code}}">{{$charge->Means}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td width="10px"></td>
                                        <td>
                                            <select class="form-control" onchange="doNewSearch(this,'')"
                                                    id="priority" name="priority">
                                                <option value="">工单优先级</option>
                                                @foreach($priorityList as $priority)
                                                    <option value="{{$priority}}">{{$priority}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td width="10px"></td>
                                        <td>
                                            <select class="form-control" onchange="doNewSearch(this,'')"
                                                    id="cusType" name="cusType">
                                                @foreach($customerTypeList as $k=>$priority)
                                                    <option value="{{$k}}">{{$priority}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td width="10px"></td>
                                        <td>
                                            <select class="form-control" onchange="doNewSearch(this,'')"
                                                    id="Status" name="Status">
                                                <option value="">工单状态</option>
                                                @foreach($statusList as $k=>$status)
                                                    <option value="{{$k}}">{{$status}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td width="10px"></td>
                                        <td>
                                            <select class="form-control" onchange="doNewSearch(this,'')"
                                                    id="dataCenter" name="dataCenter">
                                                <option value="">数据中心</option>
                                                @foreach($dataCenterList as $dataCenter)
                                                    <option value="{{$dataCenter->DataCenterName}}">{{$dataCenter->DataCenterName}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td width="10px"></td>
                                        <td>
                                            <select class="form-control" onchange="doNewSearch(this,'')"
                                                    id="replyTime" name="replyTime">
                                                <option value="">响应时长</option>
                                                @foreach($responseTimeList as $k=>$item)
                                                    <option value="{{$k}}">{{$item}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td width="10px"></td>
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
                                        <td height="15px"></td>
                                    </tr>
                                    <tr>
                                        <td><label>快速查询</label></td>
                                        <td width="30px"></td>
                                        <td colspan="14">
                                            <div class="input-group" style="min-width:100%">
                                                <input type="text" class="form-control" name="searchInfo"
                                                       id="searchInfo" value="{{isset($search)?$search:''}}"
                                                       placeholder="请输入客户名称/工单标题/工单编号/IP地址/工单备注(关键字)/关联设备/关联变更编号/关联问题编号">
                                                    <span class="input-group-btn">
                            <a class="btn btn-info" style="background-color:#19b492" id="searchAll"
                               onclick="doNewSearch(this,'')">
                                <span class="glyphicon glyphicon-search">搜索</span>
                            </a>
                                        <button type="button" style="margin-left:5px" class="btn btn-default"
                                                onclick="doExport(this,'true')">导出结果为Excel
                                        </button>
                        </span>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div style="margin-top:5px;">
                <div class="tab-content">
                    <div id="tab-1" class="tab-pane active">
                        <div class="full-height-scroll">
                            <div class="table-responsive" style="background-color: white">
                                <table id="supportTable" class="table-no-bordered table-fixpadding"
                                       style="text-align: center;color:#6b7d86" bgcolor="#FFFFFF"
                                       cellpadding="0"
                                       cellspacing="0" width="100%"
                                       data-pagination="true"
                                       data-show-export="true"
                                       data-page-size="10"
                                       data-id-field="Id"
                                       data-page-list="[10, 25, 50, 100, ALL]"
                                       data-show-footer="false"
                                       data-side-pagination="server"
                                       data-url="/support/getAllList"
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
<script src="/js/job_list.js"></script>
<script type="text/javascript" src="/js/common.js"></script>
<script type="text/javascript" src="/js/job_list.js"></script>
<script src="/js/plugins/bootstrap-table/bootstrap-table.js"></script>
<!-- put your locale files after bootstrap-table.js -->
<script src="/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>

<script>
    $(function () {
        $(".table-responsive").css({"overflow": "inherit"});
        //工单tips提示
        showToolTip($("._handle_tip"));
        showTableToolTip("._jobState");
    });
    $('#searchInfo').bind('keypress',function(event){
        var searchAll = document.getElementById("searchAll");
        if(event.keyCode == "13")
        {
            searchAll.click();
        }
    });
    var url = "{{env('JOB_URL2')}}";
    layer.config({
        extend: 'extend/layer.ext.js'
    });
    $("#subsupport").click(function () {
        layer.open({
            type: 2,
            title: '工单管理>提交工单 （<span style="color:#ff253d">*表示必填项</span>）',
            area: ['800px', '640px'],
            shade: 0.2,
            content: ['/support/create', 'no'],
            end: function () {
                $('#supportTable').bootstrapTable('refresh');
            }
        });
    });
    var $supportTable = $('#supportTable'),
            $remove = $('.remove'),
            selections = [];

    function initTable() {
        $supportTable.bootstrapTable({
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
                        var dim = '';
                        if (row.rid == null || row.isValidate == 1) {
                            dim = '<a href="javascript:void(0);" onclick=addCollection("' + row.Id + '")' +
                                    ' class="showCollectNote" title="收藏"><i class="fa fa-heart-o"></i></a> ';
                        } else {
                            dim = '<a href="javascript:void(0);" onclick=delCollection("' + row.Id + '")' +
                                    ' class="showCollectNote" title="取消收藏"><i class="fa fa-heart"></i></a> ';
                        }
                        return dim + s;
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
                        var cusName = row.CusName.length > 10 ? row.CusName.substr(0, 10) + '...' : row.CusName;
                        var s = '<a class="showCusTips"  id="cusInfo_' + row.Id + '"    ' +
                                'href="{{env('JOB_URL', 'http://www.51idc.cn')}}/crm/user/finance/customerDetailNewtail.html?cusinfid=' + row.CustomerInfoId + '" target="_blank">' + cusName + '</a>';
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
                        var IPAddr = row.devIPAddr != null ? subByText(row.devIPAddr, 20) : '';
                        var EquipmentId = row.EquipmentId != null ? row.EquipmentId : '';
                        var s = IPAddr + '<br/>' + EquipmentId;
                        return s;
                    }
                }, {
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
                    'searchInfo': $('#searchInfo').val()
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
                lastOperation = lastOperation.replace(new RegExp("<img", "gm"), "<img style='width:100%;max-height:50px'");
                custips = layer.tips('<div style="word-wrap: break-word;color: #ffffff">工单标题：<br/>' + row.Title + '<br/>' +
                        '工单内容：[点击标题查看全部详情]<br/><div class="supportBody" style="color: #ffffff;max-height: 150px;overflow: hidden;">' + content + '</div><br/>' +
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
            },
            'mouseenter .showCollectNote': function (e, value, row, index) {
                var self= this;
                $.ajax({
                    type: "get",
                    async: false,
                    url: "/support/getCollectionNote/" + row.Id,
                    success: function (data) {
                        if (data!="") {
                            var a = data.inValidate == 0?"分类："+data.type+"<br/>收藏原因:":"取消收藏原因:";
                            custips = layer.tips('<div style="word-wrap: break-word; color: #ffffff">'+
                                    a + data.note  + '</div>',self, {time: 0, tips: [1, '#999999'], maxWidth: 400});
                        }
                    }
                });
            },
            'mouseleave .showCollectNote': function (e, value, row, index) {
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
    function doNewSearch(data, values) {//检索筛选
        if (data.name == 'mysupport') {
            $("#mySupport").val("mySupport");
        }
        if (data.name == 'lestThanOne' || data.name == 'moreThanOne' || data.name == 'moreThanTwo') {
            timeOutIds = $("#" + values).val();
            //没数据则直接返回
            if (timeOutIds == "") {
                return
            }
            $("#timeOutIds").val(timeOutIds);
            getOverTime();
            $("#searchForm")[0].reset();
        }
        var isExport = false;
        if (values != "") {
            isExport = values;
        }
        $('#supportTable').bootstrapTable('refresh', {
            query: {
                'user': $("#mySupport").val(),
                'timeOutIds': $("#timeOutIds").val(),
                'SuppOptGroup': $('#SuppOptGroup').val(),
                'isExport': isExport,
                'priority': $('#priority').val(),
                'cusType': $('#cusType').val(),
                'Status': $('#Status').val(),
                'dataCenter': $('#dataCenter').val(),
                'replyTime': $('#replyTime').val(),
                'Evaluation': $('#Evaluation').val(),
                'searchInfo': $('#searchInfo').val(),
                'pageNumber': 1
            }
        });
    }
    function doExport(data, values) {//检索筛选
        if (data.name == 'mysupport') {
            $("#mySupport").val("mySupport");
        }
        if (data.name == 'lestThanOne' || data.name == 'moreThanOne' || data.name == 'moreThanTwo') {
            timeOutIds = $("#" + values).val();
            //没数据则直接返回
            if (timeOutIds == "") {
                return
            }
            $("#timeOutIds").val(timeOutIds);
            getOverTime();
            $("#searchForm")[0].reset();
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
            'pageNumber': 1
        };
        queryString = $.param(params);
        window.location.href = "/support/exportAllList?" + queryString;
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
    initTable();
    function addCollection(supportId) {
        layer.confirm('确定收藏该工单吗?', {icon: 3, title: '提示？'}, function () {
            layer.open({
                type: 2,
                title: '收藏工单',
                area: ['400px', '250px'],
                shade: 0.2,
                content: ['/support/editReason?sid='+supportId, 'no']
            });
        });
    }
    function delCollection(supportId) {
        layer.confirm('确定取消收藏该工单吗?', {icon: 3, title: '提示？'}, function () {
            layer.prompt({title: '请输入取消收藏原因', formType: 2}, function (text) {
                $.ajax({//查询待处理工单数量
                    type: "POST",
                    async: false,
                    data: {'supportId': supportId, 'notes': text},
                    url: "/support/delCollection",
                    headers: {'X-CSRF-Token': $('meta[name=_token]').attr('content')},
                    dataType: 'json',
                    success: function (res) {
                        if (res.status == 'successful') {
                            layer.msg(res.msg, {icon: 6});
                        } else {
                            layer.msg(res.msg, {icon: 2});
                        }
                        $('#supportTable').bootstrapTable('refresh');
                    }
                });
            });
        });
        //根据超时的数据遍历将表单变红
    }
</script>
</body>
<script src="/js/plugins/layer/laydate/laydate.js"></script>

</html>