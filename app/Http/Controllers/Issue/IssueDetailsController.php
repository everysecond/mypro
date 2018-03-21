<?php
/**
 * Created by PhpStorm.
 * User: chenglh
 * Date: 2016/9/26
 * Time: 12:56
 */
namespace Itsm\Http\Controllers\Issue;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Input;
use Itsm\Http\Controllers\Change\ChangeController;
use Itsm\Http\Controllers\Controller;
use Itsm\Http\Helper\ProcessMakerApi;
use Itsm\Http\Helper\PublicMethodsHelper;
use Itsm\Http\Helper\ThirdCallHelper;
use Itsm\Jobs\SendIssueEmail;
use Itsm\Model\Usercenter\Change;
use Itsm\Model\Usercenter\Correlation;
use Itsm\Model\Usercenter\Issue;
use Itsm\Model\Usercenter\IssueRecord;

class IssueDetailsController extends Controller
{

    //问题审核不通过驳回
    public function saveToApply($id)
    {
        if ($id) {
            $issue = Issue::where('Id', $id)->first();
            $pmRole = $this->getProcessRole("issue");
            $proApi = new ProcessMakerApi(env("ISSUE_PROCESS_ID"), env("ISSUE_STEP_ONE_ID"));
            //获取工作流信息
            $tokenInfo = $this->getAccessTokenByRole("employee", "issue");
            $token = $tokenInfo['access_token'];
            $caseInfo = $proApi->getCaseCurrentTask($issue->caseId, $token);
            $dynaForm = $proApi->getCaseDynaForms($token, $issue->caseId, "reply-btn btnSub");
            $user = Request()->session()->get('user');

            //问题来源
            $sourceList = ThirdCallHelper::getDictArray('问题来源', 'issueSource');
            //问题分类
            $categoryList = ThirdCallHelper::getDictArray('工单类型', 'WorksheetTypeOne');
            //优先级
            $priorityList = ThirdCallHelper::getDictArray('优先级', 'issuePriority');

            $issue['issueSubmitUser'] = ThirdCallHelper::getStuffName($issue->issueSubmitUserId);
            return view("issue/saveToApply", [
                'issue'        => $issue,
                'sourceList'   => $sourceList,
                'categoryList' => $categoryList,
                'priorityList' => $priorityList,
                'stepForm'     => $dynaForm,
            ]);
        } else {
            return '参数错误！';
        }
    }

    //保存问题驳回到数据库
    public function saveToapplyData()
    {
        $userId = Request()->session()->get('user')->Id;
        $data = Input::except('_token');
        $issueDescribe = PublicMethodsHelper::htmlToSafe($data['issueDescribe']);
        /**
         * step1 更新数据
         */
        if ($data) {
            $issueSource = Arr::get($data, "issueSource", []);
            $update = [
                'issueTitle'       => $data['issueTitle'],
                'issueSource'      => implode(',', $issueSource),
                'issueCategory'    => $data['issueCategory'],
                'issuePriority'    => $data['issuePriority'],
                'issueDescribe'    => $issueDescribe,
                'issueCheckUserId' => $data['issueCheckUserId'],
                "upUserId"         => $userId,
                'upTs'             => date('Y-m-d H:i:s'),
            ];

            $save = Issue::where('Id', $data['Id'])->update($update);
        }
        /**
         * step 2 驱动
         */
        $processVar = $data['processVar'];
        if ($processVar) {
            $variableValue = $data[$processVar];
            $passOrNo = $variableValue == 1 ? '重新提交' : '作废';
            $status = $this->getNextStatusByIssue($data);
        }
        $save = Issue::where('Id', $data['Id'])->update([
            'issueState' => $status
        ]);
        $issue = Issue::where('Id', $data['Id'])->first();
        if ($update == false) {//插入数据失败
            return ['status' => false, 'statusMsg' => '提交出错,请稍后再试!'];
        } else {
            $url = env("APP_URL", "http://www.itsm.com");
            $replyContent = '';
            $replyContent .= '<div class="stl">问题主题</div>' . '<div class="cont">' . $data['issueTitle'] . '</div>';
            $replyContent .= '<div class="stl">' . "<a href='$url/issue/details/{$data['Id']}#detailsArea'>详情参详问题主体信息</a>" . '</div>';
            $recordId = IssueRecord::insertGetId([
                'issueId'         => $data['Id'],
                'ts'              => date('Y-m-d H:i:s'),
                'issuecontent'    => $replyContent,
                'userId'          => $userId,
                'issueState'      => '问题申请',
                'issueStatusCode' => $data['issueState'],
                'passOrNo'        => $passOrNo,
            ]);
            $record = IssueRecord::where('Id', $recordId)->first();
            //如果不是保存操作 则添加队列任务发送邮件或短信
            if ($data['passOrNo'] != 'save') {
                //添加到队列 发送邮件或短信
                $title = '问题标题：' . $issue['issueTitle'] . ';' . ThirdCallHelper::getStuffName($userId) . $passOrNo . $record['issueState'];
                $userIds = $this->getNextIds($status, $issue);
                $job = new SendIssueEmail($title, $userIds, $issue['issuePriority'], $replyContent); //创建队列任务
                $this->dispatch($job);
            }
            return ['status' => true, 'statusMsg' => '操作成功', 'currentAction' => 'editApproval'];
        }
    }

    //问题详情表单
    public function IssueDetails($id)
    {
        if ($id) {
            $userId = Request()->session()->get('user')->Id;
            $issue = Issue::where('Id', $id)->first();
            $pmRole = $this->getProcessRole("issue");
            $res = new ProcessMakerApi(env("ISSUE_PROCESS_ID"), env("ISSUE_STEP_ONE_ID"));
            //获取工作流信息
            $tokenInfo = $this->getAccessTokenByRole("employee", "issue");
            $token = $tokenInfo['access_token'];
            //同步第三方
            if (!$newStatus = $this->syncCaseStatusByIssue($issue->caseId, $token, $issue->issueState)) {
                return "第三方API校验失败，请重试";
            }
            //操作权限验证
            $hasRule = false;
            if (in_array("issue_" . $newStatus, $pmRole)) {
                $hasRule = true;
            }
            //重新赋值
            $issue->issueState = $newStatus;
            $dynaForm = $res->getCaseDynaForms($token, $issue->caseId, "reply-btn btnSub");

            //获取所有问题操作记录
            $issueRecord = IssueRecord::where('IssueId', $id)->get();
            $issueRecord = Change::translationStuff($issueRecord, 'userId');
            $issueRecord = Change::subStuffName($issueRecord, 'userId');
            $issueRecord = Change::transUrl($issueRecord, 'issuecontent');
            $issueSource = ThirdCallHelper::translationByRow($issue, 'issueSource');
            $whetherAnalysis = $this->ifAnalysis($issue->whetherAnalysis);//是否需要分析

            //变更状态转化为数字方便判断
            $statusStep = $this->getSteps($issue->issueState);
            //匹配所有上传过的文件
            $uploadFiles = $this->getUploadFiles($issue);
            $arr = [
                'stepForm'        => $dynaForm,
                'issue'           => $issue,
                'whetherAnalysis' => $whetherAnalysis,
                'statusStep'      => $statusStep,
                'issueRecord'     => $issueRecord,
                'uploadFiles'     => $uploadFiles,
                'issueSource'     => $issueSource,
                'userId'          => $userId,
                'hasRule'         => $hasRule,
            ];
            return view("issue/issueInfo", $arr);
        } else {
            return "参数错误！";
        }
    }

    /**
     * 将状态转化为数字方便判断
     * @param $newStatus
     * @return int
     */
    protected function getSteps($newStatus)
    {
        $statusStep = 0;
        switch ($newStatus) {
            case 'reject':
                $statusStep = -1;//驳回问题
                break;
            case 'approval':
                $statusStep = 1;//审核
                break;
            case 'analyse':
                $statusStep = 2;//分析问题
                break;
            case 'check':
                $statusStep = 3;//确认实施方案
                break;
            case 'closed':
                $statusStep = 4;//关闭问题
                break;
            case 'completed':
                $statusStep = 5;//完成
                break;
        }
        return $statusStep;
    }

    /*
     *
     * 是否需要进行分析将1和0转化为是和否
     */
    protected function ifAnalysis($yesNo)
    {
        switch ($yesNo) {
            case 0:
                $yesOrNo = '否';
                break;
            case 1:
                $yesOrNo = '是';
                break;
        }
        return $yesOrNo;
    }

    /**
     * 正则匹配所有上传过的文件
     * @param $issue
     * @return string
     */
    protected function getUploadFiles($issue)
    {
        $reg = '/<a[^>]*?href=["http:\/\/]?(.*?)["]?\s[^>]*?>.*<\/a>/';
        preg_match_all($reg, $issue->issueCheckOpinion, $match1);
        preg_match_all($reg, $issue->issueAnalysis, $match2);
        preg_match_all($reg, $issue->issueSolution, $match3);
        preg_match_all($reg, $issue->issueResult, $match4);
        $match = 'match';
        $uploadFiles = '';
        foreach (range(1, 4) as $num) {
            if (isset(${$match . $num}[0][0])) {
                $uploadFiles .= ${$match . $num}[0][0] . '&nbsp;&nbsp;&nbsp;&nbsp;';
            }
        }
        return $uploadFiles;
    }

    /*
     * 审核中
     */
    public function saveApproval()
    {
        try {
            $userId = Request()->session()->get('user')->Id;
            $all = Input::except('_token');
            $opinion = PublicMethodsHelper::htmlToSafe(Arr::get($all, "issueCheckOpinion", ""));
            /**
             * step1 更新数据
             */
            $update = [
                'issueChargeUserId' => Arr::get($all, "issueChargeUserId", ""),
                'assignTs'          => date('Y-m-d H:i:s'),
                'whetherAnalysis'   => $all['whetherAnalysis'],
                'issueCheckOpinion' => $opinion,
                'upUserId'          => $userId,
                'upTs'              => date('Y-m-d H:i:s'),
            ];
            $save = Issue::where('Id', $all['Id'])->update($update);
            /**
             * step 2 驱动
             */
            $status = $all['issueState'];
            $passOrNo = $this->getOperation($all, $status);
            $save = Issue::where('Id', $all['Id'])->update([
                'issueState' => $status
            ]);
            /**
             * step3 新增记录表
             * 保存操作记录在issuerecord表
             */
            $issue = Issue::where('Id', $all['Id'])->first();
            $replyMsg = '';

            if (!empty($all['issueCheckOpinion'])) {
                $replyMsg .= '<div class="stl">审核意见</div>' . '<div class="cont">' . $opinion . '</div>';
            }
            if (!empty($all['issueChargeUserId'])) {
                $replyMsg .= '<div class="stl">问题负责人：' . ThirdCallHelper::getStuffName($issue['issueChargeUserId']) . '；&nbsp;&nbsp;&nbsp;&nbsp;';
                $replyMsg .= '是否需要进行分析：' . $this->ifAnalysis($issue['whetherAnalysis']) . '</div>';
            }
            $retId = IssueRecord::insertGetId([
                'issueId'         => $all['Id'],
                'issuecontent'    => $replyMsg,
                'issueState'      => '问题审核',
                'issueStatusCode' => $all['issueState'],
                'passOrNo'        => $passOrNo,
                'userId'          => $userId,
                'ts'              => date('Y-m-d H:i:s')
            ]);
            $ret = IssueRecord::where('Id', $retId)->first();
            //如果不是保存操作 则添加队列任务发送邮件或短信
            if ($all['passOrNo'] != 'save') {
                //添加到队列 发送邮件或短信
                $title = '问题标题：' . $issue['issueTitle'] . '；' . ThirdCallHelper::getStuffName($userId) . $passOrNo . $ret['issueState'];
                $userIds = $this->getNextIds($status, $issue);
                $replyMsg = preg_replace(['/<br>/'], ["\n"], $replyMsg);
                $job = new SendIssueEmail($title, $userIds, $issue['issuePriority'], $replyMsg); //创建队列任务
                $this->dispatch($job);
            }
        } catch (\Exception $ex) {
            return array('status' => false, 'msg' => $ex->getMessage());
        }
        return array('status' => true, 'msg' => "操作成功");
    }

    /*
     * 分析问题及解决方案制定
     */
    public function saveAnalysis()
    {
        try {
            $userId = Request()->session()->get('user')->Id;
            $all = Input::except('_token');
            $analyse = PublicMethodsHelper::htmlToSafe(Arr::get($all, "issueAnalysis", ""));
            $solution = PublicMethodsHelper::htmlToSafe(Arr::get($all, "issueSolution", ""));
            /**
             * step1 更新数据
             */
            $update = [
                'issueAnalysis'   => $analyse,
                'issueSolution'   => $solution,
                'issueSolutionTs' => date('Y-m-d H:i:s'),
                'upUserId'        => $userId,
                'upTs'            => date('Y-m-d H:i:s'),
            ];
            $save = Issue::where('Id', $all['Id'])->update($update);
            $issue = Issue::where('Id', $all['Id'])->first();
            /**
             * step 2 驱动
             */
            $status = $all['issueState'];
            $passOrNo = $this->getOperation($all, $status);
            $save = Issue::where('Id', $all['Id'])->update([
                'issueState' => $status
            ]);
            /**
             * step3 新增记录表
             * 保存操作记录在issuerecord表
             */
            $replyMsg = '';
            if (!empty($all['issueAnalysis'])) {
                $replyMsg .= '<div class="stl">问题根本原因描述</div>' . '<div class="cont">' . $analyse . '</div>';
            }
            if (!empty($all['issueSolution'])) {
                $replyMsg .= '<div class="stl">问题解决方案描述</div>' . '<div class="cont">' . $solution . '</div>';
            }
            $retId = IssueRecord::insertGetId([
                'issueId'         => $all['Id'],
                'issuecontent'    => $replyMsg,
                'issueState'      => '问题根本原因及解决方案',
                'issueStatusCode' => $all['issueState'],
                'passOrNo'        => $passOrNo,
                'userId'          => $userId,
                'ts'              => date('Y-m-d H:i:s')
            ]);
            $ret = IssueRecord::where('Id', $retId)->first();
            //如果不是保存操作 则添加队列任务发送邮件或短信
            if ($all['passOrNo'] != 'save') {
                //添加到队列 发送邮件或短信
                $title = '问题标题：' . $issue['issueTitle'] . ';' . ThirdCallHelper::getStuffName($userId) . $passOrNo . $ret['issueState'];
                $userIds = $this->getNextIds($status, $issue);
                $replyMsg = preg_replace(['/<br>/'], ["\n"], $replyMsg);
                $job = new SendIssueEmail($title, $userIds, $issue['issuePriority'], $replyMsg); //创建队列任务
                $this->dispatch($job);
            }
        } catch (\Exception $ex) {
            return array('status' => false, 'msg' => $ex->getMessage());
        }
        return array('status' => true, 'msg' => "操作成功");
    }

    /*
    * 实施解决问题
    */
    public function saveCheck()
    {
        try {
            $userId = Request()->session()->get('user')->Id;
            $all = Input::except('_token');
            $result = PublicMethodsHelper::htmlToSafe(Arr::get($all, "issueResult", ""));
            /**
             * step1 更新数据
             */
            $update = [
                'issueResult'     => $result,
                'triggerAnalysis' => Arr::get($all, "triggerAnalysis", ""),
                'upUserId'        => $userId,
                'upTs'            => date('Y-m-d H:i:s'),
            ];
            $save = Issue::where('Id', $all['Id'])->update($update);
            $issue = Issue::where('Id', $all['Id'])->first();

            /**
             * step 2 驱动
             */
            $status = $all['issueState'];
            $passOrNo = $this->getOperation($all, $status);
            $save = Issue::where('Id', $all['Id'])->update([
                'issueState' => $status
            ]);
            /**
             * step3 新增记录表
             * 保存操作记录在issuerecord表
             */
            $replyMsg = '';
            if (!empty($all['issueResult'])) {
                $replyMsg .= '<div class="stl">问题解决方案简要说明</div>' . '<div class="cont">' . $result . '</div>';
            }
            $retId = IssueRecord::insertGetId([
                'issueId'         => $all['Id'],
                'issuecontent'    => $replyMsg,
                'issueState'      => '问题解决方案 ',
                'issueStatusCode' => $all['issueState'],
                'passOrNo'        => $passOrNo,
                'userId'          => $userId,
                'ts'              => date('Y-m-d H:i:s')
            ]);
            $ret = IssueRecord::where('Id', $retId)->first();
            //如果不是保存操作 则添加队列任务发送邮件或短信
            if ($all['passOrNo'] != 'save') {
                //添加到队列 发送邮件或短信
                $title = '问题标题：' . $issue['issueTitle'] . '；' . ThirdCallHelper::getStuffName($userId) . $passOrNo . $ret['issueState'];
                $userIds = $this->getNextIds($status, $issue);
                $replyMsg = preg_replace(['/<br>/'], ["\n"], $replyMsg);
                $job = new SendIssueEmail($title, $userIds, $issue['issuePriority'], $replyMsg); //创建队列任务
                $this->dispatch($job);
            }
        } catch (\Exception $ex) {
            return array('status' => false, 'msg' => $ex->getMessage());
        }
        return array('status' => true, 'msg' => "操作成功");
    }

    /*
    * 关闭问题
    */
    public function saveClose()
    {
        try {
            $userId = Request()->session()->get('user')->Id;
            $all = Input::except('_token');
            $reason = PublicMethodsHelper::htmlToSafe(Arr::get($all, "issueCloseReason", ""));
            /**
             * step1 更新数据
             */
            $update = [
                'issueCloseReason' => $reason,
                'issueCloseUserId' => $userId,
                'issueCloseTs'     => date('Y-m-d H:i:s'),
                'upUserId'         => $userId,
                'upTs'             => date('Y-m-d H:i:s'),
            ];
            $save = Issue::where('Id', $all['Id'])->update($update);
            $issue = Issue::where('Id', $all['Id'])->first();

            /**
             * step 2 驱动
             */
            $status = $all['issueState'];
            $passOrNo = $this->getOperation($all, $status);
            $save = Issue::where('Id', $all['Id'])->update([
                'issueState' => $status
            ]);
            /**
             * step3 新增记录表
             * 保存操作记录在issuerecord表
             */
            $replyMsg = '';
            if (!empty($all['issueCloseReason'])) {
                $replyMsg .= '<div class="stl">问题关闭原因</div>' . '<div class="cont">' . $reason . '</div>';
            }
            $retId = IssueRecord::insertGetId([
                'issueId'         => $all['Id'],
                'issuecontent'    => $replyMsg,
                'issueState'      => '问题关闭原因 ',
                'issueStatusCode' => $all['issueState'],
                'passOrNo'        => $passOrNo,
                'userId'          => $userId,
                'ts'              => date('Y-m-d H:i:s')
            ]);
            $ret = IssueRecord::where('Id', $retId)->first();
            //如果不是保存操作 则添加队列任务发送邮件或短信
            if ($all['passOrNo'] != 'save') {
                //添加到队列 发送邮件或短信
                $title = '问题标题：' . $issue['issueTitle'] . '；' . ThirdCallHelper::getStuffName($userId) . $passOrNo . $ret['issueState'];
                $userIds = $this->getNextIds($issue['issueState'], $issue);
                $replyMsg = preg_replace(['/<br>/'], ["\n"], $replyMsg);
                $job = new SendIssueEmail($title, $userIds, $issue['issuePriority'], $replyMsg); //创建队列任务
                $this->dispatch($job);
            }
        } catch (\Exception $ex) {
            return array('status' => false, 'msg' => $ex->getMessage());
        }
        return array('status' => true, 'msg' => "操作成功");
    }

    /**
     * 获取当前属于何种操作（只保存，审核通过以及审核不通过）
     * @param $all
     * @param $status
     * @return string
     */
    protected function getOperation($all, &$status)
    {
        if ($all['passOrNo'] == 'save') {
            $passOrNo = '保存';
        } else {
            $status = $this->getNextStatusByIssue($all);
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
     * 驱动工作流后获取驱动后状态
     * @param $all
     * @return mixed
     */
    protected function getNextStatusByIssue($all)
    {
        $variableVar = [];
        $caseId = Issue::where('Id', $all['Id'])->value('caseId');
        //获取变量
        $processVar = $all['processVar'];
        if ($processVar) {
            $variableValue = $all[$processVar];
            $variableVar = [
                $processVar => $variableValue
            ];
        }
        $res = new ProcessMakerApi(env("ISSUE_PROCESS_ID"), env("ISSUE_STEP_ONE_ID"));
        $tokenInfo = $this->getAccessTokenByRole("employee", "issue");
        $caseInfo = $res->getCaseCurrentTask($caseId, $tokenInfo['access_token']);
        //重新获取token
        $currentTokenInfo = $this->getAccessTokenByRole("issue_" . $caseInfo['status'], "issue");
        $nextRes = $res->nextCase($caseId, $currentTokenInfo['access_token'], $variableVar);
        if (isset($nextRes['status'])) {
            $status = $nextRes['status'];
        } else {
            $status = $caseInfo['status'];
        }
        return $status;
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
            switch ($status) {
                case 'reject'://通知问题申请人
                    $userIds = $all['issueSubmitUserId'];
                    break;
                case 'approval'://通知 问题审核人
                    $userIds = $all['issueCheckUserId'];
                    break;
                case 'analyse'://通知 问题负责人
                    $userIds = $all['issueChargeUserId'];
                    break;
                case 'check'://通知 问题审核人
                    $userIds = $all['issueCheckUserId'];
                    break;
                case 'closed'://通知 问题审核人
                    $userIds = $all['issueCheckUserId'];
                    break;
            }
            return $userIds;
        } else {
            return '';
        }
    }

    public function flowChart()
    {
        $currentStatus = Input::get('currentStatus');
        return view("issue/flowchart", ['currentStatus' => $currentStatus]);
    }

}