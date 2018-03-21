<?php
/**
 * User: Lidz<lidz@51idc.com>
 * Date: 2016/9/6 10:23
 */

namespace Itsm\Http\Controllers\Change;

use Illuminate\Support\Facades\Input;
use Itsm\Http\Controllers\Controller;
use Itsm\Http\Helper\ProcessMakerApi;
use Itsm\Http\Helper\PublicMethodsHelper;
use Itsm\Http\Helper\ThirdCallHelper;
use Itsm\Jobs\SendChangeEmail;
use Itsm\Model\Auth\Authorities;
use Itsm\Model\Res\AuxDict;
use Itsm\Model\Res\AuxStuff;
use Itsm\Model\Usercenter\Change;
use Itsm\Model\Usercenter\ChangeRecord;
use Itsm\Model\Usercenter\Correlation;
use Itsm\Model\Usercenter\Issue;

class ChangeDetailsController extends Controller
{
    public function changeDetails($id)
    {
        if ($id) {
            $change = Change::where('Id', $id)->first();
            $pmRole = $this->getProcessRole();
            $res = new ProcessMakerApi(env("CHANGE_PROCESS_ID"), env("CHANGE_STEP_ONE_ID"));
            //获取工作流信息
            $tokenInfo = $this->getAccessTokenByRole("employee");
            $token = $tokenInfo['access_token'];
            //同步第三方
            if (!$newStatus = $this->syncCaseStatus($change->caseId, $token, $change->changeState)) {
                return "第三方API校验失败，请重试";
            }
            //操作权限验证
            $hasRule = false;
            if (in_array("change_" . $newStatus, $pmRole)) {
                $hasRule = true;
            }
            //重新赋值
            $change->changeState = $newStatus;
            $dynaForm = $res->getCaseDynaForms($token, $change->caseId, "reply-btn btnSub");
            //获取所有变更操作记录
            $changeRecord = ChangeRecord::where('changeId', $id)->get();
            $changeRecord = Change::translationStuff($changeRecord, 'userId');
            $changeRecord = Change::subStuffName($changeRecord, 'userId');
            $user = Request()->session()->get('user');
            //变更实施结果数组
            $implementResultArr = AuxDict::select('Code', 'Means')->where('DomainCode', 'implementResult')->get();
            //验证结果数组
            $checkResultArr = AuxDict::select('Code', 'Means')->where('DomainCode', 'checkResult')->get();
            $departStuffArr = PublicMethodsHelper::getDepartStuff($change->proDesigerGroupId);//获取部门所有员工
            $designDepart = ThirdCallHelper::getAppDepartment('change_design');//获取所有具有变更规划权限的一级及二级部门
            $testDepart = ThirdCallHelper::getAppDepartment('change_test');//获取所有具有变更规划权限的一级及二级部门
            $releaseDepart = ThirdCallHelper::getAppDepartment('change_release');//获取所有具有变更实施权限的一级及二级部门
            $designPermission = in_array($user->Id, $departStuffArr) ? true : false;//判断当前登陆人员是否在方案制定组
            $departStuffArr = PublicMethodsHelper::getDepartStuff($change->testGroupId);//获取部门所有员工
            $testPermission = in_array($user->Id, $departStuffArr) ? true : false;//判断当前登陆人员是否在测试方案组
            $departStuffArr = PublicMethodsHelper::getDepartStuff($change->feasibilityGroupId);//获取部门所有员工
            $feasibilityPermission = in_array($user->Id, $departStuffArr) ? true : false;//判断当前登陆人员是否在可行性审批部门
            $departStuffArr = PublicMethodsHelper::getDepartStuff($change->applyUserId);//获取部门所有员工
            $toapplyPermission = in_array($user->Id, $departStuffArr) ? true : false;//判断当前登录人是否为变更申请人
            //查找方案负责人上级的Id
            $leaderId = '';
            if (!empty($change->proDesigerId)) {
                $leaderId = AuxStuff::where('Id', $change->proDesigerId)->value('parentId');
            }
            //匹配所有上传过的文件
            $uploadFiles = $this->getUploadFiles($change);
            //变更状态转化为数字方便判断
            $statusStep = $this->getStep($newStatus);
            $arr = ['change'                => $change,
                    'changeRecord'          => $changeRecord,
                    'statusStep'            => $statusStep,
                    'user'                  => $user,
                    'designPermission'      => $designPermission,
                    'testPermission'        => $testPermission,
                    'feasibilityPermission' => $feasibilityPermission,
                    'designDepart'          => $designDepart,
                    'testDepart'            => $testDepart,
                    'releaseDepart'         => $releaseDepart,
                    'toapplyPermission'     => $toapplyPermission,
                    'stepForm'              => $dynaForm,
                    'hasRule'               => $hasRule,
                    'uploadFiles'           => $uploadFiles,
                    'implementResultArr'    => $implementResultArr,
                    'checkResultArr'        => $checkResultArr,
                    'leaderId'              => $leaderId,];
            return view("change/changestepone", $arr);
        } else {
            return '参数错误！';
        }
    }

    /**
     * 正则匹配所有上传过的文件
     * @param $change
     * @return string
     */
    protected function getUploadFiles($change)
    {
        $reg = '/<a[^>]*?href=["http:\/\/]?(.*?)["]?\s[^>]*?>.*<\/a>/';
        preg_match_all($reg, $change->feasibilityOpinion, $match1);
        preg_match_all($reg, $change->changeSchemeCont, $match2);
        preg_match_all($reg, $change->planImplementCont, $match3);
        preg_match_all($reg, $change->planRollbackCont, $match4);
        preg_match_all($reg, $change->changeEffectCont, $match5);
        preg_match_all($reg, $change->testCont, $match6);
        preg_match_all($reg, $change->checkTestCont, $match7);
        preg_match_all($reg, $change->implementCont, $match8);
        preg_match_all($reg, $change->checkResultCont, $match9);
        $match = 'match';
        $uploadFiles = '';
        foreach (range(1, 9) as $num) {
            if (isset(${$match . $num}[0][0])) {
                $uploadFiles .= ${$match . $num}[0][0] . '&nbsp;&nbsp;&nbsp;&nbsp;';
            }
        }
        return $uploadFiles;
    }

    /**
     * 将状态转化为数字方便判断
     * @param $newStatus
     * @return int
     */
    protected function getStep($newStatus)
    {
        $statusStep = 0;
        switch ($newStatus) {
            case 'reject':
                $statusStep = -1;//变更驳回
                break;
            case 'approval':
                $statusStep = 1;//可行性审批
                break;
            case 'design':
                $statusStep = 2;//变更方案规划
                break;
            case 'actualize':
                $statusStep = 3;//实施/回退方案制定
                break;
            case 'test':
                $statusStep = 4;//方案测试
                break;
            case 'testApproval':
                $statusStep = 5;//方案测试结果审批
                break;
            case 'release':
                $statusStep = 6;//变更发布实施
                break;
            case 'approved':
                $statusStep = 7;//变更结果验证
                break;
            case 'completed':
                $statusStep = 8;//完成
                break;
        }
        return $statusStep;
    }

    /**
     * 获取某部门所有员工
     * @return array
     */
    public function getDepStuffs()
    {
        if ($id = Input::get('depId')) {
            $role = Input::get('role') ? Input::get('role') : null;
            return PublicMethodsHelper::getDepartStuffName($id, $role);
        } else {
            return [];
        }
    }

    /**
     * 可行性审批不通过 状态为申请驳回
     * @return string
     */
    public function saveToApply($id)
    {
        if ($id) {
            $change = Change::where('Id', $id)->first();
            $pmRole = $this->getProcessRole();
            $res = new ProcessMakerApi(env("CHANGE_PROCESS_ID"), env("CHANGE_STEP_ONE_ID"));
            //获取工作流信息
            $tokenInfo = $this->getAccessTokenByRole("employee");
            $token = $tokenInfo['access_token'];
            $caseInfo = $res->getCaseCurrentTask($change->caseId, $token);
            //操作权限验证
            $hasRule = false;
            if (in_array("change_" . $caseInfo['status'], $pmRole)) {
                $hasRule = true;
            }
            $dynaForm = $res->getCaseDynaForms($token, $change->caseId, "reply-btn btnSub");
            $user = Request()->session()->get('user');
            $changeTypeArr = ThirdCallHelper::getDictArray('变更类型', 'changeType');
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
            $conditionList = ThirdCallHelper::getDictArray('触发条件', 'changeCondition');
            $checkUser = ChangeController::getCheckUser();
            $oneGroup = ChangeController::getFisibilityGroup();
            $departStuffArr = PublicMethodsHelper::getDepartStuff($change->applyUserId);//获取部门所有员工
            $toapplyPermission = in_array($user->Id, $departStuffArr) ? true : false;//判断当前登录人是否为变更申请人
            return view("change/changetoapply", [
                'change'            => $change,
                'changeTypeArr'     => $changeTypeArr,
                'user'              => $user,
                'oneGroup'          => $oneGroup,
                'toapplyPermission' => $toapplyPermission,
                'conditionList'     => $conditionList,
                'checkUser'         => $checkUser,
                'changeCategory'    => $changeCategory,
                'stepForm'          => $dynaForm,
            ]);
        } else {
            return '参数错误！';
        }
    }

    /**
     * 驱动工作流后获取驱动后状态
     * @param $all
     * @return mixed
     */
    protected function getNextStatus($all)
    {
        $variableVar = [];
        $caseId = Change::where('Id', $all['changeId'])->value('caseId');
        //获取变量
        $processVar = $all['processVar'];
        if ($processVar) {
            $variableValue = $all[$processVar];
            $variableVar = [
                $processVar => $variableValue
            ];
        }
        $res = new ProcessMakerApi(env("CHANGE_PROCESS_ID"), env("CHANGE_STEP_ONE_ID"));
        $tokenInfo = $this->getAccessTokenByRole("employee");
        $caseInfo = $res->getCaseCurrentTask($caseId, $tokenInfo['access_token']);
        //重新获取token
        $currentTokenInfo = $this->getAccessTokenByRole("change_" . $caseInfo['status']);
        $nextRes = $res->nextCase($caseId, $currentTokenInfo['access_token'], $variableVar);
        if (isset($nextRes['status'])) {
            $status = $nextRes['status'];
        } else {
            $status = $caseInfo['status'];
        }
        return $status;
    }

    /**
     * 获取当前属于何种操作（只保存，审核通过以及审核不通过）
     * @param $all
     * @param $status
     * @return string
     */
    protected function getOperate($all, &$status)
    {
        if ($all['passOrNo'] == 'save') {
            $passOrNo = '保存';
        } else {
            $status = $this->getNextStatus($all);
            $processVar = $all['processVar'];
            if ($processVar) {
                $variableValue = $all[$processVar];
                $passOrNo = $variableValue == 1 ? '审核通过' : '审核不通过';
            } else {
                $passOrNo = '提交';
            }
        }
        return $passOrNo;
    }

    /**
     * 获取某部门含有某权限的所有人员Id，转化为字符串
     * @param $role
     * @param $depart
     * @return string
     */
    public function getDepartIds($role, $depart)
    {
        $stuffIds = Authorities::select('b.Id')
            ->Join('res.aux_stuff as b', 'b.Login', '=', 'auth.authorities.username')
            ->raw("LEFT JOIN res.aux_dict as c on b.Depart=c.`Code` and  c.DomainCode='DepartType'")
            ->Raw("LEFT JOIN res.aux_dict as d on b.second_dept=d.`Code` and  d.DomainCode='second_dept'")
            ->where('auth.authorities.authority', $role)
            ->where(function ($stuffIds) use ($depart) {
                $stuffIds->where('b.Depart', $depart)
                    ->orwhere('b.second_dept', $depart);
            })->get()->toArray();
        $arr = [];
        foreach ($stuffIds as $stuffId) {
            $arr[] = $stuffId['Id'];
        }
        $Ids = implode(",", $arr);
        return $Ids;
    }

    /**
     * 获取个流程节点邮件发送的目标人员Ids
     * @param $status
     * @param $all
     * @return string
     */
    public function getNextIds($status, $all)
    {
        if ($status) {
            $userIds = '';
            if(isset($all['changeId'])){
                $applyUserId = Change::where('Id', $all['changeId'])->value('applyUserId');
                switch ($status) {
                    case 'reject'://通知变更申请人
                        $userIds = "";
                        break;
                    case 'design'://变更方案规划
                        $userIds = Change::where('Id', $all['changeId'])->value('proDesigerId');
                        break;
                    case 'actualize'://实施/回退方案制定
                        $proDesigerGroupId = Change::where('Id', $all['changeId'])->value('proDesigerGroupId');
                        $userIds = $this->getDepartIds('change_actualize', $proDesigerGroupId);;
                        break;
                    case 'test'://方案测试
                        $testGroupId = Change::where('Id', $all['changeId'])->value('testGroupId');
                        $userIds = $this->getDepartIds('change_test', $testGroupId);
                        break;
                    case 'testApproval'://方案测试结果审批 有方案负责人上级审核
                        $leaderId = '';
                        $testGroupId = Change::where('Id', $all['changeId'])->value('proDesigerId');
                        if ($testGroupId) {
                            $leaderId = AuxStuff::where('Id', $testGroupId)->value('parentId');
                        }
                        $userIds = $this->getDepartIds('change_test', $testGroupId);
                        break;
                    case 'release'://变更发布实施
                        $userIds = Change::where('Id', $all['changeId'])->value('changeImplementUserId');
                        break;
                    case 'approved'://变更结果验证
                        $userIds = Change::where('Id', $all['changeId'])->value('checkUserId');
                        break;
                }
                $userIds .= $userIds == "" ? $applyUserId:",$applyUserId";//任何流程节点都药通知变更申请人
            }elseif($status == 'approval'){//通知 具有可行性审批权限的人和申请者本人
                $userIds = $this->getDepartIds('change_approval', $all['feasibilityGroupId']);
                $applyUserId = $userId = Request()->session()->get('user')->Id;
                $userIds .= ",$applyUserId";
            }
            $userIds = implode(",",array_unique(explode(',',$userIds)));
            return $userIds;
        } else {
            return '';
        }
    }

    /**
     * 可行性审批操作
     * @return string
     */
    public function saveFeasibility()
    {
        try {
            $userId = Request()->session()->get('user')->Id;
            $all = Input::except('_token');
            /**
             * step1 更新数据
             */
            $feasibilityOpinion = PublicMethodsHelper::htmlToSafe($all['feasibilityOpinion']);
            $update = [
                'feasibilityOpinion' => $feasibilityOpinion,
                'proDesigerGroupId'  => $all['proDesigerGroupId'],
                'testGroupId'        => $all['testGroupId'],
                'proDesigerId'       => $all['proDesigerId'],
                'feasibilityTs'      => date('Y-m-d H:i:s'),
                'feasibilityUserId'  => $userId,
                'UpUserId'           => $userId,
                'UpTs'               => date('Y-m-d H:i:s')
            ];
            $save = Change::where('Id', $all['changeId'])->update($update);

            /**
             * step 2 驱动
             */
            $status = $all['changeState'];
            $passOrNo = $this->getOperate($all, $status);
            $save = Change::where('Id', $all['changeId'])->update([
                'changeState' => $status
            ]);
            /**
             * step3 新增记录表
             * 保存操作记录在changerecord表
             */
            $replyMsg = '';
            if (!empty($all['feasibilityOpinion'])) {
                $replyMsg .= '<div class="stl">可行性审批意见</div>' . '<div class="cont">'.$feasibilityOpinion.'</div>';
            }
            if (!empty($all['proDesigerGroupId'])) {
                $replyMsg .= '<span class="stl">方案制定组：' .AuxDict::where('Code', $all['proDesigerGroupId'])->value('Means').'</span>';
            }
            if (!empty($all['proDesigerId'])) {
                $replyMsg .= '；<span class="stl">方案责任人：' .AuxStuff::where('Id', $all['proDesigerId'])->value('Name').'</span>';
            }
            if (!empty($all['testGroupId'])) {
                $replyMsg .= '；<span class="stl">测试方案组：'.AuxDict::where('Code', $all['testGroupId'])->value('Means').'</span>';
            }
            $retId = ChangeRecord::insertGetId([
                'changeId'         => $all['changeId'],
                'replycontent'     => $replyMsg,
                'changeState'      => $all['changeStateMeans'],
                'changeStatusCode' => $all['changeState'],
                'passOrNo'         => $passOrNo,
                'userId'           => $userId,
                'ts'               => date('Y-m-d H:i:s')
            ]);
            //如果不是保存操作 则添加队列任务发送邮件或短信
            if ($all['passOrNo'] != 'save') {
                //添加到队列 发送邮件或短信
                $st = ThirdCallHelper::getDictMeans("变更状态","changeState",$status);
                $str = "你有一个变更单:单号(".$all['changeNo']."),需要进行".$st."操作，变更主题：";
                $title = '变更标题：'.$str. $all['changeTitle'] . ';上一步由' . ThirdCallHelper::getStuffName($userId) . $passOrNo . $all['changeStateMeans'];
                $userIds = $this->getNextIds($status, $all);
                $replyMsg = "你有一个变更单:单号(".$all['changeNo']."),需要进行".$st."操作。  ".$replyMsg;
                $replyMsg = preg_replace(['/<br>/'], ["\n"], $replyMsg);
                $job = new SendChangeEmail($title, $userIds, $all['changeType'], $replyMsg); //创建队列任务
                $this->dispatch($job);
            }
        } catch (\Exception $ex) {
            return array('status' => false, 'msg' => $ex->getMessage());
        }
        return array('status' => true, 'msg' => "操作成功");
    }

    /*
     * 变更驳回提交数据库保存
     */
    public function saveToapplyData()
    {
        $userId = Request()->session()->get('user')->Id;
        $data = Input::except('_token');
        $reason = PublicMethodsHelper::htmlToSafe($data['changeReason']);
        $context = PublicMethodsHelper::htmlToSafe($data['changeContext']);
        $risk = PublicMethodsHelper::htmlToSafe($data['changeRisk']);
        /**
         * step1 更新数据
         */
        if ($data) {
            $update = [
                "changeTitle"        => $data['changeTitle'],
                "changeObject"       => $data['changeObject'],
                "expectTs"           => $data['expectTs'],
                "changeType"         => $data['changeType'],
                "changeCondition"    => $data['changeCondition'],
                "changeCategory"     => $data['changeCategory'],
                "changeSubCategory"  => $data['changeSubCategory'],
                "changeReason"       => $reason,
                "changeContext"      => $context,
                "changeRisk"         => $risk,
                "feasibilityGroupId" => $data['feasibilityGroupId'],
                "checkUserId"        => $data['checkUserId'],
                "UpUserId"           => $userId,
                'UpTs'               => date('Y-m-d H:i:s')
            ];
            $save = Change::where('Id', $data['changeId'])->update($update);
        }

        /**
         * step 2 驱动
         */
        $status = $data['changeState'];
        $passOrNo = $this->getOperate($data, $status);
        $save = Change::where('Id', $data['changeId'])->update([
            'changeState' => $status
        ]);
        $processVar = $data['processVar'];
        if ($processVar) {
            $variableValue = $data[$processVar];
            $passOrNo = $variableValue == 1 ? '重新提交' : '作废';
        }
        if ($update == false) {//插入数据失败
            return ['status' => false, 'statusMsg' => '提交出错,请稍后再试!'];
        } else {
            $url = env("APP_URL", "http://www.itsm.com");
            $replyContent = '';
            $replyContent .= '<div class="stl">变更主题</div>' .'<div class="cont">'.$data['changeTitle'].'</div>';
            $replyContent .= '<div class="stl">期望完成时间：' .$data['expectTs'];
            $replyContent .= '&nbsp;&nbsp;&nbsp;&nbsp;'. "<a href='$url/change/details/{$data['changeId']}#detailsArea'>详情参详变更主体信息</a>".'</div>';
            $recordData = ChangeRecord::insert([
                    'changeId'         => $data['changeId'],
                    'Ts'               => date('Y-m-d H:i:s'),
                    'replycontent'     => $replyContent,
                    'userId'           => $userId,
                    'changeState'      => '变更申请',
                    'changeStatusCode' => $data['changeState'],
                    'passOrNo'         => $passOrNo
                ]
            );
            //如果不是保存操作 则添加队列任务发送邮件或短信
            if ($data['passOrNo'] != 'save') {
                //添加到队列 发送邮件或短信
                $st = ThirdCallHelper::getDictMeans("变更状态","changeState",$status);
                $str = "你有一个变更单:单号(".$data['changeId']."),需要进行".$st."操作，  ";
                $title = '变更标题：'.$str. $data['changeTitle'] . ';上一步由' . ThirdCallHelper::getStuffName($userId) . $passOrNo . '变更申请';
                $userIds = $this->getNextIds($status, $data);
                $job = new SendChangeEmail($title, $userIds, $data['changeType'], $replyContent); //创建队列任务
                $this->dispatch($job);
            }

            return ['status' => true, 'statusMsg' => '操作成功', 'currentAction' => 'editApproval'];
        }
    }

    /**
     * 方案规划操作
     * @return string
     */
    public function saveProgramme()
    {
        try {
            $userId = Request()->session()->get('user')->Id;
            $all = Input::except('_token');
            /**
             * step1 更新数据
             */
            $changeSchemeCont = PublicMethodsHelper::htmlToSafe( $all['changeSchemeCont']);
            $update = [
                'changeSchemeCont' => $changeSchemeCont,
                'UpUserId'         => $userId,
                'UpTs'             => date('Y-m-d H:i:s')
            ];
            if (!empty($all['changeTimeStart'])) {
                $update['changeTimeStart'] = $all['changeTimeStart'];
            }
            if (!empty($all['changeTimeEnd'])) {
                $update['changeTimeEnd'] = $all['changeTimeEnd'];
            }
            if (!empty($all['changeExpectTs'])) {
                $update['changeExpectTs'] = $all['changeExpectTs'];
            }
            $save = Change::where('Id', $all['changeId'])->update($update);

            /**
             * step 2 驱动
             */
            $status = $all['changeState'];
            $passOrNo = $this->getOperate($all, $status);
            $save = Change::where('Id', $all['changeId'])->update([
                'changeState' => $status
            ]);
            /**
             * step3 新增记录表
             * 保存操作记录在changerecord表
             */
            $replyMsg = '';
            if (!empty($all['changeSchemeCont'])) {
                $replyMsg .= '<div class="stl">变更方案规划</div>' . '<div class="cont">'.$changeSchemeCont.'</div>';
            }
            if (!empty($all['changeTimeStart'])) {
                $replyMsg .= '<div class="stl">变更时间窗口：' . $all['changeTimeStart'];
            }
            if (!empty($all['changeTimeEnd'])) {
                $replyMsg .= '~' . $all['changeTimeEnd'];
            }
            if (!empty($all['changeExpectTs'])) {
                $replyMsg .= '；预计完成时间：' . $all['changeExpectTs'].'</div>';
            }
            if (!empty($replyMsg)) {
                $retId = ChangeRecord::insertGetId([
                    'changeId'         => $all['changeId'],
                    'replycontent'     => $replyMsg,
                    'changeState'      => $all['changeStateMeans'],
                    'changeStatusCode' => $all['changeState'],
                    'passOrNo'         => $passOrNo,
                    'userId'           => $userId,
                    'ts'               => date('Y-m-d H:i:s')
                ]);
            }
            //如果不是保存操作 则添加队列任务发送邮件或短信
            if ($all['passOrNo'] != 'save') {
                //添加到队列 发送邮件或短信
                $st = ThirdCallHelper::getDictMeans("变更状态","changeState",$status);
                $str = "你有一个变更单:单号(".$all['changeNo']."),需要进行".$st."操作，变更主题：";
                $title = '变更标题：'.$str. $all['changeTitle'] . ';上一步由' . ThirdCallHelper::getStuffName($userId) . $passOrNo . $all['changeStateMeans'];
                $userIds = $this->getNextIds($status, $all);
                $replyMsg = "你有一个变更单:单号(".$all['changeNo']."),需要进行".$st."操作。  ".$replyMsg;
                $replyMsg = preg_replace(['/<br>/'], ["\n"], $replyMsg);
                $job = new SendChangeEmail($title, $userIds, $all['changeType'], $replyMsg); //创建队列任务
                $this->dispatch($job);
            }
        } catch (\Exception $ex) {
            return array('status' => false, 'msg' => $ex->getMessage());
        }
        return array('status' => true, 'msg' => "操作成功");
    }

    /**
     * 方案制定操作
     * @return string
     */
    public function saveProdesign()
    {
        try {
            $userId = Request()->session()->get('user')->Id;
            $all = Input::except('_token');
            $planImplementCont = PublicMethodsHelper::htmlToSafe( $all['planImplementCont']);
            $planRollbackCont = PublicMethodsHelper::htmlToSafe( $all['planRollbackCont']);
            $changeEffectCont = PublicMethodsHelper::htmlToSafe( $all['changeEffectCont']);
            /**
             * step1 更新数据
             */
            $update = [
                'planImplementCont' => $planImplementCont,
                'planRollbackCont'  => $planRollbackCont,
                'changeEffectCont'  => $changeEffectCont,
                'UpUserId'          => $userId,
                'UpTs'              => date('Y-m-d H:i:s')
            ];
            if (!empty($all['changeTimeStart'])) {
                $update['changeTimeStart'] = $all['changeTimeStart'];
            }
            if (!empty($all['changeTimeEnd'])) {
                $update['changeTimeEnd'] = $all['changeTimeEnd'];
            }
            if (!empty($all['changeExpectTs'])) {
                $update['changeExpectTs'] = $all['changeExpectTs'];
            }
            $save = Change::where('Id', $all['changeId'])->update($update);

            /**
             * step 2 驱动
             */
            $status = $all['changeState'];
            $passOrNo = $this->getOperate($all, $status);
            $save = Change::where('Id', $all['changeId'])->update([
                'changeState' => $status
            ]);
            /**
             * step3 新增记录表
             * 保存操作记录在changerecord表
             */
            $replyMsg = '';
            if (!empty($all['planImplementCont'])) {
                $replyMsg .= '<div class="stl">实施方案</div>' . '<div class="cont">'.$planImplementCont.'</div>';
            }
            if (!empty($all['planRollbackCont'])) {
                $replyMsg .= '<div class="stl">回退方案</div>' . '<div class="cont">'.$planRollbackCont.'</div>';
            }
            if (!empty($all['changeEffectCont'])) {
                $replyMsg .= '<div class="stl">变更风险及影响分析</div>' . '<div class="cont">'.$changeEffectCont.'</div>';
            }
            if (!empty($all['changeTimeStart'])) {
                $replyMsg .= '<div class="stl">变更时间窗口：' . $all['changeTimeStart'];
            }
            if (!empty($all['changeTimeEnd'])) {
                $replyMsg .= '~' . $all['changeTimeEnd'];
            }
            if (!empty($all['changeExpectTs'])) {
                $replyMsg .= '；预计完成时间：' . $all['changeExpectTs'].'</div>';
            }
            if (!empty($replyMsg)) {
                $retId = ChangeRecord::insertGetId([
                    'changeId'         => $all['changeId'],
                    'replycontent'     => $replyMsg,
                    'changeState'      => $all['changeStateMeans'],
                    'changeStatusCode' => $all['changeState'],
                    'passOrNo'         => $passOrNo,
                    'userId'           => $userId,
                    'ts'               => date('Y-m-d H:i:s')
                ]);
            }
            //如果不是保存操作 则添加队列任务发送邮件或短信
            if ($all['passOrNo'] != 'save') {
                //添加到队列 发送邮件或短信
                $st = ThirdCallHelper::getDictMeans("变更状态","changeState",$status);
                $str = "你有一个变更单:单号(".$all['changeNo']."),需要进行".$st."操作，变更主题：";
                $title = '变更标题：'.$str. $all['changeTitle'] . ';上一步由' . ThirdCallHelper::getStuffName($userId) . $passOrNo . $all['changeStateMeans'];
                $userIds = $this->getNextIds($status, $all);
                $replyMsg = "你有一个变更单:单号(".$all['changeNo']."),需要进行".$st."操作。  ".$replyMsg;
                $replyMsg = preg_replace(['/<br>/'], ["\n"], $replyMsg);
                $job = new SendChangeEmail($title, $userIds, $all['changeType'], $replyMsg); //创建队列任务
                $this->dispatch($job);
            }
        } catch (\Exception $ex) {
            return array('status' => false, 'msg' => $ex->getMessage());
        }
        return array('status' => true, 'msg' => "操作成功");
    }

    /**
     * 方案测试操作
     * @return string
     */
    public function saveTesting()
    {
        try {
            $userId = Request()->session()->get('user')->Id;
            $all = Input::except('_token');
            /**
             * step1 更新数据
             */
            $testCont = PublicMethodsHelper::htmlToSafe( $all['testCont']);
            $update = [
                'testCont' => $testCont,
                'UpUserId' => $userId,
                'UpTs'     => date('Y-m-d H:i:s')
            ];
            if (!empty($all['changeTimeStart'])) {
                $update['changeTimeStart'] = $all['changeTimeStart'];
            }
            if (!empty($all['changeTimeEnd'])) {
                $update['changeTimeEnd'] = $all['changeTimeEnd'];
            }
            if (!empty($all['changeExpectTs'])) {
                $update['changeExpectTs'] = $all['changeExpectTs'];
            }
            $save = Change::where('Id', $all['changeId'])->update($update);
            /**
             * step 2 驱动
             */
            $status = $all['changeState'];
            $passOrNo = $this->getOperate($all, $status);
            $save = Change::where('Id', $all['changeId'])->update([
                'changeState' => $status
            ]);
            /**
             * step3 新增记录表
             * 保存操作记录在changerecord表
             */
            $replyMsg = '';
            if (!empty($all['testCont'])) {
                $replyMsg .= '<div class="stl">测试报告</div>' .'<div class="cont">'. $testCont.'</div>';
            }
            if (!empty($all['changeTimeStart'])) {
                $replyMsg .= '<div class="stl">变更时间窗口：' . $all['changeTimeStart'];
            }
            if (!empty($all['changeTimeEnd'])) {
                $replyMsg .= '~' . $all['changeTimeEnd'];
            }
            if (!empty($all['changeExpectTs'])) {
                $replyMsg .= '；预计完成时间：' . $all['changeExpectTs'].'</div>';
            }
            if (!empty($replyMsg)) {
                $retId = ChangeRecord::insertGetId([
                    'changeId'         => $all['changeId'],
                    'replycontent'     => $replyMsg,
                    'changeState'      => $all['changeStateMeans'],
                    'changeStatusCode' => $all['changeState'],
                    'passOrNo'         => $passOrNo,
                    'userId'           => $userId,
                    'ts'               => date('Y-m-d H:i:s')
                ]);
            }
            //如果不是保存操作 则添加队列任务发送邮件或短信
            if ($all['passOrNo'] != 'save') {
                //添加到队列 发送邮件或短信
                $st = ThirdCallHelper::getDictMeans("变更状态","changeState",$status);
                $str = "你有一个变更单:单号(".$all['changeNo']."),需要进行".$st."操作，变更主题：";
                $title = '变更标题：'.$str . $all['changeTitle'] . ';上一步由' . ThirdCallHelper::getStuffName($userId) . $passOrNo . $all['changeStateMeans'];
                $userIds = $this->getNextIds($status, $all);
                $replyMsg = "你有一个变更单:单号(".$all['changeNo']."),需要进行".$st."操作。  ".$replyMsg;
                $replyMsg = preg_replace(['/<br>/'], ["\n"], $replyMsg);
                $job = new SendChangeEmail($title, $userIds, $all['changeType'], $replyMsg); //创建队列任务
                $this->dispatch($job);
            }
        } catch (\Exception $ex) {
            return array('status' => false, 'msg' => $ex->getMessage());
        }
        return array('status' => true, 'msg' => "操作成功");
    }

    /**
     * 测试结果审批操作
     * @return string
     */
    public function saveExamining()
    {
        try {
            $userId = Request()->session()->get('user')->Id;
            $all = Input::except('_token');
            /**
             * step1 更新数据
             */
            $checkTestCont = PublicMethodsHelper::htmlToSafe( $all['checkTestCont']);
            $update = [
                'checkTestCont'          => $checkTestCont,
                'changeImplementGroupId' => $all['changeImplementGroupId'],
                'changeImplementUserId'  => $all['changeImplementUserId'],
                'UpUserId'               => $userId,
                'checkTestTs'            => date('Y-m-d H:i:s'),
                'UpTs'                   => date('Y-m-d H:i:s')
            ];
            $save = Change::where('Id', $all['changeId'])->update($update);
            /**
             * step 2 驱动
             */
            $status = $all['changeState'];
            $passOrNo = $this->getOperate($all, $status);
            $save = Change::where('Id', $all['changeId'])->update([
                'changeState' => $status
            ]);
            /**
             * step3 新增记录表
             * 保存操作记录在changerecord表
             */
            $replyMsg = '';
            if (!empty($all['checkTestCont'])) {
                $replyMsg .= '<div class="stl">方案及测试结果审核说明</div>' . '<div class="cont">'.$checkTestCont.'</div>';
            }
            if (!empty($all['changeImplementGroupId'])) {
                $replyMsg .= '<div class="stl">变更实施组：' . AuxDict::where('Code', $all['changeImplementGroupId'])->value('Means');
            }
            if (!empty($all['changeImplementUserId'])) {
                $replyMsg .= '；变更实施人：' . AuxStuff::where('Id', $all['changeImplementUserId'])->value('Name').'</div>';
            }
            if (!empty($replyMsg)) {
                $retId = ChangeRecord::insertGetId([
                    'changeId'         => $all['changeId'],
                    'replycontent'     => $replyMsg,
                    'changeState'      => $all['changeStateMeans'],
                    'changeStatusCode' => $all['changeState'],
                    'passOrNo'         => $passOrNo,
                    'userId'           => $userId,
                    'ts'               => date('Y-m-d H:i:s')
                ]);
            }
            //如果不是保存操作 则添加队列任务发送邮件或短信
            if ($all['passOrNo'] != 'save') {
                //添加到队列 发送邮件或短信
                $st = ThirdCallHelper::getDictMeans("变更状态","changeState",$status);
                $str = "你有一个变更单:单号(".$all['changeNo']."),需要进行".$st."操作，变更主题：";
                $title = '变更标题：'.$str . $all['changeTitle'] . ';上一步由' . ThirdCallHelper::getStuffName($userId) . $passOrNo . $all['changeStateMeans'];
                $userIds = $this->getNextIds($status, $all);
                $replyMsg = "你有一个变更单:单号(".$all['changeNo']."),需要进行".$st."操作。  ".$replyMsg;
                $replyMsg = preg_replace(['/<br>/'], ["\n"], $replyMsg);
                $job = new SendChangeEmail($title, $userIds, $all['changeType'], $replyMsg); //创建队列任务
                $this->dispatch($job);
            }
        } catch (\Exception $ex) {
            return array('status' => false, 'msg' => $ex->getMessage());
        }
        return array('status' => true, 'msg' => "操作成功");
    }

    /**
     * 变更实施操作
     * @return string
     */
    public function saveImplement()
    {
        try {
            $userId = Request()->session()->get('user')->Id;
            $all = Input::except('_token');
            /**
             * step1 更新数据
             */
            $implementCont = PublicMethodsHelper::htmlToSafe( $all['implementCont']);
            $update = [
                'implementCont'   => $implementCont,
                'implementResult' => $all['implementResult'],
                'UpUserId'        => $userId,
                'UpTs'            => date('Y-m-d H:i:s')
            ];
            if (!empty($all['actualTs'])) {
                $update['actualTs'] = $all['actualTs'];
            }
            $save = Change::where('Id', $all['changeId'])->update($update);
            /**
             * step 2 驱动
             */
            $status = $all['changeState'];
            $passOrNo = $this->getOperate($all, $status);
            $save = Change::where('Id', $all['changeId'])->update([
                'changeState' => $status
            ]);
            /**
             * step3 新增记录表
             * 保存操作记录在changerecord表
             */
            $replyMsg = '';
            if (!empty($all['implementCont'])) {
                $replyMsg .= '<div class="stl">实施情况说明</div>' .'<div class="cont">'.$implementCont.'</div>';
            }
            if (!empty($all['implementResult'])) {
                $replyMsg .= '<div class="stl">验证结果：'.AuxDict::where('Code', $all['implementResult'])
                        ->where('DomainCode', 'checkResult')
                        ->value('Means').'</div>';
            }
            if (!empty($replyMsg)) {
                $retId = ChangeRecord::insertGetId([
                    'changeId'         => $all['changeId'],
                    'replycontent'     => $replyMsg,
                    'changeState'      => $all['changeStateMeans'],
                    'changeStatusCode' => $all['changeState'],
                    'passOrNo'         => $passOrNo,
                    'userId'           => $userId,
                    'ts'               => date('Y-m-d H:i:s')
                ]);
            }
            //如果不是保存操作 则添加队列任务发送邮件或短信
            if ($all['passOrNo'] != 'save') {
                //添加到队列 发送邮件或短信
                $st = ThirdCallHelper::getDictMeans("变更状态","changeState",$status);
                $str = "你有一个变更单:单号(".$all['changeNo']."),需要进行".$st."操作，变更主题：";
                $title = '变更标题：' .$str. $all['changeTitle'] . ';上一步由' . ThirdCallHelper::getStuffName($userId) . $passOrNo . $all['changeStateMeans'];
                $userIds = $this->getNextIds($status, $all);
                $replyMsg = "你有一个变更单:单号(".$all['changeNo']."),需要进行".$st."操作。  ".$replyMsg;
                $replyMsg = preg_replace(['/<br>/'], ["\n"], $replyMsg);
                $job = new SendChangeEmail($title, $userIds, $all['changeType'], $replyMsg); //创建队列任务
                $this->dispatch($job);
            }
        } catch (\Exception $ex) {
            return array('status' => false, 'msg' => $ex->getMessage());
        }
        return array('status' => true, 'msg' => "操作成功");
    }

    /**
     * 变更结果验证操作
     * @return string
     */
    public function saveVerificating()
    {
        try {
            $userId = Request()->session()->get('user')->Id;
            $all = Input::except('_token');
            /**
             * step1 更新数据
             */
            $checkResultCont = PublicMethodsHelper::htmlToSafe( $all['checkResultCont']);
            $update = [
                'checkResultCont' => $checkResultCont,
                'checkResult'     => $all['checkResult'],
                'UpUserId'        => $userId,
                'checkTs'         => date('Y-m-d H:i:s'),
                'UpTs'            => date('Y-m-d H:i:s')
            ];
            $save = Change::where('Id', $all['changeId'])->update($update);
            /**
             * step 2 驱动
             */
            $status = $all['changeState'];
            $passOrNo = $this->getOperate($all, $status);
            $save = Change::where('Id', $all['changeId'])->update([
                'changeState' => $status
            ]);
            /**
             * step3 新增记录表
             * 保存操作记录在changerecord表
             */
            $replyMsg = '';
            if (!empty($all['checkResultCont'])) {
                $replyMsg = '<div class="stl">验证结果说明</div>' . '<div class="cont">'.$checkResultCont.'</div>';
            }
            if (!empty($all['checkResult'])) {
                $replyMsg .= '<div class="stl">验证结果：'.AuxDict::where('Code', $all['checkResult'])->where("domainCode","checkResult")->value('Means').'</div>';
            }
            if (!empty($replyMsg)) {
                $retId = ChangeRecord::insertGetId([
                    'changeId'         => $all['changeId'],
                    'replycontent'     => $replyMsg,
                    'changeState'      => $all['changeStateMeans'],
                    'changeStatusCode' => $all['changeState'],
                    'passOrNo'         => $passOrNo,
                    'userId'           => $userId,
                    'ts'               => date('Y-m-d H:i:s')
                ]);
            }
            //如果不是保存操作 则添加队列任务发送邮件或短信
            if ($all['passOrNo'] != 'save') {
                //添加到队列 发送邮件或短信
                $st = ThirdCallHelper::getDictMeans("变更状态","changeState",$status);
                $str = "你有一个变更单:单号(".$all['changeNo']."),需要进行".$st."操作，变更主题：";
                $title = '变更标题：'.$str . $all['changeTitle'] . ';上一步由' . ThirdCallHelper::getStuffName($userId) . $passOrNo . $all['changeStateMeans'];
                $userIds = $this->getNextIds($status, $all);
                $replyMsg = "你有一个变更单:单号(".$all['changeNo']."),需要进行".$st."操作。  ".$replyMsg;
                $replyMsg = preg_replace(['/<br>/'], ["\n"], $replyMsg);
                $job = new SendChangeEmail($title, $userIds, $all['changeType'], $replyMsg); //创建队列任务
                $this->dispatch($job);
            }
        } catch (\Exception $ex) {
            return array('status' => false, 'msg' => $ex->getMessage());
        }
        return array('status' => true, 'msg' => "操作成功");
    }

    /**
     * 流程图blade
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function flowChart(){
        $currentStatus = Input::get('currentStatus');
        $changeId = Input::get("changeId");
        $nextOperater = "";
        if($changeId){
            $this->getNextOperater($changeId,$currentStatus,$nextOperater);
        };
        return view("change/flowchart",['currentStatus'=>$currentStatus,"nextOperater"=>$nextOperater]);
    }

    /**
     * 获取下一步流程操作部门或操作人
     * @param $changeId
     * @param $currentStatus
     * @param $nextOperater
     */
    public function getNextOperater($changeId,$currentStatus,&$nextOperater){
        $change = Change::where("Id",$changeId)->first();
        if(!empty($change)){
            switch($currentStatus){
                case "可行性审批":
                    if($dep = $change->feasibilityGroupId){
                        $stuffs = AuxStuff::select("res.aux_stuff.*")
                            ->leftJoin("auth.authorities","res.aux_stuff.Login","=","auth.authorities.username")
                            ->where(function($stuffs) use($dep) {
                                $stuffs->where("res.aux_stuff.Depart",$dep)
                                    ->orWhere("res.aux_stuff.second_dept",$dep);})
                            ->where("auth.authorities.authority","change_approval")
                            ->get();
                        if(!empty($stuffs)){
                            foreach($stuffs as $stuff){
                                $nextOperater .= $nextOperater ==""?$stuff->Name:"、".$stuff->Name;
                            }
                            $nextOperater = "可审批人:".$nextOperater;
                        }
                    }
                    break;
                case "变更驳回":
                    if($stuffId = $change->UserId){
                        $stuff = ThirdCallHelper::getStuffName($stuffId);
                        $nextOperater = "待操作人：".$stuff;
                    }
                    break;
                case "变更方案规划":
                    if($stuffId = $change->proDesigerId){
                        $stuff = ThirdCallHelper::getStuffName($stuffId);
                        $nextOperater = "规划人：".$stuff;
                    }
                    break;
                case "实施/回退方案制定":
                    if($dep = $change->proDesigerGroupId){
                        $dep = AuxDict::where("Code",$dep)->first();
                        $nextOperater = "实施部门：".$dep->Means;
                    }
                    break;
                case "方案测试":
                    if($dep = $change->testGroupId){
                        $stuffs = AuxStuff::select("res.aux_stuff.*")
                            ->leftJoin("auth.authorities","res.aux_stuff.Login","=","auth.authorities.username")
                            ->where(function($stuffs) use($dep) {
                                $stuffs->where("res.aux_stuff.Depart",$dep)
                                    ->orWhere("res.aux_stuff.second_dept",$dep);})
                            ->where("auth.authorities.authority","change_test")
                            ->get();
                        if(!empty($stuffs)){
                            foreach($stuffs as $stuff){
                                $nextOperater .= $nextOperater ==""?$stuff->Name:"、".$stuff->Name;
                            }
                            $nextOperater = "测试审批人:".$nextOperater;
                        }
                    }
                    break;
                case "方案及测试结果审批":
                    if($dep = $change->proDesigerId){
                        $leaderId = AuxStuff::where('Id', $change->proDesigerId)->value('parentId');
                        $stuff = ThirdCallHelper::getStuffName($leaderId);
                        $nextOperater = "审批人：".$stuff;
                    }
                    break;
                case "变更发布实施":
                    if($stuffId = $change->changeImplementUserId){
                        $stuff = ThirdCallHelper::getStuffName($stuffId);
                        $nextOperater = "发布人：".$stuff;
                    }
                    break;
                case "变更结果验证":
                    if($stuffId = $change->checkUserId){
                        $stuff = ThirdCallHelper::getStuffName($stuffId);
                        $nextOperater = "验收人：".$stuff;
                    }
                    break;
            }
        }
    }
}