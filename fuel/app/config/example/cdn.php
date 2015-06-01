<?php
return array(
   'akamai' => array(
       'account1' => array(
           'enable' => true,
           'authentication' => array(
               'user' => 'hogehoge@hogefuga.jp',
               'password' => 'hogehoge',
           ),
           'notification' => array(
               'type' => 'mail',
               'addr' => 'hogehoge@hogefuga.jp',
               'title' => 'report-akamai',
           ),
       ),
   ),
   'keycdn' => array(
       'account1' => array(
           'enable' => true,
           'authentication' => array(
               'user' => 'hogehoge@hogefuga.jp',
               'password' => 'hogehoge',
           ),           
           'notification' => array(
               'type' => 'slack',
               'token' => 'XXXX-YYYY-XZZZZ-ZZXXYY-000-AAAA',
               'title' => 'report-keycdn',
               'channel' => 'cdn-channel',
           ),
       ),
   ),
   'cloudfront' => array(
       'account1' => array(
           'enable' => true,
           'authentication' => array(
               'user' => '<AccessKey>',
               'password' => '<SecretKey>',
           ),           
           'notification' => array(
               'type' => 'hipchat',
               'token' => '<HipChat v2 API token>',
               'target' => '@user or room-id',
           ),
       ),
   ),
);