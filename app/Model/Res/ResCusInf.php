<?php
/**
 * Created by PhpStorm.
 * User: chenglh
 * Date: 2016/8/11
 * Time: 16:10
 */

namespace Itsm\Model\Res;
use Itsm\Model\Model;

class ResCusInf extends Model
{
    protected $connection = 'res';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'res_cusinf';

    protected $fillable = ['Id','CusName','Address','Memo', 'Authorization','CusImportanceType'];
}