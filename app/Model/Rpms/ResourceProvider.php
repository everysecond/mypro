<?php
/**
 * User: Lidz<lidz@51idc.com>
 * Date: 2017/11/13 16:18
 */
namespace Itsm\Model\Rpms;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Itsm\Model\Model;

class ResourceProvider extends Model
{
    protected $connection = 'rpms';
    public $primaryKey = 'id';
    public $timestamps = false;

    protected $table = 'resource_provider';

    protected $fillable = ['id'];

    static function translationSupplier($array, $Code)
    {
        $cacheKey = "RPMS-Providers";
        $cacheTime = Carbon::now()->addMinutes(2);

        static $allSuppliers = [];
        if (!$allSuppliers) {
            if (!Cache::tags("RPMS-Providers")->has($cacheKey)) {
                $suppliers = ResourceProvider::Select('providerName', 'id')->get();
                foreach ($suppliers as $supp) {
                    $meansArray[$supp->id] = $supp->providerName;
                };
                Cache::tags("RPMS-Providers")->put("RPMS-Providers", json_encode($meansArray), $cacheTime);
            }
            $meanArr = json_decode(Cache::tags("RPMS-Providers")->get($cacheKey), true);
            $allSuppliers = $meanArr;
        }
        foreach ($array as $item) {
            if ($item[$Code] != '' && $item[$Code] != null) {
                if (isset($allSuppliers[$item[$Code]])) {
                    $item[$Code] = $allSuppliers[$item[$Code]];
                } else {
                    $item[$Code] = '无';
                }
            } else {
                $item[$Code] = '无';
            }
        }
        return $array;
    }

    static function translationSupplierId($SupplierId)
    {
        $cacheKey = "RPMS-Providers";
        $cacheTime = Carbon::now()->addMinutes(2);

        static $allSuppliers = [];
        if (!$allSuppliers) {
            if (!Cache::tags("RPMS-Providers")->has($cacheKey)) {
                $suppliers = ResourceProvider::Select('providerName', 'id')->get();
                foreach ($suppliers as $supp) {
                    $meansArray[$supp->id] = $supp->providerName;
                };
                Cache::tags("RPMS-Providers")->put("RPMS-Providers", json_encode($meansArray), $cacheTime);
            }
            $meanArr = json_decode(Cache::tags("RPMS-Providers")->get($cacheKey), true);
            $allSuppliers = $meanArr;
        }
        $name = "";
        if (isset($allSuppliers[$SupplierId])) {
            $name = $allSuppliers[$SupplierId];
        } else {
            $name = '无';
        }
        return $name;
    }
}


