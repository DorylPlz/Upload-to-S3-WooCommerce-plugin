# Upload-to-S3-WooCommerce-plugin
* Permite subir archivos a S3 relacionados con el producto de WooCommerce, almacena la ruta del archivo de s3 como metadata "ManualPDF".
* Permite modificar el campo de metadata "ManualPDF" con documentos ya existentes en S3.
* Muestra el documento almacenado en "ManualPDF" en la página de producto con el enlace de descarga y nombre

El siguiente trozo de codigo es usado para obtener el enlace de descarga del link en la vista:
$ManualPDF = esc_url(get_post_meta(get_the_ID(), 'ManualPDF', true)); 

