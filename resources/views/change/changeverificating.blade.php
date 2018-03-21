<div class="job-reply" style="margin-top: -10px">
    <label style="color:#1bcbab;">验证结果说明:</label>
    <input id="route" type="hidden" value="saveVerificating">
    <div class="reply-editor" style="width: 82%">
        <textarea class="approvalValidate" id="msg" style="height: 140px;width:720px" data-name="验证结果说明"
                  name="checkResultCont">{!! $change->changeResult !!}</textarea>
    </div>
    <div style="width:70%;margin-top: 15px">
        <label>验证结果：</label>
        <select class="form-control validate" name="checkResult" style="width:282px;display: inline-block">
            <option value="">请选择</option>
            @foreach($checkResultArr as $item)
                <option value="{{$item->Code}}">{{$item->Means}}</option>
            @endforeach
        </select>
    </div>
    <div class="reply-opt">
        <input type="button" class="reply-btn btnSub" name="onlySave" value="保存"/>
        <input name="processVar" value="{!! $stepForm['variable'] !!}" type="hidden"/>
        <div @if($change->checkUserId != $user->Id)
             style="display: none;"
             @else
             style="display: inline;"
                @endif>
            {!! $stepForm['form'] !!}{!! $stepForm['submit'] !!}
        </div>
    </div>
</div>