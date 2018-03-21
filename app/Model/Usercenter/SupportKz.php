<?php

namespace Itsm\Model\Usercenter;

use Itsm\Model\Model;

class SupportKz extends Model
{

    protected $connection = 'usercenter';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'support_kz';

    protected $guarded = [];

}