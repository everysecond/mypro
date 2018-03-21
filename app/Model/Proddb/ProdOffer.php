<?php
/**
 * User: Lidz<lidz@51idc.com>
 * Date: 2017/6/12 17:27
 */

namespace Itsm\Model\Proddb;


use Itsm\Model\Model;

class ProdOffer extends Model
{
    protected $connection = 'proddb';
    public $primaryKey = 'id';
    public $timestamps = false;

    protected $table = 'prodoffer';

    protected $fillable = ['enquiryId', 'prodName', 'prodPC', 'describe', 'amount', 'unitPrice'];
}