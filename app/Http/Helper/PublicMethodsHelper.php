<?php
/**
 * Public local functions
 * User: Wujiang <wuj@51idc.com>
 * Date: 8/11/16 10:26
 */
namespace Itsm\Http\Helper;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Input;
use Itsm\Model\Res\AuxStuff;
use Itsm\Model\Auth\Authorities;
use Itsm\Model\Res\ResCusInf;
use Itsm\Model\Usercenter\Handover;
use Itsm\Model\Usercenter\HandoverRelation;
use Itsm\Model\Usercenter\Support;
use Illuminate\Support\Facades\Cache;

class PublicMethodsHelper
{
    /** 工单类型 @var array */
    public static $supportStatusList = [
        'Todo'      => "待处理",
        'ReAppoint' => "待指派",
        'Appointed' => "已指派",
        'Doing'     => "处理中",
        'Suspend'   => "挂起中",
        'Done'      => "已处理",
        'Closed'    => "已关闭",
    ];
    /** 客户类型 @var array */
    protected static $customerList = [
        ''             => "所有客户",
        'Ordinary'     => "51IDC普通客户",
        'KeyCustomers' => "51IDC重要客户",
        'agent'        => "代理商客户",
    ];
    /**  响应时间 @var array */
    protected static $responseTime = [
        '2'  => '≤2min',
        '5'  => '2min-5min',
        '10' => '5min',
        '0'  => '未响应'
    ];

    /**
     * get client Ip
     * @return string
     */
    public static function getIp()
    {
        return Request()->ip();
    }

    /**
     * get http_user_agent
     * @return string
     */
    public static function getUserAgent()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";
    }

    /**
     * 工单状态转换 "todo=>处理中"
     * @param $status
     * @return string
     */
    public static function supportStatusConv($status)
    {
        $statusList = self::$supportStatusList;
        return Arr::get($statusList, $status, "未知");

    }

    /**
     * 获取工单状态列表
     * @return array
     */
    public static function getSupportStatusList()
    {
        return self::$supportStatusList;
    }

    /**
     * 获取工单条件-响应时间列表
     * @return array
     */
    public static function getResponseTimeList()
    {
        return self::$responseTime;
    }

    /**
     * 获取客户类型列表
     * @return array
     */
    public static function getCustomerList()
    {
        return self::$customerList;
    }

    /**
     * 通过code转换为客户类型名称
     * @param $customerCode
     * @return string
     */
    public static function getCustomerName($customerCode)
    {
        $customerList = self::$customerList;
        return Arr::get($customerList, $customerCode, "未知");
    }

    /**
     * 获取当前登录用户信息
     * @return type
     */
    public static function getUser()
    {
        return Request()->session()->get('user');
    }

    /**
     * 筛分出当前角色可查阅的所有工单列表数据返回的是未完成的sql
     * @param $userRole @角色
     * @param $supportList @传入的sql
     * @param $tableSupport @数据库及表名
     * @return mixed
     */
    public static function getRoleData($userRole, $supportList, $tableSupport)
    {
        $user = Request()->session()->get('user');
        switch ($userRole) {
            //服务台管理员
            case self::ROLE_DC_EMPLOYEE;
                $stuffDataCenterGroupId = ResUserGroup::select('UsersId')->where('UserId', $user->Id)->get();
                $supportList = $supportList->where(function ($supportList) use (
                    $tableSupport,
                    $stuffDataCenterGroupId
                ) {
                    $supportList->whereIn($tableSupport . '.DataCenterId', $stuffDataCenterGroupId)
                        ->orwhereIn($tableSupport . '.DatacenterTwoId', $stuffDataCenterGroupId);
                });
                break;
            //其他人员
            case self::OTHER;
                $supportList = $supportList->where(function ($supportList) use ($tableSupport, $user) {
                    $supportList->where($tableSupport . '.ChargeUserId', $user->Id)
                        ->orWhere($tableSupport . '.ChargeUserTwoId', $user->Id)
                        ->orwhere('AsuserId', $user->Id);
                });
                break;
        }
        return $supportList;
    }

    /**
     * 发送数据
     * @param String $url 请求的地址
     * @param Array $header 自定义的header数据
     * @param Array $content POST的数据
     * @return String
     */
    public static function tocurl($url, $header, $content)
    {
        $ch = curl_init();
        if (substr($url, 0, 5) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($content));
        $response = curl_exec($ch);
        if ($error = curl_error($ch)) {
            die($error);
        }
        curl_close($ch);
        return $response;
    }

    /**
     * 获取部门成员Id数组
     * @param $departId
     * @return array
     */
    public static function getDepartStuff($departId)
    {
        $departMembers = [];
        if ($departId) {
            $departMembersId = AuxStuff::select('Id')
                ->where(function ($departMembersId) use ($departId) {
                    $departMembersId->where('Depart', $departId)
                        ->orwhere('second_dept', $departId);
                })
                ->where('InValidate', '0')
                ->get()->toArray();
            foreach ($departMembersId as $item) {
                $departMembers[] = $item['Id'];
            }
        }
        return $departMembers;
    }

    /**
     * 获取某部门含有某权限的所有人员Id，转化为字符串
     * @param $role
     * @param $depart
     * @return string
     */
    public static function getDepartStuffName($depart, $role)
    {

        $hasRoleIds = Authorities::select('b.Id', 'b.Name')
            ->Join('res.aux_stuff as b', 'b.Login', '=', 'auth.authorities.username')
            ->raw("LEFT JOIN res.aux_dict as c on b.Depart=c.`Code` and  c.DomainCode='DepartType'")
            ->Raw("LEFT JOIN res.aux_dict as d on b.second_dept=d.`Code` and  d.DomainCode='second_dept'")
            ->where(function ($stuffIds) use ($depart) {
                $stuffIds->where('b.Depart', $depart)
                    ->orwhere('b.second_dept', $depart);
            });
        if ($role) {
            $hasRoleIds->where('auth.authorities.authority', $role);
        }

        $hasRoleIds = $hasRoleIds->get()->toArray();
        return $hasRoleIds;
    }

    /**
     * 获取某部门所有人员Id，转化为字符串
     * @param $depart
     * @return string
     */
    public static function getDepartStuffNames($depart)
    {

        $ids = AuxStuff::select('aux_stuff.Id', 'aux_stuff.Name')
            ->raw("LEFT JOIN aux_dict as c on aux_stuff.Depart=c.`Code` and  c.DomainCode='DepartType'")
            ->Raw("LEFT JOIN aux_dict as d on aux_stuff.second_dept=d.`Code` and  d.DomainCode='second_dept'")
            ->where(function ($stuffIds) use ($depart) {
                $stuffIds->where('aux_stuff.Depart', $depart)
                    ->orwhere('aux_stuff.second_dept', $depart);
            });
        $ids = $ids->get()->toArray();
        return $ids;
    }

    /**
     * 获取超时时间,自动过滤周六周日
     * @param $day 时间戳
     */
    public static function getTimeOutTimeWithWeek($times, $days = 1, $returnType = "date")
    {
        $now = time();
        $checkTime = time();
        while ($days--) {
            if (date("N", $now) == 7 || date("N", $now) == 6) {
                $checkTime += 24 * 3600;
            }
            $now = $now - 24 * 3600;

        }
        if ($returnType == "date") {
            return date("Y-m-d H:i:s", $checkTime - $times);
        } else {
            return $checkTime - $times;
        }
    }

    /**
     * 获取2个时间段的排除周六周日的最终时间
     * @param $day1
     * @param string $day2
     * @return bool|int|string
     */
    public static function diffBetweenTwoDays($day1, $day2 = "")
    {
        if (!$day2) {
            $day2 = time();
        }
        $day1 = strtotime($day1);
        if ($day2 <= $day1) {
            return 0;
        }
        $diffNow = $day2;
        while (true) {
            if ($day2 > $day1 && (date("N", $day2) == 7 || date("N", $day2) == 6)) {
                $diffNow -= 24 * 3600;
            }
            $day2 -= 24 * 3600;
            if ($day2 < $day1) {
                break;
            }
        }
        return $diffNow;
    }

    /**
     * 获取事件的通知类型以及抄送人
     */
    public static function getEventPInfoAndCCIdsById($id)
    {
        $result = [
            'ccIds'      => "",
            'chargerId'      => "",
            'remindType' => ""
        ];
        /**
         * 获取父级ID
         */
        $parentId = HandoverRelation::where("handEventId", $id)->where("inValidate", 0)->first();
        if (empty($parentId)) {
            return [];
        }
        /**
         * 获取父级信息
         */
        $parentInfo = Handover::where("inValidate", 0)->find($parentId->handoverId);
        if ($parentInfo->chargerId) {
            $result['chargerId'] = $parentInfo->chargerId;
        }
        if ($parentInfo->ccIds) {
            $result['ccIds'] = $parentInfo->ccIds;
        }
        if ($parentInfo->remindType) {
            $result['remindType'] = trim($parentInfo->remindType, ",");
        }
        return $result;

    }

    /**
     * 去除html和空格
     * @param $string
     * @param $sublen
     * @return string
     */
    public static  function removeStrHtml($string)
    {
        $string = strip_tags($string);
        $string = trim($string);
        $string = str_replace("\t", "", $string);
        $string = str_replace("\r\n", "", $string);
        $string = str_replace("\r", "", $string);
        $string = str_replace("\n", "", $string);
        $string = str_replace(" ", "", $string);
        $string = str_replace("　", "", $string);
        return trim($string);
    }

    /**
     * 将code转译成中文
     * @param $list
     * @param $arr
     * @param $Code
     * @return mixed
     */
    public static function codeToChinese($list,$arr,$Code){
        foreach ($list as $item) {
            if (isset($arr[$item->{$Code}])) {
                $item->{$Code} = $arr[$item->{$Code}];
            }
        }
        return $list;
    }
    //获取工单负责人
    public static function chargeUser()
    {
        $users = \DB::select("select distinct(Name)as name,a.Id from res.aux_stuff  as a RIGHT JOIN usercenter.support as b on (a.Id = b.ChargeUserId or a.Id = b.ChargeUserTwoId) WHERE name is not NULL and a.Permit='yes'ORDER BY a.Id ASC ");
        return $users;

    }
    //获取工单指派人/跟踪人
    public static function asUser()
    {
        $user = AuxStuff::selectRaw("distinct(Name),res.aux_stuff.Id")
            ->rightJoin('usercenter.support as a','a.AsuserId','=','res.aux_stuff.Id')
            ->whereNotNull('res.aux_stuff.Name')
            ->where('res.aux_stuff.Permit','yes')
            ->orderBy('res.aux_stuff.Id','asc')
            ->get()->toArray();
        return $user;

    }

    //过滤html代码
    public static function htmlToSafe($html){
        $html= preg_replace("/javascript/si","",html_entity_decode(trim($html)),-1);
        return strip_tags(trim($html), '<br><p><img><b><u><hr><span><a>');
    }
    public static function getDeparts()
    {
        $dept = \DB::select("select DISTINCT(d.Means),b.Depart,c.Means as MeansOne,b.second_dept,d.Means as MeansTwo from  res.aux_stuff as b LEFT JOIN res.aux_dict as c on b.Depart=c.`Code` and  c.DomainCode='DepartType' LEFT JOIN res.aux_dict as d on b.second_dept=d.`Code` and  d.DomainCode='second_dept'");
        $oneGroup = [];
        if ($dept) {
            /**
             * 取出一级部门
             */
            foreach ($dept as $item) {
                if (!empty($item->second_dept)) {
                    $oneGroup["{$item->Depart}"]['name'] = $item->MeansOne;
                }
            }
            /**
             * 取出一级部门下面的二级部门并且返回
             */
            foreach ($dept as $childItem) {
                foreach ($oneGroup as $oneItem) {
                    if ($oneItem['name'] == $childItem->MeansOne && $childItem->MeansTwo != "") {
                        $oneGroup["{$childItem->Depart}"]['child']["{$childItem->second_dept}"] = $childItem->MeansTwo;
                    } else {
                        if ($childItem->MeansTwo != null) {
                            $oneGroup["{$childItem->Depart}"]['name'] = $childItem->MeansOne;
                        }
                    }
                }
            }

        }
        return $oneGroup;
    }

    //工单内容过滤
    public static function translationBody($list){
        foreach($list as $support){
            $support->Body = str_replace('src="/usercenter', "src=\"".env('JOB_URL2')."/usercenter", $support->Body); //工单内容过滤
            $support->Body = strip_tags($support->Body, "<br><p><span><img><strong><em><u>"); //工单内容过滤
        }
        return $list;
    }

    //手机号调整
    public static function checkMobile($mobile){
        $newMobile = str_replace("+86","",$mobile);
        $newMobile = str_replace("86-","",$newMobile);
        return $newMobile;
    }

    //验证是否为空
    public static function inValidateEmpty($str){
        $str = self::removeStrHtml($str);
        if(empty($str) || $str == "") return false;return true;
    }

    public static function getGroupMembers($id){
        $list = ThirdCallHelper::getGroupMembers($id)->toArray();
        $list = array_map(function ($arr) {
            $cachekey = ITSM_LOGIN . $arr["UserId"];
            $arr["Name"] = $arr["Name"] . "(" . (Cache::has($cachekey) ? "在线" : "离线") . ")";
            return $arr;
        }, $list);
        return $list;
    }
}