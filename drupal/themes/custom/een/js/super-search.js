jQuery(function () {
    var $ = jQuery,
        $keywords = $('#auto-1 #edit-search'),
        $form = $('#auto-1 #opportunity-super-search-form');




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
      var search = $.trim($keywords.val() ) ;

      return $.ajax({
        url: 'opportunities/_count',
        data: {
          search: search
        }
      }).then(function (data) {
        $('#auto-1 .sb-results').html('<span>'+data.total + '</span> opportunities found');

          var params = {};
              params.search = search;

          $form.attr('action', '/opportunities#!/page/1?' + $.param(params));
      });
    };

    var updateResults = debounce(function() {
      $('#auto-1 .sb-results').addClass('transp');
      ajaxSearch().then(function() {
          $('#auto-1 .sb-results').removeClass('transp');

          var search = $.trim($keywords.val() ) ;
          var params = {};
          params.search = search;
          $form.attr('action', '/opportunities#!/page/1?' + $.param(params));
      });
    }, 600);


    /*
     * Keyword search 
     */
    $keywords.on('keyup', function(e) {
        
        var code = (e.keyCode ? e.keyCode : e.which);
        if (code != 9 && code != 16){
            updateResults();
        }
        
    });


    ajaxSearch();




    $('#auto-1 #edit-search').autocomplete({
        source: "/search/autosuggest",
        minLength: 2,
        classes: {
            "ui-autocomplete": "highlight"
        },
        appendTo: ".js-form-item-search",
        select: function( event, ui ) {
            console.log( "Selected: " + ui.item.value + " aka " + ui.item.id );

            var params = {};
            params.search = ui.item.value;
            $form.attr('action', '/opportunities#!/page/1?' + $.param(params));
            $form.submit();
        },
        open: function (e, ui) {
            var acData = $(this).data('ui-autocomplete');
            acData
                .menu
                .element
                .find('li')
                .each(function () {
                    var me = $(this);
                    var keywords = acData.term.split(' ').join('|');
                    me.html(me.text().replace(new RegExp("(" + keywords + ")", "gi"), '<b>$1</b>'));
                });
        }
    });


    if($('.search-keywords-within-results').not('.widget .search-keywords-within-results, .find-partners .search-keywords-within-results').length){

        var searchbox = $('.search-keywords-within-results').not('.widget .search-keywords-within-results, .find-partners .search-keywords-within-results'),
            searchboxPosition = $(searchbox).position(),
            searchListContainer = $('.js-opportunities-list');


        $(window).scroll(function (event) {
            var scroll = $(window).scrollTop();
            if(searchboxPosition.top < scroll){
                $(searchbox).width($('#content').width());
                $(searchbox).addClass('fixed');

                var marginNeeded = $(searchbox).outerHeight() + 20;
                $(searchListContainer).css('margin-top', marginNeeded+"px");

            } else {
                $(searchbox).removeClass('fixed');
                $(searchListContainer).css('margin-top', "0px");
            }
        });

        $(window).resize(function (event) {
            $(searchbox).width($('#content').width());
        });

        $("#edit-search").on('focus', function(){
            searchboxPosition = $('.search-keywords-within-results').offset();
            $('body, html').animate({
                scrollTop: searchboxPosition.top
            }, 250);
        });
    }


    /*$('#opportunity-super-search-form').keydown(function(event){
        if(event.keyCode == 13) {
            event.preventDefault();
            return false;
        }
    });*/

});
