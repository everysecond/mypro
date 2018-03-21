<?php
/**
 * Call third party in this class
 * User: Wujiang <wuj@51idc.com>
 * Date: 8/10/16 12:00
 */
namespace Itsm\Http\Helper;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Itsm\Model\Proddb\ProdOffer;
use Itsm\Model\Proddb\ProdType;
use Itsm\Model\Res\AuxDict;
use Itsm\Model\Res\AuxStuff;
use Itsm\Model\Res\MyQuote;
use Itsm\Model\Res\ResContact;
use Itsm\Model\Res\ResCusInf;
use Itsm\Model\Res\ResDataCenter;
use Itsm\Model\Res\ResDev;
use Itsm\Model\Res\ResUserGroup;
use Itsm\Model\Res\ResUsers;
use Itsm\Model\Rpms\ResourceContract;
use Itsm\Model\Rpms\ResourceProd;
use Itsm\Model\Rpms\ResourceProvider;
use Itsm\Model\Rpms\ResourceSuppliers;
use Itsm\Model\Rpms\ResourceType;
use Itsm\Model\Usercenter\Support;
use Itsm\Model\Usercenter\Userlogin;
use Itsm\Model\Usercenter\UserSupport;

class ThirdCallHelper
{
    /**
     * 获取员工姓名
     * @param $stuffId
     * @return mixed
     */
    public static function getStuffName($stuffId)
    {
        $stuff = AuxStuff::where('Id', $stuffId)->first();

        return $stuff != null ?$stuff->Name:"";
    }

    /**
     * 获取数据字典对应名称
     * @param $domain
     * @param $domainCode
     * @param $code
     * @return mixed
     */
    public static function getDictMeans($domain, $domainCode, $code)
    {
        return AuxDict::where('Domain', $domain)
            ->where('DomainCode', $domainCode)
            ->where('Code', $code)->value('Means');
    }

    /**
     * 获取部门名称
     * @param $code
     * @return mixed
     */
    public static function getDepartMeans($code)
    {
        return AuxDict::where('Code', $code)->value('Means');
    }

    /**
     *获取对应分类数组数据
     * @param $Domain 分类类型名称
     * @param $DomianCode 分类名称对应Code
     * @param $Eng  默认为空
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getDictArray($Domain, $DomianCode,$Eng="")
    {
        $arr = AuxDict::select('Code', 'Means')
            ->where('Domain', $Domain)
            ->where('DomainCode', $DomianCode)
            ->where(function ($arr) {
                $arr->whereNull('Validate')
                    ->orwhere('Validate', '<>', AuxDict::DISABLED_YES);
            });
        if($Eng!="")$arr->where("Eng",$Eng);
        return $arr->orderByRaw("Means = '业务开通申请' desc, Means='其他' desc")->get();
    }

    /**
     * 获取工单工作组
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getWorkGroups()
    {
        return ResUsers::select('Id', 'UsersName', 'chargeGroup')
            ->where('isGrouping', ResUsers::DISABLED_YES)
            ->orderBy('sort', 'desc')//按照sort字段降序排列
            ->get();
    }

    /**
     * 获取对应工作组的所有成员ID和Name
     * @param $GroupId 工作组Id
     * @return array|\Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getGroupMembers($GroupId)
    {
        return $arr = ResUserGroup::select('res_usergroup.UserId', 'aux_stuff.Name')
            ->leftJoin('aux_stuff', 'aux_stuff.Id', '=', 'res_usergroup.UserId')
            ->where('UsersId', $GroupId)
            ->where(function ($arr) {
                $arr->whereNull('aux_stuff.InValidate')
                    ->orwhere('aux_stuff.InValidate', '<>', ResUserGroup::DISABLED_YES);
            })
            ->get();
    }

    /**
     * 获取所有数据中心Id和DataCenterName
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getDataCenter($type = 'IDC')
    {
        return ResDataCenter::select('Id', 'DataCenterName')
            ->where('WebDisplay', 'yes')->where('type', $type)
            ->get();
    }

    /**
     * 获取对应客户所有联系人
     * @param $CusInfId 客户Id
     * @return array|\Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getContacts($CusInfId)
    {
        return $arr = ResContact::select('Id', 'Name', 'Mobile', 'Tel', 'Email', 'Credentials', 'CredType')
            ->where('CusInfId', $CusInfId)
            ->where(function ($arr) {
                $arr->whereNull('InValidate')
                    ->orwhere('InValidate', '<>', ResUserGroup::DISABLED_YES);
            })
            ->get();
    }

    /**
     * 获取客户所有登录账号
     * @param $CusId
     * @return array|\Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getLoginList($CusId){
        return $loginList = Userlogin::select("userlogin.*")
            ->leftJoin("usercenter.cuslogin as b","b.UserLoginId","=","userlogin.Id")
            ->where("b.CusInfId",$CusId)
            ->get();
    }

    /**
     * 获取工单编号的对应客户名与id
     * @param $supportId 工单编号
     * @return array|\Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getCusId($supportId)
    {
        return $arr = ResCusInf::select('res_cusinf.Id', 'res_cusinf.CusName')
            ->leftJoin('usercenter.support as A', 'res_cusinf.Id', '=', 'A.CustomerInfoId')
            ->where('A.Id', $supportId)
            ->where(function ($arr) {
                $arr->whereNull('res_cusinf.InValidate')
                    ->orwhere('res_cusinf.InValidate', '<>', ResCusInf::DISABLED_YES);
            })
            ->get();
    }

    /**
     * 获取工单编号的对应客户名
     * @param $cusId 客户Id
     * @return array|\Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getCusName($cusId)
    {
        return $arr = ResCusInf::where('Id', $cusId)->value('CusName');
    }

    /**
     * 获取对应联系人信息
     * @param $ContactId
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */

    public static function getContactInf($ContactId)
    {
        return $arr = ResContact::select('aux_dict.Means', 'res_contact.Name', 'res_contact.Mobile', 'res_contact.Tel', 'res_contact.Email', 'res_contact.Credentials',
            'res_contact.CredType','res_contact.LoginId','res_contact.UserLoginId','b.Id as UserLoginId2')
            ->leftJoin('aux_dict', 'res_contact.CredType', '=', 'aux_dict.Code')
            ->leftJoin('usercenter.userlogin as b', 'b.LoginId', '=', 'res_contact.LoginId')
            ->where('res_contact.Id', $ContactId)
            ->where(function ($arr) {
                $arr->whereNull('res_contact.InValidate')
                    ->orwhere('res_contact.InValidate', '<>', ResUserGroup::DISABLED_YES);
            })
            ->first();
    }

    /**
     * 获取关联设备信息
     * @paran $CustId 联系人Id
     * @return array|\Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getDev($CustId)
    {
        return $arr = ResDev::select('res_dev.DevId', 'res_dev.DevType', 'res_dev.devIpaddrone',
            'res_datacenter.DataCenterName')
            ->leftJoin('res_datacenter', 'res_datacenter.Id', '=', 'res_dev.DataCenterId')
            ->where('CustomerId', $CustId)
            ->where(function ($arr) {
                $arr->whereNull('InValidate')
                    ->orwhere('InValidate', '<>', ResDev::DISABLED_YES);
            })
            ->get();
    }

    /**
     * 通过客户名模糊查询获取客户信息
     * @paran $CusName 客户名
     * @return array|\Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function getCusInf($CusName)
    {
        $preArray = ResCusInf::selectRaw("res.res_cusInf.CusName,res.res_cusInf.Id,res.res_cusInf.Authorization");
        $isIp = preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $CusName);
        if(!$isIp)
        {
            $arr = $preArray
                ->where('res.res_cusInf.CusName', 'like', '%' . $CusName . '%')
                ->where(function ($arr) {
                    $arr->whereNull('res.res_cusInf.InValidate')
                        ->orwhere('res.res_cusInf.InValidate', '<>', ResCusInf::DISABLED_YES);
                })
                ->take(5)
                ->get();
        }
        else{
            $arr = ResCusInf::selectRaw("distinct(res.res_cusInf.CusName),res.res_cusInf.Id,res.res_cusInf.Authorization")
                ->leftJoin('usercenter.support as a','a.CustomerInfoId','=','res.res_cusInf.Id')
                ->where('a.devIPAddr', 'like', '%' . $CusName . '%')
                ->where(function ($arr) {
                    $arr->whereNull('res.res_cusInf.InValidate')
                        ->orwhere('res.res_cusInf.InValidate', '<>', ResCusInf::DISABLED_YES);
                })
                ->take(5)
                ->get();
        }
        return $arr;
    }

    public static function getCusInfName($CusName)
    {
        return $arr = ResCusInf::where('CusName', 'like', '%' . $CusName . '%')
            ->where(function ($arr) {
                $arr->whereNull('InValidate')
                    ->orwhere('InValidate', '<>', ResCusInf::DISABLED_YES);
            })
            ->take(5)->get();
    }

    public static function getProdType($search,$code,$name)
    {
        return $arr = ResourceType::whereIn('status',[0,1])
            ->where(function ($arr) use ($search) {
                $arr->where('typeCode', 'like', '%' . $search . '%')
                    ->orwhere('typeName', 'like', '%' . $search . '%');
            })->where(function ($arr) use ($search) {
                $arr->whereNull('parentTypeCode')
                    ->orwhere('parentTypeCode',"");
            })
            ->where("typeCode","!=",$code)
            ->where("typeName","!=",$name)
            ->take(5)->get();
    }

    public static function getProdName($prodName)
    {
        return $arr = ProdOffer::where('prodName', 'like', '%' . $prodName . '%')
            ->orderBy("ts","desc")
            ->take(5)->get();
    }

    public static function getProdTypeName($code){
        $ret =  ResourceType::select("typeName")->where("typeCode",$code)->first();
        return $ret?$ret->typeName:"";
    }

    public static function getRelateProdTypeName($code){
        $type =  ProdType::select("TypeName")->where("TypeCode",$code)->first();
        return $type?$type->TypeName:"";
    }

    /**
     * 获取工单信息
     * @param $SupportId 工单编号id
     * @return \Illuminate\Database\Eloquent\Model|null|static[]
     */
    public static function getSupportInf($SupportId)
    {
        return UserSupport::select('usercenter.support.Id', 'usercenter.support.Title', 'usercenter.support.Body',
            'res.aux_stuff.Name', 'usercenter.support.dataCenter')
            ->where('usercenter.support.Id', $SupportId)
            ->leftJoin('res.aux_stuff', 'usercenter.support.OperationId', '=', 'res.aux_stuff.Id')
            ->get();
    }

    //转换Code与Means值，获取联系人身份是商务联系人还是技术联系人
    public static function translationDict($array, $Code, $DoMainCode)
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

    /**
     * 获取客户+客户经理及其商业/技术联系人信息
     * @param $CusInfId 客户Id
     * @return \Illuminate\Database\Eloquent\Model|null|static[]
     */

    public static function getCusDictInf($CusInfId)
    {
        $CusInfo = ResCusInf::select('res_cusinf.Id', 'res_cusinf.CusName', 'res_cusinf.CusType', 'res_cusinf.Tel',
            'res_cusinf.Email', 'res_cusinf.Address', 'res_cusinf.Sell', 'aux_stuff.Name')
            ->where('res_cusinf.Id', $CusInfId)
            ->leftJoin('aux_stuff', 'res_cusinf.Sell', '=', 'aux_stuff.Id')
            ->where(function ($arr) {
                $arr->whereNull('res_cusinf.InValidate')
                    ->orwhere('res_cusinf.InValidate', '<>', ResCusInf::DISABLED_YES);
            })
            ->get()->toArray();
        $CusDictInfo['cusinf'] = $CusInfo;
        $contacts = ResContact::where('CusInfId', $CusInfId)->where('InValidate', '0')->get();
        $contacts = self::translationDict($contacts, 'ConType', 'contactType')
            ->toArray();
        $CusDictInfo['contacts'] = $contacts;
        var_dump($CusDictInfo);
    }

    /**
     * 传入客户Id获取客户拥有的所有服务数据
     * @param $cusId
     * @return array
     */
    public static function getCusService($cusId)
    {
        $cacheKey = "ITSM-Identity-" . $cusId;
        $cacheTime = Carbon::now()->addHour(1);
        if (!Cache::tags("ITSM-Identity")->has($cacheKey)) {
            $servicesArr = [
                "isVIP"      => false, //vip
                "isAType"    => false, //A类
                "isMAN"      => false, //管家
                "isDSF"      => false,  //第三方
                "isNewCus"      => false,  //17年5月后新客户
                "MANdetails" => "", //管家服务详情
                "DSFdetails" => "",  //第三方服务详情
                "Memo"       => ""//客户备注
            ];
            $cusInfo = ResCusInf::select('CusImportanceType', 'cusClassify','special','CreateTs')->where('Id', $cusId)->first();
            if (!empty($cusInfo)) {
                $servicesArr['isVIP'] = $cusInfo->CusImportanceType == 'KeyCustomers' ? true : false;
                $servicesArr['isAType'] = $cusInfo->cusClassify == 'A' ? true : false;
                $servicesArr['Memo'] = $cusInfo->special;
                $createTs = strtotime($cusInfo->CreateTs);
                $time = strtotime("2017-05-01 00:00:00");
                $servicesArr['isNewCus'] = $createTs>=$time?true:false;
            }
            $services = MyQuote::select('res.myquote.ProdTypeOne', 'a.CusInfId', 'b.TypeName', 'myquote.SubType')
                ->join('res.res_cusinfcontract as a', 'res.myquote.OrderId', '=', 'a.Id')
                ->leftJoin('proddb.prodtype as b', 'res.myquote.ProdTypeTwo', '=', 'b.TypeCode')
                ->where('a.OrderType', 'formalOrder')
                ->whereNotNull('a.LoadedTS')
                ->whereNull('a.DestoryTs')
                ->where('a.CusInfId', $cusId)
                ->groupBy('res.myquote.ProdTypeOne')
                ->groupBy('a.CusInfId')
                ->groupBy('res.myquote.ProdTypeTwo')
                ->get();
            foreach ($services as $service) {
                if ($service->ProdTypeOne == 'MAN') {
                    $servicesArr['isMAN'] = true;
                    $servicesArr['MANdetails'] .= $service->TypeName . ':' . $service->SubType . "<br/>";
                } elseif ($service->ProdTypeOne == 'DSF') {
                    $servicesArr['isDSF'] = true;
                    $servicesArr['DSFdetails'] .= $service->TypeName . ':' . $service->SubType . "<br/>";
                }
            }
            if (Cache::tags("ITSM-Identity")->put($cacheKey, json_encode($servicesArr), $cacheTime)) {
                return $servicesArr;
            }
        }
        return json_decode(Cache::tags("ITSM-Identity")->get($cacheKey), true);;
    }

    /**
     * 获取工单身份标识
     * @param $result
     * @return mixed
     */
    public static function translationIdentity($result)
    {
        if (!empty($result)) {
            foreach ($result as $item) {
                $item["identity"] = static::getCusService($item["CustomerInfoId"]);
            }
        }
        return $result;
    }

    public static function identity($result)
    {
        if (!empty($result)) {
            foreach ($result as $item) {
                $item["identity"] = static::getCusService($item["cusId"]);
            }
        }
        return $result;
    }

    /**
     * 获取工单预计处理时长
     * @param $supportId @工单Id
     * @return mixed|string
     */
    public static function getPredictTs($supportId)
    {
        $support = Support::select('a.Eng')
            ->leftJoin('res.aux_dict as a', 'a.Code', '=', 'usercenter.support.ClassInficationOne')
            ->where('a.Domain', '工单类型')
            ->where('a.Eng', '<>', "N/A")
            ->where('a.DomainCode', 'WorkSheetTypeOne')
            ->where('usercenter.support.Id', $supportId)
            ->first();
        if (!empty($support)) {
            $predictTs = $support->Eng;
        } else {
            $predictTs = '';
        }
        return $predictTs;
    }

    public static function translationPreDictTs($supportList)
    {
        $cacheKey = "ITSM-aux-dict-";
        foreach ($supportList as $item) {
            if (!Cache::tags("ITSM-aux-dict")->has($cacheKey . $item->Id)) {
                $support = Support::select('a.Eng')
                    ->leftJoin('res.aux_dict as a', 'a.Code', '=', 'usercenter.support.ClassInficationOne')
                    ->where('a.Eng', '<>', "N/A")
                    ->where('a.DomainCode', 'WorksheetTypeOne')
                    ->where('usercenter.support.Id', $item->Id)
                    ->first();
                if ($support) {
                    Cache::tags("ITSM-aux-dict")->put($cacheKey . $item->Id, json_encode($support->toArray()),
                        Carbon::now()->addHour());
                } else {
                    $item['predictTs'] = "";
                    continue;
                }
            }
            //get cache
            $cacheArr = json_decode(Cache::tags("ITSM-aux-dict")->get($cacheKey . $item->Id), true);
            $item['predictTs'] = $cacheArr['Eng'];
        }
        return $supportList;
    }

    /**
     * 转换工单的第一工作组名称
     * @param $supportList
     * @return mixed
     */
    public static function translationClassName($supportList)
    {
        $group = ResUsers::select('Id', 'UsersName', 'chargeGroup')->get()->toArray();
        $newGroup = [];
        foreach ($group as $k => $item) {
            $newGroup[$item['Id']] = $item;
        }
        foreach ($supportList as $k => $item) {
            if ($k) {
                $supportList[$k]['UsersName'] = Arr::get($newGroup, "$k.UsersName", "无");
            } else {
                $supportList[$k]['UsersName'] = '其他';
            }
        }
        return $supportList;
    }

    /**
     * 获取用户的代理商
     * @param $supportList
     * @return mixed
     */
    public static function getAgentName($supportList)
    {
        $cacheKey = "ITSM-AgentName-";
        $cacheTime = Carbon::now()->addHours(8);
        if (!empty($supportList)) {
            foreach ($supportList as $support) {
                if (!Cache::tags("ITSM-AgentName")->has($cacheKey . $support->Id)) {
                    $agentName = Support::select('a.CusName')
                        ->leftJoin('res.res_cusinf as a', 'a.Id', '=', 'usercenter.support.AgentId')
                        ->where('usercenter.support.Id', $support->Id)
                        ->first();
                    if ($agentName) {
                        Cache::tags("ITSM-AgentName")->put($cacheKey . $support->Id, json_encode($agentName->toArray()),
                            $cacheTime);
                    } else {
                        $support->agentName = '';
                        continue;
                    }
                }
                $cacheAgent = json_decode(Cache::tags("ITSM-AgentName")->get($cacheKey . $support->Id), true);
                $support->agentName = $cacheAgent['CusName'];

            }
            return $supportList;
        }
    }

    public static function getSupplierName($supplierId)
    {
        $supplier = ResourceProvider::where('id', $supplierId)->first();

        return $supplier != null ?$supplier->providerName:"";
    }

    public static function findSupplierBySearch($search)
    {
        return $arr = ResourceProvider::where('status',0)
            ->where('providerName', 'like', '%' . $search . '%')
            ->take(5)->get();
    }

    public static function findContractBySearch($search)
    {
        return $arr = ResourceContract::where('contractNo', 'like', '%' . $search . '%')
            ->take(5)->get();
    }

    /**
     * 获取一级及二级部门
     * @return array
     */
    public static function getAppDepartment($role)
    {
        /*$depart1 = AuxDict::select('Means', 'Code')
            ->whereNotNull('Means')
            ->where('Domain', '一级部门')
            ->where('Means', '<>', '离职员工')
            ->whereRaw('(Validate =0 or Validate is null)')
            ->get()->toArray();
        foreach ($depart1 as &$item) {
            $depart2 = AuxDict::select('Means', 'Code')
                ->whereNotNull('Means')
                ->where('Domain', '二级部门')
                ->where('ParentCode', $item['Code'])
                ->whereRaw('(Validate =0 or Validate is null)')
                ->get()->toArray();
            $item['secondDep'] = $depart2;
        }
        return $depart1;*/

        $fisibilityGroup = DB::select("select DISTINCT(d.Means),b.Depart,c.Means as MeansOne,b.second_dept,d.Means as MeansTwo from auth.authorities as a JOIN res.aux_stuff as b on a.username=b.Login LEFT JOIN res.aux_dict as c on b.Depart=c.`Code` and  c.DomainCode='DepartType' LEFT JOIN res.aux_dict as d on b.second_dept=d.`Code` and  d.DomainCode='second_dept' where a.authority='$role'");
        $oneGroup = [];
        if ($fisibilityGroup) {
            /**
             * 取出一级部门
             */
            foreach ($fisibilityGroup as $item) {
                if (!empty($item->second_dept)) {
                    $oneGroup[$item->Depart]['name'] = $item->MeansOne;
                }
            }
            /**
             * 取出一级部门下面的二级部门并且返回
             */
            foreach ($fisibilityGroup as $childItem) {
                foreach ($oneGroup as $oneItem) {
                    if ($oneItem['name'] == $childItem->MeansOne && $childItem->MeansTwo != "") {
                        $oneGroup[$childItem->Depart]['child'][$childItem->second_dept] = $childItem->MeansTwo;
                    } else {
                        if ($childItem->MeansTwo != null) {
                            $oneGroup[$childItem->Depart]['name'] = $childItem->MeansOne;
                        }
                    }
                }
            }

        }
        return $oneGroup;
    }

    //判断code是否为数组元素
    public static function isSubElement($strs, $code)
    {
        $isSub = false;
        $arr = explode(",", $strs);
        foreach ($arr as $str) {
            if ($str == $code) {
                $isSub = true;
                break;
            }
        }
        return $isSub;
    }

    /**
     * 处理多个记录
     * 将数组code转换为中文
     */
    public static function translationArr($dataList, $code)
    {
        foreach ($dataList as $item) {
            if (!$item->$code) {
                continue;
            }
            $keys = explode(",", $item->$code);
            if (null != $keys) {
                $values = '';
                foreach ($keys as $items) {
                    $values .= Support::translationMeans($code, $items) . "、";
                }
                $values = rtrim($values, "、");
            }
            $item[$code] = $values;
        }
        return $dataList;
    }

    /**
     * 处理单个记录
     * 将数组code转换为中文
     */
    public static function translationByRow($dataList, $code)
    {
        if (!$dataList->$code) {
            return $dataList;
        }
        $keys = explode(",", $dataList->$code);
        if (null != $keys) {
            $values = '';
            foreach ($keys as $items) {
                $values .= Support::translationMeans($code, $items) . "、";
            }
            $values = rtrim($values, "、");
        }
        $dataList[$code] = $values;
        return $dataList;
    }

    /*
    *
    * 优先级将1和0转化为重要和一般
    */
    public static function getPriority($priority)
    {
        switch ($priority) {
            case 0:
                $priority = '一般';
                break;
            case 1:
                $priority = '重要';
                break;
        }
        return $priority;
    }

    /*
    *
    * 将提醒时间转换为分钟显示
    */
    public static function getRemindType($type)
    {
        switch ($type) {
            case 'no':
                $type = '不需要';
                break;
            case 'two':
                $type = '2分钟';
                break;
            case 'five':
                $type = '5分钟';
                break;
            case 'ten':
                $type = '10分钟';
                break;
            case 'fifteen':
                $type = '15分钟';
                break;
        }
        return $type;
    }

    /**
     * 替换所有空白字符
     * @param $str
     * @return mixed
     */
    public static function myTrim($str)
    {
        $search = array(" ","　","\n","\r","\t");
        $replace = array("","","","","");
        return str_replace($search, $replace, $str);
    }

    /**
     * 替换除\n\r\t外所有空白字符
     * @param $str
     * @return mixed
     */
    public static function myTrimOnlyNRT($str)
    {
        $search = array(" ","　");
        $replace = array("","","","","");
        return str_replace($search, $replace, $str);
    }

    /**
     * 截取前六十个字符
     * @param $str
     * @return string
     */
    public static function subStr60($str){
        if(mb_strlen($str) <60){
            return $str;
        }else{
            return mb_substr($str,0,60)."...";
        }
    }

    /**
     * @param $str
     * @return string
     */
    public static function subStr38($str){
        if(mb_strlen($str) <38){
            return $str;
        }else{
            return mb_substr($str,0,38)."...";
        }
    }

    //获取php时间戳
    public static function getTime(){
        return time();
    }
}