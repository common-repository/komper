jQuery(document).ready(function($) {
    $(".docompare").hide();
    
    $(".komperproduct").autocomplete({
        source: getdata_url+"komper.php?act=getdata",
        focus: function( event, ui ) {
            var activeid = $(document.activeElement).attr("name");
            $("#komperproduct_"+activeid ).val( ui.item.label );
            return false;
        },
        select: function( event, ui ) {
            var activeid = $(document.activeElement).attr("name");
            var kpids = $('#kpids_'+activeid).val().replace(" ", "");
            var jml = kpids.split(",");
            if(jml.length == 2){
                alert("Max 2 product to select!");
                //e.preventDefault();
                return false;
            }else{
                getproduct(ui.item,activeid);
            }
            $(this).val('');return false;
        }
    });
    var removeitem = function(pid,activeid){
        var pids_val = $("#kpids_"+activeid).val();
        var pids_cut = removeValue(pids_val,pid,",");
        $('#list_'+pid).remove();
        $("#kpids_"+activeid).val(pids_cut);
        return false;
    }
    window.removeitem = removeitem;
    
    function getproduct(item,activeid) {
        var pids_val = $("#kpids_"+activeid).val();
        var pids = "";
        $("#docompare_"+activeid).show();
        if(pids_val != ""){ 
            pids = pids_val+","+item.value;
        }else{pids = item.value;}
        $('#kprodlist_'+activeid).append("<li class='ui-widget' id='list_"+item.value+"'><span>"+item.label+"</span><a href='javascript:void();' onclick='return removeitem("+item.value+","+activeid+");'><span class='ui-icon ui-icon-circlesmall-close' style='display:inline-block'></span></a></li>");
        $('#kpids_'+activeid).val(pids);
    }
});

var removeValue = function(list, value, separator) {
  separator = separator || ",";
  var values = list.split(separator);
  for(var i = 0 ; i < values.length ; i++) {
    if(values[i] == value) {
      values.splice(i, 1);
      return values.join(separator);
    }
  }
  return list;
}