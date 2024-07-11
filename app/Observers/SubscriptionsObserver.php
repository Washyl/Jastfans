<?php

namespace App\Observers;

use App\Helpers\PaymentHelper;
use App\Model\Subscription;
use App\Services\TwitterService;
use Illuminate\Support\Facades\Log;

class SubscriptionsObserver
{
    protected $twitterService;

    public function __construct(TwitterService $twitterService)
    {
        $this->twitterService = $twitterService;
    }

    /**
     * Listen to the Subscription deleting event.
     *
     * @param Subscription $subscription
     * @return void
     */
    public function deleting(Subscription $subscription)
    {
        try{
            $paymentHelper = new PaymentHelper();
            $cancelSubscription = $paymentHelper->cancelSubscription($subscription);
            if(!$cancelSubscription) {
                Log::error("Failed cancelling subscription for id: ".$subscription->id);
            }
        } catch (\Exception $exception) {
            Log::error("Failed cancelling subscription for id: ".$subscription->id." error: ".$exception->getMessage());
        }
    }

    /**
     * Listen to the Subscription created event.
     *
     * @param Subscription $subscription
     * @return void
     */
    public function created(Subscription $subscription)
    {
        $this->tweetNewSubscriber($subscription);
    }


    /**
     * Tweet about the new subscriber
     *
     * @param Subscription $subscription
     * @return void
     */
    private function tweetNewSubscriber(Subscription $subscription)
    {
        $creator = $subscription->creator;

        // Check if the creator has connected their Twitter account
        if ($creator->twitter_token && $creator->twitter_token_secret) {
            $subscriber = $subscription->subscriber;
            $message = "Exciting news! I just got a new subscriber. Thank you for your support!";
            
            try {
                $result = $this->twitterService->tweet($creator, $message);
                if ($result['success']) {
                    Log::info("Tweeted about new subscriber successfully for creator: {$creator->id}");
                } else {
                    Log::error("Failed to tweet about new subscriber for creator: {$creator->id}. Error: " . json_encode($result['error']));
                }
            } catch (\Exception $e) {
                Log::error("Exception when tweeting about new subscriber: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Listen to the Subscription updating event.
     *
     * @param Subscription $subscription
     * @return void
     */
    public function updating(Subscription $subscription) {
        //
    }
}
