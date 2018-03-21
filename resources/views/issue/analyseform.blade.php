{{--问题分析表单   分析问题及解决方案制定中--}}
<div class="job-reply" style="margin-top: -10px">
    {{csrf_field()}}
    <input id="route" type="hidden" value="saveAnalysis">
    <label style="color:#1bcbab;">问题根本原因描述（问题分析描述）:</label>
    <div class="reply-editor" style="width: 82%">
        <textarea id="msg" style="height: 140px;width:720px" name="issueAnalysis" class="contentValidate"
                  data-name="问题根本原因">{!! $issue->issueAnalysis !!}</textarea>
    </div>
    <div class="reply-editor" style="width: 82%">
            <label style="color:#1bcbab;">问题解决方案描述:</label>
            <textarea class="contentValidate" id="msg1" style="height: 140px;" data-name="问题解决方案"
                      name="issueSolution">{!! $issue->issueSolution !!}</textarea>
    </div>
    <div class="reply-opt" style="margin-top: 45px">
        <input value="保存" class="reply-btn btnSub" name="onlySave" type="button"/>
        <input name="processVar" value="{!! $stepForm['variable'] !!}" type="hidden"/>
        {!! $stepForm['form'] !!}{!! $stepForm['submit'] !!}
    </div>
</div>