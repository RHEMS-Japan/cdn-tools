<?php

namespace App\Http\Controllers;

use Config;
use App\History;
class RootController extends Controller
{
  public function index()
  {
    $accounts = Config::get('cdn');
    return view('index', compact('accounts')); 
  }

  public function purge($service, $account)
  {
    $data = array(
      'service' => $service,
      'account' => $account,
    );
    $historys = History::where('cdnType', $service)
                       ->where('accountName', $account)
                       ->orderBy('created_at', 'desc')
                       ->limit(5)
                       ->get();
    $info = $this->make_info($data);
    return view('purge', compact('historys', 'info'));
  }

  private function make_info($data)
  {
    $service = $data['service'];
    $account = $data['account'];
    $account_config = Config::get('cdn', false)[$service][$account];
    $defaults = $account_config['defaults'];
    switch ((string) $service) {
      case 'cloudflare':
        $service_label = 'CloudFlare';
        $purge_label = 'Zone ID';
        $purge_url_label = 'ARL/URL(s)';
        $explain_path = 'full path';
        break;
      case 'cloudfront':
        $service_label = 'CloudFront';
        $purge_label = 'Distribution ID';
        $purge_url_label = 'Pattern(s)';
        $explain_path = 'path after /';
        break;
    }
    
    $info = array(
      'service' => $service,
      'account' => $account,
      'account_config' => $account_config,
      'defaults' => $defaults,
      'service_label' => $service_label,
      'purge_label' => $purge_label,
      'purge_url_label' => $purge_url_label,
      'explain_path' => $explain_path
    );
    
    return $info;
  }
}
