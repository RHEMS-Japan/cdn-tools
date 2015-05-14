<?php

namespace Fuel\Tasks;

use Config;
use Cli;
use CCU;

class Luna {

    public static function spad($str) {
        return str_pad($str, 15, ' ', STR_PAD_RIGHT) . ':';
    }

    public static function slackOut($msg) {
        Config::load('slack', true);
        $slack_config = Config::get('slack');
        $token = $slack_config['token'];
        $channel = $slack_config['channel'];
        $title = $slack_config['title'];
        $slackcmd = $slack_config['slackcmd'];
        $cmd = "echo '```$msg```' | $slackcmd -c '$channel' -k '$token' -T '$title'";
        exec($cmd);
    }

    public static function request_message($msg, $data) {
        $output = static::spad('Purge Type') . ($msg['type'] == 'cpcode' ? 'CPCode' : 'ARL') . PHP_EOL;
        $output .= static::spad('Domain') . $msg['domain'] . PHP_EOL;
        $output .= static::spad('Action') . $msg['action'] . PHP_EOL;
        $output .= static::spad('PurgeID') . $data['purgeId'] . PHP_EOL;
        if ($msg['type'] == 'cpcode') {
            $output .= static::spad('CPCode') . $msg['objects'][0] . PHP_EOL;
        } else {
            $output .= static::spad('Objects') . PHP_EOL;
            foreach ($msg['objects'] as $obj) {
                $output .= '   - [' . $obj . ']' . PHP_EOL;
            }
        }
        return $output;
    }

    public static function complete_message($msg) {
        $output = 'Purge ID: ' . ($msg->purgeId) . ' Done.';
        return $output;
    }

    public static function incomplete_message($msg) {
        $output = 'Purge ID: ' . ($msg->purgeId) . ' in progress.';
        return $output;
    }

    public static function queue_message($msg) {
        $output = $msg . ' Request stuck queue.';
        return $output;
    }

    public static function purge($body) {
        $ccu = new CCU();
        $result = $ccu->purge_request(json_encode($body));
        if ($result['success']) {
            $json = json_decode($result['data']['contents'], true);
            return static::request_message($body, $json);
        } else {
            Cli::error($result['data']['contents']);
        }
    }

    public static function purgeUrl($url_file) {
        $slack_opt = Cli::option('slack', false);
        if (file_exists($url_file)) {
            $arls = array();
            $urlfile = file($url_file);
            foreach ($urlfile as $ari) {
                $buff = trim($ari);
                if (!empty($buff)) {
                    if (substr($buff, 0, 1) != '#') {
                        $arls[] = $buff;
                    }
                }
            }
            $type = 'arl';
            $domain = Cli::option('domain', 'production');
            $action = Cli::option('action', 'invalidate');
            $body = array(
                'type' => $type,
                'domain' => $domain,
                'action' => $action,
                'objects' => $arls,
            );
            $msg = static::purge($body);
            Cli::write($msg);
            if ($slack_opt) {
                static::slackOut($msg);
            }
        } else {
            die('ARL file not found.' . PHP_EOL);
        }
    }

    public static function purgeCode($cpcode) {
        $slack_opt = Cli::option('slack', false);
        if ((preg_match('/[0-9]+/', $cpcode)) && ($cpcode > 0)) {
            $type = 'cpcode';
            $domain = Cli::option('domain', 'production');
            $action = Cli::option('action', 'invalidate');
            $body = array(
                'type' => $type,
                'domain' => $domain,
                'action' => $action,
                'objects' => array($cpcode),
            );
            $msg = static::purge($body);
            Cli::write($msg);
            if ($slack_opt) {
                static::slackOut($msg);
            }
        } else {
            die('Invalid cp code.' . PHP_EOL);
        }
    }

    public static function check() {
        $slack_opt = Cli::option('slack', false);
        $ccu = new CCU();
        $result = $ccu->check_request();
        if (!empty($result['complete'])) {
            $msg = array();
            foreach ($result['complete'] as $item) {
                $msg[] = static::complete_message($item);
            }
            Cli::write(implode(PHP_EOL, $msg));
            if ($slack_opt) {
                static::slackOut(implode(PHP_EOL, $msg));
            }
        } else {
            if (!empty($result['incomplete'])) {
                $msg = array();
                foreach ($result['incomplete'] as $item) {
                    $msg[] = static::incomplete_message($item);
                }
                Cli::write(implode(PHP_EOL, $msg));
            }
        }
    }

    public static function check_queue() {
        $slack_opt = Cli::option('slack', false);
        $ccu = new CCU();
        $result = $ccu->check_queue();
        $msg = static::queue_message($result);
        Cli::write($msg);
        if ($slack_opt) {
            static::slackOut($msg);
        }
    }

    public static function run($opt = null) {
        $type = Cli::option('type', 'cpcode');
        switch ($type) {
            case "cpcode":
                static::purgeCode($opt);
                break;
            case "ari":
                static::purgeUrl($opt);
                break;
            default:
                die('Invalid purge type.' . PHP_EOL);
                break;
        }
    }

}
