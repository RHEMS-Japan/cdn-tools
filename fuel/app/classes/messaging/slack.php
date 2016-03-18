<?php

namespace Messaging;

use Webapi;

class Slack {

    const
            ChannelsList_URL = 'https://slack.com/api/channels.list',
            ChatPostMessage_URL = 'https://slack.com/api/chat.postMessage';

    private $_config;
    private $_channels;

    public function __construct($token) {
        $this->_config['token'] = $token;
        $this->_channels = $this->channels_list();
    }

    public function api_wrapper($url, $option = array(), $method = 'GET') {
        $params = array(
            'token' => $this->_config['token'],
        );
        $params = array_merge($params, $option);
        $config = array(
            'ssl-verify' => false,
        );
        $webapi = new Webapi();
        $result = $webapi->execute($url, $config, $method, $params);
        $json = json_decode($result['contents'], true);
        if ($json['ok'] !== true)
            throw new \FuelException($json['error']);
        return $json;
    }

    public function channels_list() {
        $result = $this->api_wrapper(self::ChannelsList_URL);
        return $result['channels'];
    }

    public function get_channel_id($channel_name) {
        $channel_id = false;
        foreach ($this->_channels as $channel) {
            if ($channel['name'] == $channel_name) {
                $channel_id = $channel['id'];
                break;
            }
        }
        return $channel_id;
    }

    public function send_message($channel_name, $text, $opt = array()) {
        $result = array();
        $channel_id = $this->get_channel_id($channel_name);
        if ($channel_id === false) {
            throw new \FuelException('invalid channel name.');
        }
        $params = array(
            'channel' => $channel_id,
            'text' => $text,
            'username' => 'cdntools',
        );
        $params = array_merge($params, $opt);
        try {
            $json = $this->api_wrapper(self::ChatPostMessage_URL, $params);
            $result = array(
                'success' => true,
                'json' => $json,
                'message' => 'done',
            );
        } catch (\Exception $e) {
            $result = array(
                'success' => false,
                'json' => $json,
                'error' => $e->getMessage(),
            );
        }
        return $result;
    }

}
