<?php
/*
Plugin Name: Komper
Plugin URI: http://www.vnetware.com/komper/
Description: A plugin to create side-by-side product comparison. This is free version, get the pro version with more features! 
Version: 1.1.4
Author: Miaz Akemapa
Author URI: http://www.photogrammer.net/
*/

require_once("config.php");

function komper_admin() {
    include('komper_admin.php');
}

function komper_admin_actions() {
        add_options_page("Komper Admin", "Komper Settings", 1, "Komper", "komper_admin");
}

function install_table(){
   global $wpdb;
   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
   
   //Field name table
   $tbl_name = KOMPER_TABLE_NAME;
   if($wpdb->get_var("show tables like '$tbl_name'") != $tbl_name){
       $sql = "CREATE TABLE $tbl_name (`id` INT(11) NOT NULL AUTO_INCREMENT 
                PRIMARY KEY, `field_name` VARCHAR(100) NULL, `field_type` VARCHAR(50) NULL DEFAULT 'varchar(50)',
                `field_format` VARCHAR( 10 ) NULL,
                `parent` INT(5) NULL DEFAULT '0',`field_order` INT( 5 ) NULL)";
       $wpdb->query($sql);
   }
   
   //field value table
   $tbl_valname = KOMPER_TABLE_VALUE;
   if($wpdb->get_var("show tables like '$tbl_valname'") != $tbl_valname){
        $sql2 = "CREATE TABLE $tbl_valname (`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY 
                KEY, `product_id` INT(11) NULL, `field_id` INT(11) NOT NULL, `field_values` TEXT NULL, 
                INDEX (`product_id`));";
        $wpdb->query($sql2);
   }
   
   $tbl_product = KOMPER_TABLE_PRODUCT;
   if($wpdb->get_var("show tables like '$tbl_product'") != $tbl_product){
        $sql3 = "CREATE TABLE $tbl_product (`id` INT(11) NOT NULL AUTO_INCREMENT PRIMARY 
                KEY, `product_name` varchar(100) NULL, `product_image` VARCHAR(100) NULL,  `post_id` INT(11) NULL,  
                INDEX (`product_name`));";
        $wpdb->query($sql3);
   }
      
   add_option("komper_db_version", KOMPER_DB_VERSION);
}

register_activation_hook(__FILE__,'install_table');
add_action('admin_menu', 'komper_admin_actions');

//add_action('admin_print_scripts', 'add_field_javascript');

//Styles
add_action( 'admin_init', 'komper_admin_init' );
//add_action('init', 'load_jquery_ui');

function komper_admin_init() {
   wp_register_style( 'komperStyleSheet', plugins_url('assets/css/komper.css', __FILE__) );
   //wp_register_style( 'jqueryStyle', plugins_url('assets/css/smoothness/jquery-ui-1.9.0.custom.min.css', __FILE__) );
   wp_register_style( 'cssbootstrap', plugins_url('assets/css/bootstrap.min.css', __FILE__) );
   
   wp_register_script('bootstrapjs', plugins_url('assets/js/bootstrap.min.js', __FILE__));
   wp_enqueue_script('bootstrapjs');
}
function load_jquery_ui() {
    global $wp_scripts;
    
    wp_enqueue_script('jquery-ui-tabs');
    $ui = $wp_scripts->query('jquery-ui-core');
 
    //$url = "https://ajax.aspnetcdn.com/ajax/jquery.ui/{$ui->ver}/themes/smoothness/jquery.ui.all.css";
    $url = "http://code.jquery.com/ui/{$ui->ver}/themes/base/jquery-ui.css";
    wp_enqueue_style('jquery-ui-smoothness', $url, false, $ui->ver);
}
 


//AJAX Action
add_action('wp_ajax_add_field','add_field_callback');
add_action('wp_ajax_edit_field','edit_field');
add_action('wp_ajax_del_fields','del_fields');
add_action('wp_ajax_sort_field','sort_field');
add_action('wp_ajax_compare_result','compare_result');
add_action('wp_ajax_btn_generate_form','btn_generate_form');
add_action('wp_ajax_mce_display_form','mce_display_form');
add_action('wp_ajax_del_products','del_products');

function add_field_callback() {
    global $wpdb;
    $fname = isset($_POST['fieldname']) ? $_POST['fieldname'] : "";
    $ftype = isset($_POST['fieldtype']) ? $_POST['fieldtype'] : "";
    $fformat = isset($_POST['fieldformat']) ? $_POST['fieldformat'] : "";
    $icount= isset($_POST['icount']) ? $_POST['icount'] : "";
    $icount = $icount+1;
    $last_id = "";
    $field_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM ".KOMPER_TABLE_NAME.";" ) );
    
    if($fname != "" AND $ftype != "" AND $field_count < 15){
        $wpdb->insert(KOMPER_TABLE_NAME, 
                array('field_name' => $fname, 
                    'field_type' => $ftype,
                    'field_format' => $fformat,
                    'parent' => 0,
                    'field_order' => $icount
                ), array('%s','%s','%s','%d','%d') 
        );
        $last_id = $wpdb->insert_id;        
    }
    echo $last_id;
    die();
}

function sort_field(){
    global $wpdb;
    $komfields = isset($_POST['kompfield']) ? $_POST['kompfield'] : "";
    $listingCounter = 1;
    $res = "";
    foreach ($komfields as $recordIDValue) {
        $sql = "UPDATE ".KOMPER_TABLE_NAME." SET field_order = " . $listingCounter . " WHERE id = " . $recordIDValue;
        $wpdb->query($sql);
        
        $listingCounter = $listingCounter + 1;	
    }
    echo "OK";
    die();
}
function edit_field() {
    global $wpdb;
    $fid = isset($_POST['e_fieldid']) ? $_POST['e_fieldid'] : "";
    $fname = isset($_POST['e_fieldname']) ? $_POST['e_fieldname'] : "";
    if($fid != "" AND $fname != ""){
        global $wpdb;
        $ftype = isset($_POST['e_fieldtype']) ? $_POST['e_fieldtype'] : "";
        $fformat = isset($_POST['e_fieldformat']) ? $_POST['e_fieldformat'] : "";
        
        $wpdb->update( 
                KOMPER_TABLE_NAME, 
                array( 
                        'field_name' => $fname,
                        'field_type' => $ftype,
                        'field_format' => $fformat
                ), 
                array( 'id' => $fid ), 
                array('%s','%s','%s'), 
                array( '%d' ) 
        );
    }
    die();
}
function del_fields(){
    global $wpdb;
    $fids = isset($_POST['fids']) ? $_POST['fids'] : "";
    $jml = count($fids);
    if($jml > 0){
        $i=0;
        while($i<$jml){
            $wpdb->query("delete from ".KOMPER_TABLE_NAME." where id = '".$fids[$i]."'");
            $i++;
        }
    }
    die();
}

function del_products(){
    global $wpdb;
    $pids = isset($_POST['pids']) ? $_POST['pids'] : "";
    $jml = count($pids);
    if($jml > 0){
        $i=0;
        while($i<$jml){
            $wpdb->query("delete from ".KOMPER_TABLE_PRODUCT." where id = '".$pids[$i]."'");
            $i++;
        }
    }
    die();
}

function compare_result(){
    $pids = isset($_POST['pids']) ? $_POST['pids'] : "";
    $table = display_tables($pids);
    echo $table;
    die();
}
function btn_generate_form(){
    if ( !current_user_can('edit_pages') && !current_user_can('edit_posts') ) {
    	die(__("You are not allowed to be here"));
    }
    wp_register_style( 'jqueryStyle', plugins_url('assets/css/smoothness/jquery-ui-1.9.0.custom.min.css', __FILE__) );
    wp_enqueue_style('jqueryStyle');
    wp_register_style( 'bootstrapcss', plugins_url('assets/css/bootstrap.min.css', __FILE__) );
    wp_enqueue_style('bootstrapcss');
    wp_deregister_script( 'jquery-ui-core' );
    wp_deregister_script( 'jquery-ui-autocomplete' );
    
    wp_register_script('bootstrapjs', plugins_url('assets/js/bootstrap.min.js', __FILE__));
    wp_enqueue_script('bootstrapjs');
    
    include_once( dirname( dirname(__FILE__) ) . '/komper/assets/tinymce/komper.php');
    die();	
}
function mce_display_form(){
    echo "nothing to do here";
    die();
}


//Rewrite Rule for Output Page
add_action( 'init', 'wpse9870_init_internal' );
function wpse9870_init_internal(){
    add_rewrite_rule( 'komper.php$', 'index.php?wpse9870_api=1', 'top' );
}

add_filter( 'query_vars', 'wpse9870_query_vars' );
function wpse9870_query_vars( $query_vars ){
    $query_vars[] = 'wpse9870_api';
    return $query_vars;
}

add_action( 'parse_request', 'wpse9870_parse_request' );
function wpse9870_parse_request( &$wp ){
    //wp_enqueue_script('jquery');
    if ( array_key_exists( 'wpse9870_api', $wp->query_vars ) ) {
        include 'output.php';
        exit();
    }
    return;
}

// WIDGET CLASS
class Komper extends WP_Widget {
    function Komper() {
        $widget_ops = array(
                'classname' => 'Komper',
                'description' => 'A plugin to display comparasion table of your products.'
          );
      $this->WP_Widget(
                'Komper',
                'Komper',
                $widget_ops
      );
    }
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['newwindow'] = $new_instance['newwindow'];
        $instance['noheader'] = $new_instance['noheader'];

        return $instance;
    }
    public function form( $instance ) {
        $defaults = array( 'title' => 'Products', 'newwindow' => false,'noheader' => false);
        $instance = wp_parse_args( (array) $instance, $defaults );
        
        if(isset($instance[ 'title' ])){
            $title = $instance['title'];
        }else{
            $title = __('New title','text_domain');
        }
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>
        <p>
            <input class="checkbox" id="<?php echo $this->get_field_id('newwindow'); ?>" name="<?php echo $this->get_field_name('newwindow'); ?>" type="checkbox" value="1" <?php checked($instance['newwindow'],true );?> />
            <label for="<?php echo $this->get_field_id('newwindow'); ?>"><?php _e('Open in new window'); ?></label> 
        </p>
        <p>
            <input class="checkbox" id="<?php echo $this->get_field_id('noheader'); ?>" name="<?php echo $this->get_field_name('noheader'); ?>" type="checkbox" value="1" <?php checked($instance['noheader'],true );?> />
            <label for="<?php echo $this->get_field_id('noheader'); ?>"><?php _e('Without Themes Header'); ?></label> 
        </p>
        <?php 
    }
    
    function widget($args, $instance) {
        wp_register_style( 'jqueryStyle', plugins_url('assets/css/smoothness/jquery-ui-1.9.0.custom.min.css', __FILE__) );
        wp_enqueue_style('jqueryStyle');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-autocomplete');
        wp_register_script('komperjs', plugins_url('assets/js/komper.js', __FILE__), array('jquery'), '1.0', true ); 
        wp_enqueue_script('komperjs');  
        
        extract($args, EXTR_SKIP);
        $title = apply_filters( 'widget_title', $instance['title'] );
        $target = apply_filters( 'widget_title', $instance['newwindow'] );
        $noheader = apply_filters( 'widget_title', $instance['noheader'] );
        echo $before_widget;
        if (!empty($title))
            echo $before_title . $title . $after_title;
        display_form($target,$noheader);
        echo $after_widget; // post-widget code from theme
    }
    
}
add_action('widgets_init',create_function('','return register_widget("Komper");'));

function cari($array, $key, $value){
    $results = array();
    if (is_array($array)){
        if (isset($array[$key]) && $array[$key] == $value)
            $results[] = $array;

        foreach ($array as $subarray)
            $results = array_merge($results, cari($subarray, $key, $value));
    }
    return $results;
}

function display_tables($pids){
    global $wpdb;
    $res = "";
    
    $ids = array_unique(explode(",",trim($pids)));
    $p_jml = count($ids);
    
    //reset $PIDS
    $a = 0;
    $pids = "";
    while($a < $p_jml){
        if($a > 0) $pids = $pids.",";
        $pids = $pids.$ids[$a];
        $a++;
    }
    
    $i = 0;
    while($i < $p_jml){
        $sql = "SELECT a.id,a.field_name,a.field_type,a.field_order,a.field_format,b.id as pid, b.product_id,b.field_values FROM ".KOMPER_TABLE_NAME." a ";
        $sql .= " left join ".KOMPER_TABLE_VALUE." b on b.field_id = a.id and b.product_id = $ids[$i] ";
        $sql .= " order by a.field_order asc";
        $data[] = $wpdb->get_results($sql);
        $i++;
    }
    
    $fields = $wpdb->get_results("select * from ".KOMPER_TABLE_NAME." order by field_order asc");
    $products = $wpdb->get_results("select * from ".KOMPER_TABLE_PRODUCT." where id in (".$pids.") order by FIND_IN_SET(id, '$pids') ");

    $f_jml = count($fields);
    $d_jml = count($data);
    $div = $d_jml / $p_jml;
    
    $res .= "<link rel='stylesheet' id='cssbootstrap-css'  href='".plugins_url('assets/css/bootstrap.min.css', __FILE__)."' type='text/css' media='all' />";
    $res .= "<script type='text/javascript' src='".plugins_url('assets/js/bootstrap.min.js', __FILE__)."'></script>";
    $res .= '<table class="table table-striped table-bordered">
    <tbody>
        <tr>
            <th width="20%" rowspan="2"></th>';
            for($y=0;$y<$p_jml;$y++){
                $res .= '<td><h4>'.$products[$y]->product_name.'</h4></td>';
            }
        $res.= '</tr>
        <tr>';
            for($x=0;$x<$p_jml;$x++){
                if(isset($products[$x]->product_image) and ($products[$x]->product_image != "")){
                    $res.='<td><img src="'.get_site_url()."/wp-content/products/thumb/".$products[$x]->product_image.'" class="img-rounded" /></td>';
                }else $res .= "<td></td>";
            }
        $res.='</tr>';
        $i=0;
        foreach($fields as $row){
            if($row->field_type == 'group'){$trclass='class="success fieldgroup"';$colspan = "colspan='".($p_jml+1)."'";}else {$trclass=""; $colspan="";}
        $res.='<tr '.$trclass.'>
            <td '.$colspan.'>'.$row->field_name.'</td>';
            
            for($x=0;$x<$p_jml;$x++){
                if($data[$x][$i]->field_type == "group"){
                    $res .= '';
                }else{
                    $res.='<td>';
                    if($data[$x][$i]->field_type == "radio"){ 
                        if($data[$x][$i]->field_values == "yes"){
                            $res.='<i class="icon-ok"></i>';
                        }else{
                            $res.='<i class="icon-remove"></i>';;
                        } 
                    }else{
                        $res.=format_text($data[$x][$i]->field_format,$data[$x][$i]->field_values); 
                        //$res .= $data[$x][$i]->field_values;
                    }
                    $res.='</td>';
                }
            }
        $res.='</tr>';
        $i++; }
    $res.='</tbody>
</table>';
    
    return $res;
}

function display_form($target = null,$noheader = null){
    if($target == "yes" or $target == "1"){ $f_target = " target='_blank' ";}else {$f_target = "";}
    if($noheader == "yes" or $noheader == "1"){$f_value = "noheader";}else{$f_value="";}
    $rand = rand(1,999);
    
    echo "<script>
            var getdata_url = '".get_site_url()."/';
                jQuery('#kpids').val('');
            </script>";
        $komper_url = get_site_url();
        $plugin_url = plugins_url('', __FILE__);
print <<<EOM
  <style>
    .ui-autocomplete {
        clear:both;
        max-height: 300px;
        overflow-y: auto;
        /* prevent horizontal scrollbar */
        overflow-x: hidden;
        font-size: 10px;
    }
    .ui-autocomplete-loading {
        background: white url('$plugin_url/assets/img/ui-anim_basic_16x16.gif') right center no-repeat;
    }
    
    * html .ui-autocomplete {
        height: 100px;
    }
    </style>
   <strong>Select Products</strong><br />
   <div>
       <input type="text" name="$rand" class="komperproduct span3" id="komper_product_$rand" /> 
        <br /><br />
        <div>
           <ul id='kprodlist_$rand' class='kprodlist'>
           </ul>
        </div>
   <form name='fcompare' id='fcompare' method='GET' action='$komper_url/komper.php' $f_target >
       <input type="hidden" name="kpids" id="kpids_$rand" class="kpids" value='' />
       <input type="hidden" name="target" id="target" value='$f_value' />
       <input type='submit' name='docompare' id='docompare_$rand' class='btn docompare' value='Compare' />
   </form>
   </div>
   <div>&nbsp;<br /></div>
EOM;
}

// Hook General Function
add_action('init',list_fieldtype);
add_action('init',list_textformat);
add_action('hook_format',format_text,10,2);
do_action('hook_format',$format,$text);

function list_fieldtype(){
    $field_type= array('textarea' => 'Textarea','radio' => 'Radio Select','date' => 'Date','group' => 'Field Group');
    return $field_type;
}

function list_textformat(){
    $text_format = array(
        '<span>' => 'Normal Text',
        '<h1>' => 'Heading 1',
        '<h2>' => 'Heading 2', 
        '<h3>' => 'Heading 3',
        '<h4>' => 'Heading 4',
        '<h5>' => 'Heading 5',
        '<h6>' => 'Heading 6',
        '<strong>' => 'Bold',
        '<em>' => 'Italic',
        '<u>' => 'Underline'
        );
    return $text_format;
}

function format_text($format,$text){
    $formatted = "";
    $text = stripslashes($text);
    //$text = htmlentities($text);
    $text_format = list_textformat();
    if(array_key_exists($format, $text_format)){
        $close = str_replace("<","</",$format);
        $formatted = $format.$text.$close;
        return $formatted;
    }else {
        return $text;
    }
    return false;
}


?>
