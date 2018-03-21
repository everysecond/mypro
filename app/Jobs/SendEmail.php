<?php

namespace Itsm\Jobs;

use Itsm\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Itsm\Http\Helper\PublicMethodsHelper;
use GuzzleHttp\Client;
use Log;
class SendEmail extends Job implements SelfHandling,ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $email = '';//邮箱地址
    protected $emailTitle = '';//邮件标题
    protected $emailContent = '';//邮件内容
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email,$emailTitle, $emailContent)
    {
        $this->email = $email;
        $this->emailTitle = $emailTitle;
        $this->emailContent = $emailContent;
    }

    /**
     * 发送邮件
     *
     * @return void
     */
    public function handle()
    {
        $url = env("JOB_URL")."/crm/api/itsmMessageSend.html";
        $header = ['key'=>env("JOB_HEADER_KEY")];
        $content = [
            'title'=>  $this->emailTitle,
            'content'=>$this->emailContent,
            'receivers'=>$this->email,
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
