jQuery(function () {
    var $ = jQuery,
        url = '/opportunities/eoi/companies',
        $searchResultsContainer = $('.companies-house-list'),
        $searchTrigger = $("#ch-search-trigger"),
        $searchField = $('#ch_search');

    $searchTrigger.click(function(e) {

        var searchTerm = $searchField.val();

        if(searchTerm.length > 0){

            $.get(url, {q: searchTerm}, function( data ) {
                var results = $.parseJSON(data.results);

                if(results && results.total_results > 0){

                    $searchResultsContainer.empty();

                    $.each(results.items, function() {
                        $searchResultsContainer.append($('<li>')
                                .append($('<a>', {'class': 'company-result', href: '#', text: this.title.toLowerCase(), 'data-title': this.title.toLowerCase(), 'data-number': this.company_number, 'data-postcode': this.address.postal_code}))
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

    $(document).on('click', '.company-result', function(e){
        e.preventDefault();
        $('.form-companies-house-search #ch_search').val($(this).attr('data-title'));
        $('.form-companies-house-search #edit-company-number').val($(this).attr('data-number'));
        $searchResultsContainer.hide();
    });
});
