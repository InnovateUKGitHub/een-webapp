jQuery(function () {
    var $ = jQuery,
        countryWrapper = $('#edit-country--wrapper'),
        selectedCheckboxesCount = countryWrapper.find('input:checked').length,
        checkboxes = countryWrapper.find('input');

    countryWrapper.find('legend').append(
        '<br/><span class="selected-countries">'
        + selectedCheckboxesCount
        + ' Selected</span> <a id="clear-countries">Clear</a>'
    );

    checkboxes.click(function() {
        selectedCheckboxesCount = countryWrapper.find('input:checked').length;
        $('.selected-countries').html(selectedCheckboxesCount + ' Selected');
    });

    $('#clear-countries').click(function() {
        countryWrapper.find('input:checked').click();
    });
    
    
    if($('.accordion-container .form-checkboxes').length){
        var panel = $('.accordion-container .form-checkboxes');
            panel.before('<a role="button" tabindex="0" class="accordion-toggle" href="#" aria-label="Toggle checkboxes"></a>');
            
        var toggle = $('.accordion-toggle');
            toggle.addClass('visible');
        
        $(document).on('click', '.accordion-toggle', function(e){
            e.preventDefault();
            
            var accordionContent = $(this).next(panel);
            
            //Expand or collapse this panel
            $(accordionContent).slideToggle('slow', function(){
                 $(toggle).toggleClass('visible', $(this).is(':visible'));
            });
        });
    }
});
