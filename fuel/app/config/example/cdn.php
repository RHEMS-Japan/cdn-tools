<?php

return array(
    'akamai' => array(
        'account1' => array(
            'defaults' => array('00000000'),
            'authentication' => array(
                'user' => 'hogehoge@hogefuga.jp',
                'password' => 'hogehoge',
            ),
            'notification' => array(
                'type' => 'hipchat',
                'token' => '<HipChat V2 API room notification token>',
                'room' => 'room-id',
            ),
        ),
    ),
    'keycdn' => array(
        'account1' => array(
            'defaults' => array('zone-name'),
            'zonelist' => APPPATH.'/config/zones.json',
            'authentication' => array(
                'user' => 'hogehoge@hogefuga.jp',
                'password' => 'hogehoge',
            ),
            'notification' => array(
                'type' => 'slack',
                'token' => 'xoxp-xxxxxxxxx-eeeeee-yyyyyyy-zzzzzz',
                'team' => 'Team',
                'channel' => 'channel-name',
            ),
        ),
    ),
);
