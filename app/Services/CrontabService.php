<?php

namespace Itsm\Services;

use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Itsm\Http\Controllers\RPMS\ResourceBillController;
use Itsm\Jobs\HandoverRemind;
use Itsm\Jobs\SendCommonEmail;
use Itsm\Model\Rpms\ResourceContract;
use Itsm\Model\Usercenter\HandOverTask;
use Itsm\Model\Usercenter\SupportHangupTask;
use Itsm\Jobs\hangupRemind;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 定时任务处理
 *
 * @author liky@51idc.com
 */
class CrontabService extends BaseService
{

    use DispatchesJobs;

    /**
     * 挂起记录提醒
     * @param type $type 连续提醒
     */
    public function hangupRemind($type = "no")
    {
        $continuous = SupportHangupTask::$continuous["{$type}"];
        $now = date("Y-m-d H:i");
        $query = SupportHangupTask::select("Id", "SupportId", "RemindStuff", "RemindMode", "HangupText",
            "ContinuityRemind");
        if ($type == "no") {//不连续提醒
            $query->where("RemindTs", "=", $now);
        } else {
            $query->where("RemindTs", "<=", $now); //连续提醒合法时间
        }

        $list = $query->where("State", "1")//挂起中
        ->where("Remind", "1")//需要提醒
        ->where("ContinuityRemind", $continuous)//提醒间隔时间（分钟）
        ->get()->toArray();
        $job = new hangupRemind($list);
        $this->dispatch($job);
    }

    /**
     * 事件通知
     * @param string $type
     */
    public function handoverRemind($type = "no")
    {

        $now = date("Y-m-d H:i");
        $query = HandOverTask::selectRaw("handover_task.*,b.supportId,b.chargerId")
            ->leftJoin("handover_event as b","b.id","=","handover_task.eventId");
        if ($type == "no") {
            $query->where("handover_task.remindTs", "=", $now);
        } else {
            $query->where("handover_task.remindTs", "<=", $now);
        }
        //过滤出未提醒的
        $list = $query->where("handover_task.state", "=", "1")->where("handover_task.remindType", "=", $type)->get()->toArray();
        if ($list) {
            $job = new HandoverRemind($list);
            $this->dispatch($job);
        }
        Log::info("debug", [$type]);
    }

    public function generateBill($type = "no")
    {
        //系统默认提前3天生成账单
        $interval = env("BILLINTERVAL") ? env("BILLINTERVAL") : 3;

        $billController = new ResourceBillController();
        $contractList = ResourceContract::where("status","doing")
        ->whereNotNull("startTs")
        ->whereNotNull("endTs")
        ->whereRaw("startTs < NOW()")
        ->whereRaw("endTs > NOW()")
        ->whereRaw("id not in (SELECT contractId FROM resource_bill WHERE billEnd > DATE_ADD(NOW(), INTERVAL $interval DAY))")->get();

        $tsStart = date("Y-m-d H:i:s");
        Log::info("debug", [$tsStart.":开始系统生成账单..."]);
        //开始生成
        foreach ($contractList as $contract)
        {
            $billController->generateBill($contract);
        }

        $tsEND = date("Y-m-d H:i:s");
        Log::info("debug", [$tsEND."系统生成账单完成..."]);
    }

    public function checkBillCompleteness($type = "no")
    {
        $contractList = DB::select("SELECT b.cnt,a.seq,a.contractId from rpms.resource_bill a join ".
            "(SELECT COUNT(contractId) cnt,MAX(id) as maxid from rpms.resource_bill where seq is not null and deleted=0 GROUP BY contractId ) as b on a.id=b.maxid ".
            "where b.cnt!=(a.seq+1)");
        $tsStart = date("Y-m-d H:i:s");
        Log::info("debug", [$tsStart.":开始检测账单连续性..."]);
        $msg = "账单连续性检测结果:";
        if($contractList && count($contractList)>0){
            $msg =$msg."账单不连续数量".count($contractList)."条,详情如下:</br>";
        }else{
            $msg =$msg."未检测到不连续账单!";
        }
        //开始生成
        foreach ($contractList as $contract)
        {
            $msg =$msg."合同id:".$contract->contractId."已产生账期数:".($contract->seq+1).",当前有效账单数:".$contract->cnt."</br>";
        }
        //邮件发送
        $job = new SendCommonEmail("html","yuwc@anchnet.com",null,null,"账单连续性检测",$msg,null,[663]);//创建队列任务
        $this->dispatch($job);

        $tsEND = date("Y-m-d H:i:s");
        Log::info("debug", [$tsEND."检测账单连续性完成..."]);
    }

}
