<?php

namespace Itsm\Jobs;

use Itsm\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Itsm\Http\Helper\PublicMethodsHelper;
use GuzzleHttp\Client;

class SendChangeEmail extends Job implements SelfHandling, ShouldQueue
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
     * 发送变更邮件
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
//        echo $response->getBody() . "\n";
//        echo var_export($content) . ";url:" . $url . "\n";
    }
}
