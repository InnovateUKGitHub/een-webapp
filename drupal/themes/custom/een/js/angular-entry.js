var een = window.angular.module('een', []);

een.controller('MainCtrl', [function () {

}]);

var deparam = function (params, coerce) {
    var obj = {},
        coerce_types = {'true': !0, 'false': !1, 'null': null};

    // Iterate over all name=value pairs.
    params.replace(/\+/g, ' ').split('&').forEach(function (v) {
        var param = v.split('='),
            key = decodeURIComponent(param[0]),
            val,
            cur = obj,
            i = 0,

            // If key is more complex than 'foo', like 'a[]' or 'a[b][c]', split it
            // into its component parts.
            keys = key.split(']['),
            keys_last = keys.length - 1;

        // If the first keys part contains [ and the last ends with ], then []
        // are correctly balanced.
        if (/\[/.test(keys[0]) && /\]$/.test(keys[keys_last])) {
            // Remove the trailing ] from the last keys part.
            keys[keys_last] = keys[keys_last].replace(/\]$/, '');

            // Split first keys part into two parts on the [ and add them back onto
            // the beginning of the keys array.
            keys = keys.shift().split('[').concat(keys);

            keys_last = keys.length - 1;
        } else {
            // Basic 'foo' style key.
            keys_last = 0;
        }

        // Are we dealing with a name=value pair, or just a name?
        if (param.length === 2) {
            val = decodeURIComponent(param[1]);

            // Coerce values.
            if (coerce) {
                val = val && !isNaN(val) && ((+val + '') === val) ? +val        // number
                    : val === 'undefined' ? undefined         // undefined
                        : coerce_types[val] !== undefined ? coerce_types[val] // true, false, null
                            : val;                                                          // string
            }

            if (keys_last) {
                // Complex key, build deep object structure based on a few rules:
                // * The 'cur' pointer starts at the object top-level.
                // * [] = array push (n is set to array length), [n] = array if n is
                //   numeric, otherwise object.
                // * If at the last keys part, set the value.
                // * For each keys part, if the current level is undefined create an
                //   object or array based on the type of the next keys part.
                // * Move the 'cur' pointer to the next level.
                // * Rinse & repeat.
                for (; i <= keys_last; i++) {
                    key = keys[i] === '' ? cur.length : keys[i];
                    cur = cur[key] = i < keys_last
                        ? cur[key] || ( keys[i + 1] && isNaN(keys[i + 1]) ? {} : [] )
                        : val;
                }

            } else {
                // Simple key, even simpler rules, since only scalars and shallow
                // arrays are allowed.

                if (Object.prototype.toString.call(obj[key]) === '[object Array]') {
                    // val is already an array, so push on the next value.
                    obj[key].push(val);

                } else if ({}.hasOwnProperty.call(obj, key)) {
                    // val isn't an array, but since a second value has been specified,
                    // convert val into an array.
                    obj[key] = [obj[key], val];

                } else {
                    // val is a scalar.
                    obj[key] = val;
                }
            }

        } else if (key) {
            // No value was defined, so set something meaningful.
            obj[key] = coerce
                ? undefined
                : '';
        }
    });

    return obj;
};

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
