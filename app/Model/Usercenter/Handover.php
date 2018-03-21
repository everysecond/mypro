<?php
/**
 * Created by PhpStorm.
 */

namespace Itsm\Model\Usercenter;

use Illuminate\Support\Arr;
use Itsm\Model\Model;

class Handover extends Model
{

    protected $connection = 'usercenter';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'handover';
    protected $guarded = [];

    protected static $status = [
        'email'  => '邮件',
        'sms'    => '短信',
        'wechat' => '微信'
    ];

    /**
     * 将handover_event里的id转换为所连接的handover的id
     * @params $list
     */
    public static function translationId($list)
    {
        foreach ($list as &$item) {
            if (HandoverRelation::where('handEventId', $item->id)) {
                if ($handover = HandoverRelation::select('handoverId')->where('handEventId', $item->id)->first()) {
                    $item->handoverId = $handover->handoverId;
                }
            }
        }
        return $list;
    }

    public static function translaltionStatus($list)
    {
        foreach ($list as $item) {
            $status = $item->remindType;
            $statusArr = explode(',', $status);
            $newStatus = "";
            foreach ($statusArr as $statusItem) {
                $newStatus .= Arr::get(self::$status, $statusItem) . ",";
            }
            $item->remindType = trim($newStatus, ",");
        }
        return $list;
    }
}