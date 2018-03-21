<?php
/**
 * Created by PhpStorm.
 * User: chenglh
 * Date: 2016/10/11
 * Time: 10:55
 */
namespace Itsm\Model\Usercenter;

use Itsm\Model\Model;

class SupportRelation extends Model
{

    protected $connection = 'usercenter';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'support_relation';
    protected $guarded = [];
}