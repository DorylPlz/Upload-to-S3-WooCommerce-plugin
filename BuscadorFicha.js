jQuery(document).ready(function($) {
  //document.getElementById("select_ficha").style.display = "none";
  $('#botonBuscar').on('click',function(e){
    var buscadorInput = document.getElementById("buscador").value;
    var data = { txtbuscar: buscadorInput, action:'BuscadorFicha_process'};
    $.ajax({
      type: "POST",
      dataType: "json",
      url: document.location.protocol+'//'+document.location.host+'/CursosWp/wp-admin/admin-ajax.php',
      data: data,
      success: function( response, data ) 
      { 
        document.getElementById("select_ficha").style.display= "block";
            var len = response.length;

            $("#select_ficha").empty();
            for( var i = 0; i<len; i++){
                var id = response[i];
                
                $("#select_ficha").append("<option value='"+id+"'>"+id+"</option>");

            }
        console.log(response);
      },
      error: function( error )
      {
        console.log(error);
      }
   });
  });



});