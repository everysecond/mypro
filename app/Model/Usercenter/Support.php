<?php

/**
 * Created by PhpStorm.
 * User: Lidz
 * Date: 2016/6/13
 * Time: 18:57
 */
namespace Itsm\Model\Usercenter;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Itsm\Model\Model;
use Itsm\Model\Res\AuxDict;
use Itsm\Model\Res\AuxStuff;
use Itsm\Model\Res\ResCusInf;
use Itsm\Model\Res\ResStuff;
use Illuminate\Support\Facades\Cache;

class Support extends Model
{
    protected $connection = 'usercenter';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'support';

    protected $fillable = ['Id,app_uid'];

    const DISABLED_YES = 1;
    const DISABLED_NO = 0;

    function supSource()
    {//工单来源
        return $this->belongsTo('Itsm\Model\Res\Dict', 'Source', 'Code')
            ->where('DomainCode', 'supportSource');
    }

    function supWorkSheetTypeOne()
    {//工单类型三级分类
        return $this->belongsTo('Itsm\Model\Res\Dict', 'ClassInficationOne', 'Code')
            ->where('DomainCode', 'WorkSheetTypeOne');
    }

    function supClassInfication()
    {//工单类型二级分类
        return $this->belongsTo('Itsm\Model\Res\Dict', 'ClassInfication', 'Code');
    }

    function CusName()
    {//工单类型分类
        return $this->belongsTo('Itsm\Model\Res\ResCusInf', 'CustomerInfoId', 'Id');
    }

    function supStatus()
    {//工单状态
        return $this->belongsTo('Itsm\Model\Res\AuxDict', 'Status', 'Code')
            ->where('DomainCode', 'WorksheetStatus');
    }

    function supContact()
    {//工单联系人
        return $this->belongsTo('Itsm\Model\Res\ResContact', 'ContactId', 'Id');
    }

    function chargeUser()
    {//工单第一负责人
        return $this->belongsTo('Support\Model\Res\AuxStuff', 'ChargeUserId', 'Id');
    }

    function chargeGroup()
    {//工单第一负责人工作组
        return $this->belongsTo('Itsm\Model\Res\ResUsers', 'DatacenterId', 'Id');
    }

    function chargeUserTwo()
    {//工单第二负责人
        return $this->belongsTo('Itsm\Model\Res\Stuff', 'ChargeUserTwoId', 'Id');
    }

    function createUser($createUserId)
    {//工单创建人
        if ($createUserId > 500000) {
            $createUser = Userlogin::where('Id', $createUserId)->first();
            $createUser = isset($createUser) ? $createUser->LoginId : '';
        } else {
            $createUser = AuxStuff::where('Id', $createUserId)->first();
            $createUser = isset($createUser) ? $createUser->Name : '';
        }
        return $createUser;
    }

    /**
     * 获取 code 的 Means
     * @param $array
     * @param $Code
     * @param $DoMainCode
     * @return mixed
     */
    static function translationDict($array, $Code, $DoMainCode)
    {
        $cacheKey = "ITSM-AuxDict-" . $DoMainCode;
        $cacheTime = Carbon::now()->addHours(8);
        //缓存优化
        static $dictArr = [];
        if (!isset($dictArr[$DoMainCode])) {
            if (!Cache::tags("ITSM-AuxDict")->has($cacheKey)) {
                $dictList = AuxDict::Select('Means', 'Code')->where('DomainCode', $DoMainCode)->get();
                foreach ($dictList as $dict) {
                    $meansArray[$dict->Code] = $dict->Means;
                };
                Cache::tags("ITSM-AuxDict")->put($cacheKey, json_encode($meansArray), $cacheTime);
            }
            $cacheMeansArr = json_decode(Cache::tags("ITSM-AuxDict")->get($cacheKey), true);
            $dictArr[$DoMainCode] = $cacheMeansArr;
        }
        foreach ($array as $item) {
            if ($item[$Code] != '' && $item[$Code] != null && isset($dictArr[$DoMainCode][$item[$Code]])) {
                $item[$Code] = $dictArr[$DoMainCode][$item[$Code]];
            } else {
                $item[$Code] = '无';
            }
        }
        return $array;
    }

    /**
     * 直接返回对应code的Means
     * @param $Code
     * @param $DoMainCode
     * @return string
     */
    static function explainDict($Code, $DoMainCode)
    {
        $cacheKey = "ITSM-AuxDict-" . $DoMainCode;
        $cacheTime = Carbon::now()->addHours(8);
        //缓存优化
        static $dictArr = [];
        if (!isset($dictArr[$DoMainCode])) {
            if (!Cache::tags("ITSM-AuxDict")->has($cacheKey)) {
                $dictList = AuxDict::Select('Means', 'Code')->where('DomainCode', $DoMainCode)->get();
                foreach ($dictList as $dict) {
                    $meansArray[$dict->Code] = $dict->Means;
                };
                Cache::tags("ITSM-AuxDict")->put($cacheKey, json_encode($meansArray), $cacheTime);
            }
            $cacheMeansArr = json_decode(Cache::tags("ITSM-AuxDict")->get($cacheKey), true);
            $dictArr[$DoMainCode] = $cacheMeansArr;
        }
            if ($Code != '' && isset($dictArr[$DoMainCode][$Code])) {
                return $dictArr[$DoMainCode][$Code];
            } else {
                return "未定义";
            }
    }

    //数据字典精确查询
    static function translationMeans($DoMainCode, $Code)
    {
        $dicts = AuxDict::Select('Means', 'Code')->where('DomainCode', $DoMainCode)->where('Code', $Code)->first();
        if (null != $dicts && null != $dicts['Means']) {
            return $dicts['Means'];
        } else {
            return '';
        }
    }


    /**
     * 获取客户名称
     * @param $array
     * @param $Code
     * @return mixed
     */
    static function translationCusName($array, $Code)
    {
        $cacheKey = "CUST-";
        $cacheTime = Carbon::now()->addHours(8);

        static $staticCusArr = [];

        foreach ($array as $item) {
            if ($item[$Code] != '' && $item[$Code] != null) {
                if (isset($staticCusArr[$item[$Code]]) && !empty($staticCusArr[$item[$Code]])) {
                    $item['CusName'] = $staticCusArr[$item[$Code]];
                    continue;
                }
                //备用
                if (!Cache::has($cacheKey . $item[$Code])) {
                    $cusInfoRow = ResCusInf::select('*')->where('Id',
                        $item[$Code])->first();
                    if ($cusInfoRow) {
                        $cacheInfo = json_encode($cusInfoRow->toArray());
                        Cache::put($cacheKey . $item[$Code], $cacheInfo, $cacheTime);
                    }

                }

                $cusInfoArr = json_decode(Cache::get($cacheKey . $item[$Code]), true);
                if (empty($cusInfoArr)) {
                    $cusInfoRow = ResCusInf::select('*')->where('Id',
                        $item[$Code])->first();
                    $cusInfoArr = $cusInfoRow->toArray();
                }
                $item['CusName'] = Arr::get($cusInfoArr, 'CusName', "");
                $staticCusArr[$item[$Code]] = $item['CusName'];
            }
        }
        return $array;
    }

    /**
     * 获取处理时长
     * 计算公式(已处理时长＝已处理时间（如果没有已处理时间，按当前时间）－工单创建时间－挂起时长)
     * @param $supportList
     * @param $db
     * @return mixed
     */
    public static function translationOverTime($supportList, $db)
    {
        foreach ($supportList as $item) {
            $ProcessTime = strtotime($item['ProcessTs']);
            $CreateTime = strtotime($item['Ts']);
            $HangUpTime = $item['hangupDuration'];//转换为秒
            if (is_null($item['ProcessTs'])) {
                $ProcessTime = time();
            }
            $overTime = $ProcessTime - $CreateTime - $HangUpTime;
            $overTime = $overTime < 0 ? 0 : $overTime;
            $item['overTime'] = $overTime;

        }
        return $supportList;
    }

    static function overTimeList($supportList, $db, $condition1, $condition2)
    {//用TIMESTAMPDIFF
        $supportList->where(function ($supportList) use ($db, $condition1, $condition2) {
            $supportList->where(function ($supportList) use ($db, $condition1, $condition2) {
                $supportList->WhereNull($db . '.ProcessTs');
                $supportList->where(function ($supportList) use ($db, $condition1, $condition2) {
                    $supportList->WhereNull($db . '.hangupDuration')
                        ->whereRaw("((unix_timestamp(NOW()) - $db.Ts)/60 - c.ENG)/60 $condition1 AND ((unix_timestamp(NOW()) - $db.Ts)/60 - c.ENG)/60 $condition2");
                })->orwhere(function ($supportList) use ($db, $condition1, $condition2) {
                    $supportList->WhereNotNull($db . '.hangupDuration')
                        ->whereRaw("((unix_timestamp(NOW()) - $db.Ts)/60 - c.ENG - $db.hangupDuration/60)/60 $condition1 AND ((unix_timestamp(NOW()) - $db.Ts)/60 - c.ENG - $db.hangupDuration/60)/60 $condition2");
                });
            })->orwhere(function ($supportList) use ($db, $condition1, $condition2) {
                $supportList->WhereNotNull($db . '.ProcessTs');
                $supportList->where(function ($supportList) use ($db, $condition1, $condition2) {
                    $supportList->WhereNull($db . '.hangupDuration')
                        ->whereRaw("(($db.ProcessTs - $db.Ts)/60 - c.ENG)/60 $condition1 AND (($db.ProcessTs - $db.Ts)/60 - c.ENG)/60 $condition2");
                })->orwhere(function ($supportList) use ($db, $condition1, $condition2) {
                    $supportList->WhereNotNull($db . '.hangupDuration')
                        ->whereRaw("(($db.ProcessTs - $db.Ts)/60 - c.ENG - $db.hangupDuration/60)/60 $condition1 AND (($db.ProcessTs - $db.Ts)/60 - c.ENG - $db.hangupDuration/60)/60 $condition2");
                });
            });;
        });
        return $supportList;
    }

}