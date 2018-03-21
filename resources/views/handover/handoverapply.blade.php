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

        input[type=checkbox] {
            width: 25px;
        }
    </style>
</head>
<body>
<div class="job-detail clearfix" style="width: 98%;min-width: 720px;margin-left: 10px">
    <form id="myform">
        <p class="info-title" style="height: 32px;" id="detailsArea">新增交接单</p>
        <div class="job-record  module-style" style="width: 98%;margin-top: 0">
            <input type="hidden" name="ccIds" value=""/>
            {{--<input type="hidden" name="changeState" value="{{$change->changeState}}"/>
            <input type="hidden" id="changeType" name="changeType" value="{{$change->changeType}}"/>
            <input type="hidden" name="changeTitle" value="{{$change->changeTitle}}"/>
            <input type="hidden" name="feasibilityGroupId" value="{{$change->feasibilityGroupId}}"/>
            <input type="hidden" name="changeStateMeans"
                   value="{{ThirdCallHelper::getDictMeans('变更状态','changeState',$change->changeState)}}"/>--}}
            <div class="info-content">
                <ul>
                    <li><span style="color: red">*</span><span>转交部门：</span>
                        <div style="display: inline-block">
                            <select name="groupId" id="groupId" class="form-control validate"
                                    onchange="getStuff(this)"
                                    style="margin-left: 10px;">
                                <option value="">请选择</option>
                                @foreach($groups as $key=>$item)
                                    <option value="{{$key}}">{{$item['name']}}</option>
                                    @if(isset($item['child'])&&is_array($item['child']))
                                        @foreach($item['child'] as $k=>$value)
                                            <option value="{{$k}}">
                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$value}}</option>@endforeach
                                    @endif
                                @endforeach
                            </select></div>
                    </li>
                    <li><span style="color: red">*</span><span>分派任务负责人：</span>
                        <div style="display: inline-block">
                            <select name="chargerId" id="chargerId" class="form-control validate"
                                    style=";margin-left: 10px;">
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
                        <input type="checkbox" value="sms" name="rmode[]">短信
                        <input type="checkbox" value="wechat" name="rmode[]" checked="">微信
                        <input type="checkbox" value="email" name="rmode[]">邮件
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
                                                    <option value="{{$k}}">
                                                        &nbsp;&nbsp;&nbsp;&nbsp;{{$value}}</option>@endforeach
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
                            <div>
                                <span style="display: inline-block;font-size: 12px">已选人员：</span>
                                <select class="form-control" multiple=""
                                        style="display: inline-block;margin-left:10px;width: 50%" name="cuids">
                                </select>
                                <button class="btn btn-xs btn-primary" onclick="return false;"
                                        style="display: inline-block;" id="removeusers">移除
                                </button>
                            </div>
                        </div>
                    </li>
                    <li></li>
                    <li><span style="color: red">*</span><span>注意事项：</span>
                        <div style="width: 80%;float: right;margin-top: 12px">
                            <textarea style="height: 120px;width:180%" name="notes" class="validate"></textarea>
                        </div>
                    </li>
                    <li></li>
                </ul>
            </div>
        </div>
        <div class="job-record module-style" style="width: 98%;padding:0;">
            <div class="label-title">
                <span id="relatedEvents" class="title_active">相关事件<span class="label_line"></span></span>
                <div style="float: right">
                    <span style="color: red;font-size: 8px;margin-right: -35px">(*至少添加一个)</span>
                    <div id="subEvent" class="btn btn-primary">添加事件</div>
                </div>
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
                               data-side-pagination="client"
                               data-response-handler="responseHandler">
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div style="margin-left: 20%;margin-top: 5px">
            <input type="button" id="eventsumit"
               class="btn btn-primary mar_top20 btnSub" value="提交信息">
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

<script charset="utf-8" src="/js/plugins/kindeditor/kindeditor-min.js"></script>
<script charset="utf-8" src="/js/plugins/kindeditor/lang/zh_CN.js"></script>
<script src="/js/plugins/layer/laydate/laydate.js"></script>
<!-- 自定义js -->
<script src="/js/handover.js"></script>
<script>
    function lalert(txt) {
        if (txt != '')
            layer.alert(txt, {icon: 2, closeBtn: false, area: '100px'});
    }
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
        var roption = $("[name='cuids'] option:selected"), ccIds = "";
        if (roption.length > 0) {
            $("[name='cuids'] option[value='" + roption.val() + "']").remove();
            $("[name='cuids'] option").each(function () {
                ccIds += (ccIds != "" ? "," : "") + $(this).val();
            });
            $("input[name='ccIds']").val(ccIds);
        }
    });

    var url = "{{env("JOB_URL")}}";
    var relatedEventsTable = $('#relatedEventsTable'),
    //relateSupportTable = $('#relateSupportTable'),
            selections = [],
            data = [];

    function initTable() {//加载数据
        relatedEventsTable.bootstrapTable({
            data: data,
            pageSize: 20,
            striped: true,
            columns: [
                [{
                    field: 'supportId',
                    title: '工单编号',
                    valign: 'middle',
                    align: 'left',
                    width: '180px',
                    formatter: function (value, row, index) {
                        if (row.supportId) {
                            return '<a class="J_menuItem" title="工单'+ row.supportId +'详情"' +
                                    'href="/wo/supportrefer/' + row.supportId + '">' + row.supportId + '</a>';
                        } else {
                            return '无';
                        }
                    }
                }, {
                    field: 'cusname',
                    title: '客户名称',
                    valign: 'middle',
                    align: 'left',
                    width: '20%',
                    formatter: function (value, row, index) {
                        if (row.cusname) {
                            var cusName = row.cusname.length > 10 ? row.cusname.substr(0, 10) + '...' : row.cusname;
                            var s = '<a title="客户详情"' +
                                    'href="{{env('JOB_URL', 'http://www.51idc.cn')}}/crm/user/finance/customer_detail.html?cusinfid=' + row.cusId + '" target="_blank">' + cusName + '</a>';
                            return s;
                        } else {
                            return '无';
                        }
                    }
                }, {
                    field: 'status',
                    title: '状态',
                    valign: 'middle',
                    align: 'left',
                    width: '180px',
                    events: 'operateEvents'
                }, {
                    field: 'remindTs',
                    title: '预约时间',
                    valign: 'middle',
                    align: 'left',
                    width: '240px'
                }, {
                    field: 'typeName',
                    title: '事件类型',
                    valign: 'middle',
                    align: 'left',
                    width: '180px'
                }, {
                    field: 'charger',
                    title: '负责人',
                    valign: 'middle',
                    align: 'left',
                    width: '180px'
                }, {
                    title: '操作',
                    valign: 'middle',
                    align: 'left',
                    width: '180px',
                    formatter: function (value, row, index) {
                        var rem = '<a class="remove" href="javascript:void(0)" title="删除">' +
                                '<i class="glyphicon glyphicon-remove"></i>' +
                                '</a>&nbsp;&nbsp';
                        var edi = '<a class="edit" href="javascript:void(0)" title="编辑">' +
                                '<i class="glyphicon glyphicon-edit"></i>' +
                                '</a>';
                        return rem + edi;
                    },
                    events: 'operateEvents'
                }]
            ]
        });
        window.operateEvents = {
            'click .remove': function (e, value, row, index) {
                layer.confirm('确定要移除此事件吗？', {
                    btn: ['确定', '取消']
                }, function (index) {
                    relatedEventsTable.bootstrapTable('remove', {
                        field: 'Id',
                        values: [row.Id]
                    });
                    layer.close(index);
                })
            },
            'click .edit': function (e, value, row, index) {
                var edit = layer.open({
                    type: 2,
                    title: '事件/编辑',
                    area: ['840px', '570px'],
                    content: ['/handover/eventEdits', '#test'],
                    success: function () {
                        var sup = $('[scrolling="#test"]').contents();
                        sup.find("#notes").text(row.notes);
                        sup.find("#supportId").val(row.supportId);
                        sup.find("#support").val(row.supportId);
                        sup.find("#type").val(row.type);
                        sup.find("#typeName").val(row.typeName);
                        sup.find('input[name="remindTs"]').val(row.remindTs);
                        sup.find('input[name="remindType"][value="' + row.remindType + '"]').attr('checked', 'checked');
                        sup.find("#groupId").val(row.groupId);
                        if(row.groupId == 'second_dept_23'){
                            sup.find("#dcHidden").removeClass('hiddenDiv').addClass('dcAttr');
                            sup.find("#groupId").removeClass('groupAttr');
                            var dc = sup.find("select[name='dcGroup']");
                            dc.empty().append('<option value="">-数据中心-</option>');
                            $.ajax({
                                type: "GET",
                                url: "/handover/getDCDept",
                                success: function (arr) {
                                    if (arr) {
                                        for (var i = 0; i < arr.length; i++) {
                                            dc.append('<option value="' + arr[i]['UsersName'] + '"' + (row.dcGroup == arr[i]['UsersName'] ? "selected" : "") + '>' + arr[i]['UsersName'].replace("数据中心组", '') + '</option>');
                                        }
                                    }
                                    if (row.dcGroup != '') {
                                        sup.find("select[name='dcGroup']").trigger("change");
                                    }
                                }
                            });
                        }
                        else
                        {
                        sup.find("#dcHidden").removeClass('dcAttr').addClass('hiddenDiv');
                        sup.find("#groupId").addClass('groupAttr');
                        var obj = sup.find("#chargerId");
                        obj.empty().append('<option value="">-请选择-</option>');
                        var obj1 = sup.find("#chargerTwoId");
                        obj1.empty().append('<option value="">-请选择-</option>');
                            var ul = sup.find(".hiddenUl");
                            ul.empty();
                        $.ajax({
                            type: "GET",
                            url: "/handover/getDepStuffs?depId=" + row.groupId,
                            success: function (arr) {
                                if (arr) {
                                    for (var i = 0; i < arr.length; i++) {
                                        obj.append('<option value="' + arr[i]['Id'] + '">' + arr[i]['Name'] + '</option>');
                                        obj1.append('<option value="' + arr[i]['Id'] + '">' + arr[i]['Name'] + '</option>');
                                        ul.append('<li onclick="copystf(this)" value="' + arr[i]['Id'] + '">' + arr[i]['Name'] + '</li>');
                                    }
                                }
                                obj.val(row.chargerId);
                            }
                        });
                        }
                        sup.find("input[name='csIds']").val(row.csIds);
                        sup.find("input[name='csNames']").val(row.csNames);
                        var arr = row.csIds.split(","),
                                arrName = row.csNames.split(","),
                                csIdsObj = sup.find("#cuids");
                        for (var i in arr) {
                            csIdsObj.append('<option value="' + arr[i] + '">' + arrName[i] + '</option>');
                        }
                        sup.find("#charger").val(row.charger);
                        sup.find("#cusId").val(row.cusId);
                        sup.find("#cusname").attr('value', row.cusname);
                        sup.find("#cusName").val(row.cusname);
                        sup.find("#priority").val(row.priority);
                        sup.find("#index").val(index);
                        sup.find("select[name='dcGroup']").on("change", function () {
                            var obj = sup.find('#chargerId');
                            obj.empty().append('<option value="">-请选择-</option>');
                            var obj1 = sup.find('#chargerTwoId');
                            obj1.empty().append('<option value="">-请选择-</option>');
                            $.ajax({
                                type: "GET",
                                url: "/handover/getDepStuffs?depId=" + row.dcGroup,
                                success: function (arr) {
                                    if (arr) {
                                        for (var i = 0; i < arr.length; i++) {
                                            obj.append('<option value="' + arr[i]['Id'] + '"' + (row.chargerId == arr[i]['Id'] ? "selected" : "") + '>' + arr[i]['Name'] + '</option>');
                                            obj1.append('<option value="' + arr[i]['Id'] + '">' + arr[i]['Name'] + '</option>');
                                        }
                                    }
                                    obj.val(row.chargerId);
                                }
                            });
                            sup.find("select[name='dcGroup']").unbind();
                        });
                    }
                });
            },
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
        return text.substr(0, length) + suffix;
    }

    $("#subEvent").click(function (value, row, index) {
        layer.open({
            type: 2,
            title: '新增交接单>新增事件',
            area: ['840px', '570px'],
            content: ['/handover/newEvent'],
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

            if (!$("input[type='checkbox']").is(':checked')) {
                layer.tips('请填写此项！', $("input[type='checkbox']"), {time: 2000, tips: 3});
                validateMark = true;
                $('.btnSub').removeAttr('disabled');
                layer.close(indexValidate);
                return false;
            }
        }
    }


    //交接单提交
    var validateMark = false;
    $('.btnSub').unbind();
    $('.btnSub').click(function () {
        $(this).attr('disabled', 'disabled');
        var indexValidate = layer.load(0, {shade: false});
        validateMark = false;
        validate(indexValidate);
        if (!validateMark) {
            var data = $('#relatedEventsTable').bootstrapTable('getData');
            if (data.length < 1) {
                layer.msg('请至少提交一个事件！', {icon: 2, time: 2000}, function () {
                    validateMark = true;
                    $('.btnSub').removeAttr('disabled');
                    layer.close(indexValidate);
                    return false;
                })
            } else {
                var handoverData = $('#myform').serializeArray(), formData = {};
                formData['rmode'] = '';
                //将数据转化成符合要求的格式
                for (var i in handoverData) {
                    var name = handoverData[i]['name'];
                    if(handoverData[i].value != undefined && name == 'rmode[]'){
                        formData['rmode'] += (formData['rmode']!=""?",":"")+handoverData[i].value;
                    }else{
                        formData[name] = handoverData[i].value;
                    }
                }
                //验证成功后提交所有数据生成交接单
                $.ajax({
                    type: "POST",
                    data: {
                        formData,
                        "eventsData": data
                    },
                    url: "/handover/handoverSub",
                    success: function (arr) {
                        if (arr.status == true) {
                            layer.msg(arr.msg, {icon: 1, time: 2000}, function () {
                                //提交成功跳转列表页面
                                layer.close(indexValidate);
                                parent.$('.J_menuItem[menuname="待办交接单"]').click();
                                parent.$('.J_menuTab[data-id="/handover/handoverApply"]').remove();
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
    $(function () {
        $(".record-list .clearfix").remove();
    })
    function closeFrame() {//关闭当前弹出层
        parent.$('.J_menuItem[menuname="待办交接单"]').click();
        parent.$('.J_menuTab[data-id="/handover/handoverApply"]').remove();
    }
</script>
</body>
</html>
