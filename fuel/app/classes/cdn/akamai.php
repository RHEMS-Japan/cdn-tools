<?php

namespace Cdn;

use Config;

class Akamai {

    const PURGE_BASE = 'https://api.ccu.akamai.com';
    const PURGE_ENDPOINT = 'https://api.ccu.akamai.com/ccu/v2/queues/default';
    const PURGE_QUEUE = 'https://api.ccu.akamai.com/ccu/v2/queues/default';

    private $account;
    private $authentication;

    public function __construct($account) {
        $this->authentication = Config::get('akamai.cdn.' . $account . '.authentication');
        $this->account = $account;
    }

    public function purge_request($req) {
        $success = false;
        $webapi = new Webapi();
        $result = $webapi->execute(self::PURGE_ENDPOINT, $this->authentication, 'POST', $req);
        if ($result['http_code'] == 201) {
            // Request accepted.
            $json = json_decode($result['contents'], true);
            $cdnRequest = new Model_CdnRequest();
            $cdnRequest->cdnType = $this->get_cdn();
            $cdnRequest->accountName = $this->account;
            $cdnRequest->estimatedSeconds = $json['estimatedSeconds'];
            $cdnRequest->progressUri = $json['progressUri'];
            $cdnRequest->purgeId = $json['purgeId'];
            $cdnRequest->supportId = $json['supportId'];
            $cdnRequest->httpStatus = $json['httpStatus'];
            $cdnRequest->detail = $json['detail'];
            $cdnRequest->pingAfterSeconds = $json['pingAfterSeconds'];
            //$cdnRequest->created_at = date('Y-m-d H:i:s');
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
        $entries = Model_CdnRequest::find('all', array(
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
            $result = $webapi->execute(self::PURGE_BASE . $entry->progressUri, $this->authentication, 'GET');
            if ($result['http_code'] == 200) {
                $json = json_decode($result['contents'], true);
                if ($json['purgeStatus'] == 'Done') {
                    $entry->done = 1;
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
        $result = $webapi->execute(self::PURGE_QUEUE, $this->authentication, 'GET');
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

}
