/*
 * 
 * 
 * PLEASE DONT JUDGE THIS! 
 */


$( document ).ready(function() {
    $('h1').css('color', 'red');
});

$(document).on('change','select',function(){
    create();
    
    /*if($('#descision1').find('option:selected')){
        $('#flow1').removeClass('hide');
    };*/
    
    if($('#descision1').val()){
        $('#flow1').removeClass('hide');
    };
    
});

/*$(document).on('change','#descision1',function(){
    $("#descision2 option:selected, #from option:selected, #to option:selected, #based option:selected, #in option:selected").prop("selected", false);

    $('#from').addClass('hide');
    $('#to').addClass('hide');
    $('#in').addClass('hide');
    $('#based').addClass('hide');
    create();
});*/

$(document).on('keyup','#descision1',function(){
    /*$("#descision2 option:selected, #from option:selected, #to option:selected, #based option:selected, #in option:selected").prop("selected", false);

    $('#from').addClass('hide');
    $('#to').addClass('hide');
    $('#in').addClass('hide');
    $('#based').addClass('hide');*/
    create();
    
    if($('#descision1').val()){
        $('#flow1').removeClass('hide');
    };
});

$(document).on('change','#descision2',function(){
    $("#from option:selected, #to option:selected, #based option:selected, #in option:selected").prop("selected", false);

    $('#from').addClass('hide');
    $('#to').addClass('hide');
    $('#in').addClass('hide');
    $('#based').addClass('hide');
    
    if($('#descision2').find('option:selected')){
        var val= $('#descision2').val();
        $('#'+val).removeClass('hide');
    };
    
    create();
});


function create() {
    var sentence = "";
    
    sentence += "<span style='color:black'>"+$('.desc1').html()+"</span>";
    sentence += " ";
   // sentence += $('#descision1').find('option:selected').text();
    sentence += $('#descision1').val();
        
    sentence += " <span style='color:black'>"+$('.desc1-2').html()+"</span> ";
    sentence += $('#descision2').find('option:selected').text();
    
    if($('#from').find('option:selected') && !$('#from').hasClass('hide')){
        var from = $('#from');
        if($(from).find('select option:selected').val() != 'none'){
            sentence += " <span style='color:black'>"+$(from).children('p').text()+"</span> ";
        }
        sentence += $(from).find('select option:selected').text();
    };
    
    if($('#in').find('option:selected') && !$('#in').hasClass('hide')){
        var from = $('#in');
        if($(from).find('select option:selected').val() != 'none'){
            sentence += " <span style='color:black'>"+$(from).children('p').text()+"</span> ";
        }
        sentence += $(from).find('select option:selected').text();
    };
    
    if($('#based').find('option:selected') && !$('#based').hasClass('hide')){
        var from = $('#based');
        if($(from).find('select option:selected').val() != 'none'){
            sentence += " <span style='color:black'>"+$(from).children('p').text()+"</span> ";
        }
        sentence += $(from).find('select option:selected').text();
    };
    
    if($('#to').find('option:selected') && !$('#to').hasClass('hide')){
        var from = $('#to');
        if($(from).find('select option:selected').val() != 'none'){
            sentence += " <span style='color:black'>"+$(from).children('p').text()+"</span> ";
        }
        sentence += $(from).find('select option:selected').text();
    };
    
    
    
    $('.super-sentence').html(sentence);
}
