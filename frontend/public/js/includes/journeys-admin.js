jQuery(document).ready(function() {
	//enable datepicker on expiry date field
	jQuery("#date_expiry").datepicker({
		format: "yyyy-mm-dd",
		clearBtn: true,
		todayHighlight: true,
		autoclose: true,
		todayBtn: true
	}).attr("readonly", true);
});