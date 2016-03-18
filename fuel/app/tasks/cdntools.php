<?php

namespace Fuel\Tasks;

use Config;
use Cli;
use DB;
use DBUtil;
use Messaging\Notification;
use Messaging\Hipchat;
use Messaging\Slack;

class cdntools {

    CONST CLI_VERSION = '0.01';

    public static function create_table() {
        /**
         * SQLite3ではmigrateに失敗するためCREATE文を直接発行
         */
        return DB::query('CREATE TABLE cdnrequest(
                        id integer primary key autoincrement,
                        cdnType text,
                        accountName text,
                        estimatedSeconds text,
                        progressUri text,
                        purgeId text,
                        supportId text,
                        httpStatus int,
                        detail text,
                        pingAfterSeconds int,
                        done int,
                        created_at int,
                        updated_at int
                    );'
                )->execute();
    }

    /**
     * SQLiteデータベースの初期設定
     */
    public static function init_db() {
        $create = false;
        $result = array();
        if (DBUtil::table_exists('cdnrequest')) {
            $ready = Cli::prompt('drop table cdnrequet! are you ready?', array('Y', 'n'));
            if ($ready == 'Y') {
                DBUtil::drop_table('cdnrequest');
                $create = self::create_table();
            } else {
                
            }
        } else {
            $create = self::create_table();
        }
        if ($create) {
            $result = array(
                'success' => true,
                'message' => 'Database initialized.',
            );
        } else {
            $result = array(
                'success' => false,
                'error' => 'Abort.',
            );
        }
        return $result;
    }

    public static function check_cdn($cdn) {
        return Config::get('cdn.' . $cdn, false);
    }

    public static function check_account($cdn, $account) {
        return Config::get('cdn.' . $cdn . '.' . $account, false);
    }

/*
    public static function nofitication($config, $msgs) {
      $result = array(
      'success' => false,
      );
      $token = $config['token'];
      switch ($config['type']) {
      case 'slack':
      $channel = $config['channel'];
      $msg_api = new Slack($token);
      $result = $msg_api->send_message($channel, $msgs);
      break;
      case 'hipchat':
      $room = $config['room'];
      $msg_api = new Hipchat($token);
      $result = $msg_api->send_message($room, $msgs);
      break;
      }
      return $result;
    }
*/

    public static function check_batch() {
        $all_config = Config::get('cdn');
        $msgs = array();
        foreach ($all_config as $cdn_name => $cdn_val) {
            foreach ($cdn_val as $account => $config) {
                $cls_name = "\\cdn\\" . $cdn_name;
                $service = new $cls_name($account, $config);
                $result = $service->delegate('check', array());
                if (!empty($result['complete'])) {
                    foreach ($result['complete'] as $item) {
                        $msgs[] = $item->message;
                        if ($config['notification']) {
                            // 通知を行う
                            $n = new Notification($config['notification'], $item->message);
                        }
                    }
                } else {
                    if (!empty($result['incomplete'])) {
                        foreach ($result['incomplete'] as $item) {
                            $msgs[] = $item->message;
                        }
                    }
                }
            }
        }
        return array(
            'success' => true,
            'message' => $msgs,
        );
    }

    public static function error_message($text) {
        Cli::error($text);
    }

    public static function run($p1, $p2 = null, $command = null, $opt1 = null, $opt2 = null, $opt3 = null) {
        $result = array('success' => false);
        $cdn = false;
        $account_config = false;
        $notification_config = false;
        $quiet = Cli::option('quiet', false);
        if (self::check_cdn($p1)) {
            // CDNサービスが存在
            $cdn = $p1;
            $options = array();
            switch ($cdn) {
                case 'akamai':
                    // サービス固有オプション
                    $domain = Cli::option('domain', 'production');
                    $action = Cli::option('action', 'invalidate');
                    $options = array(
                        'quiet' => $quiet,
                        'domain' => $domain,
                        'action' => $action,
                        'opt1' => $opt1,
                    );
                    break;
                case 'keycdn':
                    $options = array(
                        'opt1' => $opt1,
                        'opt2' => $opt2,
                    );
                    break;
                case 'cloudfront':
                    $options = array(
                        'opt1' => $opt1,
                        'opt2' => $opt2,
                    );
                    break;
            }
            $account_config = Config::get('cdn.' . $cdn . '.' . $p2, false);
            if ($account_config) {
                // 有効なサービス名でアカウント設定あり
                $account_name = $p2;
                $notification_config = Config::get('cdn.' . $cdn . '.' . $account_name . '.notification', false);
                $cls_name = "\\Cdn\\" . $cdn;
                $cdn_service = new $cls_name($account_name, $account_config);
                $result = $cdn_service->delegate($command, $options);
                if ($notification_config && (!$quiet) && $result['success']) {
                    // 通知を行う
                    $n = new Notification($notification_config, $result['message']);
                }
            } else {
                // アカウント設定が無効
                $result['error'] = 'Account settings mismatch.';
            }
        } else {
            // CDNサービス名以外のコマンド
            switch ($p1) {
                case 'version':
                    $result = array(
                        'success' => true,
                        'message' => 'RHEMS CDN-Tools Version ' . self::CLI_VERSION,
                    );
                    break;
                case 'api-health':
                    $result = self::api_health();
                    break;
                case 'batch':
                    $result = self::check_batch();
                    break;
                case 'debug':
                    $result = self::debug();
                    break;
                case 'init-db':
                    $result = self::init_db();
                    break;
                default:
                    // コマンドが見つからない
                    $result['error'] = 'Command not found.';
                    break;
            }
        }
        if ($result['success']) {
            // 成功時の出力
            if (!$quiet) {
                Cli::write($result['message']);
            }
            exit(0);
        } else {
            // 失敗時の出力
            Cli::error($result['error']);
            exit(-1);
        }
    }

    public static function api_health() {
        $all_config = Config::get('cdn');
        $msgs = array();
        foreach ($all_config as $cdn_name => $cdn_val) {
            foreach ($cdn_val as $account => $config) {
                $cls_name = "\\cdn\\" . $cdn_name;
                $service = new $cls_name($account, $config);
                $result = $service->check_health();
                if ($result['success']) {
                    $msgs[] = "$cdn_name : $account : [OK]";
                } else {
                    $msgs[] = "$cdn_name : $account : [NG]";
                }
            }
        }
        return array(
            'success' => true,
            'message' => $msgs,
        );
    }

    public static function debug() {
        return array(
            'success' => true,
            'message' => 'debug message',
        );
    }

}
