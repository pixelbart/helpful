(function ($) {

  "use strict";

  const HelpfulPlugin = {
    el: ".helpful",
    vote: "helpful_save_vote",
    feedback: "helpful_save_feedback",
    initPlugin: function () {
      const self = this;
  
      if (self.el.length < 1) return;

      $(document).on("click", ".helpful .helpful-controls button", function (e) {
        if (e.target !== e.currentTarget) {
          return;
        }

        var currentButton = $(this);
        var currentForm = $(currentButton).closest('.helpful');
        var ajaxData = {};

        $.extend(ajaxData, helpful.ajax_data);
        $.extend(ajaxData, $(currentButton).data());
        ajaxData.action = self.vote;

        self.ajaxRequest(ajaxData).done(function (response) {
          $(currentForm).find(".helpful-header").remove();
          $(currentForm).find(".helpful-controls").remove();
          $(currentForm).find(".helpful-footer").remove();
          $(currentForm).find(".helpful-content").html(response);
          self.feedbackForm(currentForm);
        });
      });
    },
    feedbackForm: function (currentForm) {
      var self = this;

      $(currentForm).find('.helpful-cancel').click(function (e) {
        e.preventDefault();
        var ajaxData = [
          { name: 'action', value: 'helpful_save_feedback' },
          { name: 'cancel', value: 1 },
          { name: 'type', value: $(currentForm).find('[name="type"]').val() },
          { name: '_wpnonce', value: $(currentForm).find('[name="_wpnonce"]').val() },
        ];

        self.ajaxRequest(ajaxData).done(function (response) {
          $(currentForm).find(".helpful-content").html(response);
        });
      });
      
      $(currentForm).on("submit", ".helpful-feedback-form", function (e) {
        e.preventDefault();
        var ajaxData = $(this).serializeArray();
        self.ajaxRequest(ajaxData).done(function (response) {
          $(currentForm).find(".helpful-content").html(response);
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

  $(function () {
    HelpfulPlugin.initPlugin();
  });

})(jQuery);