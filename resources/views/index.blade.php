<!DOCTYPE html>
<html>
    <head>
        <title>RHEMS CDN Tools</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="stylesheet" href="/bower_components/bootstrap/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="/bower_components/bootstrap/dist/css/bootstrap-theme.min.css">
        <link rel="stylesheet" href="{{ mix('/css/app.css') }}">
        <script src="https://www.promisejs.org/polyfills/promise-6.1.0.min.js"></script>
        <script src="/bower_components/jquery/dist/jquery.min.js"></script>
        <script src="/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
        <script src="/bower_components/moment/min/moment-with-locales.min.js"></script>
        <style>
            body {
              background-color: #FFF;
            }
            .starter-template {
                padding: 10px 5px;
                text-align: center;
            }
            .rhems-logo {
                width: 90px;
            }
            .backdrop-css {
                background-color: #000;
                opacity: 50%;
            }
        </style>
    </head>
    <body>
        <nav class="navbar navbar-default navbar-fixed-top">
            <div class="container">
                <div class="navbar-header">
                    <a class="navbar-brand" href="/" style="color: gray;">
                        <div>
                            <img alt="brand" src="/img/rhems_logo.png" style="width: 32px"/>
                            RHEMS Apps - CDN Tools
                        </div>
                    </a>
                </div>
            </div>
        </nav>

        <div class="container">
            <h2 style="padding-top: 20px; padding-bottom: 8px;">Please choose</h2>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>CDN</th>
                        <th>Account</th>
                        <th>Notification</th>
                    </tr>
                </thead>
                <tbody>
            <?php foreach($accounts as $serviceName => $account): ?>
            <?php foreach($account as $accountName => $config): ?>
                    <tr onclick="location.href='/cdn/{{ $serviceName }}/{{ $accountName }}'">
                        <td><?php echo $serviceName; ?></td>
                        <td><?php echo $accountName; ?></td>
                        <td><?php echo $config['notification']['type']; ?></td>
                    </tr>
            <?php endforeach; ?>
            <?php endforeach; ?>
            </table>
            <hr />
            <div style="font-size:8px; text-align: center;">&copy; 2019 RHEMS Japan.CO,. Ltd.</div>
        </div>
    </body>
</html>
