jQuery(document).ready(function($) {
  //document.getElementById("select_ficha").style.display = "none";
  $('#botonBuscar').on('click',function(e){
    var buscadorInput = document.getElementById("buscador").value;
    var data = { txtbuscar: buscadorInput, action: ajax_var.action};
    var loc = window.location.pathname;
    var dir = loc.substring(0, loc.lastIndexOf('/'));
    var path = dir.toLowerCase(dir);
    $.ajax({
      type: "POST",
      dataType: "json",
      url: ajax_var.url,
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