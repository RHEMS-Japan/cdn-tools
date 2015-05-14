<?php

class Webapi {

    protected $config = array();

    public function __construct($config = array()) {
        $this->config = $config;
    }

    public function execute($url, $config = null, $method = 'GET', $params = null) {
        if (is_null($config)) {
            $config = $this->config;
        }
        $ch = curl_init($url);
        if (isset($config['authentication']['type'])) {
            switch ($config['authentication']['type']) {
                case 'basic':
                    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                    $credentials = $config['authentication']['user'] . ':' . $config['authentication']['password'];
                    curl_setopt($ch, CURLOPT_USERPWD, $credentials);
                    break;
                default:
            }
        }
        $headers = array();
        if (isset($config['content-type'])) {
            switch ($config['content-type']) {
                case 'json':
                    $headers[] = 'Content-type: application/json';
                    break;
                case 'xml':
                    $headers[] = 'Content-type: application/xml';
                    break;
                case 'octed-stream':
                    $headers[] = 'Content-type: application/octed-stream';
                    break;
                default:
                    $headers[] = 'Content-type: ' . $config['content-type'];
            }
        }
        if (isset($config['ssl-verify'])) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $config['ssl-verify']);
        }
        if (isset($config['headers'])) {
            if (is_array($config['headers'])) {
                $headers = array_merge($headers, $config['headers']);
            }
        }

        if (isset($config['http-version'])) {
            switch ($config['http-version']) {
                case '1.1':
                    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                    break;
                default:
            }
        }
        if (isset($method)) {
            switch ($method) {
                case 'GET':
                    // Nothing do.
                    break;
                case 'POST':
                    curl_setopt($ch, CURLOPT_POST, true);
                    break;
                case 'PUT':
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                    break;
                default:
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                    break;
            }
        }
        if (!empty($params)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        $result = curl_exec($ch);
        $result_opt = curl_getinfo($ch);
        curl_close($ch);
        return array(
            'http_code' => $result_opt['http_code'],
            'contents' => $result,
            'http_info' => $result_opt,
        );
    }

}
