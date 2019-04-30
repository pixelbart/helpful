(function($) {

  var HelpfulPlugin = {

    initClass: function() {
      var self = this;

      if( $(".helpful").length < 1 ) {
        return;
      }

      self.initPro();
      self.initContra();
    },

    // on pro
    initPro: function() {
      var self = this;

      $("body").on("click", ".helpful-pro", function(e) {
        e.preventDefault();

        var helpfulContainer = $(this).closest(".helpful");
        var ajaxData = self.ajaxDataButton(this, "helpful_ajax_pro");
        var currentRequest = self.ajaxRequest(ajaxData);

        currentRequest.done(function(response) {
          $(helpfulContainer).html( response );
        });

        currentRequest.always(function(response) {
          self.feedbackForm(helpfulContainer);
          self.contactForm7();
        });

        return false;
      });
    },

    // on contra
    initContra: function() {
      var self = this;

      $("body").on("click", ".helpful-con", function(e) {
        e.preventDefault();

        var helpfulContainer = $(this).closest(".helpful");
        var ajaxData = self.ajaxDataButton(this, "helpful_ajax_contra");
        var currentRequest = self.ajaxRequest(ajaxData);

        currentRequest.done(function(response) {
          $(helpfulContainer).html( response );
        });

        currentRequest.always(function(response) {
          self.feedbackForm(helpfulContainer);
          self.contactForm7();
        });

        return false;
      });
    },

    // default ajax data for buttons
    ajaxDataButton: function(element, action) {
      var ajaxData = {}

      ajaxData["action"] = action;
      ajaxData["post_id"] = $(element).data("id");
      ajaxData["user"] = $(element).data("user");
      ajaxData["pro"] = $(element).data("pro");
      ajaxData["contra"] = $(element).data("contra");
      ajaxData["_ajax_nonce"] = $(element).data("nonce");

      return ajaxData;
    },

    // insert feedback
    feedbackForm: function(parentElement) {
      if( $(".helpful-feedback") ) {

        var self = this;
        var ajaxData = {}
        var currentContainer = $(".helpful-feedback");

        $("body").on("click", ".helpful-feedback button", function(e) {

          ajaxData["action"] = "helpful_ajax_feedback";
          ajaxData["type"] = $(currentContainer).data("type");
          ajaxData["post_id"] = $(currentContainer).data("post");
          ajaxData["post_content"] = $(currentContainer).find("textarea").val();
          ajaxData["_ajax_nonce"] = $(currentContainer).data("nonce");

          if( ajaxData["post_content"].length > 0 ) {
            self.ajaxRequest(ajaxData).done(function(response) {
              $(parentElement).html(response);
            });
          }
        });
      }
    },

    // contact form 7 support
    contactForm7: function() {
      if( $(".wpcf7").length ) {
        var wpcf7Elm = $( ".wpcf7" );
        var actionUrl = $(wpcf7Elm).find("form").attr("action").split("#");

        $(wpcf7Elm).find("form").attr("action", "#" + actionUrl[1]);

         $(wpcf7Elm).find("form").each( function() {
          var $form = $( this );
          if ( typeof wpcf7 !== "undefined" ) {
            wpcf7.initForm( $form );
            if ( wpcf7.cached ) {
              wpcf7.refill( $form );
            }
          }
        });
      }
    },

    ajaxRequest: function(data) {
      return $.ajax({
        url : helpful.ajax_url,
        data : data,
        method : "POST",
      });      
    }
  };

  $(function() {
    HelpfulPlugin.initClass();
  });

})(jQuery);
