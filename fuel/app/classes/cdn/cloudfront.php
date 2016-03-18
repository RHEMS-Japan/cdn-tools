<?php

namespace Cdn;

use Config;
use Validation;
use Webapi;
use Aws\CloudFront\CloudFrontClient;

class CloudFront {

    private $user;
    private $authentication;

    public function __construct($user, $config) {
        $this->user = $user;
        $this->authentication = $config['authentication'];
        $this->client = CloudFrontClient::factory(array('credentials' => array(
                        'key' => $config['authentication']['user'],
                        'secret' => $config['authentication']['password'],
                    ))
        );
    }

    public function validate($type) {
        $val = Validation::forge();
        switch ($type) {
            case 'purge':
                $val->add('opt1', 'Distribution code')->add_rule('required');
                break;
            case 'purge-url':
                $val->add('opt1', 'Distribution code')->add_rule('required');
                $val->add('opt2', 'URL Pattern')->add_rule('required');
                break;
        }
        return $val;
    }

    public function get_cdn() {
        $cls = get_class($this);
        return strtolower(substr($cls, strrpos($cls, "\\") + 1, strlen($cls)));
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
        $webapi = new Webapi();
        $complete = array();
        $incomplete = array();
        foreach ($entries as $entry) {
            $list_invalidations = $this->client->listInvalidations(array(
                // DistributionId is required
                'DistributionId' => $entry->supportId,
            ));
            foreach ($list_invalidations["Items"] as $item) {
                if ($entry->purgeId == $item['Id']) {
                    if ($item['Status'] == 'Completed') {
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
        }
        $all = count($complete) + count($incomplete);
        return array(
            'success' => true,
            'message' => count($complete) . '/' . $all . ' is processed.',
            'incomplete' => $incomplete,
            'complete' => $complete,
        );
    }

    public function purge_request($request) {
        $success = false;
        try {
            $response = $this->client->createInvalidation(array(
                'DistributionId' => $request['dist'],
                'Paths' => array(
                    'Quantity' => 1,
                    'Items' => $request['pattern'],
                ),
                'CallerReference' => microtime(),
            ));
            $result = array(
                'api-request' => $request,
                'api-response' => $response,
            );
            // Request accepted.
            $cdnRequest = new \Model\CdnRequest();
            $cdnRequest->cdnType = $this->get_cdn();
            $cdnRequest->accountName = $this->user;
            $cdnRequest->estimatedSeconds = 0;
            $cdnRequest->progressUri = '';
            $cdnRequest->purgeId = $response['Id'];
            $cdnRequest->supportId = $request['dist'];
            $cdnRequest->httpStatus = $response['Status'];
            $cdnRequest->detail = json_encode($response['InvalidationBatch']);
            $cdnRequest->pingAfterSeconds = 0;
            $cdnRequest->created_at = date('Y-m-d H:i:s');
            $cdnRequest->updated_at = date('Y-m-d H:i:s');
            $cdnRequest->done = 0;
            $cdnRequest->save();
            $success = true;
            $result['api-response-json'] = json_encode($response);
            $result['message'] = 'cloudfront(' . $this->user . '):: Purge request accepted - [' . $response['Id'] . ']';
        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
        }
        $result['success'] = $success;
        return $result;
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
                    $req = array(
                        'type' => 'cpcode',
                        'dist' => $options['opt1'],
                        'pattern' => array('/*',)
                    );
                    $result = $this->purge_request($req);
                } else {
                    // パラメータ妥当性検証失敗
                    $result['error'] = 'Invalid parameter.';
                }
                break;
            case 'purge-url':
                if ($this->validate($command)->run($options)) {
                    $req = array(
                        'type' => 'cpcode',
                        'dist' => $options['opt1'],
                        'pattern' => array($options['opt2'])
                    );
                    $result = $this->purge_request($req);
                } else {
                    // パラメータ妥当性検証失敗
                    $result['error'] = 'Invalid parameter.';
                }
                break;
        }
        return $result;
    }

}
