(function ($) {
  'use strict';

  // https://docs.angularjs.org/api/ng/directive/ngSubmit
  // only prevents default form submit when action is removed
  $('#opportunity-search-form').removeAttr('action');

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

  var getParams = function () {
    var hash = window.location.hash;
    var data;

    if (hash) {
      var arr = window.location.hash.split('!/page/');
      var arr2 = arr[1].split('?');
      data = $.deparam(arr2[1]);
      data.page = parseInt(arr2[0]);
    }
    return data;
  };

  var setParams = function (page, q) {
    window.location.hash = '!/page/' + page + '?' + q;
  };

  var distance = function (date) {
    return (new Date().getTime() - date.getTime());
  };

  var indexOf = function (array, comparer) {
    for (var i = 0; i < array.length; i++) {
      if (array[i] === comparer) {
        return i;
      }
    }
    return -1;
  };

  var een = window.angular.module('een', []);

  // changed to stop conflicting with Drupal templating engine
  een.config(function ($interpolateProvider) {
    $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
  });

  een.filter('cut', function () {
    return function (value, wordwise, max, tail) {
      if (!value) return '';

      max = parseInt(max, 10);
      if (!max) return value;
      if (value.length <= max) return value;

      value = value.substr(0, max);
      if (wordwise) {
          var lastspace = value.lastIndexOf(' ');
          if (lastspace != - 1) {
            // Also remove . and , so its gives a cleaner result.
            if (value.charAt(lastspace - 1) == '.' || value.charAt(lastspace - 1) == ',') {
              lastspace = lastspace - 1;
            }
            value = value.substr(0, lastspace);
          }
      }

      return value + (tail || ' …');
    };
  });

  een.filter('unsafe', function ($sce) { return $sce.trustAsHtml; });

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
        day: '1 day',
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
          search: opts.search,
          opportunity_type: opts.opportunity_type,
          country: opts.country
        }
      });
    };

    return {
      search: search
    };
  });

  een.factory('checkboxFactory', function () {
    var setOpps = function (opps) {
      var inputs = $('#edit-opportunity-type--wrapper').find('input');
      for (var i = 0; i < inputs.length; i++) {
        var index = indexOf(opps, inputs[i].value);

        if (index > -1) {
          $(inputs[i]).prop('checked', true);
        }
      }
    };

    var setCountry = function (opps) {
      var inputs = $('#edit-country--wrapper').find('input');
      for (var i = 0; i < inputs.length; i++) {
        var index = indexOf(opps, inputs[i].value);

        if (index > -1) {
          $(inputs[i]).prop('checked', true);
        }
      }
    };

    return {
      setOpps: setOpps,
      setCountry: setCountry
    };
  });

  een.controller('MainCtrl', function ($scope, oppsFactory, timeFactory, $sce, checkboxFactory) {

    var parseResults = function (results) {
      return $.map(results, function (result) {
        var today = new Date();
        var date = new Date(result.date);
        var fiveDaysAgo = today.setDate(today.getDate() - 5);

        if (date.getTime() < fiveDaysAgo) {
          result.date = null;
        } else {
          result.date = timeFactory.inWords(distance(date)) + ' |';
        }

        return result;
      });
    };

    var queryAPI = function (paging) {
      $scope.meta.searching = true;
      $scope.$apply();

      if (!paging) {
        $scope.data.page = 1;
      }

      oppsFactory.search({
        page: $scope.data.page,
        opportunity_type: $scope.data.opportunity_type,
        country: $scope.data.country,
        search: $scope.data.search
      }).then(function (data) {
        $scope.data = {
          page: parseInt(data.page),
          opportunity_type: data.opportunity_type || [],
          country: data.country || [],
          pageTotal: parseInt(data.pageTotal),
          total: parseInt(data.total),
          search: data.search
        };

        $scope.meta = {
          loaded: true,
          searching: false,
          paging: false,
          searched: true
        };

        $scope.results = parseResults(data.results);
        $scope.$apply();

        setParams($scope.data.page, $.param({
          search: $scope.data.search,
          opportunity_type: $scope.data.opportunity_type,
          country: $scope.data.country
        }));

      }).fail(function () {
        $scope.results = [];
      });
    };

    var liveQueryAPI = debounce(function () {
      $scope.meta.searching = true;
      $scope.$apply();

      queryAPI();
    }, 300);

    $scope.submit = function () {
      queryAPI(true);
      return true;
    };

    $scope.queryKeyUp = function () {
      liveQueryAPI();
    };

    $scope.next = function () {
      $scope.data.page++;
      queryAPI(true);

      $('body').animate({
        scrollTop: 340
      }, 1000);

      return false;
    };

    $scope.prev = function () {
      $scope.data.page--;
      queryAPI(true);

      $('body').animate({
        scrollTop: 340
      }, 1000);

      return false;
    };

    $scope.getFlagClass = function (code) {
      if (code === 'UK') {
        code = 'gb';
      }
      return 'flag-icon flag-icon-' + code.toLowerCase();
    };

    $scope.selectOppCheckbox = function ($event) {
      var tar = $event.target;
      if (tar.type === 'checkbox') {
        if (tar.value && tar.checked) {
          if (indexOf($scope.data.opportunity_type, tar.value) === -1) {
            $scope.data.opportunity_type.push(tar.value);
          }
        } else {
          var index = indexOf($scope.data.opportunity_type, tar.value);
          if (index > -1) {
            $scope.data.opportunity_type.splice(index, 1);
          }
        }
      }
      liveQueryAPI();
    };

    $scope.selectCountryCheckbox = function ($event) {
      var tar = $event.target;
      if (tar.type === 'checkbox') {
        if (tar.value && tar.checked) {
          if (indexOf($scope.data.country, tar.value) === -1) {
            $scope.data.country.push(tar.value);
          }
        } else {
          var index = indexOf($scope.data.country, tar.value);
          if (index > -1) {
            $scope.data.country.splice(index, 1);
          }
        }
      }
      liveQueryAPI();
    };

    var data = getParams();

    $scope.meta = {
      loaded: true,
      paging: false,
      searched: false,
      searching: false
    };

    $scope.results = [];

    if (data) {
      $scope.data = {
        opportunity_type: data.opportunity_type || [],
        country: data.country || [],
        search: data.search,
        page: data.page
      };

      queryAPI(true);
      checkboxFactory.setOpps($scope.data.opportunity_type);
      checkboxFactory.setCountry($scope.data.country);
    } else {
      $scope.data = {
        opportunity_type: [],
        country: [],
        search: '',
        page: 1
      };
    }
  });

})(jQuery);
