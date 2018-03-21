<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/03/10
 * Time: 上午 10:28
 */

namespace Itsm\Model\Usercenter;

use Itsm\Model\Model;
use Itsm\Model\Res\AuxDict;
use Itsm\Model\Res\AuxStuff;

class SupportClassConfig extends Model
{

    protected $connection = 'usercenter';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'supportclassconfig';
    protected $guarded = [];
    /**
     * 将员工Id转为Name
     * @param $array
     * @param $Code
     * @return mixed
     */
    static function translationStuff($array, $Code)
    {
        $allStuff = [];
        $stuffs = AuxStuff::Select('Name', 'Id')->get();
        foreach ($stuffs as $stuff) {
            $allStuff[$stuff->Id] = $stuff->Name;
        };
        foreach ($array as $item) {
            if (isset($allStuff[$item->{$Code}])) {
                $item->{$Code} = $allStuff[$item->{$Code}];
            }
        }
        return $array;
    }

    /**
     * 获取人员姓名简称
     * @param $array
     * @param $Code
     * @return mixed
     */
    static function subStuffName($array, $Code)
    {
        foreach ($array as $item) {//截取名省略姓
            $item->subName = mb_substr($item->{$Code}, -2);
        }
        return $array;
    }

    /**
     * 获取配置列表
     * @param $Id
     * @return array
     */
    static function getConfigList($Id){
        return $classifyList = SupportClassConfig::select("b.*")
            ->leftJoin("classifyconfig as b","supportclassconfig.ConfigId","=","b.Id")
            ->where("supportclassconfig.ClassifyId",$Id)
            ->where("supportclassconfig.Deleted",0)->get()->toArray();
    }
}