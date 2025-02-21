<?php

namespace App\service;

use Pusher\Pusher;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PusherService
{
    public ?Pusher $pusher;
    private static ?PusherService $instance = null;

    public function __construct(ParameterBagInterface $params)
    {
        if (self::$instance) {
            return self::$instance;
        }

        $this->pusher = new Pusher(
            $params->get('pusher_key'),
            $params->get('pusher_secret'),
            $params->get('pusher_app_id'),
            [
                'cluster' => $params->get('pusher_cluster'),
                'useTLS' => true
            ]
        );

        self::$instance = $this;
    }

    public static function trigger(string $channel, string $event, array $data)
    {
        if (self::$instance) {
            return self::$instance->pusher->trigger($channel, $event, $data);
        }
    }
}
