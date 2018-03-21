{{--方案规划表单--}}
<div class="job-reply" style="margin-top: -10px">
    <label style="color:#1bcbab;">请填写或上传变更方案规划:</label>
    <input id="route" type="hidden" value="saveProgramme">
    <div class="reply-editor" style="width: 82%">
        <textarea id="msg" style="height: 140px;width:720px" name="changeSchemeCont" class="msgValidate"
                  data-name="方案规划">
            {!! $change->changeSchemeCont !!}</textarea>
    </div>
    <div style="width:100%;margin-top:20px;">
        &nbsp;变更时间窗口:&nbsp;
        <div style="width: 214px;display: inline-block; ">
            <input name='changeTimeStart' id="start" class="form-control layer-date validate"
                   placeholder=" YYYY-MM-DD  hh:mm:ss " style="margin-top: -5px" value="{{$change->changeTimeStart}}">
        </div>
        &nbsp;To&nbsp;
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
        <input value="保存" class="reply-btn btnSub" name="onlySave" type="button"/>
        <input name="processVar" value="{!! $stepForm['variable'] !!}" type="hidden"/>
        {!! $stepForm['form'] !!}{!! $stepForm['submit'] !!}
    </div>
</div>