<?php

namespace Fuel\Tasks;

use Config;
use Cli;
use DB;
use DBUtil;

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
                        created_at text,
                        updated_at text
                    );'
                )->execute();
    }

    /**
     * SQLiteデータベースの初期設定
     */
    public static function init_db() {
        $result = false;
        if (DBUtil::table_exists('cdnrequest')) {
            $ready = Cli::prompt('drop table cdnrequet! are you ready?', array('Y', 'n'));
            if ($ready == 'Y') {
                DBUtil::drop_table('cdnrequest');
                $result = self::create_table();
            }
        } else {
            $result = self::create_table();
        }
        if ($result) {
            Cli::write('Database initialized.');
        }
    }

    public static function check_cdn($cdn) {
        return Config::get('cdn.' . $cdn, false);
    }

    public static function check_account($cdn, $account) {
        return Config::get('cdn.' . $cdn . '.' . $account, false);
    }

    public static function error_message($text) {
        Cli::error($text);
    }

    public static function run($p1, $p2 = null, $command = null, $opt1 = null, $opt2 = null, $opt3 = null) {
        $cdn = false;
        $account = false;
        if (self::check_cdn($p1)) {
            // CDNサービスが存在
            $quiet = Cli::option('quiet', false);
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
                        'opt1' => $opt1
                    );
                    break;
                case 'keycdn':
                    break;
                case 'cloudfront':
                    break;
            }
            $account_config = Config::get('cdn.' . $cdn . '.' . $p2);
            if ($account_config) {
                // 有効なサービス名でアカウント設定あり
                $account_name = $p2;
                $cls_name = "\\Cdn\\" . $cdn;
                $cdn_service = new $cls_name($account_name, $account_config);
                $result = $cdn_service->delegate($command, $options);
                var_dump($result);
            } else {
                // アカウント設定が無効
                self::error_message('Account settings mismatch.');
            }
        } else {
            // CDNサービス名以外のコマンド
            switch ($p1) {
                case 'version':
                    break;
                case 'api-helth':
                    self::api_helth();
                    break;
                case 'debug':
                    self::show_config();
                    break;
                case 'init-db':
                    self::init_db();
                    break;
                default:
                    // コマンドが見つからない
                    self::error_message('Command not found.');
                    break;
            }
        }
    }

    public static function api_helth() {
        $all_config = Config::get('cdn');
        $msgs = array();
        foreach ($all_config as $cdn_name => $cdn_val) {
            foreach ($cdn_val as $account => $config) {
                $cls_name = "\\cdn\\" . $cdn_name;
                $service = new $cls_name($account, $config);
                $msgs[$cdn_name][$account] = $service->check_helth();
            }
        }
        var_dump($msgs);
    }

    public static function show_config() {
        var_dump(Config::get('cdn'));
        var_dump(Config::get('cdn.akamai.account1.authentication'));
        $test = new \Cdn\Akamai('account1', Config::get('cdn.akamai.account1'));
        var_dump($test->get_cdn());
    }

}
