(function ($) {
  'use strict';

  // https://docs.angularjs.org/api/ng/directive/ngSubmit
  // only prevents default form submit when action is removed
  $('#opportunity-search-form').removeAttr('action');

  var een = window.angular.module('een', []);

  een.config(function ($interpolateProvider) {
    $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
  });

  een.factory('oppsFactory', function () {
    var search = function (opts) {
      return $.ajax({
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
        q: $scope.query
      }).then(function (data) {
        $scope.heading = {
          opportunity_type: data.opportunity_type,
          page: data.page,
          loaded: true,
          searched: true,
          pageTotal: data.pageTotal,
          total: data.total
        };

        $scope.results = data.results;
        $scope.$apply();
      }).fail(function(test) {
        $scope.results = [];
      });
      return true;
    };

    $scope.heading = {
      opportunity_type: null,
      page: 1,
      loaded: true,
      searched: false,
      total: 500
    };

    $scope.results = [
      {
        id: "BOKR20150806001",
        title: "South Korean manufacturer of frozen yogurt powder is looking for distributors in Europe",
        type: "BO",
        summary: "South Korean manufacturer of frozen yogurt powder is looking for distributors in Europe. Their products contain live probiotics which are good for the health and have passed strict control and certification tests. They are looking for a distributor in retail/food industry with well-established distribution channel. ",
        date: "2016-08-02T00:00:00",
        country_code: "KR",
        country: "South Korea"
      }
    ];
  });

})(jQuery);
