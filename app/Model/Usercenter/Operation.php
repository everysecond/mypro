<?php

/**
 * Created by PhpStorm.
 * User: Lidz
 * Date: 2016/6/13
 * Time: 18:57
 */
namespace Itsm\Model\Usercenter;

use Itsm\Model\Model;
use Itsm\Model\Res\AuxStuff;
class Operation  extends Model
{
    protected $connection = 'usercenter';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'operation';

    protected $fillable = ['Id'];

    const DISABLED_YES = 1;
    const DISABLED_NO  = 0;
    public static $ucdis = [
        "pending"=>0,//待审核
        "pass"=>1,//审核通过
        "delete"=>2,//删除
        "remark"=>3,//备注记录
        "hangup"=>4,//挂起记录
        "confirm"=>5//负责人已确认指派信息
    ];//记录类型

    public static function replyUser($replyUserId){
        if($replyUserId>500000){
            $loginUser = Userlogin::where('Id',$replyUserId)->first();
            $loginUser = !empty($loginUser)?$loginUser->LoginId:$replyUserId;
        }else{
            $loginUser = AuxStuff::where('Id',$replyUserId)->first();
            $loginUser = isset($loginUser)?$loginUser->Name:$replyUserId;
        }
        return $loginUser;
    }
    public static function replylist($supportId){

    }
   function chargeGroup(){
        return $this->belongsTo('Itsm\Model\Res\ResUsers', 'DatacenterId', 'Id');
    }

    function operationUser(){
        return $this->belongsTo('Itsm\Model\Res\AuxStuff', 'OperationId', 'Id');
    }

    function chargeGroupTwo(){
        return $this->belongsTo('Itsm\Model\Res\ResUsers', 'DatacenterTwoId', 'Id');
    }

    function operationUserTwo(){
        return $this->belongsTo('Itsm\Model\Res\AuxStuff', 'ChargeUserTwoId', 'Id');
    }

    public static function getLastOperation($list)
    {
        if (!empty($list)) {
            foreach ($list as $item) {
                $item->lastOperation = '无';
                $item->lastOperationTs = '';
                $operation = Operation::where('SupportId', $item->Id)
                    ->orderBy('ReplyTs', 'desc')->first();
                if ($operation && $operation->reply != null) {
                    $item->lastOperation = str_replace('src="/usercenter',  "src=\"".env('JOB_URL2')."/usercenter", $operation->reply);
                    $item->lastOperationTs = $operation->ReplyTs;
                }
            }
        }
        return $list;
    }

}