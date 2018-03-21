<?php
/**
 * Created by PhpStorm.
 * 员工名单表
 * User: lidz
 * Date: 2016/8/10
 * Time: 16:14
 */

namespace Itsm\Model\Res;
use Itsm\Model\Model;

class AuxStuff extends Model
{
    protected $connection = 'res';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'aux_stuff';

    protected $fillable = ['Name', 'Title', 'position'];
}