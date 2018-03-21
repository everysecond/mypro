<div class="job-reply" style="margin-top: -10px">
    <label style="color:#1bcbab;">请填写实施情况说明:</label>
    <input id="route" type="hidden" value="saveImplement">
    <div class="reply-editor" style="width: 82%">
        <textarea class="approvalValidate" id="msg" style="height: 140px;width:720px" data-name="实施情况说明"
                  name="implementCont">{!! $change->implementCont !!}</textarea>
    </div>
    <div style="width:70%;margin-top: 15px">
        <label>实施结果：</label>
        <select class="form-control validate" name="implementResult" style="width:282px;display: inline-block">
            <option value="">请选择</option>
            @foreach($checkResultArr as $item)
                <option value="{{$item->Code}}">{{$item->Means}}</option>
            @endforeach

        </select>
        <label>实际完成时间：</label>
        <div style="width: 214px;display: inline-block; ">
            <input name='actualTs' id="actualTs" class="form-control layer-date validate"
                   placeholder=" YYYY-MM-DD  hh:mm:ss " style="margin-top: -5px" value="{{$change->actualTs}}"
                   onclick="laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss'})" required>
        </div>
    </div>
    <div class="reply-opt">
        <input name="processVar" value="{!! $stepForm['variable'] !!}" type="hidden"/>
        {!! $stepForm['form'] !!}{!! $stepForm['submit'] !!}
    </div>
</div>