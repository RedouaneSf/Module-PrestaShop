$(document).ready(function () {
   
    $('#field-company').hide();
    $('#field-siret').hide();
    $('.form-control-comment').hide();
    
    $('#field-id_default_group-4').click(function () {
      
      
      $('#field-company').show();
      $('#field-siret').show();
      
    });
    $('#field-id_default_group-3').click(function () {
       
        
        $('#field-company').hide();
        $('#field-siret').hide();
        
      });

  });