<div class="info-top">
    <p>问题审核信息</p>
</div>
<div class="info-content">
    <ul>
        <li><span class="issue-title">问题经理：</span>
            <p>{{ThirdCallHelper::getStuffName($issue->issueCheckUserId)}}</p></li>
        <li><span class="issue-title">问题分派时间：</span>
            <p>{{$issue->assignTs}}</p>
        </li>
        <li><span class="issue-title">问题经理审核意见：</span></li>
        <div class="info-content">
            <li>
                <div class="info-body">
                    {!! $issue->issueCheckOpinion !!}
                </div>
            </li>
        </div>
        <li>
            <span class="issue-title">是否需要进行分析：</span>
            <span>{{$whetherAnalysis}}</span>
        </li>
    </ul>
</div>