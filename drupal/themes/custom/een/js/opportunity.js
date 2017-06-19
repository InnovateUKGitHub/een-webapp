jQuery(function () {
    var $ = jQuery,
        phoneLink = $('.edit-email--phone-number'),
        phoneField = $('.eoi-phone-number'),
        hiddenField = $('.phoneStatus');

    phoneField.hide();

    phoneLink.click(function () {
        hiddenField.val(phoneField.is(':visible') ? '0' : '1');
        phoneField.toggle();
    });

    function smoothScroll(t) {
        if (location.pathname.replace(/^\//, '') == t.pathname.replace(/^\//, '') && location.hostname == t.hostname) {
            var target = $(t.hash);
            target = target.length ? target : $('[name=' + t.hash.slice(1) + ']');
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
        '.status-summary a[href*="#"]:not([href="#"])').click(function (e) {
        e.preventDefault();
        smoothScroll(this);        
        $($(this).attr('href')).focus();
    });
    
    
    
    
    var newletterFormSection = $('#edit-newsletter--wrapper');
    if($(newletterFormSection).length){
        
        $(newletterFormSection).find('.block-label').each(function(i, item){
             if(i > 1){
                $(item).addClass('indented');
            }
        });

        $('.block-label.indented').wrapAll($('<div class="accordion-content"/>'));
    }
    
    if($('#edit-newsletter--wrapper .accordion-content').length){

        var panel = $('.accordion-content');
            panel.before('<a role="button" tabindex="0" class="accordion-toggle" href="#" aria-label="Toggle checkboxes"> Newsletters (choose) </a>');
            panel.hide();

        var toggle = $('.accordion-toggle');

        $(document).on('click', '.accordion-toggle', function(e){
            e.preventDefault();
            var accordionContent = $(this).next(panel);

            //Expand or collapse this panel
            $(accordionContent).slideToggle('slow', function(){
                 $(toggle).toggleClass('visible', $(this).is(':visible'));
            });
        });
    }

    $('#email-verification-form').on('submit', function(e) {

        if(!$('#edit-token').val()){
            var data = $("#email-verification-form :input").serializeArray();
            $.get('/opportunities/' + data[1].value + '/_ajax', 'email=' + data[0].value);

            $('.js-form-item-emailverification, .js-rp-message, #edit-submit--2').hide();
            $('.email-verification-sent').fadeIn('fast');

            $('#edit-token').removeClass('disabled').attr('disabled', false);
            $('#verify-code').removeClass('is-disabled').attr('disabled', false);
            e.preventDefault();
        }

    });

    $('.js-not-received').on('click', function(e){

        $('.email-verification-sent').hide();
        $('.js-form-item-emailverification, .js-rp-message, #edit-submit--2').show();

        e.preventDefault();
    })


    $('.js-login-type').on('change', 'input', function(e){
        var id = $(this).val();
        $('.login-types').hide();
        $('#'+id).show();
    });

    $('#edit-token').after('<input type="submit" id="verify-code" disabled class="button button--primary js-form-submit form-submit is-disabled" value="Continue"/>')


    var p = $('.js-rp-message').detach();
    $('.js-form-item-emailverification').after(p);

    var a = $('#edit-actions--2').detach();
    $('.js-form-item-emailverification').after(a);



    $('#een-login-form').on('submit', function(e) {
        e.preventDefault();
        $('#login').html('<p>Login method not available at this time.');
    });


});
