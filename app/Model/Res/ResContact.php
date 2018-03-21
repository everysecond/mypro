<?php
/**
 * Created by PhpStorm.
 * User: chenglh
 * Date: 2016/8/11
 * Time: 10:31
 */
namespace Itsm\Model\Res;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Itsm\Model\Model;
use Itsm\Model\Usercenter\Userlogin;

class ResContact extends Model
{
    protected $connection = 'res';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'res_contact';

    protected $fillable = ['Name', 'CusInfId', 'Mobile', 'Email', 'Credentials', 'CredType'];

    static function translationDict($array, $Code, $DoMainCode)
    {
        $dicts = AuxDict::Select('Means', 'Code')->where('DomainCode', $DoMainCode)->get();
        foreach ($dicts as $dict) {
            $meansArray[$dict->Code] = $dict->Means;
        };
        foreach ($array as $item) {
            if ($item[$Code] != '' && $item[$Code] != null && isset($meansArray[$item[$Code]])) {
                $item[$Code] = $meansArray[$item[$Code]];
            } else {
                $item[$Code] = '';
            }
        }
        return $array;
    }

    static function translationStuff($array, $Code)
    {
        $cacheKey = "ITSM-AuxStuff";
        $cacheTime = Carbon::now()->addHours(8);

        static $allStuff = [];
        if (!$allStuff) {
            if (!Cache::tags("ITSM-AuxStuff")->has($cacheKey)) {
                $stuffs = AuxStuff::Select('Name', 'Id')->get();
                foreach ($stuffs as $stuff) {
                    $meansArray[$stuff->Id] = $stuff->Name;
                };
                Cache::tags("ITSM-AuxStuff")->put("ITSM-AuxStuff", json_encode($meansArray), $cacheTime);
            }
            $meanArr = json_decode(Cache::tags("ITSM-AuxStuff")->get($cacheKey), true);
            $allStuff = $meanArr;
        }
        foreach ($array as $item) {
            if ($item[$Code] != '' && $item[$Code] != null) {
                if (isset($allStuff[$item[$Code]])) {
                    if ($Code == 'OperationId' || $Code=='memberIds' || $Code=='collectUserId') {
                        $colors = '<span style="color:#f8ac59;">';
                        if (!empty($item['grpl0'])) {
                            $colors = '<span style="color:#f8ac59;">';
                        } else {
                            if (!empty($item['grpl1'])) {
                                $colors = '<span style="color:#1bcbab;">';
                            } else {
                                if (!empty($item['grpcenter'])) {
                                    $colors = '<span style="color:#4285f5;">';
                                }
                            }
                        }
                        $stuff = $colors . $allStuff[$item[$Code]] . '</span>';
                        $item[$Code] = $stuff;
                    } else {
                        $item[$Code] = $allStuff[$item[$Code]];
                    }

                } else {
                    if ($item[$Code] >= 500000) {
                        $user = Userlogin::Select('LoginId')->where('Id', $item[$Code])->first();
                        $user = isset($user) ? $user->LoginId : '';
                        $user = '<span style="color:red">' . $user . '</span>';
                        $item[$Code] = $user;
                    }
                }
            } else {
                $item[$Code] = '无';
            }
        }
        return $array;
    }

    static function translationTime($array, $Code)
    {
        foreach ($array as $item) {
            $item[$Code] = substr($item[$Code], 0, 10);
        }
        return $array;
    }

    static function translationStatus($array)
    {
        // 检查客户联系人名称、手机/固定电话、邮箱、证件类型、证件号码是否信息完整
        foreach ($array as $item) {
            if (!$item['Name'] || (!$item['Mobile'] && !$item['Tel']) || !$item['Email'] || !$item['CredType'] || !$item['Credentials']) {
                $item['checkStatus'] = '信息不完整';
            } else {
                $item['checkStatus'] = '信息完整';
            }
        }
        return $array;

    }
}