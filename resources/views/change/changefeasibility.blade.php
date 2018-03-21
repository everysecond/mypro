{{--可行性审批form表单--}}
<input id="route" type="hidden" value="saveFeasibility">
<div class="job-detail clearfix">
    {{csrf_field()}}
    <table>
        <div>
                    <textarea name="feasibilityOpinion" minlength="20" id="feasibilityOpinion"
                              placeholder="请填写可行性审批意见" class="approvalValidate" data-name="可行性审批意见"
                              style="width:82%;height: 100px">{!! $change->feasibilityOpinion !!}</textarea>
        </div>
        <td class="bold black" align="center" style="width:10%;height:50px">指定方案制定组
        </td>
        <td>
            <select class="form-control validate" name="proDesigerGroupId" id="proDesigerGroupId"
                    onchange="getStuff(this)" style="width:200px;margin-left: 20px" data-role="change_design">
                <option value="">-请选择-</option>
                @foreach($designDepart as $key=>$item)
                    @if($change->proDesigerGroupId && $change->proDesigerGroupId==$key)
                        <option value="{{$key}}" selected="">{{$item['name']}}</option>
                    @else
                        <option value="{{$key}}">{{$item['name']}}</option>
                    @endif
                    @if(isset($item['child'])&&is_array($item['child']))
                        @foreach($item['child'] as $k=>$value)
                            @if($change->proDesigerGroupId && $change->proDesigerGroupId == $k)
                            <option value="{{$k}}" selected="">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$value}}</option>
                            @else
                                <option value="{{$k}}">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$value}}</option>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </select>
        </td>
        <td class="bold black" align="rigth" style="width:10%;height:50px;">指定方案责任人
        </td>
        <td>
            <select class="form-control validate" name="proDesigerId" id="changeImplementUserId"
                    style="width: 200px;margin-left: 10px">
                <option value="">-请选择-</option>
                @if($change->proDesigerId)
                    <option selected="" value="{{$change->proDesigerId}}">{{\Itsm\Http\Helper\ThirdCallHelper::getStuffName($change->proDesigerId)}}</option>
                @endif
            </select>
        </td>
    </table>
    <table>
        <td class="bold black" align="right" style="width:10%;height:50px;">测试方案组
        </td>
        <td>
            <select class="form-control validate" name="testGroupId" style="width:200px;margin-left: 45px">
                <option value="">-请选择-</option>
                @foreach($testDepart as $key=>$item)
                    @if($change->testGroupId && $change->testGroupId == $key)
                    <option value="{{$key}}" selected="">{{$item['name']}}</option>
                    @else <option value="{{$key}}">{{$item['name']}}</option> @endif
                    @if(isset($item['child'])&&is_array($item['child']))
                        @foreach($item['child'] as $k=>$value)
                            @if($change->testGroupId && $change->testGroupId == $k)
                                <option value="{{$k}}" selected="">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$value}}</option>
                            @else
                                <option value="{{$k}}">
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{$value}}</option>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </select>
        </td>
    </table>
</div>
<div>
</div>

<div class="reply-opt">
    <input value="保存" class="reply-btn btnSub" name="onlySave" type="button"/>
    <input name="processVar" value="{!! $stepForm['variable'] !!}" type="hidden"/>
    {!! $stepForm['form'] !!}
    @if($stepForm['variable'] == "")
        {!! $stepForm['submit'] !!}
    @endif
</div>
<div id="enlargeImage" class="hide">
    <div class="img-wrap">
        <i id="closeLargeImg" class="img-close"></i>
        <img class="large-img" src=""/>
    </div>
</div>