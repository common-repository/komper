<?php

$pid = isset($_GET['pid']) ? $_GET['pid'] : "";
$addsubmit = isset($_POST['addsubmit']) ? $_POST['addsubmit'] : "";
$editsubmit = isset($_POST['editsubmit']) ? $_POST['editsubmit'] : "";

if($addsubmit != ""){
    $product_name = isset($_POST['product_name']) ? $_POST['product_name'] : "";
    $product_image = upload_img($_FILES);
    $wpdb->insert(KOMPER_TABLE_PRODUCT, 
            array('product_name' => $product_name, 
                'product_image' => $product_image
            ), array('%s','%s') 
    );
    $pid = $wpdb->insert_id;
    unset($_POST['product_name']);
    unset($_POST['addsubmit']);
    foreach($_POST as $key => $post){
        $wpdb->insert(KOMPER_TABLE_VALUE, 
            array('product_id' => $pid, 
                'field_id' => $key,
                'field_values' => $post
            ), array('%s','%s','%s','%s')
        );  
    }
    //wp_safe_redirect( '?page='.PAGE.'&act=insert'); exit; 
    echo '<META HTTP-EQUIV="Refresh" Content="0; URL=?page='.PAGE.'&act=insert">';
}

if($editsubmit != ""){
    
    $product_id = isset($_POST['pid']) ? $_POST['pid'] : "";
    $product_name = isset($_POST['product_name']) ? $_POST['product_name'] : "";
    
    $fileName = isset($_FILES['product_img']['name']) ? $_FILES['product_img']['name'] : "" ;
    if($fileName != ""){
        $product_image = upload_img($_FILES);
        $wpdb->update(KOMPER_TABLE_PRODUCT, 
                array('product_name' => $product_name, 
                    'product_image' => $product_image
                ), array( 'id' => $product_id ), 
                array('%s','%s'), array('%d') 
        );
    }else{
        $wpdb->update(KOMPER_TABLE_PRODUCT, 
                array('product_name' => $product_name
                ), array( 'id' => $product_id ), 
                array('%s'), array('%d') 
        );
    }
    unset($_POST['product_name']);
    unset($_POST['editsubmit']);
    unset($_POST['pid']);
    foreach($_POST as $key => $post){
        $ids = explode("|",$key);
        $pid = $ids[0];
        $fid = $ids[1];
        
        if($pid != ""){
            $wpdb->update(KOMPER_TABLE_VALUE, 
                array('field_values' => $post
                ), array('id' => $pid),
                array('%s'), array('%d')
            );  
        }else{
            $wpdb->insert(KOMPER_TABLE_VALUE, 
            array('product_id' => $product_id, 
                'field_id' => $fid,
                'field_values' => $post
            ), array('%s','%s','%s')
        );  
        }
    }
    echo '<META HTTP-EQUIV="Refresh" Content="0; URL=?page='.PAGE.'&act=insert">';
}
function generateform($fieldType,$fieldName,$fieldId,$field_type,$val = null){
    $form = "";
    $val = isset($val) ? $val : "";
    if(array_key_exists($fieldType,$field_type)){
        switch($fieldType){
            case "group":
                    $form = "<hr noshade>";
                    break;
            case "textarea":
                $form = "<textarea class='field span8' rows=3 name='".$fieldName."' id='".$fieldId."'>".$val."</textarea>";
                break;
            case "radio";
                if($val == "yes") $yes = "checked";else $yes = "";
                if($val == "no" OR $val == "") $no = "checked";else $no = "";
                $form = "<input type='radio' name='".$fieldName."' id='".$fieldId."_1' value='yes' $yes />Yes &nbsp;
                    <input type='radio' name='".$fieldName."' id='".$fieldId."_2' value='no' $no />No";
                break;
            case "date":
                echo '<script>
                    jQuery(function() {
                        jQuery("#date_'.$fieldId.'").datepicker({
                            changeMonth: true,
                            changeYear: true,
                            yearRange: "-50:+10",
                            dateFormat: "d MM yy"
                        });
                    });
                    </script>';
                $form = "<input type='text' name='".$fieldName."' id='date_".$fieldId."' value='".$val."'>";
            break;
        }
    }
    return $form;
}

function upload_img($files){
    
    $uploaddir = str_replace('wp-admin/options-general.php','wp-content/products/',$_SERVER['SCRIPT_FILENAME']);
    $product_image = "";
    $fileName = isset($files['product_img']['name']) ? $files['product_img']['name'] : "" ;
    if($fileName != ""){
        
        if(!is_dir($uploaddir)){
            mkdir($uploaddir,0755);
        }
	$fileSize = $_FILES['product_img']['size'];
	$fileError = $_FILES['product_img']['error'];
	if($fileSize > 0 || $fileError == 0){
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            $newfilename = md5($fileName.time()).".".$ext;
            $move = move_uploaded_file($_FILES['product_img']['tmp_name'], $uploaddir.$newfilename);
            if($move){
                $product_image = $newfilename;
                
                $thumbdir = $uploaddir."/thumb/";
                if(!is_dir($thumbdir)){
                    mkdir($thumbdir,0755);
                }
                $thumb = $thumbdir.$newfilename;
                resize($uploaddir.$newfilename,'200','200',$thumb);
            }
	}
    }
    return $product_image;
}

//RESIZE IMAGE
function resize($img, $w, $h, $newfilename) {
    if (!extension_loaded('gd') && !extension_loaded('gd2')) {
        trigger_error("GD is not loaded", E_USER_WARNING);
        return false;
    }
    $imgInfo = getimagesize($img);
        switch ($imgInfo[2]) {
        case 1: $im = imagecreatefromgif($img); break;
        case 2: $im = imagecreatefromjpeg($img);  break;
        case 3: $im = imagecreatefrompng($img); break;
        default:  trigger_error('Unsupported filetype!', E_USER_WARNING);  break;
    }
    if ($imgInfo[0] <= $w && $imgInfo[1] <= $h) {
        $nHeight = $imgInfo[1];
        $nWidth = $imgInfo[0];
    }else{
        if ($w/$imgInfo[0] > $h/$imgInfo[1]) {
            $nWidth = $w;
            $nHeight = $imgInfo[1]*($w/$imgInfo[0]);
            }else{
            $nWidth = $imgInfo[0]*($h/$imgInfo[1]);
            $nHeight = $h;
        }
    }
    $nWidth = round($nWidth);
    $nHeight = round($nHeight);

    $newImg = imagecreatetruecolor($nWidth, $nHeight);
    if(($imgInfo[2] == 1) OR ($imgInfo[2]==3)){
        imagealphablending($newImg, false);
        imagesavealpha($newImg,true);
        $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
        imagefilledrectangle($newImg, 0, 0, $nWidth, $nHeight, $transparent);
    }
    imagecopyresampled($newImg, $im, 0, 0, 0, 0, $nWidth, $nHeight, $imgInfo[0], $imgInfo[1]);
    switch ($imgInfo[2]) {
        case 1: imagegif($newImg,$newfilename); break;
        case 2: imagejpeg($newImg,$newfilename);  break;
        case 3: imagepng($newImg,$newfilename); break;
        default:  trigger_error('Failed resize image!', E_USER_WARNING);  break;
    }

    return $newfilename;
}

$fields = $wpdb->get_results("select * from ".KOMPER_TABLE_NAME." order by field_order asc");

wp_register_style( 'jqueryStyle', plugins_url('assets/css/smoothness/jquery-ui-1.9.0.custom.min.css', __FILE__) );
wp_enqueue_style('jqueryStyle');
?>
<script>
jQuery(document).ready(function($) {
    $('#checkall').click(function() {
        $(this).closest('table').find(':checkbox').prop('checked', this.checked);
    });
    
    //Delete Field Function
    $("#btndelfield").click(function(e) {
        var selected = new Array();
        $('input[type=checkbox]').each(function () {
            if($(this).is(':checked')){
                selected.push($(this).attr('value'));
            }
        });
        if(selected != ""){
            if(confirm("Delete this fields?")){
                var pids = selected;
                var d_data = {
                        action: 'del_products',
                        pids: pids
                };
                $.post(ajaxurl, d_data, function(resp){
                    location.href='?page=<?php echo PAGE; ?>&act=insert';
                });
            }
        }else{
            alert("Please select at least one field to delete.");
        }
        return false;
    });
});
</script>
    <div id="menu-management">
        <div class="nav-tabs-wrapper">
            <div class="nav-tabs">
                <a href="?page=<?php echo PAGE; ?>" class="nav-tab hide-if-no-js">Field List</a>
                <a href="?page=<?php echo PAGE; ?>&act=insert" class="nav-tab nav-tab-active">Product List</a>
            </div>
        </div>
        <div class="menu-edit">
            <div id="nav-menu-header">
                    <div id="submitpost" class="submitbox">
                        <div class="major-publishing-actions">
                            <div class="publishing-action">
                                <h3><span>Product List</span></h3>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="post-body">
                    <div id="post-body-content">
                        <?php if($do == "new"){  ?>
                        <div id="menu-instructions" class="post-body-plain" style="padding:10px;">
                            <form name="newproduct" class="form-horizontal" onsubmit="return true;" action="?page=<?php echo PAGE;?>&act=insert" method="POST" accept-charset="utf-8" enctype="multipart/form-data">
                              <div class="control-group">
                                <label class="control-label" for="product_name">Product Name</label>
                                <div class="controls">
                                  <input type="text" id="product_name" name="product_name">
                                </div>
                              </div>
                              <div class="control-group">
                                <label class="control-label" for="product_image">Product Image</label>
                                <div class="controls">
                                  <input type="file" name="product_img" id="product_img">
                                </div>
                              </div>
                              <?php foreach($fields as $row){ ?>
                              <div class="control-group">
                                <label class="control-label" for="<?php echo $row->id;?>"><?php echo $row->field_name;?></label>
                                <div class="controls">
                                  <?php echo generateform($row->field_type,$row->id,$row->id,$field_type); ?>
                                </div>
                              </div>
                              <?php } ?>
                              <div class="control-group">
                                <div class="controls">
                                  <input type="submit" name="addsubmit" id="addsubmit" class="button button-primary" value="Submit" />
                                  <input type="button" name="btncancel" id="btncancel" class="button button-primary" value="Cancel" onclick="window.location.href = '?page=<?php echo PAGE; ?>&act=insert';" />
                                </div>
                              </div>
                            </form>
                        </div>
                        <?php } elseif($do == "edit") { 
                        $fields = $wpdb->get_results("SELECT a.*,b.field_values, b.id as pid FROM ".KOMPER_TABLE_NAME." a left join ".KOMPER_TABLE_VALUE." b on a.id = b.field_id and b.product_id = '".$pid."' order by a.field_order asc");
                        $prod = $wpdb->get_row("select * from ".KOMPER_TABLE_PRODUCT." where id = '".$pid."' "); 
                        ?>
                        <div id="menu-instructions" class="post-body-plain" style="padding:10px;">
                            <form name="newproduct" class="form-horizontal" onsubmit="return ture;" action="?page=<?php echo PAGE;?>&act=insert&do=edit&pid=<?php echo $pid;?>" method="POST" accept-charset="utf-8" enctype="multipart/form-data">
                              <input type="hidden" id="pid" name="pid" value="<?php echo $prod->id; ?>" />
                                <div class="control-group">
                                <label class="control-label" for="product_name">Product Name</label>
                                <div class="controls">
                                  <input type="text" id="product_name" name="product_name" value="<?php echo $prod->product_name; ?>" />
                                </div>
                              </div>
                              <div class="control-group">
                                <label class="control-label" for="product_img">Product Image</label>
                                <div class="controls">
                                  <input type="file" name="product_img" id="product_img">
                                </div>
                              </div>
                              <?php foreach($fields as $row){ ?>
                              <div class="control-group">
                                <label class="control-label" for="<?php echo $row->pid;?>"><?php echo $row->field_name;?></label>
                                <div class="controls">
                                  <?php echo generateform($row->field_type,$row->pid."|".$row->id,$row->id,$field_type, $row->field_values); ?>
                                </div>
                              </div>
                              <?php } ?>
                              <div class="control-group">
                                <div class="controls">
                                  <input type="submit" name="editsubmit" id="editsubmit" class="button button-primary" value="Submit" />
                                  <input type="button" name="btncancel" id="btncancel" class="button button-primary" value="Cancel" onclick="window.location.href = '?page=<?php echo PAGE; ?>&act=insert';" />
                                </div>
                              </div>
                            </form>
                        </div>
                        <?php } else { 
                            $perpage = 20;
                            $page = isset($_GET['p']) ? $_GET['p'] : "1";
                            
                            $sql = "select * from ".KOMPER_TABLE_PRODUCT." order by id desc";
                            $tot_products = count($wpdb->get_results($sql));    
                            $pages = ceil($tot_products/$perpage);
                            
                            $offset = ($page - 1) * $perpage;
                            
                            $sql2 = $sql." limit $offset,$perpage";
                            $products = $wpdb->get_results($sql2);
                            
                        ?>
                        <div id="menu-instructions" class="post-body-plain" style="padding:10px;width:550px;">
                            <div class="row-fluid">
                                <div class="span6">
                                    <a class="btn btn-warning" name="btndelfield" id="btndelfield">Delete Selected</a>
                                </div>
                                <div class="span6" align="right">
                                    <a href="?page=<?php echo PAGE; ?>&act=insert&do=new" class="btn btn-primary">Add New Product</a>
                                </div>
                            </div>
                            
                            <div>
                            <table class="table table-striped table-bordered">
                              <thead>
                                <tr>
                                    <th><input type="checkbox" class="checkbox checkall" id="checkall" name="checkall" /></th>
                                    <th>No</th>
                                    <th>Product Name</th>
                                    <th>Action</th>
                                </tr>
                              </thead>
                              <tbody>
                                <?php $i=0; 
                                if(count($products) > 0){
                                foreach($products as $row) { $i++; ?>
                                <tr>
                                    <td><input type="checkbox" class="checkbox" name="delcheck" id="delcheck" value="<?php echo $row->id; ?>" /></td>
                                    <td width="5%"><?php echo $i; ?></td>
                                    <td width="70%">
                                        <a href='<?php echo get_site_url()."/komper.php?kpids=".$row->id."&target=noheader"; ?>' data-toggle="modal" data-target="#sampleOutput">
                                            <?php echo $row->product_name;?>
                                        </a>
                                    </td>
                                    <td width="20%">
                                        <a href="?page=<?php echo PAGE; ?>&act=insert&do=edit&pid=<?php echo $row->id; ?>" class="btn btn-link" id="editbtn"><i class="icon-edit"></i> Edit</a>
                                    </td>
                                </tr>
                                <?php }
                                } else{ ?>
                                   <tr>
                                       <td colspan="4">No Products</td>
                                   </tr>
                                <? }?>
                              </tbody>
                            </table>
                            </div>
                            <div class="pagination pagination-centered">
                                <ul>
                                    <?php
                                    if($page == 1) { $state = "class=\"disabled\""; $prev = "#";}else {$state = ""; $prev = "?page=".PAGE."&act=insert&p=".($page - 1);}
                                    echo "<li ".$state."><a href='".$prev."'>Prev</a></li>";
                                    for($i=1; $i<=$pages; $i++){
                                        if($page == $i) $state = "class=\"active\""; else $state = "";
                                        echo '<li '.$state.'><a href="?page='.PAGE.'&act=insert&p='.$i.'">'.$i.'</a></li>';
                                    }
                                    if($page == ($i-1)) { $state = "class=\"disabled\""; $next = "#";}else {$state = ""; $next = "?page=".PAGE."&act=insert&p=".($page + 1);}
                                    echo "<li ".$state."><a href='".$next."'>Next</a></li>";
                                    ?>
                                </ul>
                            </div>
                            <div align="right">
                                <span class="label"><?php echo $tot_products;?> Products</span>
                            </div>
                        </div>
                        
                        <?php } ?>
                    </div><!-- /#post-body-content -->
                    
                </div><!-- /#post-body -->
                <div id="nav-menu-footer">
                    <div class="major-publishing-actions">
                    <div class="publishing-action">
                        &nbsp;
                    </div>
                    </div>
                </div><!-- /#nav-menu-footer -->
        </div><!-- /.menu-edit -->
        <div class="alert alert-info">
            <h4>You are using Komper free version</h4>
            With free version you can create up to 15 fields, and compare 2 products. <br />
            Upgrade to Pro version to get unlimited fields, unlimited products to compare, insert comparation table to your posts or pages, and more.. <br />
            Get it here: <a href="http://www.vnetware.com/" target="_blank">http://www.vnetware.com/</a>
        </div>
        <!-- MODAL SAMPLE OUTPUT -->
<div class="modal hide" id="sampleOutput" tabindex="-1" role="dialog" aria-labelledby="outputLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="outputLabel">Sample Output</h3>
  </div>
  <div class="modal-body">
    <p>loading..</p>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
  </div>
</div>
    <!-- END MODAL SAMPLE OUTPUT -->    
</div>
