<?php

namespace Cdn;

use Config;

class KeyCdn {

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
            'success' =>true,
            'health' => true,
        );
    }

}
