<?php
/**
 * User: Wujiang <wuj@51idc.com>
 * Date: 8/11/16 18:36
 */
namespace Itsm\Http\Controllers\Supports;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Itsm\Http\Controllers\Controller;
use Itsm\Http\Controllers\Dashboard\Index;
use Itsm\Http\Helper\PublicMethodsHelper;
use Itsm\Http\Helper\ThirdCallHelper;
use Itsm\Jobs\SendEmail;
use Itsm\Jobs\SpeedAnswer;
use Itsm\Model\Res\AuxDict;
use Itsm\Model\Res\AuxStuff;
use Itsm\Model\Res\AuxStuffDatacenter;
use Itsm\Model\Res\ResContact;
use Itsm\Model\Res\ResCusInf;
use Itsm\Model\Res\ResDataCenter;
use Itsm\Model\Res\ResDev;
use Itsm\Model\Res\ResUserGroup;
use Itsm\Model\Usercenter\Change;
use Itsm\Model\Usercenter\Correlation;
use Itsm\Model\Usercenter\Issue;
use Itsm\Model\Usercenter\Operation;
use Itsm\Model\Usercenter\Support;
use Itsm\Model\Usercenter\SupportKz;
use Itsm\Model\Usercenter\SupportRelation;
use Itsm\Model\Usercenter\Suppstencil;
use Itsm\Model\Usercenter\TimedEvents;
use Itsm\Model\Usercenter\UpLoad;
use Itsm\Model\Usercenter\Userlogin;
use Itsm\Model\Usercenter\UserSupport;
use Itsm\Model\Usercenter\WechatUserInfo;
use Maatwebsite\Excel\Excel;

class SupportsController extends Controller
{
    //无响应
    const NO_REPLY = 0;
    //小于2分钟
    const REPLY_ELT_TWO_MIN = 2;
    //2-5分钟
    const REPLY_TWO2FIVE_MIN = 5;
    //大于5分钟
    const REPLY_GT_FIVE_MIN = 10;

    //条件值定义
    const TWO_MIN = 2;
    const FIVE_MIN = 5;

    /**  工单表名称 @var string */
    protected static $tableSupport = 'usercenter.support';
    protected static $tableKz = 'usercenter.support_kz';

    public function getMyHeadImage(Request $req)
    {//select headimgurl from usercenter.wechat_userinfo where userLoginId='wangcq' and subscribe=1

        $user = $req->session()->get('user');
        $image = WechatUserInfo::select('headimgurl')->where('userLoginId', $user->LoginId)->where('subscribe',
            WechatUserInfo::DISABLED_YES)->first();
        if ($image && $image->headimgurl) {
            return $image->headimgurl;
        } else {
            return '';
        }

    }

    /**
     * 提交工单页面
     * @param Request $req
     * @param Response $res
     * @return
     */
    public function create(Request $req, Response $res)
    {
        $params = $req->all();

        if ($name = $req->input('cusName')) {//获取客户信息请求
            return ThirdCallHelper::getCusInf($name);
        }
        if ($contactId = $req->input('contactId')) {//获取客户对应所有联系人请求
            return ThirdCallHelper::getContactInf($contactId);
        }
        if ($cusId = $req->input('cusId')) {//获取联系人信息请求
            return ThirdCallHelper::getContacts($cusId);
        }
        if ($cusId = $req->input('getLoginList')) {//获取客户所有登录账号
            return ThirdCallHelper::getLoginList($cusId);
        }
        $dataCenter = ThirdCallHelper::getDataCenter();

        return view(('supports/create'), compact('params', 'dataCenter'));
    }

    public function getCusInf($CusName)
    {
        $preArray = ResCusInf::selectRaw("res.res_cusInf.CusName,res.res_cusInf.Id,res.res_cusInf.Authorization");
        $isIp = preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $CusName);
        if (!$isIp) {
            $arr = $preArray
                ->where('res.res_cusInf.CusName', 'like', '%' . $CusName . '%')
                ->where(function ($arr) {
                    $arr->whereNull('res.res_cusInf.InValidate')
                        ->orwhere('res.res_cusInf.InValidate', '<>', ResCusInf::DISABLED_YES);
                })
                ->take(100)
                ->get();
        } else {
            $arr = ResCusInf::selectRaw("distinct(res.res_cusInf.CusName),res.res_cusInf.Id,res.res_cusInf.Authorization")
                ->leftJoin('res.res_reshis as a', 'res.res_cusInf.Id', '=', 'a.CusId')
                ->leftJoin('res.res_rescarrier as b', 'b.Id', '=', 'a.ResId')
                ->leftJoin('res.resv_resattr as c', 'b.Id', '=', 'c.ResId')
                ->whereNull('ReleaseTs')
                ->where('c.attrCode', 'ip_addr')
                ->where('c.Restype', 'IP')
                ->where('c.AttrValue', 'like', '%' . $CusName . '%')
                ->take(100)
                ->get();
        }
        return $arr;
    }

    /**
     * 需要参数supId即被拆分工单的Id，获取原被拆分工单信息
     * @param Request $req
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function supportSplit(Request $req)
    {
        $support = Support::where('Id', $req->input('supId'))->first();
        $parentSum = Support::where('parentSupportId', $req->input('supId'))->get()->count();
        $num = $parentSum == 0 ? '-01' : '-0' . ($parentSum + 1);
        return view('supports/supportsplit', [
            'support' => $support,
            'num'     => $num
        ]);
    }

    /**
     * 获取待办工单总条数
     * @param Request $req
     * @return int
     */
    public function getTodoNum(Request $req)
    {
        $user = $req->session()->get('user')->Id;
        $num = Support::select('Id')
            ->whereRaw("(ChargeUserId = $user or ChargeUserTwoId = $user or AsuserId = $user )")
            ->whereNotIn('Status', ["Done", "Closed"])
            ->get()->count();
        return $num;
    }

    /**
     * 工单提交的验证及验证通过后写入
     * @param Request $req
     * @param Response $res
     * @return array
     */
    public function createSubmit(Request $req, Response $res)
    {
        $reqAll = $req->all();
        $user = $req->session()->get('user');
        $parentSupportId = empty($reqAll['parentSupportId']) ? null : $reqAll['parentSupportId'];//判断是否是拆分出来的工单
        /*页面输入校验 验证提交内容是否规范*/
        $validator = Validator::make($reqAll, [
            'CustomerId' => 'required',
            'title'      => 'required',
            'datacenter' => 'required'
        ], [
            'required' => ':attribute 的字段是必要的。',
        ]);

        $body = strip_tags(html_entity_decode(trim($req->input('content'))));
        if($body == "" || strlen($body) == 0){
            return ['status' => false, 'statusMsg' => '工单内容有效内容为空或包含非法字符！'];
        }

        if ($validator->fails()) {//验证不通过,
            return ['status' => false, 'statusMsg' => '工单提交失败!'];
        } else {
            if(!isset($reqAll["subType"]) && !$reqAll['contactId']){
                return ['status' => false, 'statusMsg' => '工单提交失败，请选择联系人!'];
            }
            $contactName = ResContact::select("Name")->where("Id",$reqAll['contactId'])->first();
            $contactName = !empty($contactName)?$contactName->Name:"";
            $ret = Support::insertGetId([
                'Title'           => $reqAll['title'],
                'CustomerInfoId'  => $reqAll['CustomerId'],
                'ContactId'       => $reqAll['contactId'],
                'contactName'       => $contactName,
                'email'           => $reqAll['email'],
                'mobile'          => $reqAll['mobile'],
                'EquipmentId'     => $reqAll['equipmentId'],
                'devIPAddr'       => $reqAll['DevId'],
                'dataCenter'      => $reqAll['datacenter'],
                'CreateUserId'    => $user->Id,
                'Status'          => 'Todo',
                'ServiceModel'    => $reqAll['serviceModel'],
                'Source'          => 'oshelp',
                'priority'        => '3',
                'userId'        => $reqAll['userId']?$reqAll['userId']:null,
                'Body'            => strip_tags(html_entity_decode(trim($req->input('content'))),
                    '<br><p><img><b><u><hr><span>'),
                'Ts'              => date('Y-m-d H:i:s'),
                'UpTs'              => date('Y-m-d H:i:s'),
                'parentSupportId' => $parentSupportId
            ]);
            if ($ret == false) {//插入数据失败
                return ['status' => false, 'statusMsg' => '提交出错,请稍后再试!'];
            } else {
                if (!empty($reqAll['triggerId'])) {
                    $relate = Correlation::insertGetId([
                        'changeId'     => Arr::get($reqAll, "changeId", 0),
                        'supportId'    => $ret,
                        'issueId'      => Arr::get($reqAll, "issueId", 0),
                        'repositoryId' => Arr::get($reqAll, "repositoryId", 0),
                        'triggerId'    => Arr::get($reqAll, "triggerId", 0),
                        'userId'       => $user->Id,
                        'ts'           => date('Y-m-d H:i:s', time()),
                    ]);
                }
                return ['status' => $ret, 'statusMsg' => '工单提交成功!'];
            }
        }
    }

    /**
     * 设备页面
     * @param Request $req
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function selectEquipment(Request $req)
    {
        return view('supports/equipmentlist', [
            'cusinfId' => $req->input('cusinfId'),
            'mode'     => $req->input('mode')
        ]);
    }

    /**
     * 获取客户对应的所有设备信息  也支持检索IP返回对应设备
     * @param Request $req
     * @return mixed
     */
    public function getEquipmentList(Request $req)
    {
        $ip = $req->input('IPaddr') ? $req->input('IPaddr') : '';//支持IP地址查询关联设备
        $devtype = Input::get('mode');
        $resdev = ResDev::select('res_dev.DevId', 'res_dev.DevType', 'res_dev.devIpaddrone',
            'res_datacenter.DataCenterName', 'aux_dict.Means')
            ->leftJoin('res_datacenter', 'res_datacenter.Id', '=', 'res_dev.DatacenterId')
            ->leftJoin('aux_dict', 'res_dev.DevType', '=', 'aux_dict.Code')
            ->where('aux_dict.DomainCode', 'DevType')
            ->where('res_dev.CustomerId', $req->input('cusinfId'))
            ->where('res_datacenter.type', $devtype)
            ->where('res_dev.devIpaddrone', 'like', '%' . trim($ip) . '%')
            ->where(function ($arr) {
                $arr->whereNull('res_dev.InValidate')
                    ->orwhere('res_dev.InValidate', '<>', ResDev::DISABLED_YES);
            });
        $reqarray['total'] = $resdev->count();
        $reqarray['rows'] = $resdev->limit($req->input('pageSize'))
            ->offset(($req->input('pageNumber') - 1) * $req->input('pageSize'))->get();
        return $reqarray;
    }

    /**
     * 待操作列表
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function todoList()
    {
        $statusList = PublicMethodsHelper::getSupportStatusList();
        $customerList = PublicMethodsHelper::getCustomerList();
        return view('supports/todolist', [
            'statusList'   => $statusList,
            'customerList' => $customerList
        ]);
    }

    /**
     * 获取待操作工单
     * @param Request $req
     * @param Response $rep
     * @return mixed
     */
    public function getTodoList(Request $req, Response $rep)
    {
        /**
         * 获取用户角色
         */
        $user = $req->session()->get('user');
        $tableSupport = self::$tableSupport;

        $supportList = Support::select($tableSupport . '.*', "support_relation.id as rid",
            "support_relation.inValidate as isValidate")
            ->leftJoin('res.res_cusinf as b', $tableSupport . '.CustomerInfoId', '=', 'b.Id')
            ->leftJoin('res.aux_stuff as s', $tableSupport . '.OperationId', '=', 's.Id')
            ->leftJoin('support_relation', $tableSupport . '.Id', '=', 'support_relation.supportId');

        /**
         * 只看指派给自己的工单或者第一负责人和第二负责人是自己的
         */
        $supportList = $supportList->where(function ($supportList) use ($user) {
            $supportList->where('ChargeUserId', $user->Id)
                ->orWhere('ChargeUserTwoId', $user->Id)
                ->orwhere('AsuserId', $user->Id);
        });

        /**
         * 判断邮件工单
         */
        if ($req->input("email")) {

            $supportList = $supportList->Where($tableSupport . '.ClassInficationOne', 'emailRequest');

            //取todo得总数
            $countList = Support::select($tableSupport . '.Id')
                ->leftJoin('res.res_cusinf as b', $tableSupport . '.CustomerInfoId', '=', 'b.Id');
            $countList = $countList->where(function ($supportList) use ($user) {
                $supportList->where('ChargeUserId', $user->Id)
                    ->orWhere('ChargeUserTwoId', $user->Id)
                    ->orwhere('AsuserId', $user->Id);
            });
            $countList = $countList->Where($tableSupport . '.ClassInficationOne', 'emailRequest');
            $countList = $countList->where($tableSupport . '.Status', 'Todo');
            $todoCount = $countList->get()->count();
            $supportArray['todoCount'] = $todoCount;

        } else {

            //排除email的
            $supportList = $supportList->where(function ($supportList) use ($tableSupport) {
                $supportList->Where($tableSupport . '.ClassInficationOne', '!=', 'emailRequest')
                    ->orWhere($tableSupport . '.ClassInficationOne', null);
            });
        }

        //公共查询 Status or cusType
        if ($status = $req->input("Status")) {
            $supportList = $supportList->where($tableSupport . '.Status', $status);
            //if($status == "Suspend")$supportList->orderByRaw("$tableSupport.ClassInficationOne = 'Equipment_personnel_2015' asc");
        } else {
            //默认
            $supportList = $supportList->Where($tableSupport . '.Status', '!=',
                'Done')->Where($tableSupport . '.Status', '!=', 'Closed');
        }

        //客户类型选择
        if ($cusType = $req->input("cusType")) {
            $supportList = $supportList->where(function ($supportList) use ($cusType, $tableSupport) {
                $supportList->Where('b.CusImportanceType', $cusType)
                    ->orWhereNotNull($tableSupport . '.agentId');
            });

        }
        //筛选超时的
        if ($timeOutIds = $req->input("timeOutIds")) {
            $supportList = $supportList->whereIn($tableSupport . '.Id', explode(",", $timeOutIds));
        }

        //排序
//        $supportList->orderByRaw("FIELD($tableSupport.status,'Todo','ReAppoint','Appointed','Doing','Suspend'),$tableSupport.OperationId >=500000 desc,FIELD(b.CusImportanceType,'KeyCustomers') desc,FIELD($tableSupport.ClassInficationOne,'Equipment_personnel_2015') asc,$tableSupport.OperationId < 500000 desc,$tableSupport.UpTs desc,$tableSupport.ts desc");
        $supportList->orderByRaw("FIELD($tableSupport.status,'Todo','ReAppoint','Appointed','Doing','Suspend'),$tableSupport.OperationId >=500000 desc,FIELD($tableSupport.ClassInficationOne,'Equipment_personnel_2015') asc, FIELD(s.second_dept,'second_dept_23') desc, $tableSupport.OperationId < 500000 desc,FIELD(b.CusImportanceType,'KeyCustomers') desc,$tableSupport.UpTs desc,$tableSupport.ts desc");

        //$supportList->orderByRaw("$tableSupport.status = 'Todo' desc,$tableSupport.status = 'ReAppoint' desc,$tableSupport.status = 'Appointed' desc,$tableSupport.status = 'Doing' desc,$tableSupport.status = 'Suspend' desc,$tableSupport.OperationId >=500000 desc,b.CusImportanceType='KeyCustomers'desc,$tableSupport.ClassInficationOne = 'Equipment_personnel_2015' asc,$tableSupport.OperationId < 500000 desc,$tableSupport.UpTs desc,$tableSupport.ts desc");
        $supportArray['total'] = $supportList->count();

        //分页
        $pageSize = !empty($req->input('pageSize')) ? $req->input('pageSize') : 20;
        $pageNumber = !empty($req->input('pageNumber')) ? $req->input('pageNumber') : 1;
        $supportList = $supportList->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();

        //公共转换
        $supportList = $this->translationBySupportInfo($supportList, $tableSupport);

        $supportArray['rows'] = $supportList;
        return $supportArray;
    }

    /**
     * 适用于todoList和operateList数据转换
     * @param $supportList
     * @param $tableSupport
     * @return mixed
     */
    public function translationBySupportInfo($supportList, $tableSupport)
    {
        //获取工单第一负责人
        $supportList = ResContact::translationStuff($supportList, 'ChargeUserId');
        //获取工单第二负责人
        $supportList = ResContact::translationStuff($supportList, 'ChargeUserTwoId');
        //获取工单分类
        $supportList = Support::translationDict($supportList, 'ClassInficationOne', 'WorkSheetTypeOne');
        //获取工单二级分类
        $supportList = Support::translationDict($supportList, 'ClassInfication', 'supporteventsort');
        //获取工单来源
        $supportList = Support::translationDict($supportList, 'Source', 'supportSource');
        //获取客户评价
        $supportList = Support::translationDict($supportList, 'Evaluation', 'WorksheetAppraisal');
        //获取指派人
        $supportList = ResContact::translationStuff($supportList, 'AsuserId');
        //最后操作人L0/L1/DC处理
        $this->switchColor($supportList, 'OperationId');
        //获取最后最新操作内容
        $supportList = Operation::getLastOperation($supportList);
        //获取最后操作人名称
        $supportList = ResContact::translationStuff($supportList, 'OperationId');
        //获取工单客户名称
        $supportList = Support::translationCusName($supportList, 'CustomerInfoId');
        //获取工单处理时长
        $supportList = Support::translationOverTime($supportList, $tableSupport);
        //格式化用户身份
        $supportList = ThirdCallHelper::translationIdentity($supportList);
        //获取代理商名称
        $supportList = ThirdCallHelper::getAgentName($supportList);
        //工单内容过滤
        $supportList = PublicMethodsHelper::translationBody($supportList);

        return $supportList;
    }

    //获取待办工单详情列表
    public function OrderList($SupportId)
    {
        $orderlist = ThirdCallHelper::getSupportInf($SupportId);
        return view('supports/OrderList', [
            '$orderlist' => $orderlist
        ]);
    }

    /**
     * 操作工单列表
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function operateList()
    {
        $musicSrc = UpLoad::select('Path', 'FileSource')->where('cusInfId', '-1')->where('contractNo', '-1')->first();
        $musicSrc = env('JOB_URL') . $musicSrc->FileSource . '/cred/' . $musicSrc->Path;
        $statusList = PublicMethodsHelper::getSupportStatusList();
        $customerList = PublicMethodsHelper::getCustomerList();
        //排除已处理和已经完成的
        unset($statusList['Done'], $statusList['Closed']);
        return view('supports/operatelist', [
            'statusList'   => $statusList,
            'customerList' => $customerList,
            'musicSrc'     => $musicSrc
        ]);
    }

    public function getUpGradeList(Request $req)
    {

    }

    /**
     * 操作工单列表
     * @param Request $req
     * @param Response $rep
     * @return mixed
     */
    public function getOperateList(Request $req, Response $rep)
    {
        //获取用户角色
        $user = Request()->session()->get('user');
        $tableSupport = self::$tableSupport;

        $supportList = Support::select($tableSupport . '.*', "support_relation.id as rid",
            "support_relation.inValidate as isValidate")
            ->leftJoin('res.res_cusinf as b', $tableSupport . '.CustomerInfoId', '=', 'b.Id')
            ->leftJoin('userlogin as c', $tableSupport . '.OperationId', '=', 'c.Id')
            ->leftJoin('res.aux_stuff as s', $tableSupport . '.OperationId', '=', 's.Id')
            ->leftJoin('support_relation', $tableSupport . '.Id', '=', 'support_relation.supportId');
        //若是要获取所有升级工单则不按角色筛选，否则需要按照角色进行筛选
        if (!$req->input("upGrade")) {
            $supportList = $this->getRoleData($supportList, $tableSupport);
        }

        //如果是邮件工单 检测工单三级分类
        if ($req->input("email")) {
            //各种告警列表筛选 大范围sql
            if ($kw = Input::get('keyWord')) {
                $supportList = $supportList->where('supportTag',$kw);
            }else{//邮件请求列表中剔除各类告警邮件
                $supportList->whereNull('supportTag');
                $supportList = $supportList->Where($tableSupport . '.ClassInficationOne', 'emailRequest');
            }

            //取todo得总数
            $countList = Support::select($tableSupport . '.Id')
                ->leftJoin('res.res_cusinf as b', $tableSupport . '.CustomerInfoId', '=', 'b.Id');
            $countList = $countList->Where($tableSupport . '.ClassInficationOne', 'emailRequest');
            $userRole = $this->getUserRole();
            //只存在现场工程师看自己所在组情况,否则看所有
            if ($userRole == self::ROLE_DC_EMPLOYEE) {
                $stuffDataCenter = AuxStuffDatacenter::select('DatacenterId')
                    ->where('StuffId', $user->Id)->get();
                if(!empty($stuffDataCenter) ){
                    $stuffDataCenter = $stuffDataCenter->toArray();
                    $stuffDc = '';
                    foreach($stuffDataCenter as $dc){
                        $stuffDc .= ($stuffDc == ''?"":",").$dc['DatacenterId'];
                    }
                }else{
                    $stuffDc = '';
                }

                if ($stuffDc) {
                    $countList = $countList->whereRaw("($tableSupport.DatacenterId IN ($stuffDc) OR $tableSupport.DatacenterTwoId IN($stuffDc))");
                }
            }

            if ($kw = Input::get('keyWord')) {
                $countList = $countList->where('supportTag',$kw);
            }else{//邮件请求列表中剔除各类告警邮件
                $countList = $countList->whereNull('supportTag');
                $countList = $countList->Where($tableSupport . '.ClassInficationOne', 'emailRequest');
            }

            $countList = $countList->where($tableSupport . '.Status', 'Todo');
            $todoCount = $countList->get()->count();
            $supportArray['todoCount'] = $todoCount;

        } else {
            if ($req->input("upGrade")) {
                //只显示升级过的工单
                $supportList = $supportList->where($tableSupport . '.upGrade', 1);
            } else {
                //排除email的
                $supportList = $supportList->where(function ($supportList) use ($tableSupport) {
                    $supportList->Where($tableSupport . '.ClassInficationOne', '!=', 'emailRequest')
                        ->orWhere($tableSupport . '.ClassInficationOne', null);
                });
            }
        }

        if (empty(Input::get('keyWord'))) {
            $supportList = $supportList->where(function($supportList) use ($tableSupport){
                $supportList->where( $tableSupport.'.supportTag',"!=","largearea-alarm")
                    ->orWhere($tableSupport . '.supportTag', null);
            });
        }

        //公共查询 Status or cusType
        if ($status = $req->input("Status")) {
            if ($status != 'Done' && $status != 'Closed') {
                $supportList = $supportList->where($tableSupport . '.Status', $status);
            }
        } else {
            //默认
            $supportList = $supportList->Where($tableSupport . '.Status', '!=',
                'Done')->Where($tableSupport . '.Status', '!=', 'Closed');
        }
        //过滤超时的
        if ($timeOutIds = $req->input("timeOutIds")) {
            $supportList->whereIn($tableSupport . '.Id', explode(",", $timeOutIds));
        }
        if ($cusType = $req->input("cusType")) {
            $supportList = $supportList->where(function ($supportList) use ($cusType, $tableSupport) {
                $supportList->Where('b.CusImportanceType', $cusType);
                if ($cusType == "agent") {
                    $supportList->orWhereNotNull($tableSupport . '.AgentId');
                }
            });

        }
        if ($sortName = $req->input('sortName')) {
            $sortOrder = $req->input('sortOrder');
            $supportList->orderByRaw("$tableSupport.$sortName $sortOrder");
        } else {
            //排序
            $supportList->orderByRaw("FIELD($tableSupport.status,'Todo','ReAppoint','Appointed','Doing','Suspend'),$tableSupport.OperationId >=500000 desc,FIELD(s.second_dept,'second_dept_23') desc,FIELD($tableSupport.ClassInficationOne,'Equipment_personnel_2015') asc,$tableSupport.OperationId < 500000 desc,FIELD(b.CusImportanceType,'KeyCustomers') desc,$tableSupport.UpTs desc,$tableSupport.ts desc");
        }
        $supportArray['total'] = $supportList->count();

        //分页
        $pageSize = !empty($req->input('pageSize')) ? $req->input('pageSize') : 20;
        $pageNumber = !empty($req->input('pageNumber')) ? $req->input('pageNumber') : 1;
        $supportList = $supportList->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();

        //公共转换
        $supportList = $this->translationBySupportInfo($supportList, $tableSupport);

        $supportArray['rows'] = $supportList;

        if ($req->input("email") &&$req->input("reqType")) {
            $supportArray = $this->hasTag($supportArray);
        }else if(!$req->input("email")){
            $supportArray['juhe'] = $this->sameSupport($req,$rep);
        }

        return $supportArray;
    }

    protected function hasTag($supportArray){
        $tagList = ThirdCallHelper::getDictArray("工单标签","supportTag");
        foreach($tagList as $item){
            $list = Support::select('Id')->where("supportTag",$item->Code)
                ->Where('Status', 'Todo')->count();
            $supportArray[$item->Code] = $list;
            $list2 = Support::select('Id')->where("supportTag",$item->Code)
                ->Where('Status','!=', 'Closed')->where('Status','!=', 'Done')->count();
            $supportArray["s".$item->Code] = $list2;
        }
        $supportArray['tagList'] = $tagList;
        return $supportArray;
    }

    protected function getSameSupportCount(Request $req, Response $rep, $fiveMinute)
    {
        $tableSupport = self::$tableSupport;
        $supportList = Support::selectRaw("count(*) AS count,dataCenter,ClassInficationOne")
            ->leftJoin('res.res_cusinf as b', $tableSupport . '.CustomerInfoId', '=', 'b.Id');

        $supportList = $this->getRoleData($supportList, $tableSupport);

        $supportList = $supportList->where('Ts', '>=', $fiveMinute)
            ->whereNotNull('ClassInficationOne')->whereNotNull('dataCenter')
            ->where(function ($supportList) use ($tableSupport) {
                $supportList->where($tableSupport . '.ClassInficationOne', 'Network_attack_2015')
                    ->orWhere($tableSupport . '.ClassInficationOne', 'Network_fault_check_2015');
            });

        //公共查询 Status or cusType
        if ($status = $req->input("Status")) {
            if ($status != 'Done' && $status != 'Closed') {
                $supportList = $supportList->where($tableSupport . '.Status', $status);
            }
        } else {
            //默认
            $supportList = $supportList->Where($tableSupport . '.Status', '!=',
                'Done')->Where($tableSupport . '.Status', '!=', 'Closed');
        }
        /*//过滤超时的
        if ($timeOutIds = $req->input("timeOutIds")) {
            $supportList->whereIn($tableSupport . '.Id', explode(",", $timeOutIds));
        }*/
        if ($cusType = $req->input("cusType")) {
            $supportList = $supportList->where(function ($supportList) use ($cusType, $tableSupport) {
                $supportList->Where('b.CusImportanceType', $cusType)
                    ->orWhereNotNull($tableSupport . '.agentId');
            });

        }
        $supportList = $supportList->groupby("dataCenter", "ClassInficationOne")->get()->toArray();
        foreach ($supportList as $k => $item) {
            if ($item['count'] < 5) {
                unset($supportList[$k]);
            }
        }
        return $supportList;
    }

    /**
     * 统计5分钟以内的工单列表 聚合工单
     * @param Request $req
     * @param Response $rep
     * @return mixed
     */
    public function sameSupport(Request $req, Response $rep)
    {
        $returnData = [
            'rows'  => [],
            'total' => 0
        ];
        $fiveMinute = date('Y-m-d H:i:s', strtotime("-5 minute"));
        $countCondition = $this->getSameSupportCount($req, $rep, $fiveMinute);
        if (!$countCondition) {
            return $returnData;
        }

        $tableSupport = self::$tableSupport;

        $supportList = Support::select($tableSupport . '.*')
            ->leftJoin('res.res_cusinf as b', $tableSupport . '.CustomerInfoId', '=', 'b.Id');

        $supportList = $this->getRoleData($supportList, $tableSupport);

        $supportList = $supportList->where('Ts', '>=', $fiveMinute)->where($tableSupport . '.ClassInficationOne', '!=',
            'emailRequest')->whereNotNull('ClassInficationOne')->whereNotNull('dataCenter');
        //根据获得的数据中心和工单分类筛选
        $supportList->where(function ($supportList) use ($countCondition) {
            foreach ($countCondition as $k => $item) {
                $supportList->orwhereRaw("ClassInficationOne = '{$item['ClassInficationOne']}' AND dataCenter = '{$item['dataCenter']}'");
            }
        });
        //公共查询 Status or cusType
        if ($status = $req->input("Status")) {
            if ($status != 'Done' && $status != 'Closed') {
                $supportList = $supportList->where($tableSupport . '.Status', $status);
            }
        } else {
            //默认
            $supportList = $supportList->Where($tableSupport . '.Status', '!=',
                'Done')->Where($tableSupport . '.Status', '!=', 'Closed');
        }
        if ($cusType = $req->input("cusType")) {
            $supportList = $supportList->where(function ($supportList) use ($cusType, $tableSupport) {
                $supportList->Where('b.CusImportanceType', $cusType)
                    ->orWhereNotNull($tableSupport . '.agentId');
            });

        }
        if ($sortName = $req->input('sortName')) {
            $sortOrder = $req->input('sortOrder');
            $supportList->orderByRaw("$tableSupport.$sortName $sortOrder");
        } else {
            //排序
            $supportList->orderByRaw("$tableSupport.status = 'Todo' desc,$tableSupport.status = 'ReAppoint' desc,$tableSupport.status = 'Appointed' desc,$tableSupport.status = 'Doing' desc,$tableSupport.status = 'Suspend' desc,$tableSupport.upts desc,$tableSupport.ts desc");
        }
        $supportArray['total'] = $supportList->count();

        //分页
        $pageSize = !empty($req->input('pageSize')) ? $req->input('pageSize') : 20;
        $pageNumber = !empty($req->input('pageNumber')) ? $req->input('pageNumber') : 1;
        $supportList = $supportList->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();

        //公共转换
        $supportList = $this->translationBySupportInfo($supportList, $tableSupport);

        $supportArray['rows'] = $supportList;
        return $supportArray;
    }


    /**
     * 查询所有工单
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function searchList()
    {
        $chargeGroupList = AuxDict::where('DomainCode', 'chargeGroup')->where('Domain', '负责人群组')->get();
        $statusList = PublicMethodsHelper::getSupportStatusList();
        $customerTypeList = PublicMethodsHelper::getCustomerList();
        $responseTimeList = PublicMethodsHelper::getResponseTimeList();
        $dataCenterList = ResDataCenter::select('Id', 'DataCenterName')->orderby('res_datacenter.Id')->get();
        $evaluationList = AuxDict::where('DomainCode', 'WorksheetAppraisal')->where('Domain', '技术支持客户满意度评价')->get();
        $priorityList = [1, 2, 3];
        return view('supports/searchlist', [
            'chargeList'       => $chargeGroupList,
            'statusList'       => $statusList,
            'dataCenterList'   => $dataCenterList,
            'evaluationList'   => $evaluationList,
            'priorityList'     => $priorityList,
            'responseTimeList' => $responseTimeList,
            'customerTypeList' => $customerTypeList
        ]);
    }

    /**
     * 查看所有
     * @param Request $req
     * @param Response $rep
     * @return mixed
     */
    public function getSearchList(Request $req, Response $rep)
    {
        $tableSupport = self::$tableSupport;

        $supportList = Support::select($tableSupport . '.*')
            ->leftJoin('res.res_cusinf as b', $tableSupport . '.CustomerInfoId', '=', 'b.Id');

        //判断是否是点击过来的
        if ($source = $req->input("source")) {
            $supportList = $this->searchByLink($supportList, $req);
        } else {
            //过滤权限条件
            $supportList = $this->getRoleData($supportList, $tableSupport);
        }
        //高级条件查询
        $searchList = [
            'SuppOptGroup',
            'priority',
            'cusType',
            'Status',
            'dataCenter',
            'replyTime',
            'Evaluation',
            'searchInfo',
            'user',
            'timeOutIds'
        ];
        foreach ($searchList as $search) {//工单检索
            $reqSearch = $req->input($search);
            if ($reqSearch != '') {
                switch ($search) {
                    case 'timeOutIds':
                        $supportList->whereIn($tableSupport . '.Id', explode(",", $reqSearch));
                        break;
                    case 'user':
                        if ($reqSearch == 'mySupport') {
                            $user = $req->session()->get('user');
                            $supportList->where(function ($supportList) use ($user) {
                                $supportList->where('ChargeUserId', $user->Id)
                                    ->orWhere('ChargeUserTwoId', $user->Id)
                                    ->orwhere('AsuserId', $user->Id);
                            });
                        }
                        break;
                    case 'searchInfo':
                        $supportList->where(function ($supportList) use ($reqSearch, $tableSupport) {
                            $supportList->Where('b.CusName', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.Title', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.Id', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.devIPAddr', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.Memo', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.EquipmentId', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.changeNO', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.problemNO', 'like', '%' . trim($reqSearch) . '%');

                        });
                        break;
                    case 'cusType':
                        $supportList->where(function ($supportList) use ($reqSearch, $tableSupport) {
                            $supportList->Where('b.CusImportanceType', $reqSearch)
                                ->orWhereNotNull($tableSupport . '.agentId');
                        });

                        break;
                    case 'replyTime':
                        switch ($reqSearch) {
                            case self::NO_REPLY:
                                $supportList->whereNull($tableSupport . '.FirstReplyTs');
                                break;
                            case self::REPLY_ELT_TWO_MIN:
                                $supportList->whereRaw("TIMESTAMPDIFF(SECOND, $tableSupport . ts, $tableSupport . FirstReplyTs) <= " . self::TWO_MIN * 60);
                                break;
                            case self::REPLY_TWO2FIVE_MIN:
                                $supportList->whereRaw("TIMESTAMPDIFF(SECOND, $tableSupport . ts, $tableSupport . FirstReplyTs)) > " . (self::TWO_MIN * 60) . " AND TIMESTAMPDIFF(SECOND, $tableSupport . ts, $tableSupport . FirstReplyTs) <= " . (self::FIVE_MIN * 60));
                                break;
                            case self::REPLY_GT_FIVE_MIN:
                                $supportList->whereRaw("TIMESTAMPDIFF(SECOND, $tableSupport . ts, $tableSupport . FirstReplyTs) >  " . (self::FIVE_MIN * 60));
                                break;
                        };
                        break;
                    case 'SuppOptGroup':
                        $supportList = $supportList->where("SuppOptGroup", $reqSearch);
                        break;
                    //判断未评价的
                    case 'Evaluation':
                        if ($reqSearch == 'notEvaluate') {
                            $supportList->whereNull("Evaluation");
                        } else {
                            $supportList->where($tableSupport . '.' . $search, $reqSearch);
                        }
                        break;
                    default:
                        $supportList->where($tableSupport . '.' . $search, $reqSearch);
                }
            }
        }
        //排序
        $supportList = $supportList->orderByRaw("$tableSupport.status = 'Todo' desc,$tableSupport.status = 'ReAppoint' desc,$tableSupport.status = 'Appointed' desc,$tableSupport.status = 'Doing' desc,$tableSupport.status = 'Suspend' desc,$tableSupport.status = 'Done' desc,$tableSupport.status = 'Closed' desc,$tableSupport.upts desc,$tableSupport.ts desc");
        $supportArray['total'] = $supportList->count();
        //分页
        $pageSize = !empty($req->input('pageSize')) ? $req->input('pageSize') : 20;
        $pageNumber = !empty($req->input('pageNumber')) ? $req->input('pageNumber') : 1;
        $supportList = $supportList->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();

        $supportList = ResContact::translationStuff($supportList, 'ChargeUserId');
        $supportList = ResContact::translationStuff($supportList, 'ChargeUserTwoId');
        $supportList = Support::translationDict($supportList, 'ClassInficationOne', 'WorkSheetTypeOne');
        $supportList = Support::translationDict($supportList, 'Evaluation', 'WorksheetAppraisal');
        $supportList = ResContact::translationStuff($supportList, 'AsuserId');
        $supportList = ResContact::translationStuff($supportList, 'OperationId');
        $supportList = Support::translationCusName($supportList, 'CustomerInfoId');
        $supportList = ThirdCallHelper::translationIdentity($supportList);
        $supportList = ThirdCallHelper::getAgentName($supportList);
        $supportList = Support::translationOverTime($supportList, $tableSupport);
        $supportList = ThirdCallHelper::translationPreDictTs($supportList);

        $supportArray['rows'] = $supportList;

        return $supportArray;
    }

    /**
     * 查看所有工单
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function allList(Request $req)
    {
        $search = Input::get('searchInfo');
        $chargeGroupList = AuxDict::where('DomainCode', 'chargeGroup')->where('Domain', '负责人群组')->get();
        $statusList = PublicMethodsHelper::getSupportStatusList();
        $customerTypeList = PublicMethodsHelper::getCustomerList();
        $responseTimeList = PublicMethodsHelper::getResponseTimeList();
        $dataCenterList = ResDataCenter::select('Id', 'DataCenterName')->orderby('res_datacenter.Id')->get();
        $evaluationList = AuxDict::where('DomainCode', 'WorksheetAppraisal')->where('Domain', '技术支持客户满意度评价')->get();
        $priorityList = [1, 2, 3];
        return view('supports/alllist', [
            'search'           => $search,
            'chargeList'       => $chargeGroupList,
            'statusList'       => $statusList,
            'dataCenterList'   => $dataCenterList,
            'evaluationList'   => $evaluationList,
            'priorityList'     => $priorityList,
            'responseTimeList' => $responseTimeList,
            'customerTypeList' => $customerTypeList
        ]);
    }

    /**
     * 查看我的收藏工单
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function collectionList()
    {
        $chargeGroupList = AuxDict::where('DomainCode', 'chargeGroup')->where('Domain', '负责人群组')->get();
        $statusList = PublicMethodsHelper::getSupportStatusList();
        $customerTypeList = PublicMethodsHelper::getCustomerList();
        $responseTimeList = PublicMethodsHelper::getResponseTimeList();
        $dataCenterList = ResDataCenter::select('Id', 'DataCenterName')->orderby('res_datacenter.Id')->get();
        $evaluationList = AuxDict::where('DomainCode', 'WorksheetAppraisal')->where('Domain', '技术支持客户满意度评价')->get();
        $priorityList = [1, 2, 3];
        $json = TimedEvents::where("Name","工单收藏原因分类")->first();
        $arr = "";
        if(!empty($json)){
            $arr = explode(",",$json->Parameter);
        }else{
            $arr = [
                "长期资源>3天",
                "短期资源<3天",
                "其它"
            ];
        }
        $ismine = Input::get("ismine");
        return view('supports/collectionlist', [
            'chargeList'       => $chargeGroupList,
            'statusList'       => $statusList,
            'dataCenterList'   => $dataCenterList,
            'evaluationList'   => $evaluationList,
            'priorityList'     => $priorityList,
            'responseTimeList' => $responseTimeList,
            'customerTypeList' => $customerTypeList,
            'ismine' => $ismine,
            'arr' => $arr
        ]);
    }

    /**
     * 查看所有
     * @param Request $req
     * @param Response $rep
     * @return mixed
     */
    public function getAllList(Request $req, Response $rep)
    {
        $tableSupport = self::$tableSupport;

        $supportList = Support::select("$tableSupport.*","support_relation.id as rid")
            ->join('res.res_cusinf as b', $tableSupport . '.CustomerInfoId', '=', 'b.Id')
            ->leftJoin('support_relation', $tableSupport . '.Id', '=', 'support_relation.supportId');

        //过滤权限条件
        $supportList = $this->getRoleData($supportList, $tableSupport);

        //高级条件查询
        $searchList = [
            'SuppOptGroup',
            'priority',
            'cusType',
            'Status',
            'dataCenter',
            'replyTime',
            'Evaluation',
            'searchInfo',
            'user',
            'timeOutIds'
        ];
        foreach ($searchList as $search) {//工单检索
            $reqSearch = $req->input($search);
            if ($reqSearch != '') {
                switch ($search) {
                    case 'timeOutIds':
                        $supportList->whereIn($tableSupport . '.Id', explode(",", $reqSearch));
                        break;
                    case 'user':
                        if ($reqSearch == 'mySupport') {
                            $user = $req->session()->get('user');
                            $supportList->where(function ($supportList) use ($user) {
                                $supportList->where('ChargeUserId', $user->Id)
                                    ->orWhere('ChargeUserTwoId', $user->Id)
                                    ->orwhere('AsuserId', $user->Id);
                            });
                        }
                        break;
                    case 'searchInfo':
                        $supportList->where(function ($supportList) use ($reqSearch, $tableSupport) {
                            $supportList->Where('b.CusName', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.Title', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.Id', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.devIPAddr', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.Memo', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.EquipmentId', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.changeNO', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.problemNO', 'like', '%' . trim($reqSearch) . '%');

                        });
                        break;
                    case 'cusType':
                        $supportList->where(function ($supportList) use ($reqSearch, $tableSupport) {
                            $supportList->Where('b.CusImportanceType', $reqSearch)
                                ->orWhereNotNull($tableSupport . '.agentId');
                        });

                        break;
                    case 'replyTime':
                        switch ($reqSearch) {
                            case self::NO_REPLY:
                                $supportList->whereNull($tableSupport . '.FirstReplyTs');
                                break;
                            case self::REPLY_ELT_TWO_MIN:
                                $supportList->whereRaw("TIMESTAMPDIFF(SECOND, $tableSupport . ts, $tableSupport . FirstReplyTs) <= 120");
                                break;
                            case self::REPLY_TWO2FIVE_MIN:
                                $supportList->whereRaw("TIMESTAMPDIFF(SECOND, $tableSupport . ts, $tableSupport . FirstReplyTs) > " . (self::TWO_MIN * 60) . " AND TIMESTAMPDIFF(SECOND, $tableSupport . ts, $tableSupport . FirstReplyTs) <= " . (self::FIVE_MIN * 60));
                                break;
                            case self::REPLY_GT_FIVE_MIN:
                                $supportList->whereRaw("TIMESTAMPDIFF(SECOND, $tableSupport . ts, $tableSupport . FirstReplyTs) > " . (self::FIVE_MIN * 60));
                                break;
                        };
                        break;
                    case 'SuppOptGroup':
                        $supportList = $supportList->where("SuppOptGroup", $reqSearch);
                        break;
                    //判断未评价的
                    case 'Evaluation':
                        if ($reqSearch == 'notEvaluate') {
                            $supportList->whereNull("Evaluation");
                            $supportList = $supportList->where(function ($supportList) use ($tableSupport) {
                                $supportList->Where($tableSupport . '.ClassInficationOne', '!=', 'emailRequest');
                            });
                        } else {
                            $supportList->where($tableSupport . '.' . $search, $reqSearch);
                        }
                        break;
                    default:
                        $supportList->where($tableSupport . '.' . $search, $reqSearch);
                    //if($reqSearch == "Suspend")$supportList->orderByRaw("$tableSupport.ClassInficationOne = 'Equipment_personnel_2015' asc");
                }
            }
        }
        //排序
        $supportList->whereNotNull("$tableSupport.status");
        $supportList->orderByRaw("FIELD($tableSupport.status,'Todo','ReAppoint','Appointed','Doing','Suspend','Done','Closed'),$tableSupport.UpTs desc");
        $supportArray['total'] = $supportList->count();

        //分页
        $pageSize = !empty($req->input('pageSize')) ? $req->input('pageSize') : 20;
        $pageNumber = !empty($req->input('pageNumber')) ? $req->input('pageNumber') : 1;
        $supportList = $supportList->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();

        //公共转换
        $supportList = $this->translationBySupportInfo($supportList, $tableSupport);
        $supportArray['rows'] = $supportList;
        return $supportArray;
    }

    /**
     * 工单收藏原因blade
     * @param Request $req
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editReason(Request $req){
        $id = $req->get("sid");
        $json = TimedEvents::where("Name","工单收藏原因分类")->first();
        $arr = "";
        if(!empty($json)){
            $arr = explode(",",$json->Parameter);
        }else{
            $arr = [
                "长期资源>3天",
                "短期资源<3天",
                "其它"
            ];
        }
        if($id){
            return view('supports/editCollectReason',['sid'=>$id,'arr'=>$arr]);
        }
    }

    public function addCollection()
    {
        $supportId = Request()->get("supportId");
        if ($supportId) {
            $user = Request()->session()->get('user');
            $relation = SupportRelation::where("userId", $user->Id)->where("supportId", $supportId)->first();
            $notes = Request()->get("notes");
            $type = Request()->get("collectReason");
            if (!empty($relation)) {
                $relationUp['inValidate'] = 0;
                if ($notes) {
                    $relationUp['note'] = $notes;
                    $relationUp['type'] = $type;
                }
                if (SupportRelation::where("userId", $user->Id)->where("supportId",
                        $supportId)->update($relationUp) === false
                ) {
                    return ['status' => 'fail', 'msg' => '收藏失败!'];
                }
            } else {
                if (!SupportRelation::insert([
                    'supportId' => $supportId,
                    'userId'    => $user->Id,
                    'note'      => $notes,
                    'type'      => $type,
                    'ts'        => date("Y-m-d H:i:s", time())
                ])
                ) {
                    return ['status' => 'fail', 'msg' => '收藏失败!'];
                }
            }
        }
        return ['status' => 'successful', 'msg' => '收藏成功!'];
    }

    public function delCollection()
    {
        $supportId = Request()->get("supportId");
        if ($supportId) {
            $user = Request()->session()->get('user');
            $relation = SupportRelation::where("supportId", $supportId)->first();
            $userRole = $this->getUserRole();
            //如果不是服务台
            if ($userRole != self::ROLE_DESK_MANAGER) {
                $relation = $relation->where("user", $user->Id);
            }
            if (!empty($relation)) {
                $relationUp['inValidate'] = 1;
                $relationUp['inValidateAt'] = date('Y-m-d H:i:s', time());
                if ($notes = Request()->get("notes")) {
                    $relationUp['note'] = $notes;
                }
                if (SupportRelation::where("supportId",
                        $supportId)->update($relationUp) === false
                ) {
                    return ['status' => 'fail', 'msg' => '取消收藏失败!'];
                }
            }
        }
        return ['status' => 'successful', 'msg' => '取消收藏成功!'];
    }

    public function getCollectionNote($id){
        $note = SupportRelation::where("supportId",$id)->orderBy("Id","desc")->first();
        return !empty($note)?$note:"";
    }

    /**
     * 查看所有收藏
     * @param Request $req
     * @param Response $rep
     * @return mixed
     */
    public function getCollectionList(Request $req, Response $rep)
    {
        $tableSupport = self::$tableSupport;
        $supRel = "support_relation";

        $supportList = SupportRelation::select("$supRel.supportId","$supRel.userId as collectUserId",
            "$supRel.ts as collectTs", "$supRel.note",'c.*')
            ->leftJoin("$tableSupport as c", "support_relation.supportId", '=', 'c.Id')
            ->leftJoin('res.res_cusinf as b', 'c.CustomerInfoId', '=', 'b.Id')->where("support_relation.inValidate", 0);


        //过滤权限条件
        $supportList = $this->getRoleData($supportList, $tableSupport);
        //高级条件查询
        $searchList = [
            'SuppOptGroup',
            'priority',
            'cusType',
            'Status',
            'dataCenter',
            'replyTime',
            'Evaluation',
            'searchInfo',
            'user',
            'timeOutIds',
            'colType',
            'ismine'
        ];

        $user = $req->session()->get('user');

        //工单检索
        foreach ($searchList as $search) {
            $reqSearch = $req->input($search);
            if ($reqSearch != '') {
                switch ($search) {
                    case 'timeOutIds':
                        $supportList->whereIn('c.Id', explode(",", $reqSearch));
                        break;
                    case 'user':
                        if ($reqSearch == 'mySupport') {
                            $supportList->where(function ($supportList) use ($user) {
                                $supportList->where('ChargeUserId', $user->Id)
                                    ->orWhere('ChargeUserTwoId', $user->Id)
                                    ->orwhere('AsuserId', $user->Id);
                            });
                        }
                        break;
                    case 'searchInfo':
                        $supportList->where(function ($supportList) use ($reqSearch, $tableSupport) {
                            $supportList->Where('b.CusName', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere('c.Title', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere('c.Id', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere('c.devIPAddr', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere('c.Memo', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere('c.EquipmentId', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere('c.changeNO', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere('c.problemNO', 'like', '%' . trim($reqSearch) . '%');

                        });
                        break;
                    case 'cusType':
                        $supportList->where(function ($supportList) use ($reqSearch, $tableSupport) {
                            $supportList->Where('b.CusImportanceType', $reqSearch)
                                ->orWhereNotNull('c.agentId');
                        });

                        break;
                    case 'replyTime':
                        switch ($reqSearch) {
                            case self::NO_REPLY:
                                $supportList->whereNull('c.FirstReplyTs');
                                break;
                            case self::REPLY_ELT_TWO_MIN:
                                $supportList->whereRaw("TIMESTAMPDIFF(SECOND, c.ts, c.FirstReplyTs) <= " . self::TWO_MIN * 60);
                                break;
                            case self::REPLY_TWO2FIVE_MIN:
                                $supportList->whereRaw("TIMESTAMPDIFF(SECOND, c.ts, c.FirstReplyTs)  > " . (self::TWO_MIN * 60) . " AND TIMESTAMPDIFF(SECOND, c.ts, c.FirstReplyTs)  <= " . (self::FIVE_MIN * 60));
                                break;
                            case self::REPLY_GT_FIVE_MIN:
                                $supportList->whereRaw("TIMESTAMPDIFF(SECOND, c.ts, c.FirstReplyTs)  >  " . (self::FIVE_MIN * 60));
                                break;
                        };
                        break;
                    case 'SuppOptGroup':
                        $supportList = $supportList->where("SuppOptGroup", $reqSearch);
                        break;
                    //判断未评价的
                    case 'Evaluation':
                        if ($reqSearch == 'notEvaluate') {
                            $supportList->whereNull("Evaluation");
                        } else {
                            $supportList->where('c.' . $search, $reqSearch);
                        }
                        break;
                    case 'colType':
                        $supportList->where("$supRel.type", $reqSearch);
                        break;
                    case 'ismine':
                        $supportList->where("$supRel.userId", $user->Id);
                        break;
                    default:
                        $supportList->where('c.' . $search, $reqSearch);
                }
            }
        }
        //排序
        $supportList = $supportList->orderByRaw("c.status = 'Todo' desc,c.status = 'ReAppoint' desc,c.status = 'Appointed' desc,c.status = 'Doing' desc,c.status = 'Suspend' desc,c.status = 'Done' desc,c.status = 'Closed' desc,c.upts desc,c.ts desc");
        $supportArray['total'] = $supportList->count();

        //分页
        $pageSize = !empty($req->input('pageSize')) ? $req->input('pageSize') : 20;
        $pageNumber = !empty($req->input('pageNumber')) ? $req->input('pageNumber') : 1;
        $supportList = $supportList->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();

        $supportList = ResContact::translationStuff($supportList, 'ChargeUserId');
        $supportList = ResContact::translationStuff($supportList, 'ChargeUserTwoId');
        $supportList = Support::translationDict($supportList, 'ClassInficationOne', 'WorkSheetTypeOne');
        $supportList = Support::translationDict($supportList, 'Evaluation', 'WorksheetAppraisal');
        $supportList = ResContact::translationStuff($supportList, 'AsuserId');
//        //最后操作人L0/L1/DC处理
        $this->switchColor($supportList, 'OperationId');
        $supportList = Operation::getLastOperation($supportList);
        $supportList = ResContact::translationStuff($supportList, 'OperationId');
        $supportList = ResContact::translationStuff($supportList, 'userId');
        $supportList = ResContact::translationStuff($supportList, 'collectUserId');
        $supportList = Support::translationCusName($supportList, 'CustomerInfoId');
        $supportList = ThirdCallHelper::translationIdentity($supportList);
        $supportList = ThirdCallHelper::getAgentName($supportList);
        $supportList = Support::translationOverTime($supportList, $tableSupport);
        $supportList = ThirdCallHelper::translationPreDictTs($supportList);

        $supportArray['rows'] = $supportList;
        return $supportArray;
    }


    /**
     * 链接过来的条件过滤
     * @param $supportList
     * @param Request $req
     */
    public function searchByLink($supportList, Request $req)
    {
        $tableSupport = self::$tableSupport;
        $source = $req->input("source");

        //获取user
        $userId = Request()->session()->get('user')->Id;
        //获取工单类型
        $monthClassInficationOne = $req->input("monthClassInficationOne");
        //判断月份
        $month = $req->input("monthType");
        if ($month == "now") {
            $month = date("Y-m", time());
        }
        if ($month == "prev") {
            $month = date("Y-m", strtotime("-1 month"));
            $month = date("Y-m") == $month?date("Y-m", strtotime("-32 day")):$month;
        }

        //来源为staff页面饼图点击事件 我的待办工单
        if ($source == 'myNotDone') {
            if ($status = $req->input('monthStatus')) {
                $supportList = $supportList->where($tableSupport . '.Status', $status);
            }
            $supportList = $supportList->whereRaw($tableSupport . ".Status not IN('Done','Closed')")
                ->whereRaw("($tableSupport.ChargeUserId = $userId or $tableSupport.ChargeUserTwoId = $userId or $tableSupport.AsuserId =$userId)");
        }

        //来源为staff的月份统计
        if ($source == "month") {
            if ($monthClassInficationOne) {
                $supportList = $supportList->where($tableSupport . ".ClassInficationOne", $monthClassInficationOne);
            }
            $supportList = $supportList->whereRaw($tableSupport . ".Status IN('Done','Closed')")
                ->where(\DB::raw("DATE_FORMAT($tableSupport.Ts,'%Y-%m')"), $month)
                ->whereRaw("($tableSupport.ChargeUserId = $userId or $tableSupport.ChargeUserTwoId = $userId or $tableSupport.AsuserId =$userId)");
        }
        //来源为admin页面的工单类型统计
        if ($source == "admin") {
            if ($monthClassInficationOne) {
                if($monthClassInficationOne == "noEmailRequest"){
                    $supportList = $supportList->whereRaw("(ClassInficationOne != 'emailRequest' or ClassInficationOne IS NULL)");
                }else{
                    $supportList = $supportList->where($tableSupport . ".ClassInficationOne", $monthClassInficationOne);
                }
            }
            $sourceStatus = $req->input("sourceStatus");
            switch ($sourceStatus) {
                //处理中
                case "Doing":
                    $supportList = $supportList->where($tableSupport . ".Status", $sourceStatus);
                    break;
                //挂起
                case "Suspend":
                    $supportList = $supportList->where($tableSupport . ".Status", $sourceStatus);
                    break;
                //即将超时
                case "timeout":
                    $supportList = $supportList->where($tableSupport . ".Status", $sourceStatus);
                    break;
                case "evaluation":
                    $supportList = $supportList->whereRaw("($tableSupport.Evaluation ='Good' or $tableSupport.Evaluation ='Best')");
                    break;
                default:
                    //否则默认查已经处理的工单
                    $supportList = $supportList->whereRaw($tableSupport . ".Status IN('Done','Closed')");
                    break;
            }
            //当前工单类型不区分当月
            if ($month) {
                $supportList = $supportList->where(\DB::raw("DATE_FORMAT($tableSupport.Ts,'%Y-%m')"), $month);
            } else {
                $supportList = $supportList->leftJoin('res.aux_dict as c', "$tableSupport.ClassInficationOne", '=',
                    'c.Code')
                    ->where('c.Domain', '工单类型')
                    ->where('c.DomainCode', 'WorkSheetTypeOne')
                    ->whereRaw('(c.Validate is NULL or c.Validate = 0)');
            }
            //如果看某个人的工单处理情况
            $reqUserId = $req->input("userId");
            if ($reqUserId) {
                $reqUserIdArr = explode(",", $reqUserId);
                //如果有多个userID
                if (isset($reqUserIdArr[1])) {
                    $reqUserIdArr = trim($reqUserId, ",");
                    $supportList = $supportList->whereRaw("($tableSupport.ChargeUserId IN ($reqUserIdArr) or $tableSupport.ChargeUserTwoId IN ($reqUserIdArr) or $tableSupport.AsuserId IN ($reqUserIdArr))");

                } else {
                    $supportList = $supportList->whereRaw("($tableSupport.ChargeUserId = $reqUserId or $tableSupport.ChargeUserTwoId = $reqUserId or $tableSupport.AsuserId =$reqUserId)");
                }
            }
        }
        return $supportList;
    }

    /**
     * 批量关闭邮件工单
     * @param Request $req
     * @param Response $res
     * @return array
     */
    public function batchCloseMailSupport(Request $req, Response $res)
    {
        $user = $req->session()->get('user');
        $supIds = $req->input('supIds');

        if (count($supIds) > 0) {
            foreach ($supIds as $supId) {
                $support = Support::where('Id', $supId['Id'])->first();
                $support->UpTs = date('Y-m-d H:i:s');
                $support->EvTs = date('Y-m-d H:i:s');
                $support->OperationId = $user->Id;
                if ($support->Status != 'ToDo') {
                    $support->Status = 'Closed';
                }

                //添加快速应答回复
                $operationRet = Operation::insertGetId([
                    'reply'       => '已关闭',
                    'ReplyTs'     => date('Y-m-d H:i:s'),
                    'ReplyID'     => $supId['Id'],
                    'ReplyUserID' => $user->Id,
                    'SupportId'   => $supId['Id'],
                    'UCDis'       => 0
                ]);
                if ($operationRet < 1 || !$support->save()) {
                    return ['status' => 'failure'];
                }
            }
            return ['status' => 'success'];
        }
        return ['status' => 'failure'];
    }

    /**
     * 批量已处理工单
     * @param Request $req
     * @param Response $res
     * @return array
     */
    public function batchDoneSupport(Request $req, Response $res)
    {
        $user = $req->session()->get('user');
        $supIds = $req->input('supIds');

        if (count($supIds) > 0) {
            foreach ($supIds as $supId) {
                $support = Support::where('Id', $supId['Id'])->first();
                $support->UpTs = date('Y-m-d H:i:s');
                $support->OperationId = $user->Id;
                if ($support->Status != 'ToDo') {
                    $support->Status = 'Done';
                }

                //添加快速应答回复
                $operationRet = Operation::insertGetId([
                    'reply'       => '当前问题已解决',
                    'ReplyTs'     => date('Y-m-d H:i:s'),
                    'ReplyUserID' => $user->Id,
                    'ReplyID'     =>  $supId['Id'],
                    'SupportId'   =>  $supId['Id'],
                    'UCDis'       => Operation::$ucdis['pass']
                ]);
                if ($operationRet < 1 || !$support->save()) {
                    return ['status' => 'failure'];
                }
            }
            return ['status' => 'success'];
        }
        return ['status' => 'failure'];
    }


    public function batchReplySupport(Request $req, Response $res)
    {
        $user = $req->session()->get('user');
        $supIds = $req->input('supIds');
        $msg = $req->input('replyData');
        if (get_magic_quotes_gpc()) {
            $msg = stripslashes($msg);
        }

        if (count($supIds) > 0) {
            foreach ($supIds as $supId) {
                $support = Support::where('Id', $supId['Id'])->first();
                $support->UpTs = date('Y-m-d H:i:s');
                $support->OperationId = $user->Id;
                if ($support->Status == 'Appointed') {
                    $support->Status = 'Doing';
                }
                if($support->Status != 'Done' &&$support->Status != 'Closed'){
                    //添加快速应答回复
                    $operationRet = Operation::insertGetId([
                        'reply'       => $msg,
                        'ReplyTs'     => date('Y-m-d H:i:s'),
                        'ReplyUserID' => $user->Id,
                        'SupportId'   => $supId['Id'],
                        'UCDis'       => Operation::$ucdis['pass'],
                        'Source'       => 'ITSM'
                    ]);
                    if ($operationRet < 1 || !$support->save()) {
                        return ['status' => 'failure'];
                    }else{
                        if($req->input('isEmail') == "yes"){
                            $title = "您的工单：'".$support->Title."'有新的回复";
                            if($support->CreateUserId>=500000){
                                $user = Userlogin::Select('LoginId')->where('Id', $support->CreateUserId)->first();
                                if(isset($user)&& $email = $user->LoginId){
                                    $job = new SendEmail($email,$title, $msg); //创建队列任务
                                    $this->dispatch($job); //添加到队列
                                }
                            }
                        }
                    }
                }
            }
            return ['status' => 'success'];
        }
        return ['status' => 'failure'];
    }

    /**
     * 批量指派 应答工单
     * @param Request $req
     * @param Response $res
     * @return array
     */
    public function batchAnswerMailSupport(Request $req, Response $res)
    {
        $user = $req->session()->get('user');
        $supIds = $req->input("supIds");

        if (count($supIds) > 0) {
            $username = AuxStuff::where('Id', $user->Id)->first();
            $username = isset($username->Name) ? $username->Name : '';
            foreach ($supIds as $supId) {
                $support = Support::where('Id', $supId['Id'])->first();
                $support->DatacenterId = 0;
                $support->ChargeUserId = 581; //指定人员
                $support->dose = 1;
                $support->SpTs = date('Y-m-d H:i:s');
                $support->UpTs = date('Y-m-d H:i:s');
                $support->AsuserId = $user->Id;
                $support->OperationId = $user->Id;
                $support->Status = 'Doing';
                if (!$support->FirstReplyTs) {
                    $support->FirstReplyTs = date('Y-m-d H:i:s');
                }

                //添加快速应答回复
                $operationRet1 = Operation::insertGetId([
                    'reply'       => ' 您提交的请求我们已受理，我们会立即帮您查看您的问题。',
                    'ReplyTs'     => date('Y-m-d H:i:s'),
                    'ReplyUserID' => $user->Id,
                    'SupportId'   => $supId['Id'],
                    'UCDis'       => 1
                ]);
                $operationRet2 = Operation::insertGetId([
                    'reply'        => '已指派',
                    'ReplyTs'      => date('Y-m-d H:i:s'),
                    'ReplyID'      => $supId['Id'],
                    'ReplyUserID'  => $user->Id,
                    'SupportId'    => $supId['Id'],
                    'UCDis'        => 0,
                    'OperationId'  => 581, //指定人员
                    'DatacenterId' => 0
                ]);
                if ($operationRet1 < 1 || $operationRet2 < 1 || !$support->save()) {
                    return ['status' => 'failure'];
                }
            }
            return ['status' => 'success'];
        }
        return ['status' => 'failure'];
    }

    /**
     * 获取待处理的工单
     * @param Request $req
     * @param Response $res
     * @return int
     */
    public function getTodoCount(Request $req, Response $res)
    {
        $TodoHandler = Support::select('Id')->Where('Status', 'Todo');
        $tableSupport = self::$tableSupport;

        $TodoHandler = $this->getRoleData($TodoHandler, $tableSupport);

        $TodoCount = $TodoHandler->get()->count();
        return $TodoCount;
    }

    /**
     * 快速应答工单
     * @param Request $req
     * @param Response $res
     * @return array
     */
    public function speedAnswer(Request $req, Response $res)
    {
        $user = $req->session()->get('user');;//获取当前用户ID

        if ($answerId = $req->input('supId')) {//待处理工单快速应答
            $support = UserSupport::where('Id', $answerId)->first();
            if ("Todo" != $support->Status) {//状态为待处理才能快速应答
                return ['status' => 'failure'];
            }
            $support->UpTs = date('Y-m-d H:i:s');
            $support->OperationId = $user->Id;
            $support->Status = 'ReAppoint';

            if (!$support->FirstReplyTs) {
                $support->FirstReplyTs = date('Y-m-d H:i:s');
            }
            $username = AuxStuff::where('Id', $user->Id)->first();
            $username = isset($username->Name) ? $username->Name : '';

            //添加快速应答回复
            $operationRet = Operation::insertGetId([
                'reply'       => ' 您提交的请求我们已受理，我们会立即帮您查看您的问题。',
                'ReplyTs'     => date('Y-m-d H:i:s'),
                'ReplyUserID' => $user->Id,
                'SupportId'   => $answerId,
                'UCDis'       => 1
            ]);

            if ($operationRet < 1 || !$support->save()) {
                return ['status' => 'unsuccess'];
            } else {
                //发送推送消息 调用API3.6接口
                $supportId = $support->Id;
                $operationId = $operationRet;
                $userId = $user->Id;
                $status = 'ReAppoint';
                $job = new SpeedAnswer($supportId, $operationId, $userId, $status);
                $this->dispatch($job);
            }
            return ['status' => 'success'];
        }
    }

    /**
     * 获取客户+客户经理及其商业/技术联系人信息
     * @param $CusInfId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function cusContactList($CusInfId)
    {
        $cusContactList = ThirdCallHelper::getCusDictInf($CusInfId);
        return view('supports/CusContactList', [
            'cusList' => $cusContactList
        ]);
    }

    /**
     * 获取超时工单统计数据及Id
     * @return array
     */
    public function getOverTimeNum(Request $req, Response $res)
    {
        $overTimeArray = [
            "oneHour" => [//超时1小时以内
                'count' => 0,
                'ids'   => []
            ],
            "twoHour" => [//超时1小时以上，2小时以内
                'count' => 0,
                'ids'   => []
            ],
            "overTwo" => [//超时2小时以上
                'count' => 0,
                'ids'   => []
            ],
            "allIds"  => []
        ];
        $tableSupport = self::$tableSupport;

        $supports = Support::select($tableSupport . '.Id', $tableSupport . '.ProcessTs', $tableSupport . '.Ts',
            $tableSupport . '.hangupDuration', 'b.ENG')
            ->leftJoin('res.aux_dict as b', $tableSupport . '.ClassInficationOne', '=', 'b.Code')
            ->where('b.DomainCode', 'WorkSheetTypeOne')
            //统计超时工单排除4种状态
            ->whereNotIn($tableSupport . '.Status', ['Done', 'Closed', 'Todo', 'ReAppoint'])
            ->where('b.ENG', '!=', 'N/A')
            ->whereRaw('LENGTH(b.ENG)>0');
        if ($req->input("myself")) {
            $user = Request()->session()->get('user');
            $supports = $supports->where(function ($supportList) use ($tableSupport, $user) {
                $supportList->where($tableSupport . '.ChargeUserId', $user->Id)
                    ->orWhere($tableSupport . '.ChargeUserTwoId', $user->Id)
                    ->orWhere($tableSupport . '.AsuserId', $user->Id);
            });
            $supports = $supports->get();
        } else {
            $supports = $this->getRoleData($supports, $tableSupport)->get();
        }
        foreach ($supports as $support) {

            //这里计算时长单位用秒
            $processTs = $support->ProcessTs ? strtotime($support->ProcessTs) : time();//已处理时间，若没有取当前
            $ts = $support->Ts ? strtotime($support->Ts) : time();//工单创建时间
            $hangupDuration = $support->hangupDuration ? $support->hangupDuration : 0;//挂起时长

            $referTime = $support->ENG ? 60 * $support->ENG : 0;//参考时长
            $overTime = $processTs - $hangupDuration - $referTime - $ts;//计算出的超时时长

            //表示超时1小时以内
            if ($overTime > 0 && $overTime <= 3600) {
                $overTimeArray['oneHour']['count'] += 1;
                $overTimeArray['oneHour']['ids'][] = $support->Id;

                //表示超时1小时以内2小时以内
            } elseif ($overTime > 3600 && $overTime <= 7200) {
                $overTimeArray['twoHour']['count'] += 1;
                $overTimeArray['twoHour']['ids'][] = $support->Id;

                //表示超时2小时以内
            } elseif ($overTime > 7200) {
                $overTimeArray['overTwo']['count'] += 1;
                $overTimeArray['overTwo']['ids'][] = $support->Id;
            }
            //汇总所有数据,做超时对比用
            if ($overTime > 0) {
                $overTimeArray['allIds'][] = $support->Id;
            }
        }
        return $overTimeArray;
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
            //服务台管理员
            case self::ROLE_DC_EMPLOYEE;
                $stuffDc = AuxStuffDatacenter::select('a.DataCenterName')
                    ->leftJoin("res_datacenter as a","a.Id","=","aux_stuff_datacenter.DataCenterId")
                    ->where('aux_stuff_datacenter.StuffId', $user->Id)->get();
                $stuffDataCenterGroupId = ResUserGroup::select('UsersId')->where('UserId', $user->Id)->get();
                $supportList = $supportList->where(function ($supportList) use (
                    $tableSupport,
                    $stuffDataCenterGroupId,
                    $stuffDc
                ) {
                    $supportList->whereIn($tableSupport . '.DatacenterId', $stuffDataCenterGroupId)
                        ->orwhereIn($tableSupport . '.DatacenterTwoId', $stuffDataCenterGroupId)
                        ->orwhereIn($tableSupport . '.dataCenter', $stuffDc);
                });
                break;
            //其他人员
            case self::OTHER;
                $supportList = $supportList->where(function ($supportList) use ($tableSupport, $user) {
                    $supportList->where($tableSupport . '.ChargeUserId', $user->Id)
                        ->orWhere($tableSupport . '.ChargeUserTwoId', $user->Id)
                        ->orwhere('AsuserId', $user->Id)
                        ->orwhere('b.Sell', $user->Id);
                    if($this->hasUserRule("sales_manager")){
                        $stuff = AuxStuff::where("Id",176)->first();
                        if(!empty($stuff) && $stuff->second_dept){
                            $sells = AuxStuff::select("Id")->where("second_dept",$stuff->second_dept)->get();
                            $sellIds = "";
                            foreach ($sells as $sell) {
                                $sellIds .= $sell->Id.",";
                            }
                            $sellIds = trim($sellIds,",");
                            $supportList->orWhereRaw("b.Sell in ($sellIds)");
                        }
                    }
                });
                break;
        }
        return $supportList;
    }

    /**最后操作人颜色预处理
     * @param $supportList 操作列表
     * @param $code 组
     */
    protected function switchColor($supportList, $keyCode)
    {
        foreach ($supportList as &$opt) {
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
     * 导出数据
     * @param Request $req
     * @param Response $rep
     * @param Excel $excel
     * @return mixed
     */
    public function exportAllList(Request $req, Response $rep, Excel $excel)
    {
        $tableSupport = self::$tableSupport;

        $supportList = Support::select($tableSupport . '.*')
            ->leftJoin('res.res_cusinf as b', $tableSupport . '.CustomerInfoId', '=', 'b.Id');


        //过滤权限条件
        $supportList = $this->getRoleData($supportList, $tableSupport);

        //高级条件查询
        $searchList = [
            'SuppOptGroup',
            'priority',
            'cusType',
            'Status',
            'dataCenter',
            'replyTime',
            'Evaluation',
            'searchInfo',
            'user',
            'timeOutIds'
        ];
        foreach ($searchList as $search) {//工单检索
            $reqSearch = $req->input($search);
            if ($reqSearch != '') {
                switch ($search) {
                    case 'timeOutIds':
                        $supportList->whereIn($tableSupport . '.Id', explode(",", $reqSearch));
                        break;
                    case 'user':
                        if ($reqSearch == 'mySupport') {
                            $user = $req->session()->get('user');
                            $supportList->where(function ($supportList) use ($user) {
                                $supportList->where('ChargeUserId', $user->Id)
                                    ->orWhere('ChargeUserTwoId', $user->Id)
                                    ->orwhere('AsuserId', $user->Id);
                            });
                        }
                        break;
                    case 'searchInfo':
                        $supportList->where(function ($supportList) use ($reqSearch, $tableSupport) {
                            $supportList->Where('b.CusName', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.Title', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.Id', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.devIPAddr', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.Memo', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.EquipmentId', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.changeNO', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.problemNO', 'like', '%' . trim($reqSearch) . '%');

                        });
                        break;
                    case 'cusType':
                        $supportList->where(function ($supportList) use ($reqSearch, $tableSupport) {
                            $supportList->Where('b.CusImportanceType', $reqSearch)
                                ->orWhereNotNull($tableSupport . '.agentId');
                        });

                        break;
                    case 'replyTime':
                        switch ($reqSearch) {
                            case self::NO_REPLY:
                                $supportList->WhereNull($tableSupport . '.FirstReplyTs');
                                break;
                            case self::REPLY_ELT_TWO_MIN:
                                $supportList->whereRaw("TIMESTAMPDIFF(SECOND, $tableSupport . ts, $tableSupport . FirstReplyTs) <= " . self::TWO_MIN * 60);
                                break;
                            case self::REPLY_TWO2FIVE_MIN:
                                $supportList->whereRaw("TIMESTAMPDIFF(SECOND, $tableSupport . ts, $tableSupport . FirstReplyTs) > " . (self::TWO_MIN * 60) . " AND TIMESTAMPDIFF(SECOND, $tableSupport . ts, $tableSupport . FirstReplyTs) <= " . (self::FIVE_MIN * 60));
                                break;
                            case self::REPLY_GT_FIVE_MIN:
                                $supportList->whereRaw("TIMESTAMPDIFF(SECOND, $tableSupport . ts, $tableSupport . FirstReplyTs) >  " . (self::FIVE_MIN * 60));
                                break;
                        };
                        break;
                    case 'SuppOptGroup':
                        $supportList = $supportList->where("SuppOptGroup", $reqSearch);
                        break;
                    //判断未评价的
                    case 'Evaluation':
                        if ($reqSearch == 'notEvaluate') {
                            $supportList->whereNull("Evaluation");
                        } else {
                            $supportList->where($tableSupport . '.' . $search, $reqSearch);
                        }
                        break;
                    default:
                        $supportList->where($tableSupport . '.' . $search, $reqSearch);
                }
            }
        }
        //排序
        $supportList = $supportList->orderByRaw("$tableSupport.status = 'Todo' desc,$tableSupport.status = 'ReAppoint' desc,$tableSupport.status = 'Appointed' desc,$tableSupport.status = 'Doing' desc,$tableSupport.status = 'Suspend' desc,$tableSupport.status = 'Done' desc,$tableSupport.status = 'Closed' desc,$tableSupport.upts desc,$tableSupport.ts desc");

//        $pageSize = !empty($req->input('pageSize')) ? $req->input('pageSize') : 4400;
//        $pageNumber = !empty($req->input('pageNumber')) ? $req->input('pageNumber') : 1;
//        $supportList = $supportList->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();

        $supportList = $supportList->get();


        $supportList = ResContact::translationStuff($supportList, 'ChargeUserId');
        $supportList = ResContact::translationStuff($supportList, 'ChargeUserTwoId');
        $supportList = Support::translationDict($supportList, 'ClassInficationOne', 'WorkSheetTypeOne');
        $supportList = Support::translationDict($supportList, 'Evaluation', 'WorksheetAppraisal');
        $supportList = ResContact::translationStuff($supportList, 'AsuserId');
        //最后操作人L0/L1/DC处理
        $this->switchColor($supportList, 'OperationId');
        $supportList = ResContact::translationStuff($supportList, 'OperationId');
        $supportList = Support::translationCusName($supportList, 'CustomerInfoId');
        $supportList = ThirdCallHelper::translationIdentity($supportList);
        $supportList = ThirdCallHelper::getAgentName($supportList);
        $supportList = Support::translationOverTime($supportList, $tableSupport);
        $supportList = ThirdCallHelper::translationPreDictTs($supportList);
        set_time_limit(0);
        ini_set('memory_limit', '256M');

        $filename = '工单筛选列表——' . date('Ymd', time());
        $export_data = [];

        foreach ($supportList as $key => $v) {
            $export_data[] = [
                "工单编号\n数据中心\n负责人"   => $v['Id'] . "\n" . $v['dataCenter'] . "\n" . $v['ChargeUserTwoId'],
                "工单标题"              => $v['Title'],
                "工单状态\n工单分类\n指派人"   => $v['Status'] . "\n" . $v['ClassInficationOne'] . "\n" . $v['AsuserId'],
                "客户名称"              => $v['CusName'],
                "IP地址\n关联设备"        => $v['devIPAddr'] . "\n" . $v['EquipmentId'],
                "创建时间\n更新时间\n最后更新人" => $v['Ts'] . "\n" . $v['UpTs'] . "\n" . strip_tags($v['OperationId']),
                "服务评价\n跟踪人"         => $v['Evaluation'] . "\n" . $v['AsuserId'],
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

    /**
     * 清除全部cache
     */
    public function cleanCache()
    {
//        ITSM-AuxDict-$DoMainCode
//        ITSM-aux-dict-$item['Id']
//        ITSM-AgentName-$item['Id']
//        CUST-$cusId
//        ITSM-AuxStuff

        $cacheArr = [
            "ITSM-AuxDict",
            "ITSM-aux-dict",
            "ITSM-AgentName",
            "ITSM-AuxStuff"
        ];
        foreach ($cacheArr as $item) {
            Cache::tags($item)->flush();
        }
        echo "clean successful";
    }

    //清除人员列表缓存
    public function cleanMemCache()
    {
        $cacheArr = [
            "ITSM-AuxDict",
            "ITSM-AuxStuff"
        ];
        foreach ($cacheArr as $item) {
            Cache::forget($item);
        }
        return array('status' => "ok");
    }

    /**
     * 弹出将被关联的变更列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function relateChange(Request $request)
    {
        $data = $request->all();
        $supportId = $data["supportId"];
        return view('supports/relatechange', compact("supportId"));
    }

    /*
    * 获取所有变更列表
    */
    public function getRelateChange()
    {
        $changeLists = Change::select('*')->orderBy("Id", "desc");
        if ($keyword = Input::get('searchInfo')) {
            $changeLists = $changeLists->where(function ($changeLists) use ($keyword) {
                $changeLists->Where('RFCNO', 'like', '%' . trim($keyword) . '%')
                    ->orWhere('changeTitle', 'like', '%' . trim($keyword) . '%');
            });
        }
        if ($supportId = Input::get('supportId')) {
            $sql = "select changeId from `Correlation` where `supportId` = $supportId and `changeId` <> 0 and `inValidate` = 0 order by `Id` desc";
            $changeLists = $changeLists->whereRaw("(Id not in($sql))");
        }
        $total = $changeLists->count();
        $changeList['total'] = $total;
        $pageSize = !empty(Input::get('pageSize')) ? Input::get('pageSize') : 5;
        $pageNumber = !empty(Input::get('pageNumber')) ? Input::get('pageNumber') : 1;
        $changeLists = $changeLists->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();
        $changeLists = Support::translationDict($changeLists, 'changeCategory', 'changeCategory');
        $changeList['rows'] = $changeLists;
        return $changeList;
    }

    //获取已经关联的变更列表
    public function relateChangeData(Request $req)
    {
        $sid = $req->input("supportId");
        $relateChange = Correlation::where("supportId", $sid)->where('changeId', '<>', 0)->where('inValidate',
            0)->orderBy("Id", "desc")->get()->toArray();
        //定义返回结果格式
        $arr = [
            'rows'  => [],
            'total' => 0
        ];
        if (!empty($relateChange)) {
            foreach ($relateChange as &$changeRecord) {
                $arr['rows'][] = Change::where("Id", $changeRecord['changeId'])->orderBy("Id", "desc")->first();
            }
            $arr['rows'] = Support::translationDict($arr['rows'], 'changeCategory', 'changeCategory');
            $arr['rows'] = Support::translationDict($arr['rows'], 'changeCondition', 'changeCondition');
            $arr['rows'] = Change::translationStuff($arr['rows'], 'applyUserId');
            $arr['total'] = count($arr['rows']);
        }
        return $arr;
    }

    //获取已经关联的问题列表
    public function relateIssueData(Request $req)
    {
        $sid = $req->input("supportId");
        $relateIssue = Correlation::where("supportId", $sid)->where('issueId', '<>', 0)->where('inValidate',
            0)->orderBy("Id", "desc")->get()->toArray();
        //定义返回结果格式
        $arr = [
            'rows'  => [],
            'total' => 0
        ];
        if (!empty($relateIssue)) {
            foreach ($relateIssue as &$issueRecord) {
                $arr['rows'][] = Issue::where("Id", $issueRecord['issueId'])->orderBy("Id", "desc")->first();
            }
            $arr['rows'] = Support::translationDict($arr['rows'], 'issueCategory', 'WorksheetTypeOne');
            $arr['rows'] = Support::translationDict($arr['rows'], 'issuePriority', 'issuePriority');
            $arr['rows'] = Change::translationStuff($arr['rows'], 'issueSubmitUserId');
            $arr['total'] = count($arr['rows']);
        }
        return $arr;
    }

    /*
    * 获取所有问题列表
    */
    public function getRelateIssue()
    {
        $issueLists = Issue::select('*')->orderBy("Id", "desc");
        if ($keyword = Input::get('searchInfo')) {
            $issueLists = $issueLists->where(function ($issueLists) use ($keyword) {
                $issueLists->Where('issueNo', 'like', '%' . trim($keyword) . '%')
                    ->orWhere('issueTitle', 'like', '%' . trim($keyword) . '%');
            });
        }
        if ($supportId = Input::get('supportId')) {
            $sql = "select issueId from `Correlation` where `supportId` = $supportId and `issueId` <> 0 and `inValidate` = 0 order by `Id` desc";
            $issueLists = $issueLists->whereRaw("(Id not in($sql))");
        }
        $total = $issueLists->count();
        $issueList['total'] = $total;
        $pageSize = !empty(Input::get('pageSize')) ? Input::get('pageSize') : 5;
        $pageNumber = !empty(Input::get('pageNumber')) ? Input::get('pageNumber') : 1;
        $issueLists = $issueLists->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();
        $issueLists = Support::translationDict($issueLists, 'issueCategory', 'WorksheetTypeOne');
        $issueLists = Support::translationDict($issueLists, 'issuePriority', 'issuePriority');
        $issueList['rows'] = $issueLists;
        return $issueList;
    }

    /**
     * 弹出将被关联的问题列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function relateIssue(Request $request)
    {
        $data = $request->all();
        $supportId = $data["supportId"];
        return view('supports/relateissue', compact("supportId"));
    }

    public function updateGrade(Request $request)
    {
        try {
            $all = $request->all();
            $now = date("Y-m-d H:i:s");
            $id = intval($all['supId']);
            $user = PublicMethodsHelper::getUser();
            $userid = $user->Id;
            $upGrade = $all['type'] == 'up' ? 1 : 0;
            $reply = $all['type'] == 'up' ? '已升级' : '取消升级';
            $update = [
                'upGrade' => $upGrade
            ];
            $save = Support::where('Id', $id)->update($update);
            $optData = [
                'reply'       => $reply,
                'ReplyTs'     => $now,
                'ReplyID'     => $id,
                'ReplyUserID' => $userid,
                'SupportId'   => $id,
                'UCDis'       => Operation::$ucdis['pending']
            ]; //操作记录
            $optRst = Operation::insertGetId($optData); //添加操作记录
            if ($optRst != false) {
                return array('status' => true, 'msg' => '操作成功！');
            } else {
                return array('status' => false, 'msg' => '操作失败！请重新尝试');
            }
        } catch (\Exception $ex) {
            return array('status' => false, 'msg' => $ex->getMessage());
        }

    }

    /**
     * 查询统计工单
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function statisticList()
    {
        $chargeGroupList = AuxDict::where('DomainCode', 'chargeGroup')->where('Domain', '负责人群组')->get();
        $statusList = PublicMethodsHelper::getSupportStatusList();
        $customerTypeList = PublicMethodsHelper::getCustomerList();
        $responseTimeList = PublicMethodsHelper::getResponseTimeList();
        $dataCenterList = ThirdCallHelper::getDataCenter();
        $evaluationList = AuxDict::where('DomainCode', 'WorksheetAppraisal')->where('Domain', '技术支持客户满意度评价')->get();
        $priorityList = [1, 2, 3];
        $classifyTwo = AuxDict::select('Code', 'Means')->where('Domain', '工单事件分类')->orderby('Id')->get();
        $classifyThree = ThirdCallHelper::getDictArray('工单类型', 'WorksheetTypeOne');
        $supSourceList = ThirdCallHelper::getDictArray('工单来源', 'supportSource');
        $secondDeptList = ThirdCallHelper::getDictArray('二级部门', 'second_dept');
        $chargeUserList = PublicMethodsHelper::chargeUser();
        $AsUserList = PublicMethodsHelper::asUser();
        if ($sources = Input::get('sources')) {
            $sources = Input::get('sources') ? $sources : '';
        }
        return view('supports/statisticlist', [
            'chargeList'       => $chargeGroupList,
            'statusList'       => $statusList,
            'dataCenterList'   => $dataCenterList,
            'evaluationList'   => $evaluationList,
            'priorityList'     => $priorityList,
            'classifyTwo'      => $classifyTwo,
            'classifyThree'    => $classifyThree,
            'responseTimeList' => $responseTimeList,
            'customerTypeList' => $customerTypeList,
            'chargeUserList'   => $chargeUserList,
            'AsUserList'       => $AsUserList,
            'supSourceList'    => $supSourceList,
            'secondDeptList'   => $secondDeptList,
            'sources'          => $sources,
        ]);
    }

    /**
     * 获取超时工单统计Id
     * @return array
     */
    public function getOverTimeIds()
    {
        $allIds = [];
        $tableSupport = self::$tableSupport;

        $supports = Support::select($tableSupport . '.Id', $tableSupport . '.ProcessTs', $tableSupport . '.Ts',
            $tableSupport . '.hangupDuration', 'b.ENG')
            ->leftJoin('res.aux_dict as b', $tableSupport . '.ClassInficationOne', '=', 'b.Code')
            ->where('b.DomainCode', 'WorkSheetTypeOne')
            //统计超时工单排除4种状态
            ->whereNotIn($tableSupport . '.Status', ['Done', 'Closed', 'Todo', 'ReAppoint'])
            ->where('b.ENG', '!=', 'N/A')
            ->whereRaw('LENGTH(b.ENG)>0')->get();
        foreach ($supports as $support) {

            //这里计算时长单位用秒
            $processTs = $support->ProcessTs ? strtotime($support->ProcessTs) : time();//已处理时间，若没有取当前
            $ts = $support->Ts ? strtotime($support->Ts) : time();//工单创建时间
            $hangupDuration = $support->hangupDuration ? $support->hangupDuration : 0;//挂起时长

            $referTime = $support->ENG ? 60 * $support->ENG : 0;//参考时长
            $overTime = $processTs - $hangupDuration - $referTime - $ts;//计算出的超时时长

            //汇总所有数据,做超时对比用
            if ($overTime > 0) {
                $allIds[] = $support->Id;
            }
        }
        return $allIds;
    }

    /**
     * 获取/筛选统计工单页面数据
     * @param Request $req
     * @param Response $rep
     * @return mixed
     */
    public function getStatisticList(Request $req, Response $rep)
    {
        $tableSupport = self::$tableSupport;

        $staList = Support::select("$tableSupport.*")
            ->join('res.res_cusinf as b', $tableSupport . '.CustomerInfoId', '=', 'b.Id')
            ->leftJoin("res.aux_stuff as a", $tableSupport . '.ChargeUserId', '=', 'a.Id');

        //判断是否是点击过来的
        if ($source = $req->input("source")) {
            $staList = $this->searchByLink($staList, $req);
        } else {
            //过滤权限条件
            $staList = $this->getRoleData($staList, $tableSupport);
        }

        if (($handle = $req->input("handleTime")) == "overTimeIds") {//lidz 17.3.17
            $staList = $staList->whereRaw("TIMESTAMPDIFF(SECOND, $tableSupport.ts, if($tableSupport.processTs
            is null,CURRENT_TIMESTAMP(),$tableSupport.processTs))>(IFNULL($tableSupport.initHandleduration,
            3600)+IFNULL($tableSupport.hangupDuration,0))");
        }
        if ($handle = $req->input("handleTime") == "onTimeIds") {
            $staList = $staList->whereRaw("TIMESTAMPDIFF(SECOND, $tableSupport.ts, if($tableSupport.processTs is
             null,CURRENT_TIMESTAMP(),$tableSupport.processTs))<=(IFNULL($tableSupport.initHandleduration,
             3600)+IFNULL($tableSupport.hangupDuration,0)) ");
        }
        //根据时间窗口或者实际完成时间筛选
        $startTime = Input::get('startTime') ? Input::get('startTime') : date('Y-m-d H:i:s',
            time());
        $endTime = Input::get('endTime') ? Input::get('endTime') : date('Y-m-d H:i:s', time());
        $faStartTime = Input::get('faStartTime') ? Input::get('faStartTime') : date('Y-m-d H:i:s',
            time());
        $faEndTime = Input::get('faEndTime') ? Input::get('faEndTime') : date('Y-m-d H:i:s', time());
        if (!empty(Input::get('startTime')) || !empty(Input::get('endTime'))) {
            $staList = $staList->whereBetween($tableSupport . ".Ts", [$startTime, $endTime]);
        }
        if (!empty(Input::get('faStartTime')) || !empty(Input::get('faEndTime'))) {
            $staList = $staList->whereBetween($tableSupport . ".Ts", [$faStartTime, $faEndTime]);
        }
        //高级条件查询
        $searchList = [
            'SuppOptGroup',
            'priority',
            'cusType',
            'Status',
            'dataCenter',
            'replyTime',
            'Evaluation',
            'searchInfo',
            'user',
            'timeOutIds',
            'classifyOne',
            'classifyTwo',
            'classifyThree',
            'ChargeUserId',
            'AsUserId',
            'supportSource',
            'secondDeptCode'
        ];

        if($req->input("noEmail") && $req->input("noEmail") == "no"){//是否需要查看邮件告警类型
            $staList->where($tableSupport . '.ClassInficationOne',"!=","emailRequest");
        }

        foreach ($searchList as $search) {//工单检索
            $reqSearch = $req->input($search);
            if ($reqSearch != '') {
                switch ($search) {
                    case 'timeOutIds':
                        $staList->whereIn($tableSupport . '.Id', explode(",", $reqSearch));
                        break;
                    case 'user':
                        if ($reqSearch == 'mySupport') {
                            $user = $req->session()->get('user');
                            $staList->where(function ($staList) use ($user) {
                                $staList->where('ChargeUserId', $user->Id)
                                    ->orWhere('ChargeUserTwoId', $user->Id)
                                    ->orwhere('AsuserId', $user->Id);
                            });
                        }
                        break;
                    case 'classifyOne':
                        $staList->where(function ($staList) use ($reqSearch, $tableSupport) {
                            $staList->where($tableSupport . '.ServiceModel', $reqSearch);
                        });
                        break;
                    case 'classifyTwo':
                        $sclass = AuxDict::select("Code","Eng")
                            ->whereRaw("ParentCode='$reqSearch' and DomainCode='WorksheetTypeOne' and (Validate is null or Validate = 0)")
                            ->get();
                        $arr = [];
                        if($sclass && !empty($sclass)){
                            foreach($sclass as $v){
                                if($v->Code){
                                    $arr[] = $v->Code;
                                }
                            }
                        }
                        $staList->whereIn($tableSupport . '.ClassInficationOne', $arr);
                        break;
                    case 'classifyThree':
                        $staList->where(function ($staList) use ($reqSearch, $tableSupport) {
                            $staList->where($tableSupport . '.ClassInficationOne', $reqSearch);
                        });
                        break;
                    case 'searchInfo':
                        $staList->where(function ($staList) use ($reqSearch, $tableSupport) {
                            $staList->Where('b.CusName', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.Title', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.Id', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.devIPAddr', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.Memo', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.EquipmentId', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.changeNO', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.problemNO', 'like', '%' . trim($reqSearch) . '%');

                        });
                        break;
                    case 'cusType':
                        $staList->where(function ($staList) use ($reqSearch, $tableSupport) {
                            $staList->Where('b.CusImportanceType', $reqSearch)
                                ->orWhereNotNull($tableSupport . '.agentId');
                        });

                        break;
                    case 'replyTime':
                        switch ($reqSearch) {
                            case self::NO_REPLY:
                                $staList->WhereNull($tableSupport . '.FirstReplyTs');
                                break;
                            case self::REPLY_ELT_TWO_MIN:
                                $staList->whereRaw("TIMESTAMPDIFF(SECOND, $tableSupport . ts, $tableSupport . FirstReplyTs) <= " . self::TWO_MIN * 60);
                                break;
                            case self::REPLY_TWO2FIVE_MIN:
                                $staList->whereRaw("TIMESTAMPDIFF(SECOND, $tableSupport . ts, $tableSupport . FirstReplyTs) > " . (self::TWO_MIN * 60) . " AND TIMESTAMPDIFF(SECOND, $tableSupport . ts, $tableSupport . FirstReplyTs) <= " . (self::FIVE_MIN * 60));
                                break;
                            case self::REPLY_GT_FIVE_MIN:
                                $staList->whereRaw("TIMESTAMPDIFF(SECOND, $tableSupport . ts, $tableSupport . FirstReplyTs) >  " . (self::FIVE_MIN * 60));
                                break;
                        };
                        break;
                    case 'SuppOptGroup':
                        $staList = $staList->where("SuppOptGroup", $reqSearch);
                        break;
                    case 'supportSource':
                        $staList = $staList->where("Source", $reqSearch);
                        break;
                    //判断未评价的
                    case 'Evaluation':
                        if ($reqSearch == 'notEvaluate') {
                            $staList->whereNull("Evaluation");
                            $staList = $staList->where(function ($staList) use ($tableSupport) {
                                $staList->Where($tableSupport . '.ClassInficationOne', '!=', 'emailRequest');
                            });
                        } else {
                            $staList->where($tableSupport . '.' . $search, $reqSearch);
                        }
                        break;
                    case 'secondDeptCode':
                        $arr = explode(",", $reqSearch);
                        $staList->whereIn("a.second_dept", $arr);
                        break;
                    case 'priority':
                        if($reqSearch == 3){//若选择优先级为3，优先级为空的也默认为3 lidz 17.3.17
                            $staList->whereRaw("($tableSupport.$search = $reqSearch or $tableSupport.$search is null)");
                        }else{
                            $staList->where($tableSupport . '.' . $search, $reqSearch);
                        }
                        break;
                    default:
                        $staList->where($tableSupport . '.' . $search, $reqSearch);
                    //if($reqSearch == "Suspend")$staList->orderByRaw("$tableSupport.ClassInficationOne = 'Equipment_personnel_2015' asc");
                }
            }
        }
        //排序
        $staList->whereNotNull("$tableSupport.status");
        $staList->orderByRaw("FIELD($tableSupport.status,'Todo','ReAppoint','Appointed','Doing','Suspend','Done','Closed'),$tableSupport.UpTs desc");
        $supportArray['total'] = $staList->count();
        //分页
        $pageSize = !empty($req->input('pageSize')) ? $req->input('pageSize') : 20;
        $pageNumber = !empty($req->input('pageNumber')) ? $req->input('pageNumber') : 1;
        $staList = $staList->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();
        $staList = $this->translationBySupportInfo($staList, $tableSupport);
        $staList = Support::translationDict($staList, 'ServiceModel', 'serviceModel');
        $supportArray['rows'] = $staList;

        return $supportArray;
    }

    /**
     * 导出数据
     * @param Request $req
     * @param Response $rep
     * @param Excel $excel
     * @return mixed
     */
    public function exportStaList(Request $req, Response $rep, Excel $excel)
    {
        set_time_limit(0);
        $tableSupport = self::$tableSupport;

        $staList = Support::select($tableSupport . '.*')
            ->leftJoin('res.res_cusinf as b', $tableSupport . '.CustomerInfoId', '=', 'b.Id');

        //过滤权限条件
        $staList = $this->getRoleData($staList, $tableSupport);

        $faStartTime = Input::get('faStartTime') ? Input::get('faStartTime') : date('Y-m-d H:i:s',
            time());
        $faEndTime = Input::get('faEndTime') ? Input::get('faEndTime') : date('Y-m-d H:i:s', time());
        if (!empty(Input::get('faStartTime')) || !empty(Input::get('faEndTime'))) {
            $staList = $staList->whereBetween($tableSupport . ".Ts", [$faStartTime, $faEndTime]);
        }
        if (($handle = $req->input("handleTime")) == "overTimeIds") {
            $Ids = $this->getOverTimeIds();
            $staList = $staList->whereIn($tableSupport . '.Id', $Ids);
        }
        if ($handle = $req->input("handleTime") == "onTimeIds") {
            $Ids = $this->getOverTimeIds();
            $staList = $staList->whereNotIn($tableSupport . '.Id', $Ids);
        }
        //高级条件查询
        $searchList = [
            'SuppOptGroup',
            'priority',
            'cusType',
            'Status',
            'dataCenter',
            'replyTime',
            'Evaluation',
            'searchInfo',
            'user',
            'timeOutIds',
            'classifyOne',
            'classifyTwo',
            'classifyThree',
            'ChargeUserId',
            'AsUserId',
            'supportSource',
        ];

        if($req->input("noEmail") && $req->input("noEmail") == "no"){//是否需要查看邮件告警类型
            $staList->where($tableSupport . '.ClassInficationOne',"!=","emailRequest");
        }

        foreach ($searchList as $search) {//工单检索
            $reqSearch = $req->input($search);
            if ($reqSearch != '') {
                switch ($search) {
                    case 'timeOutIds':
                        $staList->whereIn($tableSupport . '.Id', explode(",", $reqSearch));
                        break;
                    case 'user':
                        if ($reqSearch == 'mySupport') {
                            $user = $req->session()->get('user');
                            $staList->where(function ($staList) use ($user) {
                                $staList->where('ChargeUserId', $user->Id)
                                    ->orWhere('ChargeUserTwoId', $user->Id)
                                    ->orwhere('AsuserId', $user->Id);
                            });
                        }
                        break;
                    case 'classifyOne':
                        $staList->where(function ($staList) use ($reqSearch, $tableSupport) {
                            $staList->where($tableSupport . '.ServiceModel', $reqSearch);
                        });
                        break;
                    case 'classifyTwo':
                        $staList->where(function ($staList) use ($reqSearch, $tableSupport) {
                            $staList->where($tableSupport . '.ClassInfication', $reqSearch);
                        });
                        break;
                    case 'classifyThree':
                        $staList->where(function ($staList) use ($reqSearch, $tableSupport) {
                            $staList->where($tableSupport . '.ClassInficationOne', $reqSearch);
                        });
                        break;
                    case 'searchInfo':
                        $staList->where(function ($staList) use ($reqSearch, $tableSupport) {
                            $staList->Where('b.CusName', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.Title', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.Id', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.devIPAddr', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.Memo', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.EquipmentId', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.changeNO', 'like', '%' . trim($reqSearch) . '%')
                                ->orWhere($tableSupport . '.problemNO', 'like', '%' . trim($reqSearch) . '%');

                        });
                        break;
                    case 'cusType':
                        $staList->where(function ($staList) use ($reqSearch, $tableSupport) {
                            $staList->Where('b.CusImportanceType', $reqSearch)
                                ->orWhereNotNull($tableSupport . '.agentId');
                        });

                        break;
                    case 'replyTime':
                        switch ($reqSearch) {
                            case self::NO_REPLY:
                                $staList->WhereNull($tableSupport . '.FirstReplyTs');
                                break;
                            case self::REPLY_ELT_TWO_MIN:
                                $staList->whereRaw("TIMESTAMPDIFF(SECOND, $tableSupport . ts, $tableSupport . FirstReplyTs) <= " . self::TWO_MIN * 60);
                                break;
                            case self::REPLY_TWO2FIVE_MIN:
                                $staList->whereRaw("TIMESTAMPDIFF(SECOND, $tableSupport . ts, $tableSupport . FirstReplyTs) > " . (self::TWO_MIN * 60) . " AND TIMESTAMPDIFF(SECOND, $tableSupport . ts, $tableSupport . FirstReplyTs) <= " . (self::FIVE_MIN * 60));
                                break;
                            case self::REPLY_GT_FIVE_MIN:
                                $staList->whereRaw("TIMESTAMPDIFF(SECOND, $tableSupport . ts, $tableSupport . FirstReplyTs) >  " . (self::FIVE_MIN * 60));
                                break;
                        };
                        break;
                    case 'SuppOptGroup':
                        $staList = $staList->where("SuppOptGroup", $reqSearch);
                        break;
                    case 'supportSource':
                        $staList = $staList->where("Source", $reqSearch);
                        break;
                    //判断未评价的
                    case 'Evaluation':
                        if ($reqSearch == 'notEvaluate') {
                            $staList->whereNull("Evaluation");
                        } else {
                            $staList->where($tableSupport . '.' . $search, $reqSearch);
                        }
                        break;
                    default:
                        $staList->where($tableSupport . '.' . $search, $reqSearch);
                }
            }
        }
        //排序
        $staList = $staList->orderByRaw("$tableSupport.status = 'Todo' desc,$tableSupport.status = 'ReAppoint' desc,$tableSupport.status = 'Appointed' desc,$tableSupport.status = 'Doing' desc,$tableSupport.status = 'Suspend' desc,$tableSupport.status = 'Done' desc,$tableSupport.status = 'Closed' desc,$tableSupport.upts desc,$tableSupport.ts desc");

        $staList = $staList->get();

        $staList = ResContact::translationStuff($staList, 'ChargeUserId');
        $staList = ResContact::translationStuff($staList, 'ChargeUserTwoId');
        $staList = Support::translationDict($staList, 'ClassInficationOne', 'WorkSheetTypeOne');
        $staList = Support::translationDict($staList, 'Evaluation', 'WorksheetAppraisal');
        $staList = ResContact::translationStuff($staList, 'AsuserId');
        //最后操作人L0/L1/DC处理
        $this->switchColor($staList, 'OperationId');
        $staList = ResContact::translationStuff($staList, 'OperationId');
        $staList = Support::translationCusName($staList, 'CustomerInfoId');
        $staList = ThirdCallHelper::translationIdentity($staList);
        $staList = ThirdCallHelper::getAgentName($staList);
        $staList = Support::translationOverTime($staList, $tableSupport);
        $staList = ThirdCallHelper::translationPreDictTs($staList);
        set_time_limit(0);
        ini_set('memory_limit', '256M');

        $filename = '工单筛选列表——' . date('Ymd', time());
        $export_data = [];

        foreach ($staList as $key => $v) {
            $export_data[] = [
                "工单编号"  => $v['Id'],
                "数据中心"  => $v['dataCenter'],
                "负责人"   => $v['ChargeUserId'] . '/' . $v['ChargeUserTwoId'],
                "工单标题"  => $v['Title'],
                "工单状态"  => PublicMethodsHelper::$supportStatusList["{$v['Status']}"],
                "工单分类"  => $v['ClassInficationOne'],
                "指派人"   => $v['AsuserId'],
                "客户名称"  => $v['CusName'],
                "IP地址"  => $v['devIPAddr'],
                "关联设备"  => $v['EquipmentId'],
                "创建时间"  => $v['Ts'],
                "更新时间"  => $v['UpTs'],
                "最后更新人" => strip_tags($v['OperationId']),
                "跟踪人"   => $v['AsuserId'],
                "服务评价"  => $v['Evaluation'],
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

    /**
     * 快速回复模板blade
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function rmodeList()
    {
        $groupList = Suppstencil::select('Type')
            ->whereNotNull('Type')
            ->where('Type', '数据中心组')
            ->orWhere('Type', '服务台组')
            ->where('mark', 0)->groupBy('Type')->get();
        $typeList = ThirdCallHelper::getDictArray('工单类型', 'WorksheetTypeOne');
        return view("supports/rmodelist", compact("typeList", "groupList"));
    }

    /**
     * 回复模板数据
     * @return mixed
     */
    public function getrmodeListData()
    {
        $rmodeList = Suppstencil::selectRaw("Id,supportType,Type,Title,Content")
            ->where("mark", 0)->whereNotNull("Type");

        if ($type = Input::get('Type')) {
            $rmodeList->where('Type', $type);
        }
        if ($type = Input::get('supportType')) {
            $rmodeList->where('supportType', $type);
        }
        if ($searchTxt = trim(Input::get('searchTxt'))) {
            $rmodeList->where('Title', 'like', '%' . trim($searchTxt) . '%');
        }
        $rmodeList->orderBy("CreateTs", "desc");
        $rmodeArray['total'] = $rmodeList->count();
        //分页
        $pageSize = !empty(Input::get('pageSize')) ? Input::get('pageSize') : 20;
        $pageNumber = !empty(Input::get('pageNumber')) ? Input::get('pageNumber') : 1;
        $rmodeList = $rmodeList->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();
        $rmodeList = Support::translationDict($rmodeList, 'supportType', 'WorkSheetTypeOne');
        $rmodeArray['rows'] = $rmodeList;
        return $rmodeArray;
    }

    /**
     * 导出模板列表数据
     * @param Request $req
     * @param Response $rep
     * @param Excel $excel
     * @return mixed
     */
    public function exportModeList(Request $req, Response $rep, Excel $excel)
    {
        $rmodeList = Suppstencil::select('*')->where("mark", 0)->whereNotNull("Type");

        if ($type = Input::get('Type')) {
            $rmodeList->where('Type', $type);
        }
        if ($type = Input::get('supportType')) {
            $rmodeList->where('supportType', $type);
        }
        if ($searchTxt = trim(Input::get('searchTxt'))) {
            $rmodeList->where('Title', 'like', '%' . trim($searchTxt) . '%');
        }
        $rmodeList->orderBy("CreateTs", "desc");
        $rmodeList = $rmodeList->get();
        set_time_limit(0);
        ini_set('memory_limit', '256M');

        $filename = '快速回复模板列表——' . date('Ymd', time());
        $export_data = [];

        foreach ($rmodeList as $key => $v) {
            $export_data[] = [
                "模板编号"  => $v['Id'],
                "分组"    => $v['Type'],
                "模板标题"  => $v['Title'],
                "内容"    => $v['Content'],
                "工单类型"  => ThirdCallHelper::getDictMeans('工单类型', 'WorkSheetTypeOne', $v['supportType']),
                "创建时间"  => $v['Ts'],
                "更新时间"  => $v['UpTs'],
                "最后更新人" => ThirdCallHelper::getStuffName(strip_tags($v['UpUserId'])),
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

    //删除回复模板
    public function rmodeDelete($id)
    {
        try {
            $ret = Suppstencil::where("Id", $id)->update(["mark" => "1"]);
            return array('status' => true);
        } catch (\Exception $ex) {
            return array('status' => false, 'msg' => $ex->getMessage());
        }
    }

    //模板编辑
    public function rmodeEdit($id)
    {
        $groupList = Suppstencil::select('Type')
            ->whereNotNull('Type')
            ->where('Type', '数据中心组')
            ->orWhere('Type', '服务台组')
            ->where('mark', 0)->groupBy('Type')->get();
        $typeList = ThirdCallHelper::getDictArray('工单类型', 'WorksheetTypeOne');
        $data = Suppstencil::where('Id', $id)->first();
        return view("supports/editrmode", compact("groupList", "typeList", "data"));
    }

    /**
     * 保存模板编辑
     * @param $id
     * @return array
     */
    public function rmodeEditPush($id)
    {
        $all = Input::except('_token');
        $userId = Request()->session()->get('user')->Id;
        $now = date("Y-m-d H:i:s");
        $validator = Validator::make($all, [
            'type'    => 'required',
            'title'   => 'required',
            'content' => 'required'
        ], [
            'required' => ':attribute 的字段是必要的。',
        ]);

        if ($validator->fails()) {//验证不通过,
            return ['status' => false, 'statusMsg' => '填写信息有误，保存失败!'];
        } else {
            $group = 0;
            if ($all['type'] == '服务台组') {
                $group = 1;
            }
            $update = [
                "Type"        => $all['type'],
                "supportType" => $all['supportType'],
                "Title"       => $all['title'],
                "group"       => $group,
                "UpTs"        => $now,
                "UpUserId"    => $userId,
                "Content"     => trim(PublicMethodsHelper::htmlToSafe($all['content']))
            ];
            $ret = Suppstencil::where("Id", $id)->update($update);
            return ['status' => true];
        }
    }

    //新增模板blade
    public function newRmode()
    {
        $groupList = Suppstencil::select('Type')
            ->whereNotNull('Type')
            ->where('Type', '数据中心组')
            ->orWhere('Type', '服务台组')
            ->where('mark', 0)->groupBy('Type')->get();
        $typeList = ThirdCallHelper::getDictArray('工单类型', 'WorksheetTypeOne');
        return view("supports/newrmode", compact("groupList", "typeList"));
    }

    public function newRmodePush()
    {
        $all = Input::except('_token');
        $userId = Request()->session()->get('user')->Id;
        $now = date("Y-m-d H:i:s");
        $validator = Validator::make($all, [
            'type'    => 'required',
            'title'   => 'required',
            'content' => 'required'
        ], [
            'required' => ':attribute 的字段是必要的。',
        ]);

        if ($validator->fails()) {//验证不通过,
            return ['status' => false, 'statusMsg' => '填写信息有误，保存失败!'];
        } else {
            $group = 0;
            if ($all['type'] == '服务台组') {
                $group = 1;
            }
            $insertdata = [
                "Type"         => $all['type'],
                "supportType"  => $all['supportType'],
                "Title"        => $all['title'],
                "group"        => $group,
                "CreateTs"     => $now,
                "CreateUserId" => $userId,
                "Content"      => trim(PublicMethodsHelper::htmlToSafe($all['content']))
            ];
            $retId = Suppstencil::insertGetId($insertdata);
            return ['status' => true, "retId" => $retId];
        }
    }

    public function getKzYear()
    {
        $yearList = SupportKz::selectRaw("DISTINCT(Year(Ts)) as y")
            ->whereRaw("(ClassInficationOne != 'emailRequest' or  ClassInficationOne is NULL)")
            ->whereRaw("year(Ts) >= 2014");
        $yearList = $yearList->orderBy("y", "Desc")->get()->toArray();
        return $yearList;
    }
    public function getEvaReport()
    {
        $chargeGroup = AuxDict::where('DomainCode', 'chargeGroup')->where('Domain', '负责人群组')->get();
        $type = ThirdCallHelper::getDictArray('工单类型', 'WorksheetTypeOne');
        $source = ThirdCallHelper::getDictArray('工单来源', 'supportSource');
        $yearList=$this->getKzYear();
        return view("supports/evaReport", compact('type', 'source', 'chargeGroup','yearList'));
    }

    public function getEvaList(Request $req,Response $rep)
    {
        $mon = [1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0, 7=>0, 8=>0, 9=>0, 10=>0, 11=>0, 12=>0];
        $evaList = [
            '1' => ['非常满意','Best', $mon],
            '2' => ['基本满意','Good', $mon],
            '3' => ['一般','notEvaluate', $mon],
            '4' => ['不满意','Bady', $mon],
            '5' => ['工单总数','', $mon],
            '6' => ['满意度','', $mon],
        ];
        $searchList = [
            'charge',
            'priority',
            'supportType',
            'supportSource'
        ];

        if ($year = Input::get('year')) {
            $evaluateData = SupportKz::selectRaw("year(Ts) as year,month(Ts) as month ,
        count(CASE WHEN Evaluation = 'Best' THEN 1 ELSE NULL END ) as BestSum,count(CASE WHEN Evaluation = 'Good' THEN 1 ELSE NULL END ) as GoodSum,count(CASE WHEN Evaluation = 'Bady' THEN 1 ELSE NULL END ) as BadySum,count(Id) as Sum,(count(Id) - count(CASE WHEN Evaluation = 'Bady' THEN 1 ELSE NULL END ))/count(Id) as evaluate")
                ->whereRaw("(ClassInficationOne != 'emailRequest' or  ClassInficationOne is NULL)")
                ->whereNull('InvalidateAt')
                ->orderBy("Ts", "asc")
                ->havingRaw("year = $year")
                ->groupBy("year", "month");
        } else {
            $evaluateData = SupportKz::selectRaw("year(Ts) as year,month(Ts) as month ,
        count(CASE WHEN Evaluation = 'Best' THEN 1 ELSE NULL END ) as BestSum,count(CASE WHEN Evaluation = 'Good' THEN 1 ELSE NULL END ) as GoodSum,count(CASE WHEN Evaluation = 'Bady' THEN 1 ELSE NULL END ) as BadySum,count(Id) as Sum,(count(Id) - count(CASE WHEN Evaluation = 'Bady' THEN 1 ELSE NULL END ))/count(Id) as evaluate")
                ->whereRaw("(ClassInficationOne != 'emailRequest' or  ClassInficationOne is NULL)")
                ->whereNull('InvalidateAt')
                ->orderBy("Ts", "asc")
                ->havingRaw("year = 2016")
                ->groupBy("year", "month");
        }
        foreach ($searchList as $search) {
            $reqSearch = $req->input($search);
            if ($reqSearch != '') {
                switch ($search) {
                    case 'priority':
                        $evaluateData->where('priority', $reqSearch);
                        break;
                    case 'charge':
                        if ($reqSearch == 'other')
                            $evaluateData->whereNull('chargeGroup');
                        else {
                        $evaluateData->where('chargeGroup', $reqSearch);}
                        break;
                    case 'supportSource':
                        $evaluateData->where('Source', $reqSearch);
                        break;
                    case 'supportType':
                        $evaluateData->where('ClassInficationOne', $reqSearch);
                        break;
                }
            }
        }
        $evaluateData = $evaluateData->get()->toArray();
        foreach ($evaList as &$arr) {
            if ($arr[0] == '非常满意') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=$item['BestSum'];
                }
            }
            if ($arr[0] == '基本满意') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=$item['GoodSum'];
                }
            }
            if ($arr[0] == '一般') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=$item['Sum']-$item['BadySum']-$item['BestSum']-$item['GoodSum'];
                }
            }
            if ($arr[0] == '不满意') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=$item['BadySum'];
                }
            }
            if ($arr[0] == '工单总数') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=$item['Sum'];
                }
            }
            if ($arr[0] == '满意度') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=round($item['evaluate']*100,2).'%';
                }
            }
        }

        $listArr = [
            'evaList' => $evaList,
        ];
        return $listArr;
    }
    public function getComReport()
    {
        $chargeGroup = AuxDict::where('DomainCode', 'chargeGroup')->where('Domain', '负责人群组')->get();
        $type = ThirdCallHelper::getDictArray('工单类型', 'WorksheetTypeOne');
        $source = ThirdCallHelper::getDictArray('工单来源', 'supportSource');
        $yearList=$this->getKzYear();
        return view("supports/comReport", compact('type', 'source', 'chargeGroup','yearList'));
    }

    public function getComList(Request $req,Response $rep)
    {
        $mon = [1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0, 7=>0, 8=>0, 9=>0, 10=>0, 11=>0, 12=>0];
        $evaList = [
            '1' => ['1级超时工单','1', $mon],
            '2' => ['2级超时工单','2', $mon],
            '3' => ['3级超时工单','3', $mon],
            '4' => ['工单总数','', $mon],
            '5' => ['完成超时率','', $mon],
        ];

        $searchList = [
            'year',
            'charge',
            'priority',
            'supportType',
            'supportSource'
        ];
        if ($year = Input::get('year')) {
            $evaluateData = SupportKz::selectRaw("year(Ts) as year,month(Ts) as month ,
        count(CASE WHEN priority = 1 and TIMESTAMPDIFF(SECOND,ts,IF (processTs IS NULL,CURRENT_TIMESTAMP(),processTs)) > (IF (priority = 1,3600 * 3,IF (priority = 2,3600 * 8,3600 * 48)) + IFNULL(hangupDuration, 0)) THEN 1 ELSE NULL END ) as Sum1,
        count(CASE WHEN priority = 2 and TIMESTAMPDIFF(SECOND,ts,IF (processTs IS NULL,CURRENT_TIMESTAMP(),processTs)) > (IF (priority = 1,3600 * 3,IF (priority = 2,3600 * 8,3600 * 48)) + IFNULL(hangupDuration, 0)) THEN 1 ELSE NULL END ) as Sum2,
         count(CASE WHEN priority = 3 and TIMESTAMPDIFF(SECOND,ts,IF (processTs IS NULL,CURRENT_TIMESTAMP(),processTs)) > (IF (priority = 1,3600 * 3,IF (priority = 2,3600 * 8,3600 * 48)) + IFNULL(hangupDuration, 0))THEN 1 ELSE NULL END ) as Sum3,count(Id) as Sum")
                ->whereRaw("(ClassInficationOne != 'emailRequest' or  ClassInficationOne is NULL)")
                ->whereNull('InvalidateAt')
                ->orderBy("Ts", "asc")
                ->havingRaw("year = $year")
                ->groupBy("year", "month");
        }
        foreach ($searchList as $search) {
            $reqSearch = $req->input($search);
            if ($reqSearch != '') {
                switch ($search) {
                    case 'priority':
                        $evaluateData->where('support_kz.priority', $reqSearch);
                        if($reqSearch==1)
                            $evaList = [
                                '1' => ['1级超时工单','1', $mon],
                                '2' => ['工单总数','', $mon],
                                '3' => ['完成超时率','', $mon],
                            ];
                        if($reqSearch==2)
                            $evaList = [
                                '1' => ['2级超时工单','2', $mon],
                                '2' => ['工单总数','', $mon],
                                '3' => ['完成超时率','', $mon],
                            ];
                        if($reqSearch==3)
                            $evaList = [
                                '1' => ['3级超时工单','3', $mon],
                                '2' => ['工单总数','', $mon],
                                '3' => ['完成超时率','', $mon],
                            ];
                        break;
                    case 'charge':
                        if ($reqSearch == 'other')
                            $evaluateData->whereNull('support_kz.chargeGroup');
                        else {
                            $evaluateData->where('support_kz.chargeGroup', $reqSearch);}
                        break;
                    case 'supportSource':
                        $evaluateData->where('support_kz.Source', $reqSearch);
                        break;
                    case 'supportType':
                        $evaluateData->where('support_kz.ClassInficationOne', $reqSearch);
                        break;
                }
            }
        }
        $evaluateData=$evaluateData->get()->toArray();

        foreach ($evaList as &$arr) {
            if ($arr[0] == '1级超时工单') {
                foreach ($evaluateData as $k =>$item) {
                    $arr[2][$item['month']]=$item['Sum1'];
                }
            }
            if ($arr[0] == '2级超时工单') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=$item['Sum2'];
                }
            }
            if ($arr[0] == '3级超时工单') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=$item['Sum3'];
                }
            }
            if ($arr[0] == '工单总数') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=$item['Sum'];
                }
            }
            if ($arr[0] == '完成超时率') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=round(($item['Sum1']+$item['Sum2']+$item['Sum3'])/$item['Sum']*(100),2).'%';
                }
            }
        }

        $listArr = [
            'comList' => $evaList,
        ];
        return $listArr;
    }
    public function getRepReport()
    {
        $chargeGroup = AuxDict::where('DomainCode', 'chargeGroup')->where('Domain', '负责人群组')->get();
        $type = ThirdCallHelper::getDictArray('工单类型', 'WorksheetTypeOne');
        $source = ThirdCallHelper::getDictArray('工单来源', 'supportSource');
        $yearList=$this->getKzYear();
        return view("supports/repReport", compact('type', 'source', 'chargeGroup','yearList'));
    }

    public function getRepList(Request $req,Response $rep)
    {
        $mon = [1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0, 7=>0, 8=>0, 9=>0, 10=>0, 11=>0, 12=>0];
        $evaList = [
            '1' => ['≤2min','2', $mon],
            '2' => ['2min-5min','5', $mon],
            '3' => ['＞5min','10', $mon],
            '4' => ['超时工单小计(＞2min)','all', $mon],
            '5' => ['工单总数','', $mon],
            '6' => ['响应超时率','',$mon],
        ];
        $searchList = [
            'year',
            'charge',
            'priority',
            'supportType',
            'supportSource'
        ];
        if ($year = Input::get('year')) {
            $evaluateData = SupportKz::selectRaw("year(Ts) as year,month(Ts) as month ,
        count(CASE WHEN TIMESTAMPDIFF(SECOND, ts, FirstReplyTs) <= 120 THEN '2' else null end ) as Sum2,count(CASE WHEN TIMESTAMPDIFF(SECOND, ts, FirstReplyTs) <= 300 and TIMESTAMPDIFF(SECOND, ts, FirstReplyTs) > 120 THEN '5'else null end ) as Sum5,count(CASE WHEN TIMESTAMPDIFF(SECOND, ts, FirstReplyTs) > 300 THEN '10' else null end ) as Sum10,count(Id) as Sum")
                ->whereRaw("(ClassInficationOne != 'emailRequest' or  ClassInficationOne is NULL)")
                ->whereNull('InvalidateAt')
                ->orderBy("Ts", "asc")
                ->havingRaw("year = $year")
                ->groupBy("year", "month");
        }

        foreach ($searchList as $search) {
            $reqSearch = $req->input($search);
            if ($reqSearch != '') {
                switch ($search) {
                    case 'priority':
                        $evaluateData->where('support_kz.priority', $reqSearch);
                        break;
                    case 'charge':
                        if ($reqSearch == 'other')
                            $evaluateData->whereNull('support_kz.chargeGroup');
                        else {
                            $evaluateData->where('support_kz.chargeGroup', $reqSearch);}
                        break;
                    case 'supportSource':
                        $evaluateData->where('support_kz.Source', $reqSearch);
                        break;
                    case 'supportType':
                        $evaluateData->where('support_kz.ClassInficationOne', $reqSearch);
                        break;
                }
            }
        }
        $evaluateData=$evaluateData->get()->toArray();

        foreach ($evaList as &$arr) {
            if ($arr[0] == '≤2min') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=$item['Sum2'];
                }
            }
            if ($arr[0] == '2min-5min') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=$item['Sum5'];
                }
            }
            if ($arr[0] == '＞5min') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=$item['Sum10'];
                }
            }
            if ($arr[0] == '超时工单小计(＞2min)') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=$item['Sum5']+$item['Sum10'];
                }
            }
            if ($arr[0] == '工单总数') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=$item['Sum'];
                }
            }
            if ($arr[0] == '响应超时率') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=round(($item['Sum5']+$item['Sum10'])/$item['Sum']*100,2).'%';
                }
            }
        }

        $listArr = [
            'repList' => $evaList,
        ];
        return $listArr;
    }
    public function getSucReport()
    {
        $chargeGroup = AuxDict::where('DomainCode', 'chargeGroup')->where('Domain', '负责人群组')->get();
        $type = ThirdCallHelper::getDictArray('工单类型', 'WorksheetTypeOne');
        $source = ThirdCallHelper::getDictArray('工单来源', 'supportSource');
        $yearList=$this->getKzYear();
        return view("supports/sucReport", compact('type', 'source', 'chargeGroup','yearList'));
    }

    public function getSucList(Request $req,Response $rep)
    {
        $mon = [1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0, 7=>0, 8=>0, 9=>0, 10=>0, 11=>0, 12=>0];
        $evaList = [
            '1' => ['非群组成功解决工单数','doOther', $mon],
            '2' => ['L0群组成功解决工单数','doL0', $mon],
            '3' => ['L1群组成功解决工单数','doL1', $mon],
            '4' => ['L2群组成功解决工单数','doL2', $mon],
            '5' => ['L3群组成功解决工单数','doL3', $mon],
            '6' => ['成功解决工单总数','doAll', $mon],
            '7' => ['工单总数','', $mon],
            '8' => ['非群组工单总数','otherAll', $mon],
            '9' => ['L0群组工单总数','L0All', $mon],
            '10' => ['L1群组工单总数','L1All', $mon],
            '11' => ['L2群组工单总数','L2All', $mon],
            '12' => ['L3群组工单总数','L3All', $mon],
            '13' => ['非群组工单成功解决率','doOther', $mon],
            '14' => ['L0群组工单成功解决率','doL0', $mon],
            '15' => ['L1群组工单成功解决率','doL1', $mon],
            '16' => ['L2群组工单成功解决率','doL2', $mon],
            '17' => ['L3群组工单成功解决率','doL3', $mon],
            '18' => ['工单成功解决率','doAll', $mon],
        ];
        $searchList = [
            'charge',
            'priority',
            'supportType',
            'supportSource'
        ];
        if ($year = Input::get('year')) {
            $evaluateData = SupportKz::selectRaw("year(support_kz.Ts) as year,month(support_kz.Ts) as month ,
        count(CASE WHEN chargeGroup = 'L0' THEN 1 ELSE NULL END ) as SumL0,count(CASE WHEN chargeGroup = 'L0' and date_format(support_kz.ProcessTs,'%m') = date_format(support_kz.Ts,'%m') THEN 1 ELSE NULL END ) as doSumL0,count(CASE WHEN support_kz.chargeGroup = 'L1' THEN 1 ELSE NULL END ) as SumL1,count(CASE WHEN support_kz.chargeGroup = 'L1' and date_format(ProcessTs,'%m') = date_format(Ts,'%m')THEN 1 ELSE NULL END ) as doSumL1,count(CASE WHEN chargeGroup = 'L2' THEN 1 ELSE NULL END ) as SumL2,count(CASE WHEN chargeGroup = 'L2'and date_format(ProcessTs,'%m') = date_format(Ts,'%m') THEN 1 ELSE NULL END ) as doSumL2,count(CASE WHEN chargeGroup = 'L3' THEN 1 ELSE NULL END ) as SumL3,count(CASE WHEN chargeGroup = 'L3' and date_format(ProcessTs,'%m') = date_format(Ts,'%m')THEN 1 ELSE NULL END ) as doSumL3,count(Id) as Sum,count(CASE WHEN Id and date_format(ProcessTs,'%m') = date_format(Ts,'%m')THEN 1 ELSE NULL END ) as doSum")
                ->whereRaw("(ClassInficationOne != 'emailRequest' or ClassInficationOne is NULL)")
                ->whereNull('InvalidateAt')
                ->orderBy("Ts", "asc")
                ->havingRaw("year = $year")
                ->groupBy("year", "month");
        }
        foreach ($searchList as $search) {
            $reqSearch = $req->input($search);
            if ($reqSearch != '') {
                switch ($search) {
                    case 'priority':
                        $evaluateData->where('support_kz.priority', $reqSearch);
                        break;
                    case 'charge':
                        if ($reqSearch == 'other') {
                            $evaList = [
                                '1' => ['非群组成功解决工单数','doOther',$mon],
                                '2' => ['非群组工单总数','otherAll', $mon],
                                '3' => ['工单总数','', $mon],
                                '4' => ['非群组工单成功解决率','doOther', $mon],
                            ];
                        }
                        if ($reqSearch == 'L0') {
                            $evaList = [
                                '1' => ['L0群组成功解决工单数','doL0', $mon],
                                '2' => ['L0群组工单总数','L0All', $mon],
                                '3' => ['工单总数', '',$mon],
                                '4' => ['L0群组工单成功解决率','doL0', $mon],
                            ];
                        }
                        if ($reqSearch == 'L1') {
                            $evaList = [
                                '1' => ['L1群组成功解决工单数','doL1', $mon],
                                '2' => ['L1群组工单总数','L1All', $mon],
                                '3' => ['工单总数','', $mon],
                                '4' => ['L1群组工单成功解决率','doL1', $mon],
                            ];
                        }
                        if ($reqSearch == 'L2') {
                            $evaList = [
                                '1' => ['L2群组成功解决工单数','doL2', $mon],
                                '2' => ['L2群组工单总数','L2All', $mon],
                                '3' => ['工单总数','', $mon],
                                '4' => ['L2群组工单成功解决率','doL2', $mon],
                            ];
                        }
                        if ($reqSearch == 'L3') {
                            $evaList = [
                                '1' => ['L3群组成功解决工单数','doL3', $mon],
                                '2' => ['L3群组工单总数','L3All', $mon],
                                '3' => ['工单总数','', $mon],
                                '4' => ['L3群组工单成功解决率','doL3', $mon],
                            ];
                        }

                        break;
                    case 'supportSource':
                        $evaluateData->where('support_kz.Source', $reqSearch);
                        break;
                    case 'supportType':
                        $evaluateData->where('support_kz.ClassInficationOne', $reqSearch);
                        break;
                }
            }
        }

        $evaluateData=$evaluateData->get()->toArray();

        foreach ($evaList as &$arr) {
            if ($arr[0] == 'L0群组成功解决工单数') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=$item['doSumL0'];
                }
            }
            if ($arr[0] == 'L1群组成功解决工单数') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=$item['doSumL1'];
                }
            }
            if ($arr[0] == 'L2群组成功解决工单数') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=$item['doSumL2'];
                }
            }
            if ($arr[0] == 'L3群组成功解决工单数') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=$item['doSumL3'];
                }
            }
            if ($arr[0] == '非群组成功解决工单数') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=$item['doSum']-$item['doSumL0']-$item['doSumL1']-$item['doSumL2']-$item['doSumL3'];
                }
            }
            if ($arr[0] == '成功解决工单总数') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=$item['doSum'];
                }
            }
            if ($arr[0] == '工单总数') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=$item['Sum'];
                }
            }
            if ($arr[0] == 'L0群组工单总数') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=$item['SumL0'];
                }
            }
            if ($arr[0] == 'L1群组工单总数') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=$item['SumL1'];
                }
            }
            if ($arr[0] == 'L2群组工单总数') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=$item['SumL2'];
                }
            }
            if ($arr[0] == 'L3群组工单总数') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=$item['SumL3'];
                }
            }
            if ($arr[0] == '非群组工单总数') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=$item['Sum']-$item['SumL0']-$item['SumL1']-$item['SumL2']-$item['SumL3'];
                }
            }
            if ($arr[0] == 'L0群组工单成功解决率') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=round($item['doSumL0']/$item['Sum']*100,2).'%';
                }
            }
            if ($arr[0] == 'L1群组工单成功解决率') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=round($item['doSumL1']/$item['Sum']*100,2).'%';
                }
            }
            if ($arr[0] == 'L2群组工单成功解决率') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=round($item['doSumL2']/$item['Sum']*100,2).'%';
                }
            }
            if ($arr[0] == 'L3群组工单成功解决率') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=round($item['doSumL3']/$item['Sum']*100,2).'%';
                }
            }
            if ($arr[0] == '非群组工单成功解决率') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=round(($item['doSum']-$item['doSumL0']-$item['doSumL1']-$item['doSumL2']-$item['doSumL3'])/$item['Sum']*100,2).'%';
                }
            }
            if ($arr[0] == '工单成功解决率') {
                foreach ($evaluateData as $k => $item) {
                    $arr[2][$item['month']]=round($item['doSum']/$item['Sum']*100,2).'%';
                }
            }
        }

        $listArr = [
            'sucList' => $evaList,
        ];
        return $listArr;
    }
    public function supportKZList(Request $req)
    {
        $params = $req->all();
        $chargeGroupList = AuxDict::where('DomainCode', 'chargeGroup')->where('Domain', '负责人群组')->get();
        $statusList = PublicMethodsHelper::getSupportStatusList();
        $customerTypeList = PublicMethodsHelper::getCustomerList();
        $responseTimeList = PublicMethodsHelper::getResponseTimeList();
        $dataCenterList = ThirdCallHelper::getDataCenter();
        $evaluationList = ['Bady'=>'不满意','notEvaluate'=>'一般','Good'=>'基本满意','Best'=>'非常满意'];
        $priorityList = [1, 2, 3];
        $yearList=$this->getKzYear();
        $monthList = [1,2,3,4,5,6,7,8,9,10,11,12];
        $processList = ['one'=>'1级超时工单','two'=>'2级超时工单','three'=>'3级超时工单','success'=>'成功解决'];
        $classifyTwo = AuxDict::select('Code', 'Means')->where('Domain', '工单事件分类')->orderby('Id')->get();
        $class3List = ThirdCallHelper::getDictArray('工单类型', 'WorksheetTypeOne');
        $supSourceList = ThirdCallHelper::getDictArray('工单来源', 'supportSource');
        $secondDeptList = ThirdCallHelper::getDictArray('二级部门', 'second_dept');
        $chargeUserList = PublicMethodsHelper::chargeUser();
        $AsUserList = PublicMethodsHelper::asUser();
        if ($sources = Input::get('sources')) {
            $sources = Input::get('sources') ? $sources : '';
        }
        return view('supports/supportKZ', [
            'params'           => $params,
            'chargeList'       => $chargeGroupList,
            'statusList'       => $statusList,
            'dataCenterList'   => $dataCenterList,
            'evaluationList'   => $evaluationList,
            'priorityList'     => $priorityList,
            'classifyTwo'      => $classifyTwo,
            'class3List'       => $class3List,
            'responseTimeList' => $responseTimeList,
            'customerTypeList' => $customerTypeList,
            'chargeUserList'   => $chargeUserList,
            'AsUserList'       => $AsUserList,
            'supSourceList'    => $supSourceList,
            'secondDeptList'   => $secondDeptList,
            'sources'          => $sources,
            'yearList'         => $yearList,
            'monthList'        => $monthList,
            'processList'      => $processList
        ]);
    }

    public function getSupportKZData(Request $req)
    {
        $tableKz = self::$tableKz;

        $kzList = SupportKz::selectRaw("*")->whereNull('InValidateAt')->whereRaw("(ClassInficationOne != 'emailRequest' or ClassInficationOne is NULL)");

        if($year = $req->input("year"))
        {
            $kzList=$kzList->whereRaw("DATE_FORMAT(ts,'%Y')= $year");
        }
        if($month = $req->input("month"))
        {
            $kzList=$kzList->whereRaw("DATE_FORMAT(ts,'%m')= $month");
        }
        $searchList = [
            'Status',
            'chargeGroup',
            'priority',
            'responseTime',
            'processTime',
            'supportSource',
            'class3',
            'timeOut',
            'successNum',
            'evaluate'
        ];
        foreach ($searchList as $search) {//工单检索
            $reqSearch = $req->input($search);
            if ($reqSearch != '') {
                switch ($search) {
                    case 'Status':
                        $kzList->where(function ($kzList) use ($reqSearch, $tableKz) {
                            $kzList->where($tableKz . '.Status', $reqSearch);
                        });
                        break;
                    case 'class3':
                        $kzList->where(function ($kzList) use ($reqSearch, $tableKz) {
                            $kzList->where($tableKz . '.ClassInficationOne', $reqSearch);
                        });
                        break;
                    case 'responseTime':
                        switch ($reqSearch) {
                            case self::NO_REPLY:
                                $kzList->WhereNull($tableKz . '.FirstReplyTs');
                                break;
                            case self::REPLY_ELT_TWO_MIN:
                                $kzList->whereRaw("TIMESTAMPDIFF(SECOND, $tableKz . Ts, $tableKz . FirstReplyTs) <= " . self::TWO_MIN * 60);
                                break;
                            case self::REPLY_TWO2FIVE_MIN:
                                $kzList->whereRaw("TIMESTAMPDIFF(SECOND, $tableKz . Ts, $tableKz . FirstReplyTs) > " . (self::TWO_MIN * 60) . " AND TIMESTAMPDIFF(SECOND, $tableKz . ts, $tableKz . FirstReplyTs) <= " . (self::FIVE_MIN * 60));
                                break;
                            case self::REPLY_GT_FIVE_MIN:
                                $kzList->whereRaw("TIMESTAMPDIFF(SECOND, $tableKz . Ts, $tableKz . FirstReplyTs) >  " . (self::FIVE_MIN * 60));
                                break;
                            case 'all':
                                $kzList->whereRaw("TIMESTAMPDIFF(SECOND, $tableKz . Ts, $tableKz . FirstReplyTs) >  " . (self::TWO_MIN * 60));
                                break;
                        };
                        break;
                    case 'chargeGroup':
                        if($reqSearch == 'other')
                            $kzList->whereNull("chargeGroup");
                        else
                            $kzList->where("chargeGroup", $reqSearch);
                        break;
                    case 'processTime':
                        switch($reqSearch){
                            case 'one':
                                $kzList->whereRaw("TIMESTAMPDIFF(SECOND, ts, FirstReplyTs) > 120 and chargeGroup = 'L0'");
                                break;
                            case 'two':
                                $kzList->whereRaw("TIMESTAMPDIFF(SECOND, ts, FirstReplyTs) > 120 and chargeGroup = 'L1'");
                                break;
                            case 'three':
                                $kzList->whereRaw("TIMESTAMPDIFF(SECOND, ts, FirstReplyTs) > 120 and chargeGroup = 'L2'");
                                break;
                            case 'success':
                                $kzList->whereRaw("date_format(ProcessTs,'%m') = date_format(Ts,'%m')");
                                break;
                        }
                        break;
                    case 'successNum':
                        switch($reqSearch){
                            case 'doAll':
                                $kzList->whereRaw("CASE WHEN Id and date_format(ProcessTs,'%m') = date_format(Ts,'%m')THEN 1 ELSE NULL END");
                                break;
                            case 'L0All':
                                $kzList->whereRaw("CASE WHEN chargeGroup = 'L0' THEN 1 ELSE NULL END");
                                break;
                            case 'doL0':
                                $kzList->whereRaw("CASE WHEN chargeGroup = 'L0' and date_format(support_kz.ProcessTs,'%m') = date_format(support_kz.Ts,'%m') THEN 1 ELSE NULL END");
                                break;
                            case 'L1All':
                                $kzList->whereRaw("CASE WHEN chargeGroup = 'L1' THEN 1 ELSE NULL END");
                                break;
                            case 'doL1':
                                $kzList->whereRaw("CASE WHEN chargeGroup = 'L1' and date_format(support_kz.ProcessTs,'%m') = date_format(support_kz.Ts,'%m') THEN 1 ELSE NULL END");
                                break;
                            case 'L2All':
                                $kzList->whereRaw("CASE WHEN chargeGroup = 'L2' THEN 1 ELSE NULL END");
                                break;
                            case 'doL2':
                                $kzList->whereRaw("CASE WHEN chargeGroup = 'L2' and date_format(support_kz.ProcessTs,'%m') = date_format(support_kz.Ts,'%m') THEN 1 ELSE NULL END");
                                break;
                            case 'L3All':
                                $kzList->whereRaw("CASE WHEN chargeGroup = 'L3' THEN 1 ELSE NULL END");
                                break;
                            case 'doL3':
                                $kzList->whereRaw("CASE WHEN chargeGroup = 'L3' and date_format(support_kz.ProcessTs,'%m') = date_format(support_kz.Ts,'%m') THEN 1 ELSE NULL END");
                                break;
                            case 'otherAll':
                                $kzList->whereRaw("CASE WHEN chargeGroup is null THEN 1 ELSE NULL END");
                                break;
                            case 'doOther':
                                $kzList->whereRaw("CASE WHEN chargeGroup is null and date_format(support_kz.ProcessTs,'%m') = date_format(support_kz.Ts,'%m') THEN 1 ELSE NULL END");
                                break;
                        }
                        break;
                    case 'supportSource':
                        $kzList->where("Source", $reqSearch);
                        break;
                    case 'priority':
                        $kzList->where("priority", $reqSearch);
                        break;
                    case 'timeOut':
                        switch($reqSearch){
                            case '1':
                                $kzList->whereRaw("CASE WHEN priority = 1 and TIMESTAMPDIFF(SECOND,ts,IF (processTs IS NULL,CURRENT_TIMESTAMP(),processTs)) > (IF (priority = 1,3600 * 3,IF (priority = 2,3600 * 8,3600 * 48)) + IFNULL(hangupDuration, 0)) THEN 1 ELSE NULL END ");
                                break;
                            case '2':
                                $kzList->whereRaw("CASE WHEN priority = 2 and TIMESTAMPDIFF(SECOND,ts,IF (processTs IS NULL,CURRENT_TIMESTAMP(),processTs)) > (IF (priority = 1,3600 * 3,IF (priority = 2,3600 * 8,3600 * 48)) + IFNULL(hangupDuration, 0)) THEN 1 ELSE NULL END ");
                                break;
                            case '3':
                                $kzList->whereRaw("CASE WHEN priority = 3 and TIMESTAMPDIFF(SECOND,ts,IF (processTs IS NULL,CURRENT_TIMESTAMP(),processTs)) > (IF (priority = 1,3600 * 3,IF (priority = 2,3600 * 8,3600 * 48)) + IFNULL(hangupDuration, 0)) THEN 1 ELSE NULL END ");
                                break;
                        }
                        break;
                    //判断未评价的
                    case 'evaluate':
                        if ($reqSearch == 'notEvaluate') {
                            $kzList->whereNull("Evaluation");
                        } else {
                            $kzList->where('Evaluation', $reqSearch);
                        }
                        break;
                    default:
                        $kzList->where($tableKz . '.' . $search, $reqSearch);
                }
            }
        }
        //排序
        $kzList->whereNotNull("$tableKz.Status");
        $kzList->orderByRaw("FIELD($tableKz.Status,'Todo','ReAppoint','Appointed','Doing','Suspend','Done','Closed'),$tableKz.Id desc");
        $supportArray['total'] = $kzList->count();
        //分页
        $pageSize = !empty($req->input('pageSize')) ? $req->input('pageSize') : 20;
        $pageNumber = !empty($req->input('pageNumber')) ? $req->input('pageNumber') : 1;
        $kzList = $kzList->limit($pageSize)->offset(($pageNumber - 1) * $pageSize)->get();
        $kzList = $this->translationBySupportInfo($kzList, $tableKz);
        $supportArray['rows'] = $kzList;

        return $supportArray;
    }

    /**
     * 获取不同类型工单分析数据
     * @param Request $req
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getAnalyzeTicket($type)
    {
        try {
            $url = 'https://bi.anchnet.com/trusted';
            $header = ['Content-Type' =>'application/x-www-form-urlencoded'];
            $content = [
                'username'   => 'tableau'
            ];
            $client = new Client();
            $response = $client->request('post',$url, [
                "http_errors" => true,
                "headers"     => $header,
                'form_params' => $content,
            ]);
            return view('supports/'.$type, [
                'ticket' =>$response->getBody()->getContents()
            ]);
        } catch (\Exception $ex) {
            return array('status' => false, 'msg' => $ex->getMessage());
        }
    }
}