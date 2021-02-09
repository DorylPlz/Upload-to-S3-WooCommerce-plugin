jQuery(document).ready(function($) {
  $("#select_ficha").empty();
  $("#select_ficha").append("<option value='0'></option>");
  //document.getElementById("select_ficha").style.display = "none";
  $('#botonBuscar').on('click',function(e){
    var buscadorInput = document.getElementById("buscador").value;
    var data = { txtbuscar: buscadorInput, action: ajax_var1.action};
    var loc = window.location.pathname;
    var dir = loc.substring(0, loc.lastIndexOf('/'));
    var path = dir.toLowerCase(dir);
    $.ajax({
      type: "POST",
      dataType: "json",
      url: ajax_var1.url,
      data: data,
      success: function( response, data ) 
      { 
        document.getElementById("select_ficha").style.display= "block";
            var len = response.length;

            $("#select_ficha").empty();
            $("#select_ficha").append("<option value='0'>No seleccionar ficha</option>");
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