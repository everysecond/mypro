<?php

namespace Itsm\Http\Controllers\Supports;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Itsm\Http\Controllers\Controller;
use Itsm\Http\Helper\PublicMethodsHelper;
use Itsm\Http\Helper\ThirdCallHelper;
use Itsm\Jobs\SendSms;
use Itsm\Jobs\SpeedAnswer;
use Itsm\Jobs\SendEmail;
use Itsm\Model\Auth\Authorities;
use Itsm\Model\Cloud\InsProject;
use Itsm\Model\Res\AuxDict;
use Itsm\Model\Res\AuxStuff;
use Itsm\Model\Res\MyQuote;
use Itsm\Model\Res\ResContact;
use Itsm\Model\Res\ResCusInf;
use Itsm\Model\Res\ResUsers;
use Itsm\Model\Usercenter\Operation;
use Itsm\Model\Usercenter\Support;
use Itsm\Model\Usercenter\SupportClassConfig;
use Itsm\Model\Usercenter\SupportHangupTask;
use Itsm\Model\Usercenter\Suppstencil;
use Itsm\Model\Usercenter\TimedEvents;
use Itsm\Model\Usercenter\Userlogin;
use Maatwebsite\Excel\Excel;
use Redirect;
use Log;

/**
 * 工单详情操作
 * @author liky@51idc.com
 */
class UserSupportController extends Controller
{

    public function test(Request $request, Excel $excel)
    {
        //工单导出实例
        $filename = '工单列表' . date('Ymd', time());
        $list = Support::select("Title", "Status")->skip(0)->take(10)->get()->toArray();
        foreach ($list as $st) {
            $export_data[] = [
                "工单\n标题" => $st['Title'],
                "工单状态"   => PublicMethodsHelper::$supportStatusList["{$st['Status']}"],
            ];
        }
        if (empty($list)) {
            $export_data[] = [
                '工单标题' => '',
                '工单状态' => '',
            ];
        }
        //excel导出
        $excel->create($filename, function ($excel) use ($export_data) {
            $excel->sheet('export', function ($sheet) use ($export_data) {
                $sheet->fromArray($export_data);
            });
        })->export('xls');
    }

    //获取当前工单Last10
    public function last10($id)
    {
        $ids=[];
        $month = date('Y-m-d H:i:s', strtotime("-3 month"));
        $sup = Support::where('id', $id)->first();
        $topList1 = Support::select("Id", "Title")
            ->whereRaw("(EquipmentId='{$sup['EquipmentId']}' and CustomerInfoId='{$sup['CustomerInfoId']}')")
            ->where("Ts", ">", $month)
            ->orderBy('id', 'desc')
            ->take(10)->get();
        $num = count($topList1);
        foreach($topList1 as $list){
            $ids[] = $list->Id;
        }
        $topList2 = Support::select("Id", "Title")
            ->whereRaw("(ClassInficationOne='{$sup['ClassInficationOne']}'and CustomerInfoId='{$sup['CustomerInfoId']}')")
            ->whereNotIn('Id',$ids)
            ->where("Ts", ">", $month)
            ->orderBy('id', 'desc')
            ->take(10-$num)->get();
        $list=[
            'list1'=>$topList1,
            'list2'=>$topList2,
        ];
        return $list;
    }

    /**
     * 工单详情
     * @param type $id
     * @return type
     */
    public function supportRefer($id)
    {
        $list = Support::where(['id' => $id])->first(); //工单信息
        $topList = self::last10($id);
        $secondclass = "";
        $contact = [
            "Name"=>$list->contactName,
            "Mobile"=>$list->mobile,
            "Email"=>$list->email,
            "Tel"=>""
        ]; //新平台联系人信息存在工单中未存联系人Id
        $userinfo = ""; //用户信息
        $userName = ""; //用户信息
        $customer = ""; //客户信息
        if (empty($list)) {
            exit();
        }
        $role = $this->getUserRole();
        $isadmin = 1; //管理员
        $isNocop = 0; //现场工程师能处理
        $isSuspend = 0; //能释放挂起
        //如果是现场工程师且工单类型为人员进出的 他本身可以处理工单
        if ($role == self::ROLE_DC_EMPLOYEE && $list->ClassInficationOne == 'Equipment_personnel_2015' && $list->Status != 'Suspend' && $list->Status != 'Done') {
            $isNocop = 1;
        }
        if ($role == self::ROLE_DC_EMPLOYEE && $list->ClassInficationOne == 'Equipment_personnel_2015' && $list->Status == 'Suspend') {
            $isSuspend = 1;
        }
        if ($role == self::ROLE_DC_EMPLOYEE || $role == self::OTHER) {
            //数据中心工程师和其他人员为非管理员
            $isadmin = 0; //非管理员
        }
        $timelimit = TimedEvents::select("Parameter")->where(["name" => "工单回复撤销时间", "MarkDelete" => 0])->first();
        $timereply = (empty($timelimit->Parameter) ? 10 : $timelimit->Parameter) * 60; //回复时间限制,默认10分钟
        $controller = new Controller();
        $systemManage = $this->hasUserRule(self::ROLE_SYSTEM_MANAGER);
        if($systemManage){//若是系统管理员给予半小时内撤销权限
            $timereply = 30*60;
        }
        $list->Status = isset(PublicMethodsHelper::$supportStatusList[$list->Status])?PublicMethodsHelper::$supportStatusList[$list->Status]:'';
        $list->Body = str_replace('src="/usercenter', "src=\"".env('JOB_URL2')."/usercenter", $list->Body); //工单内容过滤
        $list->Body = strip_tags($list->Body, "<br><p><span><img><strong><em><u>"); //工单内容过滤
        $list->Body = preg_replace('/(style.*?)="(.*?)"/si', "", $list->Body); //工单内容过滤
        if (!empty($list->CreateUserId)) {
            $userinfo = Operation::replyUser($list->CreateUserId);
        }
        if (!empty($list->userId)) {
            $userName = Operation::replyUser($list->userId);
        }
        //用户信息
        if ($list->ContactId) {
            $contact = ResContact::select('Name', 'Email', 'Tel',
                'Mobile')->where(['id' => $list['ContactId']])->first();
        } //联系人信息
        if ($list->CustomerInfoId) {
            $customer = ResCusInf::selectRaw("CusName,Id,special,supportMemo")->where(['id' => $list->CustomerInfoId])->first();
        }
        if ($list->ClassInfication) {
            $secondclass = AuxDict::getSource("工单事件分类", "supporteventsort", $list->ClassInfication);
        } //二级分类
        $optlist = Operation::where('SupportId', $id)->whereIn('UCDis',
            [Operation::$ucdis['pending'], Operation::$ucdis['pass'], Operation::$ucdis['confirm']])->get(); //消息记录
        $hanguptask = SupportHangupTask::where("SupportId", $id)->orderBy("id", "desc")->get(); //挂起记录
        $remarks = Operation::select("ReplyUserId", "reply", "ReplyTs")
            ->where("UCDis", Operation::$ucdis['remark'])
            ->where("SupportId", $id)
            ->orderBy("id", "desc")
            ->get(); //备注记录

        foreach ($hanguptask as &$hangrow) {
            $noticeids = $hangrow->RemindStuff; //通知人员ids
            $remindusers = []; //提醒通知人员名字
            $types = $hangrow["RemindMode"]; //通知方式
            if (!empty($types)) {
                $types = explode(",", $types);
                $types = array_map(function ($i) {
                    return SupportHangupTask::$modename[SupportHangupTask::$mode["$i"]];
                }, $types);
                $types = implode(",", $types);
            }
            if (!empty($noticeids)) {
                $noticeids = explode(",", $noticeids);
                foreach ($noticeids as $nid) {
                    $remindusers[] = Operation::replyUser($nid);
                }
            }
            $hangrow->remindusers = implode(",", $remindusers);
            $hangrow->remindtypes = $types;
        }

        foreach ($optlist as &$opt) {
            if ((strtotime('now') - (strtotime($opt->ReplyTs))) / 60 - 8 < 0) {
                $opt->eight = 'on';
            } else {
                if ((strtotime('now') - (strtotime($opt->ReplyTs))) / 60 - 8 > 0) {
                    $opt->eight = 'off';
                }
                if ((strtotime('now') - (strtotime($opt->ReplyTs))) / 60 - 30 < 0
                    && $systemManage) {//系统管理员可在半小时以内撤回信息
                    $opt->eight = 'on';
                }
            }
            $opt->ReplyUser = Operation::replyUser($opt->ReplyUserId); //回复人
            if (!empty($opt->ReplyUser)) {
                $opt->ReplyUseri = mb_substr($opt->ReplyUser, -2, 2);
            }
            $opt->AuditUser = Operation::replyUser($opt->AuditUserId); //审核人
            $opt->Datacenter = ResUsers::select("UsersName")->where("id", $opt->DatacenterId)->first();
            $opt->Operation = AuxStuff::select("Name")->where("id", $opt->OperationId)->first();
            $opt->Datacenter2 = ResUsers::select("UsersName")->where("id", $opt->DatacenterTwoId)->first();
            $opt->Operation2 = AuxStuff::select("Name")->where("id", $opt->ChargeUserTwoId)->first();
            if (!empty($this->isgroup($opt->ReplyUserId, "L1"))) {
                $opt->grpl1 = 1; //L1组（非数据中心）
                continue;
            }
            if (!empty($this->isgroup($opt->ReplyUserId, "机房"))) {
                $opt->grpcenter = 1; //L1组（数据中心）
                continue;
            }
            if (!empty($this->isgroup($opt->ReplyUserId, "L0"))) {
                $opt->grpl0 = 1; //L0组
            }
            $opt->reply = str_replace('src="/usercenter', "src=\"".env('JOB_URL2')."/usercenter", $opt->reply); //工单回复内容过滤
        }

        $isedit = ($list->ChargeUserId) ? 1 : 0; //是否需要编辑（第一负责人）
        if (null == $list->Source || "" == $list->Source) {//来源为空加载默认值
            $list->Source = "oshelp";
        }
        $identity = ThirdCallHelper::getCusService($list->CustomerInfoId);//获取客户身份
        $arr = [
            'data'        => $list,
            'userinfo'    => $userinfo,
            'userName'    => $userName,
            'contact'     => $contact,
            'customer'    => $customer,
            'secondclass' => $secondclass,
            'optlist'     => $optlist,
            'isadmin'     => $isadmin,
            'isNocop'     => $isNocop,
            'isSuspend'   => $isSuspend,
            'isedit'      => $isedit,
            'toplist'     => $topList,
            'timereply'   => $timereply,
            'remarks'     => $remarks,
            "hanguptask"  => $hanguptask,
            "identity"    => $identity,
        ]; //绑定数据
        $userId = Request()->session()->get('user')->Id;
        $lastAppoint = Operation::where('SupportId', $id)->whereNotNull('DatacenterId')
            ->where('UCDis', 0)->orderBy('Id', 'desc')->first();
        $lastAppointId = $lastAppoint ? $lastAppoint->Id : false;
        $lastConfirm = Operation::where('SupportId', $id)
            ->where('UCDis', 5)->orderBy('Id', 'desc')->first();
        $lastConfirmId = $lastConfirm ? $lastConfirm->Id : false;
        $isConfirm = ($lastConfirmId && $lastConfirmId > $lastAppointId) ? true : false;
        $arr['userId'] = $userId;
        $arr['lastAppointId'] = $lastAppointId;
        $arr['isConfirm'] = $isConfirm;
        $arr['extension']=$this->getExtension($userId);
        if ($isedit) {
            $source = AuxDict::getSource("工单来源", "supportSource", $list->Source); //工单来源
            $class = AuxDict::getSource("工单类型", "WorksheetTypeOne", $list->ClassInficationOne); //三级分类
            $grp1 = ResUsers::select("UsersName")->where("id", "{$list->DatacenterId}")->first(); //第一工作组
            $usr1 = AuxStuff::select("Name")->where("id", "{$list->ChargeUserId}")->first(); //第一负责人
            $grp2 = ResUsers::select("UsersName")->where("id", "{$list->DatacenterTwoId}")->first(); //第二工作组
            $usr2 = AuxStuff::select("Name")->where("id", "{$list->ChargeUserTwoId}")->first(); //第二负责人
            $arr['source'] = $source;
            $arr['class'] = $class;
            $arr['grp1'] = $grp1;
            $arr['usr1'] = $usr1;
            $arr['grp2'] = $grp2;
            $arr['usr2'] = $usr2;
        }
        return view("supports/supportrefer", $arr);
    }

    /**
     * 获取数据中心
     */
    public function getDataCenter($type = "IDC")
    {
        return ThirdCallHelper::getDataCenter($type);
    }

    /**
     * 获取员工分机号
     */
    public function getExtension($id)
    {
        $ex = AuxStuff::select("extension")->where('Id',$id)->first()->toArray();
        return $ex;
    }

    /**
     * 根据用户id判断是否是管理员
     * @param type $rid 用户id
     * @return string
     */
    protected function isadmin($rid)
    {
        $id = intval($rid);
        if (!empty($id) && $id < 50000) {
            $user = AuxStuff::select("Login")->where("id", $id)->first()->toArray();
            if (empty($user) || empty($user['Login'])) {
                return false;
            }
            $authRoleList = Authorities::select("id")
                ->where('username', $user["Login"])
                ->whereIn("authority", [self::ROLE_BOSS, self::ROLE_DESK_MANAGER, self::ROLE_DESK])
                ->orderBy('id', 'desc')
                ->first();
            return $authRoleList;
        }
        return false;
    }

    /**
     * 工单挂起
     * @param type $id
     */
    public function hangUp($id)
    {

        $list = Support::select("id")->where(['id' => $id])->first(); //工单信息
        if (empty($list)) {
            exit();
        }
        return view("supports/hangup", ["sid" => $id]);
    }

    /**
     * 工单挂起数据提交
     * @param Request $request
     */
    public function postHangUp(Request $request)
    {
        try {
            $data = $request->all();
            $now = date("Y-m-d H:i:s");
            $id = intval($data['sid']);
            $user = PublicMethodsHelper::getUser();
            $userid = $user->Id;
            $isremind = intval($data['isremind']);
            $support = Support::select("Status")->where("id", $id)->first();
            if ($support->Status == "Closed") {
                return array('status' => 'closed', 'msg' => "此工单状态已更新，请刷新页面重试！");
            }
            $uData = [
                'UpTs'        => $now,
                'OperationId' => $userid,
                'hangupTs'    => $now,
                'Status'      => 'Suspend'//挂起
            ]; //工单修改
            $hangupData = [
                "SupportId"  => $id,
                "HangupText" => $data['explain'],
                "Remind"     => $isremind,
                "Ts"         => $now,
                "UserId"     => $userid,
                "UpUserId"   => $userid,
                "State"      => SupportHangupTask::$state['start'] //0释放挂起，1挂起
            ]; //挂起记录
            if ($isremind == "1") {//需要提醒
                $hangupData['RemindMode'] = implode(",", $data['rmode']);
                $hangupData['ContinuityRemind'] = $data['conRemind'];
                $hangupData['RemindTs'] = $data['remindTime'];
                $hangupData['RemindStuff'] = $data['uids'];
            }
            $optData = [
                'reply'       => '已挂起',
                'ReplyTs'     => $now,
                'ReplyID'     => $id,
                'ReplyUserID' => $userid,
                'SupportId'   => $id,
                'UCDis'       => Operation::$ucdis['pending']
            ]; //操作记录
            //事务处理
            DB::transaction(function () use ($id, $uData, $hangupData, $optData) {
                $supRst = Support::where("id", $id)->update($uData); //工单状态修改
                $hangRst = SupportHangupTask::insertGetId($hangupData); //添加挂起记录
                $optRst = Operation::insertGetId($optData); //添加操作记录
            });
        } catch (\Exception $ex) {
            return array('status' => false, 'msg' => $ex);
        }
        return array('status' => true, 'msg' => "挂起成功");
    }

    /**
     *  释放工单
     * @param type $id 工单id
     */
    public function postRelease($id)
    {
        try {
            $sid = intval($id);
            $time = time();
            $now = date("Y-m-d H:i:s");
            $user = PublicMethodsHelper::getUser();
            $userid = $user->Id;
            $support = Support::select("hangupTs", "hangupDuration", "Status")->where("id", $sid)->first();
            if (empty($support) || empty($support->hangupTs)) {
                return array('status' => false, 'msg' => "操作失败");
            }
            if ($support->Status == "Closed") {
                return array('status' => 'closed', 'msg' => "此工单状态已更新，请刷新页面重试！");
            }
            $hangupTime = $support->hangupTs;
            $hangupDuration = $time - strtotime($hangupTime); //挂起时长（秒）
            $uData = [
                'UpTs'        => $now,
                'OperationId' => $userid,
                'Status'      => 'Doing'
            ]; //工单表修改
            $uHangupData = [
                'State'    => SupportHangupTask::$state['end'],
                'UpTs'     => $now,
                'UpUserId' => $userid,
            ]; //挂起记录修改
            $optData = [
                'reply'       => '挂起已释放',
                'ReplyTs'     => $now,
                'ReplyID'     => $id,
                'ReplyUserID' => $userid,
                'SupportId'   => $id,
                'UCDis'       => Operation::$ucdis['pending']
            ]; //操作记录
            DB::transaction(function () use ($support, $uData, $hangupDuration, $sid, $uHangupData, $optData) {
                if (empty($support->hangupDuration)) {
                    $uData['hangupDuration'] = $hangupDuration;
                    $sptRst = Support::where(["id" => $sid])->update($uData);
                } else {
                    $sptRst = Support::where(["id" => $sid])->increment("hangupDuration", $hangupDuration,
                        $uData); //修改工单表
                }
                $hangRst = SupportHangupTask::where([
                    "SupportId" => $sid,
                    "State"     => SupportHangupTask::$state['start']
                ])->update($uHangupData); //修改挂起记录
                $optRst = Operation::insertGetId($optData); //操作记录表修改
            });
        } catch (\Exception $ex) {
            return array('status' => false, 'msg' => "挂起释放失败");
        }
        return array('status' => true, 'msg' => "挂起释放成功");
    }

    /**
     * 工单修改
     * @param type $id
     */
    public function editSupport($id)
    {
        $list = Support::where(['id' => $id])->first(); //工单信息
        $source = AuxDict::getSource("工单来源", "supportSource", $list->Source); //工单来源
        $class = AuxDict::getSource("工单类型", "WorksheetTypeOne", $list->ClassInficationOne); //三级分类
        //来源为空加载默认值
        if (null == $list->Source || "" == $list->Source) {
            $list->Source = "oshelp";
        }
        // VIP客户和待管字的客户提交的工单默认优先级设为2，其余客户默认设为3
        if (null == $list->priority || $list->priority == 0) {
            // 级别为空加载客户标签
            $optlist = array();
            Array_push($optlist, $list);
            $this->findSupport($optlist);

            $cusinf = ResCusInf::where('id', $list->CustomerInfoId)->first();
            if ("KeyCustomers" == $cusinf->CusImportanceType || $list->CusSuppMan) {
                $list->priority = 2;
            } else {
                $list->priority = 3;
            }
        }

        $arr['source'] = $source;
        $arr['class'] = $class;
        $arr['data'] = $list;
        return view("supports/editsupport", $arr);
    }

    /**
     * 工单相关数据修改
     * @param Request $request
     */
    public function postMainSupport(Request $request)
    {
        try {
            $data = $request->all();
            $now = date("Y-m-d H:i:s");
            $id = intval($data['sid']); //工单id
            $user = PublicMethodsHelper::getUser();
            $userid = $user->Id;
            $rstbz = true; //添加回复记录状态
            $udata = [
                "Source"             => $data['usource'],
                "priority"           => $data['sorts'],
                "ServiceModel"       => trim($data['model']),
                "ClassInficationOne" => $data['thirdclass'],
                "dataCenter"         => $data['datacenter'],
                "Memo"               => $data['remark'],
                "OperationId"       => $userid,
                "UpTs"               => $now,
            ];

            if(isset($data['supportTag'])){
                $udata["supportTag"] =  $data['supportTag']==""?null:$data['supportTag'];
            }

            $support = Support::where("Id",$id)->first();
            $reply = "";
            if($support){
                if($support->Source && $support->Source != $data['usource']){
                    $reply .= '工单来源由'.Support::explainDict($support->Source,"supportSource").
                        '修改为'.Support::explainDict($data['usource'],"supportSource").'；';
                }
                if($support->priority && $support->priority != $data['sorts']){
                    $reply .= '工单优先级由'.$support->priority. '修改为'.$data['sorts'].'；';
                }
                if($support->ServiceModel && $support->ServiceModel != $data['model']){
                    $reply .= '工单一级分类由'.Support::explainDict($support->ServiceModel,"serviceModel").
                        '修改为'.Support::explainDict($data['model'],"serviceModel").'；';
                }
                if($support->ClassInficationOne && $support->ClassInficationOne != $data['thirdclass']){
                    $reply .= '工单三级分类由'.Support::explainDict($support->ClassInficationOne,"WorkSheetTypeOne").
                        '修改为'.Support::explainDict($data['thirdclass'],"WorkSheetTypeOne").'；';
                }
                if($support->dataCenter && $support->dataCenter != $data['datacenter']){
                    $reply .= '工单数据中心由'.$support->dataCenter. '修改为'.$data['datacenter'].'；';
                }
            }

            if (!empty($data['thirdclass'])) {
                $sclass = AuxDict::select("ParentCode","Eng")->whereRaw("Code='{$data['thirdclass']}' and DomainCode='WorksheetTypeOne' and (Validate is null or Validate = 0)")->first();
                $udata['ClassInfication'] = ($sclass && $sclass->ParentCode) ? $sclass->ParentCode : ""; //添加二级分类
                $udata['initHandleduration'] = ($sclass && $sclass->Eng) ? $sclass->Eng * 60 : ""; //添加参考时长
            }
            DB::transaction(function () use ($id, $udata, $data, $now, $userid,$reply,$support) {
                $rsts = Support::where(['id' => $id])->update($udata); //工单表修改
                if (!empty($data['remark'] && $support->Memo != $data['remark'] )) {//若有备注内容添加入回复记录表
                    if($reply != ""){
                        $data['remark'] .= "。<br/>编辑保存了工单基本信息：".$reply;
                    }
                    $rstbz = Operation::insertGetId([
                        'reply'       => "添加了备注内容为:".$data['remark'],
                        'ReplyTs'     => $now,
                        'ReplyID'     => $id,
                        'ReplyUserId' => $userid,
                        'SupportId'   => $id,
                        'UCDis'       => Operation::$ucdis['remark']//备注记录
                    ]);
                } else if($reply != ""){
                    $rstbz = Operation::insertGetId([
                        'reply'       => "编辑保存了工单基本信息:".$reply,
                        'ReplyTs'     => $now,
                        'ReplyID'     => $id,
                        'ReplyUserId' => $userid,
                        'SupportId'   => $id,
                        'UCDis'       => Operation::$ucdis['remark']//备注记录
                    ]);
                }
            });
        } catch (\Exception $ex) {
            return array('status' => false, 'msg' => "修改失败");
        }
        return array('status' => true, 'msg' => "修改成功");
    }

    /**
     * 查找对应工作组操作人
     * @param type $id 工作组id
     */
    public function optUsers($id)
    {
        $list = ThirdCallHelper::getGroupMembers($id)->toArray();
        $list = array_map(function ($arr) {
            $cachekey = ITSM_LOGIN . $arr["UserId"];
            $arr["Name"] = $arr["Name"] . "(" . (Cache::has($cachekey) ? "在线" : "离线") . ")";
            return $arr;
        }, $list);
        return $list;
    }

    /**
     * 指派工单
     * @param Request $request
     */
    public function postSupport(Request $request)
    {
        try {
            $now = date("Y-m-d H:i:s");
            $data = $request->all();
            $id = intval($data['sid']); //工单id
            $user = PublicMethodsHelper::getUser();
            $userid = $user->Id;
            $opt = Support::select("Status", "FirstReplyTs")->where(['id' => $id])->first();
            if (empty($opt)) {
                exit();
            }
            $status = $opt->Status;
            $first_reply_ts = $opt->FirstReplyTs;
            if (($status == 'Todo') || ($status == 'ReAppoint')) {//工单状态为待处理或者待指派
                $status = "Appointed";
            } //已指派
            elseif ($status == 'Appointed') {
                return array('status' => 'appointed', 'msg' => "此工单已被指派，请刷新页面重试！");
            } elseif ($status == 'Closed') {
                $status = "Closed";
            }
            if (empty($first_reply_ts)) {
                $first_reply_ts = $now;
            }
            $optgroup = ResUsers::select("chargeGroup")->where("id", "{$data['group1']}")->first()->chargeGroup; //工单负责组
            $chargeGroup = AuxStuff::select("chargeGroup")->where("id",$userid)->first();
            $chargeGroup = !empty($chargeGroup)&&!empty($chargeGroup->chargeGroup)?$chargeGroup->chargeGroup:"";
            $udata = [
                "Source"             => $data['usource'],
                "priority"           => $data['sorts'],
                "ServiceModel"       => trim($data['model']),
                "ClassInficationOne" => $data['thirdclass'],
                "DatacenterId"       => $data['group1'],
                "ChargeUserId"       => $data['optuser1'],
                "DatacenterTwoId"    => $data['group2'],
                "ChargeUserTwoId"    => $data['optuser2'],
                "Memo"               => $data['remark'],
                "AsuserId"           => $userid,
                "OperationId"        => $userid,
                "Status"             => $status,
                "SpTs"               => $now,
                "UpTs"               => $now,
                "FirstReplyTs"       => $first_reply_ts,
                "SuppOptGroup"       => $optgroup,
                "chargeGroup"        => $chargeGroup,
                "dataCenter"         => $data['datacenter'], //数据中心未指派可以选择
            ];
            if (!empty($data['thirdclass'])) {
                $sclass = AuxDict::select("ParentCode","Eng")->whereRaw("Code='{$data['thirdclass']}' and DomainCode='WorksheetTypeOne' and (Validate is null or Validate = 0)")->first();
                $udata['ClassInfication'] = ($sclass && $sclass->ParentCode) ? $sclass->ParentCode : ""; //添加二级分类
                $udata['initHandleduration'] = ($sclass && $sclass->Eng) ? $sclass->Eng * 60 : ""; //添加参考时长
            }

            if(!empty($data['supportTag']))$udata['supportTag']=$data['supportTag'];

            DB::transaction(function () use ($id, $udata, $data, $userid, $now) {
                $rsts = Support::where(['id' => $id])->update($udata); //工单表修改
                $rstbz = true; //添加回复记录状态
                $rstcenter = true; //指派数据中心组添加操作记录

                $msgstr = $this->addCenter($data);
                if ($msgstr != '') {
                    $centerdata = [
                        'SupportId'   => $id,
                        'ReplyUserID' => $userid,
                        'reply'       => "{$msgstr}",
                        'ReplyTs'     => $now,
                        'UCDis'       => Operation::$ucdis['pass']
                    ];
                    $rstcenter = Operation::insertGetId($centerdata); //添加操作记录
                }

                if (!empty($data['remark'])) {
                    $rstbz = Operation::insertGetId([
                        'reply'       => $data['remark'],
                        'ReplyTs'     => $now,
                        'ReplyID'     => $id,
                        'ReplyUserId' => $userid,
                        'SupportId'   => $id,
                        'UCDis'       => Operation::$ucdis['remark']//备注记录
                    ]);
                } //若有备注内容添加入回复记录表
                $odata = [
                    'reply'           => '已指派',
                    'ReplyTs'         => $now,
                    'ReplyId'         => $id,
                    'ReplyUserID'     => $userid,
                    'SupportId'       => $id,
                    'UCDis'           => 0,
                    'OperationId'     => $data['optuser1'],
                    'DatacenterId'    => $data['group1'],
                    'DatacenterTwoId' => $data['group2'],
                    'ChargeUserTwoId' => $data['optuser2']
                ];
                $rsto = Operation::insertGetId($odata); //添加操作记录

                $job = new SpeedAnswer($id, $rsto, $userid, "Appointed"); //创建队列任务（指派操作）
                $this->dispatch($job); //添加到队列
            });
        } catch (\Exception $ex) {
            return array('status' => false, 'msg' => $ex);
        }
        return array('status' => true, 'msg' => "指派成功");
    }

    /**
     *  判断组是否是数据中心组
     * @param type $gid 组id
     */
    protected function isCenter($gid)
    {
        if (empty($gid)) {
            return false;
        }
        $resUsers = ResUsers::select("UsersName")->where("id", $gid)->first();
        return $resUsers && strstr($resUsers->UsersName, '数据中心');
    }

    /**
     *   指派数据中心消息
     * @param type $data
     * @return type
     */
    protected function addCenter($data)
    {
        $msgstr = ""; //指派信息
        if ($this->isCenter($data['group1']) || $this->isCenter($data['group2'])) {
            //如果指派的组为数据中心组则添加记录
            $datacenter = ResUsers::select("UsersName")->where("id", $data['group1'])->first();
            $operation = AuxStuff::select("Name")->where("id", $data['optuser1'])->first();
            $repstr = $datacenter->UsersName . $operation->Name;
            if (!empty($data['group2'])) {
                $datacenter2 = ResUsers::select("UsersName")->where("id", $data['group2'])->first();
                $operation2 = AuxStuff::select("Name")->where("id", $data['optuser2'])->first();
                $repstr .= "," . $datacenter2->UsersName . $operation2->Name;
            }
            $msgstr = sprintf("您提交的请求需要现场工程师操作，已指派%s为您操作。", $repstr);
        } else {
            $datacenter = ResUsers::select("UsersName")->where("id", $data['group1'])->first();
            $operation = AuxStuff::select("Name")->where("id", $data['optuser1'])->first();
            $repstr = $datacenter->UsersName . $operation->Name;
            if (!empty($data['group2'])) {
                $datacenter2 = ResUsers::select("UsersName")->where("id", $data['group2'])->first();
                $operation2 = AuxStuff::select("Name")->where("id", $data['optuser2'])->first();
                $repstr .= "," . $datacenter2->UsersName . $operation2->Name;
            }
            $msgstr = sprintf("您提交的请求我们已受理，已安排%s为您处理，请稍候。", $repstr);
        }
        return $msgstr;
    }

    /**
     *  回复消息
     * @param Request $request
     * @return type
     */
    public function replyMsg(Request $request)
    {
        try {
            $data = $request->all();
            if($data['replyMark'] == "first"){
                $lastOperation = Operation::where("SupportId",$data['sid'])->orderBy("Id","desc")->first();
                if(!empty($lastOperation) && "当前问题已解决" == trim($lastOperation->reply)){
                    $operater = ThirdCallHelper::getStuffName($lastOperation->ReplyUserId);
                    $rmsg = "此工单已于".$lastOperation->ReplyTs."由".$operater."设为了已处理！继续回复将修改工单为处理中状态！";
                    return array('status' => 'confirm', 'msg' => $rmsg);
                }
            }
            $role = $this->getUserRole();
            $ucd = Operation::$ucdis['pass']; //不需要审核（审核不通过为2）
            $id = $data['sid']; //工单id
            if ($role == 'noc_op' || $role == 'other') {
                //数据中心工程师和其他人员需要审核
                $ucd = Operation::$ucdis['pending']; //需要审核
            }
            $msg = $data['msg'];
            $user = PublicMethodsHelper::getUser();
            $userid = $user->Id;
            if (get_magic_quotes_gpc()) {
                $msg = stripslashes($msg);
            }
            $now = date('Y-m-d H:i:s');
            $odata = [
                'reply'       => $msg,
                'ReplyTs'     => $now,
                'ReplyUserID' => $userid,
                'SupportId'   => $id,
                'UCDis'       => $ucd,
                'Source'       => 'ITSM'
            ];
            $opt = Support::select("Status")->where(['id' => $id])->first();
            if ($opt->Status == 'Closed') {
                return array('status' => 'closed', 'msg' => "此工单状态已更新，请刷新页面重试！");
            }
            DB::transaction(function () use ($odata, $id, $now, $userid) {
                $ret = Operation::insertGetId($odata);
                $opt = Support::select("Status")->where(['id' => $id])->first();
                $ustatus = true;
                if ($opt->Status == 'Appointed' || $opt->Status == 'Done') {// 回复 工单状态为已指派或已处理 状态改为处理中
                    $udata = [
                        'UpTs'        => $now,
                        'OperationId' => $userid,
                        'Status'      => 'Doing'
                    ];
                    $ustatus = Support::where(['id' => $id])->update($udata); //工单表修改
                    if (!empty($ucd)) {//服务台以上角色需要发消息推送
                        $job = new SpeedAnswer($id, $ret, $userid, "Doing"); //创建队列任务（服务台工程师回复）
                        $this->dispatch($job); //添加到队列
                    }
                } else {
                    $udata = [
                        'UpTs'        => $now,
                        'OperationId' => $userid,
                    ];
                    $ustatus = Support::where(['id' => $id])->update($udata); //工单表修改
                }
            });
        } catch (\Exception $ex) {
            return array('status' => false, 'msg' => "回复失败");
        }
        return array('status' => true, 'msg' => "回复成功");
    }

    /**
     * 重新指派
     * @param type $id
     */
    public function reassign($id)
    {
        $list = Support::selectRaw("id,DatacenterId,ChargeUserId,DatacenterTwoId,ChargeUserTwoId")->where(['id' => $id])->first(); //工单信息
        //dd($list);
        return view("supports/reassign", ["data" => $list]);
    }

    /**
     * 重新指派前先拿到上一次指派时间，重复指派时间若在指定限制时间内则给出confirm确认
     * 限制时间在env由REAPPOINTTS配置，默认2分钟
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|mixed|null|static
     */
    public function getSpTs($id)
    {
        $sup = Support::selectRaw("SpTs,AsuserId,ChargeUserId")->where(['Id' => $id])->first(); //工单信息
        $sup->Asuser = ThirdCallHelper::getStuffName($sup->AsuserId);
        $sup->charger = ThirdCallHelper::getStuffName($sup->ChargeUserId);
        $sup->alarmTs = env("REAPPOINTTS") ? env("REAPPOINTTS") : 2;
        return $sup;
    }

    /**
     * 重新指派回传
     * @param Request $request
     */
    public function postReassign(Request $request)
    {
        try {
            $data = $request->all();
            $id = $data['sid']; //工单id
            $user = PublicMethodsHelper::getUser();
            $userid = $user->Id;
            $now = date('Y-m-d H:i:s');
            $opt = Support::select("Status", "FirstReplyTs")->where(['id' => $id])->first();
            if (empty($opt)) {
                exit();
            }
            $status = $opt->Status;
            $first_reply_ts = $opt->FirstReplyTs;
            if (($status == 'Todo') || ($status == 'ReAppoint')) {//工单状态为待处理或者待指派
                $status = "Appointed";
            }
            if ($status == 'Closed') {//工单状态为已关闭的，状态不变
                $status = "Closed";
            } //已指派
            if (empty($first_reply_ts)) {
                $first_reply_ts = $now;
            }
            $optgroup = ResUsers::select("chargeGroup")->where("id", "{$data['group1']}")->first()->chargeGroup; //工单负责组
            $rstcenter = true; //指派数据中心组添加操作记录
            $udata = [
                "DatacenterId"    => $data['group1'],
                "ChargeUserId"    => $data['optuser1'],
                "DatacenterTwoId" => $data['group2'],
                "ChargeUserTwoId" => $data['optuser2'],
                "AsuserId"        => $userid,
                "OperationId"     => $userid,
                "Status"          => $status,
                "SpTs"            => $now,
                "UpTs"            => $now,
                "FirstReplyTs"    => $first_reply_ts,
                "SuppOptGroup"    => $optgroup,
                "chargeGroup"     => $optgroup
            ];

            $odata = [
                'reply'       => '此工单已被重新指派',
                'ReplyTs'     => $now,
                'ReplyID'     => $id,
                'ReplyUserID' => $userid,
                'SupportId'   => $id,
                'UCDis'       => Operation::$ucdis['pending']
            ];
            $odata2 = [
                'reply'           => '已指派',
                'ReplyTs'         => $now,
                'ReplyID'         => $id,
                'ReplyUserID'     => $userid,
                'SupportId'       => $id,
                'UCDis'           => Operation::$ucdis['pending'],
                'OperationId'     => $data['optuser1'],
                'DatacenterId'    => $data['group1'],
                'DatacenterTwoId' => $data['group2'],
                'ChargeUserTwoId' => $data['optuser2']
            ];
            $ret = DB::transaction(function () use ($id, $udata, $data, $odata, $userid, $now, $odata2) {
                $rsts = Support::where(['id' => $id])->update($udata); //工单表修改
                if ($rsts) {
                    $msgstr = $this->addCenter($data);
                    $rsto1 = Operation::insertGetId($odata); //添加重新指派记录
                    if ($msgstr != '') {
                        $centerdata = [
                            'SupportId'   => $id,
                            'ReplyUserID' => $userid,
                            'reply'       => "{$msgstr}",
                            'ReplyTs'     => $now,
                            'UCDis'       => Operation::$ucdis['pass']
                        ];
                        $rstcenter = Operation::insertGetId($centerdata); //添加操作记录
                    }
                    $rsto2 = Operation::insertGetId($odata2); //添加已指派记录
                    if ($rsto1 && $rsto2) {
                        return true;
                    }
                } else {
                    return false;
                }
            });
            if (!$ret) {
                return array('status' => false, 'msg' => "重新指派失败");
            }
        } catch (\Exception $ex) {
            return array('status' => false, 'msg' => "重新指派失败");
        }
        return array('status' => true, 'msg' => "重新指派成功");
    }

    /**
     * 发送短信
     */
    public function sendSms($id)
    {
        $id = intval($id);
        $list = Support::where(['id' => $id])->first(); //工单信息
        if (empty($list)) {
            exit();
        }
        if ($list->mobile) {
            $contact = [
                "Name"=>$list->contactName,
                "Mobile"=>$list->mobile,
                "Email"=>$list->email,
                "Tel"=>""
            ];
            $contact = (Object) $contact;//新平台联系人信息存在工单中未存联系人Id
        }elseif($list->ContactId){
            $contact = ResContact::select('Name', 'Mobile')->where(['id' => $list->ContactId])->first();
        } //联系人信息
        return view("supports/sendsms", ["contact" => $contact, "sid" => $id,"list" =>$list]);
    }

    /**
     * 发送邮件
     */
    public function sendEmail()
    {
        $email = Input::get("email");
        return view("supports/sendEmail", ["email" => $email]);
    }

    /**
     *  短信发送队列
     * @param Request $request
     * @return type
     */
    public function postSms(Request $request)
    {
        $data = $request->all();
        $id = $data['sid']; //工单id
        $list = Support::where(['id' => $id])->first(); //工单信息
        if (empty($list)) {
            return array('status' => false, 'msg' => "工单不存在");

        }
        if (isset($list->ContactId)) {
            $contact = ResContact::select('Mobile')->where(['id' => $list['ContactId']])->first();
        } //联系人信息
        if(isset($list->mobile) &&  !empty($list->mobile)){
            $mobile = $list->mobile;
        }elseif(isset($contact) && isset($contact->Mobile) && !empty($contact->Mobile)){
            $mobile = $contact->Mobile;
        }else{
            return array('status' => false, 'msg' => "联系手机不存在");
        }

        if (!isset($mobile)) {
            return array('status' => false, 'msg' => "联系手机不存在");
        }

        //若手机号前添加国际号码，去除+86 或86-
        $mobile = PublicMethodsHelper::checkMobile($mobile);
        $count = preg_match("/^1(3|4|5|7|8)\\d{9}$/",$mobile);
        if($count ==0 ){
            return array('status' => false, 'msg' => "联系手机不存在或非国内手机号暂不支持");
        }

        $smscontent = trim($data['msg']);
        $smscontent = ThirdCallHelper::myTrimOnlyNRT($smscontent);//替换所有空白字符
        if (empty($smscontent)) {
            return array('status' => false, 'msg' => "短消息不能为空");
        }
        $job = new SendSms($mobile, $smscontent); //创建队列任务
        $this->dispatch($job); //添加到队列
        return array('status' => true, 'msg' => "短信发送成功");
    }

    /**
     *  邮件发送队列
     * @param Request $request
     * @return array
     */
    public function postEmail(Request $request)
    {
        $data = $request->all();
        $emailTitle = $data['emailTitle']; //邮件标题
        $email = $data['email']; //邮箱地址
        $emailContent = trim($data['msg']);
        $emailContent = ThirdCallHelper::myTrimOnlyNRT($emailContent);//替换所有空白字符
        if (empty($emailContent) || empty($emailTitle)) {
            return array('status' => false, 'msg' => "邮件标题或内容不能为空!");
        }
        $job = new SendEmail($email,$emailTitle, $emailContent); //创建队列任务
        $this->dispatch($job); //添加到队列
        return array('status' => true, 'msg' => "邮件发送成功");
    }


    public function onCall(){
        $url = env("CALL_URL")."/callengine/http/operation";
        $json = Input::get("json");
        $url .= "?json=".$json;
        $client = new Client();
        $ret = "";
        try{
            $ret = $client->get($url)->getReasonPhrase();
        }catch(\Exception $ex){
            return ["status"=>"error"];
        }
        return ["status"=>$ret] ;
    }

    /**
     * 已处理（含短信队列）
     */
    public function alreadyProc($id)
    {
        $sid = intval($id);
        $list = Support::where(['id' => $sid])->first(); //工单信息
        if (empty($list)) {
            exit();
        }
        $contact = [
            "Name"=>$list->contactName,
            "Mobile"=>$list->mobile,
            "Email"=>$list->email,
            "Tel"=>""
        ];
        $contact = (Object) $contact;
        if ($list->ContactId) {
            $contact = ResContact::select('Mobile')->where(['id' => $list->ContactId])->first();
        } //联系人信息
        $list = Operation::select("reply")->whereRaw("SupportId={$sid} and ReplyId is null")->orderByRaw('id desc')->first(); //操作信息
        if (!empty($list->reply)) {
            $list->reply = strip_tags($list->reply);
        }
        return view("supports/alreadyproc", ["data" => $list, "contact" => $contact, "sid" => $sid]);
    }

    /**
     * 确认已处理
     */
    public function sureProc(Request $request)
    {
        try {
            $data = $request->all();
            $user = PublicMethodsHelper::getUser();
            $userid = $user->Id;
            $id = $data['sid']; //工单id
            $support = Support::select("Status", "ContactId", 'ClassInficationOne', 'ClassInfication','mobile')->where('Id',
                $id)->first();
            if (empty($support)) {
                return array('status' => false, 'msg' => "工单不存在");
            }
            if (isset($support->ContactId) && !empty($support->ContactId) ) {
                $contact = ResContact::select('Mobile')->where(['id' => $support->ContactId])->first();
                if(!empty($contact) && !empty($contact->Mobile)){
                    $mobile = $contact->Mobile;
                }else if(!empty($support->mobile)){
                    $mobile = $support->mobile;
                }
            }else if(!empty($support->mobile)){
                $mobile = $support->mobile;
            } //联系人信息
            $now = date('Y-m-d H:i:s');

            $smscontent = trim($data['msg']); //短消息内容
            $smscontent = ThirdCallHelper::myTrimOnlyNRT($smscontent);
            if (!empty($smscontent) && !empty($data['sureSendMsg'])) {
                if (empty($mobile)) {
                    return array('status' => false, 'msg' => "联系手机不存在");
                }
                //若手机号前添加国际号码，去除+86 或86-
                $mobile = PublicMethodsHelper::checkMobile($mobile);
                $count = preg_match("/^1(3|4|5|7|8|9)\\d{9}$/",$mobile);
                if($count ==0 ){
                    return array('status' => false, 'msg' => "联系手机不存在或非国内手机号暂不支持");
                }
                $job = new SendSms($mobile, $smscontent); //创建队列任务
                $this->dispatch($job); //添加到队列
            }
            //工单 人员进出不校验状态
            if (($support->Status == 'Closed' || $support->Status == 'Suspend') && $support->ClassInficationOne != 'Equipment_personnel_2015') {//不能是关闭状态和挂起状态
                return array('status' => false, 'msg' => "处理失败");
            }
            $udata = [
                'Status'      => 'Done',
                'UpTs'        => $now,
                'ProcessTs'   => $now,
                'OperationId' => $userid
            ]; //工单表修改
            $idata = [
                'reply'       => '当前问题已解决',
                'ReplyTs'     => $now,
                'ReplyUserID' => $userid,
                'ReplyID'     => $id,
                'SupportId'   => $id,
                'UCDis'       => Operation::$ucdis['pass']
            ]; //工单操作表修改
            $rsts = Support::where('Id', $id)->update($udata); //工单状态修改
            $rsto = Operation::insertGetId($idata);

            $job = new SpeedAnswer($id, $rsto, $userid, "Done"); //创建队列任务（已处理操作）
            $this->dispatch($job); //添加到队列
        } catch (\Exception $ex) {
            return array('status' => false, 'msg' => "操作失败");
        }
        return array('status' => true, 'msg' => "操作成功");
    }

    /**
     * 确认回复
     */
    public function sureReply($id)
    {
        try {
            $oid = intval($id);
            $user = PublicMethodsHelper::getUser();
            $userid = $user->Id;
            $now = date('Y-m-d H:i:s');
            $udata = [
                'UCDis'       => Operation::$ucdis['pass'],
                'AuditUserId' => $userid,
                'AuditTs'     => $now
            ];//操作记录表数据
            $sid = Operation::select("SupportId")->where(['id' => $oid])->first();
            $sid = $sid->SupportId;
            $rst = Operation::where(['id' => $oid])->update($udata);//操作记录表修改
            $udata = [
                'UpTs'        => $now,
                'OperationId' => $userid,
            ];//工单表数据
            $ustatus = Support::where(['id' => $sid])->update($udata); //工单表修改
            $job = new SpeedAnswer($sid, $oid, $userid, "Doing"); //创建队列任务（服务台工程师回复）
            $this->dispatch($job); //添加到队列
        } catch (\Exception $ex) {
            return array('status' => false, 'msg' => "回复失败");
        }
        return array('status' => true, 'msg' => "回复成功");
    }

    /**
     * 删除回复
     */
    public function delReply($id)
    {
        try {
            $oid = intval($id);
            $user = PublicMethodsHelper::getUser();
            $userid = $user->Id;
            $now = date('Y-m-d H:i:s');
            $udata = [
                'UCDis'       => Operation::$ucdis['delete'],
                'AuditUserId' => $userid,
                'AuditTs'     => $now
            ];
            $rst = Operation::where(['id' => $oid])->update($udata);
            $operation = Operation::where(['id' => $oid])->first();
            if(!empty($operation)){
                $id = $operation->SupportId; //工单id
                $msg = "审核 删除回复操作";
                $user = PublicMethodsHelper::getUser();
                $userid = $user->Id;
                if (get_magic_quotes_gpc()) {
                    $msg = stripslashes($msg);
                }
                $now = date('Y-m-d H:i:s');
                $odata = [
                    'reply'       => $msg,
                    'ReplyTs'     => $now,
                    'ReplyUserID' => $userid,
                    'SupportId'   => $id,
                    'UCDis'       => 2,
                    'Source'       => 'ITSM'
                ];
                $ret = Operation::insertGetId($odata);
                $opt = Support::select("Status")->where(['id' => $id])->first();
                $ustatus = true;
                $udata = [
                    'UpTs'        => $now,
                    'OperationId' => $userid,
                ];
                Support::where(['id' => $id])->update($udata); //工单表修改
            }
        } catch (\Exception $ex) {
            return array('status' => false, 'msg' => "删除回复失败");
        }
        return array('status' => true, 'msg' => "删除回复成功");
    }

    /**
     * 被指派工作人员确认接收指派
     * @param $id
     * @param Request $request
     * @return array
     */
    public function sureAppoint($id, Request $request)
    {
        try {
            $ucd = Operation::$ucdis['confirm']; //不需要审核（审核不通过为2）
            $user = PublicMethodsHelper::getUser();
            $userid = $user->Id;
            $now = date('Y-m-d H:i:s');
            $odata = [
                'reply'       => '已确认接受指派',
                'ReplyTs'     => $now,
                'ReplyUserID' => $userid,
                'SupportId'   => $id,
                'UCDis'       => $ucd
            ];
            DB::transaction(function () use ($odata, $id, $now, $userid) {
                $ret = Operation::insertGetId($odata);
                $support = Support::select('Status')->where(['id' => $id])->first();
                if ($support->Status == 'Closed') {
                    $udata = [
                        'UpTs'        => $now,
                        'OperationId' => $userid,
                        'Status'      => 'Closed'
                    ];
                }//工单表数据
                else {
                    $udata = [
                        'UpTs'        => $now,
                        'OperationId' => $userid,
                        'Status'      => 'Doing'
                    ];
                }
                $ustatus = Support::where(['id' => $id])->update($udata); //工单表修改
            });
        } catch (\Exception $ex) {
            return array('status' => false, 'msg' => $ex->getMessage());
        }
        return array('status' => true, 'msg' => "确认接受指派成功!");
    }

    /**
     * 回复内容编辑
     * @param type $id
     * @return type
     */
    public function editReply($id)
    {
        $oid = intval($id);
        $list = Operation::selectRaw("id,reply")->where(['id' => $oid])->first(); //操作信息
        return view("supports/editreply", ["data" => $list]);
    }

    /**
     *  回复内容审核
     * @param Request $request
     */
    public function postEditReply(Request $request)
    {
        try {
            $data = $request->all();
            $msg = $data['msg'];
            $oid = intval($data['oid']);
            $user = PublicMethodsHelper::getUser();
            $userid = $user->Id;
            $now = date('Y-m-d H:i:s');
            if (get_magic_quotes_gpc()) {
                $msg = stripslashes($msg);
            }
            $udata = [
                'UCDis'       => Operation::$ucdis['pass'],
                'Reply'       => "{$msg}",
                'AuditUserId' => $userid,
                'AuditTs'     => $now
            ];
            $sid = Operation::select("SupportId")->where(['id' => $oid])->first();
            $sid = $sid->SupportId;//工单id
            $rst = Operation::where(['id' => $oid])->update($udata);
            $udata = [
                'UpTs'        => $now,
                'OperationId' => $userid,
            ];//工单表数据
            $ustatus = Support::where(['id' => $sid])->update($udata); //工单表修改
            $job = new SpeedAnswer($sid, $oid, $userid, "Doing"); //创建队列任务（服务台工程师回复）
            $this->dispatch($job); //添加到队列
        } catch (\Exception $ex) {
            return array('status' => false, 'msg' => "回复失败");
        }
        return array('status' => true, 'msg' => "回复成功");
    }

    /**
     * 撤销回复
     * @param type $id 操作记录id
     */
    public function postRescind($id)
    {
        try {
            $oid = intval($id);
            $user = PublicMethodsHelper::getUser();
            $userid = $user->Id;
            $now = date('Y-m-d H:i:s');
            $operation = Operation::select("id","SupportId")
                ->whereRaw("id={$oid} and (ReplyUserId={$userid} or AuditUserId={$userid}) and ReplyId is null")->first();
            if (empty($operation)) {
                return array('status' => false, 'msg' => "撤销失败，非本人回复或审核确认信息无法撤销！");
            }
            $udata = [
                "UCDis"       => Operation::$ucdis['delete'],
                "AuditUserId" => $userid,
                "AuditTs"     => $now
            ];
            $rst = Operation::where("id", $oid)->update($udata); //撤销操作记录
            $id = $operation->SupportId; //工单id
            $msg = "撤销回复操作";
            $user = PublicMethodsHelper::getUser();
            $userid = $user->Id;
            if (get_magic_quotes_gpc()) {
                $msg = stripslashes($msg);
            }
            $now = date('Y-m-d H:i:s');
            $odata = [
                'reply'       => $msg,
                'ReplyTs'     => $now,
                'ReplyUserID' => $userid,
                'SupportId'   => $id,
                'UCDis'       => 2,
                'Source'       => 'ITSM'
            ];
            $ret = Operation::insertGetId($odata);
            $opt = Support::select("Status")->where(['id' => $id])->first();
            $ustatus = true;
            $udata = [
                'UpTs'        => $now,
                'OperationId' => $userid,
            ];
            Support::where(['id' => $id])->update($udata); //工单表修改
        } catch (\Exception $ex) {
            return array('status' => false, 'msg' => "撤销失败");
        }
        return array('status' => true, 'msg' => "撤销成功");
    }

    /**
     *  配额审核
     * @param type $id 工单id
     */
    public function postQuota($id)
    {
        try {
            $sid = intval($id);
            $user = PublicMethodsHelper::getUser();
            $userid = $user->Id;
            $url = env("JOB_URL") . "/crm/api/sendSMSOrEmailOrWechat.html";
            $header = ['key' => env("JOB_HEADER_KEY")];
            $content = [
                'supportId'   => $sid,
                'operationId' => "1",
                'userId'      => $userid,
                'status'      => "Quota"
            ];
            $client = new Client();
            $response = $client->post($url, [
                "headers"     => $header,
                'form_params' => $content,
            ]); //配合审核通过
            $rst = json_decode($response->getBody(), true);
            return array('status' => ($rst["status"] > 0), 'msg' => "请求完成");
        } catch (\Exception $ex) {
            return array('status' => false, 'msg' => "请求失败");
        }
    }

    /**
     *  云列表
     * @param type $id 工单id
     */
    public function cloud($id)
    {
        $sid = intval($id);
        $support = Support::select("CustomerInfoId")->where("id", $sid)->first(); //查找工单信息
        if (empty($support)) {
            exit();
        }

        $cloudList = Userlogin::select("userlogin.LoginId","d.Id as accId","b.AccountType","c.Name")
            ->leftJoin("cuslogin as b","userlogin.Id","=","b.UserLoginId")
            ->leftJoin("res.res_contact as c","userlogin.loginId","=","c.loginId")
            ->leftJoin("account as d","d.UserId","=","userlogin.Id")
            ->where("b.cusinfid",$support->CustomerInfoId)
            ->where("d.CustomerInfoId",$support->CustomerInfoId)
            ->groupBy("userlogin.Id")
            ->get();

        $list = InsProject::select("b.LoginId", "ins_project.name", "ins_project.project", "ins_project.cusinfid",
            "ins_project.userid")
            ->join("usercenter.userlogin as b", "ins_project.userid", "=", "b.id")
            ->where("ins_project.deleted", 0)
            ->where("ins_project.status", "starting")
            ->where("ins_project.CusInfId", $support->CustomerInfoId)
            ->get();
        return view("supports/cloud", ["data" => $cloudList,"list" => $list, "domain" => env("CLOUD_HOST")]);
    }

    /**
     * @处理工单页面加入服务标签{管理服务,第三方服务
     * @param support
     */
    public function findSupport($support)
    {
        if (null == $support || count($support) < 1) {
            return;
        }
        $cusIds = array();
        foreach ($support as $sup) {
            if (null != $sup && null != $sup->CustomerInfoId) {
                //$cusIds =$cusIds."," .$sup->CustomerInfoId;
                Array_push($cusIds, $sup->CustomerInfoId);
            }
        }
        $MANMap = array();
        // 管理服务(产品报价单级别)
        $MyQuoteList = MyQuote::select('b.CusInfId', 'myquote.SubType as OSubType')
            ->leftJoin('res.res_cusinfcontract as b', 'myquote.orderid', '=', 'b.Id')
            ->whereRaw('myquote.orderid IS NOT NULL AND (b.InValidate is null or b.InValidate=0) and b.ordertype=\'formalOrder\' AND b.loadedts IS NOT NULL AND (b.DestoryTs is null OR (b.state IN (\'unloaded\',\'released\',\'end\',\'Step60\',\'Step80\') AND myquote.prodtypeone=\'In\')) ')
            ->whereIn('b.CusInfId', $cusIds)
            ->where("prodTypeOne", 'MAN')
            ->groupBy('b.CusInfId')
            ->groupBy('myquote.SubType')
            ->get();
        $this->addToArray($MANMap, $MyQuoteList, "CusInfId", "OSubType");

        // 管理服务(产品报价单,内嵌管理服务（打包付费产品）)
        $MyQuoteList = MyQuote::selectRaw('b.CusInfId,concat(m.SubType,":",m.CfgOpt) as OSubType')
            ->leftJoin('res.res_cusinfcontract as b', 'myquote.orderid', '=', 'b.Id')
            ->leftJoin('res.myquotedetail as m', 'myquote.id', '=', 'm.MyQuote')
            ->whereRaw('myquote.orderid IS NOT NULL AND (b.InValidate is null or b.InValidate=0) and b.ordertype=\'formalOrder\' AND b.loadedts IS NOT NULL AND (b.DestoryTs is null OR (b.state IN (\'unloaded\',\'released\',\'end\',\'Step60\',\'Step80\') AND myquote.prodtypeone=\'In\')) ')
            ->whereRaw('m.ProdType in (SELECT a.TypeCode from proddb.prodtype as a left JOIN proddb.prodtype as b on a.ParProdId=b.id left join proddb.prodtype as c on b.ParProdId=c.id where c.TypeCode=\'MAN\' or b.TypeCode=\'MAN\' )')
            ->whereIn('b.cusinfid', $cusIds)
            ->where("m.BillPrice", '>', '0')
            ->groupBy('b.cusinfid')
            ->groupBy('myquote.SubType')
            ->get();
        $this->addToArray($MANMap, $MyQuoteList, "CusInfId", "OSubType");

        $DSFMap = array();
        // 第三方服务
        $dsfList = MyQuote::select('b.CusInfId', 'myquote.SubType as OSubType')
            ->leftJoin('res.res_cusinfcontract as b', 'myquote.orderid', '=', 'b.Id')
            ->whereRaw('myquote.orderid IS NOT NULL AND (b.InValidate is null or b.InValidate=0) and b.ordertype=\'formalOrder\' AND b.loadedts IS NOT NULL AND (b.DestoryTs is null OR (b.state IN (\'unloaded\',\'released\',\'end\',\'Step60\',\'Step80\') AND myquote.prodtypeone=\'In\')) ')
            ->whereIn('b.cusinfid', $cusIds)
            ->where('prodTypeOne', 'DSF')
            ->groupBy('b.cusinfid')
            ->groupBy('myquote.SubType')
            ->get();
        $this->addToArray($DSFMap, $dsfList, "CusInfId", "OSubType");

        // 相关客户
        foreach ($support as $sup) {
            if (null != $sup && null != $sup->CustomerInfoId) {
                // 根据客户Id查询产品类型为'管理服务'的执行中产品
                if (array_key_exists($sup->CustomerInfoId, $MANMap)) {
                    $man = $MANMap[$sup->CustomerInfoId];
                    if (null != $man && "" != $man) {
                        $sup->CusSuppManTxt = $man;
                        $sup->CusSuppMan = true;
                    } else {
                        $sup->CusSuppMan = false;
                    }
                }
                // 第三方服务
                if (array_key_exists($sup->CustomerInfoId, $DSFMap)) {
                    $dsf = $DSFMap[$sup->CustomerInfoId];
                    if (null != $dsf && "" != $dsf) {
                        $sup->CusSuppDSFTxt = $dsf;
                        $sup->CusSuppDSF = true;
                    } else {
                        $sup->CusSuppDSF = false;
                    }
                }
            }
        }
    }

    //添加元素到数组
    protected function addToArray(&$array, $dataList, $column, $value)
    {
        foreach ($dataList as $item) {
            if (null != $item && null != $item[$column]) {
                $strBuf = "";
                $str = "";
                foreach (array_keys($array) as $key) {
                    if ($key == $item[$column]) {
                        $str = $array[$key];
                        break;
                    }
                }
                if ("" != $str) {
                    $strBuf = $str . "<br />" . $item[$value];
                } else {
                    $strBuf = $item[$value];
                }
                $array[$item[$column]] = $strBuf;
            }
        }
    }

    //获取快速回复模板列表
    public function getReplyMode()
    {
        $type3 = Input::get('type3');
        $user = Request()->session()->get('user')->Id;
        $grpL1 = '';

        if (!empty($this->isgroup($user, "L1"))) {
            $grpL1 = 1; //L1组（服务台）
        } else {
            if (!empty($this->isgroup($user, "机房"))) {
                $grpL1 = 2; //L1组（数据中心）
            }
        }
        $retArr = [];
        if ($grpL1 == 1) {
            $rmArr = Suppstencil::select('*')
                ->where('mark',0)
                ->whereNotNull("Type")
                ->where(function($arr)use($type3){
                    $arr->where('supportType','')
                        ->orwhere('supportType', $type3);
                })
                ->orderBy('group','desc')->get();
            foreach ($rmArr as $rm) {
                $retArr[$rm->Type][$rm->Title] = [
                    'title'   => $rm->Title,
                    'content' => $rm->Content,
                    'type'    => $rm->supportType
                ];
            }
        }
        if ($grpL1 == 2) {
            $rmArr = Suppstencil::select('*')->where("mark",0)
                ->whereNotNull("Type")
                ->where('group', 0)
                ->where(function ($arr) use ($type3) {
                    $arr->where('supportType', '')
                        ->orwhere('supportType', $type3);
                })
                ->get();
            foreach ($rmArr as $rm) {
                $retArr[$rm->Type][$rm->Title] = [
                    'title'   => $rm->Title,
                    'content' => $rm->Content,
                    'type'    => $rm->supportType
                ];
            }
        }
        return $retArr;
    }

    /**
     * 查询工单配置信息
     * @param $id
     * @return null
     */
    protected function jsonConfig($id){
        $support = Support::where("Id",$id)->first();
        $jsonConfig = [];
        if( $support && $support->jsonConfig && $support->ClassInficationOne){
            $config =  json_decode($support->jsonConfig,true);
            $dic = AuxDict::where("Code",$support->ClassInficationOne)->first();
            if($dic){
                $jsonConfig["supType"] = $dic->Means?$dic->Means:"";
            }else{
                return null;
            }
            $jsonConfig["config"] = $config;
            return $jsonConfig;
        }else{
            return null;
        }
    }

    /*protected function jsonConfig($id){
        $support = Support::where("Id",$id)->first();
        $jsonConfig = [];
        if( $support && $support->jsonConfig){
            $config =  json_decode($support->jsonConfig,true);
            if(isset($config["classifyId"])){
                $dic = AuxDict::where("Id",$config["classifyId"])->first();
                if($dic){
                    $jsonConfig["supType"] = $dic->Means;
                }else{
                    return null;
                }
                $classifyList = SupportClassConfig::getConfigList($config["classifyId"]);

                foreach($classifyList as $key => $value){
                    if(isset($config[$value["AttrCode"]])){
                        $typeList = explode(":",$value["AttrType"]);
                        $jsonConfig["config"][$value["AttrName"]] = [];
                        if($typeList[0] == "DIC"){
                            if(is_array($config[$value["AttrCode"]])){
                                foreach($config[$value["AttrCode"]] as $item){
                                    $dict = AuxDict::getDic($typeList[1],$item);
                                    if($dict){
                                        $jsonConfig["config"][$value["AttrName"]][] = $dict["Means"];
                                    }
                                }
                            }else{
                                $dict = AuxDict::getDic($typeList[1],$config[$value["AttrCode"]]);
                                if($dict){
                                    $jsonConfig["config"][$value["AttrName"]][] = $dict["Means"];
                                }
                            }
                        }else{
                            if(is_array($config[$value["AttrCode"]])){
                                foreach($config[$value["AttrCode"]] as $item){
                                    $jsonConfig["config"][$value["AttrName"]][] = $item;
                                }
                            }else{
                                $jsonConfig["config"][$value["AttrName"]][] = $config[$value["AttrCode"]];
                            }
                        }
                    }
                }
            }
            return $jsonConfig;
        }else{
            return null;
        }
    }*/
}
