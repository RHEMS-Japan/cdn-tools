<?php
namespace App\Http\Messaging;

use GuzzleHttp\Client;

class Notification {
  function __construct($config, $msgs)
  {
    $params = $this->make_params($config, $msgs);
    $method = $params['method'];
    $url = $params['url'];
    $option = $params['option'];
    $client = new Client();
    $client->request($method, $url, $option);
  }   
  public function make_params($config, $msgs)
  {
    $post = 'POST';
    switch ($config['type']) {
      case 'slack':
        $url = 'https://slack.com/api/chat.postMessage';
        $token = $config['token'];
        $channel = $config['channel'];
        $params = array(
          'method' => $post,
          'url' => $url,
          'option' => array(
            'form_params' => array(  
              'token' => $token,
              'channel' => $channel,
              'text' => $msgs
            )
          )
        );
        break;
      
      case 'chatwork':
        $roomId = $config['roomId'];
        $url = 'https://api.chatwork.com/v2/rooms/'.$roomId.'/messages';
        $token = $config['token'];
        $params = array(
          'method' => $post,
          'url' => $url,
          'option' => array(
            'headers' => array(
              'Content-Type' => 'application/x-www-form-urlencoded',
              'X-ChatWorkToken' => $token,
            ),
            'form_params' => array(
              'body' => $msgs
            )
          )    
        );
        break;
    }
    return $params;
  }
}



// 参考として

/*別ファイルを作ってここで名前空間に追加する*/
/*
const SLACK_HOST = "https://slack.com/api/chat.postMessage";
const CHATWORK_HOST = "https://api.chatwork.com/v2/rooms/roomId/messages";
const X_FORM_URLENCODE = "application/x-www-form-urlencode";
const CONTENT_TYPE = "Content-Type";
const CONFIG_TOKEN = "token";
const SLACK_TOKEN = "token";
const CHANNEL = "channel";
const TEXT = "text";
const ROOMID = "roomId";
const HTTP_POST = 'post';

namespace App\Http\Messaging;
namespace App\Const\SlackC;
SlackC::SLACK_HOST;

use GuzzleHttp\Client;

class Notification {
  public $config;
  public $msgs;
  function __construct($config, $msgs)
  {
    $this->config = $config;
    $this->msgs = $msgs;
  }   

  public function getParams()
  {
    $method = self::HTTP_POST;
    switch ($this->config['type']) {
      case 'slack':
        $url = self::SLACK_HOST;
        $token = $config[self::CONFIG_TOKEN];
        $channel = $config[self::CHANNEL];
        $headers = [
          self::CONTENT_TYPE => self::X_FORM_URLENCODE,
        ];
        $bodys = [
          self::SLACK_TOKEN => $token,
          self::CHANNEL => $channel,
          self::TEXT => $text,
        ];
        $params = $this->makeParam($method, $url, $headers, $bodys);
        break;
    }
    return $params;
  }

  /**
   * ChatWorkのURLを置換する
   *
   * @param string $roomId
   * @param hogeType fuga
   * @return string $replaceUrl
   * @todo なにかリファクタとか残ってたら
   *
  public function replaceChatworkHostToRoomId(string $roomId): string
  {
    $replacUrl = str_replace(self::ROOMID, $roomId, self::CHATWORK_HOST);
    return $replaceUrl;
  }

  public function makeParam(string $method, string $url, array $headers, array $bodys, string $bodyOption='form_params')
  {
    return [
      'method' => $method,
      'url' => $url,
      'option' => [
        'headers' => $headers,
        $bodyOption => $bodys
      ]
    ];
  }
}
*/
/*
バージョン違いでの挙動に違いに注意
list($a, $b) = hogeFun();

function hogeFunc()
{
  return ["hoge", "fuga"];
}
*/
