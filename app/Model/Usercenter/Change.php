<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/31 0031
 * Time: 下午 7:28
 */

namespace Itsm\Model\Usercenter;

use Itsm\Model\Model;
use Itsm\Model\Res\AuxDict;
use Itsm\Model\Res\AuxStuff;

class Change extends Model
{

    protected $connection = 'usercenter';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'change';
    protected $guarded = [];
//  protected $fillable = [
//      'Id',
//      'RFCNO',
//      'changeTitle',
//      'changeObject',
//      'expectTs',
//      'changeType',
//      'changeCategory',
//      'changeSubCategory',
//      'changeReason',
//      'changeContext',
//      'changeRisk',
//      'applyUserId',
//      'applyTs',
//      'feasibilityUserId',
//      'feasibilityGroupId'
//  ];
    /**
     * 将员工Id转为Name
     * @param $array
     * @param $Code
     * @return mixed
     */
    static function translationStuff($array, $Code)
    {
        $allStuff = [];
        $stuffs = AuxStuff::Select('Name', 'Id')->get();
        foreach ($stuffs as $stuff) {
            $allStuff[$stuff->Id] = $stuff->Name;
        };
        foreach ($array as $item) {
            if (isset($allStuff[$item->{$Code}])) {
                $item->{$Code} = $allStuff[$item->{$Code}];
            }
        }
        return $array;
    }

    /**
     * 获取人员姓名简称
     * @param $array
     * @param $Code
     * @return mixed
     */
    static function subStuffName($array, $Code)
    {
        foreach ($array as $item) {//截取名省略姓
            $item->subName = mb_substr($item->{$Code}, -2);
        }
        return $array;
    }

    static function getApprover($array)
    {
        $allStuff = [];
        $stuffs = AuxStuff::Select('Name', 'Id')->get();
        foreach ($stuffs as $stuff) {
            $allStuff[$stuff->Id] = $stuff->Name;
        };
        $allDepart = [];
        $depart = AuxDict::select('Means', 'Code')->where('DomainCode', 'DepartType')->get();
        $secondDepart = AuxDict::select('Means', 'Code')->where('DomainCode', 'second_dept')->get();
        foreach ($depart as $d) {
            $allDepart[$d->Code] = $d->Means;
        }
        foreach ($secondDepart as $d) {
            $allDepart[$d->Code] = $d->Means;
        }
        foreach ($array as $item) {
            switch ($item->changeState) {
                case 'reject':
                    $item->approver = isset($allStuff[$item->applyUserId]) ? $allStuff[$item->applyUserId] : '无';
                    break;
                case 'approval':
                    $item->approver = isset($allDepart[$item->feasibilityGroupId]) ? $allDepart[$item->feasibilityGroupId] : '无';
                    break;
                case 'design':
                    $item->approver = isset($allStuff[$item->proDesigerId]) ? $allStuff[$item->proDesigerId] : '无';
                    break;
                case 'actualize':
                    $item->approver = isset($allDepart[$item->proDesigerGroupId]) ? $allDepart[$item->proDesigerGroupId] : '无';
                    break;
                case 'test':
                    $item->approver = isset($allDepart[$item->testGroupId]) ? $allDepart[$item->testGroupId] : '无';
                    break;
                case 'testApproval':
                    $leaderId = '';
                    if (!empty($item->proDesigerId)) {
                        $leaderId = AuxStuff::where('Id', $item->proDesigerId)->value('parentId');
                    }
                    $item->approver = isset($allStuff[$leaderId]) ? $allStuff[$leaderId] : '无';
                    break;
                case 'release':
                    $item->approver = isset($allDepart[$item->changeImplementGroupId]) ? $allDepart[$item->changeImplementGroupId] : '无';
                    break;
                case 'approved':
                    $item->approver = isset($allStuff[$item->checkUserId]) ? $allStuff[$item->checkUserId] : '无';
                    break;
                default:
                    $item->approver = '无';
                    break;
            }
        }
        return $array;
    }

    static function transUrl($array,$code){
        foreach ($array as $record) {
            $record->{$code} = str_replace("http://itsm.51idc.com","https://itsm.anchnet.com",$record->{$code});
        }

        return $array;
    }
}