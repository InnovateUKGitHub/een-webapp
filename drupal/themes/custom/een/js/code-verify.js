jQuery(function () {
    var $ = jQuery;

    $('#edit-token').after('<input type="submit" id="verify-code" disabled class="button button--primary js-form-submit form-submit is-disabled" value="Continue"/>');
    $('.review-verify #edit-token, .review-verify .js-form-submit').removeClass('is-disabled').attr('disabled', false);


    $(document).on('click', '#verify-code', function(e){

        var verifyEmail = $('.verify-email');
        $(verifyEmail).addClass('loading-verify-email');
        $('p.verify-warning').remove();
        $('.js-alert-notifications-email, .js-alert-notifications-success, .js-alert-notifications-error').hide();

        var formData = {token:$('#edit-token').val(), email:$('#edit-emailverification').val()};

        $.ajax({
            url: '/login',
            type: 'post',
            data: formData,
            success: function( data, textStatus, jQxhr ){

                if(data.status == 'failure'){
                    $(verifyEmail).append('<p class="verify-warning">'+data.message+'</p>').removeClass('loading-verify-email');
                    $('.js-alert-notifications-error').html('<p class="verify-warning">'+data.message+'</p>').show();
                } else {
                    if($('.js-alert-add').length){
                        $('.js-alert-form-content').addClass('hide');
                        $('.js-alert-notifications-success').show();
                        $('#alert-signup-form').submit();
                    } else {
                        $(verifyEmail).remove();
                        $('.js-login-type').remove();
                        $('.transp').removeClass('transp');

                        $('.form-opportunities').find('input').attr('disabled', false).removeClass('is_disabled');
                        $('.form-opportunities').find('textarea').attr('disabled', false).removeClass('is_disabled');
                        $('.form-opportunities').prepend('<div class="verify-email verify-email-complete"><p>Thank you for verifying your email.</p></div>');
                        $('.js-spb-click').attr('disabled', false);


                        var reset = $('.verify-email-complete').offset();
                        $('body, html').animate({
                            scrollTop: reset.top
                        }, 250);
                    }

                }
            },
            error: function( jqXhr, textStatus, errorThrown ){

            }
        });

        e.preventDefault();

    });


    var form = document.querySelector('.form-opportunities');

    $(document).on('submit', '.form-opportunities', function(e){

        e.preventDefault();

        if (!form.checkValidity()) {
            return;
        }

        if($('#edit-description').length) {
            if ($('#edit-description').val().length < 150 || $('#edit-description').val().length > 600) {
                $('#edit-description').focus();
                $('#edit-description').next().next('.char-required').addClass('char-error');
                return;
            }

            if ($('#edit-interest').val().length < 150 || $('#edit-interest').val().length > 600) {
                $('#edit-interest').focus();
                $('#edit-interest').next().next('.char-required').addClass('char-error');
                return;
            }
            if ($('#edit-more').val().length > 600) {
                $('#edit-more').focus();
                $('#edit-more').next().addClass('char-error');
                return;
            }
        }

        $.ajax({
            url: '/valid-user',
            type: 'get',
            success: function( data, textStatus, jQxhr ){
                if(data.loggedIn){
                    form.submit();
                } else {

                    $([document.documentElement, document.body]).animate({
                        scrollTop: $(".js-form-item-token").offset().top - 50
                    }, 500);

                    $(".entered-verification").focus();
                }
            },
            error: function( jqXhr, textStatus, errorThrown ){

            }
        });
    });
});