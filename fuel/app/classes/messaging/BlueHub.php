<?php

namespace Messaging;

use Webapi;

class BlueHub {
    
    const BH_ENDPOINT = 'https://app.b1ue-hub.com/agent/cdn_tool';

    public function send_message($message) {
        return $this->exec_curl(curl_init(self::BH_ENDPOINT), http_build_query($message));
    }

    private function exec_curl($ch, $data) {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
