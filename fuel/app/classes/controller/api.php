<?php

class Controller_Api extends Controller_Rest {

    protected $default_format = 'json';

    public function post_purge() {
        $result = array();
        $service = $this->param('service');
        $account = $this->param('account');
        $account_config = Config::get('cdn.' . $service . '.' . $account, false);
        if ($account_config) {
            $notification_config = Config::get('cdn.' . $service . '.' . $account . '.notification', false);
            $cls_name = "\\Cdn\\" . $service;
            $cdn_service = new $cls_name($account, $account_config);
            $result = $cdn_service->delegate('purge', Input::post());
            if ($notification_config && $result['success']) {
                $n = new \Cdn\Notification($notification_config, $result['message']);
            }
        } else {
            $result['error'] = 'Account settings mismatch.';
        }
        return $this->response($result);
    }

    public function get_queue() {
        $service = $this->param('service');
        $account = $this->param('account');
        $queue = \Model\CdnRequest::find('all', array(
                    'where' => array(
                        array('cdnType', $service),
                        array('accountName', $account),
                    ),
                    'order_by' => array('created_at' => 'desc'),
                    'limit' => 5,
        ));
        return $this->response($queue);
    }

}
