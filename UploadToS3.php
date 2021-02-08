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
require 'aws/aws-autoloader.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

add_action( 'woocommerce_product_options_advanced', 'form');
add_action('post_edit_form_tag', 'add_post_enctype');
add_action( 'woocommerce_process_product_meta', 'PDF_save_file', 10, 2 );
add_action('admin_head-post.php', 'BuscadorFicha_init');
add_action('admin_head-post.php', 'UpdateS3_init');
add_action('widgets_init', 'fichas_widget_init');

function fichas_widget_init() {
    register_widget('fichas_Widget');
}

function BuscadorFicha_init(){
    wp_enqueue_script('Buscador_Ficha', plugins_url('/BuscadorFicha.js', __FILE__), array('jquery'));

    wp_localize_script( 'Buscador_Ficha', 'ajax_var1', array(
        'url'    => admin_url( 'admin-ajax.php' ),
        'action' => 'BuscadorFicha_process'
    ) );
}
function UpdateS3_init(){
    wp_enqueue_script('UpdateS3_init', plugins_url('/S3Form.js', __FILE__), array('jquery'));

    wp_localize_script( 'UpdateS3_init', 'ajax_var2', array(
        'url'    => admin_url( 'admin-ajax.php' ),
        'action' => 'UpdateS3_process'
    ) );
}
add_action('wp_ajax_nopriv_BuscadorFicha_process', 'BuscadorFicha_process');
add_action('wp_ajax_BuscadorFicha_process', 'BuscadorFicha_process');
function BuscadorFicha_process(){
    $texto = esc_attr($_POST['txtbuscar']);
    $objects = getObjects();
    $json_objetos = json_encode(filterObjects($objects, $texto));
    echo $json_objetos;
    exit();
}
add_action('wp_ajax_nopriv_UpdateS3_process', 'UpdateS3_process');
add_action('wp_ajax_UpdateS3_process', 'UpdateS3_process');
function UpdateS3_process(){
    $key = esc_attr($_POST['key']);
    $secret = esc_attr($_POST['secret']);
    $bucket = esc_attr($_POST['bucket']);
    $region = esc_attr($_POST['region']);
    $folder = esc_attr($_POST['folder']);
    updateS3_function($key,$secret,$bucket,$region,$folder);
    echo 2;
    exit();
}

function updateS3_function($key,$secret,$bucket,$region,$folder){
    add_option('mrs_S3Key',$key);
    add_option('mrs_S3Secret',$secret);
    add_option('mrs_S3Bucket',$bucket);
    add_option('mrs_S3Region',$region);
    add_option('mrs_S3Folder',$folder);

    update_option('mrs_S3Key',$key);
    update_option('mrs_S3Secret',$secret);
    update_option('mrs_S3Bucket',$bucket);
    update_option('mrs_S3Region',$region);
    update_option('mrs_S3Folder',$folder);

    return;
}

//Crea campos HTML, estos se muestran en las opciones avanzadas en la creacion/modificacion de un producto woocommerce
function form(){
    $config = require('configS3.php');
    $status = $config['s3']['status'];
    if($status){
        //Input para realizar la busqueda
        echo '<div class="options_group">';
        woocommerce_wp_text_input( array(
            'id'      => 'buscador',
            'name'    => 'buscador',
            'type'    => 'Text',
            'label'   => 'Buscar Ficha Existente',
            'desc_tip' => true,
            'description' => 'Buscador por texto para ayudar encontrar una ficha ya existente.'
        ) );
        //Boton buscar
        echo '<p class="form-field buscador_field "><button id="botonBuscar" name="botonBuscar" type="button" class="short">Buscar</button></p>';
            //Select que se autocompleta con javascript que trae los documentos existentes basados en el nombre ingresado arriba
            woocommerce_wp_select( array(
                    'id'      => 'select_ficha',
                    'name'    => 'select_ficha',
                    'label'   => '',
                    'custom_attributes' => array('hidden' => 'hidden' )
                ) );
            echo '</div>';
        //Subir documento nuevo
        echo '<div class="options_group">';
        woocommerce_wp_text_input( array(
            'id'      => 'ManualPDF_nuevo',
            'name'    => 'ManualPDF_nuevo',
            'type'    => 'file',
            'label'   => 'Nueva Ficha Técnica'
        ) );
        echo '</div>';

        //Muestra el link del documento en caso de que exista
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
    }else{
        echo '<div class="options_group" id="DivUpdateS3">';
            echo '<span class="wrap">';
                echo '<p id="addArtInput" class="form-field hide_if_grouped hide_if_external">
                        <label for="configS3">Conexión a S3</label> 
                        <span name="configS3">La conexión a S3 no está configurada, porfavor ingrese sus credenciales, si no cuenta con ellas porfavor contacte con el administrador</span>
                    </p> 
                    <p id="addArtInput" class="form-field hide_if_grouped hide_if_external">
                        <input id="S3Key" placeholder="Key" type="text" name="S3Key" value="" required>
                    </p> 
                    <p id="addArtInput" class="form-field hide_if_grouped hide_if_external">
                        <input id="S3Secret" placeholder="Secret" type="text" name="S3Secret" value="" required>
                    </p> 
                    <p id="addArtInput" class="form-field hide_if_grouped hide_if_external">
                        <input id="S3Bucket" placeholder="Bucket" type="text" name="S3Bucket" value="" required>
                    </p> 
                    <p id="addArtInput" class="form-field hide_if_grouped hide_if_external">
                        <input id="S3Region" placeholder="Region" type="text" name="S3Region" value="" required>
                    </p> 
                    <p id="addArtInput" class="form-field hide_if_grouped hide_if_external">
                        <input id="S3Folder" placeholder="Carpeta" type="text" name="S3Folder" value="" required>
                    </p> ';
                    echo '<p class="form-field buscador_field "><button id="btnUpdateS3" name="btnUpdateS3" type="button" class="short">Actualizar credenciales</button></p>';
            echo '</span>';
        echo '</div>';
       
    }
    
}

//Sube a S3 y almacena enlace en metadata, o solo almacena enlace en metadata
function PDF_save_file( $id, $post ){
    $config = require('configS3.php');
    $status = $config['s3']['status'];
    if($status){
        $opcionSelect = esc_attr($_POST['select_ficha']);
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
            }elseif($opcionSelect != 'No seleccionar ficha' || $opcionSelect != NULL){
                $FilePath = $folder . $opcionSelect;
                $FullLink = $S3Path . $FilePath;
                edit_meta($id,$FullLink);

            }else{
                return;
            }  
    }  
}

function edit_meta($id,$FullLink){
    update_post_meta( $id, 'ManualPDF', $FullLink );
}

//Conexion a S3
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

//Trae los archivos desde S3
function getObjects(){
    $s3 = s3();
    $config = require('configS3.php');
    $objects = $s3->getIterator('ListObjects',[
        'Bucket' => $config['s3']['bucket'],
        'Prefix' => $config['s3']['folder']
    ]);
    return $objects;
}
//Filtra los objetos de S3 por la condición de nombre
function filterObjects($objects, $condition){
    $config = require('configS3.php');
    //Agrega el campo "No seleccionar Ficha" para que se le muestre de primera opción al usuario
    $objectsArray = array(
        "0" => "No seleccionar ficha"
    );
    //Condiciones para eliminar la carpeta de S3 en el nombre final
    $search = array(
        $config['s3']['folder'].'/'
    );
    
    $replace = array(
        ''
    );

    foreach($objects as $object) {
        if($object['Key'] != $config['s3']['folder'].'/'){//Omite la carpeta base ya que S3 la trae junto a todos sus archivos
            if (strpos($object['Key'], $condition)) { //Filtro por nombre
                $doc = str_replace( $search, $replace, $object['Key'] ); //Elimina la carpeta del nombre final
                $objectsArray[] = $doc;
            }
        }
    }
    return $objectsArray;
}

//Widget que muestra las fichas en la página de producto
class fichas_Widget extends WP_Widget {
    function __construct() {
        $widget_options = array(
            'classname' => 'widget_class', //CSS
            'description' => 'Muestra la ficha registrado en el metadata del post'
        );
        
        parent::__construct('ficha_id', 'Ficha de Producto', $widget_options);
    }

    function form($instance) {
        $defaults = array('title' => 'Ficha');
        $instance = wp_parse_args( (array) $instance, $defaults);
        
        $title = esc_attr($instance['title']);
        
        echo '<p>Title <input type="text" class="widefat" name="'.$this->get_field_name('title').'" value="'.$title.'" /></p>';
    }
    

    function update($new_instance, $old_instance) {
        
        $instance = $old_instance;        
        $instance['title'] = strip_tags($new_instance['title']);        
        return $instance;
    }
    
    function widget($args, $instance) {
        extract( $args );        
        $title = apply_filters('widget_title', $instance['title']);

        if(is_single()) {
            echo $before_widget;
            echo $before_title.$title.$after_title;

            $ManualPDF = esc_url(get_post_meta(get_the_ID(), 'ManualPDF', true));
            if($ManualPDF != NULL){

                echo '<ul>
                            <li><a href="'.$ManualPDF.'" download="Manual.PDF">Manual de Producto</a></li>
                        </ul>';    
                    echo $after_widget;
            }else{
                return;
            }

        }
    }
}
?>