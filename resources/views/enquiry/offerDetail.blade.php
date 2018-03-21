<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    <title>安畅网络 询价申请</title>
    <!--[if lt IE 9]>
    <meta http-equiv="refresh" content="0;ie.html"/>
    <![endif]-->
    <link rel="shortcut icon" href="favicon.ico">
    <link href="/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/css/font-awesome.min.css?v=4.4.0" rel="stylesheet">
    <link href="/css/style.css?v=4.1.0" rel="stylesheet">
    <link href="/css/font.css" rel="stylesheet" type="text/css">

    <!-- 第三方插件 -->
    <link rel="stylesheet" href="/js/plugins/kindeditor/themes/default/default.css"/>
    <link rel="stylesheet" href="/js/plugins/kindeditor/plugins/code/prettify.css"/>
    <!-- 自定义css -->
    <link rel="stylesheet" type="text/css" href="/css/handover.css?{{\Itsm\Http\Helper\ThirdCallHelper::getTime()}}">
    <style>
        .table-edit, .table-edit td {
            border: 1px solid #fff;
            height: 20px;
            font-size: 14px;
        }

        .hiddenDiv {
            display: none;
        }

        .dcAttr {
            display: inline-block;
        }

        * {
            font-size: 12px;
        }

        .mr15 {
            margin-left: 15px;
            float: right;
        }

    </style>
</head>
<body>
<div>
    <div class="col-sm-12">
        <div class="ibox float-e-margins">
            <div id="mC1Q5" class="print_main">
                <div class="print_content" style="margin-top: -6px;border:0px;">
                    <table class="table-edit" style="color: #000000;" bgcolor="#FFFFFF" border="1"
                           cellpadding="0" cellspacing="0" width="600px">
                        <tbody>
                        <tr>
                            <td colspan="2">
                                <div style="float:left;height: 20px;font-weight: 700;">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td align="right">产品名称:
                            </td>
                            <td colspan="3">
                                <div style="margin-left: 10px;">
                                    @if($offer!="" && $offer->prodName){{$offer->prodName}}@endif
                                </div>
                            </td>
                        </tr>
                        <td colspan="2"></td>
                        <tr>
                            <td align="right">产品型号:</td>
                            <td colspan="3">
                                <div style="margin-left: 10px;">
                                    @if($offer!="" && $offer->prodPC){{$offer->prodPC}}@endif
                                </div>
                            </td>
                        </tr>
                        <td colspan="2"></td>
                        <tr>
                            <td align="right">数量:</td>
                            <td>
                                <div style="width:208px;margin-left: 10px;">
                                    @if($offer!="" && $offer->amount){{$offer->amount}}@endif
                                </div>
                            </td>
                            <td>售价:</td>
                            <td>
                                <div style="width:208px;margin-left: 10px;">
                                    @if($offer!="" && $offer->unitPrice){{$offer->unitPrice}}@endif
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3"></td>
                        </tr>
                        <tr>
                            <td align="right">产品描述:</td>
                            <td colspan="5">
                                <div style="width:450px;margin-left: 10px;">
                                    @if($offer!="" && $offer->describe){!! $offer->describe !!}@endif
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4"></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>