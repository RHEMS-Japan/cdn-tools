<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>RHEMS CDN Tools</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        .rhems-logo { width: 32px; }
        .starter-template { padding: 10px 5px; text-align: center; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="/">
                <img alt="RHEMS" src="/img/rhems_logo.png" class="rhems-logo me-2"/>
                RHEMS CDN Tools
            </a>
        </div>
    </nav>

    <div class="container py-4">
        <h2 class="mb-4">Please choose a CDN account</h2>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>CDN</th>
                        <th>Account</th>
                        <th>Notification</th>
                    </tr>
                </thead>
                <tbody>
            @foreach($accounts as $serviceName => $account)
                @foreach($account as $accountName => $config)
                    <tr role="button" onclick="location.href='/cdn/{{ $serviceName }}/{{ $accountName }}'" style="cursor: pointer;">
                        <td>{{ $serviceName }}</td>
                        <td>{{ $accountName }}</td>
                        <td><span class="badge bg-info">{{ $config['notification']['type'] }}</span></td>
                    </tr>
                @endforeach
            @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <footer class="text-center text-muted py-3" style="font-size: 0.75rem;">
        &copy; {{ date('Y') }} RHEMS Japan Co., Ltd.
    </footer>
</body>
</html>