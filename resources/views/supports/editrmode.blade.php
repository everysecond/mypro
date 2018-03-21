<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>安畅网络 ITSM系统V1.0</title>
    <link rel="shortcut icon" href="favicon.ico">
    <link href="/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/css/font-awesome.css?v=4.4.0" rel="stylesheet">
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
<div class=" animated">
    <div class="row">
        <div class="col-sm-12">
            <div class="float-e-margins">
                <div class="ibox-content">
                    <form class="form-horizontal" id="myform">
                        <input type="hidden" id="rmodeId" value="{{$data->Id}}"/>
                        <div class="row">
                            <div class="col-sm-7">
                                <div class="col-sm-10">
                                    <div>
                                        <label class="control-label form-inline"><span class="red">* </span>分组：</label>
                                                <span class="m-b-none form-inline">
                                                    <select class="form-control validate" value="" name="type"
                                                            style="display: inline;width: 30%">
                                                        <option value="">请选择</option>
                                                        @foreach($groupList as $type)
                                                            <option value="{{$type->Type}}"
                                                                    @if($data->Type==$type->Type)selected @endif>{{$type->Type}}</option>
                                                        @endforeach
                                                    </select>
                                                </span>
                                        <label class="control-label form-inline">工单类型：</label>
                                                <span class="m-b-none form-inline">
                                                    <select class="form-control" value="" name="supportType"
                                                            style="display: inline;width: 30%">
                                                        <option value="">请选择</option>
                                                        @foreach($typeList as $type)
                                                            <option value="{{$type->Code}}"
                                                                    @if($data->supportType==$type->Code)selected @endif>{{$type->Means}}</option>
                                                        @endforeach
                                                    </select>
                                                </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 10px">
                            <div class="col-sm-12">
                                <div class="col-sm-12">
                                    <div>
                                        <label class="control-label form-inline"><span class="red">* </span>标题：</label>
                                        <input class="form-control validate" style="display: inline;width: 85%"
                                               value="{{$data->Title}}" name="title" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="margin-top: 10px;height: 180px">
                            <div class="col-sm-12">
                                <div class="col-sm-12">
                                    <div>
                                        <label class="control-label form-inline"><span class="red">* </span>内容：</label>
                                        <textarea class="validate" value="" name="content"
                                                  style="width: 85%;height: 150px;margin-bottom: -135px;resize: none;">{{$data->Content}}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

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
                        <button class="btn btn-primary" id="distribute">保存</button>
                        <button class="btn btn-primary" id="cancel">取消</button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<!-- 全局js -->
<script src="/js/jquery.min.js?v=2.1.4"></script>
<script src="/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/js/plugins/layer/layer.min.js"></script>
<script>
    $(function () {
        <!--TOKEN验证-->
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        <!--消息提示-->
        function lalert(txt) {
            if (txt != '')
                layer.alert(txt, {icon: 2, closeBtn: false, area: '100px'});
        }

        <!--取消-->
        $("#cancel").click(function () {
            var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
            parent.layer.close(index); //再执行关闭
        });
        <!--保存-->
        var validateMark = true;
        $("#distribute").click(function () {
            $(".validate").each(function(){
                if($.trim($(this).val()) == ""){
                    layer.tips('请填写此项', this, {time: 2000, tips: 2});
                    validateMark = false;
                    return false;
                }
            });
            if(validateMark){
                $.ajax({
                    type: "GET",
                    url: "/support/rmodeEditPush/"+$("#rmodeId").val(),
                    data: $("#myform").serializeArray(),
                    success: function (data) {
                        if (data && data.status) {
                            layer.msg('保存成功！', {
                                icon: 1,
                                time: 1000 //1秒关闭
                            }, function () {
                                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                                parent.$('#rmodeTable').bootstrapTable('refresh');
                                parent.layer.close(index); //再执行关闭
                            });
                        }else if(!data.status){
                            layer.msg(data.statusMsg, {
                                icon: 2,
                                time: 1000 //1秒关闭
                            })
                        }
                    }
                });
            }

        });
    })

</script>
</body>