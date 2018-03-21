<?php
/**
 * Created by PhpStorm.
 */

namespace Itsm\Model\Usercenter;

use Itsm\Model\Model;

class HandoverEvent extends Model
{

    protected $connection = 'usercenter';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'handover_event';
    protected $guarded = [];
}