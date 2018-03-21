<?php
/**
 * Created by PhpStorm.
 * User: chenglh
 * Date: 2016/10/11
 * Time: 10:55
 */
namespace Itsm\Model\Usercenter;

use Itsm\Model\Model;

class Correlation extends Model
{

    protected $connection = 'usercenter';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'correlation';
    protected $guarded = [];
}