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
        $('.sb-results').html(data.total + ' results');
      });
    };

    var updateResults = debounce(function() {
      $('.sb-results').addClass('transp');
      ajaxSearch().then(function() {
        $('.sb-results').removeClass('transp');
      });
      
      getResultUrl();
      
    }, 600);

    $('#keywords').on('keyup', function() {
        $('#search').val($('#keywords').text());
        updateResults();
    });

    $('#keywords').click(function() {
      if ($('#search').val() === '') {
        $('#keywords').text('');
        $('#search').val('');
      }
      updateResults();
    });

    $(".chosen-select-multiple").chosen({
        disable_search:false
    });

    $(".chosen-select-multiple").on('change', function(evt, params) {
      //var x = $(".chosen-select-multiple").val();
      //$('input:checked', '.explore-form #edit-country-choice').val(x);
      updateResults();
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
           $('#search_type').html(text);
           closeModal();
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
        $('.modal .form-countries').show();

         var val = $('input:checked', '.explore-form #edit-country-choice').val();
         if(val == ''){
             $('.modal .form-item-country').show();
         }
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

        if(text){
            if(val == ''){
                $countryList.show();
                $countryList.prepend('<span class="sb-close-modal">Close</span>');
            } else {
                $('#search_country').html(text);
                closeModal();
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

        /* get array of selected values */
        var countries = $("#edit-country").val();

        if(countries.length == 1){
            $('#search_country').text(countries.length + ' country selected');
        } else {
            $('#search_country').text(countries.length + ' countries selected');
        }
        

        updateResults();
    });


    function openModal(){
        $modal.show();
        if ($(window).width() < 640) {
            $modal.show().css('top', $(document).scrollTop() + 30+'px');
        }
    }
    function closeModal(){
        $modal.hide();
    }
    
    
    
    function getResultUrl() {
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
        
        var str = $.param({ search: search, opportunity_type: opportunity_type, country: country });
        var url = '/opportunities#!/page/1?'+str;
        
        $('.js-sb-view-results').attr('href', url);
    }
    

});
