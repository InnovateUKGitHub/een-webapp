jQuery(function () {
    var $ = jQuery;
    
    $(".form-opportunities #edit-submit").click(function(e) {
        
        var searchTerm = $('#ch_search').val();
        
        $.get( "/opportunities-tempajax", {q: searchTerm}, function( data ) {
            var results = $.parseJSON(data.results);
            
            $container = $('.companies-house-list');
            $container.empty();
            $.each(results.items, function() {
                $container.append($('<li>')
                        .append($('<a>', {class: 'company-result', href: '#', text: this.title, 'data-number': this.company_number, 'data-postcode': this.address.postal_code}))
                        .append(' ')
                        .append($('<a>', {href: 'https://beta.companieshouse.gov.uk'+this.links.self, text: 'View on companies house website' })));
            });
        });
        e.preventDefault();
    });
    
    
    $(document).on('click', '.company-result', function(e){
        e.preventDefault();
        $('#edit-company-number').val($(this).attr('data-number'));
        $('#edit-company-postcode').val($(this).attr('data-postcode'));
        $('.companies-house-list').remove();
    });
  
    
    
});
