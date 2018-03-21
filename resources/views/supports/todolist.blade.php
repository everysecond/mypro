<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="_token" content="{{ csrf_token() }}"/>
    <title>安畅网络 工单系统——待办工单列表</title>

    <!--[if lt IE 9]>
    <meta http-equiv="refresh" content="0;ie.html"/>
    <![endif]-->

    <link rel="shortcut icon" href="favicon.ico">
    <link href="/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/css/font-awesome.min.css?v=4.4.0" rel="stylesheet">
    <link href="/css/style.css?v=4.1.0" rel="stylesheet">
    <link rel="stylesheet" href="/css/plugins/bootstrap-table/bootstrap-table.css">
    <link rel="stylesheet" href="/css/job_list.css">
    <style>
        .hiddenTable {
            display: none;
        }

        tr {
            height: 55px;
        }

        .table-fixpadding th, .table-fixpadding td {
            padding: 0 8px !important;
        }
    </style>
</head>
<body class="gray-bg">
<div class=" wrapper-content" style="background-color: whitesmoke">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="job-list-top">
                    <button class="submit-btn" id="subsupport"> + 提交工单</button>
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
                    <input type="hidden" value="" id="cusType">
                    <input type="hidden" value="" id="Status">
                </div>
            </div>
            <div style="margin-top:5px;">
                <div class="tab-content" style="padding: 5px 5px 5px 5px;background-color: white">
                    <ul class="nav nav-tabs" id="nav-tabs">
                        <span class="pull-right small text-muted"></span>
                        <li class="active" style="background-color: white">
                            <a aria-expanded="true" data-toggle="tab" href="#tab-1" id="ordinarySupport">
                                <i class="fa fa-support"></i> 待办工单</a></li>
                        <li class="" style="background-color: white">
                            <a aria-expanded="false" data-toggle="tab" href="#tab-2" style="height: 40px;"
                               id="emailSupport">
                                <i class="fa fa-envelope"></i>
                                邮件请求 <i id="email_count" class="">
                                </i></a></li>
                    </ul>
                    <div class="" id="supT1">
                        <table id="supportTable" class="table-no-bordered table-fixpadding active"
                               style="text-align: center;color:#6b7d86;" bgcolor="#FFFFFF"
                               cellpadding="0"
                               cellspacing="0" width="100%"
                               data-pagination="true"
                               data-page-size="10"
                               data-id-field="Id"
                               data-page-list="[10, 25, 50, 100, ALL]"
                               data-show-footer="false"
                               data-side-pagination="server"
                               data-url="/support/getTodoList"
                               data-response-handler="responseHandler">
                        </table>
                        <Div id="playMusic">
                        </Div>
                    </div>
                    <div class="hiddenTable" id="supT2">
                        <div>
                            <table id="emailTable" class="table-no-bordered table-fixpadding active"
                                   style="text-align: center;color:#6b7d86;" bgcolor="#FFFFFF"
                                   cellpadding="0"
                                   cellspacing="0" width="100%"
                                   data-pagination="true"
                                   data-show-export="true"
                                   data-page-size="10"
                                   data-id-field="Id"
                                   data-page-list="[10, 25, 50, 100, ALL]"
                                   data-show-footer="false"
                                   data-pagination-detail-h-align="right"
                                   data-side-pagination="server"
                                   data-url="/support/getTodoList?email=true"
                                   data-response-handler="responseHandler">
                            </table>
                            <div id="bottomOption">
                                <button id="batchClose" class="option-btn close-btn">批量闭单</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- 全局js -->
<script src="/js/jquery.min.js?v=2.1.4"></script>
<script src="/js/jquery.slimscroll.min.js"></script>
<script src="/js/plugins/layer/layer.min.js"></script>
<script src="/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script type="text/javascript" src="/render/hplus/js/contabs.js"></script>
<script type="text/javascript" src="/js/common.js"></script>
<script type="text/javascript" src="/js/job_list.js"></script>
<script src="/js/plugins/bootstrap-table/bootstrap-table.js"></script>
<!-- put your locale files after bootstrap-table.js -->
<script src="/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>

<script>
    var url = "{{env('JOB_URL')}}";
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
    /*
     * 批量关闭工单
     */
    $("#batchClose").click(function () {
        layer.confirm('确定要批量关闭工单吗?', {
                    title: "提示",
                    btn: ['确定', '取消']
                },
                function () {
                    var selected = $('#emailTable').bootstrapTable('getSelections');
                    if (selected.length < 1) {
                        layer.msg('请选择要操作的工单！', {icon: 2});
                        return false;
                    }
                    for (var key in selected) {
                        if (selected[key].Status == 'Todo' || selected[key].Status == 'Closed') {
                            layer.msg('当前选项中包含有待处理或已关闭的工单，请重新选择！', {icon: 2});
                            return false;
                        }
                    }
                    $.ajax({
                        type: "POST",
                        data: {'supIds': selected},
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                        },
                        url: "/support/batchCloseMailSupport",
                        success: function (data) {
                            if (data.status == 'success') {
                                layer.msg('批量关闭工单成功！', {icon: 1});
                                $('#emailTable').bootstrapTable('refresh');
//                                window.location.reload();
                            }
                        }
                    })
                })
    });

    var $supportTable = $('#supportTable'),
            $emailTable = $('#emailTable'),
            selections = [];

    function initTable() {//加载数据
        $supportTable.bootstrapTable({
            pageSize: 20,
            columns: [
                [{
                    title: '工单编号',
                    valign: 'middle',
                    field: 'Id',
                    width: '10%',
                    align: 'left',
                    formatter: function (value, row, index) {

                        var s = row.Id;
                        return s;
                    }
                }, {
                    title: '&nbsp;&nbsp;&nbsp;&nbsp;工单标题',
                    valign: 'middle',
                    width: '20%',
                    field: 'Title',
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
                        var demo = '<a href="javascript:void(0);" id="title_' + row.Id + '" width="12" style="margin-left: 5px;height: 12px"></a>';
                        var title = row.Title.length > 10 ? row.Title.substr(0, 10) + '...' : row.Title;
                        var s = timeoutIcon + '<a class="showTitleTips J_menuItem" style="' + titleStyle + '" title="' + menuName + '"' +
                                'href="/wo/supportrefer/' + row.Id + '">' + title + '</a>';
                        var dim = '';
                        if (row.rid == null || row.isValidate == 1) {
                            dim = '<a href="javascript:void(0);" class="showCollectNote"' +
                                    ' onclick=addCollection("' + row.Id + '") title="收藏"><i class="fa fa-heart-o"></i></a> ';
                        } else {
                            dim = '<a href="javascript:void(0);" class="showCollectNote"' +
                                    ' onclick=delCollection("' + row.Id + '") title="取消收藏"><i class="fa fa-heart"></i></a> ';
                        }
                        var blank = '';
                        if(row.Title.length < 6)
                            blank = '&nbsp;&nbsp;&nbsp;&nbsp;'
                        return dim + s + blank +demo;
                    },
                    events: 'operateEvents'
                }, {
                    field: 'CusName',
                    title: '<div id="todo-client-list" class="select-wrap"><span class="current-title"><span class="current-select">所有客户</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list">@foreach($customerList as $k=>$status) <li class="select-list-item" value="{{$k}}">{{$status}}</li>@endforeach</ul></div>',
                    valign: 'middle',
                    width: '20%',
                    align: 'left',
                    id: 'client-list',
                    class: 'min-w75',
                    formatter: function (value, row, index) {
                        var cusName = row.CusName.length > 10 ? row.CusName.substr(0, 10) + '...' : row.CusName;
                        var s = '<a class="showCusTips"  id="cusInfo_' + row.Id + '"    ' +
                                'href="{{env('JOB_URL', 'http://www.51idc.cn')}}/crm/user/finance/customerDetailNewdetail.html?cusinfid=' + row.CustomerInfoId + '" target="_blank">' + cusName + '</a>';
                        var identity = s + formatterIdentity(row);
                        if (row.agentName) {
                            identity = identity + '<br><h5>代理商：' + row.agentName + '</h5>';
                        }
                        return identity;
                    },
                    events: 'operateEvents'
                }, {
                    field: 'ClassInfication',
                    title: '<div id="todo-status-list" class="select-wrap"><span class="current-title"><span class="current-select">工单状态</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list"><li class="select-list-item" value="">全部状态</li>@foreach($statusList as $k=>$status) <li class="select-list-item" value="{{$k}}">{{$status}}</li>@endforeach</ul></div>',
                    valign: 'middle',
                    align: 'left',
                    width: '10%',
                    formatter: function (value, row, index) {
                        return statusFormatter(row);
                    }
                }, {
                    field: 'devIPAddr',//'EquipmentId',
                    title: '数据中心<br/>工单分类',
                    valign: 'middle',
                    align: 'left',
                    width: '15%',
                    formatter: function (value, row, index) {
                        var v = row.dataCenter != null ? row.dataCenter : '';
                        var s = v + '<br/>' + row.ClassInficationOne;
                        return s;
                    },
//                    cellStyle: function (value, row, index, field) {
//                        return checkTimeOutStyle(value, row, index, field);
//                    }
                }, {
                    field: 'Ts',
                    title: '负责人<br/>最后更新人',
                    valign: 'middle',
                    width: '10%',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var t = (row.ChargeUserTwoId && '无' != row.ChargeUserTwoId) ? '/' + row.ChargeUserTwoId : '';
                        var s = row.ChargeUserId + t + '<br/>' + row.OperationId;
                        return s;
                    }
                }, {
                    field: 'Evaluation',//'ChargeUserId',
                    title: '工单跟踪人<br/>已处理时长',
                    valign: 'middle',
                    width: '15%',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var v = row.overTime != null ? timeStamp(row.overTime) : '';
                        var s = row.AsuserId + '<br/>' + v;
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
                    'cusType': $('#cusType').val(),
                    'Status': $('#Status').val(),
                }
            }
        });
        $emailTable.bootstrapTable({
            pageSize: 20,
            striped: true,
            columns: [
                [{
                    field: 'state',
                    checkbox: true,
                    valign: 'middle',
                    align: 'middle',
                    width: '5%',
                }, {
                    title: '工单编号',
                    valign: 'middle',
                    field: 'Id',
                    height: 100,
                    align: 'left',
                    width: '10%',
                    formatter: function (value, row, index) {

                        var s = row.Id;
                        return s;
                    }
                }, {
                    title: '&nbsp;&nbsp;&nbsp;&nbsp;工单标题',
                    valign: 'middle',
                    field: 'Title',
                    align: 'left',
                    width: '20%',
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
                        var title = row.Title.length > 10 ? row.Title.substr(0, 10) + '...' : row.Title;

                        var s = timeoutIcon + '<a class="showTitleTips J_menuItem" style="' + titleStyle + '"  menuName="' + row.Id + '"  id="title_' + row.Id + '"    ' +
                                'href="/wo/supportrefer/' + row.Id + '">' + title + '</a>';

                        return s;
                    },
                    events: 'operateEvents'
                }, {
                    field: 'CusName',
                    title: '客户名称',
                    valign: 'middle',
                    align: 'left',
                    width: '15%',
                    formatter: function (value, row, index) {
                        var cusName = row.CusName.length > 10 ? row.CusName.substr(0, 10) + '...' : row.CusName;
                        var s = '<a class="showCusTips"  id="cusInfo_' + row.Id + '"    ' +
                                'href="{{env('JOB_URL', 'http://www.51idc.com')}}/crm/user/finance/customerDetailNewdetail.html?cusinfid=' + row.CustomerInfoId + '" target="_blank">' + cusName + '</a>';
                        var identity = s + formatterIdentity(row);
                        return identity;
                    },
                    events: 'operateEvents'
                }, {
                    field: 'ClassInfication',
                    title: '<div id="mail-todo-status-list" class="select-wrap"><span class="current-title"><span class="current-select">工单状态</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list"><li class="select-list-item" value="">全部状态</li>@foreach($statusList as $k=>$status) <li class="select-list-item" value="{{$k}}">{{$status}}</li>@endforeach</ul></div>',
                    valign: 'middle',
                    align: 'left',
                    width: '10%',
                    formatter: function (value, row, index) {
                        return statusFormatter(row);
                    }
                }, {
                    field: 'devIPAddr',//'EquipmentId',
                    title: '数据中心<br/>工单分类',
                    valign: 'middle',
                    align: 'left',
                    width: '15%',
                    formatter: function (value, row, index) {
                        var v = row.dataCenter != null ? row.dataCenter : '';
                        var s = v + '<br/>' + row.ClassInficationOne;
                        return s;
                    }
                }, {
                    field: 'Ts',
                    title: '负责人<br/>最后更新人',
                    valign: 'middle',
                    align: 'left',
                    width: '10%',
                    formatter: function (value, row, index) {
                        var t = (row.ChargeUserTwoId && '无' != row.ChargeUserTwoId) ? '/' + row.ChargeUserTwoId : '';
                        var s = row.ChargeUserId + t + '<br/>' + row.OperationId;
                        return s;
                    }
                }, {
                    field: 'Evaluation',//'ChargeUserId',
                    title: '工单跟踪人<br/>已处理时长',
                    valign: 'middle',
                    align: 'left',
                    width: '15%',
                    formatter: function (value, row, index) {
                        var v = row.processTs != null ? timeStamp(row.processTs) : '';
                        var s = row.AsuserId + '<br/>' + v;
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
                    'Status': $('#Status').val(),
                    'user': $('#mysupport').val()
                }
            }
        });
        var custips;
        //bootstrap监听事件
        window.operateEvents = {
            'mouseenter .showCusTips': function (e, value, row, index) {
                $.ajax({
                    type: "get",
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
                lastOperation = lastOperation.replace(new RegExp("<img", "gm"), "<img style='width:80%;height:80px'");
                custips = layer.tips('<div style="word-wrap: break-word;color: #ffffff">工单标题：<br/>' + row.Title + '<br/>' +
                        '工单内容：[点击标题查看全部详情]<br/><div class="supportBody" style="color: #ffffff;max-height: 150px;overflow: hidden;">' + content + '</div><br/>' +
                        '数据中心：' + row.dataCenter + '<br/><hr>' +
                        '最后一次操作：' + row.OperationId + lastOperation + '</div>'
                        , '#title_' + row.Id, {time: 0, tips: [1, '#999999'], maxWidth: 400});
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
        };

        $emailTable.on('load-success.bs.table', function (e, data) {
            if (data.todoCount) {
                $('#email_count').addClass("mailRequest");
                $('#email_count').html(data.todoCount);
            }
        });
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


    function doNewSearch(data, values) {//普通工单检索
        if (data.name == 'lestThanOne' || data.name == 'moreThanOne' || data.name == 'moreThanTwo') {
            timeOutIds = $("#" + values).val();
            //没数据则直接返回
            if (timeOutIds == "") {
                return
            }
            $("#timeOutIds").val(timeOutIds);
            getOverTime();
            //重置下拉框
            pullDownChoice("todo-status-list", function (param) {
                $("#Status").val("");
            }, "");
            pullDownChoice("todo-client-list", function (param) {
                $("#cusType").val("");
            }, "");

        }
        if (data.name == 'mysupport') {
            $("#mySupport").val("mySupport");
        }
        if (data == "todo-client-list") {
            $("#cusType").val(values);
        }
        if (data == "todo-status-list") {
            $("#Status").val(values);
        }

        $('#supportTable').bootstrapTable('refresh', {
            query: {
                'user': $("#mySupport").val(),
                'timeOutIds': $("#timeOutIds").val(),
                'doHour': $('#doHour').val(),
                'cusType': $('#cusType').val(),
                'Status': $('#Status').val()
            }
        });
    }

    function doNewSearch2(data) {//邮件请求检索
        $('#emailTable').bootstrapTable('refresh', {
            query: {
                'data': 'supportList',
                'opsupport': 'opsupport',
                'Status': data
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
            url: "/support/getOverTimeNum?myself=true",
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
    }
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
    }
    getOverTime();
    $('#ordinarySupport').click(function () {
        $('#supT1').removeClass('hiddenTable');
        $('#supT2').addClass('hiddenTable');
    });
    $('#emailSupport').click(function () {
        $('#supT2').removeClass('hiddenTable');
        $('#supT1').addClass('hiddenTable');
    });
    initTable();
    $(function () {
        pullDownChoice("todo-client-list", function (param) {
            doNewSearch("todo-client-list", param);
        });
        pullDownChoice("todo-status-list", function (param) {
            doNewSearch("todo-status-list", param);
        });
        pullDownChoice("mail-todo-status-list", function (param) {
            doNewSearch2(param);
        });
        showToolTip($("._handle_tip"));
        showTableToolTip("._jobState");
    })
</script>
</body>

</html>