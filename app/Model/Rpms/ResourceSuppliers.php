<?php
/**
 * User: Lidz<lidz@51idc.com>
 * Date: 2017/11/13 16:18
 */
namespace Itsm\Model\Rpms;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Itsm\Model\Model;

class ResourceSuppliers extends Model
{
    protected $connection = 'res';
    public $primaryKey = 'id';
    public $timestamps = false;

    protected $table = 'res_suppliers';

    protected $fillable = ['id'];


    static function translationSupplier($array, $Code)
    {
        $cacheKey = "ITSM-Suppliers";
        $cacheTime = Carbon::now()->addHours(8);

        static $allSuppliers = [];
        if (!$allSuppliers) {
            if (!Cache::tags("ITSM-Suppliers")->has($cacheKey)) {
                $suppliers = ResourceSuppliers::Select('CompanyName', 'Id')->get();
                foreach ($suppliers as $supp) {
                    $meansArray[$supp->Id] = $supp->CompanyName;
                };
                Cache::tags("ITSM-Suppliers")->put("ITSM-Suppliers", json_encode($meansArray), $cacheTime);
            }
            $meanArr = json_decode(Cache::tags("ITSM-Suppliers")->get($cacheKey), true);
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
}


