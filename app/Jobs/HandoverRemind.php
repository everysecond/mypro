<?php

namespace Itsm\Jobs;

use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Itsm\Http\Helper\PublicMethodsHelper;
use Itsm\Http\Helper\ThirdCallHelper;
use Itsm\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Itsm\Model\Usercenter\HandoverEvent;
use Itsm\Model\Usercenter\HandOverTask;
use Itsm\Model\Usercenter\TimedEvents;

class HandoverRemind extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($list)
    {
        $this->data = $list;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $url = env("JOB_URL") . "/crm/api/itsmMessageSend.html";
        $header = ['key' => env("JOB_HEADER_KEY")];
        if (!empty($this->data)) {
            foreach ($this->data as $item) {
                //验证事件是否已经完成
                $eventId = $item['eventId'];
                $supportMsg = $item['supportId'] == 0?"":"工单编号:{$item['supportId']};";
                $handoverInfo = HandoverEvent::select("status", "chargerId","csIds")->find($eventId);
                //如果事件已完成,则不通知
                if ($handoverInfo->status != 0) {
                    HandOverTask::where("Id", $item['Id'])->update(["state" => 0]);
                    continue;
                }
                //获取提醒类型和抄送人
                $handParentInfo = PublicMethodsHelper::getEventPInfoAndCCIdsById($item['eventId']);
                $ccIds = "";
                if($handoverInfo->chargerId && $handoverInfo->chargerId != 0){
                    $ccIds = trim($handoverInfo->chargerId, ",");
                    //获取事件负责人姓名
                    $charger = ThirdCallHelper::getStuffName($item['chargerId']);
                }else{
                    $charger = "未选择";
                }

                //合并通知人
                if ($handParentInfo['ccIds']) {
                    $ccIds = $ccIds . "," . trim($handParentInfo['ccIds'], ",");
                }
                //合并事件抄送人
                if ($handoverInfo->csIds) {
                    $ccIds = $ccIds . "," . trim($handoverInfo->csIds, ",");
                    echo trim($handoverInfo->csIds, ",");
                }
                //合并交接单分派负责人
                if ($handParentInfo['chargerId']) {
                    $ccIds = $ccIds . "," .$handParentInfo['chargerId'];
                }
                $ccIdsArr = explode(",", trim($ccIds,","));
                $item['remindText'] = ThirdCallHelper::myTrimOnlyNRT($item['remindText']);//替换所有空白字符
                $item['remindText'] = ThirdCallHelper::subStr38($item['remindText']);//保留前五十个字符...

                $apiContent = [
                    'title'       => $supportMsg."事件编号:{$eventId}的提醒",
                    'userIds'     => implode(',', array_unique($ccIdsArr)),//通知人去重
                    'messageType' => $handParentInfo['remindType'],
                    'content'     => $item['remindText']."\n[事件负责人:$charger]"
                ];
                $noticeClient = new Client();
                $response = $noticeClient->post($url, [
                    "headers"     => $header,
                    'form_params' => $apiContent
                ]);
                $res = \GuzzleHttp\json_decode($response->getBody(), true);
                //如果成功
                if (Arr::get($res, "status") == 1) {
                    //如果不连续提醒，下次执行不提醒v
                    if ($item['remindType'] == "no") {
                        HandOverTask::where("id", $item['Id'])->update(["remindState" => "1", "state" => 0]);
                    }
                    HandOverTask::where("id", $item['Id'])->increment("sendCount", 1);
                    $max = TimedEvents::where([
                        "name"       => 'sendmailcount',
                        "MarkDelete" => 0
                    ])->value('Parameter');
                    if(HandOverTask::where("id", $item['Id'])->value("sendCount") >= $max){
                        HandOverTask::where("id", $item['Id'])->update(["remindState" => "1", "state" => 0]);
                    }
                }
                echo "gyuu";
                echo $response->getBody() . "\n";
                echo var_export($apiContent) . ";url:" . $url . "\n";
            }
        }
    }
}