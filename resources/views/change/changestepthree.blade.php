<div class="info-top">
    <p>变更方案规划及测试</p>
</div>
<div class="info-content">
    <ul>
        <li><span>实施方案：</span>
            <div class="info-body">
                {!! $change->planImplementCont !!}
            </div>
        </li>
        <li></li>
        <li><span>预计完成时间：</span>
            <p>{{$change->changeExpectTs}}</p></li>
        <li><span>变更时间窗口：</span>
            <p>
                {{$change->changeTimeStart}} ~{{$change->changeTimeEnd}}
            </p>
        </li>
        @if($statusStep>=4)
            @include("change/changestepfour")
        @endif
    </ul>
</div>