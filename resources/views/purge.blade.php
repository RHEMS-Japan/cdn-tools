<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>RHEMS CDN Tools - {{ $info['service_label'] }}</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        .rhems-logo { width: 32px; }
        .form-unit { padding-top: 10px; clear: left; }
        .queue_td { vertical-align: middle; }
        .modal-mask {
            position: fixed; z-index: 9998; top: 0; left: 0;
            width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, .5);
            display: table; transition: opacity .3s ease;
        }
        .modal-wrapper { display: table-cell; vertical-align: middle; }
        .modal-container {
            width: 600px; margin: 0px auto; padding: 20px 30px;
            background-color: #fff; border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
        }
        .modal-header h3 { margin-top: 0; color: black; }
        .modal-body { margin: 20px 0; }
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
        <div class="form-unit">
            <input id="service" type="hidden" value="{{ $info['service_label'] }}" />
            <input id="account" type="hidden" value="{{ $info['account'] }}" />
            <h2 class="mb-3">{{ $info['service_label'] }} - {{ $info['account'] }}</h2>
            <hr />
        </div>

        <div id="purge-form">
            <div class="mb-3">
                <label class="form-label"><b>{{ $info['purge_label'] }}</b></label>
                <input id="defaults" type="hidden" value="{{ implode(',', $info['defaults']) }}" />
                <select class="form-select" name="default" id="default-select">
                    @foreach($info['defaults'] as $default)
                    <option value="{{ $default }}">{{ $default }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label"><b>{{ $info['purge_url_label'] }}</b></label>
                @if($info['service'] == 'cloudfront')
                <div class="mb-2">
                    <code>
                        Examples:<br>
                        /images/image1.jpg<br>
                        /images/image*<br>
                        /images/*<br>
                        /*<br>
                    </code>
                </div>
                @endif
                <textarea id="urls" class="form-control" name="urls" rows="4"
                    placeholder="Please specify {{ $info['explain_path'] }}"></textarea>
            </div>

            <div id="app"></div>
        </div>

        <div class="form-unit" id="update_queue">
            <h3 class="mt-5 mb-3">Queue</h3>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Start</th>
                            <th>Purge ID</th>
                            <th>State</th>
                        </tr>
                    </thead>
                    <tbody id="queue-body">
                        @foreach ($historys as $history)
                        <tr>
                            <td>{{ $history['updated_at'] }}</td>
                            <td>{{ $history['purgeId'] }}</td>
                            <td>
                                @if($history['done'] == "1")
                                    <span class="badge bg-success">Done</span>
                                @else
                                    <span class="badge bg-warning text-dark">Processing</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($info['service'] == 'cloudfront')
            <button type="button" class="btn btn-outline-primary" id="update-queue-btn">
                Update
            </button>
            @endif
        </div>
    </div>

    <footer class="text-center text-muted py-3" style="font-size: 0.75rem;">
        &copy; {{ date('Y') }} RHEMS Japan Co., Ltd.
    </footer>
</body>
</html>