<?php
/**
 * User: Lidz<lidz@51idc.com>
 * Date: 2017/11/13 16:18
 */
namespace Itsm\Model\Rpms;

use Itsm\Model\Model;

class ResourceType extends Model
{
    protected $connection = 'rpms';
    public $primaryKey = 'id';
    public $timestamps = false;

    protected $table = 'resource_type';

    protected $fillable = ['id'];

}


