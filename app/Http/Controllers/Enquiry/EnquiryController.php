<?php

namespace Itsm\Http\Controllers\Enquiry;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Input;
use Itsm\Http\Controllers\Controller;
use Itsm\Http\Helper\ThirdCallHelper;
use Itsm\Model\Proddb\EnquiryRecord;
use Itsm\Model\Proddb\ProdEnquiry;
use Itsm\Model\Proddb\ProdOffer;
use Itsm\Model\Res\ResContact;
use Itsm\Model\Usercenter\Support;


class EnquiryController extends Controller
{
    //加载全部询价列表blade页面
    public function allList(Request $req)
    {
        $stepsList = ThirdCallHelper::getDictArray("询价状态","EnquirySteps");
        $role = $req->input("role")?$req->input("role"):"";
        return view("enquiry/alllist",["stepsList"=>$stepsList,"role"=>$role]);
    }

    //加载销售询价列表blade页面
    public function salesList()
    {
        $stepsList = ThirdCallHelper::getDictArray("询价状态","EnquirySteps");
        return view("enquiry/saleslist",["stepsList"=>$stepsList]);
    }

    //获取销售申请状态的list
    public function getSalesList(Request $req)
    {
        $eTable = "proddb.prodenquiry";
        $list = ProdEnquiry::select("$eTable.*")
            ->leftJoin("res.res_cusinf as b","$eTable.cusId","=","b.Id")
            ->where("$eTable.inValidate","0")
            ->where("$eTable.userId",$req->session()->get('user')->Id);
        if(($steps = $req->input("steps"))!=""){
            $list->where("$eTable.steps",$steps);
        }
        if(($priority = $req->input("priority"))!=""){
            $list->where("$eTable.priority",$priority);
        }
        if($begin = $req->input("beginTs")){
            $list->where("$eTable.ts",">",$begin);
        }
        if($end = $req->input("endTs")){
            $list->where("$eTable.ts","<",$end);
        }
        if($searchInfo = $req->input("searchInfo")){
            $list->where(function($list) use ($searchInfo,$eTable){
                $list->where("$eTable.title","like","%$searchInfo%")
                    ->orwhere("$eTable.enquiryNo","like","%$searchInfo%")
                    ->orwhere("b.CusName","like","%$searchInfo%");
            });
        }
        $array['total'] = $list->count();

        //排序 完成放最后，其他按提交时间倒叙
        $list->orderByRaw("steps='inquiryEnd',ts desc");

        //分页
        $pageSize = !empty($req->input('pageSize')) ? $req->input('pageSize') : 20;
        $pageNumber = !empty($req->input('pageNumber')) ? $req->input('pageNumber') : 1;
        $list = $list->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();

        //公共转换
        $list = Support::translationDict($list,"steps","EnquirySteps");
        $list = Support::translationCusName($list, 'cusId');
        $list = ResContact::translationStuff($list, 'userId');
        $userId = $req->session()->get("user")->Id;
        $list = $this->isSaleEdit($list,$userId);

        $array['rows'] = $list;
        return $array;
    }

    //获取alllistdata
    public function getAllList(Request $req)
    {
        $eTable = "proddb.prodenquiry";
        $userId = $req->session()->get("user")->Id;
        $list = ProdEnquiry::select("$eTable.*","b.Sell")
            ->leftJoin("res.res_cusinf as b","$eTable.cusId","=","b.Id")
            ->where("$eTable.inValidate",0);
        if(($role = $req->input("role"))!=""){
            if($role == "pro"){
                $list->whereRaw("$eTable.steps in ('productOffer','productConfirm')");
            }else if($role){
                $list->where("$eTable.steps",$role);
            }
        }else{
            if(!$this->hasUserRule("system_manager") && !$this->hasUserRule("product_confirm")) {//系统管理员含有全部权限
                $list->where(function($list) use ($userId,$eTable){
                    $list->where("$eTable.userId",$userId)
                        ->orwhere("$eTable.purchaseCheckerId",$userId)
                        ->orwhere("$eTable.upUserId",$userId);
                });
            }
        }

        if(($steps = $req->input("steps"))!=""){
            $list->where("$eTable.steps",$steps);
        }
        if(($priority = $req->input("priority"))!=""){
            $list->where("$eTable.priority",$priority);
        }
        if($begin = $req->input("beginTs")){
            $list->where("$eTable.ts",">",$begin);
        }
        if($end = $req->input("endTs")){
            $list->where("$eTable.ts","<",$end);
        }
        if($searchInfo = $req->input("searchInfo")){
            $list->where(function($list) use ($searchInfo,$eTable){
                $list->where("$eTable.title","like","%$searchInfo%")
                    ->orwhere("$eTable.enquiryNo","like","%$searchInfo%")
                    ->orwhere("b.CusName","like","%$searchInfo%");
            });
        }
        $array['total'] = $list->count();

        //排序 完成放最后，其他按提交时间倒叙
        $list->orderByRaw("steps='inquiryEnd',ts desc");

        //分页
        $pageSize = !empty($req->input('pageSize')) ? $req->input('pageSize') : 20;
        $pageNumber = !empty($req->input('pageNumber')) ? $req->input('pageNumber') : 1;
        $list = $list->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();

        //公共转换
        $list = Support::translationDict($list,"steps","EnquirySteps");
        $list = Support::translationCusName($list, 'cusId');
        $list = ResContact::translationStuff($list, 'userId');
        $list = ResContact::translationStuff($list, 'Sell');
        $list = $this->isEdit($list);


        $array['rows'] = $list;
        return $array;
    }

    /**
     * 判断当人登陆人员是否有对应角色权限
     * @param $list
     * @return mixed
     */
    public function isEdit($list)
    {
        foreach($list as $item){
            $isEdit = false;
            if ($item->steps == "产品报价" || $item->steps == "产品审核确认") {
                $isEdit = $this->hasUserRule("product_confirm");
            } elseif ($item->steps == "资源报价") {
                $isEdit = $this->hasUserRule("resources");
            } elseif ($item->steps == "采购报价") {
                $isEdit = $this->hasUserRule("purchase");
            }
            $isEdit = $this->hasUserRule("system_manager")?true:$isEdit;//系统管理员含有全部权限
            $item->isEdit = $isEdit;
        }
        return  $list;
    }

    public function isSaleEdit($list,$userId)
    {
        foreach ($list as $item) {
            $isEdit = false;
            if ($item->steps == "销售申请" && $item->userId == $userId) {
                $isEdit = $this->hasUserRule("sales_confirm");
            }
            $isEdit = $this->hasUserRule("system_manager")?true:$isEdit;//系统管理员含有全部权限
            $item->isEdit = $isEdit;
        }
        return $list;
    }

    /**
     * @param Request $req
     * @return mixed
     */
    public function getOfferList(Request $req)
    {
        if($enquiryId = $req->input("enquiryId")){
            $list = ProdOffer::where('enquiryId',trim($enquiryId))->where('inValidate',0);
            $array['total'] = $list->count();
            //分页
            $pageSize = !empty($req->input('pageSize')) ? $req->input('pageSize') :200;
            $pageNumber = !empty($req->input('pageNumber')) ? $req->input('pageNumber') : 1;
            $list = $list->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();
            $list = ResContact::translationStuff($list,"userId");
            $array['rows'] = $list;
            return $array;
        }
    }

    public function getRecordList(Request $req)
    {
        if($enquiryId = $req->input("enquiryId")){
            $list = EnquiryRecord::where('enquiryId',trim($enquiryId))->orderBy("ts",'desc');
            $array['total'] = $list->count();
            //分页
            $pageSize = !empty($req->input('pageSize')) ? $req->input('pageSize') : 200;
            $pageNumber = !empty($req->input('pageNumber')) ? $req->input('pageNumber') : 1;
            $list = $list->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();
            //公共转换
            $list = ResContact::translationStuff($list,"userId");
            $array['rows'] = $list;
            return $array;
        }
    }
}
