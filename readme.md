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

## 設定ファイルの準備

直接ビルド前に設定ファイルを修正するか、コンテナ起動時のボリュームオプションにて設定ファイルの場所を指定する必要があります
後者は設定変更が頻繁に行われる場合便利です

設定ひな形はfuel/app/config/example以下にあります


```
<?php
return array(
   'akamai' => array(
       'account1' => array(
           'enable' => true,
           'authentication' => array(
               'user' => 'hogehoge@hogefuga.jp',
               'password' => 'hogehoge',
           ),
           'notification' => array(
               'type' => 'mail',
               'addr' => 'hogehoge@hogefuga.jp',
               'title' => 'report-akamai',
           ),
       ),
   ),
   'keycdn' => array(
       'account1' => array(
           'enable' => true,
           'authentication' => array(
               'user' => 'hogehoge@hogefuga.jp',
               'password' => 'hogehoge',
           ),           
           'notification' => array(
               'type' => 'slack',
               'token' => 'XXXX-YYYY-XZZZZ-ZZXXYY-000-AAAA',
               'title' => 'report-keycdn',
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

## ビルド

Docker Composeがインストール済であれば以下のコマンドでビルドが完了します

```
$ git clone https://github.com/RHEMS-Japan/cdn-tools
$ cd cdn-tools
$ docker-compose build
```

## 起動

以下ボリュームオプションを利用して`/usr/local/etc/cdn-tools/config`に設定ファイルを配置した例です

```
docker run (コンテナID) \
  -v "/usr/local/etc/cdn-tools:/etc/cdn-tools"
```

## パージリクエストデータベースの作成

```
docker exec -it (コンテナID) /usr/bin/cdn initdb
drop table cdnrequet! are you ready? [ Y, n ]: Y
Database initialized.
```

すでにテーブルが存在していた場合は上記のような確認が入ります

## CLIからの操作

docker execによるコマンド実行によりリクエストを発行します

```
$ docker exec <コンテナ名> /usr/bin/cdn <CDNサービス名> <アカウント名> <コマンド> <オプション>
```

例：AkamaiにてCPコードによるパージリクエストを発行する場合

```
$ docker exec cdn_tools:latest /usr/bin/cdn akamai account1 \
    purge 12345678 --domain production --action invalidate
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

http://(コンテナIP):8031/

```現在作成中```

