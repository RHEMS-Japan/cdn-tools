# RHEMS CDN-Tools

RHEMS CDN-ToolsはCloudFront / KeyCDN / Akamai 等のCDNサービスに対しキャッシュパージや利用情報の照会を行うプログラムをDockerコンテナにまとめて提供するものです

## 機能

パージリクエストからパージ完了までを監視し、メール/インスタントメッセージサービスに状態を通知することが可能です

リクエストはCLI(docker execによるもの)とWebインターフェースをそれぞれ用意しています

利用統計情報が取得可能なCDNサービスの場合は設定アカウントごとに転送量等の情報をWebインターフェースにて確認することが可能です

メッセージングサービスは現在のところSlack / HipChatのサポートしています

## システム要件

Docker 1.5以降が動作する環境にてテストを行っています

Docker Composeの定義ファイルを同梱していますのでDocker Composeの導入もお薦めします(必須ではありません)

## ビルド

Docker Composeがインストール済であれば以下のコマンドでビルドが完了します

```
# curl -L https://github.com/docker/compose/releases/download/1.3.2/docker-compose-`uname -s`-`uname -m` > docker-compose
# sudo cp docker-compose /usr/local/bin/
# sudo chmod +x /usr/local/bin/docker-compose
```

```
$ git clone https://github.com/RHEMS-Japan/cdn-tools
$ cd cdn-tools
$ docker-compose build
```


## 設定ファイルの準備

設定ひな形はfuel/app/config/example以下にあります

```
$ cp -R fuel/app/config/example ~/.cdn-tools
```

上記のようにコピーした後、cdn.phpの修正を行って下さい

なお、db.phpについては通常修正の必要はありません

```
<?php

return array(
    'akamai' => array(
        'account1' => array(
            'defaults' => array('99999999'),
            'authentication' => array(
                'user' => 'hogehoge@hogefuga.jp',
                'password' => 'hogehoge',
            ),
            'notification' => array(
                'type' => 'hipchat',
                'token' => '<HipChat v2 API token>',
                'room' => '999999',
            ),
        ),
    ),
    'keycdn' => array(
        'account1' => array(
            'defaults' => array('hogezone'),
            'zonelist' => APPPATH.'/config/production/zones.json',
            'authentication' => array(
                'user' => 'hogehoge@hogefuga.jp',
                'password' => 'hogehoge',
            ),
            'notification' => array(
                'type' => 'slack',
                'token' => 'xoxp-xxxxxxxxx-eeeeee-yyyyyyy-zzzzzz',
                'team' => 'Team',
                'channel' => 'cdn-channel',
            ),
        ),
    ),
    '(akamai|keycdn|cloudfront)' => array(
        '(アカウント名)' => array(
            'defaults' => array('WebUIで選択可能とするID1', 'ID2'),
            'zonelist' => 'ゾーン情報(KeyCDNのみ)',
            'authentication' => array(
                'user' => 'ログインアカウント|アクセスキー',
                'password' => 'ログインパスワード|シークレットキー',
            ),
            'notification' => array(
                'type' => 'slack|hipchat',
                'token' => 'APIアクセスに必要なトークン',
                'room' => 'ルームID(hipchatの場合必要です)',
                'channel' => 'チャンネル名(Slackの場合必要です)',
                'team' => 'チーム名(Slackの場合必要です)',
            ),
        ),
    ),
);
```


## 起動

以下ボリュームオプションを利用して`/Users/rhems/.cdn-tools`に設定ファイルを配置した例です

Docker Composeの場合は`docker-compose.yml`のvolumesを適宜修正し

```
$ docker-compose up -d
```

手動で起動する場合は設定ファイルのディレクトリをボリュームオプションで指定して下さい

```
$ docker run \
  -d -p 8032:80 -v /Users/rhems/.cdn-tools:/var/www/fuelphp/fuel/app/config/production:rw \
  (コンテナイメージ)
```


## パージリクエストデータベースの作成

```
docker exec -it (コンテナID) /usr/bin/cdn init-db
drop table cdnrequet! are you ready? [ Y, n ]: Y
Database initialized.
```

すでにテーブルが存在していた場合は上記のような確認が入ります

## CLIからの操作

docker execによるコマンド実行によりリクエストを発行します

```
$ docker exec -it <コンテナ名> /usr/bin/cdn <CDNサービス名> <アカウント名> <コマンド> <オプション>
```

パージ処理の進捗状況はバッチ処理にて毎分確認され、処理完了が通知されます

### Akamai

* CPコードによるパージ

```
$ docker exec -it <コンテナ名> /usr/bin/cdn akamai (アカウント名) purge (CPコード) [-domain=staging|production] [-action=invalidate|remove]
```

* ARLファイルによるパージ

```
$ docker exec -it <コンテナ名> /usr/bin/cdn akamai (アカウント名) purge-url (ARLファイルパス) [-domain=staging|production] [-action=invalidate|remove]
```

ARLファイルパスはコンテナ内のフルパスを記述して下さい。
設定ディレクトリにARLファイルを配置している場合は`/var/www/fuelphp/fuel/app/config/production/(ARLファイル名)`となります

### KeyCDN

* ゾーン名によるパージ

```
$ docker exec -it <コンテナ名> /usr/bin/cdn keycdn (アカウント名) purge (ゾーン名)
```

処理は即時開始されます

* URLによるパージ

```
$ docker exec -it <コンテナ名> /usr/bin/cdn keycdn (アカウント名) purge-url (ゾーン名) (URLファイル)
```

URLファイルパスはコンテナ内のフルパスを記述して下さい。
設定ディレクトリにURLファイルを配置している場合は`/var/www/fuelphp/fuel/app/config/production/(URLファイル名)`となります

    
## Webインターフェースからの操作

`http://(コンテナIP):8032/` こちらへアクセスして下さい

<img src="public/img/screenshot/screen_1.png" />

`cdn.php`に設定したアカウントの一覧から目的のCDNサービスを選択して下さい

<img src="public/img/screenshot/screen_2.png" />

CDNサービスによって表示は一部異なりますが、それぞれコード/ゾーン、URL/パターンを指定したパージのリクエストが可能です

<img src="public/img/screenshot/screen_3.png" />

パージボタン押下で確認画面が表示されます


