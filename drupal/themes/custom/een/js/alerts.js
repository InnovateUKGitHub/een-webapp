jQuery(function () {
    var $ = jQuery;

    $('.js-update-pod-alert .form-checkbox').on('change', function(e){
        var form=$(this).closest('.js-update-pod-alert');

        e.preventDefault();
        $.ajax({
            type: "POST",
            url: $(form).attr('action'),
            data: form.serialize(),
            cache: false,
            success: function(){

            }
        });
    });

    $('.js-remove-alert').on('click', function(e){
        e.preventDefault();
        var row = $(this).closest('.js-alert-row');
        $.get($(this).attr('href'));
        $(row).fadeOut();
    });
});