<?php

namespace Itsm\Http\Controllers\RPMS;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Itsm\Http\Controllers\Controller;
use Itsm\Http\Helper\ThirdCallHelper;
use Itsm\Model\Proddb\ProdType;
use Itsm\Model\Res\ResContact;
use Itsm\Model\Rpms\ResourceContractProd;
use Itsm\Model\Rpms\ResourceProd;
use Itsm\Model\Rpms\ResourceType;


class ResourceTypeController extends Controller
{
    //新增或编辑资源类型blade
    public function newType(Request $req)
    {
        $relateProdTypeList = $this->packageProdType();
        $type = "";
        if($req->get("type") && $req->get("type") != "new"){
            $type = ResourceType::where("id",$req->get("type"))->first();
        }
        return view("rpms/newtype", ["relateProdTypeList" => $relateProdTypeList, "type" => $type]);
    }

    /**
     * 新建或编辑资源类型
     * @param Request $req
     * @return array
     */
    public function newTypeSub(Request $req)
    {
        $reqAll = $req->all();
        $user = $req->session()->get('user');
        /*页面输入校验 验证提交内容是否规范*/
        $validator = Validator::make($reqAll, [
            'typeCode' => 'required',
            'typeName' => 'required'
        ], [
            'required' => ':attribute 的字段是必要的。',
        ]);

        $describe = strip_tags(html_entity_decode(trim($req->input('describe'), '<br><p><img><b><u><hr><span>')));
        $typeCode = $reqAll["typeCode"];
        if(preg_match('/[\x{4e00}-\x{9fa5}]/u', $typeCode)>0 ||!preg_match("/^[a-zA-Z\s]+$/",substr($typeCode,0,1))){
            return ['status' => false, 'msg' => '类型编码不得含有中文且首字符必须为字母'];
        }
        if ($validator->fails()) {//验证不通过,
            return ['status' => false, 'msg' => '提交失败，类型编码和名称为必填!'];
        } else {
            if (isset($reqAll["typeId"]) && trim($reqAll["typeId"]) != "") {
                $type = ResourceType::where("id", trim($reqAll["typeId"]))->first();
                $other = ResourceType::where(function($list)use($reqAll){
                    $list = $list->where("typeCode",trim($reqAll["typeCode"]))
                        ->orWhere("typeName",trim($reqAll["typeName"]));
                })->where("id","!=", trim($reqAll["typeId"]))->first();
                if(!empty($other)){
                    return ['status' => false, 'msg' =>  '已存在相同编码或名称的类型，请重新编辑!'];
                }else{
                    if (!empty($type)) {
                        $udata = [
                            'typeCode'       => $reqAll['typeCode'],
                            'typeName'       => $reqAll['typeName'],
                            'parentTypeCode' => $reqAll['parentTypeCode'],
                            'relateProdType' => $reqAll['relateProdType'],
                            'describe'       => $describe,
                            'updatedBy'      => $user->Id,
                            'updates'        => $type->updates + 1
                        ];
                        $update = ResourceType::where("Id", trim($reqAll["typeId"]))->update($udata);

                        if ($update) {
                            return ['status' => $update, 'msg' => '保存成功!'];
                        } else {
                            return ['status' => false, 'msg' => '数据异常，保存失败!'];
                        }
                    } else {
                        return ['status' => false, 'msg' => '数据异常，保存失败!'];
                    }
                }
            } else {
                $type = ResourceType::where(function($list)use($reqAll){
                    $list = $list->where("typeCode",trim($reqAll["typeCode"]))
                        ->orWhere("typeName",trim($reqAll["typeName"]));
                })->first();
                if($type){
                    if($type->status == 0){
                        return ['status' => false, 'msg' => '已存在相同编码或名称的类型，请修改后重试!'];
                    }else{
                        return ['status' => false, 'msg' => '已存在相同编码或名称已被停用的类型，请问是否直接启用!','typeId'=>$type->id];
                    }
                }else{
                    $ret = ResourceType::insertGetId([
                        'typeCode'       => $reqAll['typeCode'],
                        'typeName'       => $reqAll['typeName'],
                        'parentTypeCode' => $reqAll['parentTypeCode'],
                        'relateProdType' => $reqAll['relateProdType'],
                        'describe'       => $describe,
                        'createdBy'      => $user->Id
                    ]);
                    if ($ret == false) {//插入数据失败
                        return ['status' => false, 'msg' => '提交出错,请稍后再试!'];
                    } else {
                        return ['status' => $ret, 'msg' => '新建成功!'];
                    }
                }
            }
        }
    }

    //加载销售询价列表blade页面
    public function typeList()
    {
        return view("rpms/typelist");
    }

    //资源类型列表数据接口
    public function getTypeList(Request $req){
        $list = ResourceType::selectRaw("rpms.resource_type.*,count(b.id) as usecounts")
            ->leftJoin("rpms.resource_contract_prod as b",function($list){
            $list->on("rpms.resource_type.typeCode","=","b.prodTypeTwo")->orOn("rpms.resource_type.typeCode","=","b.prodTypeOne");
        });
        if(($status = $req->input("status"))!=""){
            $list->where("status",$status);
        }

        if($statusType = $req->get("statusType")){

            if($statusType == "overTime"){
                $list->where("status",2);
                //dd($list->toSql());
            }elseif($statusType == "all"){
                $list->whereIn("status",[0,1,2]);
            }else{
                $list->whereIn("status",[0,1]);
            }
        }

        if($searchInfo = $req->input("searchInfo")){
            $list->where(function($list) use ($searchInfo){
                $list->where("typeCode","like","%$searchInfo%")
                    ->orwhere("typeName","like","%$searchInfo%");
            });
        }
        $list = $list->groupBy("rpms.resource_type.id");


        //排序 完成放最后，其他按提交时间倒叙
        $list->orderByRaw("usecounts desc,createdAt desc");
        $array['total'] = count($list->get());

        //分页
        $pageSize = !empty($req->input('pageSize')) ? $req->input('pageSize') : 15;
        $pageNumber = !empty($req->input('pageNumber')) ? $req->input('pageNumber') : 1;
        $list = $list->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();
        //公共转换
        $list = ResContact::translationStuff($list, 'createdBy');
        $this->getRelateProdTypeName($list);
        $this->getParTypeName($list);

        $array['rows'] = $list;
        return $array;
    }

    //批量处理
    public function batchOperate(Request $req)
    {
        $user = $req->session()->get('user');
        $supIds = $req->get('supIds');
        $batchType = $req->get("batchType");
        $upStatus = ($batchType == "up" ? 0 : ($batchType == "down" ? 1 : 2));

        if (count($supIds) > 0) {
            foreach ($supIds as $supId) {
                $resType = ResourceType::where('id', $supId['id'])->first();
                if (isset($resType->status) && $upStatus != $resType->status) {
                    $resType->updatedAt = date('Y-m-d H:i:s');
                    $resType->updatedBy = $user->Id;
                    $resType->updates = $resType->updates + 1;
                    if ($upStatus == 2) {
                        $mingxiList = ResourceContractProd::where("prodTypeOne",$resType->typeCode)
                            ->orWhere("prodTypeTwo",$resType->typeCode)->count();
                        $sonTypes = ResourceType::where('parentTypeCode', $resType->typeCode)
                            ->whereIn("status",[0,1])->get();

                        if($mingxiList>0 || $this->checkProdUsedCount($supId)){
                            $resType->status =$upStatus;
                            if (!$resType->save()) {
                                return ['status' => 'failure'];
                            }

                            foreach ($sonTypes as $sonType) {
                                if ($upStatus != $sonType->status) {
                                    $sonType->updatedAt = date('Y-m-d H:i:s');
                                    $sonType->updatedBy = $user->Id;
                                    $sonType->updates = $resType->updates + 1;
                                    $sonType->status =$upStatus;
                                    if (!$sonType->save()) {
                                        return ['status' => 'failure'];
                                    }
                                }
                            }
                        }else{
                            if (!$resType->delete()) {
                                return ['status' => 'failure'];
                            }

                            foreach ($sonTypes as $sonType) {
                                if ($upStatus != $sonType->status) {
                                    if (!$sonType->delete()) {
                                        return ['status' => 'failure'];
                                    }
                                }
                            }
                        }
                    }else if($upStatus == 1) {
                        $resType->status =$upStatus;
                        $sonTypes = ResourceType::where('parentTypeCode', $resType->typeCode)
                            ->where("status",0)->get();
                        foreach ($sonTypes as $sonType) {
                            if ($upStatus != $sonType->status) {
                                $sonType->updatedAt = date('Y-m-d H:i:s');
                                $sonType->updatedBy = $user->Id;
                                $sonType->updates = $resType->updates + 1;
                                $sonType->status =$upStatus;
                                if (!$sonType->save()) {
                                    return ['status' => 'failure'];
                                }
                            }
                        }
                        if (!$resType->save()) {
                            return ['status' => 'failure'];
                        }
                    } else {
                        $resType->status =$upStatus;
                        if (!$resType->save()) {
                            return ['status' => 'failure'];
                        }
                    }
                }
            }
            return ['status' => 'success'];
        }
        return ['status' => 'failure'];
    }

    /**
     * 处理关联产品类型数据
     * @return array
     */
    public function packageProdType()
    {
        $ProdTypeList = ProdType::select("*")
            ->whereNull("InValidateAt")
            ->orderBy("sort", "desc")
            ->orderBy("id", "desc")
            ->get();
        $relateProdTypeList = [];
        foreach ($ProdTypeList as $prod) {
            if (!$prod->ParProdId || 0 == $prod->ParProdId) {
                $relateProdTypeList[] = $prod;
            }
            foreach ($ProdTypeList as $prod2) {
                if ($prod2->ParProdId && $prod2->ParProdId == $prod->id && $prod2->TypeName) {
                    $prod2->TypeName = "---" . $prod2->TypeName;
                    $relateProdTypeList[] = $prod2;
                }
            }
        }

        return $relateProdTypeList;
    }

    public function getProdType(Request $req)
    {
        if ($search = $req->input('name')) {//搜索对应客户
            $code = $req->get("selfType");
            $name = $req->get("selfName");
            return ThirdCallHelper::getProdType($search,$code,$name);
        }
    }

    /**
     * @param $list
     */
    public function getRelateProdTypeName($list)
    {
        foreach ($list as $type) {
            $type->relateProdType = ThirdCallHelper::getRelateProdTypeName($type->relateProdType);
        }
    }

    /**
     * @param $list
     */
    public function getParTypeName($list)
    {
        foreach ($list as $type) {
            if($type->parentTypeCode){
                $type->parentTypeName = ThirdCallHelper::getProdTypeName($type->parentTypeCode);
            }
        }
    }

    public function checkParCount(Request $req){
        $supIds = $req->get('supIds');
        if (count($supIds) > 0) {
            foreach ($supIds as $sup) {
                if($sup['parentTypeCode']){
                    $resType = ResourceType::where('typeCode', $sup['parentTypeCode'])->where("status","!=",0)->count();
                    if($resType>0)return ['status' => 'failure'];
                }
            }
            return ['status' => 'success'];
        }
        return ['status' => 'failure'];
    }

    public function checkSonCount(Request $req){
        $supIds = $req->get('supIds');
        if (count($supIds) > 0) {
            foreach ($supIds as $sup) {
                $resType = ResourceType::where('parentTypeCode', $sup['typeCode'])->where("status",0)->count();
                if($resType>0)return ['status' => 'failure'];
            }
            return ['status' => 'success'];
        }
        return ['status' => 'failure'];
    }

    //查询是否已有该资源类型产品
    public function checkProdUsedCount($supId){
        if ($id = $supId["id"]) {
            $sup = ResourceType::where("id",$id)->first();
            $prod = ResourceProd::where('prodType',$sup->typeCode)
                ->orWhere("sonType",$sup->typeCode)->count();
            if($prod>0)return true;
        }
        return false;
    }
}
