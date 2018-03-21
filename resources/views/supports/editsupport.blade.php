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
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .red {
            color: #fd0001
        }
    </style>
</head>

<body class="gray-bg">
<div class="animated" style='padding: 0 18px;'>
    <div class="row">
        <div class="float-e-margins">
            <div class="ibox-content">
                <form class="form-horizontal" id="myform">
                    <input type="hidden" name="sid" value="{{$data->Id}}"/>

                    <div class="row">
                        <div class="col-sm-6">
                            <div>
                                <label class="control-label form-inline"><span class="red">* </span>工单来源：</label>
                                                <span class="m-b-none form-inline"> 
                                                    <select class="form-control " name="usource">
                                                        <option value="">请选择</option>
                                                        @foreach(ThirdCallHelper::getDictArray("工单来源","supportSource") as $list)
                                                            <option value="{{$list->Code}}"
                                                                    @if ($data->Source==$list->Code) selected @endif>{{$list->Means}}</option>
                                                        @endforeach
                                                    </select>
                                                </span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div>
                                <label class="control-label form-inline"><span class="red">* </span>优先级：</label>
                                                <span class="m-b-none form-inline">
                                                    <select class="form-control" name="sorts"
                                                            style="margin-left:13px;width:189px;">
                                                        <option value="">请选择</option>
                                                        <option value="1" @if($data->priority==1) selected @endif>1
                                                        </option>
                                                        <option value="2" @if($data->priority==2) selected @endif>2
                                                        </option>
                                                        <option value="3" @if($data->priority==3) selected @endif>3
                                                        </option>
                                                    </select>
                                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div>
                                <label class="control-label form-inline"><span class="red">* </span>一级分类：</label>
                                                <span class="m-b-none form-inline"> 
                                                    <select class="form-control" name="model" style="width:117px;" id="chooseOneType">
                                                        @foreach(ThirdCallHelper::getDictArray("工单业务类型","serviceModel") as $item)
                                                            <option value="{{$item->Code}}" @if($data->ServiceModel==$item->Code) selected @endif>
                                                                {{$item->Means}}
                                                            </option>
                                                        @endforeach
                                                        {{--<option value="@if($data->ServiceModel=="IDC"|| $data->ServiceModel=="ACCloud")IDC
                                                        @elseif($data->ServiceModel !="IDC" && $data->ServiceModel !="ACCloud" ){{$data->ServiceModel}}@endif"
                                                                @if($data->ServiceModel=="IDC") selected @endif>IDC
                                                        </option>
                                                        <option value="ACCloud"
                                                                @if($data->ServiceModel=="ACCloud") selected @endif>安畅云
                                                        </option>--}}
                                                    </select>
                                                </span>

                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div>
                                <label class="control-label form-inline"><span class="red">* </span>三级分类：</label>
                                                <span class="m-b-none form-inline">
                                                        <select class="form-control" name="thirdclass"
                                                                style="width: 190px;">
                                                            <option value="">请选择</option>
                                                            @foreach(ThirdCallHelper::getDictArray("工单类型","WorksheetTypeOne") as $list)
                                                                <option value="{{$list->Code}}"
                                                                        @if($data->ClassInficationOne==$list->Code) selected @endif>{{$list->Means}}</option>
                                                            @endforeach
                                                        </select>
                                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div>
                                <label class="control-label"><span class="red">* </span>数据中心：</label>
                                        <span class="m-b-none form-inline">
                                                <select class="form-control" name="datacenter" id="chooseDataCenter" style="width:117px;">
                                                    <option value="">请选择</option>
                                                    @foreach(ThirdCallHelper::getDataCenter(($data->ServiceModel != "IDC"&&$data->ServiceModel != "ACCloud")?"IDC":$data->ServiceModel) as $list)
                                                        <option value="{{$list->DataCenterName}}"
                                                                @if($data->dataCenter==$list->DataCenterName) selected @endif>{{$list->DataCenterName}}</option>
                                                    @endforeach
                                                </select>
                                        </span>
                            </div>
                        </div>
                        @if($data['supportTag'] == "largearea-alarm")
                            <div class="col-sm-6">
                                <div>
                                    <label class="control-label">工单标签：</label>
                                        <span class="m-b-none form-inline">
                                                <select class="form-control" name="supportTag" style="display: inline-block;width: 190px;margin-left:5px">
                                                    <option value="">工单标签</option>
                                                    @foreach(ThirdCallHelper::getDictArray("工单标签","supportTag","unemail") as $list)
                                                        <option value="{{$list->Code}}" @if($data->supportTag == $list->Code) selected @endif>{{$list->Means}}</option>
                                                    @endforeach
                                                </select>
                                        </span>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="row">
                        <div class="col-sm-12" style="height:60px">
                            <label class="control-label"
                                   style="vertical-align: middle;height:95%;padding: 0 9px;">添加备注：</label>
                                 <span class="m-b-none"><textarea name="remark" class="form-control"
                                                                  style="width:82%;display: inline-block;margin-left: -8px;max-width: 82%;">{{$data['Memo']}}</textarea></span>
                        </div>
                    </div>
                </form>
                <div class="hr-line-dashed"></div>
                <div class="text-right">
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
                    <button class="btn btn-primary" id="editSave">保存修改</button>
                    <button class="btn btn-primary" id="cancel">取消</button>
                </div>
            </div>

        </div>
    </div>
</div>
</body>
<!-- 全局js -->
<script src="/js/jquery.min.js?v=2.1.4"></script>
<script src="/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/js/plugins/layer/layer.min.js"></script>
<!-- iCheck -->
<script src="/js/plugins/iCheck/icheck.min.js"></script>
<script>
    $("#chooseOneType").change(function () {
        var onchange = $.trim(this.value);
        //onchange = onchange != "ACCloud"?"IDC":onchange;
        $.ajax({
            'type':'get',
            'url':'/wo/getdatacenter/'+onchange,
            'dataType':'json',
            'success':function(data){
                var html='';
                $.each(data,function(commentIndex,comment){
                    html += "<option value="+comment.DataCenterName+">"+comment.DataCenterName+"</option>";
                })
                $("#chooseDataCenter").html(html);
            }
        })
    });
    <!--消息提示-->
    function lalert(txt) {
        if (txt != '')
            layer.alert(txt, {icon: 2, closeBtn: false, area: '100px'});
    }
    $(document).ready(function () {
        $('.i-checks').iCheck({
            checkboxClass: 'icheckbox_square-green',
            radioClass: 'iradio_square-green',
        });
    });
    $(function () {
        <!--TOKEN验证-->
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        <!--取消-->
        $("#cancel").click(function () {
            var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
            parent.layer.close(index); //再执行关闭
        });
        <!--保存-->
        $("#editSave").click(function () {
            if ($("select[name='usource']").val() == "") {
                lalert("请选择工单来源");
                return false;
            }
            if ($("select[name='sorts']").val() == "") {
                lalert("请选择优先级");
                return false;
            }
            if ($("select[name='model']").val() == "") {
                lalert("请选择一级分类");
                return false;
            }
            if ($("select[name='thirdclass']").val() == "") {
                lalert("请选择三级分类");
                return false;
            }
            if ($("select[name='datacenter']").val() == "") {
                lalert("请选择数据中心");
                return false;
            }
            if ($(this).hasClass("btn-default")) {
                return false;//防止重复提交
            }
            $("#processing").addClass("inline").removeClass("hide");
            $(this).removeClass("btn-primary").addClass("btn-default");
            $.ajax({
                type: "post",
                dataType: 'json',
                url: "/wo/postmsupport",
                data: $("#myform").serializeArray(),
                success: function (data) {
                    if (data && data.status) {
                        layer.msg('修改成功！', {
                            icon: 1,
                            time: 1000 //1秒关闭
                        }, function () {
                            var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                            parent.location.reload();
                            parent.layer.close(index); //再执行关闭
                        });
                    }
                }
            })
        });
    });
</script>
</html>

