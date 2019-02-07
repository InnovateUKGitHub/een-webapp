jQuery(function () {
    var $ = jQuery,
        url = '/sign-up/companies',



        $searchResultsContainerWrapper = $('.js-address-search'),
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

        $('.js-address-notifier').remove();

        if(searchTerm.length > 0){

            $.get(url, {q: searchTerm}, function( data ) {

                if(data && data.total_results > 0){

                    $searchResultsContainer.empty();

                    $.each(data.items, function() {

                        $searchResultsContainer.append($('<li>', {'class': 'company-row ' + this.type})
                                .append($('<a>', {'class': 'company-result', href: '#',
                                    text: this.title.toLowerCase(),
                                    html: '<span>'+this.title.toLowerCase()+'</span><br /> <small>'+this.address.address_line_1+', '+this.address.postal_code + '</small>',
                                    'data-title': this.title.toLowerCase(),
                                    'data-number': this.company_number,
                                    'data-postcode': this.address.postal_code,
                                    'data-address-line-1': this.address.address_line_1,
                                    'data-locality': this.address.locality,
                                    'data-premises': this.address.premises,
                                    'data-id': this.id
                                }))
                                .append(' '));
                    });
                    $searchResultsContainerWrapper.show();
                    $("li.sfduplicate").last().addClass("lsfduplicate");
                    $("li.sfduplicate").first().addClass("fsfduplicate");


                } else {
                    $('.js-address-search').hide();
                    $('.js-address-search').before('<p class="js-address-notifier address-notifier clearfix">No company found with that name, please enter your company address below</p>')
                }

            });
        } else {
            $searchResultsContainerWrapper.show();
            $searchResultsContainer.html('<li>Please enter a company name</li>');
        }
        e.preventDefault();
    });


    /*

     */
    $(document).on('click', '.company-result', function(e){
        e.preventDefault();

        $searchResultsContainerWrapper.hide();

        $('.form-companies-house-search #ch_search').val($(this).attr('data-title'));
        $('.form-companies-house-search #edit-postcode-registered').val($(this).attr('data-postcode'));
        $('.form-companies-house-search #edit-addressone-registered').val($(this).attr('data-premises'));
        $('.form-companies-house-search #edit-addresstwo-registered').val($(this).attr('data-address-line-1'));
        $('.form-companies-house-search #edit-city-registered').val($(this).attr('data-locality'));


        $('.form-companies-house-search #edit-postcode').val($(this).attr('data-postcode'));
        $('.form-companies-house-search #edit-addressone').val($(this).attr('data-premises'));
        $('.form-companies-house-search #edit-addresstwo').val($(this).attr('data-address-line-1'));
        $('.form-companies-house-search #edit-city').val($(this).attr('data-locality'));


        $('.form-companies-house-search #edit-sfaccount').val($(this).attr('data-id'));

        $('.cs-registered-address').html(
            "<span class='js-remove-company-reference remove-company-reference'><i class='fa-remove fa'></i> Remove</span>"
            +
            $(this).attr('data-title')
            +  '<br /> ' +
            $(this).attr('data-address-line-1')
            +  '<br /> ' +
            $(this).attr('data-locality')
            +  '<br /> ' +
            $(this).attr('data-postcode')
        );

        setAddressHeader('Request to update business address');

    });



    /*

     */
    $(document).on('click', '.js-address-dismiss, .js-remove-company-reference', function(e){
        e.preventDefault();
        $searchResultsContainerWrapper.hide();

        $('#edit-postcode-registered, ' +
            '#edit-addressone-registered, ' +
            '#edit-addresstwo-registered, ' +
            '#edit-city-registered,' +
            '#edit-county_registered,' +
            '#edit-company-number').val('');

        $('.js-registered-address').html("");
        $('#edit-sfaccount').val("");
        setAddressHeader('Correspondence addresss');
    });


    function setAddressHeader(heading)
    {
      $('.js-address-heading').text(heading);
    }

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
       // $('.js-show-registered').removeClass('hide');
       // $('#registered_address').fadeIn();
    }
    function hideRegisterAddressFields(){
      //  $('#registered_address').fadeOut();
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




    /*
        On type in postcode field, PCAPredict is automatically triggered.
     */
    $(document).on('keyup', '#edit-postcode', function(e) {

        var url = "https://services.postcodeanywhere.co.uk/Capture/Interactive/Find/v1.00/json3.ws?";
        var searchText = $(this).val();

        var params = {
            Key: 'FN97-TJ51-WP36-DX35',
            Text: searchText,
            Origin: "GB",
            Countries: "GB",
            Limit: 10,
            Language: 'EN'
        };

        if(searchText){
            var query = jQuery.param( params );

            $.get(url + query, function( data ) {

                $('.js-pcapredict-results').html("<ul class='pca-search-results'></ul>").slideDown( "fast", function() {

                });

                $.each(data.Items, function( index, value ) {
                    $('.pca-search-results').append('<li class="pca-line" data-id="'+value.Id+'" data-type="'+value.Type+'">'+value.Text + ' ' + value.Description+'</li>');
                });
            });
        }
    });


    $(document).on('click', '.pca-line', function(e) {

        var type = $(this).attr('data-type');
        var id = $(this).attr('data-id');
        var searchText = $('#edit-postcode').val();

        if(type == 'Address'){
            var url = "https://services.postcodeanywhere.co.uk/Capture/Interactive/Retrieve/v1.00/json3.ws?";

            var params = {
                Key: 'FN97-TJ51-WP36-DX35',
                id: id
            };

            var query = jQuery.param( params );

            $.get(url + query, function( data ) {

                $('.js-show-hide-address').removeClass('hide');

                $('#edit-addressone').val(data.Items[0].BuildingNumber + ' ' + data.Items[0].BuildingName);
                $('#edit-addresstwo').val(data.Items[0].Street);
                $('#edit-city').val(data.Items[0].City);
                $('#edit-postcode').val(data.Items[0].PostalCode);

                $('.js-enter-address-manually').remove();
                $('.js-pcapredict-results').slideUp();
            });
        } else if(searchText){

            var url = "https://services.postcodeanywhere.co.uk/Capture/Interactive/Find/v1.00/json3.ws?";

            var params = {
                Key: 'FN97-TJ51-WP36-DX35',
                Text: searchText,
                Container: id
            };

            var query = jQuery.param( params );

            $.get(url + query, function( data ) {

                $('.js-pcapredict-results').html("<ul class='pca-search-results'></ul>");

                $.each(data.Items, function( index, value ) {
                    $('.pca-search-results').append('<li class="pca-line" data-id="'+value.Id+'" data-type="'+value.Type+'">'+value.Text + ' ' + value.Description+'</li>');
                });
            });
        }


    });

    $(document).on('click', '.js-enter-address-manually', function(e) {
        $('.js-show-hide-address').removeClass('hide');
        $(this).remove();
        e.preventDefault();
    });

    if($('#edit-postcode').val()){

        $('.js-show-hide-address').removeClass('hide');
        $('.js-enter-address-manually').remove();
    }





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

        $chooseAddressType.removeClass('hide');
        //$correspondenceAddressPostcode.addClass('hide');
        //$correspondenceAddress.addClass('hide');

        if($('#edit-postcode-registered').val()){
            showRegisterAddressFields();
        }

        if($editAlternativeAddress.val() != 1){
           // $correspondenceAddress.removeClass('hide');
            //$correspondenceAddressPostcode.removeClass('hide');
        }
        $('#edit-postcode').attr('required', false);
    }

    function companyNo(){
        hideCompanyNumberField();
        hideRegisterAddressFields();
        $('#ch-search-trigger').addClass('hide');
        $('#ch_search').addClass('no-search');
        $chooseAddressType.addClass('hide');
       // $correspondenceAddressPostcode.removeClass('hide');


        if($('#edit-postcode').val()){
          //  $correspondenceAddress.removeClass('hide');
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
   // $correspondenceAddress.addClass('hide');
    $chooseAddressType.addClass('hide');
    $('.js-steps-pwd').addClass('hide');

    if($('#edit-postcode-registered').val() && $('#edit-company-registered-yes').is(':checked')){
        $('.js-show-registered, .js-alternative-address').removeClass('hide');
       // $correspondenceAddressPostcode.addClass('hide');
    }


    if($('#edit-postcode').val() && $editAlternativeAddress.val() != 1){
        //$correspondenceAddress.removeClass('hide');
       // $correspondenceAddressPostcode.removeClass('hide');
    }

    if($('#edit-sfaccount').val()){
        setAddressHeader('Request to update business address');
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
