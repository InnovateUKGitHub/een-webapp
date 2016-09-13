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
});
