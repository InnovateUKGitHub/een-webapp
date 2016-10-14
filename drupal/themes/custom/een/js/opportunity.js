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
    
    if($('.accordion-content').length){
        var panel = $('.accordion-content');
            panel.before('<a role="button" tabindex="0" class="accordion-toggle" href="#" aria-label="Toggle checkboxes"> Newsletter </a>');
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
  
  
});
