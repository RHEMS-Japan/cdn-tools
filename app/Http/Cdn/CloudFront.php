<?php
namespace App\Http\Cdn;

use Config;
use Validation;
use Webapi;
use Request;
use Aws\CloudFront\CloudFrontClient as CloudFrontClient;
use App\History;
date_default_timezone_set('Asia/Tokyo');

class CloudFront {
    private $user;
    private $authentication;

    public function __construct($user, $config) {
        $this->user = $user;
        $this->authentication = $config['authentication'];
        $this->client = new CloudFrontClient([
            'credentials' => [
                'key' => $config['authentication']['accessKeyId'],
                'secret' => $config['authentication']['secretAccessKey'],
            ],
            'region' => 'us-east-1',
            'version' => '2018-11-05'
        ]);
    }

    public function get_cdn() {
        $cls = get_class($this);
        return strtolower(substr($cls, strrpos($cls, "\\") + 1, strlen($cls)));
    }

    public function check_request($account) {
        $entries = History::where('cdnType', 'cloudfront')
                          ->where('accountName', $account)
                          ->where('done', 0)
                          ->orderBy('created_at', 'desc')
                          ->get();
        $complete = [];
        $incomplete = [];
        foreach ($entries as $entry) {
            $list_invalidations = $this->client->listInvalidations([
                // DistributionId is required
                'DistributionId' => $entry['supportId']
            ]);
            foreach ($list_invalidations['InvalidationList']['Items'] as $item) {
                if ($entry->purgeId == $item['Id']) {
                    if ($item['Status'] == 'Completed') {
                        $entry->done = 1;
                        $entry->updated_at = date('Y-m-d H:i:s');
                        $entry->httpStatus = "Completed";
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
            $response = $this->client->createInvalidation([
                'DistributionId' => $request['dist'],
                'InvalidationBatch' => [
                    'Paths' => [
                        'Quantity' => 1,
                        'Items' => $request['pattern'],
                    ],
                    'CallerReference' => microtime(),
                ]
            ]);
            $result = [
                'api-request' => $request,
                'api-response' => $response,
            ];
            // Request accepted.
            $cdnRequest = new History;
            $cdnRequest->cdnType = $this->get_cdn();
            $cdnRequest->accountName = $this->user;
            $cdnRequest->estimatedSeconds = 0;
            $cdnRequest->progressUri = '';
            $cdnRequest->purgeId = $response['Invalidation']['Id'];
            $cdnRequest->supportId = $request['dist'];
            $cdnRequest->httpStatus = $response['Invalidation']['Status'];
            $cdnRequest->detail = json_encode($response['Invalidation']['InvalidationBatch']);
            $cdnRequest->pingAfterSeconds = 0;
            $cdnRequest->created_at = date('Y-m-d H:i:s');
            $cdnRequest->updated_at = date('Y-m-d H:i:s');
            $cdnRequest->done = 0;
            $cdnRequest->save();
            $success = true;
            $result['api-response-json'] = json_encode($response);
            $result['message'] = 'cloudfront(' . $this->user . '):: Purge request accepted - [' . $response['Invalidation']['Id'] . ']';
        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
        }
        $result['success'] = $success;
        return $result;
    }

    public function delegate($options) {
        $result = [
            'success' => false,
        ];
        if ($options['urls'] == "") {
            $urls = ['/*'];
        } else {
            $urls = $options['urls'];
            $urls = explode("\n", $urls);
            $urls = array_map('trim', $urls);
            $urls = array_filter($urls, 'strlen');
            $urls = array_values($urls);
        } 
        $req = [
            'type' => 'cpcode',
            'dist' => $options['selected_default'],
            'pattern' => $urls
        ];
        $result = $this->purge_request($req);
        return $result;
    }
}
