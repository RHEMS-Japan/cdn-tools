<?php

class Presenter_Root_Purge extends Presenter {

    public function view() {
        $this->service = $this->request()->param('service', false);
        $this->account = $this->request()->param('account', false);
        $account_config = Config::get('cdn.' . $this->service . '.' . $this->account, false);
        $this->defaults = $account_config['defaults'];
        switch ((string) $this->service) {
            case 'akamai':
                $this->service_label = 'Akamai';
                $this->purge_label = 'CP Code';
                $this->purge_url_label = 'ARL/URL(s)';
                break;
            case 'keycdn':
                $this->service_label = 'KeyCDN';
                $this->purge_label = 'Zone ID';
                $this->purge_url_label = 'URL(s)';
                break;
            case 'cloudfront':
                $this->service_label = 'CloudFront';
                $this->purge_label = 'Distribution ID';
                $this->purge_url_label = 'Pattern(s)';
                break;
        }
        /*
          $account_config = Config::get('cdn.' . $this->service . '.' . $this->account, false);
          $cls_name = "\\cdn\\" . $this->service;
          $service = new $cls_name($this->account, $config);
         */
        $this->history = \Model\CdnRequest::find('all', array(
                    'where' => array(
                        array('cdnType', $this->service),
                        array('accountName', $this->account),
                    ),
                    'order_by' => array('created_at' => 'desc'),
                    'limit' => 5,
        ));
    }

}
