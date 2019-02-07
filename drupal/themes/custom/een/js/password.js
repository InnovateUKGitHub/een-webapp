jQuery(function () {
    var $ = jQuery;

    /*
     Show/ hide password field
     */
    $(document).on('click', '.showHideP', function() {

        if($(this).text() == 'Show password') {
            $(this).text('Hide password');
        } else if($(this).text() == 'Hide password'){
            $(this).text('Show password');
        }

        if ($("input[name=password]").attr("type") == "password") {
            $("input[name=password]").attr("type", "text");

        } else {
            $("input[name=password]").attr("type", "password");
        }
    });


    //yes create an account
    $(document).on('change', '#edit-create-account-yes', function(e) {
        var capital = checkCapital($('#edit-password').val()),
            tenChars = checkTenChars($('#edit-password').val()),
            symbol = checkSymbol($('#edit-password').val());
        $('.password-section, .js-account-options').fadeIn().removeClass('hide');

        if ( capital && tenChars && symbol ) {
            $('#edit-submit').removeAttr('disabled');
        } else {
            $('#edit-submit').attr('disabled', 'disabled');
        }
    });

    //no create an account
    $(document).on('change', '#edit-create-account-no', function(e) {
        $('.password-section').fadeOut().addClass('hide');
        $('#edit-submit').removeAttr('disabled');
    });




    function checkTenChars(string){
        if (string.length >= 10) {
            $('#password-ten-chars').addClass('pass');
            return true;
        } else {
            $('#password-ten-chars').removeClass('pass');
            return false;
        }
    }

    function checkSymbol(string){
        var gotSymbol = false;
        for (var i = 0, len = string.length; i < len; i++) {
            if (string[i].match(/[^\w\s]/)) {
                // matches
                $('#password-symbol').addClass('pass');
                gotSymbol = true;
                return true;
            } else {
                // doesn't match
                $('#password-symbol').removeClass('pass');
                gotSymbol = false;
            }
        }
        if (gotSymbol == true) {
            return true;
        } else {
            return false;
        }
    }

    function checkCapital(string){
        var gotCapital = false;
        for (var i = 0, len = string.length; i < len; i++) {
            if (string[i].match(/^[A-Z]*$/)) {
                // matches
                $('#password-capital').addClass('pass');
                gotCapital = true;
                return true;
            } else {
                // doesn't match
                $('#password-capital').removeClass('pass');
                gotCapital = false;
            }
        }
        if (gotCapital == true) {
            return true;
        } else {
            return false;
        }
    }

    $(document).on('keyup', '.js-password-section #edit-password', function(e) {
        var capital = checkCapital($(e.currentTarget).val()),
            tenChars = checkTenChars($(e.currentTarget).val()),
            symbol = checkSymbol($(e.currentTarget).val());
        if (capital) {
            $('#password-capital').addClass('pass');
        }
        if (tenChars) {
            $('#password-ten-chars').addClass('pass');
        }
        if (symbol) {
            $('#password-symbol').addClass('pass');
        }
        if ( capital && tenChars && symbol ) {
            $('#edit-submit').removeAttr('disabled');
        } else {
            $('#edit-submit').attr('disabled', 'disabled');
        }
    });


});