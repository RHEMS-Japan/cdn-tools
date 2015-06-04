<?php

namespace Cdn;

use Config;
use Webapi;

class CloudFront {

    private $user;
    private $authentication;

    public function __construct($user, $config) {
        $this->user = $user;
        $this->authentication = $config['authentication'];
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
            /*
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
             */
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
        }
        return $result;
    }

}
