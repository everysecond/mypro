<?php

namespace Itsm\Http\Controllers\Handover;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Input;
use Itsm\Http\Controllers\Controller;
use Itsm\Http\Helper\PublicMethodsHelper;
use Itsm\Http\Helper\ThirdCallHelper;
use Itsm\Model\Res\AuxDict;
use Itsm\Model\Res\AuxStuff;
use Itsm\Model\Res\ResUserGroup;
use Itsm\Model\Res\ResUsers;
use Itsm\Model\Usercenter\Change;
use Itsm\Model\Usercenter\Handover;
use Itsm\Model\Usercenter\HandoverEvent;
use Itsm\Model\Usercenter\HandoverRelation;
use Itsm\Model\Usercenter\HandOverTask;
use Itsm\Model\Usercenter\Support;

class HandoverController extends Controller
{
    //数据Code数组
    private static $rMode = ['sms' => '短信', 'wechat' => '微信', 'email' => '邮件'];//提醒方式
    private static $rType = [
        'no'      => '不需要',
        'two'     => '两分钟',
        'five'    => '五分钟',
        'ten'     => '十分钟',
        'fifteen' => '十五分钟'
    ];//提醒间隔
    private static $status = ['未处理', '已处理', '已转移'];//状态
    private static $priority = ['一般', '重要'];//优先级

    /**  事件表名称 @var string */
    protected static $tableEvent = 'usercenter.handover_event';
    protected static $tableRelation = 'usercenter.handover_relation';

    //事件申请
    public function eventApply(Request $req)
    {
        $submitUser = $this->getSubmitUser();
        if ($id = Input::get('Id')) {
            return $userName = $this->getSupportId($id);
        }
        if ($id = Input::get('supportId')) {
            return $this->getSupportDetail($id);
        }
        if ($supportId = $req->input('supportId')) {//工单对应客户
            return ThirdCallHelper::getCusId($supportId);
        }
        if ($cusName = $req->input('name')) {//搜索对应客户
            return ThirdCallHelper::getCusInfName($cusName);
        }
        $submitTime = date("Y-m-d H:i:s", time());
        $eventGroups = $this->getEventGroups();
        $eventType = ThirdCallHelper::getDictArray('工单类型', 'WorksheetTypeOne');
        $dcGroups = ResUsers::select('UsersName')
            ->where("UsersName", 'like', "%中心组")->get();
        return view('handover/eventapply', compact('eventGroups', 'eventType', 'submitUser', 'submitTime', 'dcGroups'));
    }

    /**
     * 新增交接单时的提交事件模板页面
     * @param Request $req
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function newEvent(Request $req)
    {
        $submitUser = $this->getSubmitUser();
        $submitTime = date("Y-m-d H:i:s", time());
        $eventGroups = $this->getEventGroups();
        $dcGroups = ResUsers::select('UsersName')
            ->where("UsersName", 'like', "%中心组")->get();
        $eventType = ThirdCallHelper::getDictArray('工单类型', 'WorksheetTypeOne');
        return view('handover/newevent', compact('eventGroups', 'eventType', 'submitUser', 'submitTime', 'dcGroups'));
    }

    /**
     * 编辑页面给已存在交接单添加事件
     * @return array
     */
    public function eventPush()
    {
        try {
            $getAll = Input::except('_token');
            $userId = Request()->session()->get('user')->Id;
            $now = date('Y-m-d H:i:s', time());
            $notes = PublicMethodsHelper::htmlToSafe($getAll['notes']);
            $csIds = $getAll['csIds'];//抄送人
            //现场运维部无抄送人时通知部门所有人
            if(trim($getAll['dcGroup']) != "" && $getAll['chargerId'] == ""
                && $getAll['groupId'] == "second_dept_23"&& $getAll['csIds'] == ""){
                $stuffs = $this->getDep23Stuffs($getAll['dcGroup']);
                foreach($stuffs as $stf){
                    $csIds .= $stf["Id"].",";
                }
            }
            if ($handoverId = $getAll['handoverId']) {
                $eventId = HandoverEvent::insertGetId([
                    'supportId'   => $getAll['supportId'],
                    'type'        => $getAll['type'],
                    'cusId'       => $getAll['cusId'],
                    'priority'    => $getAll['priority'],
                    'remindTs'    => $getAll['remindTs'],
                    'remindType'  => $getAll['remindType'],
                    'groupId'     => $getAll['groupId'],
                    'dcGroup'     => $getAll['dcGroup'],
                    'chargerId'   => $getAll['chargerId'],
                    'csIds'       => trim($csIds,","),
                    'notes'       => $notes,
                    'submitterId' => $userId,
                    'ts'          => $now,
                    'upTs'          => $now
                ]);
                if ($eventId > 1) {
                    $upData=[
                        'upUserId'      => $userId,
                        'upTs'       => $now
                    ];
                    $save = Handover::where("id",$handoverId)->update($upData);
                    //事件添加成功后添加关系表及任务表数据
                    HandoverRelation::insertGetId([
                        'handoverId'  => $handoverId,
                        'handEventId' => $eventId,
                        'ts'          => $now
                    ]);
                    HandOverTask::insertGetId([
                        'eventId'     => $eventId,
                        'remindType'  => $getAll['remindType'],
                        'remindText'  => trim($notes),
                        'remindTs'    => $getAll['remindTs'],
                        'remindStuff' => $getAll['chargerId']
                    ]);
                }
            }
            return ['status' => true, 'msg' => '事件添加成功！'];
        } catch (\Exception $ex) {
            return ['status' => false, 'msg' => $ex->getMessage()];
        }
    }

    /**
     * 获取所有部门
     */
    public static function getEventGroups()
    {
        $eventGroup = \DB::select("select DISTINCT(d.Means),b.Depart,c.Means as MeansOne,b.second_dept,d.Means as MeansTwo from auth.authorities as a JOIN res.aux_stuff as b on a.username=b.Login LEFT JOIN res.aux_dict as c on b.Depart=c.`Code` and  c.DomainCode='DepartType' LEFT JOIN res.aux_dict as d on b.second_dept=d.`Code` and  d.DomainCode='second_dept' ORDER BY c.Code = 2 desc,c.Code = 18 desc ,c.Code = 19 desc ,c.Code = 'prod' desc");
        $oneGroup = [];
        if ($eventGroup) {
            /**
             * 取出一级部门
             */
            foreach ($eventGroup as $item) {
                if (!empty($item->second_dept)) {
                    $oneGroup[$item->Depart]['name'] = $item->MeansOne;
                }
            }
            /**
             * 取出一级部门下面的二级部门并且返回
             */
            foreach ($eventGroup as $childItem) {
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

    /**
     * 获取某部门所有员工
     * @return array
     */
    public function getDepStuffs()
    {
        if ($id = Input::get('depId')) {
            $names = PublicMethodsHelper::getDepartStuffNames($id);
            if (empty($names)) {
                $names = ResUsers::selectRaw("aux_stuff.Id,aux_stuff.Name")
                    ->leftJoin("res_usergroup", "res_users.Id", '=', "res_usergroup.UsersId")
                    ->leftJoin("aux_stuff", "aux_stuff.Id", '=', "res_usergroup.UserId")
                    ->where("res_users.UsersName", $id)
                    ->get()->toArray();
            };
            return $names;
        } else {
            return [];
        }
    }
    /**
     * 获取数据中心组
     * @return array
     */
    public function getDCDept()
    {
        $dcGroups = ResUsers::select('UsersName')
            ->where("UsersName", 'like', "%中心组")->get();
        return $dcGroups;
    }

    //获取工单编号
    public function getSupportId($id)
    {
        $supportId = Support::where('Id', 'like', '%' . $id . '%')->take(5)
            ->get();
        foreach ($supportId as &$value) {
            $value->cusName = ThirdCallHelper::getCusName($value->CustomerInfoId);
        }
        return $supportId;
    }

    //获取工单详情
    public function getSupportDetail($id)
    {
        $support = Support::selectRaw("priority,ClassInficationOne")->where('Id', $id)->get();
        foreach ($support as &$item) {
            if ($item['priority'] && ($item['priority'] == 0 || $item['priority'] == 1 || $item['priority'] == 2)) {
                $item['priority'] = 0;
            } else {
                if ($item['priority'] && $item['priority'] == 3) {
                    $item['priority'] = 1;
                }
            }

            $item['typeName'] = ThirdCallHelper::getDictMeans('工单类型', 'WorksheetTypeOne', $item['ClassInficationOne']);
        }
        return $support;
    }

    /*
    * 获取事件提交人
    */
    public static function getSubmitUser()
    {
        $user = Request()->session()->get('user')->Id;
        $name = AuxStuff::select('Name')
            ->where('Id', '=', $user)
            ->first()->toArray();
        return $name;
    }

    /*
     * 待办交接单列表+待办事件列表
     */
    public function todoList()
    {
        $typeList = ThirdCallHelper::getDictArray('工单类型', 'WorksheetTypeOne');
        return view('handover/todolist', compact('typeList'));
    }

    /*
     * 全部交接单列表+全部事件列表
     */
    public function allList()
    {
        $typeList = ThirdCallHelper::getDictArray('工单类型', 'WorksheetTypeOne');
        return view('handover/alllist', compact('typeList'));
    }

    /*
     * 待办交接单列表数据
     */
    public function todoListData()
    {
        $userId = Request()->session()->get('user')->Id;
        $todoList = Handover::selectRaw('count(case when c.priority=1 then 1 else null end) as priority,handover.* ')
            ->selectRaw('count(case when c.status=0 then 1 else null end) as notDone,count(c.id) as allEvents')
            ->leftJoin('handover_relation as b', 'handover.id', '=', 'b.handoverId')
            ->leftJoin('handover_event as c', 'b.handEventId', '=', 'c.id')
            ->where('handover.inValidate', 0)
            ->where('b.inValidate', 0)
            ->where('handover.status', 0)
            ->groupBy('handover.id')
            ->orderByraw("handover.ts desc");
        $role = $this->getUserRole();
        if ($role != 'desk' && $role != 'desk_manager' && $role != 'BOSS') {
            $todoList = $todoList->where(function ($todoList) use ($userId) {
                $todoList->Where('handover.ccIds', 'like', '%' . $userId . '%')
                    ->orwhere("handover.chargerId", $userId)
                    ->orwhere("handover.submitterId", $userId);
            });
        }
        //筛选优先级和事件类型
        if (strlen($priority = Input::get('priority'))) {
            if ($priority == 1) {
                $todoList = $todoList->having('priority', '>', 0);
            } else {
                $todoList = $todoList->having('priority', '=', 0);
            }
        }
        if ($type = Input::get('type')) {
            $todoList = $todoList->where('handover.remindType', 'like', "%$type%");
        }
        $todoListArray['total'] = count($todoList->lists("DISTINCT(handover.id)"));
        //分页
        $pageSize = !empty(Input::get('pageSize')) ? Input::get('pageSize') : 10;
        $pageNumber = !empty(Input::get('pageNumber')) ? Input::get('pageNumber') : 1;
        $todoList = $todoList->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();
        $todoList = Change::translationStuff($todoList, 'chargerId');
        $todoList = Change::translationStuff($todoList, 'submitterId');
        $todoList = Handover::translaltionStatus($todoList);
        $todoListArray['rows'] = $todoList;
        return $todoListArray;
    }

    /*
     * 所有交接单列表数据
     */
    public function allListData()
    {
        $userId = Request()->session()->get('user')->Id;
        $allList = Handover::selectRaw('count(case when c.priority=1 and c.status=0 then 1 else null end) as priority,handover.* ')
            ->selectRaw('count(case when c.status=0 and b.inValidate = 0 then 1 else null end) as notDone')
            ->selectRaw('count(case when b.inValidate = 0 then 1 else null end) as allEvents')
            ->selectRaw('count(b.id) as allEventsCount')
            ->leftJoin('usercenter.handover_relation as b', 'handover.id', '=', 'b.handoverId')
            ->Raw('and b.inValidate =  0')
            ->leftJoin('handover_event as c', 'b.handEventId', '=', 'c.id')
            ->leftJoin('res.aux_stuff as a', 'handover.chargerId', '=', 'a.Id')
            ->where("handover.inValidate",0)
            ->groupBy('handover.id')
            ->orderByraw("handover.status asc,handover.ts desc");
//        $role = $this->getUserRole();
//        if ($role != 'desk' && $role != 'desk_manager' && $role != 'BOSS') {
//            $allList = $allList->where("handover.chargerId", $userId)
//                ->orwhere("handover.ccIds", 'like', "%" . $userId . "%");
//        }

        //根据时间窗口或者实际完成时间筛选
        $handStartTime = Input::get('handStartTime') ? Input::get('handStartTime') : date('Y-m-d H:i:s',
            time());
        $handEndTime = Input::get('handEndTime') ? Input::get('handEndTime') : date('Y-m-d H:i:s', time());

        //有一个不为空,则命中条件,另一个默认为当前时间
        if (!empty(Input::get('handStartTime')) || !empty(Input::get('handEndTime'))) {
            $allList = $allList->whereBetween("handover.ts", [$handStartTime, $handEndTime]);
        }
        //筛选优先级和事件类型
        if (strlen($priority = Input::get('priority'))) {
            if ($priority == 1) {
                $allList = $allList->having('priority', '>', 0);
            } else {
                $allList = $allList->having('priority', '=', 0);
            }
        }
        //筛除一个事件都没有的交接单
        $allList = $allList->having('allEventsCount', '>', 0);
        if ($type = Input::get('remind')) {
            $allList = $allList->whereRaw('handover.remindType like "%' . $type . '%"');
        }
        //根据关键字模糊查询
        if ($keyword = Input::get('searchHand')) {
            $allList = $allList->where(function ($todoList) use ($keyword) {
                $todoList->where('handover.notes', 'like', '%' . $keyword . '%')
                    ->orWhere('handover.id', 'like', '%' . $keyword . '%')
                    ->orWhere('a.Name', 'like', '%' . $keyword . '%');
            });
        }
        $allListArray['total'] = count($allList->lists("DISTINCT(handover.id)"));
        //分页
        $pageSize = !empty(Input::get('pageSize')) ? Input::get('pageSize') : 10;
        $pageNumber = !empty(Input::get('pageNumber')) ? Input::get('pageNumber') : 1;
        $allList = $allList->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();
        $allList = Change::translationStuff($allList, 'chargerId');
        $allList = Handover::translaltionStatus($allList);
        $allList = Change::translationStuff($allList, 'submitterId');

        $allListArray['rows'] = $allList;
        return $allListArray;
    }

    /**
     * 新增交接单模板页面
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function handoverApply()
    {
        $groups = $this->getEventGroups();
        return view('handover/handoverapply', compact('groups'));
    }

    /**
     * 交接单提交
     * @return array
     */
    public function handoverSub()
    {
        try {
            $getAll = Input::except('_token');
            $formData = $getAll['formData'];
            $eventsData = $getAll['eventsData'];
            $userId = Request()->session()->get('user')->Id;
            //事物机制填充数据，异常时回滚
            $handoverId = DB::transaction(function () use ($eventsData, $userId, $formData) {
                $now = date('Y-m-d H:i:s', time());
                if ($formData) {
                    $handNotes = PublicMethodsHelper::htmlToSafe($formData['notes']);
                    //提交交接单基本信息
                    $handoverId = Handover::insertGetId([
                        'remindType'  => $formData['rmode'],
                        'ccIds'       => $formData['ccIds'],
                        'groupId'     => $formData['groupId'],
                        'chargerId'   => $formData['chargerId'],
                        'notes'       => trim($handNotes),
                        'submitterId' => $userId,
                        'ts'          => $now,
                        'upUserId'   => $userId,
                        'upTs'          => $now
                    ]);
                    if ($handoverId > 1) {
                        //交接单提交成功添加包含的事件
                        foreach ($eventsData as $event) {
                            $csIds = $event['csIds'];//抄送人
                            //现场运维部无抄送人时通知部门所有人
                            if( $event['dcGroup']!= "" && $event['groupId'] == "second_dept_23"
                                && $event['chargerId'] ==''&& $event['csIds'] ==''){
                                $stuffs = $this->getDep23Stuffs($event['dcGroup']);
                                foreach($stuffs as $stf){
                                    $csIds .= $stf["Id"].",";
                                }
                            }

                            $eventNotes = PublicMethodsHelper::htmlToSafe($event['notes']);
                            $eventId = HandoverEvent::insertGetId([
                                'supportId'   => $event['supportId'],
                                'type'        => $event['type'],
                                'cusId'       => $event['cusId'],
                                'priority'    => $event['priority'],
                                'remindTs'    => $event['remindTs'],
                                'remindType'  => $event['remindType'],
                                'groupId'     => $event['groupId'],
                                'dcGroup'     => $event['dcGroup'],
                                'chargerId'   => $event['chargerId'],
                                'csIds'       => trim($csIds,","),
                                'notes'       => trim($eventNotes),
                                'submitterId' => $userId,
                                'ts'          => $now
                            ]);
                            if ($eventId > 1) {
                                //事件添加成功后添加关系表及任务表数据
                                HandoverRelation::insertGetId([
                                    'handoverId'  => $handoverId,
                                    'handEventId' => $eventId,
                                    'ts'          => $now
                                ]);
                                HandOverTask::insertGetId([
                                    'eventId'     => $eventId,
                                    'remindType'  => $event['remindType'],
                                    'remindText'  => trim($eventNotes),
                                    'remindTs'    => $event['remindTs'],
                                    'remindStuff' => $event['chargerId']
                                ]);
                            }
                        }
                        return $handoverId;
                    }
                }
            });
            return ['status' => true, 'msg' => '交接单提交成功！', 'retId' => $handoverId];
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
     * 交接单编辑页面
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function handoverEdit()
    {
        $handoverId = Input::get('handoverId');
        $handoverData = Handover::where('id', $handoverId)->first();
        $rMode = explode(',', $handoverData->remindType);
        $ccIds = $handoverData->ccIds;
        $ccIdsArray = explode(',', $ccIds);
        $groups = $this->getEventGroups();
        return view('handover/handoveredit', compact('groups', 'handoverData', 'rMode', 'ccIds', 'ccIdsArray'));
    }

    /**
     * 交接单编辑 提交保存
     * @return array
     */
    public function editPush()
    {
        try {
            $getAll = Input::except('_token');
            $userId = Request()->session()->get('user')->Id;
            DB::transaction(function () use ($getAll, $userId) {
                $formData = $getAll['formData'];
                $now = date('Y-m-d H:i:s', time());
                $handNotes = PublicMethodsHelper::htmlToSafe($formData['notes']);
                $updateHandover = [
                    'remindType' => $formData['rmode'],
                    'ccIds'      => $formData['ccIds'],
                    'groupId'    => $formData['groupId'],
                    'chargerId'  => $formData['chargerId'],
                    'notes'      => trim($handNotes),
                    'upUserId'   => $userId,
                    'upTs'       => $now
                ];
                $save = Handover::where('id', $formData['handoverId'])->update($updateHandover);
            });
            return ['status' => true, 'msg' => '编辑成功！'];
        } catch (\Exception $ex) {
            return ['status' => false, 'msg' => $ex->getMessage()];
        }
    }

    /**
     * 获取对应交接单所有事件
     * @return mixed
     */
    public function getEvents()
    {
        $userId = Request()->session()->get('user')->Id;
        $handoverId = Input::get('handoverId');
        $events = HandoverRelation::select('b.*', 'c.status as isDone', 'handover_relation.inValidate as isInValidate')
            ->leftJoin('handover as c', 'handover_relation.handoverId', '=', 'c.id')
            ->leftJoin('handover_event as b', 'handover_relation.handEventId', '=', 'b.id')
            ->where('handover_relation.handoverId', $handoverId)
            ->where('b.inValidate', 0)
            ->orderByRaw("handover_relation.inValidate = 1 asc,b.status asc,
            b.chargerId = $userId,b.priority desc,b.upTs desc,b.id desc");
        $eventsArray['total'] = $events->count();
        //分页
        $pageSize = !empty(Input::get('pageSize')) ? Input::get('pageSize') : 10;
        $pageNumber = !empty(Input::get('pageNumber')) ? Input::get('pageNumber') : 1;
        $events = $events->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();

        $events = Support::translationDict($events, 'type', 'WorkSheetTypeOne');
        $events = Support::translationCusName($events, 'cusId');
        $events = Change::translationStuff($events, 'chargerId');
        $events = Change::translationStuff($events, 'submitterId');
        $eventsArray['rows'] = $events;
        return $eventsArray;
    }

    /**
     * 编辑页面 移除事件使事件失效
     * @return array
     */
    public function removeEvent()
    {
        try {
            $getAll = Input::except('_token');
            $userId = Request()->session()->get('user')->Id;
            DB::transaction(function () use ($getAll, $userId) {
                $eventId = $getAll['removeEventId'];
                $now = date('Y-m-d H:i:s', time());
                $updateEvent = [
                    'inValidate'       => 1,
                    'inValidateAt'     => $now,
                    'inValidateUserId' => $userId,
                    'UpTs'             => $now
                ];
                $updateRelation = [
                    'inValidate'   => 1,
                    'inValidateAt' => $now
                ];
                $updateTask = [
                    'state'    => 0,
                    'UpTs'     => $now,
                    'UpUserId' => $userId
                ];
                $save = HandoverEvent::where('id', $eventId)->update($updateEvent);
                $save = HandoverRelation::where('handEventId', $eventId)->update($updateRelation);
                $save = HandOverTask::where('eventId', $eventId)->update($updateTask);
            });
            return ['status' => true, 'msg' => '事件移除成功！'];
        } catch (\Exception $ex) {
            return ['status' => false, 'msg' => $ex->getMessage()];
        }
    }

    //待办事件列表
    public function eventTodoList()
    {
        $handoverId = Input::get('handoverId');
        $typeList = ThirdCallHelper::getDictArray('工单类型', 'WorksheetTypeOne');
        return view('handover/eventtodolist', compact('typeList', 'handoverId'));
    }

    //待办事件列表数据
    public function getEventTodoListData()
    {
        $tableRelation = self::$tableRelation;
        $userId = Request()->session()->get('user')->Id;
        if ($handoverId = Input::get('handoverId')) {
            $todoList = HandoverRelation::select($tableRelation . '.*', 'a.*')
                ->leftJoin('usercenter.handover_event as a', 'a.id', '=', $tableRelation . '.handEventId')
                ->leftJoin('res.res_cusinf as b', 'a.cusId', '=', 'b.Id')
                ->leftJoin('res.aux_stuff as c', 'a.chargerId', '=', 'c.Id')
                ->where($tableRelation . '.inValidate', 0)
                ->where($tableRelation . '.handoverId', $handoverId)
                ->where('a.status', 0);
        } else {
            $todoList = HandoverRelation::select($tableRelation . '.*', 'a.*')
                ->leftJoin('usercenter.handover_event as a', 'a.id', '=', $tableRelation . '.handEventId')
                ->leftJoin('res.res_cusinf as b', 'a.cusId', '=', 'b.Id')
                ->leftJoin('res.aux_stuff as c', 'a.chargerId', '=', 'c.Id')
                ->where($tableRelation . '.inValidate', 0)
                ->where('a.status', 0);
        }
        //根据时间窗口或者实际完成时间筛选
        $eventStartTime = Input::get('eventStartTime') ? Input::get('eventStartTime') : date('Y-m-d H:i:s',
            time());
        $eventEndTime = Input::get('eventEndTime') ? Input::get('eventEndTime') : date('Y-m-d H:i:s', time());

        //有一个不为空,则命中条件,另一个默认为当前时间
        if (!empty(Input::get('eventStartTime')) || !empty(Input::get('eventEndTime'))) {
            $todoList = $todoList->whereBetween('a.remindTs', [$eventStartTime, $eventEndTime]);
        }

        //筛选优先级和事件类型
        if (strlen($priority = Input::get('priority'))) {
            $todoList = $todoList->where('a.priority', $priority);
        }
        if ($type = Input::get('type')) {
            $todoList = $todoList->where('a.type', $type);
        }
        //根据关键字模糊查询
        if ($keyword = Input::get('searchInfo')) {
            $todoList = $todoList->where(function ($todoList) use ($keyword, $tableRelation) {
                $todoList->Where('a.supportId', 'like', '%' . $keyword . '%')
                    ->orWhere('b.CusName', 'like', '%' . $keyword . '%')
                    ->orWhere('c.Name', 'like', '%' . $keyword . '%')
                    ->orWhere('a.id', 'like', '%' . $keyword . '%')
                    ->orWhere($tableRelation . '.handoverId', 'like', '%' . $keyword . '%');
            });
        }
        $todoListCount = $todoList->count();
        $todoList = $todoList->orderByRaw("a.chargerId = $userId desc, a.priority desc,a.ts desc");
        $todoListArray['total'] = $todoListCount;
        //分页
        $pageSize = !empty(Input::get('pageSize')) ? Input::get('pageSize') : 10;
        $pageNumber = !empty(Input::get('pageNumber')) ? Input::get('pageNumber') : 1;
        $todoList = $todoList->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();
        $todoList = Support::translationDict($todoList, 'type', 'WorkSheetTypeOne');
        $todoList = Support::translationCusName($todoList, 'cusId');
        $todoList = Handover::translationId($todoList);
        $todoList = Change::translationStuff($todoList, 'chargerId');
        $todoList = Change::translationStuff($todoList, 'submitterId');
        $todoList = PublicMethodsHelper::codeToChinese($todoList, self::$priority, 'priority');
        $todoList = ThirdCallHelper::identity($todoList);
        $todoList = Handover::translaltionStatus($todoList);
        $todoListArray['rows'] = $todoList;
        return $todoListArray;
    }

    //所有事件列表
    public function eventAllList()
    {
        $handoverId = Input::get('handoverId');
        $typeList = ThirdCallHelper::getDictArray('工单类型', 'WorksheetTypeOne');
        return view('handover/eventalllist', compact('typeList', 'handoverId'));
    }

    //所有事件列表数据
    public function getEventAllListData()
    {
        $tableEvent = self::$tableEvent;
        $userId = Request()->session()->get('user')->Id;
        $tableRelation = self::$tableRelation;
        if ($handoverId = Input::get('handoverId')) {
            $allList = HandoverRelation::select($tableRelation . '.*', $tableEvent . '.*',
                $tableRelation . '.inValidate as isInValidate')
                ->leftJoin('usercenter.handover_event', $tableEvent . '.id', '=',
                    $tableRelation . '.handEventId')
                ->leftJoin('res.res_cusinf as b', $tableEvent . '.cusId', '=', 'b.Id')
                ->leftJoin('res.aux_stuff as c', $tableEvent . '.chargerId', '=', 'c.Id')
                ->where($tableRelation . '.inValidate', 0)
                ->where($tableRelation . '.handoverId', $handoverId);
        } else {
            $allList = HandoverRelation::select($tableEvent . '.*',
                $tableRelation . '.inValidate as isInValidate')
                ->leftJoin('usercenter.handover_event', $tableEvent . '.id', '=',
                    $tableRelation . '.handEventId')
                ->leftJoin('res.aux_stuff as c', $tableEvent . '.chargerId', '=', 'c.Id')
                ->leftJoin('res.res_cusinf as b', $tableEvent . '.cusId', '=', 'b.Id');
        }
        //根据时间窗口或者实际完成时间筛选
        $eventStartTime = Input::get('eventStartTime') ? Input::get('eventStartTime') : date('Y-m-d H:i:s',
            time());
        $eventEndTime = Input::get('eventEndTime') ? Input::get('eventEndTime') : date('Y-m-d H:i:s', time());

        //有一个不为空,则命中条件,另一个默认为当前时间
        if (!empty(Input::get('eventStartTime')) || !empty(Input::get('eventEndTime'))) {
            $allList = $allList->whereBetween($tableEvent . '.ts', [$eventStartTime, $eventEndTime]);
        }
        //筛选优先级和事件类型
        if (strlen($priority = Input::get('priority'))) {
            $allList = $allList->where($tableEvent . '.priority', $priority);
        }
        if (strlen($priority = Input::get('status'))) {
            if ($priority == 0) {
                $allList = $allList->where($tableEvent . '.status', $priority)
                    ->where($tableRelation . '.inValidate', 0);
            } else {
                $allList = $allList->where($tableEvent . '.status', $priority);
            }

        }
        if ($type = Input::get('type')) {
            $allList = $allList->where($tableEvent . '.type', $type);
        }
        //根据关键字模糊查询
        if ($keyword = Input::get('searchInfo')) {
            $allList = $allList->where(function ($allList) use ($keyword, $tableEvent, $tableRelation) {
                $allList->Where($tableEvent . '.supportId', 'like', '%' . $keyword . '%')
                    ->orWhere('b.CusName', 'like', '%' . $keyword . '%')
                    ->orWhere('c.Name', 'like', '%' . $keyword . '%')
                    ->orWhere($tableEvent . '.id', 'like', '%' . $keyword . '%')
                    ->orWhere($tableRelation . '.handoverId', 'like', '%' . $keyword . '%');
            });
        }
        //排序
        $allList = $allList->orderByRaw("{$tableRelation}.inValidate asc,{$tableEvent}.status asc,{$tableEvent}.chargerId = $userId desc,{$tableEvent}.priority desc,{$tableEvent}.ts desc");
        $allListArray['total'] = $allList->count();
        //分页
        $pageSize = !empty(Input::get('pageSize')) ? Input::get('pageSize') : 10;
        $pageNumber = !empty(Input::get('pageNumber')) ? Input::get('pageNumber') : 1;
        $allList = $allList->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();
        $allList = Support::translationDict($allList, 'type', 'WorkSheetTypeOne');
        $allList = Support::translationCusName($allList, 'cusId');
        $allList = PublicMethodsHelper::codeToChinese($allList, self::$priority, 'priority');
        $allList = PublicMethodsHelper::codeToChinese($allList, self::$status, 'status');
        $allList = Handover::translationId($allList);
        $allList = ThirdCallHelper::identity($allList);
        $allList = Change::translationStuff($allList, 'chargerId');
        $allList = Change::translationStuff($allList, 'submitterId');

        $allListArray['rows'] = $allList;
        return $allListArray;
    }

}