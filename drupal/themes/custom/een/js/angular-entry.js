(function () {
  'use strict';

  var deparam = function( params, coerce ) {
      var obj = {},
      coerce_types = { 'true': !0, 'false': !1, 'null': null };

      // Iterate over all name=value pairs.
      params.replace(/\+/g, ' ').split('&').forEach(function(v){
          var param = v.split( '=' ),
          key = decodeURIComponent( param[0] ),
          val,
          cur = obj,
          i = 0,

          // If key is more complex than 'foo', like 'a[]' or 'a[b][c]', split it
          // into its component parts.
          keys = key.split( '][' ),
          keys_last = keys.length - 1;

          // If the first keys part contains [ and the last ends with ], then []
          // are correctly balanced.
          if ( /\[/.test( keys[0] ) && /\]$/.test( keys[ keys_last ] ) ) {
              // Remove the trailing ] from the last keys part.
              keys[ keys_last ] = keys[ keys_last ].replace( /\]$/, '' );

              // Split first keys part into two parts on the [ and add them back onto
              // the beginning of the keys array.
              keys = keys.shift().split('[').concat( keys );

              keys_last = keys.length - 1;
          } else {
              // Basic 'foo' style key.
              keys_last = 0;
          }

          // Are we dealing with a name=value pair, or just a name?
          if ( param.length === 2 ) {
              val = decodeURIComponent( param[1] );

              // Coerce values.
              if ( coerce ) {
                  val = val && !isNaN(val) && ((+val + '') === val) ? +val        // number
                  : val === 'undefined'                       ? undefined         // undefined
                  : coerce_types[val] !== undefined           ? coerce_types[val] // true, false, null
                  : val;                                                          // string
              }

              if ( keys_last ) {
                  // Complex key, build deep object structure based on a few rules:
                  // * The 'cur' pointer starts at the object top-level.
                  // * [] = array push (n is set to array length), [n] = array if n is
                  //   numeric, otherwise object.
                  // * If at the last keys part, set the value.
                  // * For each keys part, if the current level is undefined create an
                  //   object or array based on the type of the next keys part.
                  // * Move the 'cur' pointer to the next level.
                  // * Rinse & repeat.
                  for ( ; i <= keys_last; i++ ) {
                      key = keys[i] === '' ? cur.length : keys[i];
                      cur = cur[key] = i < keys_last
                      ? cur[key] || ( keys[i+1] && isNaN( keys[i+1] ) ? {} : [] )
                      : val;
                  }

              } else {
                  // Simple key, even simpler rules, since only scalars and shallow
                  // arrays are allowed.

                  if ( Object.prototype.toString.call( obj[key] ) === '[object Array]' ) {
                      // val is already an array, so push on the next value.
                      obj[key].push( val );

                  } else if ( {}.hasOwnProperty.call(obj, key) ) {
                      // val isn't an array, but since a second value has been specified,
                      // convert val into an array.
                      obj[key] = [ obj[key], val ];

                  } else {
                      // val is a scalar.
                      obj[key] = val;
                  }
              }

          } else if ( key ) {
              // No value was defined, so set something meaningful.
              obj[key] = coerce
              ? undefined
              : '';
          }
      });

      return obj;
  };

  var $ = jQuery;

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
      data = deparam(arr2[1]);
      data.page = parseInt(arr2[0]);
    }
    return data;
  };

  var setParams = function (page, q) {
    var newHash = '!/page/' + page + '?' + q;
    if ('#' + newHash !== window.location.hash) {
      window.location.hash = '!/page/' + page + '?' + q;
    } else {
      window.dispatchEvent(new HashChangeEvent('hashchange'));
    }
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
  een.config(['$interpolateProvider', function ($interpolateProvider) {
    $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
  }]);

  een.filter('cut', function () {
    return function (value, wordwise, max, tail, id) {
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

      return value + (tail || ' …') + '<a href="/opportunities/' + id + '">more</a>';
    };
  });

  een.filter('unsafe', ['$sce', function ($sce) { return $sce.trustAsHtml; }]);

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

  een.controller('MainCtrl', ['$scope', 'oppsFactory', 'timeFactory', '$sce', 'checkboxFactory', function ($scope, oppsFactory, timeFactory, $sce, checkboxFactory) {

    var changingHash = false;

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
      if (!$scope.meta.searching) {
        $scope.meta.searching = true;
      }

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
          search: $scope.data.search
        };

        $scope.meta = {
          loaded: true,
          searching: false,
          paging: false,
          searched: true
        };

        $scope.results = parseResults(data.results);
        $scope.$apply();

        changingHash = true;

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
    }, 700);

    $scope.submit = function () {
      queryAPI(true);
      return true;
    };

    $scope.queryKeyUp = function () {
      if ($scope.data.search.length > 2) {
        liveQueryAPI();
      }
    };

    $scope.next = function ($event) {
      $event.preventDefault();
      $scope.data.page++;
      queryAPI(true);

      $('body, html').animate({
        scrollTop: 340
      }, 1000);

      return false;
    };

    $scope.prev = function ($event) {
      $event.preventDefault();
      $scope.data.page--;
      queryAPI(true);

      $('body, html').animate({
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
    };

    var initData = function () {
      var data = getParams();

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
    };

    $scope.meta = {
      loaded: true,
      paging: false,
      searched: false,
      searching: false
    };

    $scope.results = [];

    initData();

    window.onhashchange = function () {
      if (!changingHash) {
        $scope.meta.searching = true;
        $scope.$apply();

        initData();
      } else {
        changingHash = false;
      }
    };
  }]);

})();
