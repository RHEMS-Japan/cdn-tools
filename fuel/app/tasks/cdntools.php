<?php

namespace Fuel\Tasks;

use Config;
use Cli;
use DB;
use DBUtil;
use Cdn\Akamai;

class cdntools {

    CONST CLI_VERSION = '0.01';

    public static function create_table() {
        /**
         * SQLite3ではmigrateに失敗するためCREATE文を直接発行
         */
        return DB::query(
                        'CREATE TABLE cdnrequest(
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
    public static function initdb() {
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
            $notify = Cli::option('notify', 'none');
            $cdn = $p1;
            switch ($cdn) {
                case 'akamai':
                    // サービス固有オプション
                    $domain = Cli::option('domain', 'production');
                    $action = Cli::option('action', 'invalidate');
                    break;
                case 'keycdn':
                    break;
                case 'cloudfront':
                    break;
            }

            if (self::check_account($cdn, $p2)) {
                // 有効なサービス名でアカウント設定あり
                $account = $p2;
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
                    break;
                case 'debug':
                    self::show_config();
                    break;
                default:
                    // コマンドが見つからない
                    self::error_message('Command not found.');
                    break;
            }
        }
    }

    public static function show_config() {
        var_dump(Config::get('cdn'));
        var_dump(Config::get('cdn.akamai.account1.authentication'));
        $test = new \Cdn\Akamai('account1');
        var_dump($test->get_cdn());
    }

}
