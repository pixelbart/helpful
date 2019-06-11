(function ($) {

  "use strict";

  const HelpfulPlugin = {
    el: ".helpful",
    vote: "helpful_save_vote",
    feedback: "helpful_save_feedback",

    initPlugin: function () {
      const self = this;

      if (self.el.length < 1) {
        return;
      }

      $(document).on("click", ".helpful .helpful-controls button", function (e) {
        if (e.target !== e.currentTarget) {
          return;
        }

        var ajaxData = {};

        $.extend(ajaxData, helpful.ajax_data);
        $.extend(ajaxData, $(this).data());
        ajaxData.action = self.vote;

        self.ajaxRequest(ajaxData).done(function (response) {
          $(self.el).find(".helpful-header").remove();
          $(self.el).find(".helpful-controls").remove();
          $(self.el).find(".helpful-footer").remove();
          $(self.el).find(".helpful-content").html(response);
          self.feedbackForm();
        });
      });
    },

    feedbackForm: function () {
      var self = this;
      
      $(document).on("submit", ".helpful .helpful-feedback-form", function (e) {
        e.preventDefault();
        var ajaxData = $(this).serializeArray();
        self.ajaxRequest(ajaxData).done(function (response) {
          $(self.el).find(".helpful-content").html(response);
        });
      });
    },

    ajaxRequest: function (data) {
      return $.ajax({
        url: helpful.ajax_url,
        data: data,
        method: "POST",
      });
    },
  };

  HelpfulPlugin.initPlugin();

})(jQuery);