<?php

return array(
    'akamai' => array(
        'account1' => array(
            'authentication' => array(
                'user' => 'hogehoge@hogefuga.jp',
                'password' => 'hogehoge',
            ),
            'notification' => array(
                'type' => 'hipchat',
                'token' => '<HipChat v2 API token>',
                'room' => '@user or room-id',
            ),
        ),
    ),
    'keycdn' => array(
        'account1' => array(
            'authentication' => array(
                'user' => 'hogehoge@hogefuga.jp',
                'password' => 'hogehoge',
            ),
            'notification' => array(
                'type' => 'slack',
                'token' => 'xoxp-xxxxxxxxx-eeeeee-yyyyyyy-zzzzzz',
                'team' => 'Team',
                'channel' => 'cdn-channel',
            ),
        ),
    ),
);
