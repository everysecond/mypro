<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/logout', 'User@logout');
Route::get('test', 'Supports\UserSupportController@test');//测试用
Route::group([], function () {
    Route::get('/', function () {
        return redirect('/home');
    });
    //index page
    Route::get('home', 'Dashboard\Index@home');
    //人员管理
    Route::get('member', 'Dashboard\Index@member');
    Route::get('memberData', 'Dashboard\Index@getMemberData');
    Route::get('getSecondDept', 'Dashboard\Index@getSecondDept');
    //dashboard相关
    Route::group(['prefix' => 'dashboard'], function () {
        Route::get('admin', 'Dashboard\Index@admin');
        Route::get('staff', 'Dashboard\Index@staff');
        Route::get('refresh/{source}', 'Dashboard\Index@refresh');

        Route::get('supportByNotDone', 'Dashboard\Index@supportByNotDone');
        Route::get('supportByMyNotDone', 'Dashboard\Index@supportByMyNotDone');
        Route::get('supportByYear', 'Dashboard\Index@supportByYear');

        Route::get('supportByEvaluate', 'Dashboard\Index@supportByEvaluate');

        Route::get('getChargeGroup', 'Dashboard\Index@getChargeGroup');
        Route::get('getStuffSupports', 'Dashboard\Index@getStuffSupports');
        Route::get('getSupportsByThisMonth', 'Dashboard\Index@getSupportsByThisMonth');
        Route::get('getSupportsByPreMonth', 'Dashboard\Index@getSupportsByPreMonth');

        Route::get('/', 'Dashboard\Index@main');

    });
    //工单相关
    Route::group(['prefix' => 'support'], function () {
        //获取微信头像
        Route::get('getMyHeadImage', 'Supports\SupportsController@getMyHeadImage');
        Route::get('getCusInf/{id}', 'Supports\SupportsController@getCusInf');//获取客户信息

        //工单快照
        Route::get('evaReport', 'Supports\SupportsController@getEvaReport');//获取工单满意度页面
        Route::get('getEvaList', 'Supports\SupportsController@getEvaList');//获取工单满意度数据
        Route::get('comReport', 'Supports\SupportsController@getComReport');//获取完成超时页面
        Route::get('getComList', 'Supports\SupportsController@getComList');//获取完成超时数据
        Route::get('repReport', 'Supports\SupportsController@getRepReport');//获取响应超时页面
        Route::get('getRepList', 'Supports\SupportsController@getRepList');//获取响应超时数据
        Route::get('sucReport', 'Supports\SupportsController@getSucReport');//获取成功率页面
        Route::get('getSucList', 'Supports\SupportsController@getSucList');//获取成功率数据
        Route::get('supportKZList', 'Supports\SupportsController@supportKZList');//获取快照页面
        Route::get('getSupportKZData', 'Supports\SupportsController@getSupportKZData');//获取快照数据

        //工单分析
        Route::match(['get', 'post'],'analyze/{type}', 'Supports\SupportsController@getAnalyzeTicket');

        //todos list
        Route::get('todoList', 'Supports\SupportsController@todoList');
        Route::get('getTodoList', 'Supports\SupportsController@getTodoList');
        Route::get('getTodoNum', 'Supports\SupportsController@getTodoNum');

        //operate list
        Route::get('operateList', 'Supports\SupportsController@operateList');
        Route::get('getOperateList', 'Supports\SupportsController@getOperateList');
        Route::get('exportAllList', 'Supports\SupportsController@exportAllList');
        Route::get('exportModeList', 'Supports\SupportsController@exportModeList');

        Route::get('updateGrade', 'Supports\SupportsController@updateGrade');//升级工单


        //all list
        Route::get('allList', 'Supports\SupportsController@allList');
        Route::get('getAllList', 'Supports\SupportsController@getAllList');

        //collection list
        Route::get('collectionList', 'Supports\SupportsController@collectionList');
        Route::get('getCollectionList', 'Supports\SupportsController@getCollectionList');
        Route::post('addCollection', 'Supports\SupportsController@addCollection');
        Route::post('delCollection', 'Supports\SupportsController@delCollection');
        Route::get('getCollectionNote/{id}', 'Supports\SupportsController@getCollectionNote');
        Route::match(['get', 'post'],'editReason', 'Supports\SupportsController@editReason');

        //search list
        Route::get('searchList', 'Supports\SupportsController@searchList');
        Route::get('statisticList', 'Supports\SupportsController@statisticList');
        Route::get('getSearchList', 'Supports\SupportsController@getSearchList');
        Route::get('getStatisticList', 'Supports\SupportsController@getStatisticList');
        Route::get('exportStaList', 'Supports\SupportsController@exportStaList');

        //support detail
        Route::get('detail', 'Supports\SupportsController@detail');
        //get timeout count
        Route::get('getOverTimeNum', 'Supports\SupportsController@getOverTimeNum');

        //get todoCount
        Route::get('getTodoCount', 'Supports\SupportsController@getTodoCount');

        //batch closed email support
        Route::post('batchCloseMailSupport', 'Supports\SupportsController@batchCloseMailSupport');
        Route::post('batchDoneSupport', 'Supports\SupportsController@batchDoneSupport');
        Route::post('batchReplySupport', 'Supports\SupportsController@batchReplySupport');

        //batch answer email support
        Route::post('batchAnswerMailSupport', 'Supports\SupportsController@batchAnswerMailSupport');
        //quite answer
        Route::get('speedAnswer', 'Supports\SupportsController@speedAnswer');
        //清除全部缓存
        Route::get('cleanCache', 'Supports\SupportsController@cleanCache');
        //清除人员列表缓存
        Route::get('cleanMemCache', 'Supports\SupportsController@cleanMemCache');

        Route::match(['get', 'post'], 'create', 'Supports\SupportsController@create');
        Route::match(['get', 'post'], 'supportSplit', 'Supports\SupportsController@supportSplit');
        Route::match(['get', 'post'], 'createSubmit', 'Supports\SupportsController@createSubmit');
        Route::match(['get', 'post'], 'selectEquipment', 'Supports\SupportsController@selectEquipment');
        Route::match(['get', 'post'], 'getEquipmentList', 'Supports\SupportsController@getEquipmentList');

        //相关变更
        Route::match(['get', 'post'], 'getRelateChange', 'Supports\SupportsController@getRelateChange');
        Route::match(['get', 'post'], 'relateChange', 'Supports\SupportsController@relateChange');
        Route::match(['get', 'post'], 'relateChangeData', 'Supports\SupportsController@relateChangeData');
        //相关问题
        Route::match(['get', 'post'], 'getRelateIssue', 'Supports\SupportsController@getRelateIssue');
        Route::match(['get', 'post'], 'relateIssue', 'Supports\SupportsController@relateIssue');
        Route::match(['get', 'post'], 'relateIssueData', 'Supports\SupportsController@relateIssueData');

        //custom and contacts list
        Route::get('CusContactList', 'Supports\SupportsController@CusContactList');
        Route::get('sameSupport', 'Supports\SupportsController@sameSupport');

        //模板list
        Route::get('rmodeList', 'Supports\SupportsController@rmodeList');
        Route::match(['get', 'post'], 'getrmodeListData', 'Supports\SupportsController@getrmodeListData');
        Route::match(['get', 'post'], 'rmodeDelete/{Id}', 'Supports\SupportsController@rmodeDelete');
        Route::match(['get', 'post'], 'rmodeEdit/{Id}', 'Supports\SupportsController@rmodeEdit');
        Route::match(['get', 'post'], 'rmodeEditPush/{Id}', 'Supports\SupportsController@rmodeEditPush');
        Route::match(['get', 'post'], 'newRmode', 'Supports\SupportsController@newRmode');
        Route::match(['get', 'post'], 'newRmodePush', 'Supports\SupportsController@newRmodePush');
    });

    Route::get('customer/cusDetail/{id}', 'Customers\CustomerController@cusDetail');


});
Route::group(['prefix' => 'wo'], function () {
    Route::get('supportrefer/{id}', 'Supports\UserSupportController@supportRefer');//工单操作
    Route::get('supportreferold/{id}', 'Supports\UserSupportController@supportRefer');//测试用

    Route::get('editsupport/{id}', 'Supports\UserSupportController@editSupport');//工单修改
    Route::post('postmsupport', 'Supports\UserSupportController@postMainSupport');//工单相关项数据修改

    Route::get('optusers/{id}', 'Supports\UserSupportController@optUsers');//查找对应工作组操作人
    Route::get('getdatacenter/{type}', 'Supports\UserSupportController@getDataCenter');//查找不同类型的数据中心
    Route::post('csupport', 'Supports\UserSupportController@postSupport');//指派工单
    Route::get('reassign/{id}', 'Supports\UserSupportController@reassign');//重新指派
    Route::post('getspts/{id}', 'Supports\UserSupportController@getSpTs');//重新指派前先拿到上次指派时间判断是否在两分钟内，若是两分钟内给出提示
    Route::post('preassign', 'Supports\UserSupportController@postReassign');//重新指派回传

    Route::get('alreadyproc/{id}', 'Supports\UserSupportController@alreadyProc');//工单已处理
    Route::post('sureproc', 'Supports\UserSupportController@sureProc');//工单确认已处理

    Route::post('reply', 'Supports\UserSupportController@replyMsg');//回复消息
    Route::post('getreplymode', 'Supports\UserSupportController@getReplyMode');//回复消息

    Route::post('surereply/{id}', 'Supports\UserSupportController@sureReply');//确认回复
    Route::post('delreply/{id}', 'Supports\UserSupportController@delReply');//删除回复
    Route::get('editreply/{id}', 'Supports\UserSupportController@editReply');//编辑回复
    Route::post('peditreply', 'Supports\UserSupportController@postEditReply');//确认回复（审核）
    Route::post('prescind/{id}', 'Supports\UserSupportController@postRescind');//撤回消息
    Route::post('sureappoint/{id}', 'Supports\UserSupportController@sureAppoint');//撤回消息

    Route::get('sendsms/{id}', 'Supports\UserSupportController@sendSms');//发送短信
    Route::post('postsms', 'Supports\UserSupportController@postSms');//发送短信回传
    Route::get('sendEmail', 'Supports\UserSupportController@sendEmail');//发送邮件blade
    Route::post('postEmail', 'Supports\UserSupportController@postEmail');//发送邮件回传
    Route::get('onCall', 'Supports\UserSupportController@onCall');//拨打电话

    Route::get('hangup/{id}', 'Supports\UserSupportController@hangUp');//工单挂起
    Route::post('posthangup', 'Supports\UserSupportController@postHangUp');//工单挂起回传
    Route::post('release/{id}', 'Supports\UserSupportController@postRelease');//工单释放挂起回传

    Route::post('postquota/{id}', 'Supports\UserSupportController@postQuota');//配额审核通过

    Route::get('cloud/{id}', 'Supports\UserSupportController@cloud');//云列表

    Route::get('jsonConfig/{id}', 'Supports\UserSupportController@jsonConfig');//云列表
});
Route::post('kindeditor/uploadify', 'Kindeditor\UploadController@uploadify');
Route::post('kindeditor/uploadfile', 'Kindeditor\UploadController@uploadfile');
Route::post('kindeditor/uploadExcel', 'Kindeditor\UploadController@uploadExcel');
Route::post('kindeditor/uploadProvider', 'Kindeditor\UploadController@uploadProvider');
Route::post('kindeditor/uploadTypeAndProd', 'Kindeditor\UploadController@uploadTypeAndProd');


//变更管理路由
Route::group(['prefix' => 'change'], function () {
    //变更申请单
    Route::match(['get', 'post'], 'changerefer', 'Change\ChangeController@changeRefer');
    Route::match(['get', 'post'], 'changepush', 'Change\ChangeController@pushApply');//申请提交
    //待办变更列表
    Route::match(['get', 'post'], 'todolist', 'Change\ChangeController@todoList');//待办变更列表模板
    Route::match(['get', 'post'], 'todoListData', 'Change\ChangeController@getToDoListData');//待办变更列表数据接口
    Route::match(['get', 'post'], 'toChangeNum', 'Change\ChangeController@getToChangeNum'); //待办变更条数
    Route::match(['get', 'post'], 'getTodoTimeChanged', 'Change\ChangeController@getTodoTimeChanged'); //待办变更超时数据
    //可行性审批
    Route::match(['get', 'post'], 'feasibility', 'Change\ChangeController@getFeasibility');
    Route::match(['get', 'post'], 'feasiblepush', 'Change\ChangeDetailsController@pushFeasible');
    //相关变更列表
    Route::match(['get', 'post'], 'myList', 'Change\ChangeController@myList');//相关变更列表模板
    Route::match(['get', 'post'], 'getMyList', 'Change\ChangeController@getMyList');//列表数据接口
    //所有变更列表
    Route::match(['get', 'post'], 'allList', 'Change\ChangeController@allList');//相关变更列表模板
    Route::match(['get', 'post'], 'getAllList', 'Change\ChangeController@getAllList');//列表数据接口
    Route::match(['get', 'post'], 'getTimeChanged', 'Change\ChangeController@getTimeChanged');//获取超时数据
    //变更详情
    Route::match(['get', 'post'], 'details', 'Change\ChangeDetailsController@changeDetails');
    Route::match(['get', 'post'], 'flowChart', 'Change\ChangeDetailsController@flowChart');


    Route::match(['get', 'post'], 'details/{id}', 'Change\ChangeDetailsController@changeDetails');
    Route::match(['get', 'post'], 'getDepStuffs', 'Change\ChangeDetailsController@getDepStuffs');
    Route::match(['get', 'post'], 'saveToapply/{id}',
        'Change\ChangeDetailsController@saveToApply');//可行性审批不通过状态为待申请可读取数据
    Route::match(['get', 'post'], 'saveToapplydata',
        'Change\ChangeDetailsController@saveToapplyData');//可行性审批不通过状态为待申请可读取数据
    Route::match(['get', 'post'], 'saveFeasibility', 'Change\ChangeDetailsController@saveFeasibility');//可行性审批
    Route::match(['get', 'post'], 'saveProgramme', 'Change\ChangeDetailsController@saveProgramme');//方案规划
    Route::match(['get', 'post'], 'saveProdesign', 'Change\ChangeDetailsController@saveProdesign');//方案制定
    Route::match(['get', 'post'], 'saveTesting', 'Change\ChangeDetailsController@saveTesting');//测试
    Route::match(['get', 'post'], 'saveExamining', 'Change\ChangeDetailsController@saveExamining');//测试复核
    Route::match(['get', 'post'], 'saveImplement', 'Change\ChangeDetailsController@saveImplement');//变更实施
    Route::match(['get', 'post'], 'saveVerificating', 'Change\ChangeDetailsController@saveVerificating');//验证结果
    //相关问题
    Route::match(['get', 'post'], 'getRelateIssue', 'Change\ChangeController@getRelateIssue');
    Route::match(['get', 'post'], 'relateIssue', 'Change\ChangeController@relateIssue');
    Route::match(['get', 'post'], 'relateIssueData', 'Change\ChangeController@relateIssueData');
    //相关工单
    Route::match(['get', 'post'], 'getRelateSupport', 'Change\ChangeController@getRelateSupport');
    Route::match(['get', 'post'], 'relateSupport', 'Change\ChangeController@relateSupport');
    Route::match(['get', 'post'], 'relateSupportData', 'Change\ChangeController@relateSupportData');
});

//问题管理路由
Route::group(['prefix' => 'issue'], function () {
    //问题申请单
    Route::match(['get', 'post'], 'issueapply', 'Issue\IssueController@issueApply');
    Route::match(['get', 'post'], 'issuepush', 'Issue\IssueController@pushApply');
    //问题列表
    Route::match(['get', 'post'], 'todolist', 'Issue\IssueController@todoList');//待办问题列表
    Route::match(['get', 'post'], 'toIssueNum', 'Issue\IssueController@getToIssueNum'); //待办问题条数
    Route::match(['get', 'post'], 'todoListData', 'Issue\IssueController@getToDoListData');//待办变更列表数据接口
    Route::match(['get', 'post'], 'myList', 'Issue\IssueController@myList');//相关变更列表模板
    Route::match(['get', 'post'], 'getMyList', 'Issue\IssueController@getMyListData');//相关列表数据接口
    Route::match(['get', 'post'], 'allList', 'Issue\IssueController@allList');//所有变更列表模板
    Route::match(['get', 'post'], 'getAllList', 'Issue\IssueController@getAllListData');//所有列表数据接口
    //问题详情
    Route::match(['get', 'post'], 'saveToapply/{id}', 'Issue\IssueDetailsController@saveToApply');//问题审核不通过驳回
    Route::match(['get', 'post'], 'saveToapplydata', 'Issue\IssueDetailsController@saveToapplyData');//问题驳回处理
    Route::match(['get', 'post'], 'details/{id}', 'Issue\IssueDetailsController@IssueDetails');
    Route::match(['get', 'post'], 'saveApproval', 'Issue\IssueDetailsController@saveApproval');
    Route::match(['get', 'post'], 'saveAnalysis', 'Issue\IssueDetailsController@saveAnalysis');
    Route::match(['get', 'post'], 'saveCheck', 'Issue\IssueDetailsController@saveCheck');
    Route::match(['get', 'post'], 'saveClose', 'Issue\IssueDetailsController@saveClose');
    Route::match(['get', 'post'], 'flowChart', 'Issue\IssueDetailsController@flowChart');//问题流程图
    //相关变更
    Route::match(['get', 'post'], 'getRelateChange', 'Issue\IssueController@getRelateChange');
    Route::match(['get', 'post'], 'relateChange', 'Issue\IssueController@relateChange');
    Route::match(['get', 'post'], 'relateChangeData', 'Issue\IssueController@relateChangeData');
    //相关工单
    Route::match(['get', 'post'], 'getRelateSupport', 'Issue\IssueController@getRelateSupport');
    Route::match(['get', 'post'], 'relateSupport', 'Issue\IssueController@relateSupport');
    Route::match(['get', 'post'], 'relateSupportData', 'Issue\IssueController@relateSupportData');

});

Route::post("correlation/create", "Correlation\CorrelationController@create");
Route::post("correlation/closeChangeToIssue", "Correlation\CorrelationController@closeChangeToIssue");
Route::post("correlation/closeChangeToSupport", "Correlation\CorrelationController@closeChangeToSupport");
Route::post("correlation/closeIssueToChange", "Correlation\CorrelationController@closeIssueToChange");
Route::post("correlation/closeIssueToSupport", "Correlation\CorrelationController@closeIssueToSupport");
Route::post("correlation/closeSupportToChange", "Correlation\CorrelationController@closeSupportToChange");
Route::post("correlation/closeSupportToIssue", "Correlation\CorrelationController@closeSupportToIssue");
Route::post("correlation/batchSupportChange", "Correlation\CorrelationController@batchSupportChange");
Route::post("correlation/batchSupportIssue", "Correlation\CorrelationController@batchSupportIssue");
Route::post("correlation/batchChangeSupport", "Correlation\CorrelationController@batchChangeSupport");
Route::post("correlation/batchChangeIssue", "Correlation\CorrelationController@batchChangeIssue");
Route::post("correlation/batchIssueSupport", "Correlation\CorrelationController@batchIssueSupport");
Route::post("correlation/batchIssueChange", "Correlation\CorrelationController@batchIssueChange");

Route::get("process/create", "Change\ProcessController@create");
Route::get("process/nextCase", "Change\ProcessController@nextCase");
Route::get("process/nextCaseWithVariable", "Change\ProcessController@nextCaseWithVariable");
Route::get("process/caseInfo", "Change\ProcessController@caseInfo");
Route::get("process/getCaseVariables", "Change\ProcessController@getCaseVariables");
Route::get("process/getTodoCase", "Change\ProcessController@getTodoCase");
Route::get("process/getParticipatedCases", "Change\ProcessController@getParticipatedCases");
Route::get("process/getDraftCases", "Change\ProcessController@getDraftCases");
Route::get("process/getAllCases", "Change\ProcessController@getAllCases");
Route::get("process/demo", "Change\ProcessController@demo");

//交接管理路由
Route::group(['prefix' => 'handover'], function () {
    //事件路由
    Route::match(['get', 'post'], 'eventApply', 'Handover\HandoverController@eventApply');//新增事件
    Route::post('eventPush', 'Handover\HandoverController@eventPush');//新增事件提交
    Route::match(['get', 'post'], 'eventEdit/{id}', 'Handover\HandoverDetailsController@eventEdit');//编辑事件
    Route::match(['get', 'post'], 'eventEdits', 'Handover\HandoverDetailsController@eventEdits');//编辑事件
    Route::post('eventEditPush/{id}', 'Handover\HandoverDetailsController@eventEditPush');//编辑事件提交
    Route::match(['get', 'post'], 'eventDelete/{id}', 'Handover\HandoverDetailsController@eventDelete');//删除事件
    Route::match(['get', 'post'], 'eventTransfer/{id}', 'Handover\HandoverDetailsController@eventTransfer');//转移事件
    Route::match(['get', 'post'], 'createTransfer', 'Handover\HandoverDetailsController@createTransfer');//转移
    Route::match(['get', 'post'], 'getHandoverList', 'Handover\HandoverDetailsController@getHandoverList');//获取待转移的交接单
    Route::match(['get', 'post'], 'eventComplete/{id}', 'Handover\HandoverDetailsController@eventComplete');//完成事件
    Route::get('getDepStuffs', 'Handover\HandoverController@getDepStuffs');//查找负责人
    Route::get('getDCDept', 'Handover\HandoverController@getDCDept');//查找数据中心组
    Route::get('eventTodoList', 'Handover\HandoverController@eventTodoList');//待办事件列表
    Route::get('eventAllList', 'Handover\HandoverController@eventAllList');//所有事件列表
    Route::get('getEventTodoList', 'Handover\HandoverController@getEventTodoListData');//读取待办事件列表数据
    Route::get('getEventAllList', 'Handover\HandoverController@getEventAllListData');//读取所有事件列表数据
    Route::match(['get', 'post'], 'eventDetails/{id}', 'Handover\HandoverDetailsController@eventDetails');//事件详情
    //新增交接单
    Route::match(['get', 'post'], 'handoverApply', 'Handover\HandoverController@handoverApply');//新增交接单模板
    Route::match(['get', 'post'], 'handoverSub', 'Handover\HandoverController@handoverSub');//新增交接单提交
    Route::match(['get', 'post'], 'newEvent', 'Handover\HandoverController@newEvent');//新增事件提交暂存 模板
    //待办交接单
    Route::match(['get', 'post'], 'todoList', 'Handover\HandoverController@todoList');//待办交接单模板
    Route::match(['get', 'post'], 'allList', 'Handover\HandoverController@allList');//全部交接单数据
    Route::match(['get', 'post'], 'handoverEdit/{id}', 'Handover\HandoverController@handoverEdit');//交接单编辑模板
    Route::match(['get', 'post'], 'getEvents', 'Handover\HandoverController@getEvents');//获取交接单对应所有事件
    Route::match(['get', 'post'], 'editPush', 'Handover\HandoverController@editPush');//交接单编辑提交

    //全部交接单
    Route::match(['get', 'post'], 'todoListData', 'Handover\HandoverController@todoListData');//全部交接单模板
    Route::match(['get', 'post'], 'allListData', 'Handover\HandoverController@allListData');//全部交接单数据
    Route::match(['get', 'post'], 'handoverDetails/{id}', 'Handover\HandoverDetailsController@handoverDetails');//交接单详情
});

//询价管理路由
Route::group(['prefix' => 'enquiry'], function () {
    //列表相关路由
    Route::match(['get', 'post'], 'allList', 'Enquiry\EnquiryController@allList');//全部询价列表blade
    Route::match(['get', 'post'], 'proList', 'Enquiry\EnquiryController@allList');//全部询价列表blade
    Route::match(['get', 'post'], 'resList', 'Enquiry\EnquiryController@allList');//全部询价列表blade
    Route::match(['get', 'post'], 'purList', 'Enquiry\EnquiryController@allList');//全部询价列表blade
    Route::match(['get', 'post'], 'salesList', 'Enquiry\EnquiryController@salesList');//销售询价列表blade
    Route::match(['get', 'post'], 'getAllList', 'Enquiry\EnquiryController@getAllList');//全部询价列表data
    Route::match(['get', 'post'], 'getSalesList', 'Enquiry\EnquiryController@getSalesList');//销售询价列表data
    Route::match(['get', 'post'], 'getOfferList', 'Enquiry\EnquiryController@getOfferList');//销售询价列表data
    Route::match(['get', 'post'], 'getRecordList', 'Enquiry\EnquiryController@getRecordList');//销售询价列表data

    //新增详情编辑等相关路由
    Route::match(['get', 'post'], 'newEnquiry', 'Enquiry\EnquiryDetailsController@newEnquiry');//询价申请blade
    Route::match(['get', 'post'], 'newOffer', 'Enquiry\EnquiryDetailsController@newOffer');//产品报价单blade
    Route::match(['get', 'post'], 'offerDetail', 'Enquiry\EnquiryDetailsController@offerDetail');//产品报价单blade
    Route::match(['get', 'post'], 'enquirySub', 'Enquiry\EnquiryDetailsController@enquirySub');//询价申请提交
    Route::match(['get', 'post'], 'offerSub', 'Enquiry\EnquiryDetailsController@offerSub');//报价单提交
    Route::match(['get', 'post'], 'productOffer/{id}', 'Enquiry\EnquiryDetailsController@productOffer');//产品报价blade
    Route::match(['get', 'post'], 'enquiryDetail/{id}', 'Enquiry\EnquiryDetailsController@enquiryDetail');//询价详情blade
    Route::match(['get', 'post'], 'delOffer', 'Enquiry\EnquiryDetailsController@delOffer');//删除产品报价
    Route::match(['get', 'post'], 'productOfferSub', 'Enquiry\EnquiryDetailsController@productOfferSub');//产品报价sub
    Route::match(['get', 'post'], 'flowChart', 'Enquiry\EnquiryDetailsController@flowChart');//产品报价sub
});

//rpms管理路由
Route::group(['prefix' => 'rpms'], function () {
    //资源类型相关类型
    Route::group(['prefix' => 'resourceType'], function () {
        Route::match(['get','post'],'typeList','RPMS\ResourceTypeController@typeList');//资源类型列表blade
        Route::match(['get','post'],'getTypeList','RPMS\ResourceTypeController@getTypeList');//资源类型列表
        Route::match(['get','post'],'newType','RPMS\ResourceTypeController@newType');//新增资源类型blade
        Route::match(['get','post'],'newTypeSub','RPMS\ResourceTypeController@newTypeSub');//新增资源类型提交
        Route::match(['get','post'],'getProdType','RPMS\ResourceTypeController@getProdType');//查询上级类型
        Route::match(['get','post'],'batchOperate','RPMS\ResourceTypeController@batchOperate');//批量操作
        Route::match(['get','post'],'checkParCount','RPMS\ResourceTypeController@checkParCount');//查询上级类型数量
        Route::match(['get','post'],'checkSonCount','RPMS\ResourceTypeController@checkSonCount');//查询子类型数量
    });

    Route::group(['prefix' => 'resourceProd'], function () {
        Route::match(['get','post'],'prodList','RPMS\ResourceProdController@prodList');//资源类型列表blade
        Route::match(['get','post'],'getProdList','RPMS\ResourceProdController@getProdList');//资源类型列表
        Route::match(['get','post'],'newProd','RPMS\ResourceProdController@newProd');//新增资源类型blade
        Route::match(['get','post'],'newProdSub','RPMS\ResourceProdController@newProdSub');//新增资源类型提交
        Route::match(['get','post'],'batchOperate','RPMS\ResourceProdController@batchOperate');//批量操作
        Route::match(['get','post'],'getSonType','RPMS\ResourceProdController@getSonType');//获取资源子类型
    });

    Route::group(['prefix' => 'resourceProvider'], function () {
        Route::match(['get','post'],'providerList','RPMS\ResourceProviderController@providerList');//资源供应商列表blade
        Route::match(['get','post'],'getProviderList','RPMS\ResourceProviderController@getProviderList');//资源供应商列表数据接口
        Route::match(['get','post'],'getContactList','RPMS\ResourceProviderController@getContactList');//资源供应商联系人列表数据接口
        Route::match(['get','post'],'newProvider','RPMS\ResourceProviderController@newProvider');//新增资源类型blade
        Route::match(['get','post'],'providerDetail','RPMS\ResourceProviderController@providerDetail');//新增资源类型blade
        Route::match(['get','post'],'newContact','RPMS\ResourceProviderController@newContact');//新增联系人blade
        Route::match(['get','post'],'contactDetail','RPMS\ResourceProviderController@contactDetail');//新增联系人blade
        Route::match(['get','post'],'delContact','RPMS\ResourceProviderController@delContact');//标记删除联系人
        Route::match(['get','post'],'newProviderSub','RPMS\ResourceProviderController@newProviderSub');//新增资源类型提交
        Route::match(['get','post'],'newContactSub','RPMS\ResourceProviderController@newContactSub');//新增资源类型提交
        Route::match(['get','post'],'getProdType','RPMS\ResourceProviderController@getProdType');//查询上级类型
        Route::match(['get','post'],'batchOperate','RPMS\ResourceProviderController@batchOperate');//批量操作
        Route::match(['get','post'],'getSonType','RPMS\ResourceProviderController@getSonType');//获取资源子类型
    });

    Route::group(['prefix' => 'resourceContract'], function () {
        Route::match(['get','post'],'contractList','RPMS\ResourceContractController@contractList');//资源合同列表
        Route::match(['get','post'],'batchOperate','RPMS\ResourceContractController@batchOperate');//批量操作
        Route::match(['get','post'],'getContractList','RPMS\ResourceContractController@getContractList');//资源合同列表
        Route::match(['get','post'],'newContract','RPMS\ResourceContractController@newContract');//新增合同
        Route::match(['get','post'],'changeContract','RPMS\ResourceContractController@changeContract');//变更合同
        Route::match(['get','post'],'stopContract','RPMS\ResourceContractController@stopContract');//终止合同
        Route::match(['get','post'],'contractConfirm/{id}','RPMS\ResourceContractController@contractConfirm');//终止合同审核闭单
        Route::match(['get','post'],'confirmContract','RPMS\ResourceContractController@confirmContract');//终止合同审核闭单提交
        Route::match(['get','post'],'stopContractSub','RPMS\ResourceContractController@stopContractSub');//终止合同提交
        Route::match(['get','post'],'pickResource','RPMS\ResourceContractController@pickResource');//选择资源产品
        Route::match(['get','post'],'deleteResourceProd','RPMS\ResourceContractController@deleteResourceProd');//选择资源产品
        Route::match(['get','post'],'prodList','RPMS\ResourceContractController@prodList');//获取合同关联产品列表
        Route::match(['get','post'],'saveContract','RPMS\ResourceContractController@saveContract');//选择资源产品
        Route::match(['get','post'],'findSupplierBySearch','RPMS\ResourceContractController@findSupplierBySearch');//选择资源产品
        Route::match(['get','post'],'recordList','RPMS\ResourceContractController@recordList');//操做记录数据

        Route::match(['get','post'],'addRecord/{id}','RPMS\ResourceContractController@addRecord');//添加专线记录
        Route::match(['get','post'],'saveRecord','RPMS\ResourceContractController@saveRecord');//保存专线记录
        Route::match(['get','post'],'delRecord/{id}','RPMS\ResourceContractController@delRecord');//删除专线记录
        Route::match(['get','post'],'specialRocordList','RPMS\ResourceContractController@specialRocordList');//专线记录列表
        Route::match(['get','post'],'getSpecialCount','RPMS\ResourceContractController@getSpecialCount');//查询专线产品数量
    });

    Route::group(['prefix' => 'resourceBill'], function () {
        Route::match(['get','post'],'billList','RPMS\ResourceBillController@billList');//账单列表
        Route::match(['get','post'],'getBillList','RPMS\ResourceBillController@getBillList');//账单列表
        Route::match(['get','post'],'newBill','RPMS\ResourceBillController@newBill');//账单列表
        Route::match(['get','post'],'saveBill','RPMS\ResourceBillController@saveBill');//新增/修改账单
        Route::match(['get','post'],'findContractBySearch','RPMS\ResourceBillController@findContractBySearch');//新增/修改账单
        Route::match(['get','post'],'billInfo','RPMS\ResourceBillController@billInfo');//操做记录数据
        Route::match(['get','post'],'recordList','RPMS\ResourceBillController@recordList');//操做记录数据
        Route::match(['get','post'],'createBill','RPMS\ResourceBillController@createBill');//创建首期账单
        Route::match(['get','post'],'createBillByAjax','RPMS\ResourceBillController@createBillByAjax');//下期账单
        Route::match(['get','post'],'batchOperate','RPMS\ResourceBillController@batchOperate');//操做记录数据



        Route::match(['get','post'],'delBill/{id}','RPMS\ResourceBillController@delBill');//标记删除账单
    });
});

Route::group(['prefix' => 'api'], function () {
    /*Route::group(['prefix' => 'customer'], function () {
        Route::match(['get','post'],'create','Supports\SupportsApiController@create');//客户创建
    });*/
    Route::match(['get','post'],'exportOperation','Supports\SupportsApiController@exportOperation');
    Route::match(['get','post'],'import','Supports\SupportsApiController@import');
});
