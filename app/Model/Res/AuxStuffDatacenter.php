<?php
/**
 * Created by PhpStorm.
 * User: lidz
 * Date: 2016/7/29
 * Time: 18:19
 */
namespace Itsm\Model\Res;

use Itsm\Model\Res\ResDataCenter;
use Itsm\Model\Model;

class AuxStuffDatacenter extends Model
{
    protected $connection = 'res';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'aux_stuff_datacenter';

    protected $fillable = ['Id', 'StuffId', 'DatacenterId'];

    const DISABLED_YES = 1;
    const DISABLED_NO = 0;

    public static function getName($array)
    {
        $retarray = array();
        foreach ($array as $item) {
            $retarray[] = ResDataCenter::where('Id', $item->DataCenterId)->first()->DataCenterName;
        }
        return $retarray;
    }

}