jQuery(function () {
    var $ = jQuery;
    
    
    
    $('#keywords').on('keyup', function(){
        $('#search').val($('#keywords').text());
    });
 
    $(".chosen-select-multiple").chosen({
        disable_search:false
    }); 
    
});
