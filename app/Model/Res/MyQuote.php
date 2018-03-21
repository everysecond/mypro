<?php
/**
 * User: Lidz<lidz@51idc.com>
 * Date: 2016/8/16 11:00
 */

namespace Itsm\Model\Res;

use Itsm\Model\Model;

class MyQuote extends Model
{
    protected $connection = 'res';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'myquote';

    protected $fillable = ['Id'];

}