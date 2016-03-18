<?php

namespace Cdn;

use Config;
use Validation;
use Webapi;

class KeyCdn {

    const
            API_BASE = 'https://api.keycdn.com/';

    private $user;
    private $config;
    private $zones;

    public function __construct($user, $config) {
        $this->user = $user;
        $this->config = $config;
        $this->config['content-type'] = 'json';
        $this->zones = $this->get_zonelist();
    }

    public function validate($type) {
        $val = Validation::forge();
        switch ($type) {
            case 'purge':
                $val->add('opt1', 'ZoneName')->add_rule('required');
                break;
            case 'purge-url':
                $val->add('opt1', 'ZoneName')->add_rule('required');
                $val->add('opt2', 'URL(s)')->add_rule('required');
                break;
        }
        return $val;
    }

    public function get_cdn() {
        $cls = get_class($this);
        return strtolower(substr($cls, strrpos($cls, "\\") + 1, strlen($cls)));
    }

    public function read_url($fn) {
        $urls = array();
        if (file_exists($fn)) {
            $urlfile = file($fn);
            foreach ($urlfile as $uri) {
                $buff = trim($uri);
                if (!empty($buff)) {
                    if (substr($buff, 0, 1) != '#') {
                        $urls[] = $buff;
                    }
                }
            }
        }
        return $urls;
    }

    public function get_zonefile() {
        $result = false;
        if (array_key_exists('zonelist', $this->config)) {
            if (file_exists($this->config['zonelist'])) {
                $result = json_decode(file_get_contents($this->config['zonelist']), true);
            }
        }
        return $result;
    }

    public function get_zonelist() {
        $result = array();
        $zonelist = $this->get_zonefile();
        if ($zonelist) {
            $result = $zonelist['data']['zones'];
        } else {
            $webapi = new Webapi();
            $api_result = $webapi->execute(self::API_BASE . 'zones.json', $this->config, 'GET');
            $json = json_decode($api_result['contents'], true);
            if ($json['status'] == 'success') {
                $result = $json['data']['zones'];
            }
        }
        return $result;
    }

    public function get_zoned_id($name) {
        $result = false;
        foreach ($this->zones as $item) {
            if ($item['name'] == $name) {
                $result = $item['id'];
                break;
            }
        }
        return $result;
    }

    public function purge_request($request, $urls = null) {
        $success = false;
        $zone_id = $this->get_zoned_id($request);
        if ($zone_id != FALSE) {
            $webapi = new Webapi();
            if (is_null($urls)) {
                $response = $webapi->execute(self::API_BASE . 'zones/purge/' . $zone_id . '.json', $this->config, 'GET');
            } else {
                $params = array(
                    'urls' => $urls,
                );
                $response = $webapi->execute(self::API_BASE . 'zones/purge/' . $zone_id . '.json', $this->config, 'DELETE', http_build_query($params));
            }
            $result = array(
                'api-request' => $request,
                'api-response' => $response,
            );
            $json = json_decode($response['contents'], true);
            if ($json['status'] == 'success') {
                // Request accepted.
                $cdnRequest = new \Model\CdnRequest();
                $cdnRequest->cdnType = $this->get_cdn();
                $cdnRequest->accountName = $this->user;
                $cdnRequest->estimatedSeconds = 0;
                $cdnRequest->progressUri = '';
                $cdnRequest->purgeId = md5(uniqid('keycdn'));
                $cdnRequest->supportId = '';
                $cdnRequest->httpStatus = 200;
                $cdnRequest->detail = $json['description'];
                $cdnRequest->pingAfterSeconds = 0;
                $cdnRequest->created_at = date('Y-m-d H:i:s');
                $cdnRequest->updated_at = date('Y-m-d H:i:s');
                $cdnRequest->done = 0;
                $cdnRequest->save();
                $success = true;
                $result['api-response-json'] = $json;
                $result['message'] = 'keycdn(' . $this->user . '):: Purge request accepted - [' . $cdnRequest->purgeId . ']';
            } else {
                $result['error'] = $json['description'];
            }
        } else {
            $result['error'] = 'Invalid zone name.';
        }
        $result['success'] = $success;
        return $result;
    }

    public function check_health() {
        return array(
            'success' => true,
            'health' => true,
        );
    }

    public function check_request() {
        $entries = \Model\CdnRequest::find('all', array(
                    'where' => array(
                        array('cdnType', $this->get_cdn()),
                        array('accountName', $this->user),
                        array('done', 0),
                    ),
                    'order_by' => array('created_at' => 'asc'),
        ));
        $complete = array();
        $incomplete = array();
        foreach ($entries as $entry) {
            $entry->done = 1;
            $entry->updated_at = date('Y-m-d H:i:s');
            $entry->save();
            $entry->message = ($entry->cdnType) . '(' . $entry->accountName . '):: Purge done - [' . ($entry->purgeId) . ']';
            $complete[] = $entry;
        }
        $all = count($complete) + count($incomplete);
        return array(
            'success' => true,
            'message' => count($complete) . '/' . $all . ' is processed.',
            'incomplete' => $incomplete,
            'complete' => $complete,
        );
    }

    public function delegate($command, $options) {
        $result = array(
            'success' => false,
        );
        switch ($command) {
            case 'check':
                $result = $this->check_request();
                break;
            case 'purge':
                if ($this->validate($command)->run($options)) {
                    $result = $this->purge_request($options['opt1']);
                } else {
                    // パラメータ妥当性検証失敗
                    $result['error'] = 'Invalid parameter.';
                }
                break;
            case 'purge-url':
                if ($this->validate($command)->run($options)) {
                    if (is_array($options['opt2'])) {
                        $urls = $options['opt2'];
                    } else {
                        $urls = $this->read_url($options['opt2']);
                    }
                    if (empty($urls)) {
                        // URLが空だった
                        $result['error'] = 'Empty URL(s).';
                    } else {
                        $result = $this->purge_request($options['opt1'], $urls);
                    }
                } else {
                    // パラメータ妥当性検証失敗
                    $result['error'] = 'Invalid parameter.';
                }
                break;
            case 'zone-list':
                $this->get_zonelist();
                break;
            default:
                // コマンドが見つからない
                $result['error'] = 'Command not found.';
                break;
        }
        return $result;
    }

}
