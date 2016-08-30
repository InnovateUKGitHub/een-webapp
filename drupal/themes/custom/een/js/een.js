jQuery(function () {
    var $ = jQuery;

    var buttons = $("label input[type='radio'], label input[type='checkbox']");
    new GOVUK.SelectionButtons(buttons);
});
