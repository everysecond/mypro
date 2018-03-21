<div class="job-reply" style="margin-top: -10px">
    <label style="color:#1bcbab;">请填写或上传测试报告:</label>
    <input id="route" type="hidden" value="saveTesting">
    <div class="reply-editor" style="width: 88%">
        <textarea id="msg" style="height: 140px;width:720px" class="approvalValidate" name="testCont"
                  data-name="测试报告">{!! $change->testCont !!}</textarea>
    </div>
    <div style="width:100%;margin-top: 30px;">
            变更时间窗口:&nbsp;
        <div style="width: 214px;display: inline-block; ">
            <input name='changeTimeStart' id="start" class="form-control layer-date"
                   placeholder=" YYYY-MM-DD  hh:mm:ss " style="margin-top: -5px" value="{{$change->changeTimeStart}}">
        </div>
        &nbsp;To&nbsp;
        <div style="width: 214px;display: inline-block; ">
            <input name='changeTimeEnd' id="end" class="form-control layer-date"
                   placeholder=" YYYY-MM-DD  hh:mm:ss " style="margin-top: -5px" value="{{$change->changeTimeEnd}}">
        </div>
        &nbsp;预计完成时间:&nbsp;
        <div style="width: 214px;display: inline-block; ">
            <input name='changeExpectTs' id="expectDate" class="form-control layer-date"
                   placeholder=" YYYY-MM-DD  hh:mm:ss " style="margin-top: -5px" value="{{$change->changeExpectTs}}">
        </div>
    </div>
    <div class="reply-opt">
        <input class="reply-btn btnSub" name="onlySave" value="保存" type="button"/>
        <input name="processVar" value="{!! $stepForm['variable'] !!}" type="hidden"/>
        {!! $stepForm['form'] !!}
        @if($stepForm['variable'] == "")
            {!! $stepForm['submit'] !!}
        @endif
    </div>
</div>