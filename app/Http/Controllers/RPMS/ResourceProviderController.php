<?php

namespace Itsm\Http\Controllers\RPMS;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Itsm\Http\Controllers\Controller;
use Itsm\Http\Helper\PublicMethodsHelper;
use Itsm\Http\Helper\ThirdCallHelper;
use Itsm\Model\Res\ResContact;
use Itsm\Model\Rpms\ResourceContact;
use Itsm\Model\Rpms\ResourceProd;
use Itsm\Model\Rpms\ResourceProvider;
use Itsm\Model\Rpms\ResourceType;
use MongoDB\BSON\Regex;


class ResourceProviderController extends Controller
{
    //新增或编辑资源供应商blade
    public function newProvider(Request $req)
    {
        $providerTypeList = ThirdCallHelper::getDictArray("资源服务商类型", "providerType");
        $stuffList = PublicMethodsHelper::getGroupMembers(48);
        $provider = "";
        if ($req->get("type") && $req->get("type") != "new") {
            $provider = ResourceProvider::where("id", $req->get("type"))->first();
        }
        return view("rpms/newprovider", [
            "provider"         => $provider,
            "stuffList"        => $stuffList,
            "providerTypeList" => $providerTypeList
        ]);
    }

    //新增或编辑资源供应商blade
    public function providerDetail(Request $req)
    {
        $providerTypeList = ThirdCallHelper::getDictArray("资源服务商类型", "providerType");
        $stuffList = PublicMethodsHelper::getGroupMembers(48);
        $provider = "";
        if ($req->get("type") && $req->get("type") != "new") {
            $provider = ResourceProvider::where("id", $req->get("type"))->first();
        }
        return view("rpms/providerdetail", [
            "provider"         => $provider,
            "stuffList"        => $stuffList,
            "providerTypeList" => $providerTypeList
        ]);
    }

    //新增或编辑资源供应商blade
    public function newContact(Request $req)
    {
        $contactTypeList = ThirdCallHelper::getDictArray("联系人类型", "contactType");
        $contact = "";
        if ($req->get("type") && $req->get("type") != "new") {
            $contact = ResourceContact::where("id", $req->get("type"))->first();
        }
        return view("rpms/newcontact", [
            "contact"         => $contact,
            "contactTypeList" => $contactTypeList
        ]);
    }

    public function contactDetail(Request $req)
    {
        $contactTypeList = ThirdCallHelper::getDictArray("联系人类型", "contactType");
        $contact = "";
        if ($req->get("type") && $req->get("type") != "new") {
            $contact = ResourceContact::where("id", $req->get("type"))->first();
        }
        return view("rpms/contactdetail", [
            "contact"         => $contact,
            "contactTypeList" => $contactTypeList
        ]);
    }

    /**
     * 获取资源子类型列表
     * @param Request $req
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getSonType(Request $req)
    {
        $code = $req->get("type");
        $sonTypeList = ResourceType::where("parentTypeCode", $code)->where("status", 0)->get();
        return $sonTypeList;
    }

    /**
     * 新建或编辑资源供应商
     * @param Request $req
     * @return array
     */
    public function newProviderSub(Request $req)
    {
        $reqAll = $req->all();
        $updata = $req->except(["_token", "providerId"]);
        foreach ($updata as $k => $item) {
            $updata[$k] = trim($item);
        }
        $user = $req->session()->get('user');
        /*页面输入校验 验证提交内容是否规范*/
        $validator = Validator::make($updata, [
            'providerName' => 'required'
        ], [
            'required' => ':attribute 的字段是必要的。',
        ]);

        $updata['describe'] = strip_tags(html_entity_decode(trim($updata['describe'], '<br><p><img><b><u><hr><span>')));

        if ($validator->fails()) {//验证不通过,
            return ['status' => false, 'msg' => '提交失败，类型编码和名称为必填!'];
        } else {
            if (isset($reqAll["providerId"]) && trim($reqAll["providerId"]) != "") {
                $prod = ResourceProvider::where("id", trim($reqAll["providerId"]))->first();
                $sameProd = ResourceProvider::where("id", "!=", trim($reqAll["providerId"]))
                    ->where("providerName", $updata['providerName'])
                    ->first();
                if (!empty($prod)) {
                    if (empty($sameProd)) {
                        $updata['updatedBy'] = $user->Id;
                        $updata['updates'] = $prod->updates + 1;
                        $update = ResourceProvider::where("id", trim($reqAll["providerId"]))->update($updata);

                        if ($update) {
                            return ['status' => $update, 'msg' => '保存成功!'];
                        } else {
                            return ['status' => false, 'msg' => '数据异常，保存失败!'];
                        }
                    } else {
                        return ['status' => false, 'msg' => '已存在相同名称的供应商，请修改后重试!'];
                    }
                } else {
                    return ['status' => false, 'msg' => '数据异常，保存失败!'];
                }
            } else {
                $prod = ResourceProvider::where("providerName", $updata['providerName'])->first();
                if (empty($prod)) {
                    $updata['createdBy'] = $user->Id;
                    $ret = ResourceProvider::insertGetId($updata);
                    if ($ret == false) {//插入数据失败
                        return ['status' => false, 'msg' => '提交出错,请稍后再试!'];
                    } else {
                        return ['status' => $ret, 'msg' => '新建成功!'];
                    }
                } else {
                    return ['status' => false, 'msg' => '已存在相同名称的供应商，请修改后重试!'];
                }
            }
        }
    }

    /**
     * 新建或编辑资源供应商联系人
     * @param Request $req
     * @return array
     */
    public function newContactSub(Request $req)
    {
        $reqAll = $req->all();
        $updata = $req->except(["_token", "providerId", "contactId","mobileType"]);
        foreach ($updata as $k => $item) {
            $updata[$k] = trim($item);
        }
        $user = $req->session()->get('user');
        /*页面输入校验 验证提交内容是否规范*/
        $validator = Validator::make($updata, [
            'name'   => 'required',
            'type'   => 'required',
            'mobile' => 'required'
        ], [
            'required' => ':attribute 的字段是必要的。',
        ]);

        if($reqAll["mobileType"] == "+86"){
            if(!preg_match("/^((13[0-9])|(14[5|7])|(15([0-9]))|(17([0-9]))|(18[0-9]))\\d{8}$/",$reqAll["mobile"],$matches)){
                return ['status' => false, 'msg' => '手机号格式不正确!'];
            };
        }

        if ($validator->fails()) {//验证不通过,
            return ['status' => false, 'msg' => '提交失败，请填写所有红色*号必填信息!'];
        } else {
            if (isset($reqAll["providerId"]) && trim($reqAll["providerId"]) != "") {
                if (isset($reqAll["contactId"]) && trim($reqAll["contactId"]) != "") {
                    $contact = ResourceContact::where("id", trim($reqAll["contactId"]))->first();
                    $sameContact = ResourceContact::where("id", "!=", trim($reqAll["contactId"]))
                        ->where("providerId", trim($reqAll['providerId']))
                        ->where("name", $updata['name'])
                        ->where("status",0)
                        ->first();
                    if (!empty($contact)) {
                        if (empty($sameContact)) {
                            $updata['updatedBy'] = $user->Id;
                            $updata['updates'] = $contact->updates + 1;
                            $update = ResourceContact::where("id", trim($reqAll["contactId"]))->update($updata);

                            if ($update) {
                                return ['status' => $update, 'msg' => '保存成功!'];
                            } else {
                                return ['status' => false, 'msg' => '数据异常，保存失败!'];
                            }
                        } else {
                            return ['status' => false, 'msg' => '已存在相同姓名联系人，请修改后重试!'];
                        }
                    } else {
                        return ['status' => false, 'msg' => '数据异常，保存失败!'];
                    }
                } else {
                    $sameContact = ResourceContact::where("providerId", trim($reqAll['providerId']))
                        ->where("name", $updata['name'])
                        ->where("status",0)
                        ->first();
                    if (empty($sameContact)) {
                        $updata['createdBy'] = $user->Id;
                        $updata['providerId'] = $reqAll['providerId'];
                        $ret = ResourceContact::insertGetId($updata);
                        if ($ret == false) {//插入数据失败
                            return ['status' => false, 'msg' => '提交出错,请稍后再试!'];
                        } else {
                            return ['status' => $ret, 'msg' => '新建成功!'];
                        }
                    } else {
                        return ['status' => false, 'msg' => '已存在相同名称的供应商，请修改后重试!'];
                    }
                }
            }else{
                return ['status' => false, 'msg' => '数据异常，请刷新列表页面重试!'];
            }
        }
    }

    public function delContact(Request $req){
        $user = $req->session()->get('user');
        $contact = ResourceContact::where("id",$req->get("contactId"))->first();
        if($contact){
            $contact["status"] = 1;//标记删除
            $contact['updatedBy'] = $user->Id;
            $contact['updates'] = $contact->updates + 1;
            if(!$contact->save()){
                return ['status' => 'failure'];
            }
        }else{
            return ['status' => 'failure'];
        }

        return ['status' => 'success'];
    }

    //加载资源供应商列表blade页面
    public function providerList()
    {
        return view("rpms/providerlist");
    }

    //资源供应商列表数据接口
    public function getProviderList(Request $req)
    {
        $list = ResourceProvider::select("*");
        if (($status = $req->input("status")) != "") {
            $list->where("status", $status);
        }

        if ($searchInfo = $req->input("searchInfo")) {
            $list->where(function ($list) use ($searchInfo) {
                $list->where("providerName", "like", "%$searchInfo%")
                    ->orwhere("id", "like", "%$searchInfo%");
            });
        }
        $array['total'] = $list->count();

        //排序 按使用频率及创建时间排序
        $list->orderByRaw("createdAt desc");

        //分页
        $pageSize = !empty($req->input('pageSize')) ? $req->input('pageSize') : 15;
        $pageNumber = !empty($req->input('pageNumber')) ? $req->input('pageNumber') : 1;
        $list = $list->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();

        //公共转换
        $list = ResContact::translationStuff($list, 'createdBy');
        $list = ResContact::translationStuff($list, 'innerCharger');
        $list = ResContact::translationDict($list, 'providerType', 'providerType');

        $array['rows'] = $list;
        return $array;
    }

    //供应商联系人数据接口
    public function getContactList(Request $req)
    {
        $list = ResourceContact::select("*")
            ->where("status",0)
            ->where("providerId",$req->get("providerId"));

        $array['total'] = $list->count();

        //排序 按使用频率及创建时间排序
        $list->orderByRaw("createdAt desc");

        //分页
        $pageSize = !empty($req->input('pageSize')) ? $req->input('pageSize') : 100;
        $pageNumber = !empty($req->input('pageNumber')) ? $req->input('pageNumber') : 1;
        $list = $list->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();

        //公共转换
        $list = ResContact::translationDict($list, 'type', 'contactType');

        $array['rows'] = $list;
        return $array;
    }

    public function batchOperate(Request $req)
    {
        $user = $req->session()->get('user');
        $supIds = $req->get('supIds');
        $batchType = $req->get("batchType");
        $upStatus = ($batchType == "up" ? 0 : ($batchType == "down" ? 1 : 2));

        if (count($supIds) > 0) {
            foreach ($supIds as $supId) {
                $resProd = ResourceProvider::where('id', $supId['id'])->first();
                if ($upStatus != $resProd->status) {
                    $resProd->updatedAt = date('Y-m-d H:i:s');
                    $resProd->updatedBy = $user->Id;
                    $resProd->updates = $resProd->updates + 1;
                    if ($upStatus == 2) {

                    } else {
                        $resProd->status = $upStatus;
                    }
                    if (!$resProd->save()) {
                        return ['status' => 'failure'];
                    }
                }
            }
            return ['status' => 'success'];
        }
        return ['status' => 'failure'];
    }
}
