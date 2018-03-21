<div class="info-content">
    <li><span class="issue-title">问题分析专家：</span>
        <p>{{ThirdCallHelper::getStuffName($issue->issueChargeUserId)}}</p>
    </li>
    <li><span class="issue-title">提交解决方案时间：</span>
        <p>{{$issue->issueSolutionTs}}</p>
    </li>

    <li><span class="issue-title">问题根本原因描述：</span></li>
    <div class="info-content">
        <li>
            <div class="info-body">
                {!! $issue->issueAnalysis !!}
            </div>
        </li>
    </div>
    <li><span class="issue-title">问题解决方案描述：</span>
    </li>
    <div class="info-content">
        <li>
            <div class="info-body">
                {!! $issue->issueSolution !!}
            </div>
        </li>
    </div>
    <li><span class="issue-title">信息附件：</span>
        <div class="info-body">
            @if(!empty($uploadFiles))
                {!! $uploadFiles !!}
            @else
                暂无！
            @endif
        </div>
    </li>
    <li></li>
</div>