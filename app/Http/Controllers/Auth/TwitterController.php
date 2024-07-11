<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TwitterController extends Controller
{
    public function redirectToTwitter()
    {
        return Socialite::driver('twitter')->redirect();
    }

    public function handleTwitterCallback()
    {
        $twitterUser = Socialite::driver('twitter')->user();

        $user = Auth::user();
        $user->update([
            'twitter_id' => $twitterUser->id,
            'twitter_token' => $twitterUser->token,
            'twitter_token_secret' => $twitterUser->tokenSecret,
        ]);

        return redirect('/my/settings/tweet');
    }

    public function revokeAuthorization() {
        $user = Auth::user();
        $user->update([
            'twitter_token' => null,
            'twitter_token_secret' => null,
        ]);
        return redirect('/my/settings/tweet');
    }
}