jQuery(document).ready(function($) {
    //document.getElementById("select_ficha").style.display = "none";
    $('#btnUpdateS3').on('click',function(e){
      var key = document.getElementById("S3Key").value;
      var secret = document.getElementById("S3Secret").value;
      var bucket = document.getElementById("S3Bucket").value;
      var region = document.getElementById("S3Region").value;
      var folder = document.getElementById("S3Folder").value;
      var data = { key: key, secret: secret, bucket: bucket, region: region, folder: folder, action: ajax_var2.action};
      $.ajax({
        type: "POST",
        dataType: "json",
        url: ajax_var2.url,
        data: data,
        success: function( response, data ) 
        { 
          if(response = 1){
              var htmlUpdate = '<span class="wrap"><p id="addArtInput" class="form-field hide_if_grouped hide_if_external"><label for="configS3">Conexión a S3</label> <span name="configS3">Conexión configurada, porfavor recargue la página</span></p> </span>'
            $('#DivUpdateS3').html(htmlUpdate);
          }
        },
        error: function( error )
        {
          console.log(error);
        }
     });
    });
  
  
  
  });