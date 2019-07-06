<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Config;
use App\History;
use App\Http\Cdn\CloudFront as CloudFront;
use App\Http\Messaging\Notification as Notification;

class Update extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'update';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'purgeが完了したか確認する';

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
    $count = History::where('done', 0)->count();
    if($count>0) {
      $cloudfront_config = Config::get('cdn', false)['cloudfront'];
      foreach($cloudfront_config as $account => $config) {
        if ($config) {
          $notification_config = $config['notification'];
          $cdn = new CloudFront($account, $config);
          $result = $cdn->check_request($account);
        } else {
          $result = array(
            'success' => 0
          );
        }
        if ($notification_config && $result['success']) {
          $n = new Notification($notification_config, $result['message']);
        }
      }
    }
  }
}
