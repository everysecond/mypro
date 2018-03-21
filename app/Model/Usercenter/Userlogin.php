<?php

/**
 * support
 *
 * User: Wudi<wudi@51idc.com>
 * Date: 16/5/4 17:16
 */
namespace Itsm\Model\Usercenter;

use Itsm\Model\Model;

class Userlogin extends Model
{

    protected $connection = 'usercenter';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'userlogin';

    protected $fillable = ['LoginId', 'Disabled', 'LoginPasswd', 'UpTs', 'LastLoginTs', 'LastLoginIp'];


}