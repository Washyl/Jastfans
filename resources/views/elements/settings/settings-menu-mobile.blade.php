<div class="mt-3 inline-border-tabs text-bold">
    <nav class="nav nav-pills nav-justified">
        @foreach($availableSettings as $route => $setting)
            <a class="nav-item nav-link {{$activeSettingsTab == $route ? 'active' : ''}}" href="{{route('my.settings',['type'=>$route])}}">
                <div class="d-flex justify-content-center">
                    @if ($setting['icon'] == 'logo-twitter')
                        @include('elements.icon',['icon'=>$setting['icon'],'centered'=>'false','classes'=>'mr-3','variant'=>'medium'])
                    @else
                        @include('elements.icon',['icon'=>$setting['icon'].'-outline','centered'=>'false','classes'=>'mr-3','variant'=>'medium'])
                    @endif
                </div>
            </a>
        @endforeach
    </nav>
</div>
