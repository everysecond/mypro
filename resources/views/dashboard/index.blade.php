<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <title>安畅网络 RPMS系统V1.0</title>

    <!-- H+ -->
    <link href="render/hplus/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="render/hplus/css/bootstrap-table.min.css" rel="stylesheet">
    <link href="render/hplus/css/font-awesome.min.css?v=4.4.0" rel="stylesheet">
    <link href="render/hplus/css/animate.css" rel="stylesheet">
    <link href="render/hplus/css/style.css?v=4.1.0" rel="stylesheet">
    <style type="text/css">
        .nav {
            font-size: 13px;
        }

        .tips-red {
            border-radius: 20px;
            border: 1px solid #fd6262;
            background-color: #fd6262;
            color: white;
        }

        .nav .arrow {
            float: right;
        }

        .navbar-minimalize {
            position: absolute;
            top: 0;
            left: 0;
            width: 38px;
            height: 40px;
            line-height: 30px;
            margin: 0 0 0 0;
            border-radius: 0px;
        }

        .content-tabs .roll-left {
            left: 38px;
        }

        nav.page-tabs {
            margin-left: 78px;
        }

        #content-main {
            height: calc(100% - 42px);
        }
    </style>
</head>

<body class="fixed-sidebar full-height-layout gray-bg" style="overflow:hidden">
<div id="wrapper">
    <!--左侧导航开始-->
    <nav class="navbar-default navbar-static-side" role="navigation">
        <div class="nav-close"><i class="fa fa-times-circle"></i>
        </div>
        <div class="sidebar-collapse">
            <ul class="nav" id="side-menu">
                <li class="nav-header" style=" padding:0 0 5px 0;">
                    <div class="dropdown profile-element" style="background-color: #e2003b;padding: 12px 44px">
                        <a href="http://www.anchnet.com">
                            <img alt="anchnet.com中国领先的数据中心服务商" src="/img/anchnet_home_logo.svg" style="width: 120px">
                        </a>
                    </div>
                    <div class="logo-element"><img alt="anchnet.com中国领先的数据中心服务商" src="/img/anchnet-logo-small.svg"></div>
                </li>
                <li class="">
                    <a href="#" style="padding:10px 28px;">
                        <i class="fa fa-dashboard"></i> <span class="nav-label">RPMS
                            <span class="text-muted text-xs block" style="float: right">
                           <strong class="font-bold">
                                   @if(Session::has('user'))
                                       {{ Session::get('user')->LoginId }}
                                   @endif
                               </strong><b class="caret"></b>
                            </span>
                        </span>
                    </a>
                    <ul class="nav nav-second-level">
                        <li><a target="_blank" href="{{env('JOB_URL', 'http://www.51idc.cn')}}/crm">进入CRM系统</a>
                        </li>
                        <li><a target="_blank" href="{{env('JOB_URL', 'http://www.51idc.cn')}}/anchres">进入资源系统</a>
                        </li>
                        <li><a target="_blank" href="{{env('ITSM_URL', 'https://testitsm.anchnet.com')}}">进入ITSM系统</a>
                        </li>
                        <li class="divider"></li>
                        <li><a href="javascript:return;" class="logout">注销</a>
                        </li>
                    </ul>
                </li>
                {{-- 需要资源人员权限 --}}
                @if($menuRole['resources']==true)
                    <li>
                        <a class="J_menuItem" href="rpms/resourceProvider/providerList" menuname="供应商列表">
                            <i class="fa fa-tasks" style="margin-left: 2px"></i><span class="nav-label">供应商管理</span></a>
                    </li>
                    <li>
                        <a class="J_menuItem" href="rpms/resourceContract/contractList" menuname="合同列表">
                            <i class="fa fa-archive"></i> <span class="nav-label">合同管理</span></a>
                    </li>
                    <li>
                        <a class="J_menuItem" href="rpms/resourceBill/billList" menuname="账单列表">
                            <i class="fa fa-clipboard"></i> <span class="nav-label">账单管理</span></a>
                    </li>
                    <li>
                        <a href="#"><i class="fa fa-gears"></i> <span class="nav-label">系统配置</span><span
                                    class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li>
                                <a class="J_menuItem" href="rpms/resourceType/typeList" menuname="资源类型列表">
                                    <i class="fa fa-cubes"></i> <span class="nav-label">资源类型</span></a>
                            </li>
                            <li>
                                <a class="J_menuItem" href="rpms/resourceProd/prodList" menuname="资源产品列表">
                                    <i class="fa fa-cube"></i><span class="nav-label">&#160;资源产品</span></a>
                            </li>
                        </ul>
                    </li>
                @endif
                <li>
                    <a href="#"><i class="fa fa-user"></i> <span class="nav-label">个人信息管理</span><span
                                class="fa arrow"></span></a>
                    <ul class="nav nav-second-level">
                        <li><a class="J_menuItem" href="{{env('JOB_URL', 'http://www.51idc.cn')}}/crm/user/userinfoManage.html" menuname="个人信息修改">个人信息管理</a></li>
                        <li><a class="J_menuItem" href="{{env('JOB_URL', 'http://www.51idc.cn')}}/crm/user/passwordManage.html" menuname="修改密码">修改密码</a></li>
                        <li><a class="J_menuItem" href="{{env('JOB_URL', 'http://www.51idc.cn')}}/crm/user/assistApplication.html" menuname="协助申请单">协助申请单</a></li>
                        <li><a class="J_menuItem" href="{{env('JOB_URL', 'http://www.51idc.cn')}}/crm/user/wechat.html" menuname="绑定微信">绑定微信</a></li>
                        <li><a class="J_menuItem" href="{{env('JOB_URL', 'http://www.51idc.cn')}}/crm/user/callEngine.html" menuname="呼叫会议室">呼叫会议室</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
    <!--左侧导航结束-->
    <!--右侧部分开始-->
    <div id="page-wrapper" class="gray-bg dashbard-1">
        <div class="row content-tabs">
            <a class="navbar-minimalize minimalize-styl-2 btn" href="#"><i style="color:#8F95A2" class="fa fa-bars"></i> </a>
            <button class="roll-nav roll-left J_tabLeft"><i class="fa fa-backward"></i>
            </button>
            <nav class="page-tabs J_menuTabs">
                <div class="page-tabs-content">
                    <a href="javascript:;" class="active J_menuTab" data-id="dashboard/staff">Dashboard</a>
                </div>
            </nav>
            <button class="roll-nav roll-right J_tabRight"><i class="fa fa-forward"></i>
            </button>
            <div class="btn-group roll-nav roll-right">
                <button class="dropdown J_tabClose" data-toggle="dropdown">其他操作<span class="caret"></span>

                </button>
                <ul role="menu" class="dropdown-menu dropdown-menu-right">
                    <li class="J_tabShowActive"><a>定位当前选项卡</a></li>
                    <li class="J_tabCloseAll"><a>关闭全部选项卡</a></li>
                    <li class="J_tabCloseOther"><a>关闭其他选项卡</a></li>
                    <li><a target="_blank" href="{{env('JOB_URL', 'http://www.51idc.cn')}}/crm/">进入CRM系统</a>
                    </li>
                    <li><a target="_blank" href="{{env('JOB_URL', 'http://www.51idc.cn')}}/anchres/">进入资源系统</a>
                    </li>
                    <li><a target="_blank" href="{{env('ITSM_URL', 'https://testitsm.anchnet.com')}}">进入ITSM系统</a>
                    </li>
                </ul>
            </div>
            <a href="javascript:return;" class="roll-nav roll-right J_tabExit logout"><i class="fa fa fa-sign-out"></i> 注销</a>
        </div>
        <div class="row J_mainContent" id="content-main">
            <iframe class="J_iframe" name="iframe-dashboard/staff" width="100%" height="100%" src="dashboard/staff"
                    frameborder="0" data-id="dashboard/staff" seamless
                    onload="iframeOnload('dashboard/staff')"></iframe>
        </div>
    </div>
    <!--右侧部分结束-->
</div>

<!-- H+ -->
<script type="text/javascript" src="/render/hplus/js/jquery.min.js"></script>
<script type="text/javascript" src="/render/hplus/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/render/hplus/js/bootstrap-table.min.js"></script>
<script type="text/javascript" src="/render/hplus/js/plugins/metisMenu/jquery.metisMenu.js"></script>
<script type="text/javascript" src="/render/hplus/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script type="text/javascript" src="/render/hplus/js/plugins/layer/layer.min.js"></script>
<script type="text/javascript" src="/render/hplus/js/hplus.js"></script>
<script type="text/javascript" src="/render/hplus/js/plugins/slimscroll/jquery.slimscroll.min.js"></script>
<script type="text/javascript" src="/render/hplus/js/contabs.js"></script>
<script type="text/javascript" src="/render/hplus/js/plugins/pace/pace.min.js"></script>
<script type="text/javascript">
    var _topWin = window;
    while (_topWin != _topWin.parent.window) {
        _topWin = _topWin.parent.window;
    }
    if (window != _topWin)_topWin.document.location.href = location.href;

    window.onbeforeunload = function () {
        // return '提醒：刷新或关闭页面会丢失所有Tab';
    };

    $(".logout").click(function(){
        layer.confirm("确认退出？",function(){
            location.href = "logout";
        });
    })

    //获取当前登录用户头像
    function getMyHeadImage() {
        $.ajax({
            type: "GET",
            url: "/support/getMyHeadImage",
            success: function (data) {
                if (data) {
                    $("#headImage").attr('src', data);
                    $("#headImage").attr('class', 'img-circle');
                    $("#headImage").attr('style', 'width:64px;height: 64px');
                }
            }
        })
    }

    //获取待办工单即我要负责的所有未完成工单总数
    function getTodoNum() {
        $.ajax({
            type: "GET",
            url: "/support/getTodoNum",
            success: function (data) {
                if (data > 0) {
                    $('.tips-TodoNum').html(data);
                }
            }
        })
    }
    getTodoNum();
    getMyHeadImage();
    setInterval("getTodoNum()", 60000);
    $(".J_menuTab").dblclick(function () {
        getTodoNum();
    });

    //获取待办变更即我要负责的所有未完成变更总数
    function getToChangeNum() {
        $.ajax({
            type: "GET",
            url: "/change/toChangeNum",
            success: function (data) {
                if (data > 0) {
                    $('.tips-ToChangeNum').html(data);
                }
            }
        })
    }
    getToChangeNum();
    setInterval("getToChangeNum()", 60000);
    $(".J_menuTab").dblclick(function () {
        getToChangeNum();
    });

    //获取问题待办数量
    function getToIssueNum() {
        $.ajax({
            type: "GET",
            url: "/issue/toIssueNum",
            success: function (data) {
                if (data > 0) {
                    $('.tips-ToIssueNum').html(data);
                }
            }
        })
    }
    getToIssueNum();
    setInterval("getToIssueNum()", 60000);
    $(".J_menuTab").dblclick(function () {
        getToIssueNum();
    });
</script>
</body>
</html>
