jQuery(function () {
    if (window.location.pathname === '/partnering-opportunities') {
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
            $('.regions').before('<div class="region-toggle"><span class="selected-countries">'+selectedCheckboxesCount+'</span> </div><a role="button" tabindex="0" class="accordion-toggle region-sh" href="#" aria-label="Toggle checkboxes">Show</a>');
        }


        if ($('.accordion-container .form-checkboxes').length) {
            var panel = $('.accordion-container .form-checkboxes');
            panel.before('<a role="button" tabindex="0" class="accordion-toggle" href="#" aria-label="Toggle checkboxes"></a>');

            var toggle = $('.accordion-toggle');
            toggle.addClass('visible');


            $(document).on('click', '.accordion-toggle', function (e) {
                e.preventDefault();

                var accordionContent = $(this).next(panel);

                //Expand or collapse this panel
                $(accordionContent).slideToggle('slow', function () {
                    $(toggle).toggleClass('visible', $(this).is(':visible'));
                });
            });
        }
    }
});
