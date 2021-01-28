# Upload-to-S3-WooCommerce-plugin
* Permite subir archivos a S3 relacionados con el producto de WooCommerce, almacena la ruta del archivo de s3 como metadata "ManualPDF".
* Permite modificar el campo de metadata "ManualPDF" con documentos ya existentes en S3.
* Muestra el documento almacenado en "ManualPDF" en la página de producto con el enlace de descarga y nombre

**Instalación**
Cambiar el nombre de **configS3-example.php** a **configS3.php**, acceder al archivo e ingresar las credenciales de s3
- 'key' => '...',
- 'secret' => '...',
- 'bucket' => '...',
- 'region' => 'us-east-1',
- 'folder' => 'prueba' //La carpeta dentro del bucket en la que se almacenarán los archivos subidos

