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

add_action( 'woocommerce_product_options_advanced', 'form');
add_action('post_edit_form_tag', 'add_post_enctype');
add_action( 'woocommerce_process_product_meta', 'PDF_save_file', 10, 2 );
add_action('wp', 'BuscadorFicha_init');
//add_action('wp_ajax_nopriv_BuscadorFicha_process', 'BuscadorFicha_process');

function BuscadorFicha_init(){
    wp_register_script('Buscador_Ficha', plugins_url('BuscadorAJAX.js', __FILE__), array('jquery'));
    wp_enqueue_script('jquery');
    wp_enqueue_script('Buscador_Ficha');
}
/*
function BuscadorFicha_process(){
    $meta = esc_attr($_POST['postMeta']);
    $args = array(
        'meta_value' => $meta,
        'meta_key' => 'metadatatexto'
    );
    $posts = json_encode(get_posts($args));
    echo $posts;
    exit();
}*/

function form(){
    $objects = getObjects();
    $filter = filterObjects($objects, '');
    echo '<div class="options_group">';
    woocommerce_wp_text_input( array(
        'id'      => 'buscador',
        'name'    => 'buscador',
        'type'    => 'Text',
        'label'   => 'Buscar Ficha Existente',
        'desc_tip' => true,
        'description' => 'Buscador por texto para ayudar encontrar una ficha ya existente.'
    ) );
        woocommerce_wp_select( array(
                'id'      => 'select_ficha',
                'name'    => 'select_ficha',
                'label'   => '',
                'desc_tip' => true,
                'description' => 'Cambiar ficha actual por otra ya existente en la nube.',
                'options' =>  $filter
            ) );
        echo '</div>';

    echo '<div class="options_group">';
    woocommerce_wp_text_input( array(
        'id'      => 'ManualPDF_nuevo',
        'name'    => 'ManualPDF_nuevo',
        'type'    => 'file',
        'label'   => 'Nueva Ficha Técnica'
    ) );
    echo '</div>';

    if(get_post_meta(get_the_ID(), 'ManualPDF', true)){
        woocommerce_wp_text_input(array(
            'id'      => 'ManualPDF',
            'name'    => 'ManualPDF',
            'type'    => 'text',
            'label'   => 'Ficha actual',
            'desc_tip' => true,
            'description' => 'Manual PDF del producto.',
            'custom_attributes' => array('readonly' => 'readonly')
        ) );
    }
}

function PDF_save_file( $id, $post ){
    if($_FILES["ManualPDF_nuevo"]['name'] != NULL){
        $config = require('configS3.php');
        $s3 = s3();
        $S3Path = 'https://s3.'.$config['s3']['region'].'.amazonaws.com/' . $config['s3']['bucket'] . '/';
        $folder = 'prueba/';
    
        $FilePath = $folder . basename($_FILES["ManualPDF_nuevo"]['name']);
        $FullLink = $S3Path . $FilePath;
        
        try {
            $file = $_FILES["ManualPDF_nuevo"]['tmp_name'];

            $s3->putObject(
                array(
                    'Bucket'=>$config['s3']['bucket'],
                    'Key' =>  $FilePath,
                    'SourceFile' => $file,
                    'StorageClass' => 'REDUCED_REDUNDANCY'
                )
            );

            edit_meta($id,$FullLink);
        } catch (S3Exception $e) {
            die('Error:' . $e->getMessage());
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        } 
    }else{
        return;
    }
}

function edit_meta($id,$FullLink){
    update_post_meta( $id, 'ManualPDF', $FullLink );
}

function s3(){
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
    return $s3;
}

function add_post_enctype() {
    echo ' enctype="multipart/form-data"';
}

function getObjects(){
    $s3 = s3();
    $config = require('configS3.php');
    $objects = $s3->getIterator('ListObjects',[
        'Bucket' => $config['s3']['bucket'],
        'Prefix' => $config['s3']['folder']
    ]);
    return $objects;
}

function filterObjects($objects, $condition){
    $objectsArray = array(
        "0" => ""
    );
    foreach($objects as $object) {
        $objectsArray[] = $object['Key'];
    }
    return $objectsArray;
}

?>