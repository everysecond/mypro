{{--问题分析表单   实施解决问题中--}}
<div class="job-reply" style="margin-top: -10px">
    {{csrf_field()}}
    <input id="route" type="hidden" value="saveCheck">
    <label style="color:#1bcbab;">确定问题执行方案说明:</label>
    <div class="reply-editor" style="width: 82%">
        <textarea id="msg" style="height: 140px;width:720px" name="issueResult" class="approvalValidate"
                  data-name="问题执行方案">{!! $issue->issueResult !!}</textarea>
    </div>
    <div style="margin-top: -40px"><pre> </pre></div>
    <div> <input  type="checkbox" id="trigger" name="triggerAnalysis" value="触发变更">触发变更</div>
    <div class="reply-opt">
        <input value="保存" class="reply-btn btnSub" name="onlySave" type="button"/>
        <input name="processVar" value="{!! $stepForm['variable'] !!}" type="hidden"/>
        {!! $stepForm['form'] !!}
        @if($stepForm['variable'] == "")
            {!! $stepForm['submit'] !!}
        @endif
    </div>
</div>