<div class="info-top">
    <p>变更方案及测试结果复核</p>
</div>
<div class="info-content">
    <ul>
        <li><span>审批意见/测试结果审核说明：</span>
            <div class="info-body">
                {!! $change->checkTestCont !!}
            </div>
        </li>
        <li></li>
        <li><span>审批者：</span>
            <p>
                {{ThirdCallHelper::getStuffName($change->feasibilityUserId)}}
            </p>
        </li>
        <li><span>审批时间：</span>
            <p>{{$change->checkTestTs}}</p></li>
    </ul>
</div>
