<?php
/**
 * User: Lidz<lidz@51idc.com>
 * Date: 2017/6/12 17:18
 */

namespace Itsm\Model\Proddb;

use Itsm\Model\Model;

class EnquiryRecord extends Model
{
    protected $connection = 'proddb';
    public $primaryKey = 'id';
    public $timestamps = false;

    protected $table = 'enquiryrecord';

    protected $fillable = ['enquiryId', 'noticeType', 'csIds', 'recordType', 'instructions', 'userId'];
}