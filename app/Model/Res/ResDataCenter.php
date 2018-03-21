<?php
/**
 * Created by PhpStorm.
 * 数据中心表
 * User: lidz
 * Date: 2016/8/10
 * Time: 16:59
 */

namespace Itsm\Model\Res;

use Itsm\Model\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ResDataCenter extends Model
{
    protected $connection = 'res';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'res_datacenter';

    protected $fillable = ['DataCenterName', 'Address', 'Province','WebDisplay'];

    static function getDcName($dcId){
        if($dcId){
            $cacheKey = "RES-Datacenter";
            $cacheTime = Carbon::now()->addMinutes(2);

            static $allDatacenters = [];
            if (!$allDatacenters) {
                if (!Cache::tags("RES-Datacenter")->has($cacheKey)) {
                    $datacenters = ResDataCenter::Select('Id', 'DataCenterName')->get();
                    foreach ($datacenters as $supp) {
                        $meansArray[$supp->Id] = $supp->DataCenterName;
                    };
                    Cache::tags("RES-Datacenter")->put("RES-Datacenter", json_encode($meansArray), $cacheTime);
                }
                $meanArr = json_decode(Cache::tags("RES-Datacenter")->get($cacheKey), true);
                $allDatacenters = $meanArr;
            }
            if (isset($allDatacenters[$dcId])) {
                $dcName = $allDatacenters[$dcId];
            } else {
                $dcName = '无';
            }
            return $dcName;
        }else{
            return "无";
        }
    }
}