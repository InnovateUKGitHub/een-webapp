jQuery(function () {
    var $ = jQuery,
        eoiLiveEdit = $('.edit-eoi'),
        eoiLiveUpdate = $('.update-eoi'),
        url = '/opportunities/eoi/update/';

    eoiLiveEdit.click(function (e) {
        e.preventDefault();
        $(this).parent().prev().find('p').addClass('hidden');
        $(this).parent().prev().find('form').removeClass('hidden');
        $(this).parent().prev().find('textarea').focus();
        $(this).addClass('hidden');
        $(this).next().removeClass('hidden')
    });

    eoiLiveUpdate.click(function (e) {
        e.preventDefault();

        var textarea = $(this).parent().prev().find('textarea');

        $.get(url + textarea.attr('name'), {value: textarea.val()});

        $(this).parent().prev().find('p').removeClass('hidden');
        $(this).parent().prev().find('form').addClass('hidden');
        $(this).parent().prev().find('textarea').focus();
        $(this).parent().prev().find('p').html(textarea.val());
        $(this).addClass('hidden');
        $(this).prev().removeClass('hidden')

    });
});
