<?php

namespace Itsm\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Redirect;
use Itsm\Http\Helper\ProcessMakerApi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Itsm\Http\Requests\Request;
use Itsm\Model\Res\ResUserGroup;
use Itsm\Model\Res\ResUsers;
use Itsm\Model\Usercenter\Change;
use Itsm\Model\Usercenter\Issue;

class Controller extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

    const  ROLE_BOSS = 'BOSS';//公司管理
    const  ROLE_SYSTEM_MANAGER = 'system_manager';//系统管理员
    const  ROLE_DESK_MANAGER = 'desk_manager';//服务台管理员
    const  ROLE_DESK = 'desk';//服务台人员
    const  ROLE_DC_EMPLOYEE = 'noc_op';//现场工程师(数据中心工程师)
    const  OTHER = 'other';//其他人员
    //process role
    protected $processRole = [
        'change' => [
            'change_approval'     => '可行性审批',
            'change_design'       => '变更方案规划',
            'change_actualize'    => '实施方案和回退方案制定',
            'change_test'         => '方案测试',
            'change_testApproval' => '方案测试结果审核',
            'change_release'      => '变更发布实施',
            'change_approved'     => '变更结果验证',
            'change_reject'       => '更驳回分析',
            'employee'            => '员工或变更驳回分析'
        ],
        'issue'  => [
            'issue_approval' => '审核',
            'issue_reject'   => '驳回问题',
            'issue_analyse'  => '分析问题',
            'issue_check'    => '确认实施方案',
            'issue_closed'   => '关闭',
            'employee'       => '员工'
        ]
    ];

    /**
     * 工单角色
     * @var array
     */
    protected $RoleInfo = [
        self::ROLE_BOSS         => '公司管理',
        self::ROLE_DESK_MANAGER => '服务台管理员',
        self::ROLE_DESK         => '服务台人员',
        self::ROLE_DC_EMPLOYEE  => '现场工程师',
        self::OTHER             => '其他人员',
    ];

    /**
     * 变更角色对应大账户
     * @var array
     */
    protected $roleToPM = [
        'change' => [
            //可行性审批
            'change_approval'     => ['change_approval', '123456'],
            //变更方案规划
            'change_design'       => ['change_design', '123456'],
            //实施方案和回退方案制定
            'change_actualize'    => ['change_actualize', '123456'],
            //方案测试
            'change_test'         => ['change_test', '123456'],
            //方案测试结果审核
            'change_testApproval' => ['change_testApproval', '123456'],
            //变更发布实施
            'change_release'      => ['change_release', '123456'],
            //变更结果验证
            'change_approved'     => ['change_approved', '123456'],
            //变更驳回分析
            'change_reject'       => ['change_changer', '123456'],
            //员工
            'employee'            => ['change_changer', '123456']
        ],
        'issue'  => [
            'employee'        => ['issue_submitter', '123456'],
            'issue_submitter' => ['issue_submitter', '123456'],
            'issue_approval'  => ['issue_approval', '123456'],
            'issue_analyse'   => ['issue_analyse', '123456'],
            'issue_reject'    => ['issue_reject', '123456'],
            'issue_check'     => ['issue_check', '123456'],
            'issue_closed'    => ['issue_closed', '123456'],
        ],
        'prod'  => [
            'salesApplication'        => ['sales', '51idc.com'],
            'productConfirm'        => ['product', '51idc.com'],
            'inquiryEnd'        => ['product', '51idc.com'],
            'productOffer' => ['product', '51idc.com'],
            'resourcesQuotes'  => ['resources', '51idc.com'],
            'purchaseQuotes'   => ['purchase', '51idc.com']
        ]
    ];

    /**
     * 变更状态
     * @var array
     */
    protected $processStatus = [
        'approval'     => "可行性审批",
        'reject'       => "变更驳回",
        'design'       => "变更方案规划",
        'actualize'    => "实施/回退方案制定",
        'test'         => "方案测试",
        'testApproval' => "方案测试结果审批",
        'release'      => "变更发布实施",
        'approved'     => "变更结果验证",
        'completed'    => "完成"
    ];

    function __construct()
    {
        return $this->checkMenuRule();
    }

    /**
     * 验证权限菜单
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function checkMenuRule()
    {
        $menuList = [
            'change/todolist' => 'change_changer',
            'change/myList'   => 'change_changer',
            'change/allList'  => 'change_manager',

            'issue/todolist' => 'issue_submitter',
            'issue/myList'   => 'issue_submitter',
            'issue/allList'  => 'issue_manager',
        ];
        foreach ($menuList as $k => $value) {
            if (request()->path() == $k) {
                if (!$this->hasUserRule($value)) {
                    header("Location:/");
                }
            }
        }
    }

    /**
     *  获取当前用户角色
     * @return string
     */
    protected function getUserRole()
    {
        if ($this->hasUserRule(self::ROLE_BOSS)) {
            return self::ROLE_BOSS;
        }
        if ($this->hasUserRule(self::ROLE_DESK_MANAGER)) {
            return self::ROLE_DESK_MANAGER;
        }
        if ($this->hasUserRule(self::ROLE_DESK)) {
            return self::ROLE_DESK;
        }
        if ($this->hasUserRule(self::ROLE_DC_EMPLOYEE)) {
            return self::ROLE_DC_EMPLOYEE;
        }
        return self::OTHER;
    }

    /**
     *  获取当前用户的PM角色
     * @return string
     */
    protected function getProcessRole($type = "change")
    {
        $roleList = $this->processRole[$type];
        $roleArr = [];
        foreach ($roleList as $k => $item) {
            if ($this->hasUserRule($k)) {
                $roleArr[] = $k;
            }
        }
        //hack写法,判断他如果是员工,就一定有驳回权限可看
        if (in_array('employee', $roleArr) && !in_array('change_reject', $roleArr)) {
            $roleArr[] = 'change_reject';
        }
        return $roleArr;
    }

    /**
     * 根据roleName获取token
     * @param $roleName
     * @return array|string
     */
    public function getAccessTokenByRole($roleName, $type = "change")
    {

        if (!isset($this->roleToPM[$type][$roleName])) {
            return "The role `{$roleName}` not found.";
        }
        $userInfo = $this->roleToPM[$type][$roleName];
        $pmApi = new ProcessMakerApi(env(strtoupper($type) . "_PROCESS_ID"), env(strtoupper($type) . "_STEP_ONE_ID"));
        return $pmApi->getAccessToken($userInfo[0], $userInfo[1]);
    }

    /**
     * @param $role 权限模块
     * @return bool
     * 验证是否有该权限符合
     */
    public function hasUserRule($rule)
    {
        $authRoleList = Request()->session()->get('authRoleList');
        if (!empty($authRoleList)) {
            foreach ($authRoleList as $authRole) {
                if (!empty($authRole) && $authRole->authority == $rule) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 获取角色的名称
     * @param $role
     * @return string
     */
    protected function getRoleName($role)
    {
        return isset($this->RoleInfo[$role]) ? $this->RoleInfo[$role] : '';
    }

    /**
     * 筛分出当前角色可查阅的所有工单列表数据返回的是未完成的sql
     * @param $supportList @传入的sql
     * @param $tableSupport @数据库及表名
     * @return mixed
     */
    public function getRoleData($supportList, $tableSupport)
    {
        $user = Request()->session()->get('user');
        $userRole = $this->getUserRole();
        switch ($userRole) {
            //现场工程师
            case self::ROLE_DC_EMPLOYEE;
                $stuffDataCenterGroupId = ResUserGroup::select('UsersId')->where('UserId', $user->Id)->get();
                $supportList = $supportList->where(function ($supportList) use (
                    $tableSupport,
                    $stuffDataCenterGroupId
                ) {
                    $supportList->whereIn($tableSupport . '.DataCenterId', $stuffDataCenterGroupId)
                        ->orwhereIn($tableSupport . '.DatacenterTwoId', $stuffDataCenterGroupId);
                });
                break;
            //其他人员
            case self::OTHER;
                $supportList = $supportList->where(function ($supportList) use ($tableSupport, $user) {
                    $supportList->where($tableSupport . '.ChargeUserId', $user->Id)
                        ->orWhere($tableSupport . '.ChargeUserTwoId', $user->Id)
                        ->orwhere('AsuserId', $user->Id);
                });
                break;
        }
        return $supportList;
    }

    /**
     * 判断用户是否属于某个组
     * @param type $uid 用户id
     * @param type $group 组名称
     */
    protected function isgroup($uid, $group)
    {
        //urlencode 可能有中文
        $cacheKey = "ITSM-isGroup-" . intval($uid) . "-" . urlencode($group);
        $cacheTime = Carbon::now()->addHours(1);

        $uid = intval($uid);
        if (!empty($uid) && $uid < 50000) {
            if (!Cache::has($cacheKey)) {
                $query = ResUserGroup::select("Id")->where("Userid", $uid);
                if ($group == "L0") {
                    $groups = ResUsers::select("id")
                        ->where("chargeGroup", "L0")
                        ->get()
                        ->toArray();
                } else {
                    if ($group == "L1") {//非数据中心组
                        $groups = ResUsers::select("id")
                            ->where("chargeGroup", "L1")
                            ->where("UsersName", "not like", "%数据中心%")
                            ->get()
                            ->toArray();
                    } else {
                        if ($group == "机房") {//数据中心组
                            $groups = ResUsers::select("id")
                                ->where("chargeGroup", "L1")
                                ->where("UsersName", "like", "%数据中心%")
                                ->get()
                                ->toArray();
                        }
                    }
                }
                if (empty($groups)) {
                    return false;
                }
                $rst = $query->whereIn("UsersId", $groups)->get()->toArray();
                if (Cache::put($cacheKey, json_encode($rst), $cacheTime)) {
                    return $rst;
                }
            }
            return json_decode(Cache::get($cacheKey), true);
        }
        return false;
    }

    /**
     * 同步change case的状态
     * @param $caseId
     * @param $token
     * @return bool
     */
    public function syncCaseStatus($caseId, $token, $oldState)
    {
        $prApi = new ProcessMakerApi(env("CHANGE_PROCESS_ID"), env("CHANGE_STEP_ONE_ID"));
        $res = $prApi->getCaseCurrentTask($caseId, $token);
        if (isset($res['status']) && !empty($res['status'])) {
            if ($oldState == $res['status']) {
                return $res['status'];
            }
            if (Change::where("caseId", $caseId)->update(['changeState' => $res['status']]) !== false) {
                return $res['status'];
            }
        }
        return false;
    }

    /**
     * 同步issue case的状态
     * @param $caseId
     * @param $token
     * @return bool
     */
    public function syncCaseStatusByIssue($caseId, $token, $oldState)
    {
        $prApi = new ProcessMakerApi(env("ISSUE_PROCESS_ID"), env("ISSUE_STEP_ONE_ID"));
        $res = $prApi->getCaseCurrentTask($caseId, $token);
        if (isset($res['status']) && !empty($res['status'])) {
            if ($oldState == $res['status']) {
                return $res['status'];
            }
            if (Issue::where("caseId", $caseId)->update(['issueState' => $res['status']]) !== false) {
                return $res['status'];
            }
        }
        return false;
    }
}
