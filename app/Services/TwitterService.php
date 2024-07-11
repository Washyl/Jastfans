<?php

namespace App\Services;

use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class TwitterService
{
    public function tweet($user, $message)
    {
        Log::info("Attempting to tweet for user: {$user->id}");

        // Check if tokens are set
        if (empty($user->twitter_token) || empty($user->twitter_token_secret)) {
            Log::error("Twitter tokens are not set for user: {$user->id}");
            return [
                'success' => false,
                'error' => 'Twitter tokens are not set.'
            ];
        }

        $connection = new TwitterOAuth(
            config('services.twitter.client_id'),
            config('services.twitter.client_secret'),
            $user->twitter_token,
            $user->twitter_token_secret
        );
        $connection->setApiVersion(2);
        // Log the endpoint and parameters for debugging
        Log::info("Using endpoint: statuses/update with message: {$message}");

        $tweet = $connection->post("tweets", ["text" => $message]);

        $httpCode = $connection->getLastHttpCode();
        Log::info("Twitter API response code: " . $httpCode);

        if (in_array($httpCode, [200, 201])) {
            Log::info("Tweet successful. Tweet ID: {$tweet->id}");
            return [
                'success' => true,
                'tweet' => $tweet
            ];
        } else {
            $error = $connection->getLastBody();
            Log::error("Tweet failed. HTTP Code: {$httpCode}. Error: " . json_encode($error, JSON_PRETTY_PRINT));
            return [
                'success' => false,
                'error' => $error,
                'httpCode' => $httpCode
            ];
        }
    }

    public function tweetWithMedia($user, $message, $media = [])
    {
        Log::info("Attempting to tweet for user: {$user->id}");

        if (empty($user->twitter_token) || empty($user->twitter_token_secret)) {
            Log::error("Twitter tokens are not set for user: {$user->id}");
            return [
                'success' => false,
                'error' => 'Twitter tokens are not set.'
            ];
        }

        $connection = new TwitterOAuth(
            config('services.twitter.client_id'),
            config('services.twitter.client_secret'),
            $user->twitter_token,
            $user->twitter_token_secret
        );

        $mediaIds = [];
        $tempFiles = [];
        // Upload media using v1.1 API
        if (!empty($media)) {
            $connection->setApiVersion(1.1);
            foreach ($media as $mediaPath) {
                // Get the full path of the file
                $fullPath = $mediaPath;
                Log::info("filePath ".$fullPath);
                Log::info("filePath Exist ".Storage::exists($fullPath));
                try {
                    if (Storage::exists($fullPath)) {
                        $tempFilePath = tempnam(sys_get_temp_dir(), 'tweet_media_');
                        file_put_contents($tempFilePath, Storage::get($fullPath));
                        $tempFiles[] = $tempFilePath;
                        Log::info($tempFilePath);
                        $uploadedMedia = $connection->upload('media/upload', ['media' => $tempFilePath]);
    
                        Log::info("uploadedMedia: " . json_encode($uploadedMedia, JSON_PRETTY_PRINT));
                        if (isset($uploadedMedia->media_id_string)) {
                            $mediaIds[] = $uploadedMedia->media_id_string;
                        } else {
                            Log::error("Failed to upload media: " . json_encode($uploadedMedia));
                        }
                    } else {
                        Log::error("Media file not found: {$fullPath}");
                    }
                } catch (\Exception $exception) {
                    Log::error("Failed to upload media: " . json_encode($exception));
                }
            }
        }
        Log::info("changing api version ");
        // Switch to v2 API for posting tweet
        $connection->setApiVersion(2);

        $tweetParams = ["text" => $message];
        if (!empty($mediaIds)) {
            $tweetParams['media'] = ['media_ids' => $mediaIds];
        }

        Log::info("Using v2 endpoint: tweets with message: {$message} and media IDs: " . implode(', ', $mediaIds));

        $tweet = $connection->post("tweets", $tweetParams, ['jsonPayload' => true]);

        $httpCode = $connection->getLastHttpCode();
        Log::info("Twitter API response code: " . $httpCode);
        // Clean up temporary files
        foreach ($tempFiles as $tempFile) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
        if (in_array($httpCode, [200, 201])) {
            Log::info("Tweet successful. Tweet ID: " . ($tweet->data->id ?? 'Unknown'));
            return [
                'success' => true,
                'tweet' => $tweet
            ];
        } else {
            $error = $connection->getLastBody();
            Log::error("Tweet failed. HTTP Code: {$httpCode}. Error: " . json_encode($error, JSON_PRETTY_PRINT));
            return [
                'success' => false,
                'error' => $error,
                'httpCode' => $httpCode
            ];
        }
    }
}