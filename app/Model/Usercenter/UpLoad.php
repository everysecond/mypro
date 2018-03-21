<?php
/**
 * User: Lidz<lidz@51idc.com>
 * Date: 2016/9/1 13:26
 */

namespace Itsm\Model\Usercenter;

use Itsm\Model\Model;

class UpLoad extends Model
{
    protected $connection = 'usercenter';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'upload';

    protected $fillable = ['Id'];
}