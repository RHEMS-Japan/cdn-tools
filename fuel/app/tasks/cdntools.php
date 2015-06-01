<?php

namespace Fuel\Tasks;

use Config;
use Cli;
use DB;
use DBUtil;

class cdntools {

    public static function create_table() {
        /**
         * SQLite3ではmigrateに失敗するためCREATE文を直接発行
         */
        return DB::query(
                    'CREATE TABLE cdnrequest(
                        id integer primary key autoincrement,
                        cdnType text,
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

}
