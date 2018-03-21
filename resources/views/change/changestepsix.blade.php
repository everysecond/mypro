<div class="info-top">
    <p>变更发布结果</p>
</div>
<div class="info-content">
    <ul>
        <li><span>实施结果：{{ThirdCallHelper::getDictMeans('验证结果','checkResult',$change->implementResult)}}</span>
        </li>
        <li><span>完成时间：</span>
            <p>
                {{$change->actualTs}}
            </p>
        </li>
        <li><span>实施情况说明：</span>
            <div class="info-body">
                {!! $change->implementCont !!}
            </div>
        </li>
        <li></li>
        @if($statusStep>7)
            @include("change/changestepseven")
        @endif
    </ul>
</div>
