jQuery(function () {
    var $ = jQuery;
    var windowPath = window.location.pathname;
    if(localStorage.getItem('uk')) {

        setTimeout(function () {

            if (windowPath !== '/login'
                && windowPath !== '/logout'
                && windowPath !== '/partnering-opportunities'
                && windowPath.indexOf('/reset-password') !== 0
                && windowPath.indexOf('/my-account') !== 0
                && windowPath.indexOf('/opportunities') !== 0
                && window.location.hash !== '#signin-popup'
            ) {

                var fileref = document.createElement('script')
                fileref.setAttribute("type", "text/javascript")
                fileref.setAttribute("src", 'https://content.govdelivery.com/overlay/js/4585.js')

                if (typeof fileref != "undefined")
                    document.getElementsByTagName("head")[0].appendChild(fileref)
            }

        }, 30000);
    }



    $(document).on('click', '.js-uk-confirmed', function() {
        $.magnificPopup.close();
        localStorage.setItem('uk', true);
    });

    if(!localStorage.getItem('uk')){
        $.magnificPopup.open({
            items: {
                src: '<div class="white-popup ip-overlay"><div class="form-content">'+
                '<h2 class="heading-large">Welcome to the UK website for Enterprise Europe Network.<h2>'+
                '<button class="button button--primary js-uk-confirmed"> Yes, I\'m in the UK </button> <a class="js-outside-uk" href="https://een.ec.europa.eu/about/branches">I’m not in the UK, take me to EEN\'s international websites</a> ' +
                '</div><div class="ip-overlay-footer"><img src="/themes/custom/een/logo.svg" alt="Enterprise Europe Network Logo"></div></div>',
                type: 'inline'
            },
            showCloseBtn: false,
            closeOnContentClick: false,
            modal: true
        });
    }


    $(document).on('click', '.js-uk-confirmed', function() {
        $.magnificPopup.close();
        localStorage.setItem('uk', true);
    });

    $(document).on('click', '.js-no-signup', function() {
        $.magnificPopup.close();
        localStorage.setItem('no-signup', true);
    });

    $(document).on('click', '.js-no-signup-right-now', function() {
        $.magnificPopup.close();

        var targetDate = new Date();
        targetDate.setDate(targetDate.getDate() + 10);

        localStorage.setItem('no-signup', targetDate.getTime());
    });




  if(window.location.hash == '#signup-pop' && localStorage.getItem('uk')) {

      var date = new Date();
      if (localStorage.getItem('no-signup') && localStorage.getItem('no-signup') < date.getTime() || !localStorage.getItem('no-signup')) {
          $.magnificPopup.open({
              items: {
                  src: '<div class="white-popup ip-overlay"><div class="form-content">' +
                  '<p>Sign up for updates to your inbox</p>' +
                  '<form method="POST" action="/manage-your-preferences">' +
                  '<input type="hidden" name="userType" value="new"/>' +
                  '<input type="email" name="email" class="form-item form-control" placeholder="Email address"/>' +
                  '<input type="submit" class="button button--primary" value="Subscribe"/>' +
                  '</form>' +
                  '</div>' +
                  '<div class="action-links">' +
                  '<a class="js-no-signup">No thanks</a>' +
                  '<a class="js-no-signup-right-now">Remind me later</a>' +
                  '</div>' +
                  '<div class="ip-overlay-footer"><img src="/themes/custom/een/logo.svg" alt="Enterprise Europe Network Logo"></div> ' +
                  '</div>',
                  type: 'inline'
              },
              showCloseBtn: true,
              closeOnContentClick: false,
              mainClass: 'mfp-img-mobile sign-up-mfp',
              modal: true
          });
      }
  }




});
