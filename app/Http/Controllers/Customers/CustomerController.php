<?php
/**
 * User: Wujiang <wuj@51idc.com>
 * Date: 8/16/16 13:41
 */
namespace Itsm\Http\Controllers\Customers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Itsm\Http\Controllers\Controller;
use Itsm\Model\Res\AuxStuff;
use Itsm\Model\Res\ResContact;
use Itsm\Model\Res\ResCusInf;

class CustomerController extends Controller
{
    /*客户信息 分类信息*/
    protected static $cusTypes = ['self' => '个人', 'Firm' => '公司', 'agent' => '代理商'];

    public function cusDetail(Request $req, Response $res, $id)
    {
        $cusInfo = ResCusInf::where('Id', $id)->where('InValidate', '0')->first();
        $stuff = AuxStuff::where('Id', $cusInfo->Sell)->first();

        $cusInfo->SellName = '';
        if ($stuff != null) {
            $cusInfo->SellName = $stuff->Name;
        }
        if (($cusType = $cusInfo->CusType) != '' && $cusType != null) {
            $cusInfo->CusTypeName = self::$cusTypes[$cusType];
        }
        $returnData['cusInfo'] = $cusInfo;
        $contacts = ResContact::where('CusInfId', $id)->where('InValidate', '0')->get();
        $contacts = ResContact::translationDict($contacts, 'ConType', 'contactType');
        $returnData['contacts'] = $contacts;

        return $returnData;
    }
}