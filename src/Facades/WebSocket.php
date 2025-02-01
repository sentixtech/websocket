<?php

namespace SentixTech\WebSocket\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;

class WebSocket extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'websocket';
    }

    /**
     * Create a new WebSocket channel
     * 
     * @param string $channelName
     * @return bool
     */
    public static function createChannel(string $channelName)
    {
        try {
            $websocket = app('websocket');
            return $websocket->createChannel($channelName);
        } catch (\Exception $e) {
            Log::error('WebSocket channel creation error', [
                'channel' => $channelName,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Subscribe to a WebSocket channel
     * 
     * @param resource $client
     * @param string $channelName
     * @param int|null $userId
     * @return bool
     */
    public static function subscribe($client, string $channelName, ?int $userId = null)
    {
        try {
            $websocket = app('websocket');
            return $websocket->subscribeToChannel($client, $channelName, $userId);
        } catch (\Exception $e) {
            Log::error('WebSocket subscription error', [
                'channel' => $channelName,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Unsubscribe from a WebSocket channel
     * 
     * @param resource $client
     * @param string $channelName
     * @param int|null $userId
     * @return bool
     */
    public static function unsubscribe($client, string $channelName, ?int $userId = null)
    {
        try {
            $websocket = app('websocket');
            return $websocket->unsubscribeFromChannel($client, $channelName, $userId);
        } catch (\Exception $e) {
            Log::error('WebSocket unsubscription error', [
                'channel' => $channelName,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Broadcast a message to a channel
     * 
     * @param string $channelName
     * @param mixed $message
     * @param array $options
     * @return int
     */
    public static function broadcast(string $channelName, $message, array $options = [])
    {
        try {
            $websocket = app('websocket');
            return $websocket->broadcast($channelName, $message, $options);
        } catch (\Exception $e) {
            Log::error('WebSocket broadcast error', [
                'channel' => $channelName,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Emit a generic event
     * 
     * @param string $eventName
     * @param mixed $data
     * @param array $options
     * @return int
     */
    public static function emit(string $eventName, $data, array $options = [])
    {
        try {
            $websocket = app('websocket');
            return $websocket->emit($eventName, $data, $options);
        } catch (\Exception $e) {
            Log::error('WebSocket emit error', [
                'event' => $eventName,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get number of subscribers in a channel
     * 
     * @param string $channelName
     * @return int
     */
    public static function subscribers(string $channelName)
    {
        try {
            $websocket = app('websocket');
            return $websocket->getChannelSubscribers($channelName);
        } catch (\Exception $e) {
            Log::error('WebSocket subscribers count error', [
                'channel' => $channelName,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
}
