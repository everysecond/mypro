<li>
    <span>验证结果说明： </span>
    <div class="info-body">
        {!! $change->checkResultCont !!}
    </div>
</li>
<li></li>
<li>
    <span>结果验证：{{\Itsm\Http\Helper\ThirdCallHelper::getDictMeans('验证结果','checkResult',$change->checkResult)}} </span>
</li>
<li></li>
<li><span>验证人：</span>
    <p>
        {{ThirdCallHelper::getStuffName($change->checkUserId)}}
    </p>
</li>
<li><span>验证时间：{{$change->checkTs}}</span>
</li>