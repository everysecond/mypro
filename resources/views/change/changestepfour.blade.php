<li><span>回退方案：</span>
    <div class="info-body">
        {!! $change->planRollbackCont !!}
    </div>
</li>
<li></li>
<li><span>变更风险及影响风险：</span>
    <div class="info-body">
        {!! $change->changeEffectCont !!}
    </div>
</li>
<li></li>
<li><span>测试结果：</span>
    <div class="info-body">
        {!! $change->testCont !!}
    </div>
</li>
<li>
</li>
<li><span>相关文档说明及附件：</span>
    <div class="info-body">
        @if(!empty($uploadFiles))
            {!! $uploadFiles !!}
        @else
            暂无！
        @endif
    </div>
</li>
<li>
</li>