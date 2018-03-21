<?php
/**
 * User: Lidz<lidz@51idc.com>
 * Date: 2016/9/2 17:54
 */

namespace Itsm\Model\Usercenter;

use Itsm\Model\Model;

class Suppstencil extends Model
{
    protected $connection = 'usercenter';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'suppstencil';
}