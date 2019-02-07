jQuery(function () {
    var $ = jQuery;

    $(document).on('click', '.verify-my-email-unsubscribe', function (e) {
        e.preventDefault();

        console.log($("#unsubscribe-form").serialize());

        $.post("/unsubscribe", $("#unsubscribe-form").serialize(), function(data) {

            $(".js-form-type-email").hide();
            $("#unsubscribe-form").before('<p>Please check your email for a 6 digit code or click on the direct link</p>');
            $('#unsubscribe-form .js-form-item-token').show();
            $('#verify-code').removeClass('is-disabled').removeAttr('disabled');
            $('.verify-my-email-unsubscribe').hide();

        });
    });

    $('#unsubscribe-form .js-form-item-token').hide();


    $(document).on('click', '#unsubscribe-form #verify-code', function(e) {
        e.preventDefault();

        $('.verify-warning').remove();

        var formData = {token: $('#edit-token').val(), email: $('#edit-emailverification').val()};
        $.ajax({
            url: '/login',
            type: 'post',
            data: formData,
            success: function (data, textStatus, jQxhr) {

                if(data.link){
                    window.location.replace(data.link);
                }

                if(data.message){
                    $('#unsubscribe-form #verify-code').after('<p class="verify-warning">'+data.message+'</p>');
                }
            }
        });
    });
});