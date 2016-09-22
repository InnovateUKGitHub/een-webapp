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
});
