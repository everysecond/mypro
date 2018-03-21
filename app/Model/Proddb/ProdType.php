<?php
/**
 * User: Lidz<lidz@51idc.com>
 * Date: 2017/11/13 17:27
 */

namespace Itsm\Model\Proddb;


use Itsm\Model\Model;

class ProdType extends Model
{
    protected $connection = 'proddb';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'prodtype';

    protected $fillable = ['Id'];
}