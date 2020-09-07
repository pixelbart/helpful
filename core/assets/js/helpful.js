(function ($) {

  "use strict";

  const HelpfulPlugin = {
    el: ".helpful",
    vote: "helpful_save_vote",
    feedback: "helpful_save_feedback",
    helpful: helpful,
    initPlugin: function () {
      const self = this;
  
      if (self.el.length < 1) return;

      $(document).on("click", ".helpful .helpful-controls button", function (e) {
        e.preventDefault();

        var currentButton = $(this);
        var currentForm = $(currentButton).closest('.helpful');
        var ajaxData = {};

        $.extend(ajaxData, helpful.ajax_data);
        $.extend(ajaxData, $(currentButton).data());
        ajaxData.action = self.vote;

        var request = self.ajaxRequest(ajaxData);
        
        request.done(function (response) {
          $(currentForm).find(".helpful-header").remove();
          $(currentForm).find(".helpful-controls").remove();
          $(currentForm).find(".helpful-footer").remove();
          $(currentForm).find(".helpful-content").html(response);
          self.feedbackForm(currentForm);
        });
      });

      $.each($(".helpful"), function () {
        var current_container = $(this);
        if ($(current_container).is('.helpful-prevent-form')) {
          console.log(current_container);
          self.feedbackForm($(current_container));
        }
      });
    },
    feedbackForm: function (currentForm) {
      var self = this;

      $(currentForm).find('.helpful-cancel').unbind().click(function (e) {
        e.preventDefault();

        var ajaxData = {
          action: 'helpful_save_feedback',
          cancel: 1,
          type: $(currentForm).find('[name="type"]').val(),
          '_wpnonce': $(currentForm).find('[name="_wpnonce"]').val(),
        };

        var request = self.ajaxRequest(ajaxData);
        
        request.done(function (response) {
          $(currentForm).find(".helpful-content").html(response);
        });
      });
      
      $(currentForm).on("submit", ".helpful-feedback-form", function (e) {
        e.preventDefault();

        var formData = $(this).serializeArray();
        var ajaxData = {};
  
        $.each(formData, function (i, field) {
          ajaxData[field.name] = field.value;
        });

        var request = self.ajaxRequest(ajaxData);

        request.done(function (response) {
          $(currentForm).find(".helpful-content").html(response);
        });
      });
    },
    ajaxRequest: function (data) {
      if (typeof this.helpful.ajax_session !== 'undefined') {
        data.session = this.helpful.ajax_session;
      }

      console.log('ajaxRequest', data);

      return $.ajax({
        url: this.helpful.ajax_url,
        data: data,
        method: "POST",
      });
    },
  };

  $(function () {
    HelpfulPlugin.initPlugin();
  });

})(jQuery);