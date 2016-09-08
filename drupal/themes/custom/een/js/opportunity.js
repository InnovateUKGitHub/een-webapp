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
    
    
    
    $('.contents-list a[href*="#"]:not([href="#"])').click(function() {
        if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
            var target = $(this.hash);
            target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
            if (target.length) {
                $('html, body').animate({
                  scrollTop: target.offset().top
                }, 700);
              return false;
            }
        }
    });
    
    
});
