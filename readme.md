# RHEMS CDN-Tools

RHEMS CDN-ToolsはCloudFront / KeyCDN / Akamai 等のCDNサービスに対しキャッシュパージや利用情報の照会を行うプログラムをDockerコンテナにまとめて提供するものです

## 機能

パージリクエストからパージ完了までを監視し、メール/インスタントメッセージサービスに状態を通知することが可能です

リクエストはCLI(docker execによるもの)とWebインターフェースをそれぞれ用意しています

利用統計情報が取得可能なCDNサービスの場合は設定アカウントごとに転送量等の情報をWebインターフェースにて確認することが可能です

メッセージングサービスは現在のところSlack / HipChatのサポートを予定しています

## システム要件

Docker 1.5以降が動作する環境にてテストを行っています

Docker Composeの定義ファイルを同梱していますのでDocker Composeの導入もお薦めします(必須ではありません)

## ビルド

Docker Composeがインストール済であれば以下のコマンドでビルドが完了します

```
$ git clone https://github.com/RHEMS-Japan/cdn-tools
$ cd cdn-tools
$ docker-compose build
```

## 設定

コンテナ起動時のボリュームオプションにて設定する必要があります

設定ファイルはcdn-tools.phpというファイルで作成する必要があります

```
return array(
   'akamai' => array(
       'account1' => array(
           'enable' => true,
           'authentication' => array(
               'user' => 'hogehoge@rhems-japan.co.jp',
               'password' => 'hogehoge',
           ),
           'notification' => array(
               'type' => 'mail',
               'addr' => 'hogehoge@rhems-japan.co.jp',
           ),
       ),
   ),
   'keycdn' => array(
       'account1' => array(
           'enable' => true,
           'authentication' => array(
               'user' => 'hogehoge@rhems-japan.co.jp',
               'password' => 'hogehoge',
           ),           
           'notification' => array(
               'type' => 'slack',
               'token' => 'XXXX-YYYY-XZZZZ-ZZXXYY-000-AAAA',
               'channel' => 'cdn-channel',
           ),
       ),
   ),
   'cloudfront' => array(
       'account1' => array(
           'enable' => true,
           'authentication' => array(
               'user' => '<AccessKey>',
               'password' => '<SecretKey>',
           ),           
           'notification' => array(
               'type' => 'hipchat',
               'token' => '<HipChat v2 API token>',
               'target' => '@user or room-id',
           ),
       ),
   ),
);
```


以下`/usr/local/etc/cdn-tools/config`に設定ファイルを配置した場合のオプション例です

```
docker run cdn_tools:latest \
  -v "/usr/local/etc/cdn-tools:/etc/cdn-tools"
```

## CLIからの操作

docker execによるコマンド実行によりリクエストを発行します

```
$ docker exec <コンテナ名> /usr/bin/cdn <CDNサービス名> <アカウント名> <コマンド> <オプション>
```

例：AkamaiにてCPコードによるパージリクエストを発行する場合

```
$ docker exec cdn_tools:latest /usr/bin/cdn akamai account1 \
    purgeCode 12345678 --domain production --action invalidate
```

### 利用可能なコマンド

* list

    Akamai以外のCDNサービスについてはゾーン/ディストリビューションIDの一覧を取得できます

* purge

    Akamaiの場合はCPコード、KeyCDNの場合はゾーンID、CloudFrontの場合はディストリビューションID単位でのパージリクエストを行います。CDNサービス毎に指定できるオプションが異なります。なおKeyCDNのみは即時実行となりますのでご注意下さい

* purgeUrl

    前述のpurgeコマンドでの各コード/IDと合わせ局所的なパージを要求します。パスを列挙したファイルを指定して下さい。各CDNサービスによってURLの指定方法が異なりますので注意が必要です

* check

    各パージリクエストで発行されたリクエストIDごとの進行状況を確認します
    
## Webインターフェースからの操作

```現在作成中```

