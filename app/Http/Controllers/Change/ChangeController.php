<?php
/**
 * Created by PhpStorm.
 * User: chenglh
 * Date: 2016/9/4
 * Time: 18:40
 */

namespace Itsm\Http\Controllers\Change;


use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Itsm\Http\Controllers\Controller;
use Itsm\Http\Helper\ProcessMakerApi;
use Itsm\Http\Helper\PublicMethodsHelper;
use Itsm\Http\Helper\ThirdCallHelper;
use Itsm\Model\Res\AuxDict;
use Itsm\Model\Res\AuxStuff;
use Itsm\Model\Res\ResContact;
use Itsm\Model\Usercenter\Change;
use Itsm\Model\Usercenter\ChangeRecord;
use Itsm\Model\Usercenter\Correlation;
use Itsm\Model\Usercenter\Issue;
use Itsm\Model\Usercenter\Support;
use Itsm\Jobs\SendChangeEmail;

class ChangeController extends Controller
{
    //获取申请列表所有需要的数据
    public function changeRefer(Request $req)
    {
        $params = $req->all();
        //获取变更触发条件
        if ($gate = Input::get('gate')) {
            $changeCategory = AuxDict::select('Means', 'Code')
                ->where('Domain', '变更子类')->where('ParentCode', $gate)
                ->where('DomainCode', 'changeSub')
                ->get();
            return $changeCategory;
        }
        $changeCategory = AuxDict::select('Means', 'Code')
            ->where('DomainCode', 'changeCategory')
            ->where('Domain', '变更类别')
            ->get();
        if ($name = Input::get('Name')) {//获取结果验收人
            return $checkUser = $this->getCheckUserName($name);
        }
        $source = "";
        if ($createSource = Input::get('source')) {
            $source = $createSource;
        }
        //获取变更触发条件
        $conditionList = ThirdCallHelper::getDictArray('触发条件', 'changeCondition');
        $changeNo = $this->getChangeRFCNO();
        $applyName = $this->getApplyUser();
        $applyTime = date("Y-m-d H:i:s", time());
        $oneGroup = $this->getFisibilityGroup();
        return view('/change/changerefer',
            compact('changeNo', 'changeCategory', 'applyName', 'applyTime', 'conditionList', 'oneGroup', 'params',
                'source'));
    }


    //将提交的数据写入数据库
    public function pushApply()
    {
        try {
            $role = $this->getProcessRole();
            if (in_array('employee', $role)) {
                $token = $this->getAccessTokenByRole("employee");
                if (!isset($token['access_token'])) {
                    return ['status' => false, 'statusMsg' => '获取token错误!'];
                }
                $proApi = new ProcessMakerApi(env("CHANGE_PROCESS_ID"), env("CHANGE_STEP_ONE_ID"));
                $caseInfo = $proApi->createNewCase($token['access_token']);
                if (!isset($caseInfo['caseId'])) {
                    return ['status' => false, 'statusMsg' => '工作流API创建失败!'];
                }
            }
            $userId = Request()->session()->get('user')->Id;
            $input = Input::except('_token');
            if (Change::select('*')->where('RFCNO', $input['RFCNO'])->first()) {
                $input['RFCNO'] = $this->getChangeRFCNO();
            }

            $inputId = DB::transaction(function () use ($input, $userId, $caseInfo) {
                $reason = PublicMethodsHelper::htmlToSafe($input['changeReason']);
                $context = PublicMethodsHelper::htmlToSafe($input['changeContext']);
                $risk = PublicMethodsHelper::htmlToSafe($input['changeRisk']);
                $inputData = Change::insertGetId([
                    'RFCNO'              => $input['RFCNO'],
                    'caseId'             => $caseInfo['caseId'],
                    'changeTitle'        => $input['changeTitle'],
                    'changeObject'       => $input['changeObject'],
                    'expectTs'           => $input['expectTs'],
                    'changeType'         => $input['changeType'],
                    'changeCondition'    => $input['changeCondition'],
                    'changeCategory'     => $input['changeCategory'],
                    'changeSubCategory'  => $input['changeSubCategory'],
                    'changeReason'       => $reason,
                    'changeContext'      => $context,
                    'changeRisk'         => $risk,
                    'applyUserId'        => $userId,
                    'applyTs'            => $input['applyTs'],
                    'feasibilityGroupId' => $input['feasibilityGroupId'],
                    'changeState'        => $caseInfo['status'],
                    'checkUserId'        => $input['checkUserId'],
                    'UpTs'               => date('Y-m-d H:i:s'),
                    'Ts'                 => date('Y-m-d H:i:s'),
                    'UserId'             => $userId
                ]);
                if ($inputData){
                    $url = env("APP_URL", "http://www.itsm.com");
                    $replyContent = '';
                    $replyContent .= '<div class="stl">变更主题</div>' . '<div class="cont">' . $input['changeTitle'] . '</div>';
                    $replyContent .= '<div class="stl">期望完成时间：' . $input['expectTs'];
                    $replyContent .= '&nbsp;&nbsp;&nbsp;&nbsp;' . "<a href='$url/change/details/{$inputData}#detailsArea'>详情参详变更主体信息</a>" . '</div>';

                    $recordData = ChangeRecord::insert([
                            'changeId'     => $inputData,
                            'Ts'           => date('Y-m-d H:i:s'),
                            'replycontent' => $replyContent,
                            'userId'       => $userId,
                            'changeState'  => '变更申请',
                            'passOrNo'     => '提交'
                        ]
                    );
                    if (!empty($input['triggerId'])) {
                        $relate = Correlation::insertGetId([
                            'supportId'    => Arr::get($input, "supportId", 0),
                            'changeId'     => $inputData,
                            'issueId'      => Arr::get($input, "issueId", 0),
                            'repositoryId' => Arr::get($input, "repositoryId", 0),
                            'triggerId'    => Arr::get($input, "triggerId", 0),
                            'userId'       => $userId,
                            'ts'           => date('Y-m-d H:i:s', time()),
                        ]);
                    }

                    //如果不是保存操作 则添加队列任务发送邮件或短信
                    $title = '变更标题：' . $input['changeTitle'] . ';' . ThirdCallHelper::getStuffName($userId) . '提交变更申请';
                    $detailController = new ChangeDetailsController();
                    $userIds = $detailController->getNextIds('approval', $input);
                    $job = new SendChangeEmail($title, $userIds, $input['changeType'], $replyContent); //创建队列任务
                    $this->dispatch($job);
                }
            });
            return ['status' => 'ok'];
        } catch (\Exception $ex) {
            return ['status' => false, 'msg' => $ex->getMessage()];
        }
    }

    /**
     * 待办变更列表
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function todoList()
    {
        //获取变更类型
        $typeList = ThirdCallHelper::getDictArray('变更类型', 'changeType');
        //获取变更状态
        $statusList = ThirdCallHelper::getDictArray('变更状态', 'changeState');
        return view("change/todolist",
            ['statusList' => $statusList, 'typeList' => $typeList]);
    }

    //获取待变更条数
    public function getToChangeNum()
    {
        $num = $this->getToDoListData();
        return $num['total'];
    }

    //获取待变更列表页面数据
    public function getToDoListData()
    {
        $userId = Request()->session()->get('user')->Id;
        $pmRole = $this->getProcessRole();
        $statusRole = [];
        foreach ($pmRole as $item) {
            $statusArr = explode("_", $item);
            if (isset($statusArr[1])) {
                $statusRole[] = $statusArr[1];
            }
        }
        $userDept = $this->getUserDep();
        $userSecondDept = $this->getUserSecondDep();
        $myList = [];
        if (!empty($statusRole)) {
            $myList = Change::select('usercenter.change.*')
                ->leftJoin('res.aux_stuff as b', 'b.Id', '=', 'usercenter.change.proDesigerId')
                ->where("usercenter.change.inValidate",0);
            $myList = $myList->where(function ($myList) use ($statusRole, $userId, $userDept, $userSecondDept) {
                foreach ($statusRole as $roleItem) {
                    switch ($roleItem) {
                        case 'reject':
                            $myList->orwhereRaw("(changeState = 'reject' and applyUserId = $userId)");
                            break;
                        case 'approval':
                            $myList->orwhereRaw("(changeState = 'approval' and (feasibilityGroupId = '$userDept' or feasibilityGroupId = '$userSecondDept'))");
                            break;
                        case 'design':
                            $myList->orwhereRaw("(changeState = 'design' and proDesigerId = $userId)");
                            break;
                        case 'actualize':
                            $myList->orwhereRaw("(changeState = 'actualize' and (proDesigerGroupId = '$userDept' or proDesigerGroupId = '$userSecondDept'))");
                            break;
                        case 'test':
                            $myList->orwhereRaw("(changeState = 'test' and (testGroupId = '$userDept' or testGroupId = '$userSecondDept'))");
                            break;
                        case 'testApproval':
                            $myList->orwhereRaw("(changeState = 'testApproval' and b.parentId = $userId)");
                            break;
                        case 'release':
                            $myList->orwhereRaw("(changeState = 'release' and changeImplementUserId =$userId )");
                            break;
                        case 'approved':
                            $myList->orwhereRaw("(changeState = 'approved' and checkUserId = $userId)");
                            break;
                        default:
                            break;
                    }

                }
            });

            //筛选变更状态和变更类型
            if ($changType = Input::get('changeType')) {
                $myList = $myList->where('changeType', $changType);
            }
            if ($changeState = Input::get('changeState')) {
                $myList = $myList->where('changeState', $changeState);
            }
            if ($keyword = Input::get('timeOutIds')) {
                $myList = $myList->whereIn('usercenter.change.Id', explode(',', $keyword));
            }
            //排序
            $myList = $myList->orderByRaw("changeType = 'instancy' desc,changeType = 'important' desc,changeType = 'general' desc,UpTs desc,Ts desc");
            $myListArray['total'] = $myList->count();

            //分页 changeCondition feasibilityUserId
            $pageSize = !empty(Input::get('pageSize')) ? Input::get('pageSize') : 20;
            $pageNumber = !empty(Input::get('pageNumber')) ? Input::get('pageNumber') : 1;
            $myList = $myList->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();

            $myList = Support::translationDict($myList, 'changeType', 'changeType');
            $myList = Support::translationDict($myList, 'changeState', 'changeState');
            $myList = Support::translationDict($myList, 'changeCondition', 'changeCondition');
            $myList = Support::translationDict($myList, 'changeCategory', 'changeCategory');
            $myList = Support::translationDict($myList, 'changeSubCategory', 'changeSub');
            $myList = ResContact::translationStuff($myList, 'feasibilityUserId');
            $myList = ResContact::translationStuff($myList, 'applyUserId');
            $myList = ResContact::translationStuff($myList, 'UpUserId');
            $myList = ResContact::translationStuff($myList, 'UserId');
            $myList = ResContact::translationStuff($myList, 'proDesigerId');
            $myList = ResContact::translationStuff($myList, 'checkUserId');
        }
        $myListArray['rows'] = $myList;

        return $myListArray;
    }

    /**
     * 获取CRF编号
     * @return string
     */
    public function getChangeRFCNO()
    {
        //获取部门编号，以一级部门为主，没有则取二级部门
        $user = Request()->session()->get('user')->Id;
//        try{}catch (Exception $e){}
        $depart = AuxDict::select('aux_dict.Eng')
            ->leftJoin('aux_stuff', 'aux_dict.Code', '=', 'aux_stuff.second_dept')
            ->where('aux_stuff.Id', $user)
            ->whereNotNull('aux_dict.Eng')
            ->where('aux_dict.Domain', '二级部门')
            ->where('aux_dict.DomainCode', 'second_dept')
            ->where('aux_dict.Validate', '<>', AuxDict::DISABLED_YES)
            ->first();

        if (!$depart['Eng']) {
            $depart = AuxDict::select('aux_dict.Eng')
                ->rightJoin('aux_stuff', 'aux_dict.Code', '=', 'aux_stuff.Depart')
                ->where('aux_stuff.Id', $user)
                ->whereNotNull('aux_dict.Eng')
                ->where('aux_dict.Domain', '一级部门')
                ->where('aux_dict.DomainCode', 'DepartType')
                ->where('aux_dict.Validate', '<>', AuxDict::DISABLED_YES)
                ->first();
        }

        //获取年月
        $month = date("Ym", time());
        //获取当月最大的申请单编号，如果没有则从01开始计算
        $change = Change::select('*')
            ->whereRaw("DATE_FORMAT(Ts,'%Y%m') ='$month'")
            ->where("RFCNO","like","%".'CH' . '-' . $depart['Eng'] . '-' . $month ."%")
            ->orderBy('RFCNO', 'desc')
            ->first();

        $thisNum = 1;
        if (!empty($change) && $change->RFCNO) {//按当月最后一条编号加1生成
            $num = substr($change->RFCNO,-2);
            $thisNum = $num + 1;
        }
        $thisNum = sprintf('%02s', $thisNum);
        $rfc = 'CH' . '-' . $depart['Eng'] . '-' . $month . $thisNum;
        return $rfc;
    }

    /*
     * 获取变更申请人
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
            ->where('Means', '<>', '离职员工')
            ->where('DomainCode', 'DepartType')//一级部门
            ->where(function ($arr) {
                $arr->whereNull('Validate')
                    ->orwhere('Validate', '<>', 1);
            })
            ->get()->toArray();
        foreach ($depart1 as &$item) {
            $depart2 = AuxDict::select('Means', 'Code')
                ->whereNotNull('Means')
                ->where('Means', '<>', '离职员工')
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

    //获取登录人所在的一级部门
    public function getUserDep()
    {
        $user = Request()->session()->get('user')->Id;
        $userDep = AuxStuff::select('Depart', 'second_dept')
            ->where('Id', $user)
            ->where('InValidate', '<>', AuxStuff::DISABLED_YES)
            ->first()->toArray();
        if (!$userDep['Depart']) {

            $userDepart = $userDep['second_dept'];
        } else {
            $userDepart = $userDep['Depart'];;
        }
        return $userDepart;
    }

    //获取登录人所在的二级部门
    public function getUserSecondDep()
    {
        $user = Request()->session()->get('user')->Id;
        $userDep = AuxStuff::select('Depart', 'second_dept')
            ->where('Id', $user)
            ->where('InValidate', '<>', AuxStuff::DISABLED_YES)
            ->first()->toArray();
        if (!$userDep['second_dept']) {

            $userDepart = $userDep['Depart'];
        } else {
            $userDepart = $userDep['second_dept'];
        }
        return $userDepart;
    }

    //获取结果验收人
    public static function getCheckUser()
    {
        $user = Request()->session()->get('user')->Id;
        $checkDep = AuxStuff::select('Depart', 'second_dept')
            ->where('Id', $user)
            ->where('InValidate', '<>', AuxStuff::DISABLED_YES)
            ->first()->toArray();
        if (!$checkDep['Depart']) {
            $departMembers = AuxStuff::select('Id', 'Name')->where('second_dept', $checkDep['second_dept'])
                ->where('InValidate', '<>', AuxStuff::DISABLED_YES)
                ->get()->toArray();
        } else {
            $departMembers = AuxStuff::select('Id', 'Name')->where('Depart', $checkDep['Depart'])
                ->where('InValidate', '<>', AuxStuff::DISABLED_YES)
                ->get()->toArray();
        }
        return $departMembers;
    }

    /*
     * 可行性审批表
     */
    public function getFeasibility()
    {
        $designer = $this->getAppDepartment();
        return view('change/changefeasibility', ['designer' => $designer]);
    }

    /**
     * 相关变更列表模板
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function myList()
    {
        $typeList = ThirdCallHelper::getDictArray('变更类型', 'changeType');
        $statusList = ThirdCallHelper::getDictArray('变更状态', 'changeState');
        $conditionList = ThirdCallHelper::getDictArray('触发条件', 'changeCondition');
        return view("change/mynotdonelist",
            ['statusList' => $statusList, 'typeList' => $typeList, 'conditionList' => $conditionList]);
    }

    /**
     * 所有列表
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function allList()
    {
        $typeList = ThirdCallHelper::getDictArray('变更类型', 'changeType');
        $statusList = ThirdCallHelper::getDictArray('变更状态', 'changeState');
        $conditionList = ThirdCallHelper::getDictArray('触发条件', 'changeCondition');
        return view("change/alllist",
            ['statusList' => $statusList, 'typeList' => $typeList, 'conditionList' => $conditionList]);
    }

    /**
     * 获取所有我参与过的变更单
     * @return mixed
     */
    public function getMyList()
    {
        $userId = Request()->session()->get('user')->Id;
        $depart1 = AuxStuff::where('Id', $userId)->value('Depart');//获取当前登录人员所在一级部门code
        $depart2 = AuxStuff::where('Id', $userId)->value('second_dept');//获取当前登录人员所在二级部门code
        //筛选所有当前人员参与过的变更单
        $mylist = Change::selectRaw("DISTINCT(change.Id),`change`.*")
            ->leftJoin("changerecord as b", "change.Id", "=", "b.changeId")
            ->where('b.userId', $userId)
            ->where("usercenter.change.inValidate",0);;

        //根据时间窗口或者实际完成时间筛选
        $changeStartTime = Input::get('changeStartTime') ? Input::get('changeStartTime') : date('Y-m-d H:i:s',
            time());
        $changeEndTime = Input::get('changeEndTime') ? Input::get('changeEndTime') : date('Y-m-d H:i:s', time());
        $actualStartTime = Input::get('actualStartTime') ? Input::get('actualStartTime') : date('Y-m-d H:i:s',
            time());
        $actualEndTime = Input::get('actualEndTime') ? Input::get('actualEndTime') : date('Y-m-d H:i:s', time());

        //有一个不为空,则命中条件,另一个默认为当前时间
        if (!empty(Input::get('changeStartTime')) || !empty(Input::get('changeEndTime'))) {
            $mylist = $mylist->whereBetween("changeTimeStart", [$changeStartTime, $changeEndTime]);
        }
        if (!empty(Input::get('actualStartTime')) || !empty(Input::get('actualEndTime'))) {
            $mylist = $mylist->whereBetween("actualTs", [$actualStartTime, $actualEndTime]);
        }

        //筛选变更状态和变更类型
        if ($changType = Input::get('changeType')) {
            $mylist = $mylist->where('change.changeType', $changType);
        }
        if ($changeState = Input::get('changeState')) {
            $mylist = $mylist->where('change.changeState', $changeState);
        }
        if ($changeCondition = Input::get('changeCondition')) {
            $mylist = $mylist->where('change.changeCondition', $changeCondition);
        }
        //根据关键字模糊查询
        if ($keyword = Input::get('searchInfo')) {
            $mylist = $mylist->where(function ($mylist) use ($keyword) {
                $mylist->Where('RFCNO', 'like', '%' . $keyword . '%')
                    ->orWhere('changeTitle', 'like', '%' . $keyword . '%');
            });
        }

        //排序
        $mylist = $mylist->orderByRaw("
                change.changeType = 'instancy' desc,
                change.changeType = 'important' desc,
                change.changeType = 'general' desc,
                change.UpTs desc,
                change.Ts desc"
        );
        $mylistArray['total'] = count($mylist->lists("DISTINCT(change.Id)"));
        //分页
        $pageSize = !empty(Input::get('pageSize')) ? Input::get('pageSize') : 20;
        $pageNumber = !empty(Input::get('pageNumber')) ? Input::get('pageNumber') : 1;
        $mylist = $mylist->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();
        //将部分数据中的Code转化为中文字符
        $mylist = Change::getApprover($mylist);
        $mylist = Support::translationDict($mylist, 'changeType', 'changeType');
        $mylist = Support::translationDict($mylist, 'changeState', 'changeState');
        $mylist = Support::translationDict($mylist, 'changeCondition', 'changeCondition');
        $mylist = Support::translationDict($mylist, 'changeCategory', 'changeCategory');
        $mylist = Support::translationDict($mylist, 'changeSubCategory', 'changeSub');
        $mylist = ResContact::translationStuff($mylist, 'feasibilityUserId');
        $mylist = ResContact::translationStuff($mylist, 'applyUserId');
        $mylist = ResContact::translationStuff($mylist, 'UpUserId');
        $mylist = ResContact::translationStuff($mylist, 'UserId');
        $mylist = ResContact::translationStuff($mylist, 'proDesigerId');
        $mylistArray['rows'] = $mylist;

        return $mylistArray;
    }

    /**
     * 全部变更列表
     * @return mixed
     */
    public function getAllList(Request $req)
    {
        //筛选所有当前人员参与过或者当前人员所在部门参与过的变更单 过滤所有失效变更
        $allList = Change::select("*")->where('inValidate',0);
        //筛选变更状态和变更类型
        if ($changType = Input::get('changeType')) {

        }
        if ($changeState = Input::get('changeState')) {
            $allList = $allList->where('changeState', $changeState);
        }
        //条件
        $conditionList = [
            'changeType',
            'changeState',
            'changeCondition',
            'searchInfo',
            'timeOutIds'
        ];
        $changeStartTime = $req->input('changeStartTime') ? $req->input('changeStartTime') : date('Y-m-d H:i:s',
            time());
        $changeEndTime = $req->input('changeEndTime') ? $req->input('changeEndTime') : date('Y-m-d H:i:s', time());
        $actualStartTime = $req->input('actualStartTime') ? $req->input('actualStartTime') : date('Y-m-d H:i:s',
            time());
        $actualEndTime = $req->input('actualEndTime') ? $req->input('actualEndTime') : date('Y-m-d H:i:s', time());

        //有一个不为空,则命中条件,另一个默认为当前时间
        if (!empty($req->input('changeStartTime')) || !empty($req->input('changeEndTime'))) {
            $allList = $allList->whereBetween("changeTimeStart", [$changeStartTime, $changeEndTime]);
        }
        if (!empty($req->input('actualStartTime')) || !empty($req->input('actualEndTime'))) {
            $allList = $allList->whereBetween("actualTs", [$actualStartTime, $actualEndTime]);
        }

        foreach ($conditionList as $search) {
            $keyword = $req->input($search);
            if ($keyword) {
                switch ($search) {
                    case 'changeType':
                        $allList = $allList->where('changeType', $keyword);
                        break;
                    case 'changeState':
                        $allList = $allList->where('changeState', $keyword);
                        break;
                    case 'changeCondition':
                        $allList = $allList->where('changeCondition', $keyword);
                        break;
                    case 'timeOutIds':
                        $allList = $allList->whereIn('Id', explode(',', $keyword));
                        break;
                    case 'searchInfo':
                        $allList = $allList->where(function ($supportList) use ($keyword) {
                            $supportList->Where('RFCNO', 'like', '%' . $keyword . '%')
                                ->orWhere('changeTitle', 'like', '%' . $keyword . '%');
                        });
                        break;
                    default:
                        break;
                }
            }
        }
        //排序 默认按提交时间倒叙   否则按照参数排序
        if($req->input("sortName") && $req->input("sortName") != "" && $req->input("sortOrder")){
            $allList = $allList->orderByRaw($req->input("sortName")." ".$req->input("sortOrder"));
        }else{
            $allList = $allList->orderByRaw("Ts desc");
        }
        $allListArray['total'] = $allList->count();
        //分页
        $pageSize = !empty(Input::get('pageSize')) ? Input::get('pageSize') : 20;
        $pageNumber = !empty(Input::get('pageNumber')) ? Input::get('pageNumber') : 1;
        $allList = $allList->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();
        $allList = Support::translationDict($allList, 'changeType', 'changeType');
        $allList = Support::translationDict($allList, 'changeState', 'changeState');
        $allList = Support::translationDict($allList, 'changeCondition', 'changeCondition');
        $allList = Support::translationDict($allList, 'changeCategory', 'changeCategory');
        $allList = Support::translationDict($allList, 'changeSubCategory', 'changeSub');
        $allList = ResContact::translationStuff($allList, 'feasibilityUserId');
        $allList = ResContact::translationStuff($allList, 'applyUserId');
        $allList = ResContact::translationStuff($allList, 'UpUserId');
        $allList = ResContact::translationStuff($allList, 'UserId');
        $allList = ResContact::translationStuff($allList, 'proDesigerId');
        $allListArray['rows'] = $allList;

        return $allListArray;
    }

    /**
     * 获取超时数据
     */
    public function getTimeChanged()
    {
        $statisticsList = Change::select("Id", "changeState", "UpTs", "changeType")->whereIn("changeState",
            ['approval', 'testApproval', 'approved'])->get()->toArray();

        return $this->getStatisticsResult($statisticsList);

    }

    /**
     * 根据数组计算超时类型的变更
     * @param array $dataList
     * @return array
     */
    protected function getStatisticsResult($dataList = [])
    {
        $changedRows = [
            'approval'     => [
                'ids'   => [],
                'count' => 0
            ],
            'testApproval' => [
                'ids'   => [],
                'count' => 0
            ],
            'approved'     => [
                'ids'   => [],
                'count' => 0
            ],
            'allIds'       => []
        ];
        $allIds = [];


        //approve 可行性 重大/一般--1个工作日，紧急--4H
        //testApproval 1个工作日（重大/一般）、4H（紧急）
        //approved 一般--3个工作日，重大/紧急--1个工作日

        $oneDay = 24 * 3600;
        $fourHours = 4 * 3600;
        $threeDay = 24 * 3600 * 3;

        $approvalIds = [];
        $testApprovalIds = [];
        $approvedIds = [];

        foreach ($dataList as $item) {
            if ($item['changeState'] == 'approval') {
                $diffTime = PublicMethodsHelper::diffBetweenTwoDays($item['UpTs']);
                //时间差
                $approvalNotInstancy = date('Y-m-d H:i:s', $diffTime - $oneDay);
                if ($item['changeType'] != 'instancy' && $approvalNotInstancy > $item['UpTs']) {
                    $approvalIds[] = $item['Id'];
                    $allIds[] = $item['Id'];

                }
                //时间差
                $approvalIsInstancy = date('Y-m-d H:i:s', $diffTime - $fourHours);
                if ($item['changeType'] == 'instancy' && $approvalIsInstancy > $item['UpTs']) {
                    $approvalIds[] = $item['Id'];
                    $allIds[] = $item['Id'];
                }
            }
            if ($item['changeState'] == 'testApproval') {
                $diffTime = PublicMethodsHelper::diffBetweenTwoDays($item['UpTs']);
                //时间差l
                $testApprovalNotInstancy = date('Y-m-d H:i:s', $diffTime - $oneDay);
                //$approval
                if ($item['changeType'] != 'instancy' && $testApprovalNotInstancy > $item['UpTs']) {
                    $testApprovalIds[] = $item['Id'];
                    $allIds[] = $item['Id'];

                }
                //时间差
                $testApprovalIsInstancy = date('Y-m-d H:i:s', $diffTime - $fourHours);
                if ($item['changeType'] == 'instancy' && $testApprovalIsInstancy > $item['UpTs']) {
                    $testApprovalIds[] = $item['Id'];
                    $allIds[] = $item['Id'];
                }
            }
            if ($item['changeState'] == 'approved') {
                $diffTime = PublicMethodsHelper::diffBetweenTwoDays($item['UpTs']);
                //时间差
                $approvedNotInstancy = date('Y-m-d H:i:s', $diffTime - $oneDay);
                //approved
                if ($item['changeType'] != 'general' && $approvedNotInstancy > $item['UpTs']) {
                    $approvedIds[] = $item['Id'];
                    $allIds[] = $item['Id'];
                }
                //一般为3天
                $approvedIsInstancy = date('Y-m-d H:i:s', $diffTime - $threeDay);
                if ($item['changeType'] == 'general' && $approvedIsInstancy > $item['UpTs']) {
                    $approvedIds[] = $item['Id'];
                    $allIds[] = $item['Id'];
                }
            }
        }
        $changedRows['approval']['ids'] = $approvalIds;
        $changedRows['approval']['count'] = count($approvalIds);

        $changedRows['testApproval']['ids'] = $testApprovalIds;
        $changedRows['testApproval']['count'] = count($testApprovalIds);

        $changedRows['approved']['ids'] = $approvedIds;
        $changedRows['approved']['count'] = count($approvedIds);
        $changedRows['allIds'] = $allIds;

        return $changedRows;
    }

    /**
     * 获取待办变更超时数据
     */
    public function getTodoTimeChanged()
    {
        $userId = Request()->session()->get('user')->Id;

        $pmRole = $this->getProcessRole();
        $statusRole = [];
        foreach ($pmRole as $item) {
            $statusArr = explode("_", $item);
            if (isset($statusArr[1])) {
                $statusRole[] = $statusArr[1];
            }
        }
        $userDept = $this->getUserDep();
        $userSecondDept = $this->getUserSecondDep();
        if (!empty($statusRole)) {
            $myList = Change::select('usercenter.change.*')->leftJoin('res.aux_stuff as b', 'b.Id', '=',
                'usercenter.change.proDesigerId');
            $myList = $myList->where(function ($myList) use ($statusRole, $userId, $userDept, $userSecondDept) {
                foreach ($statusRole as $roleItem) {
                    switch ($roleItem) {
                        case 'reject':
                            $myList->orwhereRaw("(changeState = 'reject' and applyUserId = $userId)");
                            break;
                        case 'approval':
                            $myList->orwhereRaw("(changeState = 'approval' and (feasibilityGroupId = '$userDept' or feasibilityGroupId = '$userSecondDept'))");
                            break;
                        case 'design':
                            $myList->orwhereRaw("(changeState = 'design' and proDesigerId = $userId)");
                            break;
                        case 'actualize':
                            $myList->orwhereRaw("(changeState = 'actualize' and (proDesigerGroupId = '$userDept' or proDesigerGroupId = '$userSecondDept'))");
                            break;
                        case 'test':
                            $myList->orwhereRaw("(changeState = 'test' and (testGroupId = '$userDept' or testGroupId = '$userSecondDept'))");
                            break;
                        case 'testApproval':
                            $myList->orwhereRaw("(changeState = 'testApproval' and b.parentId = $userId)");
                            break;
                        case 'release':
                            $myList->orwhereRaw("(changeState = 'release' and changeImplementUserId =$userId )");
                            break;
                        case 'approved':
                            $myList->orwhereRaw("(changeState = 'approved' and checkUserId = $userId)");
                            break;
                        default:
                            break;
                    }

                }
            });

        }

        $todoList = $myList->get()->toArray();

        return $this->getStatisticsResult($todoList);
    }

    /*
     * 根据角色查询变更结果验收人
     */
    public function getCheckUserName($name)
    {
        $checkUser = AuxStuff::select('res.aux_stuff.Name', 'res.aux_stuff.Id')
            ->Join('auth.authorities', 'res.aux_stuff.Login', '=', 'auth.authorities.username')
            ->Raw("leftjoin res.aux_dict as c on res.aux_stuff.Depart=c.`Code` and  c.DomainCode='DepartType'")
            ->Raw("leftjoin res.aux_dict as d on res.aux_stuff.second_dept=d.`Code` and  d.DomainCode='second_dept'")
            ->where('auth.authorities.authority', 'change_approved')
            ->whereRaw("((res.aux_stuff.Name like '%$name%') or (res.aux_stuff.Login like '%$name%'))")
            ->get();
        return $checkUser;
    }

    /**
     * 根据角色获取可行性审批部门
     */
    public static function getFisibilityGroup()
    {
        /**
         * select b.Depart,c.Means,b.second_dept,d.Means,b.`Name`,a.username,a.authority from auth.authorities as a
         * JOIN res.aux_stuff as b on a.username=b.Login
         * LEFT JOIN res.aux_dict as c on b.Depart=c.`Code` and  c.DomainCode='DepartType'
         * LEFT JOIN res.aux_dict as d on b.second_dept=d.`Code` and  d.DomainCode='second_dept'
         * where a.authority='change_approval'
         */
        $fisibilityGroup = DB::select("select DISTINCT(d.Means),b.Depart,c.Means as MeansOne,b.second_dept,d.Means as MeansTwo from auth.authorities as a JOIN res.aux_stuff as b on a.username=b.Login LEFT JOIN res.aux_dict as c on b.Depart=c.`Code` and  c.DomainCode='DepartType' LEFT JOIN res.aux_dict as d on b.second_dept=d.`Code` and  d.DomainCode='second_dept' where a.authority='change_approval'");
        $oneGroup = [];
        if ($fisibilityGroup) {
            /**
             * 取出一级部门
             */
            foreach ($fisibilityGroup as $item) {
                if (!empty($item->second_dept)) {
                    $oneGroup[$item->Depart]['name'] = $item->MeansOne;
                }
            }
            /**
             * 取出一级部门下面的二级部门并且返回
             */
            foreach ($fisibilityGroup as $childItem) {
                foreach ($oneGroup as $oneItem) {
                    if ($oneItem['name'] == $childItem->MeansOne && $childItem->MeansTwo != "") {
                        $oneGroup[$childItem->Depart]['child'][$childItem->second_dept] = $childItem->MeansTwo;
                    } else {
                        if ($childItem->MeansTwo != null) {
                            $oneGroup[$childItem->Depart]['name'] = $childItem->MeansOne;
                        }
                    }
                }
            }

        }
        return $oneGroup;
    }

    /*
    * 获取所有问题列表
    */
    public function getRelateIssue()
    {
        $issueLists = Issue::select('*')->orderBy("Id", "desc");
        if ($keyword = Input::get('searchInfo')) {
            $issueLists = $issueLists->where(function ($issueLists) use ($keyword) {
                $issueLists->Where('issueNo', 'like', '%' . $keyword . '%')
                    ->orWhere('issueTitle', 'like', '%' . $keyword . '%');
            });
        }
        if ($changeId = Input::get('changeId')) {
            $sql = "select issueId from `Correlation` where `changeId` = $changeId and `issueId` <> 0 and `inValidate` = 0 order by `Id` desc";
            $issueLists = $issueLists->whereRaw("(Id not in($sql))");
        }
        $total = $issueLists->count();
        $issueList['total'] = $total;
        $pageSize = !empty(Input::get('pageSize')) ? Input::get('pageSize') : 5;
        $pageNumber = !empty(Input::get('pageNumber')) ? Input::get('pageNumber') : 1;
        $issueLists = $issueLists->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();
        $issueLists = Support::translationDict($issueLists, 'issueCategory', 'WorksheetTypeOne');
        $issueLists = Support::translationDict($issueLists, 'issuePriority', 'issuePriority');
        $issueList['rows'] = $issueLists;
        return $issueList;
    }

    /**
     * 弹出将被关联的问题列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function relateIssue(Request $request)
    {
        $data = $request->all();
        $changeId = $data["changeId"];
        return view('change/relateissue', compact("changeId"));
    }

    //获取已经关联的问题列表
    public function relateIssueData(Request $req)
    {
        $id = $req->input("changeId");
        $relateIssue = Correlation::where("changeId", $id)->where('issueId', '<>', 0)->where('inValidate',
            0)->orderBy("Id", "desc")->get()->toArray();
        //定义返回结果格式
        $arr = [
            'rows'  => [],
            'total' => 0
        ];
        if (!empty($relateIssue)) {
            foreach ($relateIssue as &$issueRecord) {
                $arr['rows'][] = Issue::where("Id", $issueRecord['issueId'])->orderBy("Id", "desc")->first();
            }
            $arr['rows'] = Support::translationDict($arr['rows'], 'issueCategory', 'WorksheetTypeOne');
            $arr['rows'] = Support::translationDict($arr['rows'], 'issuePriority', 'issuePriority');
            $arr['rows'] = ResContact::translationStuff($arr['rows'], 'issueSubmitUserId');
            $arr['total'] = count($arr['rows']);
        }
        return $arr;
    }

    /*
    * 获取所有工单列表
    */
    public function getRelateSupport()
    {
        $supportLists = Support::select('*')->orderBy("Id", "desc");
        if ($keyword = Input::get('searchInfo')) {
            $supportLists = $supportLists->where(function ($supportLists) use ($keyword) {
                $supportLists->Where('Id', 'like', '%' . $keyword . '%')
                    ->orWhere('Title', 'like', '%' . $keyword . '%');
            });
        }
        if ($changeId = Input::get('changeId')) {
            $sql = "select supportId from `Correlation` where `changeId` = $changeId and `supportId` <> 0 and `inValidate` = 0 order by `Id` desc";
            $supportLists = $supportLists->whereRaw("(Id not in($sql))");
        }
        $total = $supportLists->count();
        $supportList['total'] = $total;
        $pageSize = !empty(Input::get('pageSize')) ? Input::get('pageSize') : 5;
        $pageNumber = !empty(Input::get('pageNumber')) ? Input::get('pageNumber') : 1;
        $supportLists = $supportLists->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();
        $supportLists = Support::translationDict($supportLists, 'ClassInficationOne', 'WorkSheetTypeOne');
        $supportLists = ResContact::translationStuff($supportLists, 'CreateUserId');
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
        $changeId = $data["changeId"];
        return view('change/relatesupport', compact("changeId"));
    }

    //获取已经关联的问题列表
    public function relateSupportData(Request $req)
    {
        $changeId = $req->input("changeId");
        $relateSupport = Correlation::where("changeId", $changeId)->where('supportId', '<>', 0)->where('inValidate',
            0)->orderBy("Id", "desc")->get()->toArray();
        //定义返回结果格式
        $arr = [
            'rows'  => [],
            'total' => 0
        ];
        if (!empty($relateSupport)) {
            foreach ($relateSupport as &$SupportRecord) {
                $arr['rows'][] = Support::where("Id", $SupportRecord['supportId'])->orderBy("Id", "desc")->first();
            }
            $arr['rows'] = Support::translationDict($arr['rows'], 'ClassInficationOne', 'WorkSheetTypeOne');
            $arr['rows'] = Support::translationCusName($arr['rows'], 'CustomerInfoId');
            $arr['rows'] = ResContact::translationStuff($arr['rows'], 'CreateUserId');
            $arr['total'] = count($arr['rows']);
        }
        return $arr;
    }
}

