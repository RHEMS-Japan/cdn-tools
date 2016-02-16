<?php

namespace Cdn;

use Config;
use Messaging\Hipchat;
use Messaging\Slack;

class Notification {
    function __construct($config, $msgs) {
        $result = array(
            'success' => false,
        );
        $token = $config['token'];
        switch ($config['type']) {
            case 'slack':
                $channel = $config['channel'];
                $msg_api = new Slack($token);
                $result = $msg_api->send_message($channel, $msgs);
                break;
            case 'hipchat':
                $room = $config['room'];
                $msg_api = new Hipchat($token);
                $result = $msg_api->send_message($room, $msgs);
                break;
        }
        return $result;
    }

    
}


