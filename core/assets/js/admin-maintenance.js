(function ($) {

  "use strict";

  var HelpfulMaintenance = {
    el: ".helpful_maintenance",
    loader: "<div class=\"helpful_loader\"><i class=\"dashicons dashicons-update\"></i></div>",
    init: function () {
      var self = this;

      $(self.el).on("click", "button", function (e) {
        if (e.target !== e.currentTarget) {
          return;
        }
        
        self.performMaintenance();
      });
    },
    performMaintenance: function () {
      var self = this;
      var container = $(self.el).find(".helpful_response");

      $(container).html(self.loader);
      $(container).find("pre").remove();

      self.ajaxRequest(helpful_maintenance.data).done(function (response) {

        var itemContainer = $("<pre/>");
        
        $(container).removeAttr("hidden");

        $.each(response, function (i, v) {
          $(itemContainer).append(v + "<br>");
        });

        $(container).html(itemContainer);
      });
    },
    ajaxRequest: function (data) {
      return $.ajax({
        url: helpful_maintenance.ajax_url,
        data: data,
        method: "POST",
      });
    },
  };

  $(function () {
    HelpfulMaintenance.init();
  });

})(jQuery);