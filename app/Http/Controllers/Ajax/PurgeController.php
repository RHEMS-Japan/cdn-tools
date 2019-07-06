<?php

namespace App\Http\Controllers\Ajax;

use Illuminate\Http\Request;
use Config;
use App\Http\Requests\PurgeRequest;
use App\Http\Controllers\Controller;
use App\Http\Cdn\CloudFront as CloudFront;
use App\Http\Cdn\CloudFlare as CloudFlare;
use App\Http\Messaging\Notification as Notification;

class PurgeController extends Controller
{
  public function purge(PurgeRequest $request)
  {
    $service = mb_strtolower($request["service"]);
    $account = $request["account"];
    $account_config = Config::get('cdn', false)[$service][$account];

    if ($account_config) {
      $notification_config = $account_config['notification'];
      switch ($service) {
        case 'cloudfront':  
          $cdn_service = new CloudFront($account, $account_config);
          break;
        case 'cloudflare':
          $cdn_service = new CloudFlare($account, $account_config);
          break;
      } 
      $result = $cdn_service->delegate($request);
      if ($notification_config && $result['success']) {
        $n = new Notification($notification_config, $result['message']);
      }
    } else {
      $result['error'] = 'Account settings mismatch.';
    }

    $return = array(
      "service" => $service,
      "account" => $account,
      "account_config" => $account_config,
      "notification_config" => $notification_config,
      "cdn_service" => $cdn_service,
      "result" => $result
    );

    return $return;
  }
}
