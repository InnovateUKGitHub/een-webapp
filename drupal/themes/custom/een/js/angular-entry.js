(function () {
  var een = window.angular.module('een', []);

  een.config(function ($interpolateProvider) {
    $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
  });

  een.controller('MainCtrl', function ($scope) {
    $scope.test = 'hello?';
  });

})();
