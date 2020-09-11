(function ($) {
	"use strict";
	
	const HelpfulAdminLog = {
		container: "#helpful-table-log",
		init: function () {
			const self = this;
			
			if ($(self.container).length < 1) {
				return;
			}
			
			var options = self.tableOptions();
			
			$.extend(options, {
				"ajax": {
					"url": helpful_admin_log.ajax_url,
					"data": function (d) {
						d._wpnonce = helpful_admin_log.nonce;
						d.action = "helpful_get_log_data";
					},
				},
				"language": helpful_admin_log.language,
				"columns": [
					{ "data": "post_id" },
					{ "data": "post_title" },
					{ "data": "pro" },
					{ "data": "contra" },
					{ "data": "user" },
					{
						"data": {
							"_": "time",
							"filter": "time.display",
							"display": "time.display",
							"sort" : "time.timestamp",
						},
					},
				],
			});
			
			var table = $(self.container).DataTable(options);
			
			table.column("5").order("desc").draw();
		},
		tableOptions: function () {
			return {
				"scrollY": 275,
				"deferRender": true,
				"scroller": true,
				"responsive": true,
				"processing": false,
				"serverSide": false,
			};
		},
		ajaxRequest: function (data) {
			return $.ajax({
				url: helpful_admin_log.ajax_url,
				data: data,
				method: "GET",
			});
		},
	};
	
	$(function () {
		HelpfulAdminLog.init();
	});
})(jQuery);