jQuery(document).ready(function($) {
    
  $('#buscador').on('change',function(e){
    alert('Changed!');
  });

        /*$.ajax({
            type: "POST",
            dataType: "json",
            url: document.location.protocol+'//'+document.location.host+'/CursosWp/wp-admin/admin-ajax.php',
            data: Objetos,
            success: function( response ) 
            { 
                var items = [];
                $.each(response, function(i, item) {
                    items.push('<li><a href="' + item.guid + '">' + item.post_title + '</a></li>');
                });
                $('select_ficha').append(items.join(''))
              console.log(response);
            },
            error: function( error )
            {
              alert( error );
            }
         });
*/
});