(function($) {
    "use strict";

    const HelpfulAdminLog = {
        container: "#helpful-table-log",
        init: function() {
            const self = this;

            if ($(self.container).length < 1) {
                return;
            }

            var options = self.tableOptions();

            $.extend(options, {
                "dom": "<\"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-tl ui-corner-tr\"Blfr>" +
                    "t" +
                    "<\"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-bl ui-corner-br\"ip>",
                "ajax": {
                    "url": helpful_admin_log.ajax_url,
                    "data": function(d) {
                        d._wpnonce = helpful_admin_log.nonce;
                        d.action = "helpful_get_log_data";
                    },
                },
                "language": helpful_admin_log.language,
                "columns": [
                    { "data": "row_id", "visible": false },
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
                            "sort": "time.timestamp",
                        },
                    },
                ],
                "select": true,
                "buttons": [{
                    "extend": "selected",
                    "text": helpful_admin_log.translations.delete,
                    action: function(e, dt, node, config) {
                        let rows = dt.rows({ selected: true });
                        let remove = [];

                        $.each(rows.data(), function(index, row) {
                            remove.push(row.row_id);
                        });

                        let alertMessage = "";
                        let translation = helpful_admin_log.translations.delete_confirm;

                        if (remove.length > 0) {

                            if (1 === remove.length) {
                                alertMessage = translation.singular;
                            } else {
                                alertMessage = translation.plural.replace("%d", remove.length);
                            }

                            if (confirm(alertMessage)) {
                                let request = self.ajaxRequest({
                                    "_wpnonce": helpful_admin_log.nonces.delete_rows,
                                    "action": "helpful_delete_rows",
                                    "rows": remove,
                                });

                                request.done(function(response) {
                                    if (response.success) {
                                        alert(response.data);
                                        rows.remove().draw(false);
                                    } else {
                                        alert(response.data);
                                    }
                                });
                            }
                        }
                    }
                }, {
                    "text": helpful_admin_log.translations.export,
                    action: function(e, dt, node, config) {
                        let rows = dt.rows({ selected: true });
                        let exportItems = [];

                        $.each(rows.data(), function(index, row) {
                            exportItems.push(row.row_id);
                        });


                        if (exportItems.length > 0) {
                            let request = self.ajaxRequest({
                                "_wpnonce": helpful_admin_log.nonces.export_rows,
                                "action": "helpful_export_rows",
                                "rows": exportItems,
                            });

                            request.done(function(response) {
                                if ("success" === response.status) {
                                    window.location.href = response.file;
                                } else {
                                    alert(response.message);
                                }
                            });
                        } else {
                            let request = self.ajaxRequest({
                                "_wpnonce": helpful_admin_log.nonces.export_rows,
                                "action": "helpful_export_rows",
                                "rows": "all",
                            });

                            request.done(function(response) {
                                if ("success" === response.status) {
                                    window.location.href = response.file;
                                } else {
                                    alert(response.message);
                                }
                            });
                        }
                    }
                }],
            });

            var table = $(self.container).DataTable(options);

            table.column("6").order("desc").draw();
        },
        tableOptions: function() {
            return {
                "scrollY": 275,
                "deferRender": true,
                "scroller": true,
                "responsive": true,
                "processing": false,
                "serverSide": false,
            };
        },
        ajaxRequest: function(data) {
            return $.ajax({
                url: helpful_admin_log.ajax_url,
                data: data,
                method: "GET",
            });
        },
    };

    $(function() {
        HelpfulAdminLog.init();
    });
})(jQuery);