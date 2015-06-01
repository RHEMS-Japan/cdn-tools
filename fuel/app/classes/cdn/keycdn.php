<?php

namespace Cdn;

use Config;

class KeyCdn {

    private $account;
    private $authentication;

    public function __construct($account) {
        $this->authentication = Config::get('cdn.keycdn.' . $account . '.authentication');
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
