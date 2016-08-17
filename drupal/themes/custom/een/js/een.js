$(function() {
    var buttons = $("label input[type='radio'], label input[type='checkbox']");
console.log(buttons);
    var selectionButtons = new GOVUK.SelectionButtons(buttons);
});

