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
add_action('admin_head-post.php', 'BuscadorFicha_init');

add_action('wp_ajax_nopriv_BuscadorFicha_process', 'BuscadorFicha_process');


function BuscadorFicha_init(){
    wp_register_script('Buscador_Ficha-js', plugins_url('/BuscadorFicha.js', __FILE__), array('jquery'));
    wp_enqueue_script('jquery');
    wp_enqueue_script('Buscador_Ficha-js');
}

function BuscadorFicha_process(){
    $texto = esc_attr($_POST['txtbuscar']);
    $objects = getObjects();
    $json_objetos = json_encode(filterObjects($objects, $texto));
    echo $json_objetos;
    exit();
}

function form(){
    
    $filter = [];
    echo '<div class="options_group">';
    woocommerce_wp_text_input( array(
        'id'      => 'buscador',
        'name'    => 'buscador',
        'type'    => 'Text',
        'label'   => 'Buscar Ficha Existente',
        'desc_tip' => true,
        'description' => 'Buscador por texto para ayudar encontrar una ficha ya existente.'
    ) );
    echo '<p class="form-field buscador_field "><button id="botonBuscar" name="botonBuscar" type="button" class="short">Buscar</button></p>';
        woocommerce_wp_select( array(
                'id'      => 'select_ficha',
                'name'    => 'select_ficha',
                'label'   => '',
                'options' =>  $filter,
                'custom_attributes' => array('hidden' => 'hidden' )
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
            'custom_attributes' => array('readonly' => 'readonly' )
        ) );
    }
}

function PDF_save_file( $id, $post ){
    $opcionSelect = esc_attr($_POST['select_ficha']);
    $config = require('configS3.php');
    $s3 = s3();
    $S3Path = 'https://s3.'.$config['s3']['region'].'.amazonaws.com/' . $config['s3']['bucket'] . '/';
    $folder = $config['s3']['folder'] . '/';


        if($_FILES["ManualPDF_nuevo"]['name'] != NULL){
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
        }elseif($opcionSelect != 'No seleccionar ficha'){
            $FilePath = $folder . $opcionSelect;
            $FullLink = $S3Path . $FilePath;
            edit_meta($id,$FullLink);

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
    $config = require('configS3.php');
    $objectsArray = array(
        "0" => "No seleccionar ficha"
    );
    $search = array(
        $config['s3']['folder'].'/'
    );
    
    $replace = array(
        ''
    );
    foreach($objects as $object) {
        if($object['Key'] != $config['s3']['folder'].'/'){
            if (strpos($object['Key'], $condition)) {
                $doc = str_replace( $search, $replace, $object['Key'] );
                $objectsArray[] = $doc;
            }
        }
    }
    return $objectsArray;
}

?>