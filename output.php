<?php
global $wpdb;

$act = isset($_GET['act']) ? $_GET['act'] : "";
$target = isset($_GET['target']) ? $_GET['target'] : "";

if($act == "getdata"){
    $term = isset($_GET['term']) ? $_GET['term'] : "";
    $sql = "select * from ".KOMPER_TABLE_PRODUCT." where product_name like '%' %s '%' ";
    $sql2 = "select id as value,product_name as label from ".KOMPER_TABLE_PRODUCT." where product_name like '%".$term."%' ";
    
    $data = $wpdb->get_results($sql2);
    echo json_encode($data);
    die();
}

function show_output($pids){
    global $wpdb;
    $res = "";
    $ids = array_unique(explode(",",trim($pids)));
    $p_jml = count($ids);
    if($p_jml > 1){
        $ids = array($ids[0],$ids[1]);
    }
    //reset $PIDS
    $pids = implode(',', array_map('mysql_real_escape_string', $ids));
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
?>

<!DOCTYPE html>
<!--[if IE 8]>
<html xmlns="http://www.w3.org/1999/xhtml" class="ie8 wp-toolbar"  dir="ltr" lang="en-US">
<![endif]-->
<!--[if !(IE 8) ]><!-->
<html xmlns="http://www.w3.org/1999/xhtml" class="wp-toolbar"  dir="ltr" lang="en-US">
<!--<![endif]-->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<?php
$pids = isset($_GET['kpids']) ? $_GET['kpids'] : "";
$pids = html_entity_decode($pids, ENT_NOQUOTES, 'UTF-8');

if($target == "noheader"){
    wp_head();  
}else{
    get_header();
}
?>
<div id="komper_output">
    <?php echo show_output($pids);  ?>
</div>

<?php
if($target == ""){
    get_footer();
}
?>