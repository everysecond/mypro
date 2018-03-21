<?php

namespace Itsm\Model\Usercenter;

use Itsm\Model\Model;

class TimedEvents extends Model
{
    protected $connection = 'usercenter';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'timedevents';

    protected $fillable = ['Id,MarkDelete'];
}
