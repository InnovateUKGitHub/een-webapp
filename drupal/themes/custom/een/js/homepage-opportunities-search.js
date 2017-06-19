(function () {
    'use strict';

    if (window.location.pathname === '/') {

        var $ = jQuery;

        // https://docs.angularjs.org/api/ng/directive/ngSubmit
        // only prevents default form submit when action is removed
        $('#opportunity-search-form').removeAttr('action');

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
                    if (lastspace != -1) {
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

            var setCountry = function (opps) {
                var inputs = $('#edit-country--wrapper').find('input');
                for (var i = 0; i < inputs.length; i++) {
                    var index = indexOf(opps, inputs[i].value);
                    var $item = $(inputs[i]);
                    var $parent = $item.parent();

                    if (index > -1) {
                        $item.prop('checked', true);
                        $parent.addClass('selected');
                    } else {
                        $item.prop('checked', false);
                        $parent.removeClass('selected');
                    }

                    $('.selected-countries').html(opps.length + ' Selected');
                }
            };


            var setFacets = function (opps) {
                $('.facet-counts').remove();

                var types = opps.types.buckets;

                for (var i = 0, len = types.length; i < len; i++) {
                    var code = types[i].key.toUpperCase();
                    $('#edit-opportunity-type--wrapper input[value="' + code + '"]').parent('label').append('<span class="facet-counts">' + types[i].doc_count + '</span>');
                }

            };

            return {
                setOpps: setOpps,
                setCountry: setCountry,
                setFacets: setFacets
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

                    checkboxFactory.setOpps($scope.data.opportunity_type);
                    checkboxFactory.setCountry($scope.data.country);
                    checkboxFactory.setFacets(data.aggregations);

                    $('#auto-2 .sb-results').html('<span>'+data.total + '</span> opportunities found');


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
                    $('.js-opportunities-list').removeClass('initial-no-vis').addClass('morphed-in');
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
                    liveQueryAPIDirect();
                }
            };

            $scope.selectCountryCheckbox = function ($event) {
                var tar = $event.target;

                if (tar.value == 'anywhere' && tar.checked) {
                    $scope.data.country = ['anywhere'];
                } else if (tar.value == 'anywhere') {
                    /* is europe selected ? */
                    if ($("input[value=europe]").is(':checked')) {
                        $scope.data.country = ['europe'];
                    }
                }

                if (tar.value == 'europe' && tar.checked) {
                    $scope.data.country = ['europe'];
                }


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
                    liveQueryAPIDirect();
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

                } else {
                    $scope.data = {
                        opportunity_type: [],
                        country: [],
                        search: '',
                        page: 1
                    };

                    if ($scope.meta.searched) {
                        queryAPI(true);
                    }
                    queryAPI(true);
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


            $("#auto-2 #edit-search").autocomplete({
                source: "/search/autosuggest",
                minLength: 2,
                classes: {
                    "ui-autocomplete": "highlight"
                },
                appendTo: ".js-form-item-search",
                select: function( event, ui ) {
                    console.log( "Selected: " + ui.item.value + " aka " + ui.item.id );

                    $scope.data.search = ui.item.value;
                    liveQueryAPI();
                },
                open: function (e, ui) {
                    var acData = $(this).data('ui-autocomplete');
                    acData
                        .menu
                        .element
                        .find('li')
                        .each(function () {
                            var me = $(this);
                            var keywords = acData.term.split(' ').join('|');
                            me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
                        });
                }
            });

            $('#auto-2 #edit-submit').click(function(e){
                liveQueryAPI();
                e.preventDefault();
                window.location = 'partnering-opportunities#!/page/1?search='+$scope.data.search;
            });


            if($scope.data.search)
            {
                $('.js-opportunities-list').removeClass('initial-no-vis').addClass('morphed-in');
            }

        }]);
    }

})();
