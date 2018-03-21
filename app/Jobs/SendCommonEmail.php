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
class SendCommonEmail extends Job implements SelfHandling,ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $type = '';//邮件类型 html或txt
    protected $to = '';//发送给
    protected $cc = '';//抄送
    protected $bcc = '';//秘密抄送
    protected $subject = '';//主题
    protected $context = '';//内容
    protected $attach = '';//附件
    protected $inner_user_ids = [];//接收者的userId

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($type,$to,$cc,$bcc,$subject,$context,$attach,$inner_user_ids)
    {
        $this->type = $type;
        $this->to = $to;
        $this->cc = $cc;
        $this->bcc = $bcc;
        $this->subject = $subject;
        $this->context = $context;
        $this->attach = $attach;
        $this->inner_user_ids = $inner_user_ids;
    }

    /**
     * 发送邮件
     *
     * @return void
     */
    public function handle()
    {
        \Illuminate\Support\Facades\Log::info("debug", ["22222..."]);
        //生产环境:http://192.168.9.100:9001
        $url = env("RestApi_URL")."/message/mail";
        $header = ['key' => env("JOB_HEADER_KEY")];
        $content = [
            'type'=>  $this->type,
            'to'=>$this->to,
            'cc'=>$this->cc,
            'bcc'=>$this->bcc,
            'subject'=>$this->subject,
            'context'=>$this->context,
            'attach'=>$this->attach,
            'inner_user_ids'=>$this->inner_user_ids,
        ];
        $client = new Client();
        $response = $client->post($url,[
            "headers" => $header,
            'form_params'=>$content,
        ]);
        echo $response->getBody() . "\n";
        echo var_export($content) . ";url:" . $url . "\n";

    }
}
