var cdntools = angular.module('cdntools', ['ui.bootstrap']);

cdntools.filter('getFuelParam', function ($location) {
    return function (segment) {
        var re = new RegExp('^http\:\/\/(.*?)\/', 'i');
        var BASE_URL = $location.absUrl().match(re)[0];
        var query = $location.absUrl().replace(BASE_URL, "");
        var data = query.split("/");
        if (data[segment - 1]) {
            return data[segment - 1];
        }
        return false;
    }
});

cdntools.controller('cdntools_top', function ($scope, $window) {
    $scope.redirect = function (a, b) {
        console.log(a);
        console.log(b);
        $window.location.href = '/cdn/' + a + '/' + b;
    };
});

var InquiryModalCtrl = cdntools.controller('PurgeModalCtrl', function ($scope, $modalInstance) {
    $scope.ok = function () {
        $modalInstance.close();
    };

    $scope.cancel = function () {
        $modalInstance.dismiss();
    };
});

cdntools.controller('cdntools_purge', function ($scope, $filter, $modal) {
    var service_name = $filter('getFuelParam')(2);
    var account_name = $filter('getFuelParam')(3);
    
    $scope.service = service_name;
    $scope.account = account_name;

    $scope.form = {};
    if (service_name == 'akamai') {
        $scope.actions = [
            {name: 'Invalidate', value: 'invalidate'},
            {name: 'Remove', value: 'remove'}
        ];
        $scope.action = $scope.actions[0];
        $scope.domains = [
            {name: 'Staging', value: 'staging'},
            {name: 'Production', value: 'production'}
        ];
        $scope.domain = $scope.domains[0];
        $scope.$watch('domain', function () {
            $scope.form.domain = $scope.domain.value;
        });
        $scope.$watch('action', function () {
            $scope.form.action = $scope.action.value;
        });
    }
    ;

    $scope.update_queue = function () {
        list_queue(service_name, account_name).then(
                function (data) {
                    $scope.$apply(function () {
                        $scope.queues = data;
                    });
                    console.log(data);
                },
                function (err) {
                    console.log(err);
                });
    };

    $scope.purge = function () {
        var confirmModal = $modal.open({
            templateUrl: 'purge-confirm.html',
            controller: 'PurgeModalCtrl',
            backdrop: 'static',
            backdropClass: 'backdrop-css',
            keyboard: false,
            scope: $scope,
            size: 'lg'
        });
        confirmModal.result.then(function () {
            // OK
            purge_request(service_name, account_name, $scope.form).then(
                    function (data) {
                        console.log(data);
                        $scope.$apply(function () {
                            $scope.update_queue();
                        });
                    },
                    function (err) {
                        console.log(err);
                    });
        }, function () {
            // Cancel
        });
    };
    
    $scope.purge_url = function() {
        
        var confirmModal = $modal.open({
            templateUrl: 'purge-confirm.html',
            controller: 'PurgeModalCtrl',
            backdrop: 'static',
            backdropClass: 'backdrop-css',
            keyboard: false,
            scope: $scope,
            size: 'lg'
        });
        confirmModal.result.then(function () {
            // OK
            /*
            purge_url_request(service_name, account_name, $scope.form).then(
                    function (data) {
                        console.log(data);
                        $scope.$apply(function () {
                            $scope.update_queue();
                        });
                    },
                    function (err) {
                        console.log(err);
                    });
                    */
        }, function () {
            // Cancel
        });
        
    }

    $scope.update_queue();
});
