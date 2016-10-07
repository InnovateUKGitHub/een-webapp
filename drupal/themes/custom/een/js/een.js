jQuery(function () {
    var $ = jQuery;

    var buttons = $("label input[type='radio'], label input[type='checkbox']");
    new GOVUK.SelectionButtons(buttons);

    /* Basic toggle for form helper text */
    $(document).on('click', '.field-prefix', function () {
        var desc = $(this).next();
        $(desc).toggle();
    });
});

