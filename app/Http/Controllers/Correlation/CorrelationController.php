<?php
/**
 * User: Wujiang <wuj@51idc.com>
 * Date: 10/12/16 16:26
 */
namespace Itsm\Http\Controllers\Correlation;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Itsm\Http\Controllers\Controller;
use Itsm\Model\Usercenter\Correlation;

class CorrelationController extends Controller
{
    /**
     * 新增关系
     * @param Request $req
     * @param Response $resp
     * @return array
     */
    public function create(Request $req, Response $resp)
    {

        $userId = $req->session()->get('user')->Id;
        $request = $req->input();

        $supportId = Arr::get($request, "supportId", 0);
        $changeId = Arr::get($request, "changeId", 0);
        $issueId = Arr::get($request, "issueId", 0);
        $repositoryId = Arr::get($request, "repositoryId", 0);
        $triggerId = Arr::get($request, "triggerId", 0);

        //1、验证是否已经存在有效的关联
        $correlaRows = Correlation::where([
            'supportId'    => $supportId,
            'changeId'     => $changeId,
            'issueId'      => $issueId,
            'repositoryId' => $repositoryId,
            'triggerId'    => $triggerId,
            'inValidate'   => 0,
        ])->get()->toArray();
        if (!$correlaRows) {
            $insertData = [
                'supportId'    => $supportId,
                'changeId'     => $changeId,
                'issueId'      => $issueId,
                'repositoryId' => $repositoryId,
                'triggerId'    => $triggerId,
                'userId'       => $userId,
                'ts'           => date('Y-m-d H:i:s', time()),
            ];
            if (Correlation::insert($insertData)) {
                return ['status' => "ok", "msg" => "新增关联成功!"];
            } else {
                return ['status' => 'error', "msg" => "新增关联失败!"];
            }
        }
    }

    /*
     * 在工单管理批量关联变更
     *
     */
    public function batchSupportChange(Request $req, Response $resp)
    {

        $userId = $req->session()->get('user')->Id;
        $request = $req->input();
        $changeIds = $req->input("changeIds");
        foreach ($changeIds as $changeId) {
            $changeId = $changeId['Id'];
            $supportId = Arr::get($request, "supportId", 0);
            $issueId = Arr::get($request, "issueId", 0);
            $repositoryId = Arr::get($request, "repositoryId", 0);
            $triggerId = Arr::get($request, "triggerId", 0);
            $insertData = [
                'supportId'    => $supportId,
                'changeId'     => $changeId,
                'issueId'      => $issueId,
                'repositoryId' => $repositoryId,
                'triggerId'    => $triggerId,
                'userId'       => $userId,
                'ts'           => date('Y-m-d H:i:s', time()),
            ];
            $data = Correlation::insert($insertData);
            if (!$data) {
                return ['status' => 'error', "msg" => "关联失败!"];
            }
        }
        return ['status' => 'ok', "msg" => "关联成功!"];
    }
    /*
     * 在工单管理批量关联问题
     *
     */
    public function batchSupportIssue(Request $req, Response $resp)
    {

        $userId = $req->session()->get('user')->Id;
        $request = $req->input();
        $issueIds = $req->input("issueIds");
        foreach ($issueIds as $issueId) {
            $issueId = $issueId['Id'];
            $supportId = Arr::get($request, "supportId", 0);
            $changeId = Arr::get($request, "changeId", 0);
            $repositoryId = Arr::get($request, "repositoryId", 0);
            $triggerId = Arr::get($request, "triggerId", 0);
            $insertData = [
                'supportId'    => $supportId,
                'changeId'     => $changeId,
                'issueId'      => $issueId,
                'repositoryId' => $repositoryId,
                'triggerId'    => $triggerId,
                'userId'       => $userId,
                'ts'           => date('Y-m-d H:i:s', time()),
            ];
            $data = Correlation::insert($insertData);
            if (!$data) {
                return ['status' => 'error', "msg" => "关联失败!"];
            }
        }
        return ['status' => 'ok', "msg" => "关联成功!"];
    }
    /*
     * 在变更管理批量关联工单
     *
     */
    public function batchChangeSupport(Request $req, Response $resp)
    {

        $userId = $req->session()->get('user')->Id;
        $request = $req->input();
        $supIds = $req->input("supIds");
        foreach ($supIds as $supId) {
            $supportId = $supId['Id'];
            $changeId = Arr::get($request, "changeId", 0);
            $issueId = Arr::get($request, "issueId", 0);
            $repositoryId = Arr::get($request, "repositoryId", 0);
            $triggerId = Arr::get($request, "triggerId", 0);
            $insertData = [
                'supportId'    => $supportId,
                'changeId'     => $changeId,
                'issueId'      => $issueId,
                'repositoryId' => $repositoryId,
                'triggerId'    => $triggerId,
                'userId'       => $userId,
                'ts'           => date('Y-m-d H:i:s', time()),
            ];
            $data = Correlation::insert($insertData);
            if (!$data) {
                return ['status' => 'error', "msg" => "关联失败!"];
            }
        }
        return ['status' => 'ok', "msg" => "关联成功!"];
    }
    /*
     * 在变更管理批量关联问题
     *
     */
    public function batchChangeIssue(Request $req, Response $resp)
    {

        $userId = $req->session()->get('user')->Id;
        $request = $req->input();
        $issueIds = $req->input("issueIds");
        foreach ($issueIds as $issueId) {
            $issueId = $issueId['Id'];
            $changeId = Arr::get($request, "changeId", 0);
            $supportId = Arr::get($request, "supportId", 0);
            $repositoryId = Arr::get($request, "repositoryId", 0);
            $triggerId = Arr::get($request, "triggerId", 0);
            $insertData = [
                'supportId'    => $supportId,
                'changeId'     => $changeId,
                'issueId'      => $issueId,
                'repositoryId' => $repositoryId,
                'triggerId'    => $triggerId,
                'userId'       => $userId,
                'ts'           => date('Y-m-d H:i:s', time()),
            ];
            $data = Correlation::insert($insertData);
            if (!$data) {
                return ['status' => 'error', "msg" => "关联失败!"];
            }
        }
        return ['status' => 'ok', "msg" => "关联成功!"];
    }
    /*
     * 在问题管理批量关联工单
     *
     */
    public function batchIssueSupport(Request $req, Response $resp)
    {

        $userId = $req->session()->get('user')->Id;
        $request = $req->input();
        $supIds = $req->input("supIds");
        foreach ($supIds as $supId) {
            $supportId = $supId['Id'];
            $changeId = Arr::get($request, "changeId", 0);
            $issueId = Arr::get($request, "issueId", 0);
            $repositoryId = Arr::get($request, "repositoryId", 0);
            $triggerId = Arr::get($request, "triggerId", 0);
            $insertData = [
                'supportId'    => $supportId,
                'changeId'     => $changeId,
                'issueId'      => $issueId,
                'repositoryId' => $repositoryId,
                'triggerId'    => $triggerId,
                'userId'       => $userId,
                'ts'           => date('Y-m-d H:i:s', time()),
            ];
            $data = Correlation::insert($insertData);
            if (!$data) {
                return ['status' => 'error', "msg" => "关联失败!"];
            }
        }
        return ['status' => 'ok', "msg" => "关联成功!"];
    }
    /*
    * 在问题管理批量关联变更
    *
    */
    public function batchIssueChange(Request $req, Response $resp)
    {

        $userId = $req->session()->get('user')->Id;
        $request = $req->input();
        $changeIds = $req->input("changeIds");
        foreach ($changeIds as $changeId) {
            $changeId = $changeId['Id'];
            $supportId = Arr::get($request, "supportId", 0);
            $issueId = Arr::get($request, "issueId", 0);
            $repositoryId = Arr::get($request, "repositoryId", 0);
            $triggerId = Arr::get($request, "triggerId", 0);
            $insertData = [
                'supportId'    => $supportId,
                'changeId'     => $changeId,
                'issueId'      => $issueId,
                'repositoryId' => $repositoryId,
                'triggerId'    => $triggerId,
                'userId'       => $userId,
                'ts'           => date('Y-m-d H:i:s', time()),
            ];
            $data = Correlation::insert($insertData);
            if (!$data) {
                return ['status' => 'error', "msg" => "关联失败!"];
            }
        }
        return ['status' => 'ok', "msg" => "关联成功!"];
    }
    /*
     * 在变更管理批量取消与问题的关联
     *
     */
    public function closeChangeToIssue(Request $request)
    {
        $userId = $request->session()->get('user')->Id;
        $deleteIds = $request->input("Ids");
        $changeId = $request->input("changeId");
        foreach ($deleteIds as $deleteId) {
            $dataId = Correlation::select('Id')->where([
                'issueId'    => $deleteId['Id'],
                'inValidate' => 0,
                'changeId'   => $changeId
            ])->get()->toArray();
            Correlation::where('Id', $dataId[0]['Id'])->update([
                'inValidateUserId' => $userId,
                'inValidateTs'     => date('Y-m-d H:i:s', time()),
                'inValidate'       => 1,
                'inValidateReason' => $request->input("reason")
            ]);
        }
        return array('status' => 'success', 'msg' => "操作成功");
    }

    /*
     * 在变更管理批量取消与工单的关联
     *
     */
    public function closeChangeToSupport(Request $request)
    {
        $userId = $request->session()->get('user')->Id;
        $deleteIds = $request->input("Ids");
        $changeId = $request->input("changeId");
        foreach ($deleteIds as $deleteId) {
            $dataId = Correlation::select('Id')->where([
                'supportId'  => $deleteId['Id'],
                'inValidate' => 0,
                'changeId'   => $changeId
            ])->get()->toArray();
            Correlation::where('Id', $dataId[0]['Id'])->update([
                'inValidateUserId' => $userId,
                'inValidateTs'     => date('Y-m-d H:i:s', time()),
                'inValidate'       => 1,
                'inValidateReason' => $request->input("reason")
            ]);
        }
        return array('status' => 'success', 'msg' => "操作成功");
    }

    /*
     * 在问题管理批量取消与变更的关联
     *
     */
    public function closeIssueToChange(Request $request)
    {
        $userId = $request->session()->get('user')->Id;
        $deleteIds = $request->input("Ids");
        $issueId = $request->input("issueId");
        foreach ($deleteIds as $deleteId) {
            $dataId = Correlation::select('Id')->where([
                'changeId'   => $deleteId['Id'],
                'inValidate' => 0,
                'issueId'    => $issueId
            ])->get()->toArray();
            Correlation::where('Id', $dataId[0]['Id'])->update([
                'inValidateUserId' => $userId,
                'inValidateTs'     => date('Y-m-d H:i:s', time()),
                'inValidate'       => 1,
                'inValidateReason' => $request->input("reason")
            ]);
        }
        return array('status' => 'success', 'msg' => "操作成功");
    }

    /*
     * 在问题管理批量取消与工单的关联
     *
     */
    public function closeIssueToSupport(Request $request)
    {
        $userId = $request->session()->get('user')->Id;
        $deleteIds = $request->input("Ids");
        $issueId = $request->input("issueId");
        foreach ($deleteIds as $deleteId) {
            $dataId = Correlation::select('Id')->where([
                'supportId'  => $deleteId['Id'],
                'inValidate' => 0,
                'issueId'    => $issueId
            ])->get()->toArray();
            Correlation::where('Id', $dataId[0]['Id'])->update([
                'inValidateUserId' => $userId,
                'inValidateTs'     => date('Y-m-d H:i:s', time()),
                'inValidate'       => 1,
                'inValidateReason' => $request->input("reason")
            ]);
        }
        return array('status' => 'success', 'msg' => "操作成功");
    }

    /*
     * 在工单管理批量取消与变更的关联
     *
     */
    public function closeSupportToChange(Request $request)
    {
        $userId = $request->session()->get('user')->Id;
        $deleteIds = $request->input("Ids");
        $supportId = $request->input("supportId");
        foreach ($deleteIds as $deleteId) {
            $dataId = Correlation::select('Id')->where([
                'changeId'   => $deleteId['Id'],
                'inValidate' => 0,
                'supportId'  => $supportId
            ])->get()->toArray();
            Correlation::where('Id', $dataId[0]['Id'])->update([
                'inValidateUserId' => $userId,
                'inValidateTs'     => date('Y-m-d H:i:s', time()),
                'inValidate'       => 1,
                'inValidateReason' => $request->input("reason")
            ]);
        }
        return array('status' => 'success', 'msg' => "操作成功");
    }

    /*
     * 在工单管理批量取消与问题的关联
     *
     */
    public function closeSupportToIssue(Request $request)
    {
        $userId = $request->session()->get('user')->Id;
        $deleteIds = $request->input("Ids");
        $supportId = $request->input("supportId");
        foreach ($deleteIds as $deleteId) {
            $dataId = Correlation::select('Id')->where([
                'issueId'    => $deleteId['Id'],
                'inValidate' => 0,
                'supportId'  => $supportId
            ])->get()->toArray();
            Correlation::where('Id', $dataId[0]['Id'])->update([
                'inValidateUserId' => $userId,
                'inValidateTs'     => date('Y-m-d H:i:s', time()),
                'inValidate'       => 1,
                'inValidateReason' => $request->input("reason")
            ]);
        }
        return array('status' => 'success', 'msg' => "操作成功");
    }
}