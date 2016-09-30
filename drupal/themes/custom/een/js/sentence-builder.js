jQuery(function () {
    var $ = jQuery;
    
    
    
    $('#keywords').on('blur', function(){
        $('#search').val($('#keywords').text());
    });
    
});
