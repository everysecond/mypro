<div class="info-top">
    <p>可行性审批</p>
</div>
<div class="info-content">
    <ul>
        <li><span>审批部门：</span>
            <p>{{ThirdCallHelper::getDepartMeans($change->feasibilityGroupId)}}</p></li>
        <li></li>
        <li><span>审批意见：</span>
            <div class="info-body">{!! $change->feasibilityOpinion !!}</div>
        </li>
        <li></li>
        <li><span>审批者：</span>
            <p>
                {{ThirdCallHelper::getStuffName($change->feasibilityUserId)}}
            </p>
        </li>
        <li><span>审批时间：</span>
            <p>{{$change->feasibilityTs}}</p></li>
    </ul>
</div>