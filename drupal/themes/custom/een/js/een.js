jQuery(function () {
    var $ = jQuery;

    var buttons = $("label input[type='radio'], label input[type='checkbox']");
    new GOVUK.SelectionButtons(buttons);
    
    
    /* Basic toggle for form helper text */
    $(document).on('click', '.field-prefix', function() {
        var desc = $(this).next();
        $(desc).toggle();
    });
    
    /* EEN-229
    $('textarea').focus(function() {
        $(this).closest('.js-form-type-textarea').find('div[id$="--description"]').toggleClass('show');
    }); */
});

