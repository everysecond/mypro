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


class ResourceProdController extends Controller
{
    //新增或编辑资源类型blade
    public function newProd(Request $req)
    {
        $prodTypeList = ResourceType::where("status", 0)
            ->where(function($prodTypeList)use($req){
                $prodTypeList = $prodTypeList->whereNull("parentTypeCode")
                    ->orWhere("parentTypeCode","");
            })->get();
        $prod = "";
        if ($req->get("type") && $req->get("type") != "new") {
            $prod = ResourceProd::where("id", $req->get("type"))->first();
        }
        $isSpecialLineList = ["no"=>"否","yes"=>"是"];
        return view("rpms/newprod", [
            "prodTypeList" => $prodTypeList,
            "isSpecialLineList" => $isSpecialLineList,
            "prod" => $prod
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
        //dd($sonTypeList->toSql());
        return $sonTypeList;
    }

    /**
     * 新建或编辑资源产品
     * @param Request $req
     * @return array
     */
    public function newProdSub(Request $req)
    {
        $reqAll = $req->all();
        $user = $req->session()->get('user');
        /*页面输入校验 验证提交内容是否规范*/
        $validator = Validator::make($reqAll, [
            'prodType' => 'required',
            'prodName' => 'required'
        ], [
            'required' => ':attribute 的字段是必要的。',
        ]);

        $describe = strip_tags(html_entity_decode(trim($req->input('describe'), '<br><p><img><b><u><hr><span>')));

        if ($validator->fails()) {//验证不通过,
            return ['status' => false, 'msg' => '提交失败，类型编码和名称为必填!'];
        } else {
            if (isset($reqAll["prodId"]) && trim($reqAll["prodId"]) != "") {
                $prod = ResourceProd::where("id", trim($reqAll["prodId"]))->first();
                $sameProd = ResourceProd::where("id", "!=", trim($reqAll["prodId"]))
                    ->where("prodName", $reqAll['prodName'])
                    ->first();
                if (!empty($prod)) {
                    if (empty($sameProd)) {
                        $udata = [
                            'prodType'  => $reqAll['prodType'],
                            'prodName'  => $reqAll['prodName'],
                            'sonType'   => $reqAll['sonType'],
                            'isSpecialLine'   => $reqAll['isSpecialLine'],
                            'unit'      => $reqAll['unit'],
                            'describe'  => $describe,
                            'unitPrice' => $reqAll['unitPrice'],
                            'onePrice'  => $reqAll['onePrice'],
                            'updatedBy' => $user->Id,
                            'updates'   => $prod->updates + 1
                        ];
                        $update = ResourceProd::where("id", trim($reqAll["prodId"]))->update($udata);

                        if ($update) {
                            return ['status' => $update, 'msg' => '保存成功!'];
                        } else {
                            return ['status' => false, 'msg' => '数据异常，保存失败!'];
                        }
                    } else {
                        return ['status' => false, 'msg' => '已存在资源类型及名称相同的资源产品，请修改后重试!'];
                    }
                } else {
                    return ['status' => false, 'msg' => '数据异常，保存失败!'];
                }
            } else {
                $prod = ResourceProd::where("prodName", $reqAll['prodName'])->first();
                if (empty($prod)) {
                    $ret = ResourceProd::insertGetId([
                        'prodType'  => $reqAll['prodType'],
                        'prodName'  => $reqAll['prodName'],
                        'isSpecialLine'  => $reqAll['isSpecialLine'],
                        'sonType'   => $reqAll['sonType'],
                        'unit'      => $reqAll['unit'],
                        'describe'  => $describe,
                        'unitPrice' => $reqAll['unitPrice'],
                        'onePrice'  => $reqAll['onePrice'],
                        'createdBy' => $user->Id

                    ]);
                    if ($ret == false) {//插入数据失败
                        return ['status' => false, 'msg' => '提交出错,请稍后再试!'];
                    } else {
                        return ['status' => $ret, 'msg' => '新建成功!'];
                    }
                } else {
                    return ['status' => false, 'msg' => '已存在资源类型及名称相同的资源产品，请修改后重试!'];
                }
            }
        }
    }

    //加载销售询价列表blade页面
    public function prodList()
    {
        return view("rpms/prodlist");
    }

    //资源类型列表数据接口
    public function getProdList(Request $req)
    {
        $list = ResourceProd::select("*");
        if (($status = $req->input("status")) != "") {
            $list->where("status", $status);
        }

        if ($statusType = $req->get("statusType")) {
            if ($statusType == "overTime") {
                $list->where("status", 2);
            } elseif ($statusType == "all") {
                $list->whereIn("status",[0,1,2]);
            } else {
                $list->whereIn("status", [0, 1]);
            }
        }
        if ($prodTypeOne = $req->get("prodTypeOne")) {
            $list->where("prodType",$prodTypeOne);
        }

        if ($searchInfo = $req->input("searchInfo")) {
            $list->where(function ($list) use ($searchInfo) {
                $list->where("prodType", "like", "%$searchInfo%")
                    ->orwhere("sonType", "like", "%$searchInfo%")
                    ->orwhere("prodName", "like", "%$searchInfo%");
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
        $fromView = $req->get("fromView");
        foreach ($list as $prod) {
            if($prod->prodType)$prod->typeName = ThirdCallHelper::getProdTypeName($prod->prodType);
            if($prod->prodType)$prod->prodTypeOne = $prod->prodType;

            if($prod->sonType)$prod->sonTypeName = ThirdCallHelper::getProdTypeName($prod->sonType);
            if($prod->sonType)$prod->prodTypeTwo = $prod->sonType;

            if($prod->onePrice)$prod->oneCost = $prod->onePrice;

            if($prod->prodType)$prod->prodTypeOneName = $prod->typeName;
            if($prod->sonType)$prod->prodTypeTwoName = $prod->sonTypeName;
            if($prod->onePrice || $prod->onePrice == 0)$prod->oneCost = $prod->onePrice;
            $prod->prodId = $prod->id;
            if ($fromView && $fromView == "pickResource") {//来源为合同选择资源页面则查询最新的合同明细产品价格
                $prodMingxiLast = ResourceContractProd::where("prodId",$prod->id)->orderBy("id","desc")->first();
                if($prodMingxiLast){
                    $prod->unitPrice = $prodMingxiLast->unitPrice;
                    $prod->oneCost = $prodMingxiLast->oneCost;
                }
            }
        }

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
                $resProd = ResourceProd::where('id', $supId['id'])->first();
                if ($upStatus != $resProd->status) {
                    $resProd->updatedAt = date('Y-m-d H:i:s');
                    $resProd->updatedBy = $user->Id;
                    $resProd->updates = $resProd->updates + 1;
                    if ($upStatus == 2) {
                        $mingxiList = ResourceContractProd::where("prodId",$supId['id'])->count();
                        if($mingxiList>0){
                            $resProd->status = $upStatus;
                            if (!$resProd->save()) {
                                return ['status' => 'failure'];
                            }
                        }else{
                            if (!$resProd->delete()) {
                                return ['status' => 'failure'];
                            }
                        }
                    } else {
                        $resProd->status = $upStatus;
                        if (!$resProd->save()) {
                            return ['status' => 'failure'];
                        }
                    }
                }
            }
            return ['status' => 'success'];
        }
        return ['status' => 'failure'];
    }

    public function getProdType(Request $req)
    {
        if ($search = $req->input('name')) {//搜索对应客户
            return ThirdCallHelper::getProdType($search);
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
            $type->parentTypeCode = ThirdCallHelper::getProdTypeName($type->parentTypeCode);
        }
    }
}
