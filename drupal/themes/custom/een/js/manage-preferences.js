(function () {
    'use strict';

    var $ = jQuery;

    $('.js-unsubscribe-checkboxes').click(function(){
        if($(this).is(':checked')){
           $('.js-update-checkboxes').attr('read-only', 'true').removeAttr('checked').parent('label').removeClass('selected').addClass('checkbox-disabled');
        } else {
            $('.js-update-checkboxes').removeAttr('read-only').parent('label').removeClass('checkbox-disabled');
        }
    });


})();
