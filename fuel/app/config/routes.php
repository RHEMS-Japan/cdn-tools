<?php

return array(
    '_root_' => 'root/index',
    '_404_' => 'root/404',
    'cdn/:service/:account' => 'root/purge',
    'api/queue/:service/:account' => array(
        array('GET', new Route('api/queue')),
    ),
    'api/purge/:service/:account' => array(
        array('POST', new Route('api/purge')),
    ),
);
