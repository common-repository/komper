<?php
require_once("config.php");
global $wpdb;

$act = isset($_GET['act']) ? $_GET['act'] : "";
$do = isset($_GET['do']) ? $_GET['do'] : "";

$text_format = list_textformat();
$field_type = list_fieldtype();

$datafields = $wpdb->get_results("select * from ".KOMPER_TABLE_NAME." order by field_order asc");
$tot_field = count($datafields);
// jQuery
wp_enqueue_script( 'jquery' );
wp_enqueue_script( 'jquery-ui-draggable' );
wp_enqueue_script( 'jquery-ui-droppable' );
wp_enqueue_script( 'jquery-ui-sortable' );
wp_enqueue_script( 'jquery-ui-datepicker' );

wp_enqueue_style( 'komperStyleSheet' );
//wp_enqueue_style('jqueryStyle');
wp_enqueue_style('cssbootstrap');
// Nav Menu functions
wp_enqueue_script( 'nav-menu' );

// Metaboxes
wp_enqueue_script( 'common' );
wp_enqueue_script( 'wp-lists' );
wp_enqueue_script( 'postbox' );

echo '<div id="icon-options-general" class="icon32"><br /></div>  ';
echo "<h2>" . __( 'Komper Admin Settings', 'oscimp_trdom' ) . "</h2>";

if($act == "insert"){
    include_once("insert_data.php");
}elseif($act == "output"){
    include_once("output.php");
}else{
?>
    <script>
        var field_type = <?php echo json_encode($field_type);?>;
        var text_format = <?php echo json_encode($text_format);?>;
        var totfield = <?php echo $tot_field; ?>;
        
    jQuery(document).ready(function($) {
        if(totfield >= 15){
            $("#divaddfield").hide();
        }
        $("#flashmsg").hide();
        $( "#kfieldlist tbody" ).sortable({
                helper: function (e, ui) {  
                ui.children().each(function () {  
                    $(this).width($(this).width());  
                });  
                return ui;  
            },  
            scroll: true,
            forcePlaceholderSize:true, 
            'start': function (event, ui) {
                ui.placeholder.html('<!--[if IE]><td>&nbsp;</td><![endif]-->');
            },
            update: function(sorted){
                var order = $(this).sortable("serialize") + '&action=sort_field'; 
                
                $.post(ajaxurl, order, function(theResponse){
                        var messages = "Field position updated!";
                        $("#flashmsg").html(messages);
                        $("#flashmsg").fadeIn("slow");
                        setTimeout(function(){
                            $("#flashmsg").fadeOut("slow", function () {}); 
                        }, 2000);
                }); 
            }
        });
        
        $("#add_kompfield").click(function(){
            var kfname = $("#kompfieldname").val();
            var kftype = $("#kompfieldtype").val();
            var kfformat = $("#komptextformat").val();
            icount = $("#icount").val();
            
            if(check_totfields(icount)){
                var data = {
                        action: 'add_field',
                        fieldname: kfname,
                        fieldtype: kftype,
                        fieldformat: kfformat,
                        icount: icount
                };
                var nextcount = parseInt(icount)+1;
                $.post(ajaxurl, data, function(resp){
                    var edittd = '<a href="javascript:void(0);" class="btn btn-link" id="editbtn" onclick="doedit('+resp+','+kfname+','+kftype+','+kfformat+');"><i class="icon-edit"></i> Edit</a>';
                    var newfield = $('<tr id="kompfield_'+resp+'"><td><input type="checkbox" id="check_'+nextcount+'" name="check[]" /></td><td>'+kfname+'</td><td>'+field_type[kftype]+'</td><td>'+text_format[kfformat]+'</td><td align="center">'+edittd+'</td></tr>');
                    newfield.appendTo("#kfieldlist").slideDown('normal');
                    
                    $("#icount").val(nextcount);
                    var response = "New field added! ";
                    $("#flashmsg").html(response);
                    $("#flashmsg").fadeIn("slow");
                    setTimeout(function(){
                        $("#flashmsg").fadeOut("slow", function () {}); 
                    }, 2000);
                    $("#kompfieldname").val('');
                    $("#kompfieldtype").val('');
                    if(nextcount >= 10){
                        $("#divaddfield").hide();
                    }
                }); 
            }
        });
        
        $('#checkall').click(function() {
            $(this).closest('table').find(':checkbox').prop('checked', this.checked);
        });
        
        $('#submitedit').click(function() {
            var efname = $("#e_kompfieldname").val();
            var eftype = $("#e_kompfieldtype").val();
            var efformat = $("#e_komptextformat").val();
            var efid = $("#e_kompfieldid").val();
             var e_data = {
                    action: 'edit_field',
                    e_fieldname: efname,
                    e_fieldtype: eftype,
                    e_fieldformat: efformat,
                    e_fieldid: efid
            };
            $.post(ajaxurl, e_data, function(resp){
                location.reload();
            });
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
                    var fids = selected;
                    var d_data = {
                            action: 'del_fields',
                            fids: fids
                    };
                    $.post(ajaxurl, d_data, function(resp){
                        location.reload();
                    });
                }
            }else{
                alert("Please select at least one field to delete.");
            }
            return false;
        });
        function check_totfields(icount){
            if(icount >= 15){
                $("#infomsgerror").text('You have reached maximum amount of fields!');
                $("#divmsgerror").show();
                $("#divaddfield").hide();
                return false;
            }else {
                $("#divmsgerror").hide();
                $("#divaddfield").show();
                return true;
            }
        }
    });
    function doedit(fid,fname,ftype,fformat){
        jQuery("#e_kompfieldname").val(fname);
        jQuery("#e_kompfieldtype").val(ftype);
        jQuery("#e_komptextformat").val(fformat);
        jQuery("#e_kompfieldid").val(fid);
        jQuery("#modalEditField").modal();
    }
    </script>


    <div id="menu-management">
        <div class="nav-tabs-wrapper">
            <div class="nav-tabs">
                <span class="nav-tab nav-tab-active">Field List</span>
                <a href="?page=<?php echo PAGE; ?>&act=insert" class="nav-tab hide-if-no-js">Product List</a>
            </div>
        </div>
        <div class="menu-edit">
            <div id="nav-menu-header">
                    <div id="submitpost" class="submitbox">
                        <div class="major-publishing-actions">
                            <div class="publishing-action">
                                <h3><span>Product Field List</span></h3>
                            </div><!-- END .publishing-action -->
                        </div><!-- END .major-publishing-actions -->
                    </div><!-- END #submitpost .submitbox -->
                </div><!-- END #nav-menu-header -->
                <div id="post-body">
                    <div id="post-body-content">

                        <div id="menu-instructions" class="post-body-plain" style="padding:10px;">
                            <p>
                            <div id="divmsgerror" class="alert alert-error hide">
                                <button type="button" class="close" data-dismiss="alert">×</button>
                                <span id="infomsgerror">loading..</span>
                            </div>
                            <div id="divaddfield">
                                <h3>Create New Field</h3>
                                <form onsubmit="return false;" class="form-horizontal">
                                <div class="control-group">
                                    <label class="control-label" for="kompfieldname">Field Name:</label>
                                    <div class="controls">
                                        <input type="text" name="kompfieldname" id="kompfieldname" />
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" for="kompfieldtype">Field Type</label>
                                    <div class="controls">
                                        <select name="kompfieldtype" id="kompfieldtype">
                                                <?php foreach($field_type as $key => $ftype){ ?>
                                                <option value="<?php echo $key; ?>"><?php echo $ftype;?></option>
                                                <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label" for="komptextformat">Field Format</label>
                                    <div class="controls">
                                        <select name="komptextformat" id="komptextformat">
                                                <?php foreach($text_format as $key => $tformat){ ?>
                                                <option value="<?php echo $key; ?>"><?php echo $tformat;?></option>
                                                <?php } ?>
                                        </select>&nbsp;
                                        <input type="submit" name="save_menu" id="add_kompfield" class="button-primary menu-save" value="Submit"  />
                                        <span id="postresult">
                                            <span id="flashmsg" class="flashmsg"></span>
                                        </span>
                                    </div>
                                </div>
                                </form> 
                            </div>
                            </p>
                            <div style="width:500px;">
                                <input type="button" class="btn btn-warning" name="btndelfield" id="btndelfield" value="Delete" />
                                <table class="table table-hover table-bordered" id="kfieldlist" style="cursor: pointer;">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" class="checkbox checkall" id="checkall" name="checkall" /></th>
                                            <th>Field Name</th>
                                            <th>Field Type</th>
                                            <th>Field Format</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php $i = 0; foreach($datafields as $row){ $i++; ?>
                                        <tr id="kompfield_<?php echo $row->id; ?>" <?php if($row->field_type == 'group') echo ' class=\'success\'';?>>
                                            <td><input type="checkbox" class="checkbox" value="<?php echo $row->id; ?>" id="check_<?php echo $i;?>" name="check[]" /></td>
                                            <td><?php echo $row->field_name;?></td>
                                            <td><?php echo $field_type[$row->field_type];?></td>
                                            <td><?php echo $text_format[$row->field_format];?></td>
                                            <td align="center"><a href="javascript:void(0);" class="btn btn-link" id="editbtn" onclick="doedit(<?php echo $row->id?>,'<?php echo $row->field_name;?>','<?php echo $row->field_type;?>','<?php echo $row->field_format;?>');"><i class="icon-edit"></i> Edit</a></td>
                                        </tr>
                                    <? }?>
                                    </tbody>
                                </table>
                                <input type="hidden" name="icount" id="icount" value="<?php echo $i; ?>" />
                            </div>
                        </div>
                        
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
      
    <div class="modal hide" id="modalEditField" tabindex="-1" role="dialog" aria-labelledby="editField" aria-hidden="true">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <h3 id="editField">Edit Field</h3>
      </div>
      <div class="modal-body">
        <p id="displayformedit">
        <form onsubmit="return false;" class="form-horizontal">
        <div class="control-group">
            <label class="control-label" for="e_kompfieldname">Field Name:</label>
            <div class="controls">
                <input type="text" name="e_kompfieldname" id="e_kompfieldname" />
                <input type="hidden" name="e_kompfieldid" id="e_kompfieldid" />
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="e_kompfieldtype">Field Type</label>
            <div class="controls">
                <select name="e_kompfieldtype" id="e_kompfieldtype">
                        <?php foreach($field_type as $key => $ftype){ ?>
                        <option value="<?php echo $key; ?>"><?php echo $ftype;?></option>
                        <?php } ?>
                </select>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="e_komptextformat">Field Format</label>
            <div class="controls">
                <select name="e_komptextformat" id="e_komptextformat">
                        <?php foreach($text_format as $key => $fformat){ ?>
                        <option value="<?php echo $key; ?>"><?php echo $fformat;?></option>
                        <?php } ?>
                </select>
            </div>
        </div>
        </form> 
        </p>
      </div>
      <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        <button name="submitedit" id="submitedit" class="btn btn-primary">Save changes</button>
      </div>
    </div>      
</div>
<? } ?>