<div class="job-reply" style="margin-top: -10px">
    <label style="color:#1bcbab;">请填写或上传实施方案:</label>
    <input id="route" type="hidden" value="saveProdesign">
    <div class="reply-editor" style="width: 82%">
        <textarea class="msgValidate" id="msg" style="height: 140px;width:720px" name="planImplementCont"
                  data-name="实施方案">
            {!! $change->planImplementCont !!}</textarea>
    </div>
    <div class="reply-editor" style="width: 82%;margin-top: 5px">
        <div class="reply-editor" style="width: 48%;display: inline-block">
            <label style="color:#1bcbab;">请填写或上传回退方案:</label>
            <textarea class="msgValidate" id="msg1" style="height: 140px;" data-name="回退方案"
                      name="planRollbackCont">{!! $change->planRollbackCont !!}</textarea>
        </div>
        <div class="reply-editor" style="margin-left: 4%;width: calc(48% - 5px);display: inline-block">
            <label style="color:#1bcbab;">请填写变更风险及影响分析:</label>
            <textarea class="msgValidate" id="msg2" style="height: 140px;" data-name="变更风险及影响分析"
                      name="changeEffectCont">{!! $change->changeEffectCont !!}</textarea>
        </div>
    </div>
    <div style="width:100%;margin-top:50px;">
        &nbsp;变更时间窗口:&nbsp;
        <div style="width: 214px;display: inline-block; ">
            <input name='changeTimeStart' id="start" class="form-control layer-date validate"
                   placeholder=" YYYY-MM-DD  hh:mm:ss " style="margin-top: -5px" value="{{$change->changeTimeStart}}">
        </div>&nbsp;&nbsp;To&nbsp;

        <div style="width: 214px;display: inline-block; ">
            <input name='changeTimeEnd' id="end" class="form-control layer-date validate"
                   placeholder=" YYYY-MM-DD  hh:mm:ss " style="margin-top: -5px" value="{{$change->changeTimeEnd}}">
        </div>
        &nbsp;预计完成时间:&nbsp;
        <div style="width: 214px;display: inline-block; ">
            <input name='changeExpectTs' id="expectDate" class="form-control layer-date validate"
                   placeholder=" YYYY-MM-DD  hh:mm:ss " style="margin-top: -5px" value="{{$change->changeExpectTs}}">
        </div>
    </div>
    <div class="reply-opt">
        <input type="button" class="reply-btn btnSub" name="onlySave" value="保存"/>
        <input name="processVar" value="{!! $stepForm['variable'] !!}" type="hidden"/>
        {!! $stepForm['form'] !!}{!! $stepForm['submit'] !!}
    </div>
</div>