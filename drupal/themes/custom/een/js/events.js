(function () {
    'use strict';

    if (window.location.pathname === '/events') {

        var $ = jQuery;

        // https://docs.angularjs.org/api/ng/directive/ngSubmit
        // only prevents default form submit when action is removed
        $('#event-search-form').removeAttr('action');

        // changed to stop conflicting with Drupal templating engine
        een.config(['$interpolateProvider', function ($interpolateProvider) {
            $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
        }]);

        een.filter('getEventDay', function () {
            return function (value) {
                var j = value % 10,
                    k = value % 100;

                if (j == 1 && k != 11) {
                    return value + "st";
                }

                if (j == 2 && k != 12) {
                    return value + "nd";
                }

                if (j == 3 && k != 13) {
                    return value + "rd";
                }
                return value + "th";
            };
        });

        een.filter('cut', function () {
            return function (value, wordwise, max, tail, type, url, id) {
                if (!value) return '';

                max = parseInt(max, 10);
                if (!max) return value;
                if (value.length <= max) return value;

                value = value.substr(0, max);
                if (wordwise) {
                    var lastspace = value.lastIndexOf(' ');
                    if (lastspace != -1) {
                        // Also remove . and , so its gives a cleaner result.
                        if (value.charAt(lastspace - 1) == '.' || value.charAt(lastspace - 1) == ',') {
                            lastspace = lastspace - 1;
                        }
                        value = value.substr(0, lastspace);
                    }
                }
                if (url == null) {
                    url = '/events/' + id;
                }

                return value + (tail || ' …') + '<a href="' + url + '">more</a>';
            };
        });

        een.filter('unsafe', ['$sce', function ($sce) {
            return $sce.trustAsHtml;
        }]);

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

        een.factory('eventsFactory', function () {
            var search = function (opts) {
                return $.ajax({
                    url: 'events/_ajax',
                    data: {
                        page: opts.page,
                        resultPerPage: opts.resultPerPage,
                        search: opts.search,
                        date_type: opts.date_type,
                        date_from: opts.date_from,
                        date_to: opts.date_to,
                        country: opts.country
                    }
                });
            };

            return {
                search: search
            };
        });

        een.factory('checkboxFactory', function () {
            var setDateType = function (events) {
                var inputs = $('#edit-date-type--wrapper').find('input');
                for (var i = 0; i < inputs.length; i++) {
                    var index = indexOf(events, inputs[i].value);
                    var $item = $(inputs[i]);
                    var $parent = $item.parent();

                    if (index > -1) {
                        $item.prop('checked', true);
                        $parent.addClass('selected');
                    } else {
                        $item.prop('checked', false);
                        $parent.removeClass('selected');
                    }
                }
            };

            var setCountry = function (events) {
                var inputs = $('#edit-country--wrapper').find('input');
                for (var i = 0; i < inputs.length; i++) {
                    var index = indexOf(events, inputs[i].value);
                    var $item = $(inputs[i]);
                    var $parent = $item.parent();

                    if (index > -1) {
                        $item.prop('checked', true);
                        $parent.addClass('selected');
                    } else {
                        $item.prop('checked', false);
                        $parent.removeClass('selected');
                    }
                }
            };

            return {
                setDateType: setDateType,
                setCountry: setCountry
            };
        });

        een.controller('MainCtrl', ['$scope', 'eventsFactory', 'timeFactory', '$sce', 'checkboxFactory', function ($scope, eventsFactory, timeFactory, $sce, checkboxFactory) {
            var changingHash = false;

            var parseResults = function (results) {
                return $.map(results, function (result) {
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
                eventsFactory.search({
                    page: $scope.data.page,
                    resultPerPage: $scope.data.resultPerPage,
                    country: $scope.data.country,
                    date_type: $scope.data.date_type,
                    date_from: $scope.data.date_from,
                    date_to: $scope.data.date_to,
                    search: $scope.data.search
                }).then(function (data) {
                    $scope.data = {
                        page: parseInt(data.page),
                        country: data.country || [],
                        date_type: data.date_type || [],
                        date_from: data.date_from,
                        date_to: data.date_to,
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
                        date_type: $scope.data.date_type,
                        date_from: $scope.data.date_from,
                        date_to: $scope.data.date_to,
                        country: $scope.data.country
                    }));

                    checkboxFactory.setDateType($scope.data.date_type);
                    checkboxFactory.setCountry($scope.data.country);

                }).fail(function () {
                    $scope.results = [];
                });
            };

            var liveQueryAPI = debounce(function () {
                $scope.meta.searching = true;
                $scope.$apply();

                queryAPI();
            }, 700);

            var liveQueryAPIDirect = debounce(function () {
                $scope.meta.searching = true;
                $scope.$apply();

                queryAPI();
            });

            $scope.submit = function () {
                queryAPI(true);
                return true;
            };

            $scope.queryKeyUp = function () {
                if ($scope.data.search.length > 2) {
                    liveQueryAPI();
                }
            };

            $scope.dateChange = function () {
                liveQueryAPI();
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
                if (code === null) {
                    return 'flag-icon flag-icon-gb';
                }
                if (code === 'UK') {
                    code = 'gb';
                }
                return 'flag-icon flag-icon-' + code.toLowerCase();
            };

            $scope.selectDateTypeCheckbox = function ($event) {
                var tar = $event.target;
                if (tar.type === 'checkbox') {

                    $scope.data.date_type = [];

                    if (tar.value && tar.checked) {
                        $scope.data.date_type.push(tar.value);
                        if (tar.value == 'any') {
                            liveQueryAPIDirect();
                        } else {
                            var item = $('#edit-date-type-any');
                            item.prop('checked', false);
                            item.parent().removeClass('selected');
                        }
                    }
                }
            };

            $scope.selectCountryCheckbox = function ($event) {
                var tar = $event.target;

                if (tar.type === 'checkbox') {
                    $scope.data.country = [];
                    if (tar.value && tar.checked) {
                        $scope.data.country.push(tar.value);
                    }
                    liveQueryAPIDirect();
                }
            };

            var initData = function () {
                var data = getParams();

                if (data) {
                    $scope.data = {
                        country: data.country || [],
                        date_type: data.date_type || [],
                        date_from: data.date_from,
                        date_to: data.date_to,
                        search: data.search,
                        page: data.page,
                        resultPerPage: data.resultPerPage
                    };

                    queryAPI(true);

                } else {
                    $scope.data = {
                        country: ['anywhere'],
                        date_type: ['any'],
                        date_from: '',
                        date_to: '',
                        search: '',
                        page: 1,
                        resultPerPage: 20
                    };

                    if ($scope.meta.searched) {
                        queryAPI(true);
                    }
                }

                var dateFrom = $("#edit-date-from"),
                    dateTo = $("#edit-date-to");

                dateFrom.datepicker({
                    dateFormat: 'dd/mm/yy',
                    onSelect: function(date) {
                        $('#edit-date-type-pick').click();
                        $scope.data.date_type.push('pick');
                        $scope.data.date_from = date;
                        if ($scope.data.date_to !== '') {
                            queryAPI();
                        }
                    }
                });
                dateTo.datepicker({
                    dateFormat: 'dd/mm/yy',
                    onSelect: function(date) {
                        $('#edit-date-type-pick').click();
                        $scope.data.date_type.push('pick');
                        $scope.data.date_to = date;
                        if ($scope.data.date_from !== '') {
                            queryAPI();
                        }
                    }
                });

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
    }
})();
