jQuery(function () {
    var $ = jQuery,
        url = '/sign-up/companies',
        $searchResultsContainer = $('.companies-house-list'),
        $searchTrigger = $("#ch-search-trigger"),
        $searchField = $('#ch_search'),
        $companyNumberField = $('#edit-company-number'),
        $companyNumberHandle = $('.js-form-item-company-number'),
        $editAlternativeAddress = $('#edit-alternative-address'),
        $normalAddress = $('#normal_address'),
        $correspondenceAddress = $('.js-show-cs-address'),
        $correspondenceAddressPostcode = $('.js-corres'),
        $chooseAddressType = $('.js-alternative-address');



    if(!$companyNumberField.val()){
        $companyNumberField.parent().hide();
    }


    /***********************Company lookup***********************************************/

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


    /*

     */
    $(document).on('click', '.company-result', function(e){
        e.preventDefault();

        $searchResultsContainer.hide();

        showRegisterAddressFields();
        $('.form-companies-house-search #ch_search').val($(this).attr('data-title'));
        $('.form-companies-house-search #edit-postcode-registered').val($(this).attr('data-postcode'));

        $('.form-companies-house-search #edit-addressone-registered').val($(this).attr('data-premises'));
        $('.form-companies-house-search #edit-addresstwo-registered').val($(this).attr('data-address-line-1'));
        $('.form-companies-house-search #edit-city-registered').val($(this).attr('data-locality'));

    });


    /**********************************************************************/



    function showCompanyNumberField(){
        $companyNumberHandle.removeClass('hide').attr('disabled', false);
        $('#ch-search-trigger').removeClass('hide');
    }
    function hideCompanyNumberField(){
        $companyNumberHandle.addClass('hide').attr('disabled', true);
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


    if($editAlternativeAddress.length) {
        if($editAlternativeAddress.is(':checked')) {
            $normalAddress.find('input').attr('required', false);
        } else {
            $normalAddress.find('input').attr('required', true);
        }
    }

    $(document).on('change', $editAlternativeAddress, function(e) {

        if($editAlternativeAddress.is(':checked')) {

            if(event.target.id == 'edit-alternative-address'){

                $correspondenceAddress.addClass('hide');
                $correspondenceAddressPostcode.addClass('hide');
            }
            $normalAddress.find('input').attr('required', false);

        } else {
            $correspondenceAddress.removeClass('hide');
            $correspondenceAddressPostcode.removeClass('hide');
            $('.js-form-item-postcode').removeClass('hide');

            $normalAddress.find('input').attr('required', true);
        }
    });



    /*
        On type in postcode field, PCAPredict is automatically triggered.
     */
    $(document).on('keyup', '#edit-postcode', function(e) {
        $correspondenceAddress.fadeIn();
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

        $chooseAddressType.removeClass('hide')
        $correspondenceAddressPostcode.addClass('hide');
        $correspondenceAddress.addClass('hide');

        if($('#edit-postcode-registered').val()){
            showRegisterAddressFields();
        }

        if($editAlternativeAddress.val() != 1){
            $correspondenceAddress.removeClass('hide');
            $correspondenceAddressPostcode.removeClass('hide');
        }
        $('#edit-postcode').attr('required', false);
    }

    function companyNo(){
        hideCompanyNumberField();
        hideRegisterAddressFields();
        $('#ch-search-trigger').addClass('hide');
        $('#ch_search').addClass('no-search');
        $chooseAddressType.addClass('hide');
        $correspondenceAddressPostcode.removeClass('hide');


        if($('#edit-postcode').val()){
            $correspondenceAddress.removeClass('hide');
        }

        $('#edit-postcode-registered, ' +
            '#edit-addressone-registered, ' +
            '#edit-addresstwo-registered, ' +
            '#edit-city-registered,' +
            '#edit-county_registered,' +
            '#edit-company-number').val('').attr('disabled');

        $('#edit-postcode').attr('required', true);
    }



    /**********************************************************************/




    // on page load
    $correspondenceAddress.addClass('hide');
    $chooseAddressType.addClass('hide');
    $('.js-show-registered, .js-steps-pwd').addClass('hide');

    if($('#edit-postcode-registered').val() && $('#edit-company-registered-yes').is(':checked')){
        $('.js-show-registered, .js-alternative-address').removeClass('hide');
        $correspondenceAddressPostcode.addClass('hide');
    }


    if($('#edit-postcode').val() && $editAlternativeAddress.val() != 1){
        $correspondenceAddress.removeClass('hide');
        $correspondenceAddressPostcode.removeClass('hide');
    }


    /**********************************************************************/




    $('#ch_search').not('.no-search').on('focus', function() {
        $('.company-name-wrapper').addClass('tooltip-active');
    });

    $('#ch_search').not('.no-search').on('blur', function() {
        $('.company-name-wrapper').removeClass('tooltip-active');
    });


    /**********************************************************************/

    $('input:checked').parent().addClass('selected');
    $('#edit-alternative-address[value=1]').parent().addClass('selected');


    /* checkboxes to behave as radios */
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




});
