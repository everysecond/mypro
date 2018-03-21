<?php
namespace Itsm\Services;
use Itsm\Model\Usercenter\SmsSend;

class SmsService extends BaseService
{
    public function __construct()
    {
       parent::__construct();
    }
     public function SmsQueueCallback($params){
         SmsSend::insert(
              array(
                        'seqId'=>$params['id'],
                        'name'=> $params['name'],
                    )
        );
        echo  "queue..\n";
        return true;
     }
}
