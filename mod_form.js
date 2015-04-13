$(function() {
	$("#id_updateitems").hide();
	$("#id_item").on('change', function() {
		$("#id_updateitems").click();
	});
	
	$('#id_category').on('change', function() {
		$("#id_item").html("");

		var id = $('#id_category').val();
		$.getJSON(M.cfg.wwwroot + "/mod/aspirelists/ajax/items.php?sesskey=" + M.cfg.sesskey + '&id=' + id, function(data) {
			$.each(data, function(k, v) {
				$("#id_item").append("<option value=\"" + k + "\">" + v + "</option>");
			});
		});
	});
});