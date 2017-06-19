jQuery(function () {
    var $ = jQuery,
        url = '/sign-up/companies',
        $searchResultsContainer = $('.companies-house-list'),
        $searchTrigger = $("#ch-search-trigger"),
        $searchField = $('#ch_search')
        $companyNumberField = $('#edit-company-number');


    if(!$companyNumberField.val()){
        $companyNumberField.parent().hide();
    }

    $searchTrigger.click(function(e) {

        var searchTerm = $searchField.val();

        if(searchTerm.length > 0){

            $.get(url, {q: searchTerm}, function( data ) {

                if(data && data.total_results > 0){

                    $searchResultsContainer.empty();

                    $.each(data.items, function() {

                        $searchResultsContainer.append($('<li>')
                                .append($('<a>', {'class': 'company-result', href: '#',
                                    text: this.title.toLowerCase(),
                                    'data-title': this.title.toLowerCase(),
                                    'data-number': this.company_number,
                                    'data-postcode': this.address.postal_code,
                                    'data-address-line-1': this.address.address_line_1,
                                    'data-locality': this.address.locality,
                                    'data-premises': this.address.premises
                                }))
                                .append(' '));
                    });
                    $searchResultsContainer.show();
                } else {
                    $searchResultsContainer.show();
                    $searchResultsContainer.html('<li>No results</li>');
                }

            });
        } else {
            $searchResultsContainer.show();
            $searchResultsContainer.html('<li>Please enter a company name</li>');
        }
        e.preventDefault();
    });

    /*var noCompanyNumberField = $('#edit-no-company-number');
    if(noCompanyNumberField.length){
        if($searchField.val() == ""){
            noCompanyNumberField.val(0);
        }

        if(noCompanyNumberField.val() == 1){
            $(noCompanyNumberField).parent('label').addClass('selected');
        }
    }*/



    $(document).on('click', '.company-result', function(e){
        e.preventDefault();

        $searchResultsContainer.hide();

        showRegisterAddressFields();
        $('.form-companies-house-search #ch_search').val($(this).attr('data-title'));
        $('.form-companies-house-search #edit-postcode-registered').val($(this).attr('data-postcode'));

        $('.form-companies-house-search #edit-addressone-registered').val($(this).attr('data-premises'));
        $('.form-companies-house-search #edit-addresstwo-registered').val($(this).attr('data-address-line-1'));
        $('.form-companies-house-search #edit-city-registered').val($(this).attr('data-locality'));

        if(!$("#edit-no-company-number").parent('label').hasClass('selected')){
            $companyNumberField.parent().show();
            $('.form-companies-house-search #edit-company-number').val($(this).attr('data-number'));
            $('.js-alternative-address').removeClass('hide');
        }
    });



    function showCompanyNumberField(){
        $('.js-form-item-company-number').removeClass('hide').attr('disabled', false);
        $('#ch-search-trigger').removeClass('hide');
    }
    function hideCompanyNumberField(){
        $('.js-form-item-company-number').addClass('hide').attr('disabled', true);
        $('#ch-search-trigger').addClass('hide');
    }
    function showRegisterAddressFields(){
        $('.js-show-registered').removeClass('hide');
        $('#registered_address').fadeIn();
    }
    function hideRegisterAddressFields(){
        $('#registered_address').fadeOut();
    }

    if($('#edit-company-registered-yes').length) {
        if($('#edit-company-registered-yes').is(':checked')) {
            showCompanyNumberField();
        }
    }


    if($('#edit-alternative-address').length) {
        if($('#edit-alternative-address').is(':checked')) {
            $('#normal_address').find('input').attr('required', false);
        } else {
            $('#normal_address').find('input').attr('required', true);
        }
    }

    $(document).on('change', '#edit-alternative-address', function(e) {
        if($('#edit-alternative-address').is(':checked')) {
            $('.js-show-cs-address, .js-corres').addClass('hide');

            $('#normal_address').find('input').attr('required', false);

        } else {
            $('.js-show-cs-address, .js-corres').removeClass('hide');
            $('#normal_address').find('input').attr('required', true);
        }
    });


    $(document).on('change', '.form-checkbox', function(e) {
        if($(this).is(':checked')) {
            $(this).parent().addClass('selected');
        } else {
            $(this).parent().removeClass('selected');
        }
    });

    /*
        On checkbox click
     */
    $(document).on('change', '#edit-terms', function(e) {
        if($(this).is(':checked')) {
            $(this).parent().addClass('selected');
        } else {
            $(this).parent().removeClass('selected');
        }
    });


    $(document).on('keyup', '#edit-postcode', function(e) {
        $('.js-show-cs-address').fadeIn();
    });



    /* checkboxes to behave as radios */
    $("#edit-company-registered .form-checkbox").click(function() {
        selectedBox = this.id;

        $("#edit-company-registered .form-checkbox").each(function() {

            if ( this.id == selectedBox )
            {
                this.checked = true;
                $(this).attr('checked', true);
                $(this).parent().addClass('selected');
            }
            else
            {
                this.checked = false;
                $(this).attr('checked', false);
                $(this).parent().removeClass('selected');
            };
        });
    });

    $("#edit-create-account .form-radio").click(function() {
        selectedBox = this.id;

        $("#edit-create-account .form-radio").each(function() {
            if ( this.id == selectedBox )
            {
                this.checked = true;
                $(this).attr('checked', true);
                $(this).parent().addClass('selected');
            }
            else
            {
                this.checked = false;
                $(this).attr('checked', false);
                $(this).parent().removeClass('selected');
            };
        });
    });

    $("#edit-company-registered .form-radio").click(function() {
        selectedBox = this.id;

        $("#edit-company-registered .form-radio").each(function() {
            if ( this.id == selectedBox )
            {
                this.checked = true;
                $(this).attr('checked', true);
                $(this).parent().addClass('selected');
            }
            else
            {
                this.checked = false;
                $(this).attr('checked', false);
                $(this).parent().removeClass('selected');
            };
        });
    });

    //yes create an account
    $(document).on('change', '#edit-create-account-yes', function(e) {
        var capital = checkCapital($('#edit-password').val()),
            tenChars = checkTenChars($('#edit-password').val()),
            symbol = checkSymbol($('#edit-password').val()); 
        $('.password-section').fadeIn().removeClass('hide');
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




    function validateStep1() {
        var isValid = true;
        $('.step1 input[required="required"]').each(function() {
            if ( $(this).val() === '' )
                isValid = false;
        });
        return isValid;
    }

    function validateStep2() {
        var isValid = true;
        $('.step2 input[required="required"]').each(function() {
            if ( $(this).val() === '' )
                isValid = false;
        });
        return isValid;
    }



    $(document).on("keyup", ".step1 input, .step2 input", function() {
        if(validateStep1()){
            $('.step2').removeClass('hide');
            $('.step1-advisor').addClass('hide');
        }
        if(validateStep2()){
            $('.step3').removeClass('hide');
            $('.step2-advisor').addClass('hide');
        }
    });









    /**********************************************************************/



    if($('#edit-company-registered-no').is(':checked')){
        $('#edit-company-registered-no').parent().addClass('selected');
        companyNo();
    }

    if($('#edit-company-registered-yes').is(':checked')){
        $('#edit-company-registered-yes').parent().addClass('selected');
        companyYes();
    }


    //yes company is registered
    $(document).on('change', '#edit-company-registered-yes', function(e) {
        companyYes();
    });

    //no company is registered
    $(document).on('change', '#edit-company-registered-no', function(e) {
        companyNo();
    });


    function companyYes(){
        showCompanyNumberField();
        $('#ch-search-trigger').removeClass('hide');
        $('#ch_search').removeClass('no-search');

        if($('#edit-postcode-registered').val()){
            //$('.js-corres').addClass('hide');
        }

    }

    function companyNo(){
        hideCompanyNumberField();
        hideRegisterAddressFields();
        $('#ch-search-trigger').addClass('hide');
        $('#ch_search').addClass('no-search');
       // $('.js-corres').addClass('hide');

        $('#edit-postcode-registered, ' +
            '#edit-addressone-registered, ' +
            '#edit-addresstwo-registered, ' +
            '#edit-city-registered,' +
            '#edit-county_registered,' +
            '#edit-company-number').val('').attr('disabled');

    }



    /**********************************************************************/




    // on page load
    $('.js-show-registered, .js-alternative-address, .js-show-cs-address, .js-password-section').addClass('hide');

    if($('#edit-postcode-registered').val() && $('#edit-company-registered-yes').is(':checked')){
        $('.js-show-registered, .js-alternative-address').removeClass('hide');
        $('.js-corres').addClass('hide');
    }



    if($('#edit-postcode').val()){
        $('.js-show-cs-address, .js-corres').removeClass('hide');
    }


    /**********************************************************************/


    $('input:checked').parent().addClass('selected');

    $('#ch_search').not('.no-search').on('focus', function() {
        $('.company-name-wrapper').addClass('tooltip-active');
    });

    $('#ch_search').not('.no-search').on('blur', function() {
        $('.company-name-wrapper').removeClass('tooltip-active');
    });




});
