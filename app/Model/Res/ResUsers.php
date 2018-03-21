<?php
/**
 * Created by PhpStorm.
 * 工单工作组 数据表
 * User: lidz
 * Date: 2016/8/10
 * Time: 15:47
 */
namespace Itsm\Model\Res;

use Itsm\Model\Model;

class ResUsers extends Model
{
    protected $connection = 'res';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'res_users';

    protected $fillable = ['UsersName', 'UsersDes', 'isGrouping', 'chargeGroup', 'sort'];

}
