<?php
/**
 * User: Lidz<lidz@51idc.com>
 * Date: 2017/6/12 17:18
 */
namespace Itsm\Model\Proddb;

use Itsm\Model\Model;

class ProdEnquiry extends Model
{
    protected $connection = 'proddb';
    public $primaryKey = 'id';
    public $timestamps = false;

    protected $table = 'prodenquiry';

    protected $fillable = ['enquiryNo', 'title', 'title', 'priority', 'steps', 'expectTs'];

}


