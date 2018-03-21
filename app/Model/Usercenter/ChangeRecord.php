<?php
/**
 * Created by PhpStorm.
 * User: chenglh
 * Date: 2016/9/9
 * Time: 15:36
 */


namespace Itsm\Model\Usercenter;

use Itsm\Model\Model;

class ChangeRecord extends Model
{

    protected $connection = 'usercenter';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'changerecord';
    protected $guarded = [];

}