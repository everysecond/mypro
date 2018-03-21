<?php

namespace Itsm\Http\Controllers\Handover;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Itsm\Http\Controllers\Controller;
use Itsm\Http\Helper\PublicMethodsHelper;
use Itsm\Http\Helper\ThirdCallHelper;
use Itsm\Jobs\ItsmMessageSend;
use Itsm\Jobs\SendChangeEmail;
use Itsm\Model\Res\AuxStuff;
use Itsm\Model\Res\ResUsers;
use Itsm\Model\Usercenter\Change;
use Itsm\Model\Usercenter\Handover;
use Itsm\Model\Usercenter\HandoverEvent;
use Itsm\Model\Usercenter\HandoverRelation;
use Itsm\Model\Usercenter\HandOverTask;
use Log;
use GuzzleHttp\Client;
class HandoverDetailsController extends Controller
{
    private static $rMode = ['sms' => '短信', 'wechat' => '微信', 'email' => '邮件'];//提醒方式

    /**
     * 交接单详情
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function handoverDetails()
    {
        $handoverId = Input::get('handoverId');
        $handoverData = Handover::where('id', $handoverId)->first();
        $rModeArr = explode(',', $handoverData->remindType);
        $rMode = '';
        foreach ($rModeArr as $mode) {
            $rMode = $rMode . ($rMode ? ',' : '') . self::$rMode[$mode];
        }
        $ccIds = $handoverData->ccIds;
        $ccIdsArray = explode(',', $ccIds);
        return view('handover/handoverdetails', compact('handoverData', 'rMode', 'ccIds', 'ccIdsArray'));
    }

    /*
     * 事件详情
     */
    public function eventDetails($eventId)
    {
        $event = HandoverEvent::select('handover_event.*', 'a.handoverId')
            ->leftJoin('handover_relation as a', 'handover_event.id', '=', 'a.handEventId')
            ->where('handover_event.id', $eventId)
            ->where('a.inValidate', 0)
            ->first();
        $handover = Handover::select("*")->where("Id",$event->handoverId)->first();
        $remindType = $handover->remindType;
        $patterns = array();
        $patterns[0] = '/sms/';
        $patterns[1] = '/wechat/';
        $patterns[2] = '/email/';
        $replacements = array();
        $replacements[2] = '短信';
        $replacements[1] = '微信';
        $replacements[0] = '邮件';
        $remindType =preg_replace($patterns, $replacements, $remindType);
        $priority = \ThirdCallHelper::getPriority($event->priority);
        $type = \ThirdCallHelper::getRemindType($event->remindType);
        $csIds = $event->csIds;
        $csIdsArray = explode(',', $csIds);
        return view('handover/eventdetails', compact('event','handover','remindType', 'priority', 'type', 'csIdsArray'));
    }

    //事件编辑
    public function eventEdit($id)
    {
        $event = HandoverEvent::where('id', $id)->first();
        $submitUser = HandoverController::getSubmitUser();
        $eventType = ThirdCallHelper::getDictArray('工单类型', 'WorksheetTypeOne');
        $eventGroups = HandoverController::getEventGroups();
        $csIds = $event->csIds;
        $csIdsArray = explode(',', $csIds);
        $csNames = "";
        foreach($csIdsArray as $stuffId){
            $csName = \ThirdCallHelper::getStuffName($stuffId);
            $csNames .= $csName?$csName.",":"";
        }
        $dcGroups = ResUsers::select('UsersName')
            ->where("UsersName", 'like', "%中心组")->get();
        return view('handover/eventedit',
            compact('event', 'eventType', 'eventGroups', 'dcGroups', 'submitUser','csIds', 'csIdsArray','csNames'));
    }

    //事件编辑 新增交接单页面中的
    public function eventEdits()
    {
        $submitUser = HandoverController::getSubmitUser();
        $submitTime = date("Y-m-d H:i:s", time());
        $eventType = ThirdCallHelper::getDictArray('工单类型', 'WorksheetTypeOne');
        $eventGroups = HandoverController::getEventGroups();
        $dcGroups = ResUsers::select('UsersName')
            ->where("UsersName", 'like', "%中心组")->get();
        return view('handover/eventedits', compact('eventType', 'eventGroups', 'submitUser', 'submitTime', 'dcGroups'));
    }

    //事件编辑提交
    public function eventEditPush()
    {
        try {
            $userId = Request()->session()->get('user')->Id;
            DB::transaction(function () use ($userId) {
                $data = Input::except('_token');
                $notes = PublicMethodsHelper::htmlToSafe($data['notes']);
                $now = date('Y-m-d H:i:s', time());
                $csIds = $data['csIds'];//抄送人
                //现场运维部无抄送人时通知部门所有人
                if(trim($data['chargerId']) == "" && $data['dcGroup']!= ""
                    && $data['groupId'] == "second_dept_23" && $data['csIds'] == ""){
                    $stuffs = $this->getDep23Stuffs($data['dcGroup']);
                    foreach($stuffs as $stf){
                        $csIds .= $stf["Id"].",";
                    }
                }
                //dd($csIds);
                if ($data) {
                    $editData = [
                        'supportId'  => $data['supportId'],
                        'type'       => $data['type'],
                        'cusId'      => $data['cusId'],
                        'priority'   => $data['priority'],
                        'remindTs'   => $data['remindTs'],
                        'remindType' => $data['remindType'],
                        'groupId'    => $data['groupId'],
                        'dcGroup'    => $data['dcGroup'],
                        'feedback'   => $data['feedback'],
                        'chargerId'  => $data['chargerId'],
                        'csIds'      => trim($csIds,","),
                        'notes'      => $notes,
                        'upUserId'      => $userId,
                        'upTs'       => $now
                    ];
                    $editTaskData = [
                        'remindType'  => $data['remindType'],
                        'remindText'  => trim($notes),
                        'remindTs'    => $data['remindTs'],
                        'remindStuff' => $data['chargerId'],
                        'state'       => 1,         //重置发送
                        'UpTs'        => $now,
                        'UpUserId'    => $userId
                    ];
                    $save = HandoverEvent::where('id', $data['eventId'])->update($editData);
                    $save = HandOverTask::where('eventId', $data['eventId'])->update($editTaskData);
                }
            });
            return ['status' => true];
        } catch (\Exception $ex) {
            return ['status' => false, 'msg' => $ex->getMessage()];
        }

    }

    public function getDep23Stuffs($id){
        $stuffs = PublicMethodsHelper::getDepartStuffNames($id);
        if (empty($stuffs)) {
            $stuffs = ResUsers::selectRaw("aux_stuff.Id,aux_stuff.Name")
                ->leftJoin("res_usergroup", "res_users.Id", '=', "res_usergroup.UsersId")
                ->leftJoin("aux_stuff", "aux_stuff.Id", '=', "res_usergroup.UserId")
                ->where("res_users.UsersName", $id)
                ->get()->toArray();
        };
        return $stuffs;
    }

    /**
     * 事件删除(包括删除事件以及关系表和任务表并检查交接单是否完成)
     * @param $id
     * @return array
     */
    public function eventDelete($id)
    {
        try {
            $userId = Request()->session()->get('user')->Id;
            DB::transaction(function () use ($userId, $id) {
                $now = date('Y-m-d H:i:s', time());
                $reason = Request()->input('reason');
                $reason = PublicMethodsHelper::htmlToSafe($reason);
                $handoverId = Input::get('handoverId');
                $updateEvent = [
                    'inValidate'       => 1,
                    'inValidateAt'     => $now,
                    'inValidateUserId' => $userId,
                    'inValidateReason' => $reason,
                    'UpTs'             => $now
                ];
                $updateRelation = [
                    'inValidate'       => 1,
                    'inValidateUserId' => $userId,
                    'inValidateAt'     => $now
                ];
                $updateTask = [
                    'state'    => 0,
                    'UpTs'     => $now,
                    'UpUserId' => $userId
                ];
                $save = HandoverEvent::where('id', $id)->update($updateEvent);
                $save = HandoverRelation::where('handEventId', $id)->update($updateRelation);
                $save = HandOverTask::where('eventId', $id)->update($updateTask);
                if (!$handoverId) {
                    $handoverId = HandoverRelation::where('handEventId', Input::get('eventId'))->value('handoverId');
                }
                $unFinishCount = HandoverRelation::select('b.*', 'handover_relation.inValidate as isInValidate')
                    ->leftJoin('handover_event as b', 'handover_relation.handEventId', '=', 'b.id')
                    ->where('handover_relation.handoverId', $handoverId)
                    ->where('handover_relation.inValidate', 0)
                    ->where('b.inValidate', 0)
                    ->where('b.status', 0)->count();
                if ($unFinishCount == 0) {
                    Handover::where('id', $handoverId)->update(['status' => 1, 'upTs' => $now, 'upUserId' => $userId]);
                }
            });
            return ['status' => 'success', 'msg' => '事件移除成功！'];
        } catch (\Exception $ex) {
            return ['status' => false, 'msg' => $ex->getMessage()];
        }
    }

    //事件完成
    public function eventComplete($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $userId = Request()->session()->get('user')->Id;
                $now = date('Y-m-d H:i:s', time());
                $feedback = Request()->input('feedback');
                $feedback = PublicMethodsHelper::htmlToSafe($feedback);
                $comp = HandoverEvent::where('id', $id)
                    ->update([
                        'status'   => 1,
                        'feedback' => $feedback,
                        'upTs'     => $now,
                        'upUserId'     => $userId,
                        'solvedTs'     => $now,
                        'solvedId'     => $userId
                    ]);
                $handoverId = Input::get('handoverId');
                if (!$handoverId) {
                    $handoverId = HandoverRelation::where('handEventId', Input::get('eventId'))->value('handoverId');
                }
                $unFinishCount = HandoverRelation::select('b.*', 'handover_relation.inValidate as isInValidate')
                    ->leftJoin('handover_event as b', 'handover_relation.handEventId', '=', 'b.id')
                    ->where('handover_relation.handoverId', $handoverId)
                    ->where('handover_relation.inValidate', 0)
                    ->where('b.inValidate', 0)
                    ->where('b.status', 0)->count();
                if ($unFinishCount == 0) {
                    Handover::where('id', $handoverId)->update(['status' => 1, 'upTs' => $now, 'upUserId' => $userId]);
                }
                //获取提醒类型和抄送人
                $eventInfo = HandoverEvent::where('id',$id)->first();
                $supportMsg = $eventInfo->supportId == 0?"":"工单编号:{$eventInfo->supportId};";
                //添加到队列 发送邮件或短信
                $title = $supportMsg.'事件编号：' . $eventInfo->id . '的提醒：';
                $userIds = $this->getHandoverIds($id);//通知人去重
                $replyMsg = '交接单'.$handoverId.'子事件'.$eventInfo->id .'已完成，请知晓。'.'<br>'.'事件说明：'.$eventInfo->notes.'<br>反馈结果：'.$eventInfo->feedback;
                $replyMsg = ThirdCallHelper::subStr60($replyMsg);//控制短信长度
                $job = new ItsmMessageSend($title, $userIds, 'sms', $replyMsg); //创建队列任务
                $this->dispatch($job);
            });
            return array('status' => 'success');
        } catch (\Exception $ex) {
            return ['status' => false, 'msg' => $ex->getMessage()];
        }
    }
    //获取交接单的负责人及抄送人
    public function getHandoverIds($id)
    {
        //获取抄送人
        $handParentInfo = PublicMethodsHelper::getEventPInfoAndCCIdsById($id);
        //合并交接单分派负责人
        if ($handParentInfo['chargerId']) {
            $ccIds = $handParentInfo['chargerId'];
        }
        //合并通知人
        if ($handParentInfo['ccIds']) {
            $ccIds = $ccIds . "," .trim($handParentInfo['ccIds'], ",");
        }
        $ccIdsArr = explode(",", $ccIds);
        $ccIdsArr = implode(',', array_unique($ccIdsArr));
        return $ccIdsArr;
    }

    //事件转移
    public function eventTransfer($eventId)
    {
        $handoverId = HandoverRelation::where('handEventId', $eventId)->value('handoverId');
        return view('handover/eventtransfer', compact('eventId', 'handoverId'));
    }

    protected static $tableHand = 'usercenter.handover';

    //获取待转移的交接单
    public function getHandoverList()
    {
        $tableHand = self::$tableHand;
        $handoverList = Handover::select($tableHand . '.*')
            ->leftJoin('res.aux_stuff as a', $tableHand . '.chargerId', '=', 'a.Id')
            ->where($tableHand . '.inValidate', 0)
            ->where($tableHand . '.status', 0)
            ->orderBy($tableHand . '.id', 'desc');
        if ($keyword = Input::get('searchTransfer')) {
            $handoverList = $handoverList->where(function ($handoverList) use ($keyword, $tableHand) {
                $handoverList->Where($tableHand . '.id', 'like', '%' . $keyword . '%')
                    ->orWhere('a.Name', 'like', '%' . $keyword . '%');
            });
        }
        if ($eventId = Input::get('eventId')) {
            $sql = "select handoverId from `handover_relation` where `handEventId` = $eventId and `handoverId` <> 0 and `inValidate` = 0 order by `id` desc";
            $handoverList = $handoverList->whereRaw("({$tableHand}.id not in($sql))");
        }
        $total = $handoverList->count();
        $handoverLists['total'] = $total;
        $pageSize = !empty(Input::get('pageSize')) ? Input::get('pageSize') : 10;
        $pageNumber = !empty(Input::get('pageNumber')) ? Input::get('pageNumber') : 1;
        $handoverList = $handoverList->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();
        $handoverList = Change::translationStuff($handoverList, 'chargerId');
        $handoverList = Change::translationStuff($handoverList, 'submitterId');
        $handoverLists['rows'] = $handoverList;
        return $handoverLists;
    }

    /**
     * 创建事件-交接单转移
     * @param Request $req
     * @param Response $res
     * @return array
     */
    public function createTransfer(Request $req, Response $res)
    {
        try {
            DB::transaction(function () use ($req) {
                $request = $req->input();
                $handoverId = Arr::get($request, "handoverId", 0);
                $userId = Request()->session()->get('user')->Id;
                $now = date('Y-m-d H:i:s', time());
                $oldId = Arr::get($request, "oldId", 0);
                $handEventId = Arr::get($request, "handEventId", 0);
                $deleteData = HandoverRelation::where('handEventId', $handEventId)->orderBy('ts', 'desc')->update(
                    [
                        'inValidate'       => 1,
                        'inValidateUserId' => $userId,
                        'inValidateAt'     => date('Y-m-d H:i:s', time())
                    ]);
                $createData = HandoverRelation::insertGetId([
                        'handoverId'  => $handoverId,
                        'handEventId' => $handEventId,
                        'ts'          => date('Y-m-d H:i:s', time()),
                    ]
                );
                $unFinishCount = HandoverRelation::select('b.*', 'handover_relation.inValidate as isInValidate')
                    ->leftJoin('handover_event as b', 'handover_relation.handEventId', '=', 'b.id')
                    ->where('handover_relation.handoverId', $oldId)
                    ->where('handover_relation.inValidate', 0)
                    ->where('b.inValidate', 0)
                    ->where('b.status', 0)->count();
                if ($unFinishCount == 0) {
                    Handover::where('id', $oldId)->update(['status' => 1, 'upTs' => $now, 'upUserId' => $userId]);
                }
            });
            return ['status' => 'success', "msg" => '事件转移成功！'];
        } catch (\Exception $ex) {
            return ['state' => false, "msg" => $ex->getMessage()];
        }
    }
}