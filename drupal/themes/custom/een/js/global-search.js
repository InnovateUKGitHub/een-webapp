
jQuery(function () {

    var $ = jQuery;
    var windowPath = window.location.pathname;

    if (windowPath === '/search' || windowPath.indexOf('/search') !== -1) {

        var value = window.location.pathname.split("/").pop();
        if(value === 'all'){
            var value = ' ';
        }

        var find = '-';
        var re = new RegExp(find, 'g');

        var str = value.replace(re, ' ');

        $(document).ajaxComplete(function (data) {

            $('input[name="search_api_fulltext"]').attr("placeholder", "What are you looking for?");

            if($('input[name="search_api_fulltext"]').val() === ''){
                $('input[name="search_api_fulltext"]').val(str);
            }

            $('div[id^="edit-type"]  input[checked="checked"]').parent().addClass('selected');
            $('div[id^="edit-type"] label:first-of-type').contents().last()[0].textContent='All';


            if(history.pushState) {
                history.pushState(null, null, $('input[name="search_api_fulltext"]').val());
            }
        });
    }

});
