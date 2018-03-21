<?php
/**
 * Created by PhpStorm.
 * 数据字典 数据表
 * User: lidz
 * Date: 2016/8/10
 * Time: 13:24
 */
namespace Itsm\Model\Res;

use Itsm\Model\Model;

class AuxDict extends Model
{
    protected $connection = 'res';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'aux_dict';

    protected $fillable = ['Domain', 'DomainCode', 'Means', 'Code', 'ParentCode', 'ModuleType'];
    /**
     * 根据来源code查找名称
     * @param type $code
     */
    public static function getSource($domain,$domainCode,$code){
        return $arr = AuxDict::select( 'Means')
            ->where('Domain', $domain)
            ->where('DomainCode', $domainCode)
            ->where('Code', $code)
            ->where(function ($arr) {
                $arr->whereNull('Validate')
                    ->orwhere('Validate', '<>', AuxDict::DISABLED_YES);
            })
            ->first();
    }

    /**
     * 根据来源code查找名称
     * @param type $code
     */
    public static function getDic($domainCode, $code)
    {
        return $arr = AuxDict::select('*')
            ->where('DomainCode', $domainCode)
            ->where('Code', $code)
            ->first()->toArray();
    }

}


