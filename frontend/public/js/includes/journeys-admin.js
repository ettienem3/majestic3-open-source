jQuery(document).ready(function() {
	//enable datepicker on expiry date field
	jQuery("#date_expiry").datepicker({
		format: jQuery("#date_expiry").attr("data-info-date-format"),
		clearBtn: true,
		todayHighlight: true,
		autoclose: true,
		todayBtn: true
	}).attr("readonly", true);
});