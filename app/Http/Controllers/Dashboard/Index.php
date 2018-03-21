<?php
/**
 * User: Wujiang <wuj@51idc.com>
 * Date: 8/10/16 15:32
 */
namespace Itsm\Http\Controllers\Dashboard;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Itsm\Http\Controllers\Controller;
use Itsm\Http\Helper\PublicMethodsHelper;
use Itsm\Http\Helper\ThirdCallHelper;
use Itsm\Model\Res\AuxDict;
use Itsm\Model\Res\AuxStuff;
use Itsm\Model\Res\ResContact;
use Itsm\Model\Usercenter\Announcement;
use Itsm\Model\Usercenter\Support;


class Index extends Controller
{
    protected static $tableSupport = 'usercenter.support';

    protected static $statusList = [
        'Todo'      => '待处理',
        'ReAppoint' => '待指派',
        'Appointed' => '已指派',
        'Doing'     => '处理中',
        'Suspend'   => '挂起中'
    ];

    protected static $chargeGroupList = [
        'L0'      => 'L0',
        'L1'      => 'L1',   //数据中心远程组
        'L1scene' => 'L1',   //数据中心现场
        'L2'      => 'L2',
        'L3'      => 'L3'
    ];

    /**
     * 用户首页
     * @param Request $req
     * @param Response $resp
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function home(Request $req, Response $resp)
    {
        //菜单权限
        $needCheckRole = [
            'change_changer'  => false,
            'change_manager'  => false,
            'issue_submitter' => false,
            'issue_manager'   => false,
            'purchase'   => false,
            'resources'   => false,
            'sales_confirm'   => false,
            'product_confirm'   => false,
            'system_manager'   => false,
        ];
        $menuRes = $this->checkMenuRole($needCheckRole);
        $role = $this->getUserRole();
        return view('dashboard/index', ['menuRole' => $menuRes,'role'=>$role]);
    }
    /**
     * 人员列表
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function member()
    {
        $chargeGroupList = AuxDict::select("Code","Means")->where('DomainCode', 'chargeGroup')->where('Domain', '负责人群组')->get();
        $group=PublicMethodsHelper::getDeparts();
        $json = json_encode($group);
        return view('dashboard/member',compact('chargeGroupList','group',"json"));
    }
    //人员列表数据
    public function getMemberData(Request $req)
    {

        $list =AuxStuff::selectRaw("res.aux_stuff.*,res.aux_stuff.Id as memberIds,a.nickname as nickname1,b.nickname as nickname2")
            ->leftJoin('usercenter.wechat_userinfo as a','a.userLoginId','=','res.aux_stuff.Login')
            ->leftJoin('usercenter.wechat_userinfo as b','b.userLoginId','=','res.aux_stuff.Email')
            ->where('Permit','yes')
            ->where('InValidate',0);

        if ($groupList = Input::get('groupList')) {
            $list = $list->where('chargeGroup', $groupList);
        }
        if ($Depart = Input::get('Depart')) {
            $list = $list->where('Depart', $Depart);
        }
        if ($second = Input::get('second_dept')) {
            $list = $list->where('second_dept', $second);
        }
        if ($keyword = Input::get('searchInfo')) {
            $list = $list->where(function ($list) use ($keyword) {
                $list->where('res.aux_stuff.Id', 'like', '%' . $keyword . '%')
                    ->orWhere('res.aux_stuff.Name', 'like', '%' . $keyword . '%')
                    ->orWhere('res.aux_stuff.Tel', 'like', '%' . $keyword . '%')
                    ->orWhere('res.aux_stuff.Mobile', 'like', '%' . $keyword . '%')
                    ->orWhere('res.aux_stuff.Email', 'like', '%' . $keyword . '%');
            });
        }
        $list=$list->orderByRaw("Depart = 4 desc,Depart = 2 desc ,Depart = 18 desc ,Depart = 'prod' desc,res.aux_stuff.Id asc");
        $arr['total']=$list->count();
        $pageSize = !empty($req->input('pageSize')) ? $req->input('pageSize') : 20;
        $pageNumber = !empty($req->input('pageNumber')) ? $req->input('pageNumber') : 1;
        $list = $list->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();
        $list = Support::translationDict($list, 'Depart', 'DepartType');
        $this->switchColor($list, 'memberIds');
        $list = ResContact::translationStuff($list, 'memberIds');
        $list = Support::translationDict($list, 'second_dept', 'second_dept');
        $arr['rows']=$list;
        return $arr;
    }
    /*
     * 通过一级部门获取二级部门
     */
    public function getSecondDept(){
        $dept = \DB::select("select DISTINCT(d.Means),b.Depart,c.Means as MeansOne,b.second_dept,d.Means as MeansTwo from  res.aux_stuff as b LEFT JOIN res.aux_dict as c on b.Depart=c.`Code` and  c.DomainCode='DepartType' LEFT JOIN res.aux_dict as d on b.second_dept=d.`Code` and  d.DomainCode='second_dept'");

        if ($id = Input::get('depId')){
            return PublicMethodsHelper::getDeparts($id);
        }
    }

    /**姓名颜色预处理
     * @param $list 人员列表
     * @param $code 组
     */
    protected function switchColor($list, $keyCode)
    {
        foreach ($list as &$opt) {
            if (!empty($this->isgroup($opt->$keyCode, "L1"))) {
                $opt->grpl1 = 1; //L1组（非数据中心）
                continue;
            }
            if (!empty($this->isgroup($opt->$keyCode, "机房"))) {
                $opt->grpcenter = 1; //L1组（数据中心）
                continue;
            }
            if (!empty($this->isgroup($opt->$keyCode, "L0"))) {
                $opt->grpl0 = 1; //L0组
            }
        }
    }
    /**
     * 校验菜单某些是否有权限
     * @param array $role
     * @return array
     */
    protected function checkMenuRole($role = [])
    {
        foreach ($role as $k => &$item) {
            if ($this->hasUserRule($k)) {
                $item = true;
            }
        }

        return $role;
    }

    /**
     * 管理员仪表盘
     * @param Request $req
     * @param Response $resp
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function admin()
    {

        $role = $this->getUserRole();
        if ($role != self::ROLE_BOSS && $role != self::ROLE_DESK_MANAGER) {
            return $this->staff();
        }
        //获取管理员查看的本月工单类型统计
        $supportMonth = $this->getEmployeesDashData("", true);
        $preMonth = $this->getEmployeesDashData(date('Y-m', strtotime("-1 month")), true);
        $chargeGroupList = self::$chargeGroupList;
        //获取管理员当前工单类型统计
        $currentSupport = $this->getCurrentSupportData();
        //排序
        $currentSupport = $this->sortArray($currentSupport, "doingSum");
        $preMonth = $this->sortArray($preMonth, "total");
        $supportMonth = $this->sortArray($supportMonth, "total");
        $anList = Announcement::select("*")
            ->where("Published",true)
            ->where("PublishedVPS",true)
            ->orderBy("OnTop","desc")
            ->orderBy("PubTs","desc")
            ->limit("10")
            ->get()->toArray();
        return view('dashboard/admin', [
            'monthStatistics'    => $supportMonth,
            'preMonthStatistics' => $preMonth,
            'currentSupport'     => $currentSupport,
            'chargeGroupList'    => $chargeGroupList,
            'anList'    => $anList
        ]);
    }

    /**
     * 用户仪表盘
     * @param Request $req
     * @param Response $resp
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function staff()
    {
        $role = $this->getUserRole();
        if ($role == self::ROLE_BOSS || $role == self::ROLE_DESK_MANAGER) {
            return $this->admin();
        }
        $supportMonth = $this->getEmployeesDashData();//where a.published=true and a.publishedVPS=true order by a.ontop desc,a.PubTs desc
        $anList = Announcement::select("*")
            ->where("Published",true)
            ->where("PublishedVPS",true)
            ->orderBy("OnTop","desc")
            ->orderBy("PubTs","desc")
            ->limit("10")
            ->get()->toArray();
        //上月
        $prevSupportMonth = $this->getEmployeesDashData(date("Y-m", strtotime("-1 month")));
        return view('dashboard/staff', [
            'monthStatistics'     => $supportMonth,
            'prevMonthStatistics' => $prevSupportMonth,
            'anList' => $anList,
            'userRole'            => $role
        ]);
    }

    /**
     * 未完成工单统计
     * @return array
     */
    public function supportByNotDone()
    {

        $supportList = Support::selectRaw('Status,count(Id) as count,DatacenterId')
            ->whereRaw("Status != 'Done' and Status != 'Closed' and (ClassInficationOne != 'emailRequest' or ClassInficationOne IS NULL)")
            ->groupBy("DatacenterId", "Status")->get()->toArray();

        //转换name
        $statusList = self::$statusList;

        $rel = [];
        foreach ($statusList as $k => $statusItem) {
            foreach ($supportList as $item) {
                if ($item['DatacenterId'] == '') {
                    $item['DatacenterId'] = 0;
                }
                if (!isset($rel[$item['DatacenterId']]['num'][$k])) {
                    $rel[$item['DatacenterId']]['num'][$k] = 0;
                }
                if ($item['Status'] == $k) {
                    $rel[$item['DatacenterId']]['num'][$item['Status']] += $item['count'];
                }

            }
        }

        $rel = ThirdCallHelper::translationClassName($rel);
        $series = [];
        $legend = [];
        $i = 0;
        //整理 eCharts 数据
        foreach ($rel as $k => $item) {
            $series[$i] = [
                'name'      => $item['UsersName'],
                'type'      => 'line',
                'stack'     => '总量',
                'areaStyle' => [
                    'normal' => (object)[]
                ],
            ];
            foreach ($item['num'] as $numItem) {
                $series[$i]['data'][] = $numItem;
            }
            $i++;
            $legend[] = $item['UsersName'];
        }
        $returnData['status'] = array_values($statusList);
        $returnData['data'] = $series;
        $returnData['legend'] = $legend;

        return $returnData;
    }

    /**
     * 我的待办工单分布图
     */
    public function supportByMyNotDone()
    {
        $user = Request()->session()->get('user');

        $supportList = Support::selectRaw("count(*) as count,Status");

        $supportList = $supportList->where(function ($supportList) use ($user) {
            $supportList->where('ChargeUserId', $user->Id)
                ->orWhere('ChargeUserTwoId', $user->Id)
                ->orwhere('AsuserId', $user->Id);
        });

        $supportList = $supportList->where("status", "!=", "Closed")->where("status", "!=",
            "Done")->groupBy("Status")->get()->toArray();

        $statusList = self::$statusList;
        $rel = [];
        foreach ($statusList as $k => $statusItem) {
            foreach ($supportList as $item) {
                if (!isset($rel[$k])) {
                    $rel[$k] = 0;
                }
                if ($item['Status'] == $k) {
                    $rel[$k] = $item['count'];
                }
            }
        }
        //整理 eCharts 数据
        $series = [];
        foreach ($rel as $k => $item) {
            $series[] = [
                'value' => $item,
                'name'  => $statusList[$k],
                'code'  => $k
            ];
        }
        $returnData['status'] = array_values($statusList);
        $returnData['series'] = $series;
        return $returnData;
    }

    /**
     * 工单数量趋势(对比往年分析)
     * @param Request $req
     * @return array
     */
    public function supportByYear(Request $req)
    {
        $year = $req->input('year') ? $req->input('year') : '2014';
        $cacheKey = "CUST-DASHBOARD-ECHARTSTREND-".$year;
        $cacheTime = Carbon::now()->addHours(24);
        if(Cache::tags("ITSM-DASHBOARD")->has($cacheKey)){
            return json_decode(Cache::tags("ITSM-DASHBOARD")->get($cacheKey), true);
        }
        $rel = Support::selectRaw("Year(Ts) as y,month(Ts) as m,count(Id) as count")
            ->whereRaw("(ClassInficationOne != 'emailRequest' or  ClassInficationOne is NULL)")
            ->whereRaw("year(Ts) >= $year");
        $rel = $this->getRoleData($rel, self::$tableSupport);
        $rel = $rel->groupBy("y",
            "m")->orderBy("y", "Desc")->orderBy("m", "Asc")->get()->toArray();

        $result = [];
        $month = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        $year = [];
        foreach ($month as $mItem) {
            foreach ($rel as $k => $item) {
                if (!isset($result[$item['y']][$mItem])) {
                    $result[$item['y']][$mItem] = 0;
                }
                if ($mItem == $item['m']) {
                    $result[$item['y']][$mItem] = $item['count'];
                }

            }
        }
        //整理成eChars数据
        array_walk($month, function (&$v) {
            $v = $v . "月";
        });
        $series = [];
        $year = [];
        $i = 0;
        //区分是否是管理员
        if ($type = $req->input("type") == 'admin') {
            foreach ($result as $k => $item) {
                $series[$i] = [
                    'name'      => $k . "年",
                    'type'      => 'line',
                    'smooth'    => true,
                    'itemStyle' => [
                        'normal' => [
                            'areaStyle' => [
                                'type' => 'default'
                            ]
                        ]
                    ],
                ];
                foreach ($item as $count) {
                    $series[$i]['data'][] = $count;
                }
                $year[] = $k . "年";
                $i++;
            }
        } else {
            foreach ($result as $k => $item) {
                $series[$i] = [
                    'name'      => $k . "年",
                    'type'      => 'bar',
                    'markPoint' => [
                        'data' => [
                            ['type' => 'max', 'name' => '最大值'],
                            ['type' => 'min', 'name' => '最小值'],
                        ]
                    ],
                    'markLine'  => [
                        'data' => [
                            [
                                'type' => 'average',
                                'name' => '平均值'
                            ]
                        ]
                    ]
                ];
                foreach ($item as $count) {
                    $series[$i]['data'][] = $count;
                }
                $year[] = $k . "年";
                $i++;
            }
        }
        $returnData = [];
        $returnData['series'] = $series;
        $returnData['month'] = $month;
        $returnData['year'] = $year;
        Cache::tags("ITSM-DASHBOARD")->put($cacheKey, json_encode($returnData), $cacheTime);
        return $returnData;

    }

    /**
     * 工单满意度趋势图
     * @return array
     */
    public function supportByEvaluate()
    {
        $cacheKey = "CUST-DASHBOARD-ECHARTSEVAL";
        $cacheTime = Carbon::now()->addHours(24);
        if(Cache::tags("ITSM-DASHBOARD")->has($cacheKey)){
            return json_decode(Cache::tags("ITSM-DASHBOARD")->get($cacheKey), true);
        }
        $evaluateData = $this->getEvaluationData();
        $result = [];
        $month = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        $year = [];
        foreach ($month as $mItem) {
            foreach ($evaluateData as $k => $item) {
                if (!isset($result[$item['year']][$mItem])) {
                    $result[$item['year']][$mItem] = 0;
                }
                if ($mItem == $item['month']) {
                    $result[$item['year']][$mItem] = $item['evaluationSum'];
                }

            }
        }
        //整理成eChars数据
        array_walk($month, function (&$v) {
            $v = $v . "月";
        });
        $series = [];
        $year = [];
        $i = 0;

        foreach ($result as $k => $item) {
            $series[$i] = [
                'name'      => $k . "年",
                'type'      => 'line',
                'smooth'    => true,
                'itemStyle' => [
                    'normal' => [
                        'areaStyle' => [
                            'type' => 'default'
                        ]
                    ]
                ]
            ];
            foreach ($item as $count) {
                $series[$i]['data'][] = $count * 100;
            }
            $year[] = $k . "年";
            $i++;
        }
        $returnData = [];
        $returnData['series'] = $series;
        $returnData['month'] = $month;
        $returnData['year'] = $year;
        Cache::tags("ITSM-DASHBOARD")->put($cacheKey, json_encode($returnData), $cacheTime);
        return $returnData;
    }

    public function main()
    {
        return view('dashboard/main');
    }

    /**
     * 获取对应工作组人员当前工单处理情况
     * @param Request $req
     * @return mixed
     */
    public function getStuffSupports(Request $req)
    {
        $groupId = $req->input('groupId');
        return $this->getSupportsByGroup($groupId);
    }

    public function getSupportsByThisMonth(Request $req)
    {
        $ts = 'usercenter.support';
        $groupId = $req->input('groupId');
        //拿取对应工作组所有负责人成员Id
        $chargeUsers = ThirdCallHelper::getGroupMembers($groupId)->toArray();
        $chargeUserArr = [];
        foreach ($chargeUsers as $chargeUser) {
            $chargeUserArr[] = $chargeUser['UserId'];
        }

        //以第一负责人拿数据
        $arr1 = $this->getDatasByCharge($groupId, $chargeUserArr, $ts, 'ChargeUserId');
        //以第二负责人拿数据
        $arr2 = $this->getDatasByCharge($groupId, $chargeUserArr, $ts, 'ChargeUserTwoId');
        //以指派人即跟踪人拿数据
        $arr3 = $this->getDatasByCharge($groupId, $chargeUserArr, $ts, 'AsuserId');

        //两组数据合并
        foreach ($arr1 as &$item1) {
            foreach ($arr2 as $k => $item2) {
                if ($item1['Id'] == $item2['Id']) {
                    $item1["evaluationSum"] += $item2["evaluationSum"];
                    $item1["overTimeSum"] += $item2["overTimeSum"];
                    $item1["doneSum"] += $item2["doneSum"];
                } else {
                    $item1[$k] = $item2;
                }
            }
        }
        foreach ($arr1 as &$item1) {
            foreach ($arr3 as $k => $item3) {
                if ($item1['Id'] == $item3['Id']) {
                    $item1["evaluationSum"] += $item3["evaluationSum"];
                    $item1["overTimeSum"] += $item3["overTimeSum"];
                    $item1["doneSum"] += $item3["doneSum"];
                } else {
                    $item1[$k] = $item3;
                }
            }
        }
        foreach ($arr1 as &$item1) {
            //判断负责人是否在线
            $cachekey = ITSM_LOGIN . $item1["Id"];
            $item1['isOnLine'] = Cache::has($cachekey) ? "在线" : "离线";
        }
        return $arr1;
    }

    /**
     * 获取上月的数据
     * @param Request $req
     * @return $this
     */
    public function getSupportsByPreMonth(Request $req)
    {
        $ts = 'usercenter.support';
        $groupId = $req->input('groupId');
        //拿取对应工作组所有负责人成员Id
        $chargeUsers = ThirdCallHelper::getGroupMembers($groupId)->toArray();
        $chargeUserArr = [];
        foreach ($chargeUsers as $chargeUser) {
            $chargeUserArr[] = $chargeUser['UserId'];
        }
        $preMonth = date("Y-m", strtotime("-1 month"));
        $preMonth = $preMonth == date("Y-m")?date("Y-m", strtotime("-32 day")): $preMonth;
        //以第一负责人拿数据
        $arr1 = $this->getDatasByCharge($groupId, $chargeUserArr, $ts, 'ChargeUserId', $preMonth);
        //以第二负责人拿数据
        $arr2 = $this->getDatasByCharge($groupId, $chargeUserArr, $ts, 'ChargeUserTwoId', $preMonth);
        //以指派人即跟踪人拿数据
        $arr3 = $this->getDatasByCharge($groupId, $chargeUserArr, $ts, 'AsuserId', $preMonth);

        //两组数据合并
        foreach ($arr1 as &$item1) {
            foreach ($arr2 as $k => $item2) {
                if ($item1['Id'] == $item2['Id']) {
                    $item1["evaluationSum"] += $item2["evaluationSum"];
                    $item1["overTimeSum"] += $item2["overTimeSum"];
                    $item1["doneSum"] += $item2["doneSum"];
                } else {
                    $item1[$k] = $item2;
                }
            }
        }
        foreach ($arr1 as &$item1) {
            foreach ($arr3 as $k => $item3) {
                if ($item1['Id'] == $item3['Id']) {
                    $item1["evaluationSum"] += $item3["evaluationSum"];
                    $item1["overTimeSum"] += $item3["overTimeSum"];
                    $item1["doneSum"] += $item3["doneSum"];
                } else {
                    $item1[$k] = $item3;
                }
            }
        }
        foreach ($arr1 as &$item1) {
            //判断负责人是否在线
            $cachekey = ITSM_LOGIN . $item1["Id"];
            $item1['isOnLine'] = Cache::has($cachekey) ? "在线" : "离线";
        }
        return $arr1;
    }

    /**
     * 根据月份及角色获取对应仪表盘数据
     * @param $month @月份格式'2016-08'
     * @param $isAdmin @如果是管理员 则不需要加任何条件
     * @param $overReply @响应超时时间，按秒为单位默认120
     * @return mixed
     */
    public function getEmployeesDashData($month = "", $isAdmin = false, $overReply = 120)
    {
        $userId = Request()->session()->get('user')->Id;
        $cacheKey = "CUST-DASHBOARD-".($isAdmin?"Admin-":"Stuff$userId-");
        if (!$month) {
            $month = date("Y-m", time());
            $cacheKey = $cacheKey.$month;
            $cacheTime = Carbon::now()->addHours(1);
            if(Cache::tags("ITSM-DASHBOARD")->has($cacheKey)){
                return json_decode(Cache::tags("ITSM-DASHBOARD")->get($cacheKey), true);
            }
        }else{
            $cacheKey = $cacheKey.$month;
            $cacheTime = Carbon::now()->addHours(24);
            if(Cache::tags("ITSM-DASHBOARD")->has($cacheKey)){
                return json_decode(Cache::tags("ITSM-DASHBOARD")->get($cacheKey), true);
            }
        }
        $ts = 'usercenter.support';
        $supports = Support::select('b.Means')
            ->selectRaw("AVG(UNIX_TIMESTAMP(IFNULL($ts.ProcessTs,NOW())) - UNIX_TIMESTAMP($ts.Ts) - IFNULL(hangupDuration,0)) as avgTs,
            count($ts.Id) as total,$ts.ClassInficationOne")
            ->selectRaw("count(CASE WHEN $ts.ChargeUserTwoId is not null THEN 1 ELSE NULL END) as chargeTwoSum")
            ->selectRaw("count(CASE WHEN UNIX_TIMESTAMP($ts.FirstReplyTs)-UNIX_TIMESTAMP($ts.Ts) > $overReply THEN 1 ELSE NULL END) as overReplySum")
            ->selectRaw("count(CASE WHEN $ts.Evaluation = 'Good' or $ts.Evaluation = 'Best' THEN 1 ELSE NULL END) as evaluationSum")
            ->selectRaw("count(CASE WHEN (UNIX_TIMESTAMP($ts.ProcessTs)-UNIX_TIMESTAMP($ts.Ts)-IFNULL(hangupDuration,0)-IFNULL(b.Eng,0)*60>0 and
             b.Eng<>'N/A' and LENGTH(b.ENG)>0) THEN 1 ELSE NULL END) as overTimeSum")
            ->leftJoin('res.aux_dict as b', "$ts.ClassInficationOne", '=', 'b.Code')
            ->where('b.Domain', '工单类型')
            ->where('b.DomainCode', 'WorkSheetTypeOne')
            ->whereRaw('(b.Validate is NULL or b.Validate = 0)')
            ->where(\DB::raw("DATE_FORMAT($ts.Ts,'%Y-%m')"), $month)
            ->whereRaw("($ts.Status = 'Done' or $ts.Status = 'Closed')");
        if (!$isAdmin) {
            $supports = $supports->whereRaw("($ts.ChargeUserId = $userId or $ts.ChargeUserTwoId = $userId or $ts.AsuserId =$userId)");
        }

        $supports = $supports->GroupBy('ClassInficationOne')->get()->toArray();
        //dd("没缓存了");
        Cache::tags("ITSM-DASHBOARD")->put($cacheKey, json_encode($supports), $cacheTime);
        return $supports;
    }

    /**
     * 获取对应年份满意度查询
     * @param $yearArray
     * @return array
     */
    public function getEvaluationData($yearArray = 2014)
    {
        $evaluationArray = Support::selectRaw("year(Ts) as year,month(Ts) as month ,
        count(CASE WHEN Evaluation = 'Good' or Evaluation = 'Best' THEN 1 ELSE NULL END )/count(Id) as evaluationSum")
            ->whereRaw("(ClassInficationOne != 'emailRequest' or  ClassInficationOne is NULL)")
            ->orderBy("Ts", "desc")
            ->havingRaw("year >= $yearArray")
            ->groupBy("year", "month")->get()->toArray();
        return $evaluationArray;
    }

    /** 获取当前所有工单类型统计
     * @param int $willOverTime @即将超时提前参考时间单位秒
     * @return mixed
     */
    public function getCurrentSupportData($willOverTime = 300)
    {
        $cacheKey = "CUST-DASHBOARD-";
        $cacheKey = $cacheKey.'CURRENT';
        $cacheTime = Carbon::now()->addHours(1);
        if(Cache::tags("ITSM-DASHBOARD")->has($cacheKey)){
            return json_decode(Cache::tags("ITSM-DASHBOARD")->get($cacheKey), true);
        }
        $ts = self::$tableSupport;
        $currentSupport = Support::selectRaw("count(null) as willOverSum,count(null) as overTimeSum,
        b.Means,$ts.ClassInficationOne,count(CASE WHEN $ts.Status = 'Doing' THEN 1 ELSE NULL END) as doingSum")
            ->selectRaw("count(CASE WHEN $ts.Status = 'Suspend' THEN 1 ELSE NULL END) as suspendSum")
            ->leftJoin('res.aux_dict as b', "$ts.ClassInficationOne", '=', 'b.Code')
            ->where('b.Domain', '工单类型')
            ->where('b.DomainCode', 'WorkSheetTypeOne')
            ->whereRaw('(b.Validate is NULL or b.Validate = 0)')
            ->groupBy("ClassInficationOne")->get()->toArray();

        $supports = Support::select($ts . '.Id', $ts . '.ProcessTs', $ts . '.Ts',
            $ts . '.hangupDuration', 'b.ENG', $ts . '.ClassInficationOne')
            ->leftJoin('res.aux_dict as b', $ts . '.ClassInficationOne', '=', 'b.Code')
            ->where('b.DomainCode', 'WorkSheetTypeOne')
            //统计超时工单排除4种状态
            ->whereNotIn($ts . '.Status', ['Done', 'Closed', 'Todo', 'ReAppoint'])
            ->where('b.ENG', '!=', 'N/A')
            ->whereRaw('LENGTH(b.ENG)>0')
            ->get()->toArray();

        foreach ($currentSupport as &$data) {
            foreach ($supports as &$support) {
                //这里计算时长单位用秒
                $processTs = $support['ProcessTs'] ? strtotime($support['ProcessTs']) : time();//已处理时间，若没有取当前
                $ts = $support['Ts'] ? strtotime($support['Ts']) : time();//工单创建时间
                $hangupDuration = $support['hangupDuration'] ? $support['hangupDuration'] : 0;//挂起时长
                $referTime = $support['ENG'] ? 60 * $support['ENG'] : 0;//参考时长
                $overTime = $processTs - $hangupDuration - $referTime - $ts;//计算出的超时时长

                //表示即将超时还未超时的工单
                if ($overTime + $willOverTime >= 0 and $overTime < 0) {
                    if ($data['ClassInficationOne'] == $support['ClassInficationOne']) {
                        $data['willOverSum'] += 1;
                    }
                }
                if ($overTime >= 0) {
                    if ($data['ClassInficationOne'] == $support['ClassInficationOne']) {
                        $data['overTimeSum'] += 1;
                    }
                }
            }
        }
        Cache::tags("ITSM-DASHBOARD")->put($cacheKey, json_encode($currentSupport), $cacheTime);
        return $currentSupport;
    }

    /**
     * 获取有未完成工单的工作组
     * @return mixed
     */
    public function getChargeGroupFunc($datacCenter, $group, $scene = false)
    {
        $ts = self::$tableSupport;
        $groups = Support::selectRaw("b.UsersName,$datacCenter as DatacenterId")
            ->leftJoin('res.res_users as b', "$ts.$datacCenter", '=', 'b.Id')
            ->whereNotNull("$ts.$datacCenter")
            ->where("b.chargeGroup", $group);
        //根据部门筛选
        if ($group == 'L1' && $scene) {
            $groups = $groups->where("b.UsersName", 'like', '%数据中心组%');
        } elseif ($group == 'L1') {
            $groups = $groups->where("b.UsersName", 'not like', '%数据中心组%');
        }
        //筛选有未完成工单的工作组
        $groups = $groups->whereRaw("(ClassInficationOne != 'emailRequest' or ClassInficationOne IS NULL)")
            ->whereRaw("(Status <> 'Done' and Status <> 'Closed')")
            ->groupBy("$datacCenter")->get()->toArray();
        //dd($groups->toSql());

        $ret = [];
        foreach ($groups as $k => $item) {
            $ret[$item['DatacenterId']] = $item;
        }
        return $ret;
    }

    /**
     * 合并有未完成工单的第一及第二工作组Id
     * @param Request $req
     * @return array|string
     */
    public function getChargeGroup(Request $req)
    {
        if ($group = $req->input('chargeGroup')) {
            $scene = !empty($req->input('scene')) ? $req->input('scene') : false;
            $groupsOne = $this->getChargeGroupFunc('DatacenterId', $group, $scene);
            $groupsTwo = $this->getChargeGroupFunc('DatacenterTwoId', $group, $scene);
            $retArr = !empty(array_replace($groupsOne, $groupsTwo)) ? array_replace($groupsOne, $groupsTwo) : '';
            return $retArr;
        }
    }

    /**
     * 获取对应负责人的工单情况
     * @param $groupId @工单负责组Id
     * @param $chargeUserArr @负责人Id数组
     * @param $ts @库及表名
     * @param $charge @区分第一负责人还是第二负责人字段
     * @param $willOverTime @即将超时提前量参数
     * @return mixed
     */
    public function getSupportsByCharge($groupId, $chargeUserArr, $ts, $charge, $willOverTime)
    {
        $datas = Support::selectRaw("c.Name,c.Id,count(null) as willOverSum,count(null) as overTimeSum,
        count(CASE WHEN $ts.Status = 'Doing' THEN 1 ELSE NULL END) as doingSum")
            ->selectRaw("count(CASE WHEN $ts.Status = 'Suspend' THEN 1 ELSE NULL END) as suspendSum")
            ->leftJoin('res.aux_stuff as c', "$ts.$charge", '=', 'c.Id')
            ->whereRaw("(ClassInficationOne != 'emailRequest' or ClassInficationOne IS NULL)")
            ->whereRaw("(Status <> 'Done' and Status <> 'Closed')")
            ->whereIn($charge, $chargeUserArr);
        if ($charge == 'ChargeUserTwoId') {
            $datas = $datas->whereRaw('(ChargeUserId <> ChargeUserTwoId or ChargeUserId is NULL or ChargeUserTwoId is NULL)');
        } elseif ($charge == 'AsuserId') {
            $datas = $datas->whereRaw('(ChargeUserId <> AsuserId or ChargeUserId is NULL or AsuserId is NULL)');
        }
        $datas = $datas->groupBy($charge)->get()->toArray();

        //获取计算超时的必要信息
        $supports = Support::select('c.Id as stuffId', $ts . '.ProcessTs', $ts . '.Ts', $ts . '.hangupDuration',
            'b.ENG', $ts . '.ClassInficationOne')
            ->leftJoin('res.aux_dict as b', $ts . '.ClassInficationOne', '=', 'b.Code')
            ->leftJoin('res.aux_stuff as c', "$ts.$charge", '=', 'c.Id')
            ->where('b.DomainCode', 'WorkSheetTypeOne')
            //统计超时工单排除4种状态
            ->whereNotIn($ts . '.Status', ['Done', 'Closed', 'Todo', 'ReAppoint'])
            ->where('b.ENG', '!=', 'N/A')
            ->whereRaw('LENGTH(b.ENG)>0')
            ->whereRaw("($ts.DatacenterId = $groupId or $ts.DatacenterTwoId = $groupId)")
            ->get()->toArray();

        foreach ($datas as &$data) {
            foreach ($supports as &$support) {
                //这里计算时长单位用秒
                $processTs = $support['ProcessTs'] ? strtotime($support['ProcessTs']) : time();//已处理时间，若没有取当前
                $ts = $support['Ts'] ? strtotime($support['Ts']) : time();//工单创建时间
                $hangupDuration = $support['hangupDuration'] ? $support['hangupDuration'] : 0;//挂起时长
                $referTime = $support['ENG'] ? 60 * $support['ENG'] : 0;//参考时长
                $overTime = $processTs - $hangupDuration - $referTime - $ts;//计算出的超时时长

                //表示即将超时还未超时的工单
                if ($overTime + $willOverTime >= 0 and $overTime < 0) {
                    if ($data['Id'] == $support['stuffId']) {
                        $data['willOverSum'] += 1;
                    }
                }
                if ($overTime >= 0) {
                    if ($data['Id'] == $support['stuffId']) {
                        $data['overTimeSum'] += 1;
                    }
                }
            }
        }
        return $datas;
    }

    public function getDatasByCharge($groupId, $chargeUserArr, $ts, $charge, $month = "")
    {
        if (empty($month)) {
            $month = date("Y-m", time());
        }
        $datas = Support::selectRaw("c.Name,c.Id,count(null) as overTimeSum,
        count(CASE WHEN $ts.Status = 'Closed' or $ts.Status = 'Done' THEN 1 ELSE NULL END) as doneSum")
            ->selectRaw("count(CASE WHEN $ts.Evaluation = 'Good' or $ts.Evaluation = 'Best' THEN 1 ELSE NULL END) as evaluationSum")
            ->leftJoin('res.aux_stuff as c', "$ts.$charge", '=', 'c.Id')
            ->whereRaw("(Status = 'Done' or Status = 'Closed')")
            ->whereRaw("(ClassInficationOne != 'emailRequest' or ClassInficationOne IS NULL)")
            ->whereIn($charge, $chargeUserArr)
            ->where(\DB::raw("DATE_FORMAT($ts.Ts,'%Y-%m')"), $month);
        if ($charge == 'ChargeUserTwoId') {
            $datas = $datas->whereRaw('(ChargeUserId <> ChargeUserTwoId or ChargeUserId is NULL)');
        } elseif ($charge == 'AsuserId') {
            $datas = $datas->whereRaw('((ChargeUserId <> AsuserId and ChargeUserTwoId <> AsuserId) or ChargeUserId is NULL or ChargeUserTwoId is NULL)');
        }
        $datas = $datas->groupBy($charge)->get()->toArray();

        //获取计算超时的必要信息
        $supports = Support::select('c.Id as stuffId', $ts . '.ProcessTs', $ts . '.Ts', $ts . '.hangupDuration',
            'b.ENG', $ts . '.ClassInficationOne')
            ->leftJoin('res.aux_dict as b', $ts . '.ClassInficationOne', '=', 'b.Code')
            ->leftJoin('res.aux_stuff as c', "$ts.$charge", '=', 'c.Id')
            ->where('b.DomainCode', 'WorkSheetTypeOne')
            //统计超时工单排除4种状态
            ->whereNotIn($ts . '.Status', ['Done', 'Closed', 'Todo', 'ReAppoint'])
            ->where('b.ENG', '!=', 'N/A')
            ->whereRaw('LENGTH(b.ENG)>0')
            ->whereRaw("($ts.DatacenterId = $groupId or $ts.DatacenterTwoId = $groupId)")
            ->get()->toArray();

        foreach ($datas as &$data) {
            foreach ($supports as &$support) {
                //这里计算时长单位用秒
                $processTs = $support['ProcessTs'] ? strtotime($support['ProcessTs']) : time();//已处理时间，若没有取当前
                $ts = $support['Ts'] ? strtotime($support['Ts']) : time();//工单创建时间
                $hangupDuration = $support['hangupDuration'] ? $support['hangupDuration'] : 0;//挂起时长
                $referTime = $support['ENG'] ? 60 * $support['ENG'] : 0;//参考时长
                $overTime = $processTs - $hangupDuration - $referTime - $ts;//计算出的超时时长

                //表示即将超时还未超时的工单
                if ($overTime >= 0) {
                    if ($data['Id'] == $support['stuffId']) {
                        $data['overTimeSum'] += 1;
                    }
                }
            }
        }
        return $datas;
    }

    /**
     * 获取对应工作组所有负责人当前工单情况
     * @param $groupId @工作组Id
     * @param int $willOverTime @即将超时提前量参数
     * @return mixed
     */
    public function getSupportsByGroup($groupId, $willOverTime = 300)
    {
        $ts = self::$tableSupport;
        //拿取对应工作组所有负责人成员Id
        $chargeUsers = ThirdCallHelper::getGroupMembers($groupId)->toArray();
        $chargeUserArr = [];
        foreach ($chargeUsers as $chargeUser) {
            $chargeUserArr[] = $chargeUser['UserId'];
        }
        //以第一负责人拿数据
        $arr1 = $this->getSupportsByCharge($groupId, $chargeUserArr, $ts, "ChargeUserId", $willOverTime);
        //以第二负责人拿数据
        $arr2 = $this->getSupportsByCharge($groupId, $chargeUserArr, $ts, "ChargeUserTwoId", $willOverTime);
        //以指派人即跟组人拿数据
        $arr3 = $this->getSupportsByCharge($groupId, $chargeUserArr, $ts, "AsuserId", $willOverTime);

        //两组数据合并
        foreach ($arr1 as &$item1) {
            foreach ($arr2 as $k => $item2) {
                if ($item1['Id'] == $item2['Id']) {
                    $item1["doingSum"] += $item2["doingSum"];
                    $item1["suspendSum"] += $item2["suspendSum"];
                    $item1["willOverSum"] += $item2["willOverSum"];
                    $item1["overTimeSum"] += $item2["overTimeSum"];
                } else {
                    $item1[$k] = $item2;
                }
            }
        }

        foreach ($arr1 as &$item1) {
            foreach ($arr3 as $k => $item3) {
                if ($item1['Id'] == $item3['Id']) {
                    $item1["doingSum"] += $item3["doingSum"];
                    $item1["suspendSum"] += $item3["suspendSum"];
                    $item1["willOverSum"] += $item3["willOverSum"];
                    $item1["overTimeSum"] += $item3["overTimeSum"];
                } else {
                    $item1[$k] = $item3;
                }
            }
        }

        foreach ($arr1 as &$item1) {
            //判断负责人是否在线
            $cachekey = ITSM_LOGIN . $item1["Id"];
            $item1['isOnLine'] = Cache::has($cachekey) ? "在线" : "离线";
        }
        $arr1 = $this->sortArray($arr1, 'doingSum');
        return $arr1;
    }

    /**
     * 二维数组按照字段排序
     * @param $arr
     * @param $field
     * @return mixed
     */
    protected function sortArray($arr, $field)
    {
        if (empty($arr)) {
            return $arr;
        }
        $sort = [
            'direction' => 'SORT_DESC',
            'field'     => $field
        ];
        $arrSort = [];
        foreach ($arr as $uniqueId => $row) {
            foreach ($row as $key => $value) {
                $arrSort[$key][$uniqueId] = $value;
            }
        }
        if ($sort['direction']) {
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $arr);
        }
        return $arr;
    }

    /**
     * 刷新相关缓存
     * @param $source
     * @return array
     */
    public function refresh($source){
        $role = $this->getUserRole();
        $userId = Request()->session()->get('user')->Id;
        $isAdmin = ($role == self::ROLE_BOSS || $role == self::ROLE_DESK_MANAGER)?true:false;
        $cacheKey = "CUST-DASHBOARD-".($isAdmin?"Admin-":"Stuff$userId-");
        switch($source){
            case 'echarts':
                Cache::tags("ITSM-DASHBOARD")->forget('CUST-DASHBOARD-ECHARTSEVAL');
                $cacheKey = 'CUST-DASHBOARD-ECHARTSTREND-2014';
                break;
            case 'currentSup':
                $cacheKey = "CUST-DASHBOARD-CURRENT";
                break;
            case 'thisMonth':
                $month = date("Y-m", time());
                $cacheKey = $cacheKey.$month;
                break;
            case 'prevMonth':
                $month = date("Y-m", strtotime("-1 month"));
                $cacheKey = $cacheKey.$month;
                break;
        }
        Cache::tags("ITSM-DASHBOARD")->forget($cacheKey);
        return ["status"=>true];
    }
}
