{{--问题审核表单   审核中--}}
<div class="job-reply" style="margin-top: -10px">
    {{csrf_field()}}
    <input id="route" type="hidden" value="saveApproval">
    <div class="col-sm-12 issue-reply">
        <div class="issue-choice">
            <label class="issue-analysis" style="color:#1bcbab;">是否需要进行分析:
                <input value="1" type="radio" name="whetherAnalysis"
                       @if($issue->whetherAnalysis == '1')checked="checked" @endif>是
                <input value="0" type="radio" name="whetherAnalysis"
                       @if($issue->whetherAnalysis == '0') checked="checked" @endif>否
            </label>
        </div>
        <div class="issue-manager">
            <label class="bold black manager-label" style="color:#1bcbab;">问题负责人: </label>
            <input type="hidden" id="cid" name="issueChargeUserId" value="{!! $issue->issueChargeUserId !!}">
            <input type="text" style="margin-left: 10px" name="checkUser"
                   class="form-control input-sm validate" id="issueChargeUserId" placeholder="问题负责人"
                   value="{{ThirdCallHelper::getStuffName($issue->issueChargeUserId)}}" autocomplete="off">
            <div class="input-group hiddenDiv" id="hiddenDiv"
                 style="margin-top: -30px;margin-left:88px;background-color: white;width: 180px">
                <input type="text" class="form-control input-sm " id="checkUser" name="check"
                     placeholder="问题负责人" autocomplete="off">
                <div class="input-group-btn">
                    <ul style="max-height: 375px; max-width:200px; overflow: auto;width: auto; transition: all 0.3s ease 0s;"
                        class="dropdown-menu dropdown-menu-right question-manager-list" role="menu"></ul>
                </div>
            </div>
        </div>

    </div>
    <label style="color:#1bcbab;">请填写问题审核意见:</label>
    <div class="reply-editor" style="width: 100%">
        <textarea id="msg" style="height: 140px;width:720px" name="issueCheckOpinion" class="approvalValidate"
                  data-name="问题审核意见">{!!$issue->issueCheckOpinion!!}</textarea>
    </div>
    <div class="reply-opt">
        <input value="保存" class="reply-btn btnSub" name="onlySave" type="button"/>
        <input name="processVar" value="{!! $stepForm['variable'] !!}" type="hidden"/>
        {!! $stepForm['form'] !!}
        @if($stepForm['variable'] == "")
            {!! $stepForm['submit'] !!}
        @endif
    </div>
</div>