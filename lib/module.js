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

var st = null, cl = null;
function cgsms_entertext_onchange() {
 if(st == null) st = document.getElementById('cgsms_entertext_smstext');
 var charsleft = |MAXSMSCHARS| - st.value.length - 1;
 if(charsleft < 0) return false;
 if(cl == null) cl = document.getElementById('cgsms_entertext_charsleft');
 cl.innerHTML = charsleft;
 return true;
}

function row_selected(ev,btn) {
 //TODO count selected checkboxes
 //btn like input#m1_<alias>.delete.cms_submit attribute value = "Delete"
 return true;
}
//]]>
</script>
