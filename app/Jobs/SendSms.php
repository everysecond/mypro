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
class SendSms extends Job implements SelfHandling,ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $mobile = '';//手机号
    protected $smscontent = '';//短信内容
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($mobile,$content)
    {
        $this->mobile = $mobile;
        $this->smscontent = $content;
    }

    /**
     * 发送短信
     *
     * @return void
     */
    public function handle()
    {
        $url = env("JOB_URL")."/crm/api/smsSend.html";
        $header = ['key'=>env("JOB_HEADER_KEY")];
        $content = [
            'mobile'=>  $this->mobile,
            'sms_contect'=>$this->smscontent
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
