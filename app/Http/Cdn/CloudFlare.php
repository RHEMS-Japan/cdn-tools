<?php
namespace App\Http\Cdn;

use Config;
use Validation;
use Webapi;
use Request;
use App\History;
use GuzzleHttp\Client;
date_default_timezone_set('Asia/Tokyo');

class CloudFlare {
    private $user;
    private $email;
    private $apikey;

    public function __construct($user, $config) {
        $this->user = $user;
        $this->email = $config['authentication']['email'];
        $this->apikey = $config['authentication']['apiKey'];
    }

    public function get_cdn() {
        $cls = get_class($this);
        return strtolower(substr($cls, strrpos($cls, "\\") + 1, strlen($cls)));
    }

    public function purge_all($id) {
        $json = '{"purge_everything" : true}';
        $result = $this->purge_request($id, $json);
        return $result;
    }

    public function purge_urls($id, $urls) {
        $json = '{"files" : '.$urls.'}';
        $result = $this->purge_request($id, $json, $urls);
        return $result;
    }

    public function purge_request($id, $json, $urls="all") {
        $success = false;
        try {
            $response = $this->api_request($id, $json);
            $response = json_decode($response->getBody(), true);
            $result = array(
                'api-request' => $id,
                'api-response' => $response
            );
            $this->insert_to_db($id, $urls, $response);
            $success = true;
            $result['api-response-json'] = json_encode($response);
            $result['message'] = 'cloudflare(' . $this->user . '):: Purge request accepted - [' . $response['result']['id'] . ']';
        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
        }
        $result['success'] = $success;
        return $result;
    }

    public function api_request($id, $json) {
        $params = $this->make_api_request_params($id);
        $method = $params['method'];
        $endpoint = $params['endpoint'];
        $headers = $params['headers'];
        $option = array(
            'headers' => $headers,
            'body' => $json
        );
        $client = new Client();
        $response = $client->request($method, $endpoint, $option);
        return $response;
    }
    
    public function make_api_request_params($id) {
        $method = "POST";
        $endpoint = "https://api.cloudflare.com/client/v4/zones/".$id."/purge_cache";
        $headers = array(
            'Content-Type' => 'application/json',
            'X-Auth-Key' => $this->apikey,
            'X-Auth-Email' => $this->email
        );
        $params = array(
            'method' => $method,
            'endpoint' => $endpoint,
            'headers' => $headers
        );
        return $params;
    }

    public function insert_to_db($id, $all_or_urls, $response) {
        $cdnRequest = new History;
        $cdnRequest->cdnType = $this->get_cdn();
        $cdnRequest->accountName = $this->user;
        $cdnRequest->estimatedSeconds = 0;
        $cdnRequest->progressUri = '';
        $cdnRequest->purgeId = $response['result']['id'];
        $cdnRequest->supportId = $id;
        $cdnRequest->httpStatus = "Purge request was sent";
        $cdnRequest->detail = $all_or_urls;
        $cdnRequest->pingAfterSeconds = 0;
        $cdnRequest->created_at = date('Y-m-d H:i:s');
        $cdnRequest->updated_at = date('Y-m-d H:i:s');
        $cdnRequest->done = 1;  //cloudflare doesn't have status that purge was finished or not finished
        $cdnRequest->save();
    }

    public function transform_urls_array_to_string($urls) {
        $urls = explode("\n", $urls);
        $urls = array_map('trim', $urls);
        $urls = array_filter($urls, 'strlen');
        $array_urls = array_values($urls);
        $string_urls = '[';
        foreach($array_urls as $url) {
            $string_urls = $string_urls.'"'.$url.'",';
        }
        $string_urls = rtrim($string_urls, ',');
        $string_urls = $string_urls.']';
        return $string_urls;
    }

    public function delegate($options) {
        $result = array(
            'success' => false,
        );
        if ($options['urls'] == "") {
            $id = $options['selected_default'];
            $result = $this->purge_all($id);
        } else {
            $urls = $options['urls'];
            $string_urls = $this->transform_urls_array_to_string($urls);
            $result = $this->purge_urls($id, $string_urls);
        } 
        return $result;
    }
}
