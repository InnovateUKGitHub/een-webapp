jQuery(function () {
    var $ = jQuery;

    // var buttons = $("label input[type='radio'], label input[type='checkbox']");
    // new GOVUK.SelectionButtons(buttons);

    /* Basic toggle for form helper text */
    $(document).on('click', '.field-prefix', function () {
        var desc = $(this).next();
        $(desc).toggle();
    });

    $('textarea').focus(function() {
        $(this).closest('.js-form-type-textarea').find('div[id$="--description"]').toggleClass('show');
    });

    $('textarea').focusout(function() {
        $(this).closest('.js-form-type-textarea').find('div[id$="--description"]').toggleClass('show');
    });

    $( ".what-value" ).click(function() {
        $( ".een-value" ).slideToggle("slow");
    });

    $( ".js-filter-results, .js-search-action" ).on('click', function() {
        $('.filter-container').toggleClass('show');
    });
    /*
     *
     * accessibility
     */


    /*
     * In some instances get a link to behave like button on spacebar press
     */
    $("a.js-spb-click").on("keydown", function(e) {

        if (e.which == 32) {
            $(this)[0].click();
            e.preventDefault();
        }
    });

    setTimeout(function() {

        if($('.error-summary-list').length){
            $('.error-summary-list').find('li').each(function(){
                var text = $(this).html();
                $(this).html(text+'&nbsp;');
            });
        }

        if($('.continue-with-application-status').length){
            var content = $('.continue-with-application-status').html();
            $('.continue-with-application-status').html(content+'&nbsp;');
            $('.js-continue-focus').focus();
        }
    }, 1000);



    $(".textarea-max-length").after('<span class="char-count" aria-live="polite">' + (600 - $(this).val().length)+' characters remaining</span>');


    $('.textarea-max-length').keyup(function(e) {
        var tval = $(this).val(),
            tlength = tval.length,
            set = 600,
            remain = parseInt(set - tlength),
            charCountHTML = remain+' characters remaining',
            charTooManyHTML = Math.abs(remain)+' characters too many';
        if (remain < 0 ) {
            $(this).next('.char-count').html(charTooManyHTML);
        }
        else {
            $(this).next('.char-count').html(charCountHTML);
        }
        /*if (remain <= 0 && e.which !== 0 && e.charCode !== 0) {
            $(this).next('.char-count').val((tval).substring(0, tlength - 1))
        }*/
    });


    $( ".js-toggle-handle" ).click(function() {
        $( ".js-toggle-content" ).slideToggle("fast", function(){
            $('.js-toggle-state').hide();

            if($(this).is(":visible") == true) {
                $('.js-toggle-state.state--state-open').show();
            } else {
                $('.js-toggle-state.state--state-closed').show();
            }
        });
    });

    $( ".js-email" ).click(function() {
        $( ".js-email-signup" ).slideToggle("fast");
    });
    $( ".js-email-signup .close" ).click(function() {
        $( ".js-email-signup" ).slideToggle("fast");
    });
    /*
    * alert panel functionality
    */
    $('.close-alert').on('click', function(e){
        $(e.currentTarget).parents('.alert-banner').remove();
        /*
        * set cookie to hide the alert upon return
        */
        createCookie('showAlert','false',7);
    });

    var showAlert = readCookie('showAlert');
    if (showAlert === 'false') {
        $('.alert-banner').remove();
    } else if(!showAlert) {
        $('.alert-banner').fadeIn('fast');
    } else {
        $('.alert-banner').fadeIn('fast');
    }

    function createCookie(name,value,days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + value + expires + "; path=/";
    }

    function readCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    }

    function eraseCookie(name) {
        createCookie(name,"",-1);
    }

    /* homepage video banner */
    if ($('#data-vids').length) {
        var videos = $('#data-vids').attr('data-vids').split('*');
        jQuery('#video-bg').videobackground({
            videoSource: videos,
            loop: true,
            resizeTo: window,
            loadedCallback: function() {
                jQuery('.ui-video-background').remove();
                jQuery(this).videobackground('mute');
            },
            preloadCallback: function() {
                jQuery(this).videobackground('mute');
                jQuery(this).before('<div class="overlay"></div>');
            }
        });
    }

    $(document).ready(function() {
        var blogOwl = $('.blog-carousel');
        blogOwl.owlCarousel({
            loop: true,
            navRewind: false,
            nav:true,
            margin:20,
            responsive: {
                0: {
                  items: 1
                },
                767: {
                  items: 1
                },
                1000: {
                  items: 2
                }
            }
        });
    });



    var owl = $('.homepage-carousel');
    function homepageCarousel(){

        owl.owlCarousel({
            loop: true,
            navRewind: false,
            nav:true,
            margin:0,
            responsive: {
                0: {
                    items: 1
                },
                640: {
                    items: 2
                }
            }
        });
    }

    if ($(window).width() < 640) {
        homepageCarousel();
    }
    $(window).resize(function(){
        resizeWin();
    });

    function resizeWin(){

        if ($(window).width() < 640) {
            homepageCarousel();
        } else {
            owl.owlCarousel('destroy');
        }
    }




    //login/logout behaviour
    $(document).on('click', '.open-popup-link', function() {
        $.ajax({
            url: "/login?popover=true",
            type: 'GET',
            success: function (data) {
                $.magnificPopup.open({
                    items: {
                        src: $(data).find('#js-login-container').html() + '<button title="Close (Esc)" type="button" class="mfp-close">×</button>',
                        type: 'inline'
                    },
                    showCloseBtn: false,
                    closeOnContentClick: false
                });
            }
        });
    });

    var showLoginButtons = readCookie('loggedIn');

    if (showLoginButtons === 'false') {
        $('.js-li').removeClass('hide');
        $('.js-lo').addClass('hide');
    } else if(!showLoginButtons) {
        $('.js-li').removeClass('hide');
        $('.js-lo').addClass('hide');
    } else {
        $('.js-lo').removeClass('hide');
        $('.js-li').addClass('hide');
    }

    $(document).on('submit', '#een-login-form', function(e) {
        $.post("/login", $("#een-login-form").serialize(), function(data) {

            if(data.success == true){
                $('#login, .js-login-type').fadeOut();
                enableform();

                createCookie('loggedIn','true',1);
                $('.js-lo').removeClass('hide');
                $('.js-li').addClass('hide');

                if (!$(".op-details").length) {
                    window.location.href = '/my-account';
                } else {
                    $('.login-types').remove();
                    $.magnificPopup.close();
                }

            } else {
                $('.login-error-warning').remove();
                $('.js-form-item-password').before('<div class="login-error-warning error-summary"><p>'+data.message+'</p></div>');
            }
        });
        e.preventDefault();
    });


    function enableform(){
        $('.transp').removeClass('transp');
        $('.form-opportunities').find('input').attr('disabled', false).removeClass('is_disabled');
        $('.form-opportunities').find('textarea').attr('disabled', false).removeClass('is_disabled');
    }



});
