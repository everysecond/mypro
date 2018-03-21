<?php

namespace Itsm\Http\Controllers\Kindeditor;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Itsm\Http\Controllers\Controller;
use Itsm\Model\Res\AuxStuff;
use Itsm\Model\Rpms\ResourceContact;
use Itsm\Model\Rpms\ResourceContract;
use Itsm\Model\Rpms\ResourceContractProd;
use Itsm\Model\Rpms\ResourceProd;
use Itsm\Model\Rpms\ResourceProvider;
use Itsm\Model\Rpms\ResourceType;
use Maatwebsite\Excel\Excel;

/**
 * 图片上传
 * @author liky@51idc.com
 */
class UploadController extends Controller {
    static $defaultValue = [//新增合同相关默认值
        "contractCycle" => "12",//合同周期
        "currencyType"  => "rmb",//货币类型人民币
        "paymentCycle"  => 1,//付款方式 月付
        "balanceCycle"  => 0,//结算方式 周期
        "paymentMode"   => "laterPayment",//结算方式 周期
        "days"          => 30//后付费30天
    ];

    static $contractTypeList = [//合同类型
        "新增"   => "add",
        "续约"   => "renewal",
        "变更新增" => "changeAdd",
        "变更续约" => "changeRenewal"
    ];

    static $paymentList = [//付费方式
        "一次性" => 0,
        "月"   => 1,
        "季"   => 3,
        "半年"  => 6,
        "年"   => 12
    ];

    static $statusList = [//货币类型
        "待执行"    => "toDo",
        "执行中" => "doing",
        "终止待审核"  => "toStop",
        "审核闭单"    => "end"
    ];

    static $balanceList = [//结算周期
        "周期" => 0,
        "自然" => 1
    ];

    static $paymentMode = [//付费类型
        "预付费" => "advances",
        "后付费" => "laterPayment"
    ];

    /**
     * 上传图片
     * @param Request $request
     * @return array
     */
    public function uploadify(Request $request) {
        try {
            $allowedExtensions = ["png", "jpg", "gif","bmp"];
            //最大文件大小 5M
            $max_size = 5242880;
            $file = $request->file('imgFile');
            $file_size  = $file->getClientSize();
            if ($file->getClientOriginalExtension() && !in_array($file->getClientOriginalExtension(), $allowedExtensions)
            ) {
                return ['error' => 1, 'message' => "只能上传" . implode(",", $allowedExtensions) . "格式图片."];
            }
            if ($file_size> $max_size||!$file_size) {
                return ['error' => 1, 'message' => "图片大小不能超过5M"];
            }
            $destinationPath = 'upload/images/';
            $ymd = date("Ymd");
            $savePath = $destinationPath . $ymd . "/";
            $extension = $file->getClientOriginalExtension();
            $fileName = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $extension;
            $fileUrl = url($savePath . $fileName);
            $file->move($savePath, $fileName);
            return [
                'error' => 0,
                'url' => asset($fileUrl),
            ];
        } catch (\Exception $e) {
            return [
                'error' => 1,
                'message' => "上传图片失败",
            ];
        }
    }

    //上传文件
    public function uploadfile(Request $request)
    {
        //dd($request->file());
        $fileType = Input::get('dir');
        try {
            $allowedExtensions = [];
            if ($fileType == 'file') {
                $allowedExtensions = [
                    'doc',
                    'docx',
                    'xls',
                    'xlsx',
                    'ppt',
                    'zip',
                    'rar',
                    'gz',
                    'bz2',
                    'pdf',
                    'zip',
                    'rtf'
                ];
            }
            if ($fileType == 'image') {
                $allowedExtensions = ["png", "jpg", "gif"];
            }
            //最大文件大小 10M
            $max_size = 10485760;
            $file = $request->file('imgFile');
            $file_size = $file->getClientSize();
            if ($file->getClientOriginalExtension() && !in_array(strtolower($file->getClientOriginalExtension()),
                    $allowedExtensions)
            ) {
                return ['error' => 1, 'message' => "只能上传" . implode(",", $allowedExtensions) . "格式文件."];
            }
            if ($file_size > $max_size || !$file_size) {
                return ['error' => 1, 'message' => "文件大小不能超过10M"];
            }
            if ($fileType == 'file') {
                $destinationPath = 'upload/files/';
            } elseif ($fileType == 'image') {
                $destinationPath = 'upload/images/';
            }
            $ymd = date("Ymd");
            $savePath = $destinationPath . $ymd . "/";
            $extension = $file->getClientOriginalExtension();
            $fileName = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $extension;
            $fileUrl = url($savePath . $fileName);
            $file->move($savePath, $fileName);
            return [
                'error'     => 0,
                'url'       => asset($fileUrl),
                'filetitle' => $fileName,
            ];
        } catch (\Exception $e) {
            return [
                'error'   => 1,
                'message' => "上传文件失败",
            ];
        }
    }

    //上传RPMS合同及产品明细Api
    public function uploadExcel(Request $request,Excel $excel)
    {
        $file = $request->file("excelFile");
        $fileType = $file->getType();
        try {
            $allowedExtensions = [];
            if ($fileType == 'file') {
                $allowedExtensions = [
                    'xls',
                    'xlsx',
                ];
            }
            //最大文件大小 10M
            $max_size = 10485760;
            $file_size = $file->getClientSize();
            if ($file->getClientOriginalExtension() && !in_array(strtolower($file->getClientOriginalExtension()),
                    $allowedExtensions)
            ) {
                return ['error' => 1, 'message' => "只能上传" . implode(",", $allowedExtensions) . "格式Excel文件."];
            }
            if ($file_size > $max_size || !$file_size) {
                return ['error' => 1, 'message' => "文件大小不能超过10M"];
            }
            if ($fileType == 'file') {
                $destinationPath = 'upload/files/';
            } elseif ($fileType == 'image') {
                $destinationPath = 'upload/images/';
            }
            $ymd = date("Ymd");
            $savePath = $destinationPath . "/";
            $extension = $file->getClientOriginalExtension();
            $fileName = 'cache.'. $extension;
            $fileUrl = url($savePath . $fileName);
            $file->move($savePath, $fileName);

            $filePath = 'public/upload/files/cache.'.$extension;
            $data = $excel->load($filePath, function($reader) {})->getSheet(0)->toArray();
            unset($data[0]);

            $providers = ResourceProvider::select("id","providerName")->get();
            $pTab = "resource_prod";
            $prods = ResourceProd::select("$pTab.*","b.typeName")
                ->leftJoin("resource_type as b","b.typeCode","=","$pTab.sonType")
                ->groupBy("$pTab.id")
                ->get();

            if (count($providers) > 0 && count($prods) > 0) {
                if($data!=""){
                    $providerList = [];
                    $prodList = [];
                    $issetContractNo=[];
                    foreach ($providers as $provider) {//以供应商名称作key方便查找取值
                        $providerList[$provider->providerName] = $provider->id;
                    }
                    foreach ($prods as $prod) {//以产品模板名称作key方便查找取值
                        $prodList[($prod->typeName?$prod->typeName:"").$prod->prodName] = $prod;
                    }
                    $oldContractList = ResourceContract::select("contractNo")->where("inValidate","0")->get();
                    foreach ($oldContractList as $issetCon) {//取已存在合同编号组成数组
                        $issetContractNo[] = $issetCon->contractNo;
                    }
                    $check = $this->checkContracts($data, $providerList, $prodList,$issetContractNo);
                    if($check["error"] == 0){
                        $user = $request->session()->get("user");
                        $now = date("Y-m-d H:i:s");
                        $contractData = [];
                        $contractProds = [];
                        $contractTypeList = self::$contractTypeList;
                        $statusList = self::$statusList;
                        $paymentList = self::$paymentList;
                        $balanceList = self::$balanceList;
                        $paymentMode = self::$paymentMode;
                        foreach($data as $key=>$item){
                            if(!empty($item[0])){//如果非空则代表是合同主表信息
                                //若不为空，则将上一条合同数据插入数据库；
                                if($contractData!=[]){//批量插入合同及产品数据
                                    $oneCost = 0;$unitCyclePrice=0;
                                    $this->getTotalPrice($contractData,$contractProds,$oneCost,$unitCyclePrice);
                                    $contractData["oneTotalPrice"] = $oneCost;
                                    $contractData["unitCyclePrice"] = $unitCyclePrice;
                                    $contractId = ResourceContract::insertGetId($contractData);
                                    if($contractId>0){
                                        foreach($contractProds as &$contractProd){
                                            $contractProd["contractId"]=$contractId;
                                        }
                                        $ret = ResourceContractProd::insert($contractProds);
                                        if(!$ret){
                                            return ['error'   => 1, "message" => "第" .$key. "行产品数据插入失败，请核对数据规则!"];
                                        }
                                    }else{
                                        return ['error'   => 1, "message" => "第" .$key. "行合同数据插入失败，请核对数据规则!"];
                                    }
                                }
                                $contractData = [];//清空上一合同数据
                                $contractProds = [];//清空上一合同产品数据
                                $contractData["supplierId"] = $providerList[trim($item[0])];
                                $contractData["contractNo"] = trim($item[1]);
                                $contractData["contractType"] =$contractTypeList[trim($item[2])];
                                $contractData["contractCycle"] =trim($item[3]);
                                $contractData["dataCenterAddress"] =trim($item[4]);
                                $contractData["startTs"] =str_replace(".","-",trim($item[5]));
                                $contractData["endTs"] =str_replace(".","-",trim($item[6]));
                                $contractData["status"] =$statusList[trim($item[7])];
                                $contractData["describe"] =strip_tags(trim($item[8]));
                                $contractData["chargeOffNo"] =trim($item[9]);
                                $contractData["currencyType"] =strtolower(trim($item[10]));
                                $contractData["paymentCycle"] =$paymentList[trim($item[11])];
                                $contractData["balanceCycle"] =$balanceList[trim($item[12])];
                                $contractData["paymentMode"] =$paymentMode[trim($item[13])];
                                $contractData["days"] =trim($item[14]);
                                $contractData["createdBy"] =$user->Id;
                                $contractData["createdAt"] =$now;
                            }
                            $prodMode = $prodList[trim($item[16]?$item[16]:"").trim($item[17])];
                            $prod = [];
                            $prod["prodId"] = $prodMode->id;
                            $prod["contractId"] = 0;
                            $prod["prodTypeOne"] = $prodMode->prodType;
                            $prod["prodTypeTwo"] = $prodMode->sonType;
                            $prod["prodName"] = $prodMode->prodName;
                            $prod["amount"] = trim($item[18]);
                            $prod["unit"] = $prodMode->unit;
                            $prod["unitPrice"] =trim($item[19]);
                            $prod["oneCost"] =trim($item[20]);
                            $contractProds[] = $prod;
                        }
                        if($contractData!=[]){//批量插入产品数据
                            $oneCost = 0;$unitCyclePrice=0;
                            $this->getTotalPrice($contractData,$contractProds,$oneCost,$unitCyclePrice);
                            $contractData["oneTotalPrice"] = $oneCost;
                            $contractData["unitCyclePrice"] = $unitCyclePrice;
                            $contractId = ResourceContract::insertGetId($contractData);
                            if($contractId>0){
                                foreach($contractProds as &$contractProd){
                                    $contractProd["contractId"]=$contractId;
                                }
                                $ret = ResourceContractProd::insert($contractProds);
                                if(!$ret){
                                    return ['error'   => 1, "message" => "最后一条合同数据插入失败，请核对数据规则!"];
                                }
                            }else{
                                return ['error'   => 1, "message" => "最后一条合同数据插入失败，请核对数据规则!"];
                            }
                        }
                    }else{
                        return $check;
                    }
                    return [
                        'error'   => 0,
                        'message' => "数据导入成功",
                    ];
                }else{
                    return [
                        'error'   => 1,
                        'message' => "未获取到excel文件",
                    ];
                }
            }else{
                return [
                    'error'   => 1,
                    'message' => "请添加完善资源产品，供应商等基本信息",
                ];
            }
        } catch (\Exception $e) {
            return [
                'error'   => 1,
                'message' => $e->getMessage(),
            ];
        }
    }

    //供应商数据导入接口
    public function uploadProvider(Request $request,Excel $excel)
    {
        $file = $request->file("excelFile");
        $fileType = $file->getType();
        try {
            $allowedExtensions = [];
            if ($fileType == 'file') {
                $allowedExtensions = [
                    'xls',
                    'xlsx',
                ];
            }
            //最大文件大小 10M
            $max_size = 10485760;
            $file_size = $file->getClientSize();
            if ($file->getClientOriginalExtension() && !in_array(strtolower($file->getClientOriginalExtension()),
                    $allowedExtensions)
            ) {
                return ['error' => 1, 'message' => "只能上传" . implode(",", $allowedExtensions) . "格式Excel文件."];
            }
            if ($file_size > $max_size || !$file_size) {
                return ['error' => 1, 'message' => "文件大小不能超过10M"];
            }
            if ($fileType == 'file') {
                $destinationPath = 'upload/files/';
            } elseif ($fileType == 'image') {
                $destinationPath = 'upload/images/';
            }
            $ymd = date("Ymd");
            $savePath = $destinationPath . "/";
            $extension = $file->getClientOriginalExtension();
            $fileName = 'cache.' . $extension;
            $fileUrl = url($savePath . $fileName);
            $file->move($savePath, $fileName);

            $filePath = 'public/upload/files/cache.' . $extension;
            $data = $excel->load($filePath, function ($reader) {
            })->ignoreEmpty()->getSheet(0)->toArray();
            unset($data[0]);

            $stuffs = AuxStuff::select("Id", "Name")->get();
            $stuffList = [];
            foreach ($stuffs as $stuff) {
                $stuffList[$stuff->Name] = $stuff->Id;
            }

            if ($data != "") {
                $providers = [];
                $contacts = [];
                $user = $request->session()->get("user");
                $lastProvider = ResourceProvider::orderBy("id", "desc")->first();
                $i = $lastProvider ? $lastProvider->id : 0;
                foreach ($data as $item) {
                    if (!empty($item[0])) {
                        $i++;
                        $provider = [
                            "id"                => $i,
                            "providerName"      => $item[0],
                            "providerType"      => "carrier",
                            "innerCharger"      => isset($stuffList[$item[2]]) ? $stuffList[$item[2]] : null,
                            "tell"              => $item[3],
                            "hotLine"           => $item[5],
                            "postCode"          => $item[6],
                            "address"           => $item[7],
                            "registeredCapital" => $item[8],
                            "createdBy"         => $user->Id
                        ];
                        $contact = [
                            "providerId" => $i,
                            "name"       => $item[9],
                            "type"       => "Stype",
                            "mobile"     => $item[11],
                            "tell"       => $item[12],
                            "email"      => $item[13],
                            "createdBy"  => $user->Id
                        ];
                        $providers[] = $provider;
                        $contacts[] = $contact;
                }
                }
                $a = ResourceProvider::insert($providers);
                if ($a) {
                    $b = ResourceContact::insert($contacts);
                }
                if ($a && isset($b) && $b) {
                    return [
                        'error'   => 0,
                        'message' => "导入数据成功！",
                    ];
                }
            } else {
                return [
                    'error'   => 1,
                    'message' => "未获取到excel文件",
                ];
            }
        } catch (\Exception $e) {
            return [
                'error'   => 1,
                'message' => $e->getMessage(),
            ];
        }
    }

    //资源类型及产品数据导入接口
    public function uploadTypeAndProd(Request $request, Excel $excel)
    {
        $file = $request->file("excelFile");
        $fileType = $file->getType();
        try {
            $allowedExtensions = [];
            if ($fileType == 'file') {
                $allowedExtensions = [
                    'xls',
                    'xlsx',
                ];
            }
            //最大文件大小 10M
            $max_size = 10485760;
            $file_size = $file->getClientSize();
            if ($file->getClientOriginalExtension() && !in_array(strtolower($file->getClientOriginalExtension()),
                    $allowedExtensions)
            ) {
                return ['error' => 1, 'message' => "只能上传" . implode(",", $allowedExtensions) . "格式Excel文件."];
            }
            if ($file_size > $max_size || !$file_size) {
                return ['error' => 1, 'message' => "文件大小不能超过10M"];
            }
            if ($fileType == 'file') {
                $destinationPath = 'upload/files/';
            } elseif ($fileType == 'image') {
                $destinationPath = 'upload/images/';
            }
            $ymd = date("Ymd");
            $savePath = $destinationPath . "/";
            $extension = $file->getClientOriginalExtension();
            $fileName = 'cache.' . $extension;
            $fileUrl = url($savePath . $fileName);
            $file->move($savePath, $fileName);

            $filePath = 'public/upload/files/cache.' . $extension;
            $data = $excel->load($filePath, function ($reader) {
            })->ignoreEmpty()->getSheet(0)->toArray();
            unset($data[0]);

            if ($data != "") {
                $types = [];
                $typeCodes = [];
                $prods = [];
                $prodsArray = [];
                $typeDB = ResourceType::select("typeCode")->get();
                $prodDB = ResourceProd::select("*")->get();
                if (count($typeDB) > 0) {
                    foreach($typeDB as $m){
                        $typeCodes[] = $m->typeCode;
                    }
                }
                if (count($prodDB) > 0) {
                    foreach($prodDB as $m){
                        $prodsArray[] = $m->prodType.($m->sonType?$m->sonType:"").$m->prodName;
                    }
                }

                $user = $request->session()->get("user");
                foreach ($data as $item) {
                    if (!empty($item[1]) && !in_array($item[1], $typeCodes)) {
                        $type = [
                            "typeCode"       => trim($item[1]),
                            "typeName"       => trim($item[0]),
                            "parentTypeCode" => null,
                            "createdBy"      => $user->Id
                        ];
                        $typeCodes[] = $item[1];
                        $types[] = $type;
                    }
                    if (!empty($item[3]) && !in_array($item[3], $typeCodes)) {
                        $type = [
                            "typeCode"       => trim($item[3]),
                            "typeName"       => trim($item[2]),
                            "parentTypeCode" => trim($item[1]),
                            "createdBy"      => $user->Id
                        ];
                        $typeCodes[] = $item[3];
                        $types[] = $type;
                    }
                    $prodOnly =($item[1]?$item[1]:"").($item[3]?$item[3]:"").($item[4]?$item[4]:"");
                    if (!empty($item[4]) &&!in_array($prodOnly,$prodsArray)) {
                        $prod = [
                            "prodType"  => trim($item[1]),
                            "prodName"  => trim($item[4]),
                            "sonType"   => trim($item[3]),
                            "createdBy" => $user->Id
                        ];
                        $prodsArray[] = trim($item[4]);
                        $prods[] = $prod;
                    }
                }
                $a = ResourceType::insert($types);
                if ($a) {
                    $b = ResourceProd::insert($prods);
                }
                if ($a && isset($b) && $b) {
                    return [
                        'error'   => 0,
                        'message' => "导入数据成功！",
                    ];
                }
            } else {
                return [
                    'error'   => 1,
                    'message' => "未获取到excel文件",
                ];
            }
        } catch (\Exception $e) {
            return [
                'error'   => 1,
                'message' => $e->getMessage(),
            ];
        }
    }

    //检测excel合同数据是否符合规则
    public function checkContracts($contracts, $providerList, $prodList,$issetContractNo)
    {
        $providerList = array_keys($providerList);
        $prodList = array_keys($prodList);
        $contractTypeList = array_keys(self::$contractTypeList);
        $statusList = array_keys(self::$statusList);
        $paymentList = array_keys(self::$paymentList);
        $balanceList = array_keys(self::$balanceList);
        $paymentMode = array_keys(self::$paymentMode);
        foreach ($contracts as $key => $contract) {
            if ($contract[0] != null && trim($contract[0]) != "") {
                if (!in_array(trim($contract[0]), $providerList)) {
                    return ['error'   => 1, "message" => "第" . ($key + 1) . "行供应商未匹配到，请添加!"];
                }
                if (in_array(trim($contract[1]), $issetContractNo)) {
                    return ['error'   => 1, "message" => "第" . ($key + 1) . "行合同编号已存在，请核对!"];
                }
                if (!in_array(trim($contract[2]), $contractTypeList)) {
                    return ['error'   => 1, "message" => "第" . ($key + 1) . "行合同类型未匹配到，请核对!"];
                }
                if (!in_array(trim($contract[7]), $statusList)) {
                    return ['error'   => 1, "message" => "第" . ($key + 1) . "行合同状态未匹配到，请核对!"];
                }
                if (!in_array(trim($contract[11]), $paymentList)) {
                    return ['error'   => 1, "message" => "第" . ($key + 1) . "行付费方式未匹配到，请核对!"];
                }
                if (!in_array(trim($contract[12]), $balanceList)) {
                    return ['error'   => 1, "message" => "第" . ($key + 1) . "行结算周期未匹配到，请核对!"];
                }
                if (!in_array(trim($contract[13]), $paymentMode)) {
                    return ['error'   => 1, "message" => "第" . ($key + 1) . "行付费类型未匹配到，请核对!"];
                }
            }
            if (!in_array(trim($contract[16]?$contract[16]:"").trim($contract[17]), $prodList)) {
                return ['error'   => 1, "message" => "第" . ($key + 1) . "行产品未匹配到，请核对资源类型及产品名称是否正确!"];
            }
        }
        return ["error" => 0];
    }

    //计算合同周期及一次性总价格
    public function getTotalPrice($contract,$prods,&$oneTotalPrice,&$unitCyclePrice){
        if(count($prods)>0){
            $contractCycle = $contract["contractCycle"]?$contract["contractCycle"]:12;
            $payCycle = $contract["paymentCycle"]!=null?($contract["paymentCycle"]==0?$contractCycle:$contract["paymentCycle"]):1;
            foreach($prods as $prod){
                $unitCyclePrice += $prod["unitPrice"]*$payCycle*$prod["amount"];
                $oneTotalPrice += $prod["oneCost"]*$prod["amount"];
            }
        }
    }
}
