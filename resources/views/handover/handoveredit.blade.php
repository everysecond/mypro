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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .red {
            color: #fd0001
        }

        .info-content span {
            font-weight: bold;
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

        .info-content li, .info-content li span, .info-content li p {
            display: inline-block;
            font-size: 14px;
            line-height: 35px;
        }

        .info-content li {
            width: 49%;
        }

        input[type=checkbox]{
            width: 25px;
        }
    </style>
</head>
<body>
<div class="job-detail clearfix" style="width: 98%;min-width: 720px;margin-left: 10px">
    <form id="myform">
        <p class="info-title" style="height: 32px;" id="detailsArea">交接单编辑</p>
        <div class="job-record  module-style" style="width: 98%;margin-top: 0">
            <input type="hidden" name="ccIds" value="{{$ccIds}}"/>
            <input type="hidden" id="handoverId" name="handoverId" value="{{$handoverData->id}}"/>
            <div class="info-content">
                <ul>
                    <li><span><span style="color: red">*</span>转交部门：</span>
                        <div style="display: inline-block;">
                            <select name="groupId" id="groupId" class="form-control validate"
                                    onchange="getStuffs(this)"
                                    style="margin-left: 10px;">
                                <option value="">请选择</option>
                                @foreach($groups as $key=>$item)
                                    <option value="{{$key}}"
                                            @if($handoverData->groupId == $key)
                                            selected="selected"
                                            @endif
                                    >{{$item['name']}}</option>
                                    @if(isset($item['child'])&&is_array($item['child']))
                                        @foreach($item['child'] as $k=>$value)
                                            <option value="{{$k}}"
                                                    @if($handoverData->groupId === $k)
                                                    selected="selected"
                                                    @endif
                                            >&nbsp;&nbsp;&nbsp;&nbsp;{{$value}}</option>@endforeach
                                    @endif
                                @endforeach
                            </select></div>
                    </li>
                    <li><span><span style="color: red">*</span>分派任务负责人：</span>
                        <div style="display: inline-block">
                            <select name="chargerId" id="chargerId" class="form-control validate"
                                    style="margin-left: 10px;">
                                <option value="">请选择</option>
                            </select></div>
                    </li>
                    <li><span>预约更换时间：</span>
                        <p>
                        </p>
                    </li>
                    <li><span>自动更换负责人：</span>
                        <p></p></li>
                    <li>
                        <span class="inline"><span style="color: red">*</span>提醒方式：</span>
                        <input type="checkbox" value="sms" name="rmode[]"
                               @if(in_array('sms',$rMode))
                               checked="checked"
                                @endif
                        >短信
                        <input type="checkbox" value="wechat" name="rmode[]"
                               @if(in_array('wechat',$rMode))
                               checked="checked"
                                @endif
                        >微信
                        <input type="checkbox" value="email" name="rmode[]"
                               @if(in_array('email',$rMode))
                               checked="checked"
                                @endif
                        >邮件
                    </li>
                    <li></li>
                    <li><span>抄送人：</span>
                        <div style="float: right;width: 85%;margin-top: 24px">
                            <div>
                                <span style="display: inline-block;font-size: 12px">选择部门：</span>
                                <div style="display: inline-block;width: 50%">
                                    <select class="form-control" data-name="depart"
                                            onchange="getStuffsTwo(this)"
                                            style="margin-left: 10px;">
                                        <option>请选择</option>
                                        @foreach($groups as $key=>$item)
                                            <option value="{{$key}}">{{$item['name']}}</option>
                                            @if(isset($item['child'])&&is_array($item['child']))
                                                @foreach($item['child'] as $k=>$value)
                                                    <option value="{{$k}}">&nbsp;&nbsp;&nbsp;&nbsp;{{$value}}</option>@endforeach
                                            @endif
                                        @endforeach
                                    </select></div>
                                <br>
                                <span style="font-size: 12px">选择成员：</span>
                                <div style="display: inline-block;width: 50%">
                                    <select id="chargerTwoId" class="form-control" name="chargerTwoId"
                                            style="margin-left: 10px;">
                                        <option value="">请选择</option>
                                    </select>
                                </div>
                                <a class="btn btn-xs btn-primary" style="margin-left: 10px"
                                   onclick="return false;" id="addusers">添加</a>
                            </div>
                            <div >
                                <span style="display: inline-block;font-size: 12px">已选人员：</span>
                                <select class="form-control" multiple=""
                                        style="display: inline-block;margin-left:10px;width: 50%" name="cuids">
                                    @foreach($ccIdsArray as $ccId)
                                        <option value="{{$ccId}}">
                                            {{\Itsm\Http\Helper\ThirdCallHelper::getStuffName($ccId)}}
                                        </option>
                                    @endforeach
                                </select>
                                <button class="btn btn-xs btn-primary" onclick="return false;"
                                        style="display: inline-block;" id="removeusers">移除
                                </button>
                            </div>
                        </div>
                    </li>
                    <li></li>
                    <li><span><span style="color: red">*</span>注意事项：</span>
                        <div style="width: 80%;float: right;margin-top: 12px">
                                <textarea style="height: 120px;width:180%" name="notes"
                                          class="validate">{{$handoverData->notes}}</textarea>
                        </div>
                    </li>
                    <li></li>
                </ul>
            </div>
        </div>
        <div class="job-record module-style" style="width: 98%;padding:0;">
            <div class="label-title">
                <span id="relatedEvents" class="title_active">相关事件<span class="label_line"></span></span>
                <div id="subEvent" class="btn btn-primary" style="float: right">添加事件</div>
            </div>
            <div>
                <div id="relatedEventsList" class="record-list">
                    <div>
                        <table id="relatedEventsTable" class="table-no-bordered event-table" style="width: 100%;"
                               style="text-align: center;color:#6b7d86;" bgcolor="#FFFFFF"
                               cellpadding="0"
                               cellspacing="0" width="100%"
                               data-pagination="true"
                               data-show-export="true"
                               data-page-size="10"
                               data-id-field="Id"
                               data-pagination-detail-h-align="right"
                               data-page-list="[10,20,30]"
                               data-show-footer="false"
                               data-side-pagination="server"
                               data-url="/handover/getEvents?handoverId={{$handoverData->id}}"
                               data-response-handler="responseHandler">
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div style="margin-left: 20%;margin-top: 5px">
            <a type="button" id="eventsumit"
               class="btn btn-primary mar_top20 btnSub">保存</a>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <a type="reset" class="btn btn-primary mar_top20" onclick="closeFrame()">取消</a>
        </div>
    </form>
</div>
<div id="enlargeImage" class="hide">
    <div class="img-wrap">
        <i id="closeLargeImg" class="img-close"></i>
        <img class="large-img" src=""/>
    </div>
</div>
<!-- 全局js -->
<script src="/js/jquery.min.js?v=2.1.4"></script>
<script src="/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script src="/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script src="/js/plugins/layer/layer.min.js"></script>
<script src="/js/plugins/bootstrap-table/bootstrap-table.js"></script>
<!-- put your locale files after bootstrap-table.js -->
<script src="/js/plugins/bootstrap-table/locale/bootstrap-table-zh-CN.js"></script>
<!-- 自定义js -->
<script src="/js/handover_edit.js?1"></script>
<script>
    <!--消息提示-->
    function lalert(txt){
        if(txt!='')
            layer.alert(txt, {icon: 2,closeBtn:false,area: '100px'});
    }
    $(function() {
        <!--TOKEN验证-->
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $("select[name='groupId']").on("change", function (event,uid) {
            var name = $(this).attr("name");
            var id = $(this).find("option:selected").val();
            var obj = $("select[name='chargerId']");
            obj.empty().append('<option value="">-请选择-</option>');
            if (id == "")return;
            $.ajax({
                type: "GET",
                url: "/handover/getDepStuffs?depId=" + id,
                success: function (arr) {
                    if (arr) {
                        for (var i = 0; i < arr.length; i++) {
                            obj.append('<option value="' + arr[i]['Id'] + '"'+(uid==arr[i]['Id']?"selected":"")+'>'+ arr[i]['Name'] + '</option>');
                        }
                    }
                }
            });
        });
        var gid = $("select[name='groupId']").find("option:selected").val();
        if (gid != '') {
            var uid = '{{$handoverData->chargerId}}';
            $("select[name='groupId']").trigger("change", [uid]);
        }
    });
    //选择添加成员
    $("#addusers").click(function () {
        var uid = $("[name='chargerTwoId']").val();
        var uname = $("[name='chargerTwoId'] option:selected").text();
        var ccIds = "";
        if (!uid) {
            lalert("请选择组成员");
            return false;
        }
        if ($("[name='cuids'] option[value='" + uid + "']").length)return false;
        if (uid == $('#chargerId').val()) {
            layer.alert('该人员已被选择为负责人', {tips: 2});
            return false;
        }
        $("[name='cuids']").append('<option value="' + uid + '">' + uname + '</option>');
        $("[name='cuids'] option").each(function () {
            ccIds += (ccIds != "" ? "," : "") + $(this).val();
        });
        $("input[name='ccIds']").val(ccIds);
    });
    <!--移除成员-->
    $("#removeusers").click(function () {
        var roption = $("[name='cuids'] option:selected"), uids = "";
        if(roption.length>0){
            $("[name='cuids'] option[value='"+roption.val()+"']").remove();
            $("[name='cuids'] option").each(function(){
                uids += (uids!=""?",":"")+$(this).val();
            });
            $("input[name='ccIds']").val(uids);
        }
    });

    var url = "{{env("JOB_URL")}}";
    var relatedEventsTable = $('#relatedEventsTable'),
            selections = [],
            data=[];

    function initTable() {//加载数据
        relatedEventsTable.bootstrapTable({
            pageSize: 10,
            striped: true,
            rowStyle: function (row, index) {
                if (row.isInValidate == 1 || row.status != 0) {
                    return {
                        css: {"color": "#CCCCCF"}
                    }
                }
                return {css: {"": ""}};
            },
            columns: [
                [{
                    field: 'id',
                    title: '事件编号',
                    valign: 'middle',
                    align: 'left',
                    width: '5%',
                    formatter: function (value, row, index) {
                        var s = '<a class="J_menuItem"  title="事件' + row.id + ' 详情"' + 'href="/handover/eventDetails/' + row.id +'">' + row.id + '</a>';
                        return s;
                    }
                },{
                    field: 'supportId',
                    title: '工单编号',
                    valign: 'middle',
                    align: 'left',
                    width: '5%',
                    formatter: function (value, row, index) {
                        if (row.supportId) {
                            return '<a class="J_menuItem" title="工单'+ row.supportId +'详情"' +
                                    'href="/wo/supportrefer/' + row.supportId + '">' + row.supportId + '</a>';
                        } else {
                            return '无';
                        }
                    }
                }, {
                    field: 'usName',
                    title: '客户名称',
                    valign: 'middle',
                    align: 'left',
                    width: '12%',
                    formatter: function (value, row, index) {
                        if (row.CusName) {
                            var cusName = row.CusName.length > 10 ? row.CusName.substr(0, 10) + '...' : row.CusName;
                            var s = '<a title="客户详情"' +
                                    'href="{{env('JOB_URL', 'http://www.51idc.cn')}}/crm/user/finance/customer_detail.html?cusinfid=' + row.cusId + '" target="_blank">' + cusName + '</a>';
                            return s;
                        } else {
                            return '无';
                        }
                    }
                }, {
                    field: 'type',
                    title: '事件类型',
                    valign: 'middle',
                    align: 'left',
                    width: '5%'
                }, {
                    field: 'priority',
                    title: '优先级',
                    valign: 'middle',
                    align: 'left',
                    width: '4%',
                    formatter: function (value, row, index) {
                        if (row.priority == 1) {
                            return '重要';
                        } else {
                            return '一般';
                        }
                    }
                }, {
                    field: 'status',
                    title: '状态',
                    valign: 'middle',
                    align: 'left',
                    width: '5%',
                    formatter: function (value, row, index) {
                        if (row.isInValidate == 1) {
                            return '已转移'
                        } else if (row.status == 0) {
                            return '未完成'
                        } else {
                            return '已完成'
                        }
                    }
                }, {
                    field: 'remindTs',
                    title: '预约时间',
                    valign: 'middle',
                    align: 'left',
                    width: '10%'
                }, {
                    field: 'chargerId',
                    title: '负责人',
                    valign: 'middle',
                    align: 'left',
                    width: '5%'
                }, {
                    field: 'notes',
                    title: '事件说明',
                    valign: 'middle',
                    align: 'left',
                    width: '12%',
                    formatter: function (value, row, index) {
                        var s = stringText(row.notes);
                        return s;
                    }
                }, {
                    field: 'submitterId',
                    title: '提交人',
                    valign: 'middle',
                    align: 'left',
                    width: '5%'
                }, {
                    title: '操作',
                    valign: 'middle',
                    align: 'center',
                    width: '8%',
                    formatter: function (value, row, index) {
                        if (row.status == 1 || row.isInValidate == 1) {
                            return '无';
                        } else {
                            return '<a class="remove" href="javascript:void(0)" title="删除">' +
                                    '<i class="fa fa-trash-o"></i></a>&nbsp;&nbsp;' +
                                    '<a class="eventEdit" href="javascript:void(0)" title="编辑">' +
                                    '<i class="fa fa-edit"></i></a>&nbsp;&nbsp;' +
                                    '<a class="eventTransfer" href="javascript:void(0)" title="转移">' +
                                    '<i class="fa fa-share"></i></a>&nbsp;&nbsp;' +
                                    '<a class="eventComplete" href="javascript:void(0)" title="完成">' +
                                    '<i class="fa fa-check-square-o"></i></a>';
                        }
                    },
                    events: 'operateEvents'
                }]
            ],
            queryParamsType: "undefined",
            queryParams: function queryParams(params) {
                return {
                    pageSize: params.pageSize,
                    pageNumber: params.pageNumber
                }
            }
        });

        relatedEventsTable.on('load-success.bs.table', function (e, value) {
            if(value.rows[0].isDone){
                layer.msg('所有事件已处理，交接单已完成！', {icon: 1},function(){
                    parent.$('.J_menuItem[menuname="全部交接单"]').click();
                    parent.$('.J_menuTab[data-id="/handover/handoverEdit/'+$('#handoverId').val()+'"]').remove();
                });
            }
        });

        layer.config({
            extend: 'extend/layer.ext.js'
        });
        window.operateEvents = {
            'click .remove': function (e, value, row, index) {
                var eventId = row.id;
                showDelete = layer.confirm('您确定要删除该事件吗?', {
                    title: "提示",
                    btn: ['确定', '取消']
                }, function () {
                    layer.prompt({
                        title: '请输入删除理由',
                        formType: 2
                    }, function (text) {
                        $.ajax({
                            type: "POST",
                            data: {'reason': text, 'eventId': eventId,'handoverId':$('#handoverId').val()},
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                            },
                            url: "/handover/eventDelete/" + eventId,
                            success: function (data) {
                                if (data.status == 'success') {
                                    layer.msg('删除事件成功！', {icon: 1});
                                    relatedEventsTable.bootstrapTable('refresh');
                                }
                            }
                        });
                    });
                });
            },
            'click .eventEdit': function (e, value, row, index) {
                var eventId = row.id;
                showEdit = layer.open({
                    type: 2,
                    title: '事件/编辑',
                    area: ['800px', '570px'],
                    content: ['/handover/eventEdit/' + eventId],
                    end: function () {
                        relatedEventsTable.bootstrapTable('refresh');
                    }
                });
            },
            'click .eventTransfer': function (e, value, row, index) {
                var eventId = row.id;
                showTransfer = layer.open({
                    type: 2,
                    title: '事件/转移',
                    area: ['860px', '520px'],
                    content: ['/handover/eventTransfer/' + eventId],
                    end: function () {
                        relatedEventsTable.bootstrapTable('refresh');
                    }
                });
            },
            'click .eventComplete': function (e, value, row, index) {
                if (row.feedback) {
                    var eventId = row.id;
                    showComplete = layer.confirm('事件完成后将不得修改，确定完成吗?', {
                        title: "提示",
                        btn: ['确定', '取消']
                    }, function (text) {
                        $.ajax({
                            type: "POST",
                            data: {'reason': text, 'eventId': eventId, 'handoverId': $('#handoverId').val()},
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                            },
                            url: "/handover/eventComplete/" + eventId,
                            success: function (data) {
                                if (data.status == 'success') {
                                    layer.msg('该事件已完成！', {icon: 1});
                                    relatedEventsTable.bootstrapTable('refresh');
                                }
                            }
                        });
                    });
                }
                else {
                    var confirmDex = layer.confirm('结果反馈为空，请填写！', {}, function () {
                        layer.prompt({
                            title: '请填写事件' + row.id + '结果反馈：',
                            formType: 2
                        }, function (text) {
                            $.ajax({
                                type: "POST",
                                data: {'feedback': text, 'eventId': row.id},
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                                },
                                url: "/handover/eventComplete/" + row.id,
                                success: function (data) {
                                    if (data.status == 'success') {
                                        layer.msg('该事件已完成！', {icon: 1});
                                        relatedEventsTable.bootstrapTable('refresh');
                                    }
                                }
                            });
                        });
                    });
                }
            }
        };
    }
    initTable();
    function responseHandler(res) {
        $.each(res.rows, function (i, row) {
            row.state = $.inArray(row.id, selections) !== -1;
        });
        return res;
    }
    function substringLen(text, length) {
        var length = arguments[1] ? arguments[1] : 16;
        suffix = "";
        if (text.length > length) {
            suffix = "..";
        }
        return  text.substr(0, length) + suffix;
    }

    $("#subEvent").click(function (value, row, index) {
        layer.open({
            type: 2,
            title: '新增交接单>新增事件',
            area: ['840px', '570px'],
            content: ['/handover/eventApply'],
            maxmin: true
        });
    });

    //交接单验证
    function validate(indexValidate) {
        if ($(this).hasClass("down-btn")) {
            validateMark = true;
            $('.btnSub').removeAttr('disabled');
            layer.close(indexValidate);
            return false;//防止重复提交
        }
        if (!validateMark) {
            $('.validate').each(function () {
                if ($(this).val() == '') {
                    layer.tips('请填写此项！', this, {time: 2000, tips: 2});
                    validateMark = true;
                    $('.btnSub').removeAttr('disabled');
                    layer.close(indexValidate);
                    return false;
                }
            });

            if(!$("input[type='checkbox']").is(':checked')){
                layer.tips('请填写此项！', $("input[type='checkbox']"), {time: 2000, tips: 3});
                validateMark = true;
                $('.btnSub').removeAttr('disabled');
                layer.close(indexValidate);
                return false;
            }
        }
    }


    //交接单编辑保存
    var validateMark = false;
    $('.btnSub').unbind();
    $('.btnSub').click(function () {
        $(this).attr('disabled', 'disabled');
        var indexValidate = layer.load(0, {shade: false});
        validateMark = false;
        validate(indexValidate);
        if (!validateMark) {
            var data = $('#relatedEventsTable').bootstrapTable('getData');
            if(data.length <1){
                layer.msg('请至少提交一个事件！', {icon: 2, time: 2000}, function(){
                    validateMark = true;
                    $('.btnSub').removeAttr('disabled');
                    layer.close(indexValidate);
                    return false;
                })
            }else{
                var handoverData=$('#myform').serializeArray(),formData={};
                formData['rmode'] = '';
                //将数据转化成符合要求的格式
                for(var i in handoverData){
                    var name = handoverData[i]['name'];
                    if(handoverData[i].value != undefined && name == 'rmode[]'){
                        formData['rmode'] += (formData['rmode']!=""?",":"")+handoverData[i].value;
                    }else{
                        formData[name] = handoverData[i].value;
                    }
                }
                //验证成功后保存数据
                $.ajax({
                    type: "POST",
                    data: {formData},
                    url: "/handover/editPush",
                    success: function (arr) {
                        if (arr.status==true) {
                            layer.msg(arr.msg, {icon: 1, time: 2000}, function () {
                                //保存数据成功刷新页面
                                layer.close(indexValidate);
                                location.reload();
                            });
                        } else {
                            layer.msg(arr.msg, {icon: 2, time: 2000}, function () {
                                //
                            });
                            validateMark = true;
                            $('.btnSub').removeAttr('disabled');
                            layer.close(indexValidate);
                            return false;
                        }
                    }
                });
            }
        }
    });
    $(function(){
        $(".record-list .clearfix").remove();
    })

    function closeFrame() {//关闭当前弹出层
        parent.$('.J_menuItem[menuname="待办交接单"]').click();
        parent.$('.J_menuTab[data-id="/handover/handoverEdit/'+$('#handoverId').val()+'"]').remove();
    }
</script>
</body>
</html>
