<script type="text/javascript">
//<![CDATA[
$(document).ready(function() {
 //show only the frameset for selected gateway
 $('.sms_gateway_panel').hide();
 var val = $('#sms_gateway').val();
 $('#'+val).show();
 $('#sms_gateway').change(function() {
   $('.sms_gateway_panel').hide();
   var val = $('#sms_gateway').val();
   $('#'+val).show();
 });

 if(|PADM|) {
 $('.gatedata').tableDnD({
  dragClass: 'row1hover',
  onDrop: function(table, droprows) {
   var odd = true;
   var oddclass = 'row1';
   var evenclass = 'row2';
   var droprow = $(droprows)[0];
   $(table).find('tbody tr').each(function() {
    var name = odd ? oddclass : evenclass;
    if (this === droprow) {
     name = name+'hover';
    }
    $(this).removeClass().addClass(name);
    odd = !odd;
   });
  }
 }).find('tbody tr').removeAttr('onmouseover').removeAttr('onmouseout').mouseover(function() {
  var now = $(this).attr('class');
  $(this).attr('class', now+'hover');
 }).mouseout(function() {
  var now = $(this).attr('class');
  var to = now.indexOf('hover');
  $(this).attr('class', now.substring(0,to));
 });
 }
});

function row_selected(ev,btn) {
 var nm = btn.name,
  alias = nm.substr(0,nm.indexOf('~'));
//find checked boxes named like "m1_<alias>~<field>~sel"
 var list = document.querySelectorAll('input[name^="'+alias+'"]:checked'),
  c = list.length;
 if(c > 0) {
  var suffix = '~sel',
   sl = suffix.length;
  for (var i=0; i<c; i++) {
   nm = list[i].name;
   if(nm.indexOf(suffix,nm.length - sl) !== -1)
    return true;
  }
 }
 return false;
}
//]]>
</script>
