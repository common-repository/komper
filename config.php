<?php
global $wpdb;
global $komper_db_version;

$table_name = $wpdb->prefix . "komper_field";
$table_value = $wpdb->prefix . "komper_value";
$table_product = $wpdb->prefix . "komper_product";

$PAGE = isset($_GET['page']) ? $_GET['page'] : "";

define("KOMPER_TABLE_NAME",$table_name);
define("KOMPER_TABLE_VALUE",$table_value);
define("KOMPER_TABLE_PRODUCT",$table_product);
define("KOMPER_VERSION","1.1.3");
define("KOMPER_DB_VERSION","1.2");
define("DELIMITER",";");
define("PAGE",$PAGE);

$script = $_SERVER['SCRIPT_NAME'];
$path = str_replace("config.php","",$script);

define("KOMPERFOLDER",basename( dirname(__FILE__) ));
define("KOMPER_PATH",trailingslashit( plugins_url( KOMPERFOLDER ) ));

function _kdebug($str){
    echo "<pre>";print_r($str);echo "</pre>";
}

function jsredirect($url){
    echo "<script language='javascript'>
            window.location='".$url."';
          </script>";
}

?>