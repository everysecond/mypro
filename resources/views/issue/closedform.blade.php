{{--问题审核意见   问题解决并关闭--}}
<div class="job-reply" style="margin-top: -10px">
    {{csrf_field()}}
    <input id="route" type="hidden" value="saveClose">

    <label style="color:#1bcbab;">问题关闭理由:</label>
    <div class="reply-editor" style="width: 82%">
        <textarea id="msg" style="height: 140px;width:720px" name="issueCloseReason" class="approvalValidate"
                  data-name="问题关闭理由">{!! $issue->issueCloseReason !!}</textarea>
    </div>
    <div class="reply-opt">
        <input value="保存" class="reply-btn btnSub" name="onlySave" type="button"/>
        <input name="processVar" value="{!! $stepForm['variable'] !!}" type="hidden"/>
        {!! $stepForm['form'] !!}{!! $stepForm['submit'] !!}
    </div>
</div>

