<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <meta name="_token" content="{{ csrf_token() }}"/>
    <title>安畅网络 工单系统——操作工单列表</title>

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

        button.dim {
            display: inline-block;
            color: #fff;
            text-decoration: none;
            text-transform: uppercase;
            text-align: center;
            padding-top: 1px;
            margin-right: 3px;
            position: relative;
            cursor: pointer;
            height: 18px;
            border-radius: 8px;
            font-weight: 600;
            margin-bottom: 10px !important;
        }
        .nav-tabs > li > a {
             padding: 10px;
        }
        .nav-tabs {
             border-bottom:0;
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
                    <button class="submit-btn J_menuItem" href="/support/todoList" title="待办工单"> 我的工单</button>
                    <button class="submit-btn J_menuItem" href="/support/allList" title="全部工单"> 全部工单</button>
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
                    <input type="hidden" value="" id="cusType">
                    <input type="hidden" value="" id="Status">
                    <input type="hidden" value="" id="tagType">
                </div>
            </div>
            <div style="margin-top: 5px;">
                <div class="tab-content" style="padding: 10px;background-color: white">
                    <ul class="nav nav-tabs" id="nav-tabs">
                        <span class="pull-right small text-muted"></span>
                        <li class="active" style="background-color: white">
                            <a aria-expanded="true" data-toggle="tab" id="ordinarySupport"
                               onclick="changeTableTag(this)" data-tnum="supT1" style="height: 40px;">
                                <i class="fa fa-support"></i>操作工单</a></li>
                        <li class="" style="background-color: white">
                            <a aria-expanded="false" data-toggle="tab" style="height: 40px;"
                               id="emailSupport" onclick="changeTableTag(this)" data-tnum="supT2">
                                <i class="fa fa-envelope"></i>邮件请求<i id="email_count" class=""></i></a></li>
                        <li class="" style="background-color: white">
                            <a aria-expanded="false" data-toggle="tab" href="#tab-4" style="height: 40px;"
                               id="upGradeSupport" onclick="changeTableTag(this)" data-tnum="supT4">
                                <img src="/img/grade.png" width="20"/>升级工单</a></li>
                        @foreach(\Itsm\Http\Helper\ThirdCallHelper::getDictArray("工单标签","supportTag") as $tag)
                            <li class="" style="background-color: white" id="{{$tag->Code}}"></li>
                        @endforeach
                        <li class="" style="background-color: white" id="juhe"></li>
                    </ul>
                    <div class="supTable" id="supT1">
                        <table id="supportTable" class="table-no-bordered table-fixpadding active"
                               style="text-align: center;color:#6b7d86;" bgcolor="#FFFFFF"
                               cellpadding="0"
                               cellspacing="0" width="100%"
                               data-pagination="true"
                               data-page-size="10"
                               data-id-field="Id"
                               data-pagination-detail-h-align="right"
                               data-page-list="[10, 25, 50, 100, ALL]"
                               data-show-footer="false"
                               data-side-pagination="server"
                               data-url="/support/getOperateList"
                               data-response-handler="responseHandler">
                        </table>
                        <Div id="playMusic" data-src="{{$musicSrc}}" hidden="hidden">
                        </Div>
                    </div>
                    <div class="supTable hiddenTable" id="supT2">
                        <div>
                            <table id="emailTable" class="table-no-bordered active"
                                   style="text-align: center;color:#6b7d86;" bgcolor="#FFFFFF"
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
                                   data-url="/support/getOperateList?email=true"
                                   data-response-handler="responseHandler">
                            </table>
                        </div>
                        <div id="bottomOption" class="table-no-bordered active">
                            <button data-table="emailTable"  class="option-btn response-btn batchReply">批量应答</button>
                            <button data-table="emailTable"  class="option-btn close-btn batchClose">批量闭单</button>
                            <button data-table="emailTable" class="option-btn close-btn refresh">刷新</button>
                        </div>
                    </div>
                    <div class="supTable hiddenTable" id="supT3">
                        <div>
                            <p style="font-size: 12px;color:orange">&nbsp&nbsp标注: 聚合工单是指 连续5分钟来自相同数据中心的相同工单类型的列表</p>

                            <table id="juheTable" class="table-no-bordered active"
                                   style="text-align: center;color:#6b7d86;" bgcolor="#FFFFFF"
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
                                   data-url="/support/sameSupport"
                                   data-response-handler="responseHandler">
                            </table>
                        </div>
                    </div>
                    <div class="supTable hiddenTable" id="supT4">
                        <div>
                            <table id="upGradeTable" class="table-no-bordered active"
                                   style="text-align: center;color:#6b7d86;" bgcolor="#FFFFFF"
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
                                   data-url="/support/getOperateList?upGrade=true"
                                   data-response-handler="responseHandler">
                            </table>
                            <button data-table="upGradeTable" class="option-btn close-btn refresh">刷新</button>
                        </div>
                    </div>
                    <div class="supTable hiddenTable" id="supT8">
                        <div>
                            <table id="tagTable" class="table-no-bordered active"
                                   style="text-align: center;color:#6b7d86;" bgcolor="#FFFFFF"
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
                                   data-url="/support/getOperateList?email=true"
                                   data-response-handler="responseHandler">
                            </table>
                        </div>
                        <div id="bottomOption" class="table-no-bordered active">
                            <button data-table="tagTable" id="batchReply"
                                    class="option-btn response-btn batchReply">批量应答</button>
                            <button data-table="tagTable" id="batchClose"
                                    class="option-btn close-btn batchClose">批量闭单</button>
                            <button data-table="tagTable"
                                    class="option-btn response-btn editReply hidden">批量回复</button>
                            <button data-table="tagTable"
                                    class="option-btn close-btn batchDone hidden">批量已处理</button>
                            <button data-table="tagTable" class="option-btn close-btn refresh">刷新</button>
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
<script src="/js/job_list.js"></script>
<script type="text/javascript" src="/js/common.js"></script>
<script type="text/javascript" src="/js/job_list.js"></script>
<script src="/js/plugins/bootstrap-table/bootstrap-table.js"></script>
<!-- put your locale files after bootstrap-table.js -->
<script src="/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>


<script>
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
    ///刷新操作
    $(".refresh").click(function(){
        var table = $(this).data('table');
        var json = table == "tagTable"?{
            query: {
                'keyWord': $("#tagType").val()
            }
        }:"";
        $('#'+table).bootstrapTable('refresh',json);
    })
    /*
     * 批量关闭工单
     */
    $(".batchClose").click(function () {
        var table = $(this).data('table');
        layer.confirm('确定要批量关闭工单吗?', {
                    title: "提示",
                    btn: ['确定', '取消']
                },
                function () {
                    var selected = $('#'+table).bootstrapTable('getSelections');
                    var sIds = [];
                    if (selected.length < 1) {
                        layer.msg('请选择要操作的工单！', {icon: 2});
                        return false;
                    }
                    for (var key in selected) {
                        if (selected[key].Status == 'Todo' || selected[key].Status == 'Closed') {
                            layer.msg('当前选项中包含有待处理或已关闭的工单，请重新选择！', {icon: 2});
                            return false;
                        }
                        sIds[key] = {Id:selected[key].Id};
                    }
                    $.ajax({
                        type: "POST",
                        data: {'supIds': sIds},
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                        },
                        url: "/support/batchCloseMailSupport",
                        success: function (data) {
                            if (data.status == 'success') {
                                layer.msg('批量关闭工单成功！', {icon: 1});
                                var json = table == "tagTable"?{
                                    query: {
                                        'keyWord': $("#tagType").val()
                                    }
                                }:"";
                                $('#'+table).bootstrapTable('refresh',json);
                            }
                        }
                    })
                })
    });

    //批量已处理工单
    $(".batchDone").click(function () {
        var table = $(this).data('table');
        layer.confirm('确定要批量设置工单为已解决吗?', {
                    title: "提示",
                    btn: ['确定', '取消']
                },
                function () {
                    var selected = $('#'+table).bootstrapTable('getSelections');
                    var sIds = [];
                    if (selected.length < 1) {
                        layer.msg('请选择要操作的工单！', {icon: 2});
                        return false;
                    }
                    for (var key in selected) {
                        if (selected[key].Status != 'Todo' && selected[key].Status != 'Closed') {
                            sIds[key] = {Id:selected[key].Id};
                        }
                    }
                    $.ajax({
                        type: "POST",
                        data: {'supIds': sIds},
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                        },
                        url: "/support/batchDoneSupport",
                        success: function (data) {
                            if (data.status == 'success') {
                                layer.msg('批量处理工单成功！', {icon: 1});
                                var json = table == "tagTable"?{
                                    query: {
                                        'keyWord': $("#tagType").val()
                                    }
                                }:"";
                                $('#'+table).bootstrapTable('refresh',json);
                            }
                        }
                    })
                })
    });
    /**
     * 批量应答\指派
     */
    $('.batchReply').click(function () {
        var table = $(this).data('table');
        layer.confirm('确定要批量应答、指派工单吗?', {
                    title: "提示",
                    btn: ['确定', '取消']
                },
                function () {
                    var selected = $('#'+table).bootstrapTable('getSelections');
                    var sIds = [];
                    if (selected.length < 1) {
                        layer.msg('请选择要操作的工单！', {icon: 2});
                        return false;
                    }
                    for (var key in selected) {
                        if (selected[key].Status != 'Todo') {
                            layer.msg('当前选项中包含有非待处理的工单，请重新选择！', {icon: 2});
                            return false;
                        }
                        sIds[key] = {Id:selected[key].Id};
                    }
                    $.ajax({
                        type: "POST",
                        data: {'supIds': sIds},
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                        },
                        url: "/support/batchAnswerMailSupport",
                        success: function (data) {
                            if (data.status == 'success') {
                                layer.msg('批量应答,指派成功！', {icon: 1});
                                var json = table == "tagTable"?{
                                    query: {
                                        'keyWord': $("#tagType").val()
                                    }
                                }:"";
                                $('#'+table).bootstrapTable('refresh',json);
                                $('#emailTable').bootstrapTable('refresh');
                            }
                        }
                    })
                }
        )
    });

    //批量编辑回复
    $('.editReply').click(function () {
        var table = $(this).data('table');
        var selected = $('#'+table).bootstrapTable('getSelections');
        if (selected.length < 1) {
            layer.msg('请选择要操作的工单！', {icon: 2});
            return false;
        }
        layer.confirm('确定要批量回复工单吗?', {
                    title: "提示",
                    btn: ['确定', '取消']
                },
                function () {
                    layer.prompt({title: '请输入批量回复内容', formType: 2}, function (text) {
                        var sIds = [];
                        for (var key in selected) {
                            if (selected[key].Status != 'Todo') {
                                sIds[key] = {Id:selected[key].Id};
                            }
                        }
                        layer.confirm('是否同时给客户发送邮件?', {
                            title: "提示",
                            btn: ['是', '否']
                        },function(){
                            $.ajax({
                                type: "POST",
                                data: {'supIds': sIds,'replyData':text,'isEmail':'yes'},
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                                },
                                url: "/support/batchReplySupport",
                                success: function (data) {
                                    if (data.status == 'success') {
                                        layer.msg('批量操作成功！', {icon: 1});
                                        var json = table == "tagTable"?{
                                            query: {
                                                'keyWord': $("#tagType").val()
                                            }
                                        }:"";
                                        $('#'+table).bootstrapTable('refresh',json);
                                    }
                                }
                            })
                        },function(){
                            $.ajax({
                                type: "POST",
                                data: {'supIds': sIds,'replyData':text,'isEmail':'no'},
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                                },
                                url: "/support/batchReplySupport",
                                success: function (data) {
                                    if (data.status == 'success') {
                                        layer.msg('批量操作成功！', {icon: 1});
                                        var json = table == "tagTable"?{
                                            query: {
                                                'keyWord': $("#tagType").val()
                                            }
                                        }:"";
                                        $('#'+table).bootstrapTable('refresh',json);
                                    }
                                }
                            })
                        });
                    })
                }
        )
    });
    var $supportTable = $('#supportTable'),
            $emailTable = $('#emailTable'),
            $juheTable = $('#juheTable'),
            $upGradeTable = $('#upGradeTable'),
            $tagTable = $('#tagTable'),
            selections = [];

    function initTable() {//加载数据
        $supportTable.bootstrapTable({
            pageSize: 20,
            striped: true,
            sortable: true,
            strictSearch: true,
            searchOnEnterKey: true,
            columns: [
                [{
                    title: '工单编号',
                    valign: 'middle',
                    field: 'Id',
                    height: 100,
                    align: 'left',
                    width: '5%',
                    formatter: function (value, row, index) {

                        var s = row.Id;
                        return s;
                    },
                    sortName: 'Id',
                    sortable: true
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

                        var menuName = row.CusName;
                        if (null != menuName && menuName.length > 8) {
                            menuName = menuName.substring(0, 8) + "...";
                        }
                        var demo = '<a href="javascript:void(0);" id="title_' + row.Id + '" width="12" style="margin-left: 5px;height: 12px"></a>';

                        var title = row.Title.length > 10 ? row.Title.substr(0, 10) + '...' : row.Title;
                        var s = timeoutIcon + '<a class="J_menuItem showTitleTips" style="' + titleStyle + '"  title="' + menuName + '" ' +
                                'href="/wo/supportrefer/' + row.Id + '">' + title + '</a>';
                        var dim = '';
                        if (row.rid == null || row.isValidate == 1) {
                            dim = '<a href="javascript:void(0);" class="showCollectNote" onclick=addCollection("' + row.Id + '") title="收藏"><i class="fa fa-heart-o"></i></a> ';
                        } else {
                            dim = '<a href="javascript:void(0);" class="showCollectNote" onclick=delCollection("' + row.Id + '") title="取消收藏"><i class="fa fa-heart"></i></a> ';
                        }
                        var blank = '';
                        if(row.Title.length < 6)
                            blank = '&nbsp;&nbsp;&nbsp;&nbsp;'
                        return dim + s + blank +demo;
                    },
                    events: 'operateEvents'
                }, {
                    field: 'CusName',
                    title: '<div id="operate-client-list" class="select-wrap"><span class="current-title"><span class="current-select">所有客户</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list">@foreach($customerList as $k=>$status) <li class="select-list-item" value="{{$k}}">{{$status}}</li>@endforeach</ul></div>',
                    valign: 'middle',
                    align: 'left',
                    width: '20%',
                    formatter: function (value, row, index) {
                        if (row.CusName) {
                            var cusName = row.CusName.length > 10 ? row.CusName.substr(0, 10) + '...' : row.CusName;
                        }
                        var s = '<a class="showCusTips"  ' +
                                'href="{{env('JOB_URL', 'http://www.51idc.cn')}}/crm/user/finance/customerDetailNew.html?cusinfid=' + row.CustomerInfoId + '" target="_blank">' + cusName + '</a>';
                        var identity = s + formatterIdentity(row);
                        if (row.identity.Memo) {
                            identity = identity + '&nbsp;<a href="javascript:void(0);"><img class="showMemoTips" src="/img/cus_beizhu.png" width="16" height="15" style="margin-bottom: 6px"' +
                                    '/></a>';
                        }
                        if (row.agentName) {
                            identity = identity + '<br><h5>代理商：' + row.agentName + '</h5>';
                        }
                        return identity;
                    },
                    events: 'operateEvents',
                }, {
                    field: 'Status',
                    title: '<div id="operate-status-list" class="select-wrap"><span class="current-title"><span class="current-select">工单状态</span><i class="select-icon"></i></span>' +
                    '<ul class="select-list"><li class="select-list-item" value="">全部状态</li>@foreach($statusList as $k=>$status) <li class="select-list-item" value="{{$k}}">{{$status}}</li>@endforeach</ul></div>',
                    valign: 'middle',
                    align: 'left',
                    width: '8%',
                    formatter: function (value, row, index) {
                        var status = "";
                        var dim = '';
                        if (row.upGrade == 0) {
                            dim = '<a href="javascript:void(0);"><img class="dim" title="升级工单" src="/img/upgrade.png" width="20"' +
                                    'onclick=upGrade("' + row.Id + '") /></a>';
                        } else {
                            dim = '<a href="javascript:void(0);"><img class="dim" title="取消升级" src="/img/downgrade.png" width="20"' +
                                    'onclick=downGrade("' + row.Id + '") /></a>';
                        }
                        if (row.Status == "Todo") {
                            status = '<a  href="javascript:void(0);" onclick=fastReply("' + row.Id + '","' + row.Status + '")><img title="点击快速应答" src="/img/talk.png" width="20" /></a>';
                        }
                        return statusFormatter(row) + '&nbsp;' + dim + status;
                    }
                }, {
                    field: 'devIPAddr',//'EquipmentId',
                    title: '数据中心<br/>工单分类',
                    valign: 'middle',
                    align: 'left',
                    width: '15%',
                    formatter: function (value, row, index) {
                        var v = row.dataCenter != null ? "<span style=color:#999999>" + row.dataCenter + "</span>" : '';

                        var s = '<div id=checkOverTime>' + v + '<br/>' + row.ClassInficationOne + '</div>';
                        return s;
                    },
//                    cellStyle: function (value, row, index, field) {
//                        return checkTimeOutStyle(value, row, index, field);
//                    }
                }, {
                    field: 'UpTs',
                    title: '负责人<br/>最后更新人',
                    valign: 'middle',
                    align: 'left',
                    width: '10%',
                    formatter: function (value, row, index) {
                        var t = (row.ChargeUserTwoId && '无' != row.ChargeUserTwoId) ? '/' + row.ChargeUserTwoId : '';
                        var s = row.ChargeUserId + t + '<br/>' + row.OperationId;
                        return s;
                    },
                    sortable: true
                }, {
                    field: 'Evaluation',//'ChargeUserId',
                    title: '工单跟踪人<br/>已处理时长',
                    valign: 'middle',
                    align: 'left',
                    width: '12%',
                    formatter: function (value, row, index) {
                        var v = row.overTime != null ? timeStamp(row.overTime) : '';
                        var s = row.AsuserId + '<br/>' + v;
                        return s;
                    },
                }
                ]
            ], onLoadSuccess: function (value, row, index) {
            if (value.juhe.total > 0) {
                var html = '<a aria-expanded="false" data-toggle="tab" href="#tab-3" style="height: 40px;" ' +
                        'id="juheSupport" onclick="changeTableTag(this)" data-tnum="supT3">' +
                        '<i class="fa fa-dashboard"></i>聚合工单 <i id="juhe_count" class="">' +
                        value.juhe.total + '</i></a>';
                $("#juhe").html(html);
                $('#juheTable').bootstrapTable('refresh');
            } else {
                $("#juhe").html('');
            }
        },
            queryParamsType: "undefined",
            queryParams: function queryParams(params) {
                return {
                    pageSize: params.pageSize,
                    pageNumber: params.pageNumber,
                    timeOutIds: $('#timeOutIds').val(),
                    cusType: $('#cusType').val(),
                    Status: $('#Status').val(),
                    sortName: params.sortName,
                    sortOrder: params.sortOrder,
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
                    align: 'middle',
                    valign: 'middle',
                    width: '5%',
                }, {
                    title: '工单编号',
                    valign: 'middle',
                    field: 'Id',
                    width: '10%',
                    align: 'left',
                    formatter: function (value, row, index) {
                        return row.Id;
                    }
                }, {
                    title: '工单标题',
                    valign: 'middle',
                    field: 'Title',
                    width: '20%',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var title = row.Title.length > 10 ? row.Title.substr(0, 10) + '...' : row.Title;
                        var demo = '<a href="javascript:void(0);" id="title_' + row.Id + '" width="12" style="margin-left: 5px;height: 12px"></a>';
                        var blank = '';
                        if(row.Title.length < 6)
                            blank = '&nbsp;&nbsp;&nbsp;&nbsp;';
                        var s = '<a class="showTitleTips J_menuItem" target="_blank"' +
                                'href="/wo/supportrefer/' + row.Id + '">' + title + '</a>';
                        return s+blank+demo;
                    },
                    events: 'operateEvents'
                }, {
                    field: 'CusName',
                    title: '客户名称',
                    valign: 'middle',
                    width: '15%',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var cusName = row.CusName.length > 10 ? row.CusName.substr(0, 10) + '...' : row.CusName;
                        var s = '<a class="showCusTips"  id="cusInfo_' + row.Id + '"    ' +
                                'href="{{env('JOB_URL', 'http://www.51idc.cn')}}/crm/user/finance/customerDetailNew.html?cusinfid=' + row.CustomerInfoId + '" target="_blank">' + cusName + '</a>';
                        var identity = s + formatterIdentity(row);
                        return identity;
                    },
                    events: 'operateEvents'
                }, {
                    field: 'ClassInfication',
                    title: '<div id="mail-operate-status-list" class="select-wrap"><span class="current-title"><span class="current-select">工单状态</span><i class="select-icon"></i></span>' +
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
                    width: '10%',
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
                    width: '10%',
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
                    'reqType':'tagReq'
                }
            }
        });
        var custips;
        //bootstrap监听事件
        window.operateEvents = {
            'mouseover .showCusTips': function (e, value, row, index) {
                var self= this;
                $.ajax({
                    type: "get",
                    async: false,
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
                                    ,self, {time: 0, tips: [1, '#999999'], maxWidth: 400});
                        }
                    }
                });
            },
            'mouseleave .showCusTips': function (e, value, row, index) {
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
            },
            'mouseenter .juhe_showCusTips': function (e, value, row, index) {
                var self= this;
                $.ajax({
                    type: "get",
                    async: false,
                    url: "/customer/cusDetail/" + row.CustomerInfoId,
                    success: function (data) {
                        if (data['cusInfo']) {
                            var contacts = '';
                            for (var contact in data['contacts']) {
                                var type = data['contacts'][contact].ConType;
                                type == '' ? type = '其它类型联系人' : type = type;
                                contacts += type + ':<br/>' +
                                        '<span style="text-indent: 2em;line-height: 7px">姓名：' + data['contacts'][contact].Name + '</span>' + '<br>' +
                                        '<span style="text-indent: 2em;line-height: 7px">联系电话：' + data['contacts'][contact].Mobile + '</span><br>';
                            }
                            custips = layer.tips('<div style="word-wrap: break-word; color: #ffffff">客户名称：' + data['cusInfo'].CusName + '<br/>' +
                                    '客户经理：' + data['cusInfo'].SellName + '<br/>' +
                                    '客户类型：' + data['cusInfo'].CusTypeName + '<br/>' +
                                    '联系电话：' + data['cusInfo'].Tel + '<br/>' +
                                    '邮件：' + data['cusInfo'].EMAIL + '<br/>' +
                                    '地址：' + data['cusInfo'].Address + '<br/><hr>' + contacts + '</div>'
                                    ,self, {time: 0, tips: [1, '#999999'], maxWidth: 400});
                        }
                    }
                });
            },
            'mouseleave .juhe_showCusTips': function (e, value, row, index) {
                layer.close(custips);
            },
            'mouseover .showTitleTips': function (e, value, row, index) {
                var content = row.Body,
                        lastOperation = row.OperationId != '无' ? '于' + row.lastOperationTs + '进行了：<br/>' + row.lastOperation : '';
                content = content.replace(new RegExp("<img", "gm"), "<img style='width:100%''");
                lastOperation = lastOperation.replace(new RegExp("<img", "gm"), "<img style='width:80%;height:80px'");
                custips = layer.tips('<div style="word-wrap: break-word;color: #ffffff">工单标题：<br/>' + row.Title + '<br/>' +
                        '工单内容：[点击标题查看全部详情]<br/><div class="supportBody" style="color: #ffffff;max-height: 150px;overflow: hidden;">' + content + '</div><br/>' +
                        '数据中心：' + row.dataCenter + '<br/><hr>' +
                        '最后一次操作：' + row.OperationId + lastOperation + '</div>'
                        ,'#title_' + row.Id, {time: 0, tips: [1, '#999999'], maxWidth: 400});
            },
            'mouseout .showTitleTips': function (e, value, row, index) {
                layer.close(custips);
            },
            'mouseover .showJuHeTitleTips': function (e, value, row, index) {
                var content = row.Body,
                        lastOperation = row.OperationId != '无' ? '于' + row.lastOperationTs + '进行了：<br/>' + row.lastOperation : '';
                content = content.replace(new RegExp("<img", "gm"), "<img style='width:100%''");
                lastOperation = lastOperation.replace(new RegExp("<img", "gm"), "<img style='width:80%;height:80px'");

                custips = layer.tips('<div style="word-wrap: break-word;color: #ffffff">工单标题：<br/>' + row.Title + '<br/>' +
                        '工单内容：<br/><div class="supportBody" style="color: #ffffff">' + content + '</div><br/>' +
                        '数据中心：' + row.dataCenter + '<br/><hr>' +
                        '最后一次操作：' + row.OperationId + lastOperation + '</div>'
                        ,'#title_' + row.Id, {time: 0, tips: [1, '#999999'], maxWidth: 400});
            },
            'mouseout .showJuHeTitleTips': function (e, value, row, index) {
                layer.close(custips);
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
            'mouseover .showMemoTips': function (e, value, row, index) {
                if (row.identity.Memo) {
                    custips = layer.tips(row.identity.Memo, this, {
                        time: 0,
                        tips: [1, '#999999'],
                        maxWidth: 400
                    });
                }
            },
            'mouseleave .showMemoTips': function (e, value, row, index) {
                if (row.identity.Memo) {
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
        };
        $emailTable.on('load-success.bs.table', function (e, data) {
            if (data.tagList.length > 0) {
                for(var i in data.tagList){
                    if(data["s"+data.tagList[i].Code]>0){
                        var aa="";
                        if(data[data.tagList[i].Code]>0){
                            aa = '<i class="mailRequest">' +
                                    data[data.tagList[i].Code] + '</i>';
                        }
                        var html = '<a aria-expanded="false" data-toggle="tab" style="height: 40px;" ' +
                                'onclick="changeTableTag(this)" data-tnum="'+data.tagList[i].Code+'">'+
                                data.tagList[i].Means+aa +'</a>';
                        $("#"+data.tagList[i].Code).html(html);
                    }else{
                        $("#"+data.tagList[i].Code).html('');
                    }
                }
            }
            if (data.todoCount) {
                $('#email_count').addClass("mailRequest");
                $('#email_count').html(data.todoCount);
            }
            checkedTodos()
        });
        $tagTable.bootstrapTable({
            pageSize: 20,
            striped: true,
            columns: [
                [{
                    field: 'state',
                    checkbox: true,
                    align: 'middle',
                    valign: 'middle',
                    width: '5%',
                }, {
                    title: '工单编号',
                    valign: 'middle',
                    field: 'Id',
                    width: '10%',
                    align: 'left',
                    formatter: function (value, row, index) {
                        return row.Id;
                    }
                }, {
                    title: '工单标题',
                    valign: 'middle',
                    field: 'Title',
                    width: '20%',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var title = row.Title.length > 10 ? row.Title.substr(0, 10) + '...' : row.Title;

                        var s = '<a class="showTitleTips J_menuItem" target="_blank"  id="title_' + row.Id + '"    ' +
                                'href="/wo/supportrefer/' + row.Id + '">' + title + '</a>';
                        return s;
                    },
                    events: 'operateEvents'
                }, {
                    field: 'CusName',
                    title: '客户名称',
                    valign: 'middle',
                    width: '15%',
                    align: 'left',
                    formatter: function (value, row, index) {
                        var cusName = row.CusName.length > 10 ? row.CusName.substr(0, 10) + '...' : row.CusName;
                        var s = '<a class="showCusTips"  id="cusInfo_' + row.Id + '"    ' +
                                'href="{{env('JOB_URL', 'http://www.51idc.cn')}}/crm/user/finance/customerDetailNew.html?cusinfid=' + row.CustomerInfoId + '" target="_blank">' + cusName + '</a>';
                        var identity = s + formatterIdentity(row);
                        return identity;
                    },
                    events: 'operateEvents'
                }, {
                    field: 'ClassInfication',
                    title: '<div id="mail-tag-status-list" class="select-wrap"><span class="current-title"><span class="current-select">工单状态</span><i class="select-icon"></i></span>' +
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
                    width: '10%',
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
                    width: '10%',
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
                    'keyWord': 'anch-noc'
                }
            }
        });
    }

    //当前操作页面进入后只加载操作合邮件工单列表，后续相应列表点击后加载
    function clickToLoad(tnum){
        switch (tnum){
            case "supT3":$juheTable.bootstrapTable({
                pageSize: 20,
                striped: true,
                sortable: true,
                strictSearch: true,
                searchOnEnterKey: true,
                columns: [
                    [{
                        title: '工单编号',
                        valign: 'middle',
                        field: 'Id',
                        height: 100,
                        align: 'left',
                        width: '10%',
                        formatter: function (value, row, index) {

                            var s = row.Id;
                            return s;
                        },
                        sortName: 'Id',
                        sortable: true
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

                            var menuName = row.CusName;
                            if (null != menuName && menuName.length > 8) {
                                menuName = menuName.substring(0, 8) + "...";
                            }

                            var title = row.Title.length > 10 ? row.Title.substr(0, 10) + '...' : row.Title;

                            var s = timeoutIcon + '<a class="showJuHeTitleTips J_menuItem" style="' + titleStyle + '"  title="' + menuName + '"' +
                                    'href="/wo/supportrefer/' + row.Id + '">' + title + '</a>';
                            var demo = '<a href="javascript:void(0);" id="title_' + row.Id + '" width="12" style="margin-left: 5px;height: 12px"></a>';
                            var blank = '';
                            if(row.Title.length < 6)
                                blank = '&nbsp;&nbsp;&nbsp;&nbsp;'
                            return s+blank+demo;
                        },
                        events: 'operateEvents'
                    }, {
                        field: 'CusName',
                        title: '所有客户',
                        valign: 'middle',
                        align: 'left',
                        width: '20%',
                        formatter: function (value, row, index) {
                            if (row.CusName) {
                                var cusName = row.CusName.length > 10 ? row.CusName.substr(0, 10) + '...' : row.CusName;
                            }
                            var s = '<a class="juhe_showCusTips"  id="juhe_cusInfo_' + row.Id + '"    ' +
                                    'href="{{env('JOB_URL', 'http://www.51idc.cn')}}/crm/user/finance/customerDetailNew.html?cusinfid=' + row.CustomerInfoId + '" target="_blank">' + cusName + '</a>';
                            var identity = s + formatterIdentity(row);
                            if (row.agentName) {
                                identity = identity + '<br><h5>代理商：' + row.agentName + '</h5>';
                            }
                            return identity;
                        },
                        events: 'operateEvents',
                    }, {
                        field: 'Status',
                        title: '工单状态',
                        valign: 'middle',
                        align: 'left',
                        width: '10%',
                        formatter: function (value, row, index) {
                            var status = "";
                            if (row.Status == "Todo") {
                                status = '<a  href="javascript:void(0);" onclick=fastReply("' + row.Id + '","' + row.Status + '")><img title="点击快速应答" src="/img/talk.png" width="20" /></a>';
                            }
                            return statusFormatter(row) + '&nbsp;' + status;
                        }
                    }, {
                        field: 'devIPAddr',//'EquipmentId',
                        title: '数据中心<br/>工单分类',
                        valign: 'middle',
                        align: 'left',
                        width: '15%',
                        formatter: function (value, row, index) {
                            var v = row.dataCenter != null ? "<span style=color:#999999>" + row.dataCenter + "</span>" : '';

                            var s = '<div id=checkOverTime>' + v + '<br/>' + row.ClassInficationOne + '</div>';
                            return s;
                        }
                    }, {
                        field: 'UpTs',
                        title: '负责人<br/>最后更新人',
                        valign: 'middle',
                        align: 'left',
                        width: '10%',
                        formatter: function (value, row, index) {
                            var t = (row.ChargeUserTwoId && '无' != row.ChargeUserTwoId) ? '/' + row.ChargeUserTwoId : '';
                            var s = row.ChargeUserId + t + '<br/>' + row.OperationId;
                            return s;
                        },
                        sortable: true
                    }, {
                        field: 'Evaluation',//'ChargeUserId',
                        title: '工单跟踪人<br/>已处理时长',
                        valign: 'middle',
                        align: 'left',
                        width: '15%',
                        formatter: function (value, row, index) {
                            var v = row.overTime != null ? timeStamp(row.overTime) : '';
                            var s = row.AsuserId + '<br/>' + v;
                            return s;
                        },
                    }
                    ]
                ],
                onLoadSuccess: function (value, row, index) {
                    if (value.total > 0) {
                        var html = "<i class=\"fa fa-dashboard\"></i>聚合工单 <i id=\"juhe_count\" class=''>" + value.total + "</i>";
                        $("#juheSupport").html(html);
                    } else {
                        $("#juheSupport").html('');
                    }
                },
                queryParamsType: "undefined",
                queryParams: function queryParams(params) {
                    return {
                        pageSize: params.pageSize,
                        pageNumber: params.pageNumber,
                        timeOutIds: $('#timeOutIds').val(),
                        cusType: $('#cusType').val(),
                        Status: $('#Status').val(),
                        sortName: params.sortName,
                        sortOrder: params.sortOrder
                    }
                }
            });
                break;
            case "supT4":
                $upGradeTable.bootstrapTable({
                    pageSize: 20,
                    striped: true,
                    sortable: true,
                    strictSearch: true,
                    searchOnEnterKey: true,
                    columns: [
                        [{
                            title: '工单编号',
                            valign: 'middle',
                            field: 'Id',
                            height: 100,
                            align: 'left',
                            width: '10%',
                            formatter: function (value, row, index) {

                                var s = row.Id;
                                return s;
                            },
                            sortName: 'Id',
                            sortable: true
                        }, {
                            title: '&nbsp;&nbsp;&nbsp;&nbsp;工单标题',
                            valign: 'middle',
                            field: 'Title',
                            align: 'left',
                            width: '18%',
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
                                var s = timeoutIcon + '<a class="showUpTitleTips J_menuItem" style="' + titleStyle + '"  title="' + menuName + '"' +
                                        'href="/wo/supportrefer/' + row.Id + '">' + title + '</a>';
                                var demo = '<a href="javascript:void(0);" id="title_' + row.Id + '" width="12" style="margin-left: 5px;height: 12px"></a>';
                                var blank = '';
                                if(row.Title.length < 6)
                                    blank = '&nbsp;&nbsp;&nbsp;&nbsp;';
                                return s + blank + demo;
                            },
                            events: 'operateEvents'
                        }, {
                            field: 'CusName',
                            title: '<div id="upgrade-client-list" class="select-wrap"><span class="current-title"><span class="current-select">所有客户</span><i class="select-icon"></i></span>' +
                            '<ul class="select-list">@foreach($customerList as $k=>$status) <li class="select-list-item" value="{{$k}}">{{$status}}</li>@endforeach</ul></div>',
                            valign: 'middle',
                            align: 'left',
                            width: '20%',
                            formatter: function (value, row, index) {
                                if (row.CusName) {
                                    var cusName = row.CusName.length > 10 ? row.CusName.substr(0, 10) + '...' : row.CusName;
                                }
                                var s = '<a class="showCusTips"  id="cusInfo_' + row.Id + '"    ' +
                                        'href="{{env('JOB_URL', 'http://www.51idc.cn')}}/crm/user/finance/customerDetailNewtail.html?cusinfid=' + row.CustomerInfoId + '" target="_blank">' + cusName + '</a>';
                                var identity = s + formatterIdentity(row);
                                if (row.agentName) {
                                    identity = identity + '<br><h5>代理商：' + row.agentName + '</h5>';
                                }
                                return identity;
                            },
                            events: 'operateEvents',
                        }, {
                            field: 'Status',
                            title: '<div id="upgrade-status-list" class="select-wrap"><span class="current-title"><span class="current-select">工单状态</span><i class="select-icon"></i></span>' +
                            '<ul class="select-list"><li class="select-list-item" value="">全部状态</li>@foreach($statusList as $k=>$status) <li class="select-list-item" value="{{$k}}">{{$status}}</li>@endforeach</ul></div>',
                            valign: 'middle',
                            align: 'left',
                            width: '12%',
                            formatter: function (value, row, index) {
                                var status = "";
                                var dim = '<a href="javascript:void(0);"><img class="dim" title="取消升级" src="/img/downgrade.png" width="20"' +
                                        'onclick=downGrade("' + row.Id + '") /></a>';
                                if (row.Status == "Todo") {
                                    status = '<a  href="javascript:void(0);" onclick=fastReply("' + row.Id + '","' + row.Status + '")><img title="点击快速应答" src="/img/talk.png" width="20" /></a>';
                                }
                                return statusFormatter(row) + '&nbsp;' + dim + status;
                            }
                        }, {
                            field: 'devIPAddr',//'EquipmentId',
                            title: '数据中心<br/>工单分类',
                            valign: 'middle',
                            align: 'left',
                            width: '15%',
                            formatter: function (value, row, index) {
                                var v = row.dataCenter != null ? "<span style=color:#999999>" + row.dataCenter + "</span>" : '';

                                var s = '<div id=checkOverTime>' + v + '<br/>' + row.ClassInficationOne + '</div>';
                                return s;
                            },
//                    cellStyle: function (value, row, index, field) {
//                        return checkTimeOutStyle(value, row, index, field);
//                    }
                        }, {
                            field: 'UpTs',
                            title: '负责人<br/>最后更新人',
                            valign: 'middle',
                            align: 'left',
                            width: '10%',
                            formatter: function (value, row, index) {
                                var t = (row.ChargeUserTwoId && '无' != row.ChargeUserTwoId) ? '/' + row.ChargeUserTwoId : '';
                                var s = row.ChargeUserId + t + '<br/>' + row.OperationId;
                                return s;
                            },
                            sortable: true
                        }, {
                            field: 'Evaluation',//'ChargeUserId',
                            title: '工单跟踪人<br/>已处理时长',
                            valign: 'middle',
                            align: 'left',
                            width: '15%',
                            formatter: function (value, row, index) {
                                var v = row.overTime != null ? timeStamp(row.overTime) : '';
                                var s = row.AsuserId + '<br/>' + v;
                                return s;
                            },
                        }
                        ]
                    ],
                    queryParamsType: "undefined",
                    queryParams: function queryParams(params) {
                        return {
                            pageSize: params.pageSize,
                            pageNumber: params.pageNumber,
                            cusType: $('#cusType').val(),
                            Status: $('#Status').val(),
                            sortName: params.sortName,
                            sortOrder: params.sortOrder,
                        }
                    }
                });
                //upgrade list
                pullDownChoice("upgrade-client-list", function (param) {
                    doNewSearch("upgrade-client-list", param);
                });
                pullDownChoice("upgrade-status-list", function (param) {
                    doNewSearch("upgrade-status-list", param);
                });
                break;
            default:
                $tagTable.bootstrapTable('refresh', {
                    query: {
                        'Status': $('#Status').val(),
                        'keyWord': tnum
                    }
                });
                $("#tagType").val(tnum);
                //邮件告警 list
                pullDownChoice("mail-tag-status-list", function (param) {
                    doNewSearch2(param, 'tagTable');
                });
                break;
        }
        window.operateEvents = {
            'mouseenter .showCusTips': function (e, value, row, index) {
                var self= this;
                $.ajax({
                    type: "get",
                    async: false,
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
                                    ,self, {time: 0, tips: [1, '#999999'], maxWidth: 400});
                        }
                    }
                });
            },
            'mouseleave .showCusTips': function (e, value, row, index) {
                layer.close(custips);
            },
            'mouseenter .juhe_showCusTips': function (e, value, row, index) {
                var self= this;
                $.ajax({
                    type: "get",
                    async: false,
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
                                    ,self, {time: 0, tips: [1, '#999999'], maxWidth: 400});
                        }
                    }
                });
            },
            'mouseleave .juhe_showCusTips': function (e, value, row, index) {
                layer.close(custips);
            },
            'mouseover .showTitleTips': function (e, value, row, index) {
                var content = row.Body,
                        lastOperation = row.OperationId != '无' ? '于' + row.lastOperationTs + '进行了：<br/>' + row.lastOperation : '';
                content = content.replace(new RegExp("<img", "gm"), "<img style='width:100%'");
                lastOperation = lastOperation.replace(new RegExp("<img", "gm"), "<img style='width:100%;height:80px'");
                custips = layer.tips('<div style="word-wrap: break-word;color: #ffffff">工单标题：<br/>' + row.Title + '<br/>' +
                        '工单内容：[点击标题查看全部详情]<br/><div class="supportBody" style="color: #ffffff;max-height: 150px;overflow: hidden;">' + content + '</div><br/>' +
                        '数据中心：' + row.dataCenter + '<br/><hr>' +
                        '最后一次操作：' + row.OperationId + lastOperation + '</div>'
                        ,this, {time: 0, tips: [1, '#999999'], maxWidth: 400});
            },
            'mouseover .showUpTitleTips': function (e, value, row, index) {
                var content = row.Body,
                        lastOperation = row.OperationId != '无' ? '于' + row.lastOperationTs + '进行了：<br/>' + row.lastOperation : '';
                content = content.replace(new RegExp("<img", "gm"), "<img style='width:100%'");
                lastOperation = lastOperation.replace(new RegExp("<img", "gm"), "<img style='width:100%;height:80px'");
                custips = layer.tips('<div style="word-wrap: break-word;color: #ffffff">工单标题：<br/>' + row.Title + '<br/>' +
                        '工单内容：[点击标题查看全部详情]<br/><div class="supportBody" style="color: #ffffff;max-height: 150px;overflow: hidden;">' + content + '</div><br/>' +
                        '数据中心：' + row.dataCenter + '<br/><hr>' +
                        '最后一次操作：' + row.OperationId + lastOperation + '</div>'
                        ,'#title_' + row.Id, {time: 0, tips: [1, '#999999'], maxWidth: 400});
            },
            'mouseout .showTitleTips': function (e, value, row, index) {
                layer.close(custips);
            },
            'mouseout .showUpTitleTips': function (e, value, row, index) {
                layer.close(custips);
            },
            'mouseover .showJuHeTitleTips': function (e, value, row, index) {
                var content = row.Body,
                        lastOperation = row.OperationId != '无' ? '于' + row.lastOperationTs + '进行了：<br/>' + row.lastOperation : '';
                content = content.replace(new RegExp("<img", "gm"), "<img style='width:100%'");
                lastOperation = lastOperation.replace(new RegExp("<img", "gm"), "<img style='width:80%;height:80px'");
                custips = layer.tips('<div style="word-wrap: break-word;color: #ffffff">工单标题：<br/>' + row.Title + '<br/>' +
                        '工单内容：[点击标题查看全部详情]<br/><div class="supportBody" style="color: #ffffff;max-height: 150px;overflow: hidden;">' + content + '</div><br/>' +
                        '数据中心：' + row.dataCenter + '<br/><hr>' +
                        '最后一次操作：' + row.OperationId + lastOperation + '</div>'
                        ,'#title_' + row.Id, {time: 0, tips: [1, '#999999'], maxWidth: 400});
            },
            'mouseout .showJuHeTitleTips': function (e, value, row, index) {
                layer.close(custips);
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
        };
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
                $.ajax({
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
    function downGrade(supId) {
        layer.confirm('确定取消升级属性吗?', {icon: 3, title: '提示？'}, function (index) {
            $.ajax({
                type: "GET",
                async: false,
                url: "/support/updateGrade?type=down&supId=" + supId,
                success: function (msg) {
                    if (msg.status) {
                        layer.msg(msg.msg, {icon: 6});
                    }
                    else {
                        layer.msg(msg.msg, {icon: 2});
                    }
                    $('#supportTable').bootstrapTable('refresh');
                    $('#upGradeTable').bootstrapTable('refresh');
                }
            });
            return
        });

    }

    function upGrade(supId) {
        layer.confirm('确定升级此工单吗?', {icon: 3, title: '提示？'}, function (index) {
            $.ajax({
                type: "GET",
                async: false,
                url: "/support/updateGrade?type=up&supId=" + supId,
                success: function (msg) {
                    if (msg.status) {
                        layer.msg(msg.msg, {icon: 6});
                    }
                    else {
                        layer.msg(msg.msg, {icon: 2});
                    }
                    $('#supportTable').bootstrapTable('refresh');
                    $('#upGradeTable').bootstrapTable('refresh');
                }
            });
            return
        });

    }

    function downGrade(supId) {
        layer.confirm('确定取消升级属性吗?', {icon: 3, title:'提示？'}, function(index){
            $.ajax({
                type: "GET",
                async: false,
                url: "/support/updateGrade?type=down&supId=" + supId,
                success: function (msg) {
                    if (msg.status) {
                        layer.msg(msg.msg, {icon: 6});
                    }
                    else {
                        layer.msg(msg.msg, {icon: 2});
                    }
                    $('#supportTable').bootstrapTable('refresh');
                    $('#upGradeTable').bootstrapTable('refresh');
                }
            });
            return
        });

    }

    function fastReply(supId, status) {
        if (status != 'Todo') {
            return;
        }
        $.ajax({
            type: "GET",
            async: false,
            url: "/support/speedAnswer?supId=" + supId,
            success: function (msg) {
                if (msg.status == "success") {
                    layer.msg('工单已快速答复，请尽快安排负责人处理！', {icon: 6});
                }
                 else if (msg.status == "failure") {
                    layer.msg('此工单状态已更新，请重试！', {icon: 2});
                }
                else {
                    layer.msg('提交出错, 请稍候再试！', {icon: 2});
                }
                $('#supportTable').bootstrapTable('refresh');
                checkedTodos();
            }
        });
        return

    }

    function doNewSearch(data, values) {//普通工单检索
        if (data.name == 'lestThanOne' || data.name == 'moreThanOne' || data.name == 'moreThanTwo') {
            timeOutIds = $("#" + values).val();
            //没数据则直接返回
            if (timeOutIds == "") {
                return
            }
            $("#timeOutIds").val(timeOutIds)
            getOverTime();
            //重置下拉框
            pullDownChoice("operate-status-list", function (param) {
                $("#Status").val("");
            }, "");
            pullDownChoice("operate-client-list", function (param) {
                $("#cusType").val("");
            }, "");
            pullDownChoice("upgrade-status-list", function (param) {
                $("#Status").val("");
            }, "");
            pullDownChoice("upgrade-client-list", function (param) {
                $("#cusType").val("");
            }, "");
        }
        if (data == "operate-client-list") {
            $("#cusType").val(values);
        }
        if (data == "operate-status-list") {
            $("#Status").val(values);
        }
        if (data == "upgrade-client-list") {
            $("#cusType").val(values);
        }
        if (data == "upgrade-status-list") {
            $("#Status").val(values);
        }
        if (data == "upgrade-client-list" || data == "upgrade-status-list") {
            $('#upGradeTable').bootstrapTable('refresh', {
                query: {
                    'cusType': $('#cusType').val(),
                    'Status': $('#Status').val()
                }
            });
        } else {
            $('#supportTable').bootstrapTable('refresh', {
                query: {
                    'timeOutIds': $('#timeOutIds').val(),
                    'cusType': $('#cusType').val(),
                    'Status': $('#Status').val()
                }
            });
        }
    }

    function doNewSearch2(data,table) {//邮件请求检索
        $('#'+table).bootstrapTable('refresh', {
            query: {
                'Status': data
            }
        });
    }
    /**
     * 查找处理中任务,如果有则播放音乐
     */
    function checkedTodos() {
        $.ajax({//查询待处理工单数量
            type: "GET",
            async: false,
            url: "/support/getTodoCount",
            success: function (data) {
                if (data > 0) {//待处理工单大于0则播放音乐
                    var musicSrc = $('#playMusic').data('src');
                    $('#playMusic').html('<audio controls="" autoplay="" name="media" ><source src="' + musicSrc + '" type="audio/mpeg" ></audio>');
                } else {
                    $('#playMusic').html('');
                }

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
    }

    function rowStyleToTimeOut(row, index) {
        return {
            css: {"background-color": "red", "color": "#ffffff"}
        };
    }
    getOverTime();
    checkedTodos();

    //切换显示列表
    function changeTableTag(currentObj){
        clickToLoad($(currentObj).data('tnum'));
        $(".supTable").addClass('hiddenTable');
        var aa = $(currentObj).data('tnum');
        if(aa != 'supT1' &&aa != 'supT2' &&aa != 'supT3' &&aa != 'supT4'){
            $("#supT8").removeClass('hiddenTable');
            if(aa == 'largearea-alarm'){
                $("#batchReply").addClass("hidden");
                $("#batchClose").addClass("hidden");
                $(".editReply").removeClass("hidden");
                $(".batchDone").removeClass("hidden");
            }else{
                $("#batchReply").removeClass("hidden");
                $("#batchClose").removeClass("hidden");
                $(".editReply").addClass("hidden");
                $(".batchDone").addClass("hidden");
            }
        }else{
            $("#" + aa).removeClass('hiddenTable');
        }

    }

    initTable();

    $(function () {
        //operate list
        pullDownChoice("operate-client-list", function (param) {
            doNewSearch("operate-client-list", param);
        });
        pullDownChoice("operate-status-list", function (param) {
            doNewSearch("operate-status-list", param);
        });
        //email list
        pullDownChoice("mail-operate-status-list", function (param) {
            doNewSearch2(param,'emailTable');
        });
        showToolTip($("._handle_tip"));
        showTableToolTip("._jobState");
    });
    setInterval("$('#supportTable').bootstrapTable('refresh')", 120000);
    setInterval("$('#emailTable').bootstrapTable('refresh')", 30000);
</script>
</body>

</html>