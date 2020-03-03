(function ($) {

  "use strict";
  
  const HelpfulAdminFeedback = {
    loader: "<div class=\"helpful_loader\"><i class=\"dashicons dashicons-update\"></i></div>",
    initClass: function () {
      this.getFeedbackItems();
      this.deleteFeedbackItem();
      this.changeFeedbackFilter();
      this.exportFeedback();
    },
    getFeedbackItems: function (filter = "all") {
      const self = this;
      const container = $(".helpful-admin-feedback");

      let request;
      let data;

      $(container).html(self.loader);
      
      data = {
        action: "helpful_admin_feedback_items",
        _wpnonce: helpful_admin_feedback.nonce,
        filter: filter,
      };

      request = self.ajaxRequest(data);

      $.when(request).done(function (items) {
        $(container).html(items);
      });
    },
    changeFeedbackFilter: function () {
      const self = this;
      const filter = $(".helpful-admin-filter").find("select");

      $(filter).on("change", function () {
        self.getFeedbackItems($(filter).val());
      });
    },
    deleteFeedbackItem: function () {
      const self = this;

      let request;
      let data;
      let button;

      $(document).on("click", ".helpful-delete-item", function (e) {

        button = $(this);

        data = {
          action: "helpful_remove_feedback",
          _wpnonce: helpful_admin_feedback.nonce,
          feedback_id: $(button).data("id"),
        };

        request = self.ajaxRequest(data);
        $(button).closest("article").fadeOut();
      });
    },
    exportFeedback: function () {
      const self = this;

      if ($('.helpful-export').length < 1) {
        return;
      }

      $('.helpful-export').unbind('click').on('click', function (e) {
        e.preventDefault();

        let current_button = $(this);
        let ajax_data = {
          action: "helpful_export_feedback",
          _wpnonce: helpful_admin_feedback.nonce,
          type: $(current_button).data('type'),
        };
  
        let request = self.ajaxRequest(ajax_data);

        request.done(function (response) {
          if ('success' === response.status) {
            window.location.href = response.file;
          }
        });
      });
    },
    ajaxRequest: function (data) {
      return $.ajax({
        url: helpful_admin_feedback.ajax_url,
        data: data,
        method: "POST",
      });
    },
  };

  $(function () {
    HelpfulAdminFeedback.initClass();
  });

})(jQuery);