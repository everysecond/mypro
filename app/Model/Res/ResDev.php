<?php
/**
 * Created by PhpStorm.
 * User: chenglh
 * Date: 2016/8/11
 * Time: 10:32
 */

namespace Itsm\Model\Res;
use Itsm\Model\Model;

class ResDev extends Model
{
    protected $connection = 'res';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'res_dev';

    protected $fillable = ['DevId', 'DevType','devStatus','devUseType'];
}