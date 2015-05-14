<?php

class CCU {

    const PURGE_ENDPOINT = 'https://api.ccu.akamai.com/ccu/v2/queues/default';
    const PURGE_PROGRESS = 'https://api.ccu.akamai.com';
    const PURGE_QUEUE = 'https://api.ccu.akamai.com/ccu/v2/queues/default';

    private $config;

    public function __construct() {
        Config::load('ccu', true);
        $this->config = Config::get('ccu');
    }

    public function purge_request($req) {
        $success = false;
        $webapi = new Webapi();
        $result = $webapi->execute(self::PURGE_ENDPOINT, $this->config, 'POST', $req);
        if ($result['http_code'] == 201) {
            // Request accepted.
            $json = json_decode($result['contents'], true);
            $cacheRequest = new Model_CacheRequest();
            $cacheRequest->estimatedSeconds = $json['estimatedSeconds'];
            $cacheRequest->progressUri = $json['progressUri'];
            $cacheRequest->purgeId = $json['purgeId'];
            $cacheRequest->supportId = $json['supportId'];
            $cacheRequest->httpStatus = $json['httpStatus'];
            $cacheRequest->detail = $json['detail'];
            $cacheRequest->pingAfterSeconds = $json['pingAfterSeconds'];
            $cacheRequest->create_at = date('Y-m-d H:i:s');
            $cacheRequest->done = 0;
            $cacheRequest->save();
            $success = true;
        }
        return array(
            'success' => $success,
            'data' => $result,
        );
    }

    public function check_request() {
        $entries = Model_CacheRequest::find('all', array(
                    'where' => array(
                        array('done', 0),
                    ),
                    'order_by' => array('create_at' => 'asc'),
        ));
        $webapi = new Webapi();
        $complete = array();
        $incomplete = array();
        foreach ($entries as $entry) {
            $result = $webapi->execute(self::PURGE_PROGRESS . $entry->progressUri, $this->config, 'GET');
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
        $result = $webapi->execute(self::PURGE_QUEUE, $this->config, 'GET');
        if ($result['http_code'] == 200) {
            $json = json_decode($result['contents'], true);
            $queue = $json['queueLength'];
        }
        return $queue;
    }

}
