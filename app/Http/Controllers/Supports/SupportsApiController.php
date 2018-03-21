<?php
/**
 * User: lidz <wuj@51idc.com>
 * Date: 27/09/17 17:42
 */
namespace Itsm\Http\Controllers\Supports;

use Illuminate\Http\Request;
use Itsm\Http\Controllers\Controller;
use Itsm\Http\Helper\PublicMethodsHelper;
use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Itsm\Http\Helper\ThirdCallHelper;
use Itsm\Model\Res\ResContact;
use Itsm\Model\Usercenter\Operation;
use Itsm\Model\Usercenter\Support;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Facades\Input;

class SupportsApiController extends Controller
{
    Public function create(Request $req)
    {
        if ($data = $req->get("data")) {
            if ($unionid = $data->unionid && PublicMethodsHelper::inValidateEmpty($data->unionid)) {
                $unionid = PublicMethodsHelper::removeStrHtml($unionid);
                if (!is_numeric($unionid) || strlen($unionid) > 20) {
                    return json_encode(["errcode" => -1, "errmsg" => "unionid is not integer or too long"]);
                }
            } else {
                return json_encode(["errcode" => -1, "errmsg" => "unionid is empty!"]);
            }

            if ($cusname = $data->cusname && PublicMethodsHelper::inValidateEmpty($data->cusname)) {
                $cusname = PublicMethodsHelper::removeStrHtml($cusname);
            } else {
                return json_encode(["errcode" => -1, "errmsg" => "cusname is empty or only space string!"]);
            }
        } else {
            return json_encode(["errcode" => -1, "errmsg" => "invalid data"]);
        }
    }
    protected static $tableSupport = 'usercenter.support';

    public function exportOperation(Request $req, Response $rep, Excel $excel)
    {
        $cusId = $req->get("cusId");
        $supportList = Operation::select("b.Id","b.Title","b.Ts",'usercenter.operation.ReplyTs','usercenter.operation.reply')
            ->leftJoin("usercenter.support as b","usercenter.operation.SupportId","=","b.Id")
        ->where("b.CustomerInfoId",$cusId)
        ->where("usercenter.operation.UCDis",1);

        $supportList = $supportList->take(5)->get();
        foreach($supportList as $support){
            $support->reply = strip_tags(trim($support->reply), '');
            $support->reply = str_replace("&nbsp;", "", $support->reply);
        }
        set_time_limit(0);
        ini_set('memory_limit', '256M');

        $filename = '工单筛选列表——' . date('Ymd', time());
        $filename = iconv('UTF-8', 'GBK',$filename);
        $export_data = [];

        foreach ($supportList as $key => $v) {
            $export_data[] = [
                "工单编号"   => $v['Id'],
                "工单标题"   => $v['Title'],
                "工单提交时间"   => $v['Ts'] ,
                "工单回复时间"   => $v['ReplyTs'],
                "回复内容"        => $v['reply']
            ];
        }
        //excel导出
        $excel->create($filename, function ($excel) use ($export_data) {
            $excel->sheet('export', function ($sheet) use ($export_data) {
                $sheet->fromArray($export_data);
            });
        })->export('xls');
        exit;
    }

    public function import(Excel $excel){
        $filePath = 'public/imports/'.iconv('UTF-8', 'GBK', '0.1').'.xlsx';
        $excel->load($filePath, function($reader) {
            dd($reader->ignoreEmpty()->getSheet(0)->toArray());

        });
    }
}