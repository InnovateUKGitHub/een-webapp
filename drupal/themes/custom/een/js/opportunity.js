jQuery(function () {
    var $ = jQuery,
        phoneLink = $('.edit-email--phone-number'),
        phoneField = $('.eoi-phone-number'),
        hiddenField = $('.phoneStatus');

    phoneField.hide();

    phoneLink.click(function() {
        hiddenField.val(phoneField.is(':visible') ? '0' : '1');
        phoneField.toggle();
    });
    
    function smoothScroll(t) {
        if (location.pathname.replace(/^\//,'') == t.pathname.replace(/^\//,'') && location.hostname == t.hostname) {
            var target = $(t.hash);
            target = target.length ? target : $('[name=' + t.hash.slice(1) +']');
            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top
                }, 700);
                return false;
            }
        }
    }

    $('.contents-list a[href*="#"]:not([href="#"]),' +
        '.error-summary a[href*="#"]:not([href="#"]),' +
        '.status-summary a[href*="#"]:not([href="#"])').click(function() {
        smoothScroll(this);
    });
});
