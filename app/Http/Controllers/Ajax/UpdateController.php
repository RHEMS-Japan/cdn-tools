<?php

namespace App\Http\Controllers\Ajax;

use Config;
use App\History;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateRequest;
use App\Http\Cdn\CloudFront as CloudFront;
use App\Http\Messaging\Notification as Notification;

class UpdateController extends Controller
{ 
  public function update(UpdateRequest $request)
  {
    $service = mb_strtolower($request['service']);
    $account = $request['account'];

    $historys = History::where('cdnType', $service)
                  ->where('accountName', $account)
                  ->orderBy('created_at', 'desc')
                  ->limit(5)
                  ->get();
    
    $return = array (
      "service" => $service,
      "account" => $account,
      "historys" => $historys,
    );
    
    return $return;
  }
}
