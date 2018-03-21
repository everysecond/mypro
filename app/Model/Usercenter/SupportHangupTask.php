<?php

namespace Itsm\Model\Usercenter;

use Itsm\Model\Model;

/**
 * 工单挂起记录表
 */
class SupportHangupTask extends Model {
    protected $connection = 'usercenter';
    public $primaryKey = 'Id';
    public $timestamps = false;
    protected $table = 'Supporthanguptask';
    public static $state = [
        "start" => 1, //挂起
        "end"   => 0//释放
    ]; //状态
    public static $continuous = [
        "no"      => "0", //不连续提醒
        "two"     => "2", //没两分钟提醒一次
        "five"    => "5", //没五分钟提醒一次
        "ten"     => "10", //没十分钟提醒一次
        "fifteen" => "15", //没十五分钟提醒一次
    ]; //连续提醒方式
    public static $mode = [
        "1" => "email", //邮件
        "2" => "wechat", //微信
        "3" => "sms", //短信
    ];
    public static $modename = [
        "email"  => "邮件",
        "wechat" => "微信",
        "sms"    => "短信",
    ];
}
