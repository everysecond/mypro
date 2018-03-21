<?php

namespace Itsm\Jobs;

use Itsm\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Itsm\Http\Helper\PublicMethodsHelper;
use GuzzleHttp\Client;

class SpeedAnswer extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $supportId = '';//工单Id
    protected $operationId = '';//回复记录Id
    protected $userId = '';//操作人Id
    protected $status = '';//应答类型

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($supportId, $operationId, $userId, $status)
    {
        $this->supportId = $supportId;
        $this->operationId = $operationId;
        $this->userId = $userId;
        $this->status = $status;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $url = env("JOB_URL") . "/crm/api/sendSMSOrEmailOrWechat.html";
        $header = ['key'=>env("JOB_HEADER_KEY")];
        $content = [
            'supportId'   => $this->supportId,
            'operationId' => $this->operationId,
            'userId'      => $this->userId,
            'status'      => $this->status
        ];
        $client = new Client();
        $response = $client->post($url,[
            "headers"=>$header,
            'form_params'=>$content,
        ]);
        echo $response->getBody() . "\n";
        echo var_export($content) . ";url:" . $url . "\n";
    }
}
