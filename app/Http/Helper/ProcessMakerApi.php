<?php
/**
 * User: Wujiang <wuj@51idc.com>
 * Date: 9/7/16 13:49
 */
namespace Itsm\Http\Helper;

use GuzzleHttp;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use Illuminate\Support\Arr;

class ProcessMakerApi
{
    const CLIENT_ID = "QILJGQTGHMZXZUIXOIOKZIECQALMOTVF";
    const CLIENT_SECRET = "24292637857ce823dee8071071718046";

    const PM_WS = "workflow";
    protected $pmUrl = "";
    /**
     * 查询pro_id
     * SELECT CON_ID, CON_VALUE FROM CONTENT WHERE CON_CATEGORY='PRO_TITLE'
     */
//    const PROCESS_ID = "17527820557d57556a8df01065152638";
    protected $processId;
    /**
     * 查询first step_id
     * SELECT TAS_UID FROM TASK WHERE PRO_UID='17527820557d57556a8df01065152638' AND TAS_START='TRUE'
     */
//    const STEP_ONE = "52890639757d5759722e739032553527";
    protected $stepOneId;

    public function __construct($processId, $stepOneId)
    {
        $this->processId = $processId;
        $this->stepOneId = $stepOneId;
        $this->pmUrl = env("PM_URL");
    }

    /**
     * 获取用户AccessToken
     * @param $userName
     * @param $password
     * @param string $grantType
     * @return array|string
     */
    public function getAccessToken($userName, $password, $grantType = "password")
    {
        $params = [
            'form_params' => [
                'grant_type'    => $grantType,
                'scope'         => '*',
                'client_id'     => self::CLIENT_ID,
                'client_secret' => self::CLIENT_SECRET,
                'username'      => $userName,
                'password'      => $password,
            ]
        ];
        $action = "/oauth2/token";

        $uri = $this->pmUrl . self::PM_WS . $action;
        $res = $this->guzzlePOST($uri, $params);
        return $res;
    }

    /**
     * 创建一个case,并且驱动当前步骤,返回下一步的current
     * @param $token
     * @return array|bool|mixed
     */
    public function createNewCase($token)
    {
        //request params
        $params = [
            'form_params' => [
                'pro_uid' => $this->processId,
                'tas_uid' => $this->stepOneId,
            ],
            'headers'     => [
                'Authorization' => 'Bearer ' . $token
            ]
        ];
        $action = "api/1.0/" . self::PM_WS . "/cases";

        $uri = $this->pmUrl . $action;
        $res = $this->guzzlePOST($uri, $params);
        if (isset($res['app_uid'])) {
            //创建完毕自动触发下一步
            $nextCaseRes = $this->nextCase($res['app_uid'], $token);
            if (isset($nextCaseRes['status'])) {
                return [
                    'status'     => $nextCaseRes['status'],
                    'caseId'     => $res['app_uid'],
                    'caseNumber' => $nextCaseRes['appNumber'],
                ];
            }
        }
        return $res;
    }

    /**
     * 创建一个case
     * @param $token
     * @return array|bool|mixed
     */
    public function createNewCaseOnDraft($token)
    {
        //request params
        $params = [
            'form_params' => [
                'pro_uid' => $this->processId,
                'tas_uid' => $this->stepOneId,
            ],
            'headers'     => [
                'Authorization' => 'Bearer ' . $token
            ]
        ];
        $action = "api/1.0/" . self::PM_WS . "/cases";

        $uri = $this->pmUrl . $action;
        $res = $this->guzzlePOST($uri, $params);

        if (isset($res['app_uid'])) {
            //创建完毕返回当前状态信息
            $curCaseRes = $this->getCaseCurrentTask($res['app_uid'],$token);
            if (isset($curCaseRes['status'])) {
                return [
                    'status'     => $curCaseRes['status'],
                    'caseId'     => $res['app_uid'],
                    'caseNumber' => $curCaseRes['appNumber'],
                ];
            }
        }
        return $res;
    }

    /**
     * 获取dynaForms
     * @param $token
     * @param $dynId
     * @return array
     */
    public function getCaseDynaForms($token, $caseId, $className = "pmOutput")
    {
        $dynaForms = [];
        $newItems = [];
        $submitLabel = "提交";
        //赋默认值
        $dynaFormContent = [
            'variable' => "",
            'form'     => "",
            'submit'   => "<input type=\"button\" value=\"{$submitLabel}\" class=\"{$className}\" />"
        ];

        //step1 获取所有dynaForm
        $allDynaFormsArr = $this->getAllDynaForms($token);
        //step2 获取当前case信息,返回当前current_task
        $currentInfo = $this->getCaseCurrentTask($caseId, $token);
        $currentTaskUid = Arr::get($currentInfo, "taskId" . "");
        //step3 根据当前task获取他的step
        if ($currentTaskUid) {
            $stepRes = $this->getTaskStepByTaskId($currentTaskUid, $token);
            /**
             * 如果下一步没有dynaForms,则stepRes是空数组
             */
            if (is_array($stepRes) && !isset($stepRes[0]) && !isset($stepRes['error'])) {
                return $dynaFormContent;
            } else {
                $dynaFormId = Arr::get($stepRes, "0.step_uid_obj", "");
            }
            //获取dynaForms信息
            foreach ($allDynaFormsArr as $item) {
                if (isset($item['dyn_uid']) && $item['dyn_uid'] == $dynaFormId) {
                    $dynaForms = $item;
                    break;
                }
            }
            //获取dynaCont节点内容
            $dynaCont = Arr::get($dynaForms, "dyn_content", "");
            if ($dynaCont) {
                $dynaCont = GuzzleHttp\json_decode($dynaCont, true);
            }
            //获取items节点
            $dynaItems = Arr::get($dynaCont, "items.0.items", "");
            if ($dynaItems) {
                foreach ($dynaItems as $dItem) {
                    if (Arr::get($dItem, "0.type") != "submit") {
                        $newItems = Arr::get($dItem, "0", "");
                    } else {
                        $submitLabel = Arr::get($dItem, "0.label", "");
                    }
                }
            }
        }

        if ($newItems) {
            $dynaFormContent = [
                'variable' => $newItems['variable'],
                'form'     => $this->dynaFormToHtml($newItems),
                'submit'   => "<input type=\"button\" value=\"{$submitLabel}\" class=\"{$className}\" />"
            ];
        }
        return $dynaFormContent;

    }

    public function getCaseSelectForms($token, $caseId, $className = "pmOutput")
    {
        $dynaForms = [];
        $newItems = [];
        $submitLabel = "提交";
        //赋默认值
        $dynaFormContent = [
            'variable' => "",
            'form'   => "",
            'submit' => "<input type=\"button\" value=\"{$submitLabel}\" class=\"{$className}\" />"
        ];

        //step1 获取所有dynaForm
        $allDynaFormsArr = $this->getAllDynaForms($token);
        //step2 获取当前case信息,返回当前current_task
        $currentInfo = $this->getCaseCurrentTask($caseId, $token);
        $currentTaskUid = Arr::get($currentInfo, "taskId" . "");
        //step3 根据当前task获取他的step
        if ($currentTaskUid) {
            $stepRes = $this->getTaskStepByTaskId($currentTaskUid, $token);
            /**
             * 如果下一步没有dynaForms,则stepRes是空数组
             */
            if (is_array($stepRes) && !isset($stepRes[0]) && !isset($stepRes['error'])) {
                return $dynaFormContent;
            } else {
                $dynaFormId = Arr::get($stepRes, "0.step_uid_obj", "");
            }
            //获取dynaForms信息
            foreach ($allDynaFormsArr as $item) {
                if (isset($item['dyn_uid']) && $item['dyn_uid'] == $dynaFormId) {
                    $dynaForms = $item;
                    break;
                }
            }
            //获取dynaCont节点内容
            $dynaCont = Arr::get($dynaForms, "dyn_content", "");
            if ($dynaCont) {
                $dynaCont = GuzzleHttp\json_decode($dynaCont, true);
            }
            //获取items节点
            $dynaItems = Arr::get($dynaCont, "items.0.items", "");
            if ($dynaItems) {
                foreach ($dynaItems as $dItem) {
                    if (Arr::get($dItem, "0.type") != "submit") {
                        $newItems = Arr::get($dItem, "0", "");
                    } else {
                        $submitLabel = Arr::get($dItem, "0.label", "");
                    }
                }
            }
        }

        if ($newItems) {
            $dynaFormContent = [
                'variable' => $newItems['variable'],
                'form'   => $this->dynaSelectToHtml($newItems,"form-control-process form-control"),
                'submit' => "<a type=\"button\" value=\"{$submitLabel}\" class=\"{$className}\" />"
            ];
        }
        return $dynaFormContent;

    }


    /**
     * 获取case的当前审批进度
     * @param $caseId string
     * @param $token string
     * @return array
     */
    public function getCaseCurrentTask($caseId, $token)
    {
        $params = [
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ]

        ];
        $action = "api/1.0/" . self::PM_WS . "/cases/" . $caseId;

        $uri = $this->pmUrl . $action;
        $caseInfo = $this->guzzleGet($uri, $params);
        $currentTask = Arr::get($caseInfo, "current_task.0", "");
        $currentTaskInfo = [];
        if ($currentTask) {
            $currentTaskInfo['taskId'] = $currentTask['tas_uid'];
        }
        /**
         * 追加status
         * 检测是否已经完成，因为此处结束状态current_task为空
         */
        if(!isset($caseInfo['app_status'])){
            return $caseInfo;
        }
        $status = $caseInfo['app_status'] == 'COMPLETED' ? 'completed' : $caseInfo['app_name'];
        $currentTaskInfo['status'] = $status;
        $currentTaskInfo['appNumber'] = $caseInfo['app_number'];

        return $currentTaskInfo;
    }

    /**
     * 我的待处理列表
     * @param $token
     * @param array $filter @link http://wiki.processmaker.com/3.0/REST_API_Cases#Filters_for_listing_cases
     * @return array
     */
    public function getTodoCases($token, array $filter)
    {
        $params = [
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ]

        ];
        //条件
        $filter = array_merge($filter, ['pro_uid' => $this->processId]);
        $query = http_build_query($filter);

        $uri = "api/1.0/" . self::PM_WS . "/cases/Unassigned/paged?" . $query;
        $url = $this->pmUrl . $uri;
        $todoList = $this->guzzleGet($url, $params);
        $todoListArr = GuzzleHttp\json_decode($todoList, true);
        $todoListRes['total'] = $todoListArr['total'];
        $todoListRes['rows'] = [];
        foreach ($todoListArr['data'] as $items) {
            $todoListRes['rows'][] = $items['app_uid'];
        }
        return $todoListRes;
    }


    /**
     * 我参与过的case列表
     * @param $token
     * @param array $filter @link http://wiki.processmaker.com/3.0/REST_API_Cases#Filters_for_listing_cases
     * @return array
     */
    public function getParticipatedCases($token, array $filter)
    {
        $params = [
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ]

        ];
        //条件
        $filter = array_merge($filter, ['pro_uid' => $this->processId]);
        $query = http_build_query($filter);

        $uri = "api/1.0/" . self::PM_WS . "/cases/participated/paged?" . $query;
        $url = $this->pmUrl . $uri;
        $todoList = $this->guzzleGet($url, $params);
        $todoListArr = GuzzleHttp\json_decode($todoList, true);
        $todoListRes['total'] = $todoListArr['total'];
        $todoListRes['rows'] = [];
        foreach ($todoListArr['data'] as $items) {
            $todoListRes['rows'][] = $items['app_uid'];
        }
        return $todoListRes;
    }

    /**
     * 我的待办草稿箱列表
     * @param $token
     * @param array $filter @link http://wiki.processmaker.com/3.0/REST_API_Cases#Filters_for_listing_cases
     * @return array
     */
    public function getDraftCases($token, array $filter)
    {
        $params = [
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ]

        ];
        //条件
        $filter = array_merge($filter, ['pro_uid' => $this->processId]);
        $query = http_build_query($filter);

        $uri = "api/1.0/" . self::PM_WS . "/cases/draft/paged?" . $query;
        $url = $this->pmUrl . $uri;
        $todoList = $this->guzzleGet($url, $params);
        $todoListArr = GuzzleHttp\json_decode($todoList, true);
        $todoListRes['total'] = $todoListArr['total'];
        $todoListRes['rows'] = [];
        foreach ($todoListArr['data'] as $items) {
            $todoListRes['rows'][] = $items['app_uid'];
        }
        return $todoListRes;
    }

    /**
     * 所有case列表
     * @param $token
     * @param array $filter @link http://wiki.processmaker.com/3.0/REST_API_Cases#Filters_for_listing_cases
     * @return mixed
     */
    public function getAllCases($token, array $filter)
    {
        $params = [
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ]

        ];
        //条件
        $filter = array_merge($filter, ['pro_uid' => $this->processId]);
        $query = http_build_query($filter);

        $uri = "api/1.0/" . self::PM_WS . "/cases/advanced-search/paged?" . $query;
        $url = $this->pmUrl . $uri;
        $todoList = $this->guzzleGet($url, $params);
        $todoListArr = GuzzleHttp\json_decode($todoList, true);
        $todoListRes['total'] = $todoListArr['total'];
        $todoListRes['rows'] = [];
        foreach ($todoListArr['data'] as $items) {
            $todoListRes['rows'][] = $items['app_uid'];
        }
        return $todoListRes;
    }

    /**
     * 驱动到下一步并且返回步骤的状态
     * @param $caseId
     * @param $token
     * @param bool $variables
     * @return bool|mixed
     */
    public function nextCase($caseId, $token, $variables = [])
    {
        //设置变量
        if ($variables) {
            $setVariablesRes = $this->setVariables($caseId, $token, $variables);
            if ($setVariablesRes !== true) {
                return "create case errors." . $setVariablesRes;
            }
        }

        $params = [
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ]
        ];
        $action = "api/1.0/" . self::PM_WS . "/cases/" . $caseId . "/route-case";
        $uri = $this->pmUrl . $action;

        $res = $this->guzzlePUT($uri, $params);
        if ($res === true) {
            return $this->getCaseCurrentTask($caseId, $token);
        }
        return $res;
    }


    /**
     * 设置case变量
     * @param $caseId
     * @param $token
     * @param $params
     * @return bool|mixed
     */
    protected function setVariables($caseId, $token, $params)
    {
        $params = [
            'form_params' => $params,
            'headers'     => [
                'Authorization' => 'Bearer ' . $token
            ]

        ];
        $action = "api/1.0/" . self::PM_WS . "/cases/" . $caseId . "/variable";

        $uri = $this->pmUrl . $action;
        return $this->guzzlePUT($uri, $params);
    }

    /**
     * 获取case全部信息
     * @param $caseId
     * @param $token
     * @return bool|mixed
     */
    protected function getCaseInfo($caseId, $token)
    {

        $params = [
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ]

        ];
        $action = "api/1.0/" . self::PM_WS . "/cases/" . $caseId;

        $uri = $this->pmUrl . $action;
        return $this->guzzleGet($uri, $params);
    }

    /**
     * 获取task的step
     * @param $caseId
     * @param $token
     * @return bool|mixed
     */
    protected function getTaskStepByTaskId($taskId, $token)
    {

        $params = [
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ]

        ];
        $action = "api/1.0/" . self::PM_WS . "/project/" . $this->processId . "/activity/" . $taskId . "/steps";

        $uri = $this->pmUrl . $action;
        return $this->guzzleGet($uri, $params);
    }


    /**
     * TODO 只支持radio等单选标签 后续支持多复杂标签
     * 获取dynaForms转为html
     * @param $newItems
     * @return string
     */
    protected function dynaFormToHtml($newItems, $className = "pmOutput")
    {
        if (!$newItems) {
            return "";
        }
        $outputStart = "<span class=\"{$className}\">&nbsp;&nbsp;";
        $outputEnd = "</span>";
        $outputCont = "";
        foreach ($newItems['options'] as $items) {
            $outputCont .= "<input type='button' value={$items['label']} class='reply-btn btnSub' name={$newItems['variable']} />&nbsp;&nbsp;";
        }
        return $outputStart . $outputCont . $outputEnd;

    }

    protected function dynaSelectToHtml($newItems, $className = "pmOutput")
    {
        if (!$newItems) {
            return "";
        }
        $outputStart = "<select class=\"{$className}\" name=\"{$newItems['variable']}\">&nbsp;&nbsp;";
        $outputEnd = "</select>";
        $outputCont = "";
        foreach ($newItems['options'] as $items) {
            if($items['label'] == "询价完成"){
                $outputCont .= "<option type='button' selected='selected' value=\"{$items['value']}\">{$items['label']}</option>";
            }else{
                $outputCont .= "<option type='button' value=\"{$items['value']}\">{$items['label']}</option>";
            }
        }
        return $outputStart . $outputCont . $outputEnd;

    }

    /**
     * 获取该processId下所有dynaForms
     * @param $token
     * @return mixed
     */
    protected function getAllDynaForms($token)
    {
        $params = [
            'headers' => [
                'Authorization' => 'Bearer ' . $token
            ]

        ];
        $uri = "api/1.0/" . self::PM_WS . "/project/" . $this->processId . "/dynaforms";

        $url = $this->pmUrl . $uri;
        return $this->guzzleGet($url, $params);
    }

    /**
     * Method PUT basic Guzzle
     *
     * @param $uri
     * @param array $params
     * @return bool|mixed
     */
    protected function guzzlePUT($uri, $params = [])
    {
        try {
            $client = new GuzzleHttp\Client();
            $res = $client->request("PUT", $uri, $params);
        } catch (RequestException $e) {
            if ($e->hasResponse() && $e->getCode() >= 400) {
                return GuzzleHttp\json_decode($e->getResponse()->getBody()->getContents(), true);
            } else {
                return $this->errMsg($e->getMessage());
            }
        }
        //返回200 则表示成功
        if ($res->getStatusCode() == 200) {
            return true;
        }
    }

    /**
     * Method GET basic Guzzle
     *
     * @param $uri
     * @param array $params
     * @return bool|mixed
     */
    protected function guzzleGet($uri, $params = [])
    {
        try {
            $client = new GuzzleHttp\Client();
            $res = $client->request("GET", $uri, $params);
        } catch (RequestException $e) {
            if ($e->hasResponse() && $e->getCode() >= 400) {
                return GuzzleHttp\json_decode($e->getResponse()->getBody()->getContents(), true);
            } else {
                return $this->errMsg($e->getMessage());
            }
        }
        //返回200 则表示成功
        if ($res->getStatusCode() == 200) {
            return GuzzleHttp\json_decode($res->getBody()->getContents(), true);
        }
    }

    /**
     * Method POST basic Guzzle
     *
     * @param $uri
     * @param array $params
     * @return bool|mixed
     */
    protected function guzzlePOST($uri, $params = [])
    {
        try {
            $client = new GuzzleHttp\Client();
            $res = $client->request("POST", $uri, $params);
        } catch (RequestException $e) {
            if ($e->hasResponse() && $e->getCode() >= 400) {
                return GuzzleHttp\json_decode($e->getResponse()->getBody()->getContents(), true);
            } else {
                return $this->errMsg($e->getMessage());
            }
        }
        //返回200 则表示成功
        if ($res->getStatusCode() == 200) {
            return GuzzleHttp\json_decode($res->getBody()->getContents(), true);
        }
    }

    /**
     * 返回一个符合pm格式的错误消息
     * @param $msg
     * @return array
     */
    protected function errMsg($msg)
    {
        return [
            'error' => [
                'code'    => 500,
                'message' => $msg
            ]
        ];
    }
}