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

    $( ".filter-container .mobile-only .button" ).click(function() {
        $(this).toggleClass('open');
        $( "form" ).slideToggle("slow");
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
        console.log('SHOW BANNER');
        $('.alert-banner').fadeIn('fast');
    } else {
        console.log('SHOW BANNER');
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


    $(document).on('click', '.js-try-it-out', function(){
        createCookie('sneakpeak', 1, 1);
        $('#overlay-sneak-peak').remove();
    });

    if(!readCookie('sneakpeak')){
        $html = '<div id="overlay-sneak-peak"><div class="sp-content"><span>Here\'s a look at our new website</span><button class="btn btn-default try-it-out js-try-it-out">Try it out</button> <a href="http://www.enterprise-europe.co.uk" class="return-to-old">Go back to the old website</a> </div></div>';
        $('body').prepend($html);
    }
    
    $(document).ready(function() {
        var owl = $('.owl-carousel');
        owl.owlCarousel({
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
});
