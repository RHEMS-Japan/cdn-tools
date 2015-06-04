<?php

namespace Messaging;

use Webapi;

class Hipchat {
    
    const API_V2_ENDPOINT = 'https://api.hipchat.com/v2/';
    
    public function send_message($token, $room, $msg) {
        $success = false;
        $api = new Webapi();
        $url = self::API_V2_ENDPOINT . 'room/' . $room . '/notification';
        $config = array(
            'content-type' => 'json',
            'ssl-verify' => false,
            'headers' => array(
                'Authorization: Bearer ' . $token,
            ),
        );
        $params= array(
            'message' => $msg,
        );
        $api_result = $api->execute($url, $config, 'POST', json_encode($params));
        if ($api_result['http_code'] == 204) {
            $success = true;
        }
        return array(
            'success' => $success,
        );
    }
}
