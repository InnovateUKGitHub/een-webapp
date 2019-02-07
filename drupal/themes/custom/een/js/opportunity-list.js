jQuery(function () {
    var windowPath = window.location.pathname;
    if (windowPath === '/partnering-opportunities' || windowPath.indexOf('/aggregation') !== -1) {
        var $ = jQuery,
            countryWrapper = $('#edit-country--wrapper'),
            selectedCheckboxesCount = countryWrapper.find('input:checked').length,
            checkboxes = countryWrapper.find('input');

        $('#clear-countries').click(function () {
            countryWrapper.find('input:checked').click();
        });

        if ($('.parent-country-regions #edit-country').length) {
            var anywhereField = $("label[for='edit-country-anywhere']").detach();
            var europeField = $("label[for='edit-country-europe']").detach();
            $(".parent-country-regions #edit-country").wrapInner("<div class='regions hide'></div>");

            $(".parent-country-regions #edit-country .regions").before(anywhereField).before(europeField);
            $('.regions').before('<div class="region-toggle"></div>' +
                '<div class="region-options">' +
                '<a role="button" tabindex="0" class="accordion-toggle region-sh" href="#" aria-label="Toggle checkboxes">Show countries selected</a>' +
                ' <span class="region-pipe">|</span>' +
                '<a role="button" tabindex="0" class="region-sh js-clear-regions clear-regions" href="#" aria-label="Clear Checkboxes"> clear</a>' +
                '</div>');
        }


        if ($('.accordion-container .form-checkboxes').length) {
            var panel = $('.accordion-container .form-checkboxes');
            panel.before('<a role="button" tabindex="0" class="accordion-toggle" href="#" aria-label="Toggle checkboxes"></a>');

            var toggle = $('.accordion-toggle');
            toggle.addClass('visible');


            $(document).on('click', '.accordion-toggle', function (e) {
                e.preventDefault();

                var accordionContent = $(this).parent().next(panel);

                //Expand or collapse this panel
                $(accordionContent).slideToggle('slow', function () {
                    $(toggle).toggleClass('visible', $(this).is(':visible'));
                });
            });
        }
    }
});
