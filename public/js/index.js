var cdntools = angular.module('cdntools', ['ui.bootstrap']);
cdntools.controller('cdntools_top', function ($scope, $q, $window) {
   $scope.redirect = function(a,b) {
       console.log(a);
       console.log(b);
       $window.location.href = '/cdn/' + a + '/' + b;
   };
});
