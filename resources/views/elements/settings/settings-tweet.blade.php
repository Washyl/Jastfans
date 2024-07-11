
@if(!Auth::user()->isTwitterConnected())
    <a class="btn btn-primary btn-block rounded mr-0 capitalize" href="{{route('twitter.auth')}}">{{__('Connect to twitter')}}</a>
@else 
<div class="mb-3 card py-3 mt-3">
    <div class="custom-control custom-switch custom-switch">
        <div class="ml-3">
            <input type="checkbox" class="custom-control-input" id="auto_post" {{Auth::user()->auto_post ? 'checked' : ''}}>
            <label class="custom-control-label" for="auto_post">{{__('Enable Automatic Tweet')}}</label>
        </div>
        <div class="ml-3 mt-2">
            <small class="fa-details-label">{{__("If enabled, we will publish tweet automatically.")}}</small>
        </div>
    </div>
</div>
<a class="btn btn-primary btn-block rounded mr-0 capitalize" href="{{route('twitter.revoke')}}">{{__('Disconnect from twitter')}}</a>
@endif