<?php

namespace Itsm\Jobs;

use Itsm\Http\Helper\ThirdCallHelper;
use Itsm\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use GuzzleHttp\Client;
use Itsm\Model\Usercenter\SupportHangupTask;
use Itsm\Model\Usercenter\Support;
use Itsm\Http\Helper\PublicMethodsHelper;
use Itsm\Model\Usercenter\TimedEvents;

class hangupRemind extends Job implements SelfHandling, ShouldQueue {

    use InteractsWithQueue,
        SerializesModels;

    protected $list;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($list) {
        $this->list = $list;
    }

    /**
     *  挂起任务.
     * @return void
     */
    public function handle() {
        echo "IP:".Request()->ip()."\n";
        if (!empty($this->list)) {
            $url = env("JOB_URL") . "/crm/api/sendHangTask.html";
            $header = ['key' => env("JOB_HEADER_KEY")];

            foreach ($this->list as $row) {
                $sid = $row['SupportId'];//工单id
                $support = Support::select("Status")->where("id",$sid)->first();
                $status = PublicMethodsHelper::$supportStatusList["{$support->Status}"];//工单状态
                if($status!="挂起中"){//老系统没有同步挂起表，这里同步一下状态（工单表释放挂起，挂起记录表同步释放）
                    $rst = SupportHangupTask::where("Id",$row['Id'])->update(["State"=>SupportHangupTask::$state['end']]);
                    continue;
                }
                $types = $row["RemindMode"];
                $types = explode(",", $types);
                $types = array_map(function($i) {
                    return SupportHangupTask::$mode["$i"];
                }, $types);
                $types = implode(",", $types);
                $row['HangupText'] = ThirdCallHelper::myTrimOnlyNRT($row['HangupText']);//替换所有空白字符
                $content = [
                    'supportId' => $row['SupportId'],
                    'noticeIds' => $row['RemindStuff'],
                    'noticeTypes' => $types,
                    'noticeContent' => "工单编号：".$row['SupportId']."\r\n"."挂起说明："."\r\n".$row['HangupText']
                ];
                $client = new Client();
                $response = $client->post($url, [
                    "headers" => $header,
                    'form_params' => $content,
                ]);
                if(empty($row['ContinuityRemind'])){//不连续提醒，下次执行不提醒
                    SupportHangupTask::where("id",$row['Id'])->update(["Remind"=>"2"]);
                }
                SupportHangupTask::where("id", $row['Id'])->increment("sendCount", 1);
                $max = TimedEvents::where([
                    "name"       => 'sendmailcount',
                    "MarkDelete" => 0
                ])->value('Parameter');
                if(SupportHangupTask::where("id", $row['Id'])->value("sendCount") >= $max){
                    SupportHangupTask::where("id",$row['Id'])->update(["Remind"=>"2","State"=>SupportHangupTask::$state['end']]);
                }
                echo $response->getBody() . "\n";
                echo var_export($content) . ";url:" . $url . "\n";
            }
        }
    }

}
