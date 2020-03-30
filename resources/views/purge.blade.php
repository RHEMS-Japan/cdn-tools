<!DOCTYPE html>
<html>
  <head>
    <title>RHEMS CDN Tools</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!--
    <link rel="stylesheet" href="/bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/bower_components/bootstrap/dist/css/bootstrap-theme.min.css">
    --!>
    <link rel="stylesheet" href="{{ mix('/css/app.css') }}">
    <script src="/bower_components/jquery/dist/jquery.min.js"></script>
    <script src="/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="/bower_components/moment/min/moment-with-locales.min.js"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>    
    <style>
      body {
        background-color: #FFF;
      }
      .rhems-logo {
        width: 90px;
      }
      .form-unit {
        padding-top: 10px;
        clear: left;
      }
      .form-group {
        width: 300px;
        padding-right: 10px;
      }
      .label {
        margin-bottom: 20px;
      }
      .modal-mask {
        position: fixed;
        z-index: 9998;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, .5);
        display: table;
        transition: opacity .3s ease;
      }
      .modal-wrapper {
        display: table-cell;
        vertical-align: middle;
      }
      .modal-container {
        width: 600px;
        margin: 0px auto;
        padding: 20px 30px;
        background-color: #fff;
        border-radius: 2px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
        transition: all .3s ease;
        font-family: Helvetica, Arial, sans-serif;
      }
      .modal-header h3 {
        margin-top: 0;
        color: black;
      }
      .modal-body {
        margin: 20px 0;
      }
    </style>
  </head>
  <body>
    <nav class="navbar navbar-light bg-light">
      <div class="container">
        <div class="navbar-header">
          <a class="navbar-brand" href="/" style="color: gray;">
            <img alt="brand" src="/img/rhems_logo.png" style="width: 32px"/>
            RHEMS Apps - CDN Tools
          </a>
        </div>
      </div>
    </nav>
    
    <div class="container">
      <div class="form-unit">
        <input id="service" type="hidden" value=<?php echo $info['service_label']; ?>/>
        <input id="account" type="hidden" value=<?php echo $info['account']; ?>/>
        <h2 id="service_account" style="margin-top: 10px;">
          {{ $info['service_label'] }} - {{ $info['account'] }}
        </h2>
        <hr />
      </div>

      <div id="purge">
        <div class="form-group">
          <p><b>{{ $info['purge_label'] }}</b></p>
          <input id="defaults" type="hidden" value=<?php echo implode(",", $info['defaults']); ?>/>
          <select class="form-control" name="default" v-model="params.selected_default">
            <option v-for="def in params.defaults">
              @{{ def.text }}
            </option>
          </select>
        </div>
        <div class="form-unit">
          <p><b>{{ $info['purge_url_label'] }}</b></p>
          <?php if ($info['service'] == 'cloudflare'): ?>
            
          <?php endif; ?>
          <?php if ($info['service'] == 'cloudfront'): ?>
              <code>
                Examples:<br>
                /images/image1.jpg<br>
                /images/image*<br>
                /images/*<br>
                /images*<br>
                /*<br>
                <p></p>
              </code>
            
          <?php endif; ?>
          <textarea id="urls" class="form-control" name="urls" rows="4" cols="80" placeholder="Please specify {{ $info['explain_path'] }}"></textarea><br />
          <button type="button" class="btn btn-primary" id="show-modal" v-on:click="params.modal = true">Purge</button>
          <purge-modal v-if="params.modal" v-on:close="params.modal = false" v-bind:params="params" v-on:request_purge="purge" service="{{ $info['service_label'] }}" account="{{ $info['account'] }}"></p>
        </div>
      </div> 

      <div class="form-unit" id="update_queue">
        <h3 style="margin-top:40px">Queue</h3>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Start</th>
              <th>Purge ID</th>
              <th>State</th>
            </tr>
          </thead>
          <tbody id="queue-body">
            @foreach ($historys as $history)
            <tr class="queue">
              <td class="queue_td">{{ $history['updated_at'] }}</td>
              <td class="queue_td">{{ $history['purgeId'] }}</td>
              <td class="queue_td">{{ ($history['done'] == "1")? 'Done':'Processing'}}</td>
            </tr>
            @endforeach
          </tbody>
        </table>

        <?php if ($info['service'] == 'cloudfront'): ?>
        <button type="button" class="btn btn-primary" v-on:click="check_queue()">Update</button>
        <?php endif; ?>
      </div>

      <div style="font-size:8px; text-align: center; clear: left;">&copy; 2019 RHEMS Japan.CO,. Ltd.</div>
    </div>
    <script src=" {{ mix('js/app.js') }} "></script> 
  </body>
</html>
