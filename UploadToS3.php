<?php  
/*
Plugin Name: UploadToS3-wc
Plugin URI: https://github.com/DorylPlz
Description: Uploads a file to S3 related to a WooCommerce product and saves the link to the product's metadata.
Version: 1.0
Author: Daryl Olivares
Author URI: https://github.com/DorylPlz
License: GPL2
*/
use Aws\S3\S3Client;
require 'C:/Users/daryl/vendor/autoload.php';

add_action( 'woocommerce_product_options_advanced', 'UploadToS3');
add_action('post_edit_form_tag', 'add_post_enctype');


function add_post_enctype() {
    echo ' enctype="multipart/form-data"';
}

function UploadToS3(){
 
	echo '<div class="options_group">';
 
	woocommerce_wp_text_input( array(
        'id'      => 'ManualPDF',
        'name'    => 'ManualPDF',
        'type'    => 'file',
		'label'   => 'Archivo PDF',
		'desc_tip' => true,
		'description' => 'Manual PDF del producto.',
    ) );
    echo '</div>';

}

add_action( 'woocommerce_process_product_meta', 'PDF_save_metafields', 10, 2 );
function PDF_save_metafields( $id, $post ){
    if($_FILES["ManualPDF"]['name'] != NULL){
        $config = require('configS3.php');
        $s3 = S3Client::factory(
            array(
                'credentials' => array(
                    'key' => $config['s3']['key'],
                    'secret' => $config['s3']['secret']
                ),
                'version' => 'latest',
                'region'  => $config['s3']['region']
            )
        );
        $S3Path = 'https://s3.'.$config['s3']['region'].'.amazonaws.com/' . $config['s3']['bucket'] . '/';
        $folder = 'prueba/';
    
        $FilePath = $folder . basename($_FILES["ManualPDF"]['name']);
        $FullLink = $S3Path . $FilePath;
        update_post_meta( $id, 'ManualPDF', $FullLink );
        try {
            $file = $_FILES["ManualPDF"]['tmp_name'];

            $s3->putObject(
                array(
                    'Bucket'=>$config['s3']['bucket'],
                    'Key' =>  $FilePath,
                    'SourceFile' => $file,
                )
            );

        } catch (S3Exception $e) {
            die('Error:' . $e->getMessage());
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
        
    
    }else{
        return;
    }
    
}
 ?>