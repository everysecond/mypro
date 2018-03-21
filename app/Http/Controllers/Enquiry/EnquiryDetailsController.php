<?php
/**
 * User: Lidz<lidz@51idc.com>
 * Date: 2017/6/12 17:43
 */

namespace Itsm\Http\Controllers\Enquiry;


use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Itsm\Http\Controllers\Controller;
use Itsm\Http\Helper\ProcessMakerApi;
use Itsm\Http\Helper\ThirdCallHelper;
use Itsm\Jobs\ItsmMessageSend;
use Itsm\Model\Proddb\EnquiryRecord;
use Itsm\Model\Proddb\ProdEnquiry;
use Itsm\Model\Proddb\ProdOffer;

class EnquiryDetailsController extends Controller
{
    /**
     * 新增及编辑blade
     * @param Request $req
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function newEnquiry(Request $req)
    {
        $enquiryNo = date("Ymd", time());
        $prodEn = ProdEnquiry::where("enquiryNo", 'like', "$enquiryNo%")
            ->orderBy("enquiryNo","desc")->first();
        $thisNum = 1;
        if (!empty($prodEn) && $prodEn->enquiryNo) {//按当月最后一条编号加1生成
            $num = substr($prodEn->enquiryNo,-4);
            $thisNum = $num + 1;
        }
        $thisNum = sprintf('%04s', $thisNum);
        $enquiryId = Arr::get($req->input(), "enquiryId", "");
        $enquiry = "";
        if ($enquiryId != "") {
            $enquiry = ProdEnquiry::where("Id", $enquiryId)->first();
            if (!empty($enquiry)) {
                $enquiry->cusName = $enquiry->cusId ? ThirdCallHelper::getCusName($enquiry->cusId) : "";
            }
        }
        return view("enquiry/newenquiry",
            [
                "enquiryNo" => $enquiryNo . $thisNum,
                "enquiry"   => $enquiry
            ]);
    }

    //新增报价blade
    public function newOffer(Request $req)
    {
        if ($prodName = $req->input('name')) {//搜索对应客户
            return ThirdCallHelper::getProdName($prodName);
        }
        if($id = $req->input("enquiryId")){
            $enquiry = ProdEnquiry::where("id",trim($id))->first();
            $offer = "";
            if($offerId = $req->input("offerId")){
                $offer = ProdOffer::where("id",trim($offerId))->first();
            }
            $isUnitPriceEdit = $this->hasUserRule("product_confirm");
            if($enquiry){
                return view("enquiry/newoffer",
                    ["enquiry"=>$enquiry,"offer"=>$offer,"isUnitPriceEdit"=>$isUnitPriceEdit]);
            }
        }
    }
    public function offerDetail(Request $req)
    {
        $offer = "";
        if ($offerId = $req->input("offerId")) {
            $offer = ProdOffer::where("id", trim($offerId))->first();
        }
        return view("enquiry/offerDetail", ["offer" => $offer]);
    }

    /**
     * 产品报价blade
     * @param Request $req
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function productOffer($id)
    {
        $enquiry = "";
        if ($id != "") {
            $enquiry = ProdEnquiry::where("Id", $id)->first();
            $dynaForm = '';
            if (!empty($enquiry)) {
                $enquiry->cusName = $enquiry->cusId ? ThirdCallHelper::getCusName($enquiry->cusId) : "";
                $pm = new ProcessMakerApi(env("PROD_PROCESS_ID"), env("PROD_STEP_ONE_ID"));
                $tokenInfo = $this->getAccessTokenByRole("salesApplication", "prod");
                if (isset($tokenInfo['access_token']) && ($token = $tokenInfo['access_token'])) {//同步当前状态
                    $pmStep = $pm->getCaseCurrentTask($enquiry->caseId, $token);
                    if ($pmStep["status"] != $enquiry->steps) {
                        ProdEnquiry::where("caseId", $enquiry->caseId)->update(["steps" => $pmStep["status"]]);
                    }
                    $dynaForm = $pm->getCaseSelectForms($token, $enquiry->caseId, "btn btn-primary btnSub");
                } else {
                    return false;
                }
            }

            $isUnitPriceEdit = $this->hasUserRule("product_confirm");
            $isEdit = $this->isEdit($enquiry);
            return view("enquiry/productOffer",
                [
                    "enquiry"  => $enquiry,
                    'stepForm' => $dynaForm,
                    'isUnitPriceEdit' => $isUnitPriceEdit,
                    'isEdit' => $isEdit
                ]);
        }else{
            return "询价单不存在或已失效！";
        }
    }

    public function enquiryDetail($id)
    {
        $enquiry = "";
        if ($id != "") {
            $enquiry = ProdEnquiry::where("Id", $id)->first();
            if (!empty($enquiry)) {
                $enquiry->cusName = $enquiry->cusId ? ThirdCallHelper::getCusName($enquiry->cusId) : "";
                $pm = new ProcessMakerApi(env("PROD_PROCESS_ID"), env("PROD_STEP_ONE_ID"));
                $tokenInfo = $this->getAccessTokenByRole("salesApplication", "prod");
                if (isset($tokenInfo['access_token']) && ($token = $tokenInfo['access_token'])) {//同步当前状态
                    $pmStep = $pm->getCaseCurrentTask($enquiry->caseId, $token);
                    if ($pmStep["status"] != $enquiry->steps) {
                        ProdEnquiry::where("caseId", $enquiry->caseId)->update(["steps" => $pmStep["status"]]);
                    }
                } else {
                    return false;
                }
            }

            $isUnitPriceEdit = $this->hasUserRule("product_confirm");
            return view("enquiry/enquiryDetail",
                [
                    "enquiry"  => $enquiry,
                    "isUnitPriceEdit"  => $isUnitPriceEdit
                ]);
        } else {
            return "询价单不存在或已失效！";
        }
    }


    /**
     * 新增或保存销售询价申请单
     * @param Request $req
     * @return array
     */
    public function enquirySub(Request $req)
    {
        $reqAll = $req->all();
        $user = $req->session()->get('user');
        /*页面输入校验 验证提交内容是否规范*/
        $validator = Validator::make($reqAll, [
            'expectTs' => 'required',
        ], [
            'required' => ':attribute 的字段是必要的。',
        ]);

        $body = strip_tags(html_entity_decode(trim($req->input('body'), '<br><p><img><b><u><hr><span>')));
        if ($body == "" || strlen($body) == 0) {
            return ['status' => false, 'msg' => '询价内容有效部分为空或包含非法字符！'];
        }

        //判断是保存操作还是保存并且提交
        $steps = $reqAll["saveType"] == "onlySave" ? "salesApplication" :
            ($reqAll["saveType"] == "saveAndSub" ? "productOffer" : "");

        if ($validator->fails()) {//验证不通过,
            return ['status' => false, 'msg' => '提交失败!'];
        } else {
            $token = $this->getAccessTokenByRole("salesApplication", "prod");
            if (!isset($token['access_token'])) {
                return ['status' => false, 'msg' => '获取token错误!'];
            }
            $proApi = new ProcessMakerApi(env("PROD_PROCESS_ID"), env("PROD_STEP_ONE_ID"));
            if (isset($reqAll["enquiryId"]) && trim($reqAll["enquiryId"]) != "") {
                $enquiry = ProdEnquiry::where("Id",trim($reqAll["enquiryId"]))->first();
                if(!empty($enquiry) && $enquiry->steps == "salesApplication" &&  $steps == "productOffer"){
                    $caseInfo = $proApi->nextCase($enquiry->caseId,$token['access_token']);
                }else{
                    $caseInfo = $proApi->getCaseCurrentTask($enquiry->caseId,$token['access_token']);
                }
                if (!isset($caseInfo['status'])) {
                    $token = $this->getAccessTokenByRole("salesApplication", "prod");
                    if (!isset($token['access_token'])) {
                        return ['status' => false, 'msg' => '获取token错误!'];
                    }
                    $caseInfo = $proApi->getCaseCurrentTask($enquiry->caseId, $token['access_token']);
                }
                $udata = [
                    'title'       => $reqAll['title'],
                    'cusId'       => $reqAll['cusId'],
                    'priority'    => $reqAll['priority'],
                    'expectTs'    => $reqAll['expectTs'],
                    'steps'       => $caseInfo['status'],
                    'expectMoney' => $reqAll['expectMoney'],
                    'body'        => strip_tags(html_entity_decode(trim($req->input('body'))),
                        '<br><p><img><b><u><hr><span>'),
                    'upUserId'    => $user->Id,
                    'upTs'        => date('Y-m-d H:i:s')
                ];
                $update = ProdEnquiry::where("Id",trim($reqAll["enquiryId"]))->update($udata);

                if($steps != "salesApplication"){
                    $recordId = EnquiryRecord::insertGetId([
                        'enquiryId'    => trim($reqAll["enquiryId"]),
                        'noticeType'   => '',
                        'recordType'   => '销售提交申请',
                        'csIds'        => '',
                        'instructions' => '',
                        'userId'       => $user->Id,
                        'ts'           => date('Y-m-d H:i:s')
                    ]);

                    $this->sendMsg(50, "销售产品询价提醒", "sms,email,wechat", "详情请见产品询价管理！");
                }

                if($update){
                    return ['status' => $update, 'msg' => '操作成功!'];
                }else{
                    return ['status' => false, 'msg' => '数据异常，保存失败!'];
                }
            } else {
                if ($steps == "salesApplication") {
                    $caseInfo = $proApi->createNewCaseOnDraft($token['access_token']);
                }else{
                    $caseInfo = $proApi->createNewCase($token['access_token']);
                }

                if (!isset($caseInfo['caseId'])) {
                    return ['status' => false, 'msg' => '工作流API创建失败!'];
                }
                $ret = ProdEnquiry::insertGetId([
                    'caseId'      => $caseInfo['caseId'],
                    'enquiryNo'   => $reqAll['enquiryNo'],
                    'title'       => $reqAll['title'],
                    'cusId'       => $reqAll['cusId'],
                    'priority'    => $reqAll['priority'],
                    'expectTs'    => $reqAll['expectTs'],
                    'expectMoney' => $reqAll['expectMoney'],
                    'steps'       => $caseInfo['status'],
                    'userId'      => $user->Id,
                    'body'        => strip_tags(html_entity_decode(trim($req->input('body'))),
                        '<br><p><img><b><u><hr><span>'),
                    'ts'          => date('Y-m-d H:i:s'),
                    'upTs'          => date('Y-m-d H:i:s')

                ]);
                if ($ret == false) {//插入数据失败
                    return ['status' => false, 'msg' => '提交出错,请稍后再试!'];
                } else {
                    $recordId = EnquiryRecord::insertGetId([
                        'enquiryId'    => $ret,
                        'noticeType'   => '',
                        'recordType'   => $steps == "salesApplication" ? '销售创建' : '销售创建并提交申请',
                        'csIds'        => '',
                        'instructions' => '',
                        'userId'       => $user->Id,
                        'ts'           => date('Y-m-d H:i:s')
                    ]);

                    if ($steps != "salesApplication") {
                        $this->sendMsg(50, "销售提交产品询价提醒", "sms,email,wechat", "详情请见产品询价管理！");
                    }

                    return ['status' => $ret, 'msg' => '操作成功!'];
                }
            }
        }
    }

    /**
     * 发送推送信息 groupId 50为产品部 资源24 采购53
     * @param $groupId
     * @param $title
     * @param $msgType
     * @param $content
     */
    public function sendMsg($groupId, $title, $msgType, $content)
    {
        $users = ThirdCallHelper::getGroupMembers($groupId);
        $ids = "";
        foreach ($users as $user) {
            $ids .= $user->UserId . ",";
        }
        $ids = trim($ids, ",");
        $job = new ItsmMessageSend($title, $ids, $msgType, $content); //创建队列任务
        $this->dispatch($job);
    }

    /**
     * 新增或修改保存报价单
     * @param Request $req
     * @return array
     */
    public function offerSub(Request $req)
    {
        $reqAll = $req->all();
        $user = $req->session()->get('user');
        /*页面输入校验 验证提交内容是否规范*/
        $validator = Validator::make($reqAll, [
            'prodName' => 'required',
        ], [
            'required' => ':attribute 的字段是必要的。',
        ]);

        if ($validator->fails()) {//验证不通过,
            return ['status' => false, 'statusMsg' => '提交失败!'];
        } else {
            if (isset($reqAll["enquiryId"]) && trim($reqAll["enquiryId"]) != "") {
                if (isset($reqAll["offerId"]) && trim($reqAll["offerId"]) != "") {
                    $enquiry = ProdEnquiry::where("id", trim($reqAll["enquiryId"]))->first();
                    $offer = ProdOffer::where("id", trim($reqAll["offerId"]))->first();
                    if (!empty($offer)) {
                        $udata = [
                            'prodName'  => $reqAll['prodName'],
                            'prodPC'    => $reqAll['prodPC'],
                            'describe'  => strip_tags(html_entity_decode(trim($req->input('describe'))),
                                '<br><p><img><b><u><hr><span>'),
                            'amount'    => $reqAll['amount'],
                            'unitPrice' => $reqAll['unitPrice'],
                            'costPrice' => $reqAll['costPrice'],
                            'upUserId'  => $user->Id,
                            'upTs'      => date('Y-m-d H:i:s')
                        ];
                        $update = ProdOffer::where("id", trim($reqAll["offerId"]))->update($udata);
                        if ($update) {
                            return ['status' => $update, 'msg' => '修改保存成功!'];
                        } else {
                            return ['status' => false, 'msg' => '数据异常，保存失败!'];
                        }
                    }
                } else {
                    $enquiry = ProdEnquiry::where("id", trim($reqAll["enquiryId"]))->first();
                    if (!empty($enquiry)) {
                        $ret = ProdOffer::insertGetId([
                            'enquiryId' => trim($reqAll["enquiryId"]),
                            'prodName'  => $reqAll['prodName'],
                            'prodPC'    => $reqAll['prodPC'],
                            'describe'  => strip_tags(html_entity_decode(trim($req->input('describe'))),
                                '<br><p><img><b><u><hr><span>'),
                            'amount'    => $reqAll['amount'],
                            'unitPrice' => $reqAll['unitPrice'],
                            'costPrice' => $reqAll['costPrice'],
                            'userId'    => $user->Id,
                            'upUserId'    => $user->Id,
                            'upTs'      => date('Y-m-d H:i:s'),
                            'ts'        => date('Y-m-d H:i:s')

                        ]);
                        if ($ret == false) {//插入数据失败
                            return ['status' => false, 'msg' => '提交出错,请稍后再试!'];
                        } else {
                            return ['status' => true, 'msg' => '操作成功!'];
                        }
                    } else {
                        return ['status' => false, 'msg' => '未检测到主表Id,请刷新重试!'];
                    }
                }
            }
        }
    }

    /**
     * 删除offer操作
     * @param Request $req
     * @return array
     */
    public function delOffer(Request $req)
    {
        if ($id = $req->input("offerId")) {
            $udata = [
                "inValidate"   => 1,
                "inValidateAt" => date('Y-m-d H:i:s'),
                "upUserId"     => $user = $req->session()->get('user')->Id,
                "upTs"         => date('Y-m-d H:i:s')
            ];
            $re = ProdOffer::where('id', $id)->update($udata);
            if ($re) {
                return ["status" => true, "msg" => "删除成功！"];
            } else {
                return ["status" => false, "msg" => "删除失败！"];
            }
        }
    }

    public function productOfferSub(Request $req)
    {
        if (($id = $req->input("enquiryId"))) {
            $userId = $req->session()->get('user')->Id;
            $enquiry = ProdEnquiry::where("id", $id)->first();
            $curStatus = $enquiry->steps;
            $all = $req->all();
            if (!empty($enquiry) && $caseId = $enquiry->caseId) {
                $nextStatus = $this->getNextCase($caseId, $all);
                $udata = [
                    "steps"    => $nextStatus,
                    "upUserId" => $req->session()->get('user')->Id,
                    "upTs"     => date('Y-m-d H:i:s')
                ];

                if($curStatus == "productOffer" || $curStatus == "productConfirm"){
                    $udata["prodCheckerId"] = $userId;
                    $udata["prodCheckTs"] = date('Y-m-d H:i:s');
                }else if($curStatus == "purchaseQuotes"){
                    $udata["purchaseCheckerId"] = $userId;
                    $udata["purchaseCheckTs"] = date('Y-m-d H:i:s');
                }

                $re = ProdEnquiry::where("id", $id)->update($udata);
                if ($re && $req->input("conf") != "") {
                    $noticeType = "";
                    if (!empty($req->input("noticeType"))) {
                        foreach ($req->input("noticeType") as $item) {
                            $noticeType .= $item . ",";
                        }
                    }
                    $noticeType = $noticeType != "" ? $noticeType : "sms,email,wechat";
                    $recordType = "";
                    switch ($enquiry->steps) {
                        case "salesApplication":$recordType =  "销售提交申请";break;
                        case "productOffer":
                            if(trim($req->input("conf")) == "转资源询价"){
                                $recordType =  "产品审核 转 资源审核";
                                $groupId = 24;//资源组Id
                            }else if(trim($req->input("conf")) == "转采购询价"){
                                $recordType =  "产品审核 转 采购审核";
                                $groupId = 53;//采购组Id
                            }else if(trim($req->input("conf")) == "退回销售"){
                                $recordType =  "产品审核 退回销售";
                                $reType = -1;//表示退回销售
                            }else if(trim($req->input("conf")) == "询价完成"){
                                $recordType =  "产品审核确认";
                                $reType = 1;//表示询价完成
                            }
                            break;
                        case "resourcesQuotes":$recordType =  "资源审核";break;
                        case "purchaseQuotes":$recordType =  "采购审核";break;
                        case "productConfirm":
                            if(trim($req->input("conf")) == "转资源询价"){
                                $recordType =  "产品审核 转 资源审核";
                                $groupId = 24;//资源组Id
                            }else if(trim($req->input("conf")) == "转采购询价"){
                                $recordType =  "产品审核 转 采购审核";
                                $groupId = 53;//采购组Id
                            }else if(trim($req->input("conf")) == "询价完成"){
                                $recordType =  "产品审核确认";
                                $reType = 1;//表示询价完成
                            }
                            break;
                    }

                    //根据不同流程方向选择不同推送消息接受人员
                    if (trim($noticeType, ",") != ""){
                        if (isset($groupId)) {
                            $this->sendMsg($groupId, "产品询价提醒(编号：$enquiry->enquiryNo)", trim($noticeType, ","),
                                "详情请见产品询价管理");
                        } elseif (isset($reType) && $reType == -1) {
                            $job = new ItsmMessageSend("产品询价退回提醒(编号：$enquiry->enquiryNo)", $enquiry->userId,
                                trim($noticeType, ","), "详情请见产品询价管理");
                            $this->dispatch($job);
                        } elseif (isset($reType) && $reType == 1) {
                            $job = new ItsmMessageSend("产品询价完成提醒(编号：$enquiry->enquiryNo)",
                                $enquiry->userId, trim($noticeType, ","), "详情请见产品询价管理");
                            $this->dispatch($job);
                        }
                    }

                    $recordId = EnquiryRecord::insertGetId([
                        'enquiryId'    => $id,
                        'noticeType'   => trim($noticeType, ","),
                        'recordType'   => $recordType,
                        'instructions' => $req->input("instructions"),
                        'userId'       => $req->session()->get('user')->Id,
                        'ts'           => date('Y-m-d H:i:s')
                    ]);

                    return ["status" => true, "msg" => "操作成功！"];
                } else {
                    return ["status" => false, "msg" => "操作失败！"];
                }
            } else {
                return ["status" => false, "msg" => "该询价单已不存在！"];
            }

        }
    }

    public function flowChart(){
        $currentStatus = Input::get('currentStatus');
        return view("enquiry/flowchart",['currentStatus'=>$currentStatus]);
    }

    /**
     * 获取下一流程状态
     * @param $caseId
     * @param $processVar
     */
    public function getNextCase($caseId, $all)
    {
        $variableVar = [];
        if (!empty($all["processVar"])) {//有则传递参数
            $variableVar = [$all["processVar"] => $all["processVal"]];
        }
        $tokenInfo = $this->getAccessTokenByRole("salesApplication", "prod");
        $pm = new ProcessMakerApi(env("PROD_PROCESS_ID"), env("PROD_STEP_ONE_ID"));
        $caseInfo = $pm->getCaseCurrentTask($caseId, $tokenInfo['access_token']);
        $curTakenInfo = $this->getAccessTokenByRole($caseInfo['status'], "prod");
        $nextRes = $pm->nextCase($caseId, $curTakenInfo['access_token'], $variableVar);
        if (isset($nextRes['status'])) {
            $status = $nextRes['status'];
        } else {
            $status = $caseInfo['status'];
        }
        return $status;
    }

    /**
     * @param $enquiry
     * @return bool
     */
    public function isEdit($enquiry)
    {
        $isEdit = false;
        if ($enquiry->steps == "productOffer" || $enquiry->steps == "productConfirm") {
            $isEdit = $this->hasUserRule("product_confirm");
        } elseif ($enquiry->steps == "resourcesQuotes") {
            $isEdit = $this->hasUserRule("resources");
        } elseif ($enquiry->steps == "purchaseQuotes") {
            $isEdit = $this->hasUserRule("purchase");
        }
        $isEdit = $this->hasUserRule("system_manager") ? true : $isEdit;//系统管理员含有全部权限
        return $isEdit;
    }
}