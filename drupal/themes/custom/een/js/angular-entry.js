(function ($) {
  'use strict';

  // https://docs.angularjs.org/api/ng/directive/ngSubmit
  // only prevents default form submit when action is removed
  $('#opportunity-search-form').removeAttr('action');

  var een = window.angular.module('een', []);

  // changed to stop conflicting with Drupal templating engine
  een.config(function ($interpolateProvider) {
    $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
  });

  // added to preventDefault on hrefs
  een.directive('paginationLink', function () {
    return function (scope, element, attrs) {
      $(element).click(function (event) {
        event.preventDefault();
      });
    };
  });

  een.factory('timeFactory', function () {
    var opts = {
      refreshMillis: 60000,
      allowPast: true,
      allowFuture: false,
      localeTitle: false,
      cutoff: 0,
      autoDispose: true,
      strings: {
        prefixAgo: null,
        prefixFromNow: null,
        suffixAgo: 'ago',
        suffixFromNow: 'from now',
        inPast: 'any moment now',
        seconds: 'less than a minute',
        minute: 'about a minute',
        minutes: '%d minutes',
        hour: 'about an hour',
        hours: 'about %d hours',
        day: 'a day',
        days: '%d days',
        month: 'about a month',
        months: '%d months',
        year: 'about a year',
        years: '%d years',
        wordSeparator: ' ',
        numbers: []
      }
    };

    var inWords = function (distanceMillis) {
      if (!opts.allowPast && !opts.allowFuture) {
        throw 'timeago allowPast and allowFuture opts can not both be set to false.';
      }

      var $l = opts.strings;
      var prefix = $l.prefixAgo;
      var suffix = $l.suffixAgo;
      if (opts.allowFuture) {
        if (distanceMillis < 0) {
          prefix = $l.prefixFromNow;
          suffix = $l.suffixFromNow;
        }
      }

      if (!opts.allowPast && distanceMillis >= 0) {
        return opts.strings.inPast;
      }

      var seconds = Math.abs(distanceMillis) / 1000;
      var minutes = seconds / 60;
      var hours = minutes / 60;
      var days = hours / 24;
      var years = days / 365;

      function substitute(stringOrFunction, number) {
        var string = $.isFunction(stringOrFunction) ? stringOrFunction(number, distanceMillis) : stringOrFunction;
        var value = ($l.numbers && $l.numbers[number]) || number;
        return string.replace(/%d/i, value);
      }

      var words = seconds < 45 && substitute($l.seconds, Math.round(seconds)) ||
        seconds < 90 && substitute($l.minute, 1) ||
        minutes < 45 && substitute($l.minutes, Math.round(minutes)) ||
        minutes < 90 && substitute($l.hour, 1) ||
        hours < 24 && substitute($l.hours, Math.round(hours)) ||
        hours < 42 && substitute($l.day, 1) ||
        days < 30 && substitute($l.days, Math.round(days)) ||
        days < 45 && substitute($l.month, 1) ||
        days < 365 && substitute($l.months, Math.round(days / 30)) ||
        years < 1.5 && substitute($l.year, 1) ||
        substitute($l.years, Math.round(years));

      var separator = $l.wordSeparator || '';
      if (typeof $l.wordSeparator === 'undefined') {
        separator = ' ';
      }
      return $.trim([prefix, words, suffix].join(separator));
    };

    return {
      inWords: inWords
    };
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
        if (!immediate) {
          func.apply(context, args);
        }
      };
      var callNow = immediate && !timeout;
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
      if (callNow) {
        func.apply(context, args);
      }
    };
  };

  een.controller('MainCtrl', function ($scope, oppsFactory, timeFactory) {

    var parseResults = function (results) {
      return $.map(results, function (result) {
        var today = new Date();
        var date = new Date(result.date);
        var fiveDaysAgo = today.setDate(today.getDate() - 5);

        if (date.getTime() < fiveDaysAgo) {
          result.date = null;
        } else {
          result.date = timeFactory.inWords(date) + ' |';
        }

        return result;
      });
    };

    var queryAPI = debounce(function () {
      $scope.heading.searching = true;
      $scope.$apply();

      oppsFactory.search({
        page: $scope.heading.page,
        type: $scope.heading.opportunity_type,
        q: $scope.query
      }).then(function (data) {
        $scope.heading = {
          opportunity_type: data.opportunity_type || [],
          page: parseInt(data.page),
          pageTotal: parseInt(data.pageTotal),
          total: parseInt(data.total),
          loaded: true,
          searching: false,
          searched: true
        };

        $scope.results = parseResults(data.results);
        $scope.$apply();
      }).fail(function () {
        $scope.results = [];
      });
    }, 100);

    $scope.submit = function () {
      queryAPI();
      return true;
    };

    $scope.queryKeyUp = function () {
      queryAPI();
    };

    $scope.next = function () {
      $scope.heading.page++;
      queryAPI();

      return false;
    };

    $scope.prev = function () {
      $scope.heading.page--;
      queryAPI();

      return false;
    };

    $scope.getFlagClass = function (code) {
      if (code === 'UK') {
        code = 'gb';
      }
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
      searching: false,
      total: 500
    };

    $scope.results = [];
  });

})(jQuery);
