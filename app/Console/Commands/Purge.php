<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Config;
use App\Http\Cdn\CloudFront as CloudFront;
use App\Http\Cdn\CloudFlare as CloudFlare;
use App\Http\Messaging\Notification as Notification;

class Purge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purge {cdn_name} {account_name} {zone_id_or_distribution_id} {path?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'purge cache of cdn-service';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      $service = strtolower($this->argument("cdn_name"));
      $account = $this->argument("account_name");
      $selected_default = $this->argument("zone_id_or_distribution_id");
      $path = $this->argument("path");
      if (is_null($path)) {
        $path = "";
      }
      
      $request = [
        "service" => $service,
        "account" => $account,
        "selected_default" => $selected_default, 
        "urls" => $path
      ];
    
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

      $return = [
        "service" => $service,
        "account" => $account,
        "account_config" => $account_config,
        "notification_config" => $notification_config,
        "cdn_service" => $cdn_service,
        "result" => $result
      ];

      echo "Purge request was sent successfully !!\n";
    }
}
