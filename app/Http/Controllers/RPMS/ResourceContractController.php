<?php

namespace Itsm\Http\Controllers\RPMS;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Itsm\Http\Controllers\Controller;
use Itsm\Http\Helper\ThirdCallHelper;
use Itsm\Model\Res\AuxDict;
use Itsm\Model\Res\ResContact;
use Itsm\Model\Res\ResDataCenter;
use Itsm\Model\Rpms\ResourceBill;
use Itsm\Model\Rpms\ResourceContract;
use Itsm\Model\Rpms\ResourceContractProd;
use Itsm\Model\Rpms\ResourceProd;
use Itsm\Model\Rpms\ResourceProvider;
use Itsm\Model\Rpms\ResourceRecord;
use Itsm\Model\Rpms\ResourceSpecialLineRecord;
use Itsm\Model\Rpms\ResourceType;


class ResourceContractController extends Controller
{
    static $defaultValue = [//新增合同相关默认值
        "contractCycle"   => "12",//合同周期
        "currencyType"    => "rmb",//货币类型人民币
        "paymentCycle"    => 1,//付款方式 月付
        "balanceCycle"    => 0,//结算方式 周期
        "paymentMode"     => "laterPayment",//结算方式 周期
        "days"     => 30//后付费30天
    ];

    static $contractTypeList = [//合同类型
        "add"           => "新增",
        "renewal"       => "续约",
        "changeAdd"     => "变更新增",
        "changeRenewal" => "变更续约"
    ];

    static $currencyList = [//货币类型
        "rmb"    => "人民币",
        "hkd"    => "港币",
        "dollar" => "美元",
        "pound"  => "英镑",
        "yen"    => "日元"
    ];

    static $statusList = [//货币类型
        "toDo"    => "待执行",
        "doing" => "执行中",
        "toStop"  => "终止待审核",
        "end"    => "审核闭单"
    ];

    static $paymentList = [//付费方式
        0  => "一次性",
        1  => "月",
        3  => "季",
        6  => "半年",
        12 => "年"
    ];

    static $balanceList = [//结算周期
        0 => "周期",
        1 => "自然",
    ];

    static $paymentMode = [//付费类型
        "advances"     => "预付费",
        "laterPayment" => "后付费"
    ];

    //新增或编辑合同
    public function newContract(Request $req)
    {
        //合同类型
        $contractTypeList = [];
        //0:待执行 1:执行中 2:待终止 3:闭单
        $contractTypeList["add"]="新增";
        $contractTypeList["renewal"]="续约";
        $contractTypeList["changeAdd"]="变更新增";
        $contractTypeList["changeRenewal"]="变更续约";
        //数据中心
        $dataCenterList = ResDataCenter::whereNull("inValidateTs")->get();
       //合同状态
        $statusList = [];
        $statusList["toDo"]="待执行";
        $statusList["doing"] = "执行中";
        //货币类型
        $currencyList=[];
        $currencyList["rmb"]="人民币";
        $currencyList["hkd"]="港币";
        $currencyList["dollar"]="美元";
        $currencyList["pound"]="英镑";
        $currencyList["yen"]="日元";
        //付费方式
        $paymentList=[];
        $paymentList[0]="一次性";
        $paymentList[1]="月";
        $paymentList[3]="季";
        $paymentList[6]="半年";
        $paymentList[12]="年";
        //结算周期
        $balanceList=[];
        $balanceList[0] = "周期";
        $balanceList[1] = "自然";
        //付费类型
        $paymentMode=[];
        $paymentMode["advances"]="预付费";
        $paymentMode["laterPayment"]="后付费";

        $contract = "";
        $oldContract = "";
        $count=0;
        if (($id=$req->get("type")) && $req->get("type") != "new") {
            $contract = ResourceContract::where("id", $req->get("type"))->first();
            if(!empty($contract->startTs)){
                $contract->startTs=substr($contract->startTs,0,10);
            }
            if(!empty($contract->endTs)){
                $contract->endTs=substr($contract->endTs,0,10);
            }
            if(!empty($contract->oldContractNo)){
                $oldContract = ResourceContract::where("contractNo",$contract->oldContractNo)->first();
            }
            //用来判断是否需要显示专线记录
            $count = ResourceContractProd::where("contractId",$id)->where("isSpecialLine","yes")->count();
            $recordCount = ResourceSpecialLineRecord::where("contractId",$id)->count();
            $count = $recordCount>0?$recordCount:$count;
        }
        return view("rpms/newContract", [
            "defaultValue" => self::$defaultValue,
            "contractTypeList" => $contractTypeList,
            "dataCenterList"=>$dataCenterList,
            "statusList"=>$statusList,
            "currencyList"=>$currencyList,
            "paymentList"=>$paymentList,
            "balanceList"=>$balanceList,
            "paymentMode"=>$paymentMode,
            "count"=>$count,
            "contract" => $contract,
            "oldContract" => $oldContract
        ]);
    }

    //合同变更入口
    public function changeContract(Request $req)
    {
        //合同类型
        $contractTypeList = [];
        //0:待执行 1:执行中 2:待终止 3:闭单
        $contractTypeList["changeAdd"] = "变更新增";
        $contractTypeList["changeRenewal"] = "变更续约";
        //数据中心
        $dataCenterList = ResDataCenter::whereNull("inValidateTs")->get();
        //合同状态
        $statusList = [];
        $statusList["toDo"] = "待执行";
        $statusList["doing"] = "执行中";
        //货币类型
        $currencyList = [];
        $currencyList["rmb"] = "人民币";
        $currencyList["hkd"] = "港币";
        $currencyList["dollar"] = "美元";
        $currencyList["pound"] = "英镑";
        $currencyList["yen"] = "日元";
        //付费方式
        $paymentList = [];
        $paymentList[0] = "一次性";
        $paymentList[1] = "月";
        $paymentList[3] = "季";
        $paymentList[6] = "半年";
        $paymentList[12] = "年";
        //结算周期
        $balanceList = [];
        $balanceList[0] = "周期";
        $balanceList[1] = "自然";
        //付费类型
        $paymentMode = [];
        $paymentMode["advances"] = "预付费";
        $paymentMode["laterPayment"] = "后付费";

        $contract = "";
        if ($req->get("type") && $req->get("type") != "new") {
            $contract = ResourceContract::where("id", $req->get("type"))->first();
            if (!empty($contract->startTs)) {
                $contract->startTs = substr($contract->startTs, 0, 10);
            }
            if (!empty($contract->endTs)) {
                $contract->endTs = substr($contract->endTs, 0, 10);
            }
        }
        return view("rpms/changecontract", [
            "contractTypeList" => $contractTypeList,
            "dataCenterList"   => $dataCenterList,
            "statusList"       => $statusList,
            "currencyList"     => $currencyList,
            "paymentList"      => $paymentList,
            "balanceList"      => $balanceList,
            "paymentMode"      => $paymentMode,
            "contract"         => $contract
        ]);
    }

    //终止合同入口
    public function stopContract(Request $req)
    {
        $contract = "";
        if ($req->get("contractId") && $req->get("contractId") != "") {
            $contract = ResourceContract::where("id", $req->get("contractId"))->first();
        }

        $stopTypeList = ThirdCallHelper::getDictArray("订单终止原因类型", "endReasonType");
        return view("rpms/stopcontract",
            ["stopTypeList" => $stopTypeList, "contract" => $contract]);
    }

    public function stopContractSub(Request $req)
    {
        $all = $req->all();
        $user = $req->session()->get("user");
        $destoryMemo = strip_tags(html_entity_decode(trim($all['destoryMemo'], '<br><p><img><b><u><hr><span>')));
        if ($all["contractId"]) {
            $contract = ResourceContract::where("id", $all["contractId"])->first();
            if ($contract && $contract->status == "doing") {
                $contract->status = "toStop";
                $contract->destoryType = $all["destoryType"];
                $contract->destoryTs = $all["destoryTs"];
                $contract->destoryMemo = $destoryMemo;
                $contract->updatedAt = date('Y-m-d H:i:s');
                $contract->updatedBy = $user->Id;
                if ($contract->save()) {
                    ResourceRecord::insertGetId([
                        "module"=>"contract",
                        "moduleId"=>$contract->id,
                        "optType"=>"Modify",
                        "optContent"=>"终止合同",
                        "optId"=> $user->Id,
                        "optTs"=> date('Y-m-d H:i:s')
                    ]);
                    return ["status" => true, "msg" => "终止合同成功！"];
                }
            } else {
                return ["status" => false, "msg" => "合同状态已被更改请刷新！"];
            }
        }

        return ["status" => false, "msg" => "终止合同失败！"];
    }

    public function contractConfirm(Request $req)
    {
        //合同类型
        $contractTypeList = [];
        //0:待执行 1:执行中 2:待终止 3:闭单
        $contractTypeList = self::$contractTypeList;
        //数据中心
        $dataCenterList = ResDataCenter::whereNull("inValidateTs")->get();
        //合同状态
        $statusList = [];
        $statusList["toDo"] = "待执行";
        $statusList["doing"] = "执行中";
        $statusList["toStop"] = "终止待审核";
        $statusList["end"] = "闭单";
        //货币类型
        $currencyList = self::$currencyList;
        //付费方式
        $paymentList = self::$paymentList;
        //结算周期
        $balanceList = self::$balanceList;
        //付费类型
        $paymentMode = self::$paymentMode;
        //合同终止原因类型
        $stopTypeList = ThirdCallHelper::getDictArray("订单终止原因类型", "endReasonType");

        $contract = "";
        if ($req->get("contractId") && $req->get("contractId") != "") {
            $contract = ResourceContract::where("id", $req->get("contractId"))->first();
            if (!empty($contract->startTs)) {
                $contract->startTs = substr($contract->startTs, 0, 10);
            }
            if (!empty($contract->endTs)) {
                $contract->endTs = substr($contract->endTs, 0, 10);
            }
            if (!empty($contract->destoryTs)) {
                $contract->destoryTs = substr($contract->destoryTs, 0, 10);
            }
        }
        $count = ResourceSpecialLineRecord::where("contractId",$req->get("contractId"))->count();
        $billCounts = ResourceBill::where("contractId",$req->get("contractId"))->where("deleted",0)->count();
        $newBills = ResourceBill::where("contractId",$req->get("contractId"))
            ->where("deleted",0)->where("payStatus","new")->get();
        $confirmStr = "";
        if(count($newBills)>0){
            $confirmStr = "合同已经终止，但是仍有未支付账单！";
            $delStr = "";
            $editStr="";
            $endTime = strtotime($contract->destoryTs);//合同终止日期
            foreach ($newBills as $bill) {
                $billStart = strtotime($bill->billStart);
                $billEnd = strtotime($bill->billEnd);
                if($billStart>$endTime){
                    $delStr .= $bill->billNo.",";
                }
                if($billStart<$endTime && $billEnd>$endTime){
                    $editStr .=$bill->billNo.",";
                }
            }
            if($editStr!="")$confirmStr = $confirmStr."编号为".trim($editStr,",")."的账单金额需要调整";
            if($delStr!="")$confirmStr = $confirmStr."编号为".trim($delStr,",")."的账单可删除";
        }
        return view("rpms/contractconfirm", [
            "contractTypeList" => $contractTypeList,
            "dataCenterList"   => $dataCenterList,
            "statusList"       => $statusList,
            "currencyList"     => $currencyList,
            "paymentList"      => $paymentList,
            "balanceList"      => $balanceList,
            "paymentMode"      => $paymentMode,
            "stopTypeList"     => $stopTypeList,
            "contract"          => $contract,
            "count"             => $count,
            "billCounts"       => $billCounts,
            "type"              => $req->get("type"),
            "confirmStr"              => $confirmStr
        ]);
    }

    public function confirmContract(Request $req)
    {
        $contract = ResourceContract::where("id", $req->get("contractId"))->first();
        $status = $req->get("type") == "sure" ? "end" : "doing";
        if ($contract && $contract->status == "toStop") {
            $user = $req->session()->get("user");
            $contract->status = $status;
            $contract->updatedBy = $user->Id;
            $contract->updatedAt = date('Y-m-d H:i:s');
            if ($contract->save()) {
                ResourceRecord::insertGetId([
                    "module"=>"contract",
                    "moduleId"=>$contract->id,
                    "optType"=>$status=="end"?"ConfirmEnd":"CancelEnd",
                    "optId"=> $user->Id,
                    "optTs"=> date('Y-m-d H:i:s')
                ]);
                return ["status" => "success", "msg" => "操作成功！"];
            };
        } else {
            return ["status" => "failure", "msg" => "合同状态已被改变，请刷新重试！"];
        }
        return ["status" => "failure", "msg" => "操作失败！"];
    }


    //加载产品列表
    public function pickResource(Request $req)
    {
        $resourceList=ResourceProd::whereIn("status", [0]);
        $prodTypeList = ResourceType::where("status", 0)
            ->where(function($prodTypeList)use($req){
                $prodTypeList = $prodTypeList->whereNull("parentTypeCode")
                    ->orWhere("parentTypeCode","");
            })->get();
        return view("rpms/pickResource",[
            "resourceList"=>$resourceList,
            "prodTypeList"=>$prodTypeList
        ]);
    }

    //物理删除资源产品明细
    public function deleteResourceProd(Request $req)
    {
        $prodId = $req->get("delId");
        if ($prodId) {
            $del = ResourceContractProd::where("id", $prodId)->delete();
            if ($del) {
                return ["status" => "success"];
            }
        }
        return ["status" => "success", "msg" => "删除失败，请重试！"];
    }

    //加载合同列表
    public function contractList()
    {
        return view("rpms/contractlist");
    }

    //获取合同数据
    public function getContractList(Request $req)
    {
        $contractTable = "rpms.resource_contract";
        $list = ResourceContract::selectRaw("$contractTable.*,count(b.id) as billCounts")
            ->leftJoin("rpms.resource_bill as b",function($list){
                $list->on("b.contractId","=","rpms.resource_contract.id")
                    ->where("b.deleted","=",0)
                    ->whereNotNull("b.seq");
            })
            ->where("inValidate",0);

        if ($statusType = $req->get("statusType")) {
            //toDo:待执行 doing:执行中 toStop:待终止 end:闭单
            if ($statusType != "all") {
                $list->where("$contractTable.status", $statusType);
            }
        }

        if ($providerId = $req->get("providerId")) {
            //toDo:待执行 doing:执行中 toStop:待终止 end:闭单
            if ($statusType != "all") {
                $list->where("$contractTable.supplierId", $providerId);
            }
        }

        if ($status = $req->get("status")) {
            //toDo:待执行 doing:执行中 toStop:待终止 end:闭单
            if ($status != "all") {
                $list->where("$contractTable.status", $status);
            }
        }

        if ($contractType = $req->get("contractType")) {
            if ($contractType != "all") {
                $list->where("$contractTable.contractType", $contractType);
            }
        }

        if ($searchInfo = $req->input("searchInfo")) {
            $list->where(function ($list) use ($searchInfo,$contractTable) {
                $list->where("$contractTable.contractNo", "like", "%$searchInfo%");
            });
        }


        $list = $list->groupBy("$contractTable.id")->orderByRaw("$contractTable.createdAt desc");
        $array['total'] = count($list->get());
        //分页
        $pageSize = !empty($req->input('pageSize')) ? $req->input('pageSize') : 15;
        $pageNumber = !empty($req->input('pageNumber')) ? $req->input('pageNumber') : 1;
        $list = $list->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();


        //公共转换
        $list = ResContact::translationStuff($list, 'createdBy');
        $list = ResourceProvider::translationSupplier($list, 'supplierId');
        $list = $this->translationContractType($list, 'contractType');

        $array['rows'] = $list;
        return $array;
    }

    public function translationContractType($array, $code)
    {
        foreach ($array as $contract) {
            if ($type = $contract->$code) {
                $contract->contractTypeName = self::$contractTypeList[$type];
            }
        }
        return $array;
    }

    //保存新建/修改合同
    public function saveContract(Request $req)
    {
        $getAll = Input::except('_token');
        $reqAll = $getAll['formData'];
        $prodData = $getAll['prodData'];
         foreach($reqAll as $key=>$it){
             if(""==($it)){
                 $reqAll[$key]=null;
             }
         }

        $user = $req->session()->get('user');
        /*页面输入校验 验证提交内容是否规范*/
        $validator = Validator::make($reqAll, [
            'supplierId' => 'required',
            'contractNo' => 'required'
        ], [
            'required' => ':attribute 的字段是必要的。',
        ]);

        $contractNo = $reqAll["contractNo"];
        $chargeOffNo = $reqAll["chargeOffNo"];
        if(preg_match('/[\x{4e00}-\x{9fa5}]/u', $contractNo)>0 || preg_match('/[\x{4e00}-\x{9fa5}]/u', $chargeOffNo)>0){
            return ['status' => false, 'msg' => '合同编号及出账编号不可包含中文'];
        }

        $describe = strip_tags(html_entity_decode(trim($reqAll['describe'], '<br><p><img><b><u><hr><span>')));
        if ($validator->fails()) {//验证不通过,
            return ['status' => false, 'msg' => '提交失败，供应商和合同编号为必填!'];
        } else {
            if (isset($reqAll["id"]) && trim($reqAll["id"]) != "") {
                $contract = ResourceContract::where("id", trim($reqAll["id"]))->first();
                $billCounts = ResourceBill::where("contractId",trim($reqAll["id"]))->where("deleted",0)->count();
                if (!empty($contract) &&$billCounts==0) {
                    $oneCost = 0;$unitPrice=0;$unitPriceTotal=0;
                    if($contractCycle = $reqAll["contractCycle"]){
                        $this->getTotalPrice($contractCycle,$prodData,$oneCost,$unitPrice,$unitPriceTotal);
                    }
                    $udata = [
                        'supplierId'        => $reqAll['supplierId'],
                        'contractNo'        => $reqAll['contractNo'],
                        'contractType'      => $reqAll['contractType'],
                        'contractCycle'     => $reqAll['contractCycle'],
                        'dataCenterId'      => $reqAll['dataCenterId'],
                        'startTs'           => $reqAll['startTs'],
                        'endTs'             => $reqAll['endTs'],
                        'fileNo'            => $reqAll['fileNo'],
                        'status'            => $reqAll['status'],
                        'describe'          => $describe,
                        'chargeOffNo'       => $reqAll['chargeOffNo'],
                        'currencyType'      => $reqAll['currencyType'],
                        'paymentCycle'      => $reqAll['paymentCycle'],
                        'balanceCycle'      => $reqAll['balanceCycle'],
                        'paymentMode'       => $reqAll['paymentMode'],
                        'oneDiscount'       => null==$reqAll['oneDiscount']?0:$reqAll['oneDiscount'],
                        'cycleDiscount'     => null==$reqAll['cycleDiscount']?0:$reqAll['cycleDiscount'],
                        'oneTotalPrice'     => $oneCost,
                        'unitCyclePrice'    => $reqAll['unitCyclePrice'],
                        'monthPrice'        => $unitPrice,
                        'cycleTotalPrice'   => $unitPriceTotal,
                        'days'              => $reqAll['days'],
                        'updatedBy'         => $user->Id,
                        'updatedAt'         => date('Y-m-d H:i:s')
                    ];
                    $update = ResourceContract::where("id", trim($reqAll["id"]))->update($udata);

                    if ($update) {
                        // 添加合同资源关联
                        foreach ($prodData as $prod) {
                            if(!empty($prod['contractId'])){
                                $uProd = [
                                    'amount'        =>round($prod['amount']),
                                    'unitPrice'     =>round($prod['unitPrice'],2),
                                    'oneCost'       =>round($prod['oneCost'],2),
                                ];
                                ResourceContractProd::where("id", trim($prod["id"]))->update($uProd);
                            }else{
                                ResourceContractProd::insertGetId([
                                    'contractId'    =>$reqAll["id"],
                                    'prodId'        =>$prod["prodId"],
                                    'prodTypeOne'   =>$prod['prodTypeOne'],
                                    'isSpecialLine' =>$prod['isSpecialLine'],
                                    'prodTypeTwo'   =>isset($prod['prodTypeTwo'])?$prod['prodTypeTwo']:null,
                                    'prodName'      =>$prod['prodName'],
                                    'unit'          =>round($prod['unit'],2),
                                    'amount'        =>round($prod['amount']),
                                    'unitPrice'     =>round($prod['unitPrice'],2),
                                    'oneCost'       =>round($prod['oneCost'],2),
                                ]);
                            }
                        }

                        $modify = $this->getUpdateMsg($contract,$reqAll);

                        ResourceRecord::insertGetId([
                            "module"=>"contract",
                            "moduleId"=>$contract->id,
                            "optType"=>"Modify",
                            "optContent"=>$modify,
                            "optId"=> $user->Id,
                            "optTs"=> date('Y-m-d H:i:s')
                        ]);

                        //触发生成首期账单
                        if("doing"==$reqAll['status']){
                            $billController = new ResourceBillController();

                            $billController->createBill($reqAll["id"]);
                        }
                        return ['status' => $update, 'msg' => '保存成功!'];
                    }else {
                        return ['status' => false, 'msg' => '数据异常，保存失败!'];
                    }
                } else {
                    return ['status' => false, 'msg' => '该合同已经生成账单，合同信息不可编辑，请刷新合同列表！'];
                }
            } else {
                $contract = ResourceContract::where("contractNo",trim($reqAll["contractNo"]))->first();
                if($contract){
                    return ['status' => false, 'msg' => '合同编号已存在,请确认后重新输入!'];
                }else{
                    $oneCost = 0;$unitPrice=0;$unitPriceTotal=0;
                    if($contractCycle = $reqAll["contractCycle"]){
                        $this->getTotalPrice($contractCycle,$prodData,$oneCost,$unitPrice,$unitPriceTotal);
                    }
                    $contractId = ResourceContract::insertGetId([
                        'supplierId'        => $reqAll['supplierId'],
                        'contractNo'        => $reqAll['contractNo'],
                        'contractType'      => $reqAll['contractType'],
                        'contractCycle'     => $reqAll['contractCycle'],
                        'dataCenterId'      => $reqAll['dataCenterId'],
                        'startTs'           => $reqAll['startTs'],
                        'endTs'             => $reqAll['endTs'],
                        'fileNo'            => $reqAll['fileNo'],
                        'status'            => $reqAll['status'],
                        'describe'          => $describe,
                        'chargeOffNo'       => $reqAll['chargeOffNo'],
                        'currencyType'      => $reqAll['currencyType'],
                        'paymentCycle'      => $reqAll['paymentCycle'],
                        'balanceCycle'      => $reqAll['balanceCycle'],
                        'paymentMode'       => $reqAll['paymentMode'],
                        'oneDiscount'       => null==$reqAll['oneDiscount']?0:$reqAll['oneDiscount'],
                        'cycleDiscount'     => null==$reqAll['cycleDiscount']?0:$reqAll['cycleDiscount'],
                        'oneTotalPrice'     => $oneCost,
                        'monthPrice'         => $unitPrice,
                        'unitCyclePrice'    => $reqAll['unitCyclePrice'],
                        'cycleTotalPrice'   => $unitPriceTotal,
                        'oldContractNo'     => isset($reqAll['oldContractNo']) ? $reqAll['oldContractNo'] : null,
                        'days'              => $reqAll['days'],
                        'createdBy'         => $user->Id,
                        'createdAt'         => date('Y-m-d H:i:s'),
                        'changeTs'          => date('Y-m-d H:i:s')
                    ]);
                    if ($contractId == false) {//插入数据失败
                        return ['status' => false, 'msg' => '提交出错,请稍后再试!'];
                    } else {
                        // 添加合同资源关联
                        foreach ($prodData as $prod) {
                            ResourceContractProd::insertGetId([
                                'contractId'    =>$contractId,
                                'prodId'         =>$prod["prodId"],
                                'isSpecialLine' =>$prod["isSpecialLine"],
                                'prodTypeOne'   =>$prod['prodTypeOne'],
                                'prodTypeTwo'   =>isset($prod['prodTypeTwo'])?$prod['prodTypeTwo']:null,
                                'prodName'      =>$prod['prodName'],
                                'unit'          =>round($prod['unit'],2),
                                'amount'        =>round($prod['amount']),
                                'unitPrice'     =>round($prod['unitPrice'],2),
                                'oneCost'       =>round($prod['oneCost'],2),
                            ]);
                        }

                        ResourceRecord::insertGetId([
                            "module"=>"contract",
                            "moduleId"=>$contractId,
                            "optType"=>"New",
                            "optContent"=>self::$contractTypeList[$reqAll["contractType"]],
                            "optId"=> $user->Id,
                            "optTs"=> date('Y-m-d H:i:s')
                        ]);

                        //触发生成首期账单
                        if("doing"==$reqAll['status']){
                            $billController = new ResourceBillController();

                            $billController->createBill($contractId);
                        }
                        return ['status' => $contractId, 'msg' => '新增合同成功!'];
                    }
                }
            }
        }
    }

    public function getUpdateMsg($contract,$reqAll){
        $updateMsg = "";
        if(isset($reqAll["supplierId"]) && $reqAll["supplierId"] != $contract->supplierId){
            $oldName = ResourceProvider::translationSupplierId($contract->supplierId);
            $newName = ResourceProvider::translationSupplierId($reqAll["supplierId"]);
            $updateMsg .= "供应商由".$oldName."修改为".$newName.";";
        }
        if(isset($reqAll["contractNo"]) && $reqAll["contractNo"] != $contract->contractNo){
            $updateMsg .= "合同编号由".$contract->contractNo."修改为".$reqAll["contractNo"].";";
        }
        if(isset($reqAll["contractType"]) && $reqAll["contractType"] != $contract->contractType){
            $updateMsg .= "合同类型由".self::$contractTypeList[trim($contract->contractType)]."修改为".self::$contractTypeList[trim($reqAll["contractType"])].";";
        }
        if(isset($reqAll["dataCenterId"]) && $reqAll["dataCenterId"] != $contract->dataCenterId){
            $oldDatacenter = ResDataCenter::getDcName($contract->dataCenterId);
            $newDatacenter = ResDataCenter::getDcName($reqAll["dataCenterId"]);
            $updateMsg .= "数据中心由".$oldDatacenter."修改为".$newDatacenter.";";
        }
        if(isset($reqAll["contractCycle"]) && $reqAll["contractCycle"] != $contract->contractCycle){
            $updateMsg .= "合同周期由".$contract->contractCycle."个月修改为".$reqAll["contractCycle"]."个月;";
        }
        if(isset($reqAll["startTs"]) && $reqAll["startTs"] != substr($contract->startTs,0,10)){
            $updateMsg .= "合同开始时间由". substr($contract->startTs,0,10)."修改为".$reqAll["startTs"].";";
        }
        if(isset($reqAll["fileNo"]) && $reqAll["fileNo"] != $contract->fileNo){
            $updateMsg .= "归档编号由".$contract->fileNo."修改为".$reqAll["fileNo"].";";
        }


        if(isset($reqAll["status"]) && $reqAll["status"] != $contract->status){
            $updateMsg .= "合同状态由".self::$statusList[$contract->status]."修改为".self::$statusList[$reqAll["status"]].";";
        }
        if(isset($reqAll["describe"]) && trim($reqAll["describe"]) != $contract->describe){
            $updateMsg .= "合同说明由".$contract->describe."修改为".trim($reqAll["describe"]).";";
        }
        if(isset($reqAll["chargeOffNo"]) && $reqAll["chargeOffNo"] != $contract->chargeOffNo){
            $updateMsg .= "出账编号由".$contract->chargeOffNo."修改为".$reqAll["chargeOffNo"].";";
        }
        if(isset($reqAll["currencyType"]) && $reqAll["currencyType"] != $contract->currencyType){
            $updateMsg .= "货币类型由".$contract->currencyType."修改为".$reqAll["currencyType"].";";
        }
        if(isset($reqAll["paymentCycle"]) && $reqAll["paymentCycle"] != $contract->paymentCycle){
            $updateMsg .= "付款方式由".$contract->paymentCycle."修改为".$reqAll["paymentCycle"].";";
        }
        if(isset($reqAll["balanceCycle"]) && $reqAll["balanceCycle"] != $contract->balanceCycle){
            $old = $contract->balanceCycle == 0?"周期":($contract->balanceCycle==1?"自然":"无");
            $new = $reqAll["balanceCycle"] == 0?"周期":($reqAll["balanceCycle"]==1?"自然":"无");
            $updateMsg .= "结算方式由".$old."修改为".$new.";";
        }
        if(isset($reqAll["paymentMode"]) && $reqAll["paymentMode"] != $contract->paymentMode){
            $old = $contract->paymentMode == "advances"?"预付费":($contract->paymentMode=="laterPayment"?"后付费":"无");
            $new = $reqAll["paymentMode"] == "advances"?"预付费":($reqAll["paymentMode"]=="laterPayment"?"后付费":"无");
            $updateMsg .= "付费类型由".$old."修改为".$new.";";
        }
        if(isset($reqAll["days"]) && $reqAll["days"] != $contract->days){
            $updateMsg .= "预/后付费天数由".$contract->days."修改为".$reqAll["days"].";";
        }
        return $updateMsg;
    }

    /**
     * 获取合同相关小计价格
     * @param $contractCycle
     * @param $list
     * @param int $oneCost
     * @param int $unitPrice
     * @param int $unitPriceTotal
     */
    public function getTotalPrice($contractCycle,$list,&$oneCost=0,&$unitPrice=0,&$unitPriceTotal=0){
        foreach($list as $item){
            $oneCost += round($item["oneCost"],2)*$item["amount"];
            $unitPrice += $item["amount"]*round($item["unitPrice"],2);
            $unitPriceTotal += $item["amount"]*round($item["unitPrice"],2)*$contractCycle;
        }
    }

    public function prodList(Request $req)
    {
        $list = ResourceContractProd::selectRaw("resource_contract_prod.*, b.typeName as 'prodTypeOneName',c.typeName as 'prodTypeTwoName'")
            ->leftJoin('resource_type as b', 'resource_contract_prod.prodTypeOne', '=', 'b.typeCode')
            ->leftJoin("resource_type as c",function($list){
                $list->on("c.typeCode","=","resource_contract_prod.prodTypeTwo")->on("b.typeCode","=","c.parentTypeCode");
            });

        if (($contractId = $req->get("contractId")) != "") {
            $list->where("contractId",$contractId);
        }else{
            return null;
        }

        $array['rows'] = $list->get();
        return $array;
    }

    //检索供应商
    public function findSupplierBySearch(Request $req)
    {
        if ($search = $req->input('name')) {
            return ThirdCallHelper::findSupplierBySearch($search);
        }
    }

    public function recordList(Request $req)
    {
        $list = ResourceRecord::where("moduleId",$req->get("contractId"))
            ->where("module","contract")->get();
        $list = ResContact::translationStuff($list,"optId");
        $list = ThirdCallHelper::translationDict($list,"optType","rpmsOptType");
        return ["rows"=>$list];
    }

    public function addRecord($prodId,Request $req){
        $prod = ResourceContractProd::where("id",$prodId)->first();
        $record = "";
        if($id = $req->get("recordId")){
            $record = ResourceSpecialLineRecord::where("id",$id)->first();
        }
        return view("rpms/newSpecialRecord",[
            "prod"=>$prod,
            "record"=>$record,
        ]);
    }

    public function saveRecord(Request $req){
        $getAll = $req->except('_token');
        $user = $req->session()->get("user");
        if($recordId = $getAll["recordId"]){
            $udata = [
                "contractId"=>$getAll["contractId"],
                "prodId"=>$getAll["prodId"],
                "prodName"=>$getAll["prodName"],
                "contractor"=>$getAll["contractor"],
                "contactName"=>$getAll["contactName"],
                "hotLine"=>$getAll["hotLine"],
                "cusName"=>$getAll["cusName"],
                "contractNo"=>$getAll["contractNo"],
                "contractStatus"=>$getAll["contractStatus"],
                "dataCenter"=>$getAll["dataCenter"],
                "provider"=>$getAll["provider"],
                "specialType"=>$getAll["specialType"],
                "equipmentNo"=>$getAll["equipmentNo"],
                "amount"=>$getAll["amount"],
                "speed"=>$getAll["speed"],
                "dataCenterAddress"=>$getAll["dataCenterAddress"],
                "clientAddress"=>$getAll["clientAddress"],
                "billingDate"=>$getAll["billingDate"],
                "monthRental"=>$getAll["monthRental"],
                "firstRental"=>$getAll["firstRental"],
                "memo"=>$getAll["memo"]
            ];
            $record = ResourceSpecialLineRecord::where("id",$recordId);
            $a = $record->update($udata);
            return ['status' => true, 'msg' => '修改成功!'];
        }else{
            $ret = ResourceSpecialLineRecord::insertGetId([
                "contractId"=>$getAll["contractId"],
                "prodId"=>$getAll["prodId"],
                "prodName"=>$getAll["prodName"],
                "contractor"=>$getAll["contractor"],
                "contactName"=>$getAll["contactName"],
                "hotLine"=>$getAll["hotLine"],
                "cusName"=>$getAll["cusName"],
                "contractNo"=>$getAll["contractNo"],
                "contractStatus"=>$getAll["contractStatus"],
                "dataCenter"=>$getAll["dataCenter"],
                "provider"=>$getAll["provider"],
                "specialType"=>$getAll["specialType"],
                "equipmentNo"=>$getAll["equipmentNo"],
                "amount"=>$getAll["amount"],
                "speed"=>$getAll["speed"],
                "dataCenterAddress"=>$getAll["dataCenterAddress"],
                "clientAddress"=>$getAll["clientAddress"],
                "billingDate"=>$getAll["billingDate"],
                "monthRental"=>$getAll["monthRental"],
                "firstRental"=>$getAll["firstRental"],
                "memo"=>$getAll["memo"],
                "createdBy"=>$user->Id
            ]);
            return ['status' => $ret, 'msg' => '添加成功!'];
        }
    }

    public function specialRocordList(Request $req){
        $contractId = $req->get("contractId");
        if(!$contractId)return;
        $list = ResourceSpecialLineRecord::where("contractId",$contractId);
        $json["total"] = $list->count();
        $json["rows"] = $list->get();
        return $json;
    }

    public function delRecord($id){
        $record = ResourceSpecialLineRecord::where("id",$id);
        if($record->delete()){
            return ["status" => "success"];
        }
        return ["status" => "failure","msg"=>"删除失败！"];
    }

    public function getSpecialCount(Request $req){
        $count = 0;
        $record = 0;
        if($id = $req->get("contractId")){
            $count = ResourceContractProd::where("contractId",$id)->where("isSpecialLine","yes")->count();
            $record = ResourceSpecialLineRecord::where("contractId",$id)->count();
        }
        $arr['prodCount'] = $count;
        $arr['recordCount'] = $record;
        return $arr;
    }

    //批量处理
    public function batchOperate(Request $req)
    {
        $user = $req->session()->get('user');
        $supIds = $req->get('supIds');
        $batchType = $req->get("batchType");

        if (count($supIds) > 0) {
            foreach ($supIds as $supId) {
                $contract = ResourceContract::where('id', $supId['id'])->first();
                if ($contract->status == "toDo") {
                    $contract->inValidateAt = date('Y-m-d H:i:s');
                    $contract->inValidate = 1;
                    $contract->updatedAt = date('Y-m-d H:i:s');
                    $contract->updatedBy = $user->Id;
                    if (!$contract->save()) {
                        return ['status' => 'failure'];
                    }
                }else{
                    return ['status' => 'failure','msg'=>'编号为'.$contract->contractNo.'的合同状态已发生改变！'];
                }
            }
            return ['status' => 'success'];
        }
        return ['status' => 'failure'];
    }
}
