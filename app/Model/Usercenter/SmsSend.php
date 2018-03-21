<?php

namespace Itsm\Model\Usercenter;

use Itsm\Model\Model;
class SmsSend extends Model
{
    protected $table = 'smssend';
    public $timestamp = false;
    protected $connection = 'usercenter';
    public $primaryKey = 'Id';
}