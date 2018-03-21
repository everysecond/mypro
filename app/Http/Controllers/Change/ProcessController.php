<?php

namespace Itsm\Http\Controllers\Change;

use Illuminate\Http\Request;

use Illuminate\Support\Arr;
use Itsm\Http\Controllers\Controller;
use Itsm\Http\Helper\ProcessMakerApi;
use Itsm\Http\Requests;

class ProcessController extends Controller
{
    function create()
    {
        $role = $this->getProcessRole();
        dd($role);
        $pro = new ProcessMakerApi();
        $newCase = $pro->createNewCase(Arr::get($role, "0"));
        dd($newCase);
    }

    //demo演示
    function demo()
    {
        $pro = new ProcessMakerApi();
        $role = $this->getProcessRole();
        $rel = $pro->getAccessToken('change_changer', '123456');
//        $caseId = "33035876857d8c8b7713269010201705";
        $caseId = "88640077157d8d2fe042555018776751";

//        $res = $pro->getCaseCurrentTask($caseId, $rel['access_token']);
        $res = $pro->getCaseDynaForms($rel['access_token'], $caseId);
        dd($res);
    }

    function nextCase()
    {
        $pro = new ProcessMakerApi();
        //get token
        $rel = $pro->getAccessToken('aaron', '123456');
//        $rel = $pro->getAccessToken('admin', '51idc');
//        $rel = $pro->getAccessToken('hr', '123456');
        //create case
        echo $rel['access_token'];
        $case_id = "98501553257d24976e6d3a4049663580";
        $res = $pro->nextCase($case_id, $rel['access_token']);
        dd($res);

    }

    function getTodoCase()
    {
        $pro = new ProcessMakerApi();
        //get token
        $rel = $pro->getAccessToken('aaron', '123456');
//        $rel = $pro->getAccessToken('admin', '51idc');
//        $rel = $pro->getAccessToken('hr', '123456');
        //create case
        echo $rel['access_token'];
        $res = $pro->getTodoCases($rel['access_token'], []);
        dd($res);
    }

    function getParticipatedCases()
    {
        $pro = new ProcessMakerApi();
        //get token
        $rel = $pro->getAccessToken('aaron', '123456');
//        $rel = $pro->getAccessToken('admin', '51idc');
//        $rel = $pro->getAccessToken('hr', '123456');
        //create case
        echo $rel['access_token'];
        $res = $pro->getParticipatedCases($rel['access_token'], []);
        dd($res);
    }

    function getDraftCases()
    {
        $pro = new ProcessMakerApi();
        //get token
//        $rel = $pro->getAccessToken('aaron', '123456');
        $rel = $pro->getAccessToken('admin', '51idc');
//        $rel = $pro->getAccessToken('hr', '123456');
        //create case
        echo $rel['access_token'];
        $res = $pro->getDraftCases($rel['access_token'], []);
        dd($res);
    }

    function getAllCases()
    {
        $pro = new ProcessMakerApi();
        //get token
//        $rel = $pro->getAccessToken('aaron', '123456');
        $rel = $pro->getAccessToken('admin', '51idc');
//        $rel = $pro->getAccessToken('hr', '123456');
        //create case
        echo $rel['access_token'];
        $res = $pro->getAllCases($rel['access_token'], []);
        dd($res);
    }

    function nextCaseWithVariable()
    {
        $pro = new ProcessMakerApi();
        //get token
        $rel = $pro->getAccessToken('admin', '51idc');
        $caseId = "98501553257d24976e6d3a4049663580";
        $access = $this->getAccessTokenByRole("employee");
//        $res = $pro->setVariables($caseId, $rel['access_token'], ['manageCheckVar' => 1]);
        $res = $pro->nextCase($caseId, $rel['access_token']);
        dd($res);
    }

    function caseInfo()
    {
        $pro = new ProcessMakerApi();
        $role = $this->getProcessRole();
        $rel = $pro->getDraftCases('change_changer', '123456');
        $caseId = "33035876857d8c8b7713269010201705";
//        $caseId = "88640077157d8d2fe042555018776751";

        $res = $pro->getCaseCurrentTask($caseId, $rel['access_token']);
//        $res = $pro->getCaseDynaForms($role[0], $caseId);
        dd($res);
    }

    function getCaseVariables()
    {
        $pro = new ProcessMakerApi();
        //get token
        $rel = $pro->getAccessToken('admin', '51idc');
        $caseId = "23914483257d14020686558032549326";
        $res = $pro->getCaseVariables($caseId, $rel['access_token']);
        dd($res);
    }
}
