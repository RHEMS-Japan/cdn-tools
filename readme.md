# RHEMS CDN-Tools ver.2

RHEMS CDN-Toolsは、CDNサービスに対し、キャッシュパージを行うプログラムをDockerコンテナにまとめて提供するものです。サポート対象のCDNサービスは、**CloudFront** と **CloudFlare**です。

## 機能

CloudFrontの場合、パージリクエストからパージ完了までを監視し、メール/インスタントメッセージサービスに状態を通知することが可能です。

CloudFlareは、パージの状態を監視する機構が公式から提供されていないので、パージリクエストのみ可能です。

リクエストはCLI(docker execによるもの)とWebインターフェースをそれぞれ用意しています。       
利用統計情報が取得可能なCDNサービスの場合は設定アカウントごとに転送量等の情報をWebインターフェースにて確認することが可能です。

メッセージングサービスは、Slack と Chatworkのサポートしています。

## ビルド

Docker Composeがインストール済であれば以下のコマンドでビルドが完了します。

```
$ git clone https://github.com/RHEMS-Japan/cdn-tools.git
$ cd cdn-tools
$ docker-compose build
```

## 設定ファイルの準備

このサービスを利用するために、利用者のユーザ情報やapiトークン等を設定ファイルに記述しなければなりません。        
設定ファイルは、**config/cdn.php** に記述します。        
**config/cdn.php.example** のひな形を適宜修正し、

```
cp config/cdn.php.example config/cdn.php
```

を実行してください。ひな型は、以下の通りです。

```
<?php
return array(
    'cloudflare' => array(
        'USER_NAME1' => array(      //名前の登録(お好みで)
            'defaults' => array('000aaa111bbb222ccc', '333ddd444eee555', ・・・),   //対象のzoneID
            'authentication' => array(
                'email' => 'hoge@fuga.com',              //CloudFlareに登録したメールアドレス
                'apiKey' => '12345abcde6789fghij',       //アカウントのAPIKey
            ),
            'notification' => array(
                'type' => 'slack',
                'token' => 'aaaabbbbccccdddd111122223333',       //slackのAPIToken
                'channel' => 'aaabbbcccddd'                      //送信先のチャンネル名
            ),
        ),
        'USER_NAME2' => array(
            'defaults' => array('abcdefghijklmn12345',  '333ddd444eee555', ・・・),
            'authentication' => array(
                'email' => 'haha@fufu.com',
                'apiKey' => 'sssssdddddffffffgggggghhhhhh',
            ),
            'notification' => array(
                'type' => 'chatwork',
                'token' => '848776f08e968dcb938ac4d902d06c6b',    //chatworkのAPIToken
                'roomId' => '155634407'                           //送信先のroomId
            ),
        ),
        //この先も同じ形式でCloudFlareの利用者を追加できる
    ),
    'cloudfront' => array(
        'shindex1' => array(
            'defaults' => array('ABCDFEG1', 'AAAEEE', ・・・),    //CloudFrontのDistributionId
            'authentication' => array(
                'accessKeyId' => 'AAAABBBBCCCC',               //AWSのaccessKeyId
                'secretAccessKey' => 'aaapppoookkk',           //AWSのsecretAccessKey
            ),
            'notification' => array(
                'type' => 'slack',
                'token' => 'wwwwaaaassssdddd',
                'channel' => 'fffggghhhjjj',
            ),
        ),
        'shindex2' => array(
            'defaults' => array('RRRRFFFFGGGGTTTT', 'AAAAIIIIOOOOKKKK', ・・・),
            'authentication' => array(
                'accessKeyId' => 'NNNNMMMMMKKKKJ',
                'secretAccessKey' => 'kkknnhhhfffddduuu12345',
            ),
            'notification' => array(
                'type' => 'chatwork',
                'token' => '111222333444555666',
                'roomId' => '987654321',
            ),
        ),
        //この先も同じ形式でCloudFrontの利用者を追加できる。
    ),
);
```
CDNが、     
CloudFlareの場合、**ZONEID、登録済みのメールアドレス、アカウントのAPIKey** が必要。      
CloudFrontの場合、**DistributionID、AccessKeyID、SecretAccessKey** が必要。      

メッセージングサービスが、       
slackの場合、**APIToken**([ここ](https://api.slack.com/custom-integrations/legacy-tokens)で取得可能)、送信先の**チャンネル名**が必要。        
Chatworkの場合、**APIToken**、送信先の**roomID**が必要。

## 起動

```
$ docker-compose up -d
```
でdockerコンテナを起動してください。
コンテナ初回起動時は、webからアクセスできるようになるまで5分弱かかります。

## CLIからの操作

docker execによるコマンド実行によりパージリクエストを発行します。

```
$ docker exec -it <コンテナ名> php artisan purge ① ② ③ ④
```

4つの引数について、       
①は、cloudflare または cloudfront       
②は、アカウント名(config/cdn.phpに設定したもの)       
③は、zoneID または DistributionID        
④は、パージ対象のパス        
を指定してください。

④は、必ず必要な引数ではありません。3つの引数のみで実行した場合は、全てのファイルがパージされます。        
パージ対象を指定する場合、CloudFlareなら**フルパス**を、CloudFrontなら**/以下のパス**を指定してください。

パージ処理の進捗状況はバッチ処理にて毎分確認され、処理完了が通知されます。

## WEBインターフェースからの操作

`http://localhost:50000/`にアクセスしてください。

### トップページ

![top-page](https://user-images.githubusercontent.com/47022289/60344424-7d4ee000-99f1-11e9-9620-d259b56a8f7f.png)


### CloudFront

DistributionIDを選択し、パージ対象を **/以下のパス** を指定してください。       
パージ対象を指定しなかった場合は、全てのファイルがパージされます。

![cloudfront](https://user-images.githubusercontent.com/47022289/60344414-7922c280-99f1-11e9-81a6-bf65bae2479b.png)

### CloudFlare

ZONEIDを選択し、パージ対象を **フルパス** を指定してください。       
パージ対象を指定しなかった場合は、全てのファイルがパージされます。

![cloudflare](https://user-images.githubusercontent.com/47022289/60345191-6610f200-99f3-11e9-8b19-d13b1c8e570d.png)
