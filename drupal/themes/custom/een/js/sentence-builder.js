jQuery(function () {
    var $ = jQuery,
        $modal = $('.modal');
    
    
    
    $('#keywords').on('keyup', function(){
        $('#search').val($('#keywords').text());
    });
 
    $(".chosen-select-multiple").chosen({
        disable_search:false
    }); 
    
    
    /* opportunity type dropdown (1st) */
    $('#search_type').on('click', function(){
       $('.modal .form-item').hide();
        openModal();
        $('.modal .form-types').show();
    });
    
    
    $('.explore-form #edit-opportunity-type input').on('change', function() {
       var text = $('input:checked', '.explore-form #edit-opportunity-type').parent().text(); 
       if(text){
           $('#search_type').html(text);
           closeModal();
       }
    });
    
    
    /*
     * Country type selection
     * 
     */
    $('#search_country').on('click', function(){
        $('.modal .form-item').hide();
        openModal();
        $('.modal .form-countries').show();
        
         var val = $('input:checked', '.explore-form #edit-country-choice').val(); 
         if(val == ''){
             $('.modal .form-item-country').show();
         }
    });
    
    /*
     * Specific country selection
     * 
     */
    $('.explore-form #edit-country-choice input').on('change', function() {
        var text = $('input:checked', '.explore-form #edit-country-choice').parent().text(); 
        var val = $('input:checked', '.explore-form #edit-country-choice').val(); 
        var $countryList = $('.modal .form-item-country');
        
        if(text){
            if(val == ''){
                $countryList.show();
                $countryList.prepend('<span class="sb-close-modal">Close</span>');
            } else {
                $('#search_country').html(text);
                closeModal();
            }
        }
    });
    
    /*
     *  close modal for selecting multiple countries. 
     * 
     */
    $(document).on('click', '.sb-close-modal', function(){
        closeModal();
        
        /* get array of selected values */
        var countries = $("#edit-country").val();
        
        $.each( countries, function( key, value ) {
            //add to sentence builder
        });
    });
    
    
    function openModal(){
        $modal.show();
        if ($(window).width() < 640) {
            $('body').append('<div class="modal-overlay"></div>');
            $modal.show().css('top', $(document).scrollTop() + 30+'px');
        }
    }
    function closeModal(){
        $modal.hide();
        $('.modal-overlay').remove();
    }
    
});
