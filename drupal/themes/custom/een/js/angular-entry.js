(function () {
  'use strict';

  var een = window.angular.module('een', []);

  een.config(function ($interpolateProvider) {
    $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
  });

  een.factory('oppsFactory', function () {
    var search = function (opts) {
      return window.$.ajax({
        url: 'opportunities/_ajax',
        data: {
          page: opts.page,
          resultPerPage: 19,
          search: opts.q,
          opportunity_type: opts.type
        }
      });
    };
    return {
      search: search
    };
  });

  een.controller('MainCtrl', function ($scope, oppsFactory) {

    $scope.submit = function (form) {
      oppsFactory.search({
        page: 1,
        q: 'test'
      }).then(function (data) {
        $scope.results = data;
      }).fail(function(test) {
        $scope.results = [];
      });
    };

    $scope.heading = {
      loaded: true,
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
