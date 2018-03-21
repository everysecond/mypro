<?php

namespace Itsm\Jobs;

use GuzzleHttp\Client;
use Itsm\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendIssueEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $title = '';//邮件标题
    protected $userIds = '';//通知列表人员
    protected $changeType = '';//通知类型
    protected $content = '';//通知内容
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($title, $userIds, $changeType, $content)
    {
        $this->title = $title;
        $this->userIds = $userIds;
        $this->changeType = $changeType;
        $this->content = $content;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $url = env("JOB_URL") . "/crm/api/changeMessageSend.html";
        $header = ['key' => env("JOB_HEADER_KEY")];
        $content = [
            'title'      => $this->title,
            'userIds'    => $this->userIds,
            'changeType' => $this->changeType,
            'content'    => $this->content,
        ];
        $client = new Client();
        $response = $client->post($url, [
            "headers"     => $header,
            'form_params' => $content
        ]);
        echo $response->getBody() . "\n";
        echo var_export($content) . ";url:" . $url . "\n";
    }
}
