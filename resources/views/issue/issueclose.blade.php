<div class="info-top">
    <p>问题关闭信息</p>
</div>
<div class="info-content">
    <ul>
        <li><span class="issue-title">关闭人：</span>
            <p>{{ThirdCallHelper::getStuffName($issue->issueCloseUserId)}}</p></li>
        <li><span class="issue-title">关闭时间：</span>
            <p>{{$issue->issueCloseTs}}</p>
        </li>
        <li><span class="issue-title">关闭理由：</span></li>
        <div class="info-content">
            <li>
                <div class="info-body">
                  {!! $issue->issueCloseReason !!}
                </div>
            </li>
        </div>
    </ul>
</div>