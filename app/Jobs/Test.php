<?php

namespace Itsm\Jobs;

use Itsm\Jobs\Job;
use Itsm\Services\SmsService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
class Test extends Job implements SelfHandling,ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SmsService $smsService)
    {
        $params = array(
            "id"=>"8",
            "name"=>"luyees"
            );
        $smsService->SmsQueueCallback($params);
    }
}
