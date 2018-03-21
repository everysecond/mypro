<?php
/**
 * User: Lidz<lidz@51idc.com>
 * Date: 2017/11/13 16:18
 */
namespace Itsm\Model\Rpms;

use Itsm\Model\Model;

class ResourceContact extends Model
{
    protected $connection = 'rpms';
    public $primaryKey = 'id';
    public $timestamps = false;

    protected $table = 'resource_contact';

    protected $fillable = ['id'];

}


