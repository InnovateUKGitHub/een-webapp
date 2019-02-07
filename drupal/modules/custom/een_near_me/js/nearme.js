(function ($, Drupal, drupalSettings) {

    var x = document.getElementById("locate-status");
    
    $('body').on('click', '.findMe', function (e) {
        e.preventDefault();
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition);
        } else { 
            x.innerHTML = "Geolocation is not supported by this browser.";
        }
    });
    function showPosition(position) {
        $(location).attr('href', '/ajax/user/set-county-from-client/' + position.coords.latitude + '/' + position.coords.longitude);
    }

})(jQuery, Drupal, drupalSettings);
