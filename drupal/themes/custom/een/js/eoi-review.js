jQuery(function () {
    var $ = jQuery,
        eoiLiveEdit = $('.edit-eoi'),
        eoiLiveUpdate = $('.update-eoi'),
        url = '/opportunities/eoi/update/';

    /* Display text area on edit click */
    eoiLiveEdit.click(function (e) {
        e.preventDefault();
        $(this).parent().prev().find('p').addClass('hidden');
        $(this).parent().prev().find('form').removeClass('hidden');
        $(this).parent().prev().find('textarea').focus();
        $(this).addClass('hidden');
        $(this).next().removeClass('hidden')
    });

    /* Validate data and submit it the the backend */
    eoiLiveUpdate.click(function (e) {
        e.preventDefault();

        var textarea = $(this).parent().prev().find('textarea');

        if (textarea.hasClass('required') == true && textarea.val().trim() == '') {
            textarea.parent().addClass('error');
            textarea.parent().find('.error-message').html('This is required to complete your application.');
            return;
        }

        textarea.parent().removeClass('error');
        textarea.parent().find('.error-message').html('');

        $.get(url + textarea.attr('name'), {value: textarea.val()});

        $(this).parent().prev().find('p').removeClass('hidden');
        $(this).parent().prev().find('form').addClass('hidden');
        $(this).parent().prev().find('textarea').focus();
        $(this).parent().prev().find('p').html(textarea.val());
        $(this).addClass('hidden');
        $(this).prev().removeClass('hidden')

    });

    /* Disable form on submit when verifying the email */
    $(document).on('submit', '#email-verification-form', function () {
        $('input[type=submit]', this).attr('disabled', 'disabled');
        $(this).bind('submit', function (e) {
            e.preventDefault();
        });
    });
});
