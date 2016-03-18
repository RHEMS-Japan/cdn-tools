<!DOCTYPE html>
<html ng-app="cdntools">
    <head>
        <title>RHEMS CDN Tools</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link rel="stylesheet" href="/bower_components/bootstrap/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="/bower_components/bootstrap/dist/css/bootstrap-theme.min.css">
<!--        <script src="https://www.promisejs.org/polyfills/promise-6.1.0.min.js"></script> -->
        <script src="/bower_components/jquery/dist/jquery.min.js"></script>
        <script src="/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
        <script src="/bower_components/angular/angular.min.js"></script>
        <script src="/bower_components/angular-route/angular-route.min.js"></script>
        <script src="/bower_components/angular-bootstrap/ui-bootstrap-tpls.min.js"></script>
        <script src="/bower_components/moment/min/moment-with-locales.min.js"></script>
        <script src="/js/index.js"></script>
        <script src="/js/api.js"></script>
        <style>
            body {
                padding-top: 50px;
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
    <body ng-controller="cdntools_purge">
        <nav class="navbar navbar-default navbar-fixed-top">
            <div class="container">
                <div class="navbar-header">
                    <a class="navbar-brand" href="/#/">
                        <div>
                            <img alt="brand" src="/img/rhems_logo.png" style="width: 32px"/>
                            RHEMS Apps - CDN Tools
                        </div>
                    </a>
                </div>
            </div>
        </nav>

        <div class="container">
            <h2>
                <?php echo $service_label; ?> - <?php echo $account; ?>
            </h2>
            <hr />
            <form class="form-inline" name="purge_form" id="purge_form">
                <div class="form-group">
                    <label for="code"><?php echo $purge_label; ?></label>
                    <?php if (count($defaults) == 1): ?>
                        <input class="form-control" type="text" name="opt1" size="20" ng-model="form.opt1" ng-init="form.opt1 = '<?php echo $defaults[0]; ?>'"/>
                    <?php else: ?>
                        <select class="form-control" name="opt1" ng-model="form.opt1" ng-init="form.opt1 = '<?php echo $defaults[0]; ?>'">
                            <?php foreach ($defaults as $item): ?>
                                <option value="<?php echo $item; ?>" ><?php echo $item; ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>

                <?php if ($service == 'akamai'): ?>
                    <div class="form-group">
                        <label for="domain">Domain</label>
                        <select class="form-control" name="domain" ng-model="domain" ng-options="c.name for c in domains">
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="action">Action</label>
                        <select class="form-control" name="action" ng-model="action" ng-options="c.name for c in actions">
                        </select>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <button class="form-control btn-warning" ng-click="purge()">Purge</button>
                </div>
            </form>
            <hr />
            <form class="form-inline" name="purgeurl_form">
                <div class="form-group">
                    <label for="urls"><?php echo $purge_url_label; ?></label><br />
                    <textarea class="form-control" name="urls" ng-bind="urls" rows="5" cols="80" placeholder="http://example.jp/xxxxx.xxx"></textarea><br /><br />
                    <button class="form-control btn-warning" ng-click="purge_url()">Purge <?php echo $purge_url_label; ?></button>
                </div>
            </form>
            <div ng-show="queues">
                <hr />
                <h3>Queue</h3>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Start</th>
                            <th>Purge ID</th>
                            <th>State</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="queue in queues">
                            <td>{{queue.updated_at}}</td>
                            <td>{{queue.purgeId}}</td>
                            <td>{{(queue.done=="1")? 'Done':'Processing'}}</td>
                        </tr>
                    </tbody>
                </table>
                <button ng-click="update_queue()" class="btn btn-info">Update</button>

            </div>
            <div style="font-size:8px; text-align: center;">&copy; 2015 RHEMS Japan.CO,. Ltd.</div>
        </div>
        <script type="text/ng-template" id="purge-confirm.html">
            <div class="modal-header">
            <h3 class="modal-title text-danger">Purge warning : {{service}} - {{account}}</h3>
            </div>
            <div class="modal-body">
            <div class="row">
            <div class="col-xs-2">Purge ID</div>
            <div class="col-xs-1">:</div>
            <div class="col-xs-6">{{form.opt1}}</div>
            </div>
            <div class="row" ng-show="form.domain">
            <div class="col-xs-2">Domain</div>
            <div class="col-xs-1">:</div>
            <div class="col-xs-6">{{form.domain}}</div>
            </div>
            <div class="row" ng-show="form.action">
            <div class="col-xs-2">Action</div>
            <div class="col-xs-1">:</div>
            <div class="col-xs-6">{{form.action}}</div>
            </div>
            </div>
            <div class="modal-footer">
            <label>Are you sure?</label>
            <button class="btn btn-warning" ng-click="ok()">OK</button>
            <button class="btn btn-info" ng-click="cancel()">Cancel</button>
            </div>
        </script>
    </body>
</html>
