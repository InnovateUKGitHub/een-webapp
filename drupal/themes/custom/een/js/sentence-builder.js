jQuery(function () {
    var $ = jQuery,
        $modal = $('.modal'),
        $keywords = $('#keywords'),
        $search = $('#search'),
        $searchCountryInput = $('#search_country');

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
      var search = $.trim( $('#search').val() ) ;
      var country;
      var opportunity_type = [$('input:checked', '.explore-form #edit-opportunity-type').val()];
      var checkboxes = [$('input:checked', '.explore-form #edit-country-choice').val()];

      if (checkboxes && checkboxes[0] === '') {
        country = $(".chosen-select-multiple").val();
      } else {
        country = checkboxes;
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

          var params = {};

          if (search) {
              params.search = search;
          }
          if (opportunity_type && opportunity_type[0] !== undefined) {
              params.opportunity_type = opportunity_type;
        } else {
              var list = [];
              $('.explore-form input[name="opportunity_type"]').each(function (index) {
                  list[index] = $(this).val();
              });
              params.opportunity_type = list;
          }
          if (country && country[0] !== undefined) {
              params.country = country;
          } else {
              params.country = ['anywhere'];
          }
          $('.js-sb-view-results').attr('href', '/opportunities#!/page/1?' + $.param(params));
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
    $keywords.on('keyup', function(e) {
        
        var code = (e.keyCode ? e.keyCode : e.which);
        if (code != 9 && code != 16){
            $search.val($keywords.text());
            if($keywords.text().length > 20){
                $(this).removeClass('block');
            } else {
                $(this).addClass('block');
            }
            $(this).removeAttr("aria-label");
            updateResults();
        }
        
    });

    $keywords.click(function() {
        if ($search.val() === '') {
            $(this).addClass('block');
            $keywords.text('');
            $search.val('');
        }
    });
    
    $keywords.on('focus', function(e){
        $(window).keyup(function (e) {
            var code = (e.keyCode ? e.keyCode : e.which);
            if (code == 9 && $keywords.text() === 'Enter sector or product') {
                $keywords.text('');
                $search.val('');
            }
        });
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
            $('#search_country').attr("aria-label", countries.length + ' country selected');
        } else {
            $('#search_country .chosen').text(countries.length + ' countries selected');
            $('#search_country').attr("aria-label", countries.length + ' countries selected');
        }
    });
    
    
    $('.modal .block-label').hover(
            function(){ $(this).addClass('hover') },
            function(){ $(this).removeClass('hover') }
    );
    
    

    /* opportunity type dropdown (1st) */
    $('#search_type').on('click', function(){
        openTypeList();
    });
    
    $('#search_type').on('keydown', function(e){
        if (e.which == 32) {   // on spacebar    
            e.stopPropagation(); 
            openTypeList();
            $('.form-item-opportunity-type-hidden').show();
            $('.explore-form #edit-opportunity-type-hidden').removeClass('sr-only');
            $('#edit-opportunity-type--wrapper').hide();
        }
    });
    
    
         
    $(document).on('change', '.explore-form #edit-opportunity-type input', function() {
        opportunityListUpdated();
    });

    $(document).on('change', '.explore-form #edit-opportunity-type-hidden', function() {
        var dropdownVal = $(this).val();
        $('input[name="opportunity_type"]').parent('label').removeClass("selected");
        $('input[name="opportunity_type"][value="' + dropdownVal + '"]').prop('checked', true).parent('label').addClass("selected");
        opportunityListUpdated();
    });
    
    function openTypeList() {
        $('.modal .form-item').hide();
        openModal();
        $('.modal .form-types').show();
        setTimeout(function() { $('#edit-opportunity-type-hidden').focus() }, 500);
    }
    
    function opportunityListUpdated(){
       var text = $('input:checked', '.explore-form #edit-opportunity-type').parent().text();
       if(text){
           $('#search_type .chosen').html(text);
           $('#search_type').removeClass('empty');
           closeModal();
           $('.sb-close-modal').addClass('hide');
           $('#search_type').focus();
           $('#search_type').attr("aria-label", text);
       }
       updateResults();
    }
    

   


    /*
     * Country type selection
     *
     */
    
    
    /* opportunity type dropdown (1st) */
    $searchCountryInput.on('click', function(){
        showCountryTypes();
    });
    
    $searchCountryInput.on('keydown', function(e){
        if (e.which == 32) {   // on spacebar   
            e.stopPropagation(); 
            showCountryTypes();
            
            $('.form-item-country-choice-hidden').show();
            $('.explore-form #edit-country-choice-hidden').removeClass('sr-only');
            $('#edit-country-choice--wrapper, .form-item-country').hide();
       
        }
    });
    

    
    /*
     * Specific country selection
     *
     */
    $('.explore-form #edit-country-choice input').on('change', function() {
        updateCountryTypes();
    });
    
    $('.explore-form #edit-country-choice-hidden').on('change', function() {
        var dropdownVal = $(this).val();
        $('.input[name="country_choice"]').parent('label').removeClass("selected");
        $('input[name="country_choice"][value="' + dropdownVal + '"]').prop('checked', true).parent('label').addClass("selected");
        updateCountryTypes();
    });
    
    
    function showCountryTypes(){
        $('.modal .form-item').hide();
        openModal();
        $('.modal .form-countries, .modal .form-item-country').show();
        
        $('.search-field input').attr('disabled', 'disabled');
        $('label[for="edit-country-choice-"]').append('<span class="sb-close-modal">Done</span>');
        setTimeout(function() { $('#edit-country-choice-hidden').focus().removeClass('.sr-only') }, 500);
    }

    function updateCountryTypes(){
        var text = $('input:checked', '.explore-form #edit-country-choice').parent().text();
        var val = $('input:checked', '.explore-form #edit-country-choice').val();
        var $countryList = $('.modal .form-item-country');
            $countryList.show();

        if(text){                
            if(val != ''){
                $searchCountryInput.children('.chosen').html(text);
                $searchCountryInput.removeClass('empty').focus().attr("aria-label", text);;
                $('.sb-close-modal').addClass('hide');
                closeModal();
                
            } else {
                $('.search-field input').removeAttr('disabled');
                $('.sb-close-modal').removeClass('hide');
            }
            
        }
        updateResults();
    }
    
    
    /*
     * 
     * MODAL
     */
        
    
    function openModal(){
        $modal.show();
        $modal.attr('aria-live', 'assertive');
        if ($(window).width() < 640) {
            $modal.show().css('top', $(document).scrollTop() + 30+'px');
        }
    }
    function closeModal(){
        $modal.hide();
        $('.sb-close-modal').remove();
        $('.explore-form #edit-opportunity-type-hidden').addClass('sr-only');
        $('.explore-form #edit-country-type-hidden').addClass('sr-only');
        $('.js-form-item-country-choice-hidden').hide();
        
        
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
    
        /*
     *  close modal for selecting multiple countries.
     *
     */
    $(document).on('click', '.sb-close-modal', function(){
        closeModal();
    });
});
