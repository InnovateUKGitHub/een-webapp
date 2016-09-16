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

  var debounce = function (func, wait, immediate) {
    var timeout;
    return function () {
      var context = this;
      var args = arguments;
      var later = function () {
        timeout = null;
        if (!immediate) func.apply(context, args);
      };
      var callNow = immediate && !timeout;
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
      if (callNow) func.apply(context, args);
    };
  };

  een.controller('MainCtrl', function ($scope, oppsFactory) {
    var queryAPI = debounce(function () {
      oppsFactory.search({
        page: 1,
        type: $scope.heading.opportunity_type,
        q: $scope.query
      }).then(function (data) {
        $scope.heading = {
          opportunity_type: data.opportunity_type || [],
          page: data.page,
          pageTotal: data.pageTotal,
          total: data.total,
          loaded: true,
          searched: true
        };

        $scope.results = data.results;
        $scope.$apply();
      }).fail(function () {
        $scope.results = [];
      });
    }, 200);

    $scope.submit = function () {
      queryAPI();
      return true;
    };

    $scope.queryKeyUp = function () {
      queryAPI();
    };

    $scope.getFlagClass = function (code) {
      return 'flag-icon flag-icon-' + code.toLowerCase();
    };

    var indexOf = function (array, comparer) {
      for (var i = 0; i < array.length; i++) {
        if (array[i] === comparer) {
          return i;
        }
      }
      return -1;
    };

    $scope.selectCheckbox = function ($event) {
      var tar = $event.target;
      if (tar.type === 'checkbox') {
        if (tar.value && tar.checked) {
          if (indexOf($scope.heading.opportunity_type, tar.value) === -1) {
            $scope.heading.opportunity_type.push(tar.value);
          }
        } else {
          var index = indexOf($scope.heading.opportunity_type, tar.value);
          if (index > -1) {
            $scope.heading.opportunity_type.splice(index, 1);
          }
        }
      }
    };

    $scope.heading = {
      opportunity_type: [],
      page: 1,
      loaded: true,
      searched: false,
      total: 500
    };

    $scope.results = [];
  });

})(jQuery);
