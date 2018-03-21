<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/27
 * Time: 11:18
 */

namespace Itsm\Model\Auth;

use Itsm\Model\Model;

class Authorities extends Model
{
    protected $connection = 'auth';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'authorities';

    protected $fillable = ['Id', 'username', 'authority'];

    const DISABLED_YES = 1;
    const DISABLED_NO  = 0;

}