<?php
/**
 * 登录记录表
 * User: Lidz<lidz@51idc.com>
 * Date: 2016/8/18 9:20
 */

namespace Itsm\Model\Usercenter;

use Itsm\Model\Model;


class UserLoginHis  extends Model
{
    protected $connection = 'usercenter';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'userloginhis';

    protected $fillable = ['LoginId', 'LoginIP', 'LoginTs', 'LoginSource', 'memo'];
}