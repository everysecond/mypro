<?php

namespace Itsm\Http\Controllers\Issue;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Input;
use Itsm\Http\Controllers\Controller;
use Itsm\Http\Helper\ProcessMakerApi;
use Itsm\Http\Helper\ThirdCallHelper;
use Itsm\Jobs\SendIssueEmail;
use Itsm\Model\Res\AuxDict;
use Itsm\Model\Res\AuxStuff;
use Itsm\Model\Res\ResContact;
use Itsm\Model\Usercenter\Change;
use Itsm\Model\Usercenter\Correlation;
use Itsm\Model\Usercenter\Issue;
use Itsm\Model\Usercenter\IssueRecord;
use Itsm\Model\Usercenter\Support;


class IssueController extends Controller
{
    //加载问题提交页面基础数据
    public function issueapply(Request $req)
    {
        $params = $req->all();
        if ($name = Input::get('Name')) {//问题审核人
            return $userName = $this->getTodoRoleName($name, Input::get('roleType'));
        }
        $issueNo = $this->getIssueNo();
        //问题来源
        $sourceList = ThirdCallHelper::getDictArray('问题来源', 'issueSource');
        //问题分类
//        $categoryList = ThirdCallHelper::getDictArray('问题分类', 'issueCategory');
        $categoryList = ThirdCallHelper::getDictArray('工单类型', 'WorksheetTypeOne');
        //优先级
        $priorityList = ThirdCallHelper::getDictArray('优先级', 'issuePriority');
        $issueSubmitUserId = $this->getApplyUser();
        $issueSubmitTs = date("Y-m-d H:i:s", time());
        return view('/issue/issueapply',
            compact('issueNo', 'sourceList', 'categoryList', 'priorityList', 'issueSubmitUserId', 'issueSubmitTs',
                'params'));
    }


    //保存问题提交
    public function pushApply()
    {
        $role = $this->getProcessRole("issue");

        $caseId = 0;
        $status = '';
        if (in_array('employee', $role)) {
            $token = $this->getAccessTokenByRole("employee", "issue");
            if (!isset($token['access_token'])) {
                return ['status' => false, 'statusMsg' => '获取token错误!'];
            }
            $proApi = new ProcessMakerApi(env("ISSUE_PROCESS_ID"), env("ISSUE_STEP_ONE_ID"));
            $caseInfo = $proApi->createNewCase($token['access_token']);
            if (!isset($caseInfo['caseId'])) {
                return ['status' => false, 'statusMsg' => '工作流API创建失败!'];
            }
            $caseId = $caseInfo['caseId'];
            $status = $caseInfo['status'];
        } else {
            return ['status' => false, 'statusMsg' => '当前登录身份不能申请问题!'];
        }
        $userId = Request()->session()->get('user')->Id;
        $input = Input::except('_token');
        $ifExist = Issue::select('*')->orderBy('Id',"desc")->first();
        if($input['issueNo']==$ifExist['issueNo']){
            $input['issueNo']=$this->getIssueNo();
        }
        $issueSource = Arr::get($input, "issueSource", []);
        $inputdata = Issue::insertGetId([
            'caseId'            => $caseId,
            'issueState'        => $status,
            'issueNo'           => $input['issueNo'],
            'issueTitle'        => $input['issueTitle'],
            'issueSource'       => implode(',', $issueSource),
            'issueCategory'     => $input['issueCategory'],
            'issuePriority'     => $input['issuePriority'],
            'issueDescribe'     => $input['issueDescribe'],
            'issueSubmitUserId' => $userId,
            'issueSubmitTs'     => $input['issueSubmitTs'],
            'issueCheckUserId'  => $input['issueCheckUserId'],
            'ts'                => date('Y-m-d H:i:s'),
            'upTs'              => date('Y-m-d H:i:s'),
        ]);
        if (!$inputdata) {//插入数据失败
            return ['status' => false, 'statusMsg' => '提交出错,请稍后再试!'];
        } else {
            $url = env("APP_URL", "http://www.itsm.com");
            $replyContent = '';
            $replyContent .= '<div class="stl">问题主题</div>' .'<div class="cont">'. $input['issueTitle'].'</div>';
            $replyContent .= '<div class="stl">申请时间：' . '&nbsp;' . $input['issueSubmitTs'];
            $replyContent .= '&nbsp;&nbsp;&nbsp;&nbsp;' . "<a href='$url/issue/details/{$inputdata}#detailsArea'>详情参详问题主体信息</a>".'</div>';
            $record = IssueRecord::insertGetId([
                'issueId'      => $inputdata,
                'issuecontent' => $replyContent,
                'ts'           => date('Y-m-d H:i:s'),
                'userId'       => $userId,
                'issueState'   => '问题申请',
                'passOrNo'     => '提交',
            ]);
            if (!empty($input['triggerId'])) {
                $relate = Correlation::insertGetId([
                    'supportId'    => Arr::get($input, "supportId", 0),
                    'changeId'     => Arr::get($input, "changeId", 0),
                    'issueId'      => $inputdata,
                    'repositoryId' => Arr::get($input, "repositoryId", 0),
                    'triggerId'    => Arr::get($input, "triggerId", 0),
                    'userId'       => $userId,
                    'ts'           => date('Y-m-d H:i:s', time()),
                ]);
            }
            //如果不是保存操作 则添加队列任务发送邮件或短信
            $title = '问题标题：' . $input['issueTitle'] . ';' . ThirdCallHelper::getStuffName($userId) . '提交问题申请';
            $detailController = new IssueDetailsController();
            $userIds = $detailController->getNextIds('approval', $input);
            $job = new SendIssueEmail($title, $userIds, $input['issuePriority'], $replyContent); //创建队列任务
            $this->dispatch($job);
            return ['status' => 'ok', 'statusMsg' => '提交成功!'];
        }
    }

    /**
     * 待办问题列表
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function todoList()
    {
        //获取问题状态
        $stateList = ThirdCallHelper::getDictArray('问题状态', 'issueState');
        //问题分类
        $categoryList = ThirdCallHelper::getDictArray('工单类型', 'WorksheetTypeOne');;
        //优先级
        $priorityList = ThirdCallHelper::getDictArray('优先级', 'issuePriority');
        //问题来源
        $sourceList = ThirdCallHelper::getDictArray('问题来源', 'issueSource');

        return view("issue/todolist",
            [
                'priorityList' => $priorityList,
                'stateList'    => $stateList,
                'categoryList' => $categoryList,
                'sourceList'   => $sourceList
            ]);
    }

    //获取待办条数
    public function getToIssueNum()
    {
        $num = $this->getToDoListData();
        return $num['total'];
    }

    //获取待问题列表
    public function getToDoListData()
    {
        $userId = Request()->session()->get('user')->Id;
        $pmRole = $this->getProcessRole("issue");
        $statusRole = [];
        foreach ($pmRole as $item) {
            $statusArr = explode("_", $item);
            if (isset($statusArr[1])) {
                $statusRole[] = $statusArr[1];
            }
        }
        $todoList = [];
        if (!empty($statusRole)) {
            $todoList = Issue::select('*')->where("issue.inValidate","0");
            $todoList = $todoList->where(function ($todoList) use ($statusRole, $userId) {
                foreach ($statusRole as $roleItem) {
                    switch ($roleItem) {
                        case 'reject':
                            $todoList->orwhereRaw("(issueState = 'reject' and issueSubmitUserId = $userId)");
                            break;
                        case 'approval':
                            $todoList->orwhereRaw("(issueState = 'approval' and issueCheckUserId = $userId)");
                            break;
                        case 'analyse':
                            $todoList->orwhereRaw("(issueState = 'analyse' and issueChargeUserId = $userId)");
                            break;
                        case 'check':
                            $todoList->orwhereRaw("(issueState = 'check' and issueCheckUserId = $userId)");
                            break;
                        case 'closed':
                            $todoList->orwhereRaw("(issueState = 'closed' and issueCheckUserId = $userId)");
                            break;
                        default:
                            break;
                    }
                }
            });
            //筛选条件
            if ($issueCategory = Input::get('issueCategory')) {
                $todoList = $todoList->where('issue.issueCategory', $issueCategory);
            }
            if ($issueState = Input::get('issueState')) {
                $todoList = $todoList->where('issue.issueState', $issueState);
            }
            if ($issuePriority = Input::get('issuePriority')) {
                $todoList = $todoList->where('issue.issuePriority', $issuePriority);
            }
            if ($issueSource = Input::get('issueSource')) {
                $todoList = $todoList->whereRaw('issue.issueSource like "%' . $issueSource . '%"');
            }
            //排序
            $todoList = $todoList->orderByRaw("issuePriority = 'instancy' desc,issuePriority = 'important' desc,issuePriority = 'general' desc,ts DESC");
            $todoListArray['total'] = $todoList->count();

            //分页 changeCondition feasibilityUserId
            $pageSize = !empty(Input::get('pageSize')) ? Input::get('pageSize') : 20;
            $pageNumber = !empty(Input::get('pageNumber')) ? Input::get('pageNumber') : 1;
            $todoList = $todoList->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();

            //将部分数据中的Code转化为中文字符
            $todoList = Support::translationDict($todoList, 'issueState', 'issueState');
            $todoList = Support::translationDict($todoList, 'issueCategory', 'WorksheetTypeOne');
            $todoList = Support::translationDict($todoList, 'issuePriority', 'issuePriority');
            $todoList = ThirdCallHelper::translationArr($todoList, 'issueSource');
            $todoList = ResContact::translationStuff($todoList, 'issueCheckUserId');
            $todoList = ResContact::translationStuff($todoList, 'issueChargeUserId');
            $todoList = ResContact::translationStuff($todoList, 'issueSubmitUserId');
            $todoList = ResContact::translationStuff($todoList, 'upUserId');

        }

        $todoListArray['rows'] = $todoList;
        return $todoListArray;
    }

    /**
     * 获取IssueNo
     * @return string
     */
    public function getIssueNo()
    {
        //获取年月
        $month = date("Ym", time());
        //获取当月最大的申请单编号，如果没有则从01开始计算
        $num = Issue::select('Id')
            ->whereRaw("DATE_FORMAT(Ts,'%Y%m') ='$month'")
            ->orderBy('Id', 'desc')
            ->count();

        $thisNum = 1;
        if (isset($num)) {
            $thisNum = $num + 1;
        }
        $thisNum = sprintf('%02s', $thisNum);
        $rfc = 'AC-PR-' . $month . $thisNum;
        return $rfc;
    }

    /*
     * 获取申请人
     */
    public function getApplyUser()
    {
        $user = Request()->session()->get('user')->Id;
        $name = AuxStuff::select('Name')
            ->where('Id', '=', $user)
            ->first()->toArray();
        $apply = $name['Name'];
        return $apply;
    }

    //同时获取一级部门及下属二级部门
    public static function getAppDepartment()
    {
        $depart1 = AuxDict::select('Means', 'Code')
            ->whereNotNull('Means')
            ->where('DomainCode', 'DepartType')//一级部门
            ->where(function ($arr) {
                $arr->whereNull('Validate')
                    ->orwhere('Validate', '<>', 1);
            })
            ->get()->toArray();
        foreach ($depart1 as &$item) {
            $depart2 = AuxDict::select('Means', 'Code')
                ->whereNotNull('Means')
                ->where('DomainCode', 'second_dept')//二级部门
                ->where('ParentCode', $item['Code'])
                ->where(function ($arr) {
                    $arr->whereNull('Validate')
                        ->orwhere('Validate', '<>', 1);
                })
                ->get()->toArray();
            $item['secondDep'] = $depart2;
        }
        return $depart1;
    }

    //获取登录人所在的部门
    public function getUserDep()
    {
        $user = Request()->session()->get('user')->Id;
        $userDep = AuxStuff::select('Depart', 'second_dept')
            ->where('Id', $user)
            ->where('InValidate', '<>', AuxStuff::DISABLED_YES)
            ->first()->toArray();
        if ($userDep['Depart']) {
            $userDepart = $userDep['Depart'];
        } else {
            $userDepart = $userDep['second_dept'];
        }
        return $userDepart;
    }

    /*
     * 相关问题列表
     */

    public function MyList()
    {
        //获取问题类型
        $stateList = ThirdCallHelper::getDictArray('问题状态', 'issueState');
        $sourceList = ThirdCallHelper::getDictArray('问题来源', 'issueSource');
        $categoryList = ThirdCallHelper::getDictArray('工单类型', 'WorksheetTypeOne');
        $priorityList = ThirdCallHelper::getDictArray('优先级', 'issuePriority');
        return view("issue/relatelist", [
            'stateList'    => $stateList,
            'categoryList' => $categoryList,
            'priorityList' => $priorityList,
            'sourceList'   => $sourceList
        ]);
    }

    /*
     * 读取相关问题列表数据
     */

    public function getMyListData()
    {
        $userId = Request()->session()->get('user')->Id;

        //筛选所有当前人员参与过的问题
        $myList = Issue::selectRaw("DISTINCT(issue.Id),`issue`.*")
            ->leftJoin("issuerecord as b", "issue.Id", "=", "b.issueId")
            ->where('b.userId', $userId)
            ->where("issue.inValidate","0");

        //根据时间窗口或者实际完成时间筛选
        $issueStartTime = Input::get('issueStartTime') ? Input::get('issueStartTime') : date('Y-m-d H:i:s',
            time());
        $issueEndTime = Input::get('issueEndTime') ? Input::get('issueEndTime') : date('Y-m-d H:i:s', time());

        //有一个不为空,则命中条件,另一个默认为当前时间
        if (!empty(Input::get('issueStartTime')) || !empty(Input::get('issueEndTime'))) {
            $myList = $myList->whereBetween("issue.ts", [$issueStartTime, $issueEndTime]);
        }
        //筛选变更状态和变更类型
        if ($issueCategory = Input::get('issueCategory')) {
            $myList = $myList->where('issue.issueCategory', $issueCategory);
        }
        if ($issueState = Input::get('issueState')) {
            $myList = $myList->where('issue.issueState', $issueState);
        }
        if ($issuePriority = Input::get('issuePriority')) {
            $myList = $myList->where('issue.issuePriority', $issuePriority);
        }
        if ($issueSource = Input::get('issueSource')) {
            $myList = $myList->whereRaw('issue.issueSource like "%' . $issueSource . '%"');
        }
//        根据关键字模糊查询
        if ($keyword = Input::get('searchInfo')) {
            $myList = $myList->where(function ($myList) use ($keyword) {
                $myList->Where('issueNo', 'like', '%' . $keyword . '%')
                    ->orWhere('issueTitle', 'like', '%' . $keyword . '%');
            });
        }
        //排序
        $myList = $myList->orderByRaw("issuePriority = 'instancy' desc,issuePriority = 'important' desc,issuePriority = 'general' desc,ts DESC");
        $myListArray['total'] = count($myList->lists("DISTINCT(issue.Id)"));
        //分页
        $pageSize = !empty(Input::get('pageSize')) ? Input::get('pageSize') : 20;
        $pageNumber = !empty(Input::get('pageNumber')) ? Input::get('pageNumber') : 1;
        $myList = $myList->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();
        //将部分数据中的Code转化为中文字符
        $myList = Support::translationDict($myList, 'issueState', 'issueState');
        $myList = Support::translationDict($myList, 'issueCategory', 'WorksheetTypeOne');
        $myList = Support::translationDict($myList, 'issuePriority', 'issuePriority');
        $myList = ThirdCallHelper::translationArr($myList, 'issueSource');
        $myList = ResContact::translationStuff($myList, 'issueCheckUserId');
        $myList = ResContact::translationStuff($myList, 'issueChargeUserId');
        $myList = ResContact::translationStuff($myList, 'issueSubmitUserId');
        $myList = ResContact::translationStuff($myList, 'upUserId');
        $myList = ResContact::translationStuff($myList, 'UserId');
        $myList = ResContact::translationStuff($myList, 'proDesigerId');
        $myListArray['rows'] = $myList;

        return $myListArray;
    }

    /*
     * 所有问题列表
     */
    public function allList()
    {
        //获取问题类型
        $stateList = ThirdCallHelper::getDictArray('问题状态', 'issueState');
        $sourceList = ThirdCallHelper::getDictArray('问题来源', 'issueSource');
        $categoryList = ThirdCallHelper::getDictArray('工单类型', 'WorksheetTypeOne');
        $priorityList = ThirdCallHelper::getDictArray('优先级', 'issuePriority');
        return view("issue/alllist", [
            'stateList'    => $stateList,
            'categoryList' => $categoryList,
            'priorityList' => $priorityList,
            'sourceList'   => $sourceList
        ]);
    }

    /*
     * 读取所有问题列表数据
     */
    public function getAllListData()
    {
        //筛选所有问题
        $allList = Issue::select("*")->where("issue.inValidate",0);

        //根据时间窗口或者实际完成时间筛选
        $issueStartTime = Input::get('issueStartTime') ? Input::get('issueStartTime') : date('Y-m-d H:i:s',
            time());
        $issueEndTime = Input::get('issueEndTime') ? Input::get('issueEndTime') : date('Y-m-d H:i:s', time());

        //有一个不为空,则命中条件,另一个默认为当前时间
        if (!empty(Input::get('issueStartTime')) || !empty(Input::get('issueEndTime'))) {
            $allList = $allList->whereBetween("issue.ts", [$issueStartTime, $issueEndTime]);
        }
        //筛选变更状态和变更类型
        if ($issueCategory = Input::get('issueCategory')) {
            $allList = $allList->where('issue.issueCategory', $issueCategory);
        }
        if ($issueState = Input::get('issueState')) {
            $allList = $allList->where('issue.issueState', $issueState);
        }
        if ($issuePriority = Input::get('issuePriority')) {
            $allList = $allList->where('issue.issuePriority', $issuePriority);
        }
        if ($issueSource = Input::get('issueSource')) {
            $allList = $allList->whereRaw('issue.issueSource like "%' . $issueSource . '%"');
        }

//        根据关键字模糊查询
        if ($keyword = Input::get('searchInfo')) {
            $allList = $allList->where(function ($myList) use ($keyword) {
                $myList->Where('issueNo', 'like', '%' . $keyword . '%')
                    ->orWhere('issueTitle', 'like', '%' . $keyword . '%');
            });
        }

        //排序
        $allList = $allList->orderByRaw("issuePriority = 'instancy' desc,issuePriority = 'important' desc,issuePriority = 'general' desc,ts DESC");
        $allListArray['total'] = count($allList->lists("DISTINCT(issue.Id)"));
        //分页
        $pageSize = !empty(Input::get('pageSize')) ? Input::get('pageSize') : 20;
        $pageNumber = !empty(Input::get('pageNumber')) ? Input::get('pageNumber') : 1;
        $allList = $allList->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();
        //将部分数据中的Code转化为中文字符
        $allList = Support::translationDict($allList, 'issueState', 'issueState');
        $allList = Support::translationDict($allList, 'issueCategory', 'WorksheetTypeOne');
        $allList = Support::translationDict($allList, 'issuePriority', 'issuePriority');
        $allList = ThirdCallHelper::translationArr($allList, 'issueSource');
        $allList = ResContact::translationStuff($allList, 'issueCheckUserId');
        $allList = ResContact::translationStuff($allList, 'issueChargeUserId');
        $allList = ResContact::translationStuff($allList, 'issueSubmitUserId');
        $allList = ResContact::translationStuff($allList, 'upUserId');
        $allList = ResContact::translationStuff($allList, 'UserId');
        $allList = ResContact::translationStuff($allList, 'proDesigerId');
        $allListArray['rows'] = $allList;

        return $allListArray;
    }

    /*
    * 根据姓名或拼音模糊查询问题相关操作人
    */
    public function getTodoRoleName($name, $role)
    {
        $chargeUser = AuxStuff::selectRaw("DISTINCT(res.aux_stuff.Name), res.aux_stuff.Id")
            ->Join('auth.authorities', 'res.aux_stuff.Login', '=', 'auth.authorities.username')
            ->Raw("leftjoin res.aux_dict as c on res.aux_stuff.Depart=c.`Code` and  c.DomainCode='DepartType'")
            ->Raw("leftjoin res.aux_dict as d on res.aux_stuff.second_dept=d.`Code` and  d.DomainCode='second_dept'")
            ->where('auth.authorities.authority', $role)
            ->whereRaw("((res.aux_stuff.Name like '%$name%') or (res.aux_stuff.Login like '%$name%'))")
            ->get();
        return $chargeUser;
    }

    /*
    * 获取所有变更列表
    */
    public function getRelateChange()
    {
        $changeLists = Change::select('*')->orderBy("Id","desc");
        if ($keyword = Input::get('searchInfo')) {
            $changeLists = $changeLists->where(function ($changeLists) use ($keyword) {
                $changeLists->Where('RFCNO', 'like', '%' . $keyword . '%')
                    ->orWhere('changeTitle', 'like', '%' . $keyword . '%');
            });
        }
        if ($issueId = Input::get('issueId')) {
            $sql = "select changeId from `correlation` where `issueId` = $issueId and `changeId` <> 0 and `inValidate` = 0 order by `Id` desc";
            $changeLists = $changeLists->whereRaw("(Id not in($sql))");
        }
        $total = $changeLists->count();
        $changeList['total'] = $total;
        $pageSize = !empty(Input::get('pageSize')) ? Input::get('pageSize') : 5;
        $pageNumber = !empty(Input::get('pageNumber')) ? Input::get('pageNumber') : 1;
        $changeLists = $changeLists->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();
        $changeLists = Support::translationDict($changeLists, 'changeCategory', 'changeCategory');
        $changeList['rows'] = $changeLists;
        return $changeList;
    }

    /**
     * 弹出将被关联的变更列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function relateChange(Request $request)
    {
        $data = $request->all();
        $issueId = $data["issueId"];
        return view('issue/relatechange', compact("issueId"));
    }

    //获取已经关联的变更列表
    public function relateChangeData(Request $req)
    {
        $id = $req->input("id");
        $relateChange = Correlation::where("issueId", $id)->where('changeId', '<>', 0)->where('inValidate',
            0)->orderBy("Id","desc")->get()->toArray();

        //定义返回结果格式
        $arr = [
            'rows'  => [],
            'total' => 0
        ];

        if (!empty($relateChange)) {
            foreach ($relateChange as &$changeRecord) {
                $arr['rows'][] = Change::where("Id", $changeRecord['changeId'])->orderBy("Id", "desc")->first();
            }
            $arr['rows'] = Support::translationDict($arr['rows'], 'changeCategory', 'changeCategory');
            $arr['rows'] = Support::translationDict($arr['rows'], 'changeCondition', 'changeCondition');
            $arr['rows'] = Change::translationStuff($arr['rows'], 'applyUserId');
            $arr['total'] = count($arr['rows']);
        }
        return $arr;
    }
    /*
     * 获取所有工单列表
     */
    public function getRelateSupport()
    {
        $supportLists = Support::select('*')->orderBy("Id","desc");
        if ($keyword = Input::get('searchInfo')) {
            $supportLists = $supportLists->where(function ($supportLists) use ($keyword) {
                $supportLists->Where('Id', 'like', '%' . $keyword . '%')
                    ->orWhere('Title', 'like', '%' . $keyword . '%');
            });
        }
        if ($issueId = Input::get('issueId')) {
            $sql = "select supportId from `correlation` where `issueId` = $issueId and `supportId` <> 0 and `inValidate` = 0 order by `Id` desc";
            $supportLists = $supportLists->whereRaw("(Id not in($sql))");
        }
        $total = $supportLists->count();
        $supportList['total'] = $total;
        $pageSize = !empty(Input::get('pageSize')) ? Input::get('pageSize') : 9;
        $pageNumber = !empty(Input::get('pageNumber')) ? Input::get('pageNumber') : 1;
        $supportLists = $supportLists->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();
        $supportLists = Support::translationDict($supportLists, 'ClassInficationOne', 'WorkSheetTypeOne');
        $supportLists = Change::translationStuff($supportLists, 'CreateUserId');
        $supportList['rows'] = $supportLists;
        return $supportList;
    }

    /**
     * 弹出将被关联的工单列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function relateSupport(Request $request)
    {
        $data = $request->all();
        $issueId = $data["issueId"];
        return view('issue/relatesupport', compact("issueId"));
    }

    //获取已经关联的工单列表
    public function relateSupportData(Request $req)
    {
        $id = $req->input("id");
        $relateSupport = Correlation::where("issueId", $id)->where('supportId', '<>', 0)->where('inValidate',
            0)->orderBy("Id","desc")->get()->toArray();
        //定义返回结果格式
        $arr = [
            'rows'  => [],
            'total' => 0
        ];

        if (!empty($relateSupport)) {
            foreach ($relateSupport as &$supportRecord) {
                $arr['rows'][] = Support::where("Id", $supportRecord['supportId'])->orderBy("Id", "desc")->first();
            }
            $arr['rows'] = Support::translationDict($arr['rows'], 'ClassInficationOne', 'WorkSheetTypeOne');
            $arr['rows'] = Support::translationCusName($arr['rows'], 'CustomerInfoId');
            $arr['rows'] = Change::translationStuff($arr['rows'], 'CreateUserId');
            $arr['total'] = count($arr['rows']);
        }
        return $arr;
    }
}
