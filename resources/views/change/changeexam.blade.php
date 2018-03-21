<div class="job-reply" style="margin-top: -10px">
    <label style="color:#1bcbab;">方案及测试结果审核说明:</label>
    <input id="route" type="hidden" value="saveExamining">
    <div class="reply-editor" style="width: 82%">
        <textarea class="approvalValidate" id="msg" style="height: 140px;width:720px" data-name="测试结果审核说明"
                  name="checkTestCont">{!! $change->checkTestCont !!}</textarea>
    </div>
    <div style="width:70%;margin-top: 15px">
        <label>指定变更实施组：</label>
        <select class="form-control validate" name="changeImplementGroupId" onchange="getStuff(this)"
                id="changeImplementGroupId" style="width:30%;display: inline-block" data-role="change_release" required>
            <option value="">请选择</option>
            @foreach($releaseDepart as $key=>$item)
                @if($change->changeImplementGroupId && $change->changeImplementGroupId == $key)
                <option value="{{$key}}" selected>{{$item['name']}}</option>
                @else <option value="{{$key}}">{{$item['name']}}</option> @endif
                @if(isset($item['child'])&&is_array($item['child']))
                    @foreach($item['child'] as $k=>$value)
                            @if($change->changeImplementGroupId && $change->changeImplementGroupId == $k)
                        <option value="{{$k}}" selected>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$value}}</option>
                            @else <option value="{{$k}}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$value}}</option> @endif
                    @endforeach
                @endif
            @endforeach
        </select>
        <label>指定变更实施人：</label>
        <select class="form-control validate" name="changeImplementUserId"
                id="changeImplementUserId" style="width:30%;display: inline-block" required>
            <option value="">请选择</option>
            @if($change->changeImplementUserId)
                <option value="{{$change->changeImplementUserId}}" selected=''>
                    {{ThirdCallHelper::getStuffName($change->changeImplementUserId)}}</option>
            @endif
        </select>
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
<script>

</script>