<?php
/**
 * Created by PhpStorm.
 */

namespace Itsm\Model\Usercenter;

use Itsm\Model\Model;

class Issue extends Model
{

    protected $connection = 'usercenter';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'issue';
    protected $guarded = [];
}