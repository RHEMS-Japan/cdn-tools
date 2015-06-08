<?php

namespace Cdn;

use Config;
use Validation;
use Webapi;

class Akamai {

    const PURGE_BASE = 'https://api.ccu.akamai.com';
    const PURGE_ENDPOINT = 'https://api.ccu.akamai.com/ccu/v2/queues/default';
    const PURGE_QUEUE = 'https://api.ccu.akamai.com/ccu/v2/queues/default';

    private $user;
    private $config;

    public function __construct($user, $config) {
        $this->user = $user;
        $this->config = $config;
        $this->config['content-type'] = 'json';
    }

    public function validate($type) {
        $val = Validation::forge();
        switch ($type) {
            case 'purge':
                $val->add('opt1', 'CP code')->add_rule('required')
                        ->add_rule('valid_string', array('numeric'));
                break;
            case 'purge-url':
                $val->add('opt1', 'ARL File')->add_rule('required');
                break;
        }
        return $val;
    }

    public function read_arl($fn) {
        $arls = array();
        if (file_exists($fn)) {
            $arlfile = file($fn);
            foreach ($arlfile as $ari) {
                $buff = trim($ari);
                if (!empty($buff)) {
                    if (substr($buff, 0, 1) != '#') {
                        $arls[] = $buff;
                    }
                }
            }
        }
        return $arls;
    }

    public function delegate($command, $options) {
        $result = array(
            'success' => false,
        );
        switch ($command) {
            case 'purge':
                if ($this->validate($command)->run($options)) {
                    $req = array(
                        'type' => 'cpcode',
                        'domain' => $options['domain'],
                        'action' => $options['action'],
                        'objects' => array($options['opt1']),
                    );
                    $result = $this->purge_request(json_encode($req));
                } else {
                    // パラメータ妥当性検証失敗
                    $result['error'] = 'Invalid parameter.';
                }
                break;
            case 'purge-url':
                if ($this->validate($command)->run($options)) {
                    if (is_array($options['opt1'])) {
                        $arls = $options['opt1'];
                    } else {
                        $arls = $this->read_arl($options['opt1']);
                    }
                    if (empty($arls)) {
                        // ARLが空だった
                        $result['error'] = 'Empty ARL(s).';
                    } else {
                        $req = array(
                            'type' => 'arl',
                            'domain' => $options['domain'],
                            'action' => $options['action'],
                            'objects' => $arls,
                        );
                        $result = $this->purge_request(json_encode($req));
                    }
                } else {
                    $result['error'] = 'Invalid parameter.';
                }
                break;
            case 'check':
                $result = $this->check_request();
                break;
            case 'check-queue':
                $result = $this->check_queue();
                break;
            case 'check-health':
                $result = $this->check_health();
            default:
                // コマンドが見つからない
                $result['error'] = 'Command not found.';
                break;
        }
        return $result;
    }

    public function purge_request($request) {
        $success = false;
        $webapi = new Webapi();
        $response = $webapi->execute(self::PURGE_ENDPOINT, $this->config, 'POST', $request);
        $result = array(
            'api-request' => $request,
            'api-response' => $response,
        );
        $json = json_decode($response['contents'], true);
        if ($response['http_code'] == 201) {
            // Request accepted.
            $cdnRequest = new \Model\CdnRequest();
            $cdnRequest->cdnType = $this->get_cdn();
            $cdnRequest->accountName = $this->user;
            $cdnRequest->estimatedSeconds = $json['estimatedSeconds'];
            $cdnRequest->progressUri = $json['progressUri'];
            $cdnRequest->purgeId = $json['purgeId'];
            $cdnRequest->supportId = $json['supportId'];
            $cdnRequest->httpStatus = $json['httpStatus'];
            $cdnRequest->detail = $json['detail'];
            $cdnRequest->pingAfterSeconds = $json['pingAfterSeconds'];
            $cdnRequest->created_at = date('Y-m-d H:i:s');
            $cdnRequest->updated_at = date('Y-m-d H:i:s');
            $cdnRequest->done = 0;
            $cdnRequest->save();
            $success = true;
            $result['api-response-json'] = $json;
            $result['message'] = 'akamai(' . $this->user . '):: Purge request accepted - [' . $json['purgeId'] . ']';
        } else {
            $result['error'] = $json['title'];
        }
        $result['success'] = $success;
        return $result;
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
        $webapi = new Webapi();
        $complete = array();
        $incomplete = array();
        foreach ($entries as $entry) {
            $result = $webapi->execute(self::PURGE_BASE . $entry->progressUri, $this->config, 'GET');
            if ($result['http_code'] == 200) {
                $json = json_decode($result['contents'], true);
                if ($json['purgeStatus'] == 'Done') {
                    $entry->done = 1;
                    $entry->updated_at = date('Y-m-d H:i:s');
                    $entry->save();
                    $entry->message = ($entry->cdnType) . '(' . $entry->accountName . '):: Purge done - [' . ($entry->purgeId) . ']';
                    $complete[] = $entry;
                } else {
                    $entry->message = ($entry->cdnType) . '(' . $entry->accountName . '):: Purge in progress - [' . ($entry->purgeId) . ']';
                    $incomplete[] = $entry;
                }
            }
        }
        $all = count($complete) + count($incomplete);
        return array(
            'success' => true,
            'message' => 'Akamai:: ' . count($complete) . '/' . $all . ' is processed.',
            'incomplete' => $incomplete,
            'complete' => $complete,
        );
    }

    public function check_queue() {
        $queue = 0;
        $webapi = new Webapi();
        $result = $webapi->execute(self::PURGE_QUEUE, $this->config, 'GET');
        if ($result['http_code'] == 200) {
            $json = json_decode($result['contents'], true);
            $queue = $json['queueLength'];
        }
        return array(
            'success' => true,
            'queue' => $queue,
        );
    }

    public function get_cdn() {
        $cls = get_class($this);
        return strtolower(substr($cls, strrpos($cls, "\\") + 1, strlen($cls)));
    }

    public function check_health() {
        $result = false;
        $webapi = new Webapi();
        $result = $webapi->execute(self::PURGE_QUEUE, $this->config, 'GET');
        if ($result['http_code'] == 200) {
            $result = true;
        }
        return array(
            'success' => true,
            'health' => $result,
        );
    }

}
