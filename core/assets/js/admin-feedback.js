(function ($) {
  
  const HelpfulAdminFeedback = {
    loader: "<div class=\"helpful_loader\"><i class=\"dashicons dashicons-update\"></i></div>",
    initClass: function () {
      this.getFeedbackItems();
      this.deleteFeedbackItem();
      this.changeFeedbackFilter();
    },
    getFeedbackItems: function (filter = "all") {
      const self = this;
      const container = $(".helpful-admin-feedback");

      var request;
      var data;

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

      var request;
      var data;
      var button;

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