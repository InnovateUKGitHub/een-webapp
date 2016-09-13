(function () {
  var een = window.angular.module('een', []);

  een.config(function ($interpolateProvider) {
    $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
  });

  een.controller('MainCtrl', function ($scope) {
    $scope.heading = {
      loaded: false,
      total: 500
    };

    $scope.results = [
      {
        title: 'Manufacturer of carbonated soft drinks and boza is looking for trade intermediary services.',
        date: '4 days ago',
        id: 'BOBG20150709001',
        flag: 'bg',
        country: 'Bulgaria'
      }
    ];
  });

})();
