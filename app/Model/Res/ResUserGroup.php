<?php
/**
 * Created by PhpStorm.
 * 工单工作组与负责人关系表
 * User: lidz
 * Date: 2016/8/10
 * Time: 16:11
 */
namespace Itsm\Model\Res;

use Itsm\Model\Model;

class ResUserGroup extends Model
{
    protected $connection = 'res';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'res_usergroup';

    protected $fillable = ['UserId', 'UsersId'];

}