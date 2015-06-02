<?php

namespace Cdn;

use Config;
use Validation;
use Webapi;

class Akamai_Validator {

    public function _validation_validfile($val) {
        return file_exists($val);
    }

}

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
        $val->add_callable(new Akamai_Validator);
        switch ($type) {
            case 'purge':
                $val->add('opt1', 'CP code')->add_rule('required')
                        ->add_rule('valid_string', array('numeric'));
                break;
            case 'purge-url':
                $val->add('opt1', 'ARL File')->add_rule('required')
                        ->add_rule('validfile');
                break;
        }
        return $val;
    }

    public function read_arl($fn) {
        $arls = array();
        $arlfile = file($fn);
        foreach ($arlfile as $ari) {
            $buff = trim($ari);
            if (!empty($buff)) {
                if (substr($buff, 0, 1) != '#') {
                    $arls[] = $buff;
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
                    $arls = $this->read_arl($options['opt1']);
                    if (empty($arls)) {
                        // ARLファイルが空だった
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
                    // パラメータ妥当性検証失敗
                    $result['error'] = 'Invalid parameter.';
                }
                break;
            case 'check':
                $result = $this->check_queue();
                break;
            default:
                // コマンドが見つからない
                $result['error'] = 'Command not found.';
                break;
        }
        return $result;
    }

    public function purge_request($req) {
        $success = false;
        $webapi = new Webapi();
        $result = $webapi->execute(self::PURGE_ENDPOINT, $this->config, 'POST', $req);
        if ($result['http_code'] == 201) {
            // Request accepted.
            $json = json_decode($result['contents'], true);
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
        }
        return array(
            'success' => $success,
            'data' => $result,
        );
    }

    public function check_request() {
        $entries = \Model\CdnRequest::find('all', array(
                    'where' => array(
                        array('cdnType', $this->get_cdn()),
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
                    $complete[] = $entry;
                } else {
                    $incomplete[] = $entry;
                }
            }
        }
        return array(
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
        return $queue;
    }

    public function get_cdn() {
        $cls = get_class($this);
        return strtolower(substr($cls, strrpos($cls, "\\") + 1, strlen($cls)));
    }

    public function check_helth() {
        $result = false;
        $webapi = new Webapi();
        $result = $webapi->execute(self::PURGE_QUEUE, $this->config, 'GET');
        if ($result['http_code'] == 200) {
            $result = true;
        }
        return $result;
    }

}
