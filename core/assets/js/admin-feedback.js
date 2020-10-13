(function ($) {

  "use strict";
  
  const HelpfulAdminFeedback = {
    loader: "<div class=\"helpful_loader\"><i class=\"dashicons dashicons-update\"></i></div>",
    initClass: function () {
      this.resetFeedback();
      this.deleteFeedbackItem();
      this.changeFeedbackFilter();
      this.exportFeedback();
    },
    resetFeedback: function () {
      const self = this;
      const filter_form = $(".helpful-admin-filter");

      if ($("[name='post_id']").length) {
        $(".helpful-reset").show();
      }

      $(".helpful-reset").on("click", function (e) {
        e.preventDefault();
  
        $(this).hide();
        $("[name='post_id']").remove();

        let ajax_data = $(filter_form).serializeArray();
  
        self.getFeedackItems(ajax_data);
      });
    },
    getFeedackItems: function (ajax_data) {
      const self = this;
      const filter_form = $(".helpful-admin-filter");
      const container = $(".helpful-admin-feedback");

      let request;
      let data;

      $(container).html(self.loader);

      request = self.ajaxRequest(ajax_data);

      request.done(function (response) {
        $(container).html(response);

        $(container).find("[data-page]").unbind().click(function (e) {
          let page = $(this).data("page");
          $(filter_form).find("[name='paginate']").val(page);
          $(filter_form).change();
        });
      });      
    },
    changeFeedbackFilter: function () {
      const self = this;
      const filter_form = $(".helpful-admin-filter");

      let ajax_data = $(filter_form).serializeArray();
      self.getFeedackItems(ajax_data);

      $(filter_form).find("[name='filter']").on("change", function () {
        $(filter_form).find("[name='paginate']").val(1);
      });

      $(filter_form).on("change", function (e) {
        e.preventDefault();
        let ajax_data = $(this).serializeArray();
        self.getFeedackItems(ajax_data);
        return false;
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

      if ($(".helpful-export").length < 1) {
        return;
      }

      $(".helpful-export").unbind("click").on("click", function (e) {
        e.preventDefault();

        let current_button = $(this);
        let ajax_data = {
          action: "helpful_export_feedback",
          _wpnonce: helpful_admin_feedback.nonce,
          type: $(current_button).data("type"),
        };
  
        let request = self.ajaxRequest(ajax_data);

        request.done(function (response) {
          if ("success" === response.status) {
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