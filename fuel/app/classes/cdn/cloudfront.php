<?php

namespace Cdn;

use Config;

class CloudFront {

    private $account;
    private $authentication;

    public function __construct($account) {
        $this->authentication = Config::get('cdn.cliudfront.' . $account . '.authentication');
        $this->account = $account;
    }

    public function get_cdn() {
        $cls = get_class($this);
        return strtolower(substr($cls, strrpos($cls, "\\") + 1, strlen($cls)));
    }

    public function check_helth() {
        return true;
    }

}
