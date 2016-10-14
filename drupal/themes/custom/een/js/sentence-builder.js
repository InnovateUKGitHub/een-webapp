jQuery(function () {
    var $ = jQuery,
        $modal = $('.modal');

    var debounce = function (func, wait, immediate) {
      var timeout;
      return function () {
        var context = this;
        var args = arguments;
        var later = function () {
          timeout = null;
          if (!immediate) {
            func.apply(context, args);
          }
        };
        var callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) {
          func.apply(context, args);
        }
      };
    };

    var ajaxSearch = function () {
      var search = $('#search').val();
      var country;
      var opportunity_type = [$('input:checked', '.explore-form #edit-opportunity-type').val()];
      var checkboxes = [$('input:checked', '.explore-form #edit-country-choice').val()];

      if (checkboxes && checkboxes[0] === '') {
        country = $(".chosen-select-multiple").val();
      } else {
        country = checkboxes;
      }

      if (country && country[0] === 'anywhere') {
        country[0] = '';
      }

      return $.ajax({
        url: 'opportunities/_count',
        data: {
          search: search,
          opportunity_type: opportunity_type,
          country: country
        }
      }).then(function (data) {
        $('.sb-results').html('<span>'+data.total + '</span> opportunities found');
        
        
        var str = $.param({ search: search, opportunity_type: opportunity_type, country: country });
        var url = '/opportunities#!/page/1?'+str;
        $('.js-sb-view-results').attr('href', url);
        
      });
    };

    var updateResults = debounce(function() {
      $('.sb-results').addClass('transp');
      ajaxSearch().then(function() {
        $('.sb-results').removeClass('transp');
      });
      $('.js-sb-view-results').removeClass('disabled');
      
    }, 600);


    /*
     * Keyword search 
     */
    $('#keywords').on('keyup', function() {
        $('#search').val($('#keywords').text());
        if($('#keywords').text().length > 20){
            $(this).removeClass('block');
        } else {
            $(this).addClass('block');
        }
        updateResults();
    });

    $('#keywords').click(function() {
        if ($('#search').val() === '') {
            $(this).addClass('block');
            $('#keywords').text('');
            $('#search').val('');
        }
        updateResults();
    });
    
    
    /* 
     * initialise chosen select for multiple country select 
     */

    $(".chosen-select-multiple").chosen({
        disable_search:false
    });

  
    
    $(".chosen-select-multiple").chosen().change(function(){
        updateResults();
         
        var countries = $("#edit-country").val();
        if(countries.length == 1){
            $('#search_country .chosen').text(countries.length + ' country selected');
        } else {
            $('#search_country .chosen').text(countries.length + ' countries selected');
        }
    });
    

    /* opportunity type dropdown (1st) */
    $('#search_type').on('click', function(){
       $('.modal .form-item').hide();
        openModal();
        $('.modal .form-types').show();
        updateResults();
    });


    $('.explore-form #edit-opportunity-type input').on('change', function() {
       var text = $('input:checked', '.explore-form #edit-opportunity-type').parent().text();
       if(text){
           $('#search_type .chosen').html(text);
           $('#search_type').removeClass('empty');
           closeModal();
           $('.sb-close-modal').addClass('hide');
       }

       updateResults();
    });


    /*
     * Country type selection
     *
     */
    $('#search_country').on('click', function(){
        $('.modal .form-item').hide();
        openModal();
        $('.modal .form-countries, .modal .form-item-country').show();
        $('.search-field input').attr('disabled', 'disabled');
        $('label[for="edit-country-choice-"]').append('<span class="sb-close-modal">Done</span>');
        
        updateResults();
    });




    /*
     * Specific country selection
     *
     */
    $('.explore-form #edit-country-choice input').on('change', function() {
        var text = $('input:checked', '.explore-form #edit-country-choice').parent().text();
        var val = $('input:checked', '.explore-form #edit-country-choice').val();
        var $countryList = $('.modal .form-item-country');
            $countryList.show();

        if(text){
           
                
            if(val != ''){
                $('#search_country .chosen').html(text);
                $('#search_country').removeClass('empty');

                $('.sb-close-modal').addClass('hide');
                closeModal();
            } else {
                $('.search-field input').removeAttr('disabled');
                $('.sb-close-modal').removeClass('hide');
            }
            
        }
        updateResults();
    });

    /*
     *  close modal for selecting multiple countries.
     *
     */
    $(document).on('click', '.sb-close-modal', function(){
        closeModal();
    });
    
    
    function openModal(){
        $modal.show();
        if ($(window).width() < 640) {
            $modal.show().css('top', $(document).scrollTop() + 30+'px');
        }
    }
    function closeModal(){
        $modal.hide();
        $('.sb-close-modal').remove();
    }
    
    if($('.modal')){
         $(document).keyup(function(e) {
            if (e.keyCode == 27) { // escape key maps to keycode `27`
                closeModal();
            }
        });
        
        $(window).click(function(event) {
            closeModal();
        });

        $('.modal, .dropdown, .sb-close-modal').click(function(event){
            var target = $( event.target );
            if (!target.is(".sb-close-modal")) {
                 event.stopPropagation(); 
            } 
        });       
    }
    

});
