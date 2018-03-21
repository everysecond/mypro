<?php

namespace Itsm\Http\Controllers\RPMS;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Itsm\Http\Controllers\Controller;
use Itsm\Http\Helper\ThirdCallHelper;
use Itsm\Model\Res\ResContact;
use Itsm\Model\Res\ResDataCenter;
use Itsm\Model\Rpms\ResourceBill;
use Itsm\Model\Rpms\ResourceContract;
use Itsm\Model\Rpms\ResourceContractProd;
use Itsm\Model\Rpms\ResourceProd;
use Itsm\Model\Rpms\ResourceProvider;
use Itsm\Model\Rpms\ResourceRecord;


class ResourceBillController extends Controller
{
    static $otherTypeList = [//其它类型
        "dk"           => "带宽",
        "zx"       => "专线",
    ];

    //新增或编辑合同
    public function newBill(Request $req)
    {
        $bill = "";
        if ($req->get("type") && $req->get("type") != "new") {
            $bill = ResourceBill::selectRaw("resource_bill.*,b.contractNo")
                ->leftJoin('resource_contract as b', 'resource_bill.contractId', '=', 'b.id')
                ->where("resource_bill.id",$req->get("type"))->first();
            if(!empty($bill->billStart)){
                $bill->billStart=substr($bill->billStart,0,10);
            }
            if(!empty($bill->billEnd)){
                $bill->billEnd=substr($bill->billEnd,0,10);
            }
        }
        return view("rpms/newBill", [
            "otherTypeList" => ResourceBillController::$otherTypeList,
            "bill" => $bill
        ]);
    }

    //新增或编辑合同
    public function billInfo(Request $req)
    {
        $bill = "";
        if ($req->get("type") && $req->get("type") != "new") {
            $bill = ResourceBill::selectRaw("resource_bill.*,b.contractNo")
                ->leftJoin('resource_contract as b', 'resource_bill.contractId', '=', 'b.id')
                ->where("resource_bill.id",$req->get("type"))->first();
        }
        return view("rpms/billInfo", [
            "otherTypeList" => ResourceBillController::$otherTypeList,
            "bill" => $bill
        ]);
    }

    //加载合同列表
    public function billList()
    {
        return view("rpms/billList");
    }

    //获取合同数据
    public function getBillList(Request $req)
    {
        $list = ResourceBill::selectRaw("resource_bill.*,b.supplierId")
            ->leftJoin('resource_contract as b', 'resource_bill.contractId', '=', 'b.id')
            ->leftJoin('resource_provider as c', 'b.supplierId', '=', 'c.id');

        if ($statusType = $req->get("statusType")) {
            if ($statusType != "all") {
                if ($statusType == "expired") {
                    $list->whereRaw("DATEDIFF(resource_bill.billExpire,NOW())<0 AND resource_bill.payStatus != 'success'");
                }else{
                    $list->whereRaw("MONTH(resource_bill.billExpire) = MONTH(NOW())");
                }
            }
        }

        if ($billStatus = $req->get("billStatus")) {
            if ($billStatus != "all") {
                $list->where("payStatus", $billStatus);
            }
        }

        if ($contractId = $req->get("contractId")) {
            if ($billStatus != "all") {
                $list->where("contractId", $contractId);
            }
        }

        if ($searchInfo = $req->input("searchInfo")) {
            $list->where(function ($list) use ($searchInfo) {
                $list->where("b.contractNo", "like", "%$searchInfo%")
                    ->orwhere("resource_bill.billNo", "like", "%$searchInfo%")
                    ->orwhere("c.providerName", "like", "%$searchInfo%");
            });
        }
        $list->where("deleted", 0);

        $array['total'] = $list->count();

        //排序 按使用频率及创建时间排序
        $list->orderByRaw("createdAt desc");

        //分页
        $pageSize = !empty($req->input('pageSize')) ? $req->input('pageSize') : 15;
        $pageNumber = !empty($req->input('pageNumber')) ? $req->input('pageNumber') : 1;
        $list = $list->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();

        //公共转换
        $list = ResContact::translationStuff($list, 'createdBy');
        $list = ResourceProvider::translationSupplier($list, 'supplierId');

        //转换账单状态显示
        $this->isExpire($list);

        $array['rows'] = $list;
        return $array;
    }

    //保存新建/修改合同
    public function saveBill(Request $req)
    {
        $reqAll = $req->all();
        $user = $req->session()->get('user');

         foreach($reqAll as $key=>$it){
             if(""==($it)){
                 $reqAll[$key]=null;
             }
         }

        /*页面输入校验 验证提交内容是否规范*/
        $validator = Validator::make($reqAll, [
            'contractId' => 'required',
            'billNo' => 'required'
        ], [
            'required' => ':attribute 的字段是必要的。',
        ]);

        $describe = strip_tags(html_entity_decode(trim($reqAll['describe'], '<br><p><img><b><u><hr><span>')));
        if ($validator->fails()) {//验证不通过,
            return ['status' => false, 'msg' => '提交失败，合同编号和账单编号为必填!'];
        } else {
            //检测账单编号是否影响系统账单规则
            if($this->checkBillNo($reqAll)){
                return ['status' => false, 'msg' => '请修改账单编号,不能使用系统保留的账单编号!'];
            }
            if (isset($reqAll["id"]) && trim($reqAll["id"]) != "") {
                $bill = ResourceBill::where("id", trim($reqAll["id"]))->first();
                if (!empty($bill)) {
                    $udata = [
                        'contractId'        => $reqAll['contractId'],
                        'billNo'            => $reqAll['billNo'],
                        'billStart'         => $reqAll['billStart'],
                        'billEnd'           => $reqAll['billEnd'],
                        'billExpire'        => $reqAll['billExpire'],
                        'oneCost'           => null==$reqAll['oneCost']?0:$reqAll['oneCost'],
                        'cycleCost'         => null==$reqAll['cycleCost']?0:$reqAll['cycleCost'],
                        'discount'          => null==$reqAll['discount']?0:$reqAll['discount'],
                        'otherType'         => $reqAll['otherType'],
                        'otherAmount'       => null==$reqAll['otherAmount']?0:$reqAll['otherAmount'],
                        'billAmount'        => null==$reqAll['billAmount']?0:$reqAll['billAmount'],
                        'describe'          => $describe,
                        'updatedAt'         => date('Y-m-d H:i:s'),
                        'updatedBy'         => $user->Id,
                    ];
                    $update = ResourceBill::where("id", trim($reqAll["id"]))->update($udata);

                    if ($update) {
                        //添加操作记录
                        ResourceRecord::insertGetId([
                            "moduleId"=>$reqAll["id"],
                            "module"=>"bill",
                            "optType"=>"Modify",
                            "optContent"=>$this->compareBillInfo($bill,$udata),
                            "optId"=> $user->Id,
                            "optTs"=> date('Y-m-d H:i:s')
                        ]);

                        return ['status' => $update, 'msg' => '保存成功!'];
                    }else {
                        return ['status' => false, 'msg' => '数据异常，保存失败!'];
                    }
                } else {
                    return ['status' => false, 'msg' => '获取原始账单失败，操作失败!'];
                }
            } else {
                $bill = ResourceBill::where("billNo",trim($reqAll["billNo"]))->first();
                if($bill){
                    return ['status' => false, 'msg' => '账单已存在,请确认后重新输入!'];
                }else{
                    $billId = ResourceBill::insertGetId([
                        'contractId'        => $reqAll['contractId'],
                        'billNo'            => $reqAll['billNo'],
                        'payStatus'         => "new",
                        'billStart'         => $reqAll['billStart'],
                        'billEnd'           => $reqAll['billEnd'],
                        'billExpire'        => $reqAll['billExpire'],
                        'oneCost'           => null==$reqAll['oneCost']?0:$reqAll['oneCost'],
                        'cycleCost'         => null==$reqAll['cycleCost']?0:$reqAll['cycleCost'],
                        'discount'          => null==$reqAll['discount']?0:$reqAll['discount'],
                        'otherType'         => $reqAll['otherType'],
                        'otherAmount'       => null==$reqAll['otherAmount']?0:$reqAll['otherAmount'],
                        'billAmount'        => null==$reqAll['billAmount']?0:$reqAll['billAmount'],
                        'describe'          => $describe,
                        'createdBy'         => $user->Id,
                        'createdAt'         => date('Y-m-d H:i:s')
                    ]);
                    if ($billId == false) {//插入数据失败
                        return ['status' => false, 'msg' => '提交出错,请稍后再试!'];
                    } else {
                        // 操作记录
                        ResourceRecord::insertGetId([
                            "moduleId"=>$billId,
                            "module"=>"bill",
                            "optType"=>"New",
                            "optContent"=>"新建账单",
                            "optId"=> $user->Id,
                            "optTs"=> date('Y-m-d H:i:s')
                        ]);

                        return ['status' => $billId, 'msg' => '新增账单成功!'];
                    }
                }
            }
        }
    }

    private function checkBillNo($reqAll){
        if(null == $reqAll["billSeq"]){
            $contractNo = trim($reqAll["contractNo"])."_AE";
            $billNo = trim($reqAll["billNo"]);

            $val = str_replace($contractNo,"",$billNo);

            return is_numeric($val);
        }else{
            return false;
        }

    }

    private function compareBillInfo($bill,$udata){
        $updContent="";
        if($bill['contractId']!=$udata['contractId']){
            $updContent .="合同id由:".$bill['contractId'] ." 变更为 ".$udata['contractId'].",";
        }
        if($bill['billNo']!=$udata['billNo']){
            $updContent .="账单编号由:".$bill['billNo'] ." 变更为 ".$udata['billNo'].",";
        }
        if($bill['billStart']!=$udata['billStart']){
            $updContent .="账单开始时间由:".$bill['billStart'] ." 变更为 ".$udata['billStart'].",";
        }
        if($bill['billEnd']!=$udata['billEnd']){
            $updContent .="账单结束时间由:".$bill['billEnd'] ." 变更为 ".$udata['billEnd'].",";
        }
        if($bill['billExpire']!=$udata['billExpire']){
            $updContent .="账单日期由:".$bill['billExpire'] ." 变更为 ".$udata['billExpire'].",";
        }
        if($bill['oneCost']!=$udata['oneCost']){
            $updContent .="首次费用由:".$bill['oneCost'] ." 变更为 ".$udata['oneCost'].",";
        }
        if($bill['cycleCost']!=$udata['cycleCost']){
            $updContent .="周期金额由:".$bill['cycleCost'] ." 变更为 ".$udata['cycleCost'].",";
        }
        if($bill['discount']!=$udata['discount']){
            $updContent .="折扣金额由:".$bill['discount'] ." 变更为 ".$udata['discount'].",";
        }
        if($bill['otherType']!=$udata['otherType']){
            $updContent .="其它类型由:".$bill['otherType'] ." 变更为 ".$udata['otherType'].",";
        }
        if($bill['otherAmount']!=$udata['otherAmount']){
            $updContent .="其它金额由:".$bill['otherAmount'] ." 变更为 ".$udata['otherAmount'].",";
        }
        return ""==$updContent?"":rtrim($updContent,",");
    }

    //检索合同编号
    public function findContractBySearch(Request $req)
    {
        if ($search = $req->input('name')) {
            return ThirdCallHelper::findContractBySearch($search);
        }
    }

    public function recordList(Request $req)
    {
        $list = ResourceRecord::where("moduleId",$req->get("billId"))->where("module",'bill')->get();
        $list = ResContact::translationStuff($list,"optId");
        return ["rows"=>$list];
    }

    //手动生成下期账单
    public function createBillByAjax(Request $req){
        if(!$req->get("contractId")){
            return ['status' => false, 'msg' => '获取合同信息失败,请刷新后重试!'];
        }else{
            return $this->createBill($req->get("contractId"));
        }
    }

    //执行中合同生成首期账单
    public function createBill($contractId)
    {
        $contract = ResourceContract::where("id", $contractId)->first();
        if($contract['status']!="doing"){
            return ['status' => false, 'msg' => '只有执行中合同才能生产下期账单!'];
        }

        $stsCode = 0;
        $stsMsg = "";
        $this->generateBill($contract, $stsCode, $stsMsg);
        if($stsCode!=0){
            return ['status' => false, 'msg' => $stsMsg];
        }else{
            return ['status' => true, 'msg' => '新增账单成功!'];
        }
    }

    //系统生成账单
    public function generateBill($contract ,&$stsCode=0 ,&$stsMsg="")
    {
        $seq=0;
        $startTs=$contract['startTs'];//合同开始日期
        $endTs=$contract['endTs'];//合同结束日期
        $billStart="";//账单开始日期
        $billEnd="";//账单结束日期
        $billExpire="";//账单账期

        $oneCost=$contract['oneTotalPrice'];//一次性金额
        $cycleCost=$contract['unitCyclePrice'];//周期金额
        $oneDiscount=$contract['oneDiscount'];//一次性优惠
        $cycleDiscount=$contract['cycleDiscount'];//周期优惠

        $paymentCycle=$contract['paymentCycle'];//付费方式
        $balanceCycle=$contract['balanceCycle'];//结算周期
        $days=$contract['days'];//后付天数
        if($contract['paymentMode']=="advances"){//预付
            $days=-$days;
        }
        $lastBill = ResourceBill::where("contractId",$contract['id'])
                ->whereRaw("seq is not null")
                ->orderBy('id', 'desc')
                ->first();

        if($balanceCycle==0){//周期
            if(!$lastBill){//首期账单
                $billStart = $startTs;
                if($paymentCycle == 0){
                    $billEnd = $endTs;
                }else{
                    $billEnd = date("Y-m-d",strtotime("+$paymentCycle month -1 day",strtotime($billStart)));
                }
            }else if($lastBill['billEnd']>=$contract['endTs']){//是否产生下期账单
                $stsCode = 1;
                $stsMsg = "当前合同对应账单已经全部产生!";
                return;
            }else{//下一期账单
                $startTs = $lastBill['billStart'];
                $seq = $lastBill['seq'] + 1;
                $billStart =date("Y-m-d",strtotime("+$paymentCycle month ",strtotime($startTs)));
                $billEnd = date("Y-m-d",strtotime("+$paymentCycle month -1 day",strtotime($billStart)));
            }
        }else{//自然(结算周期)
            if(!$lastBill){//首期账单
                $billStart = $startTs;
            }else if($lastBill['billEnd']>=$contract['endTs']){//是否产生下期账单
                $stsCode = 1;
                $stsMsg = "当前合同对应账单已经全部产生!";
                return;
            }else{//下一期账单
                $billStart = $lastBill['billStart'];
                $seq = $lastBill['seq'] + 1;
            }

            if($paymentCycle==0){
                if(!$lastBill) {//首期账单
                    $billEnd = $endTs;
                }else{
                    $stsCode = 2;
                    $stsMsg = "系统不支持当前付费方式:".$paymentCycle."月!";
                    return;
                }
            }else if($paymentCycle==1){
                $this->dealBillByMonth($billStart ,$seq ,$billEnd ,$cycleCost ,$cycleDiscount ,$endTs);
            }else if($paymentCycle==3){
                $this->dealBillByQuarter($billStart ,$seq ,$billEnd ,$cycleCost ,$cycleDiscount ,$endTs);
            }else if($paymentCycle==6){
                $this->dealBillByHalfYear($billStart ,$seq ,$billEnd ,$cycleCost ,$cycleDiscount ,$endTs);
            }else if($paymentCycle==12){
                $this->dealBillByYear($billStart ,$seq ,$billEnd ,$cycleCost ,$cycleDiscount ,$endTs);
            }else{
                $stsCode = 2;
                $stsMsg = "系统不支持当前付费方式:".$paymentCycle."月!";
                return;
            }
        }
        $billExpire = date("Y-m-d",strtotime("+$days day",strtotime($billEnd)));

        if($seq>0){
            $oneCost=0;
            $oneDiscount=0;
        }

        $billId = ResourceBill::insertGetId([
            'contractId'        => $contract['id'],
            'billNo'            => $contract['contractNo']."_AE".$seq,
            'payStatus'         => "new",
            'billStart'         => $billStart,
            'billEnd'           => $billEnd,
            'billExpire'        => $billExpire,
            'oneCost'           => $oneCost,
            'cycleCost'         => $cycleCost,
            'discount'          => $oneDiscount+$cycleDiscount,
            'billAmount'        => $oneCost+$cycleCost-$oneDiscount-$cycleDiscount,
            'createdBy'         => 500000,
            'createdAt'         => date('Y-m-d H:i:s'),
            'seq'               => $seq
        ]);
        if ($billId == false) {//插入数据失败
            $stsCode = 99;
            $stsMsg = "提交出错,请稍后再试!";
            return;
        } else {
            // 操作记录
            ResourceRecord::insertGetId([
                "moduleId"=>$billId,
                "module"=>"bill",
                "optType"=>"New",
                "optContent"=>"系统生成第".$seq."期账单",
                "optId"=> 500000,
                "optTs"=> date('Y-m-d H:i:s')
            ]);

            $stsCode = 0;
            $stsMsg = "新增账单成功!";
            return;

        }
    }

    //批量操作(审核/申请/支付/删除)
    public function batchOperate(Request $req){
        $userId = $req->session()->get("user")->Id;
        if(!$req->get("optType")){
            return ['status' => false, 'msg' => '获取操作类型失败,请刷新后重试!'];
        }else{
            $billCount = ResourceBill::whereIn("id", $req->get("billIds"))->where("payStatus","success")->count();
            if($billCount > 0){
                return ['status' => false, 'msg' => '已支付的账单不支持批量操作,请重新选择!'];
            }

            if("delete"==$req->get("optType")){
                $udata =[
                    "deleted" =>"1"
                ];

                $update = ResourceBill::whereIn("id", $req->get("billIds"))->update($udata);

                //操作记录
                $arr=[];
                foreach($req->get("billIds") as $key){
                    $ar = [
                        "moduleId"      =>$key,
                        "module"        =>"bill",
                        "optType"       =>"Delete",
                        "optContent"    =>"批量删除账单",
                        "optId"         =>$userId ,
                        "optTs"         => date('Y-m-d H:i:s')
                    ];
                    $arr[]=$ar;
                };
                ResourceRecord::insert($arr);

                return ['status' => $update, 'msg' => '批量删除成功!'];
            }else if("audit"==$req->get("optType") || "application"==$req->get("optType")){
                $billCount = ResourceBill::whereIn("id", $req->get("billIds"))->where("payStatus","audit"==$req->get("optType")?"new":"audit")->count();
                if($billCount!=count($req->get("billIds"))){
                    return ['status' => false, 'msg' => ("audit"==$req->get("optType")?"批量审核只能选择未支付的账单":"批量申请只能选择审核中的账单").",请重新选择!"];
                }

                $udata =[
                    "payStatus"         =>$req->get("optType"),
                    'updatedBy'         => $userId,
                    'updatedAt'         => date('Y-m-d H:i:s')
                ];

                $update = ResourceBill::whereIn("id", $req->get("billIds"))->update($udata);
                //操作记录
                $arr=[];
                foreach($req->get("billIds") as $key){
                    $ar = [
                        "moduleId"      =>$key,
                        "module"        =>"bill",
                        "optType"       =>$req->get("optType"),
                        "optContent"    =>"批量".("audit"==$req->get("optType")?"审核":"申请")."账单",
                        "optId"         =>$userId ,
                        "optTs"         => date('Y-m-d H:i:s')
                    ];
                    $arr[]=$ar;
                };
                ResourceRecord::insert($arr);

                return ['status' => $update, 'msg' => "批量".("audit"==$req->get("optType")?"审核":"申请")."成功!"];

            }else if("payment"==$req->get("optType")){
                $billCount = ResourceBill::whereIn("id", $req->get("billIds"))
                    ->where("payStatus","application")
                    ->WhereNotNull("billStart")
                    ->WhereNotNull("billEnd")
                    ->WhereNotNull("billExpire")
                    ->count();
                if($billCount!=count($req->get("billIds"))){
                    return ['status' => false, 'msg' => '批量支付只支持状态为申请中的账单,请重新选择!'];
                }
                $udata =[
                    "payStatus" =>"success",
                    "payTs"     =>date('Y-m-d H:i:s'),
                    'updatedBy'         => $userId,
                    'updatedAt'         => date('Y-m-d H:i:s')
                ];

                $update = ResourceBill::whereIn("id", $req->get("billIds"))->update($udata);
                //操作记录
                $arr=[];
                foreach($req->get("billIds") as $key){
                    $ar = [
                        "moduleId"      =>$key,
                        "module"        =>"bill",
                        "optType"       =>"Payment",
                        "optContent"    =>"批量支付账单",
                        "optId"         =>$userId ,
                        "optTs"         => date('Y-m-d H:i:s')
                    ];
                    $arr[]=$ar;
                };
                ResourceRecord::insert($arr);

                return ['status' => $update, 'msg' => '批量支付成功!'];
            }else{
                return ['status' => false, 'msg' => '未知的操作类型,请刷新后重试!'];
            }
        }
    }

    //标记删除账单（单条）
    public function delBill($id,Request $req){
        $udata =[
            "deleted" =>"1"
        ];

        $update = ResourceBill::where("id", $id)->update($udata);

        //操作记录
        $ar = [
            "moduleId"      =>$id,
            "module"        =>"bill",
            "optType"       =>"Delete",
            "optContent"    =>"删除账单",
            "optId"         => $req->session()->get("user")->Id,
            "optTs"         => date('Y-m-d H:i:s')
        ];
        ResourceRecord::insert($ar);

        return ['status' => $update, 'msg' => '删除成功!'];
    }

    //处理结算为自然周期的按月账单
    private function dealBillByMonth(&$billStart ,$seq ,&$billEnd ,&$cycleCost ,&$cycleDiscount ,$endTs){
        if($seq==0){
            $month01 = date('Y-m-01', strtotime($billStart));
            $billEnd = date('Y-m-d', strtotime("$month01 +1 month -1 day"));

            $totalDays = round((strtotime($billEnd)-strtotime($month01))/3600/24);
        }else{
            $billStart = date('Y-m-d',strtotime("+1 month ",strtotime(date('Y-m-01', strtotime($billStart)))));
            $billEnd = date('Y-m-d',strtotime("$billStart +1 month -1 day"));

            $totalDays = round((strtotime($billEnd)-strtotime($billStart))/3600/24);
        }

        if(strtotime($billEnd)>strtotime($endTs)){//最后一期
            $billEnd = $endTs;
        }

        if($seq==0 || (strtotime($billEnd)==strtotime($endTs))){//首期或最后一期
            //实际天数
            $days = round((strtotime($billEnd)-strtotime($billStart))/3600/24);

            $totalDays = $totalDays + 1;//加上最后一天
            $days = $days + 1;//加上最后一天
            //实际每天消费/优惠金额
            $dayCost =  round($cycleCost/$totalDays,6) ;
            $dayDiscount =  round($cycleDiscount/$totalDays,6) ;


            $cycleCost = $dayCost * $days;
            $cycleDiscount = $dayDiscount * $days;

            $cycleCost = round($cycleCost,2) ;
            $cycleDiscount = round($cycleDiscount,2) ;
        }
    }

    //处理结算为自然周期的按季账单
    private function dealBillByQuarter(&$billStart ,$seq ,&$billEnd ,&$cycleCost ,&$cycleDiscount ,$endTs){
        $year = date('Y',strtotime($billStart)); //获取开始时间所在年
        $month = date('m',strtotime($billStart)); //获取开始时间所在月
        $quarter = ceil($month/3); //当前月份所在季度

        $totalDays=0; //当前季度有多少天
        if($seq==0){//首期账单
            if($quarter == 1){
                $billEnd =  $year."-03-31";
                $totalDays = round((strtotime($billEnd)-strtotime($year."-01-01"))/3600/24);
            }else if($quarter == 2){
                $billEnd =  $year."-06-30";
                $totalDays = round((strtotime($billEnd)-strtotime($year."-04-01"))/3600/24);
            }else if($quarter == 3){
                $billEnd =  $year."-09-30";
                $totalDays = round((strtotime($billEnd)-strtotime($year."-07-01"))/3600/24);
            }else if($quarter == 4){
                $billEnd =  $year."-12-31";
                $totalDays = round((strtotime($billEnd)-strtotime($year."-10-01"))/3600/24);
            }
        }else{
            //后续账单依次向后推一个季度
            if($quarter == 1){
                $billStart =  $year."-04-01";
            }else if($quarter == 2){
                $billStart =  $year."-07-01";
            }else if($quarter == 3){
                $billStart =  $year."-10-01";
            }else if($quarter == 4){
                $billStart =  ($year+1)."-01-01";
            }
            $billEnd = date('Y-m-d',strtotime("$billStart +3 month -1 day"));
            $totalDays = round((strtotime($billEnd)-strtotime($billStart))/3600/24);
        }

        if(strtotime($billEnd)>strtotime($endTs)){//最后一期
            $billEnd = $endTs;
        }

        if($seq==0 || (strtotime($billEnd)==strtotime($endTs))){//首期或最后一期
            //实际天数
            $days = round((strtotime($billEnd)-strtotime($billStart))/3600/24);

            $totalDays = $totalDays + 1;//加上最后一天
            $days = $days + 1;//加上最后一天
            //实际每天消费/优惠金额
            //实际每天消费/优惠金额
            $dayCost =  round($cycleCost/$totalDays,6) ;
            $dayDiscount =  round($cycleDiscount/$totalDays,6) ;


            $cycleCost = $dayCost * $days;
            $cycleDiscount = $dayDiscount * $days;

            $cycleCost = round($cycleCost,2) ;
            $cycleDiscount = round($cycleDiscount,2) ;
        }
    }

    //处理结算为自然周期的按半年账单
    private function dealBillByHalfYear(&$billStart ,$seq ,&$billEnd ,&$cycleCost ,&$cycleDiscount ,$endTs){
        $year = date('Y',strtotime($billStart)); //获取开始时间所在年
        $month = date('m',strtotime($billStart)); //获取开始时间所在月
        $halfYear = ceil($month/6); //当前月份为上半年或下半年

        $totalDays=0; //本半年有多少天
        if($seq==0){//首期账单
            if($halfYear == 1){
                $billEnd =  $year."-06-30";
                $totalDays = round((strtotime($billEnd)-strtotime($year."-01-01"))/3600/24);
            }else{
                $billEnd =  $year."-12-31";
                $totalDays = round((strtotime($billEnd)-strtotime($year."-07-01"))/3600/24);
            }
        }else{
            if($halfYear == 1){
                $billStart =  $year."-07-01";
            }else {
                $billStart =  ($year+1)."-01-01";
            }
            $billEnd = date('Y-m-d',strtotime("$billStart +6 month -1 day"));
            $totalDays = round((strtotime($billEnd)-strtotime($billStart))/3600/24);
        }

        if(strtotime($billEnd)>strtotime($endTs)){//最后一期
            $billEnd = $endTs;
        }

        if($seq==0 || (strtotime($billEnd)==strtotime($endTs))){//首期或最后一期
            //实际天数
            $days = round((strtotime($billEnd)-strtotime($billStart))/3600/24);

            $totalDays = $totalDays + 1;//加上最后一天
            $days = $days + 1;//加上最后一天
            //实际每天消费/优惠金额
            //实际每天消费/优惠金额
            $dayCost =  round($cycleCost/$totalDays,6) ;
            $dayDiscount =  round($cycleDiscount/$totalDays,6) ;


            $cycleCost = $dayCost * $days;
            $cycleDiscount = $dayDiscount * $days;

            $cycleCost = round($cycleCost,2) ;
            $cycleDiscount = round($cycleDiscount,2) ;
        }
    }

    //处理结算为自然周期的按年账单
    private function dealBillByYear(&$billStart ,$seq ,&$billEnd ,&$cycleCost ,&$cycleDiscount ,$endTs){
        $year = date('Y',strtotime($billStart)); //获取开始时间所在年

        $totalDays=0; //本年有多少天
        if($seq==0){//首期账单
            $billEnd =  $year."-12-31";
            $totalDays = round((strtotime($billEnd)-strtotime($year."-01-01"))/3600/24);
        }else{
            $billStart =  ($year+1)."-01-01";
            $billEnd =  date('Y-m-d',strtotime("$billStart +1 year -1 day"));

            $totalDays = round((strtotime($billEnd)-strtotime($billStart))/3600/24);
        }

        if(strtotime($billEnd)>strtotime($endTs)){//最后一期
            $billEnd = $endTs;
        }

        if($seq==0 || (strtotime($billEnd)==strtotime($endTs))){//首期或最后一期
            //实际天数
            $days = round((strtotime($billEnd)-strtotime($billStart))/3600/24);

            $totalDays = $totalDays + 1;//加上最后一天
            $days = $days + 1;//加上最后一天
            //实际每天消费/优惠金额
            //实际每天消费/优惠金额
            $dayCost =  round($cycleCost/$totalDays,6) ;
            $dayDiscount =  round($cycleDiscount/$totalDays,6) ;


            $cycleCost = $dayCost * $days;
            $cycleDiscount = $dayDiscount * $days;

            $cycleCost = round($cycleCost,2) ;
            $cycleDiscount = round($cycleDiscount,2) ;
        }
    }

    //计算账单超期天数
    private function isExpire($billList){
        $now = date('Y-m-d');
        foreach($billList as $bill){
            if("success"!=$bill['payStatus']){
                $days = round((strtotime($now)-strtotime($bill['billExpire']))/3600/24);
                if($days>0){
                    $bill['payStatus'] = $days;
                }
            }
        }
    }
}
