(function($) {

  var HelpfulPlugin = {

    initClass: function() {
      var self = this;

      if( $('.helpful').length < 1 ) {
        return;
      }

      self.initPro();
      self.initContra();
    },

    // on pro
    initPro: function() {
      var self = this;

      $('body').on('click', '.helpful-pro', function(e) {
        e.preventDefault();

    		var ajaxData = {}
        var helpful_container = $(this).closest('.helpful')

    		ajaxData['action'] = 'helpful_ajax_pro';
    		ajaxData['post_id'] = $(this).data('id');
    		ajaxData['user'] = $(this).data('user');
    		ajaxData['pro'] = $(this).data('pro');
    		ajaxData['contra'] = $(this).data('contra');

        var currentRequest = self.ajaxRequest(ajaxData);

        currentRequest.done(function(response) {
    			$(helpful_container).html( response );
        });

    		currentRequest.always(function(response) {
          self.feedbackForm(helpful_container);
    			self.contactForm7();
    		});

        return false;
      });
    },

    // on contra
    initContra: function() {
      var self = this;

      $('body').on('click', '.helpful-con', function(e) {
        e.preventDefault();

    		var ajaxData = {}
        var helpful_container = $(this).closest('.helpful')

    		ajaxData['action'] = 'helpful_ajax_contra';
    		ajaxData['post_id'] = $(this).data('id');
    		ajaxData['user'] = $(this).data('user');
    		ajaxData['pro'] = $(this).data('pro');
    		ajaxData['contra'] = $(this).data('contra');

        var currentRequest = self.ajaxRequest(ajaxData);

        currentRequest.done(function(response) {
    			$(helpful_container).html( response );
        });

    		currentRequest.always(function(response) {
          self.feedbackForm(helpful_container);
    			self.contactForm7();
    		});

        return false;
      });
    },

    // insert feedback
    feedbackForm: function(parentElement) {
      if( $('.helpful-feedback') ) {

        var self = this;
        var ajaxData = {}
        var currentContainer = $('.helpful-feedback');

        $('body').on('click', '.helpful-feedback button', function(e) {

          ajaxData['action'] = 'helpful_ajax_feedback';
          ajaxData['type'] = $(currentContainer).data('type');
          ajaxData['post_id'] = $(currentContainer).data('post');
          ajaxData['post_content'] = $(currentContainer).find('textarea').val();

          if( ajaxData['post_content'].length > 0 ) {
            self.ajaxRequest(ajaxData).done(function(response) {
              $(parentElement).html(response);
            });
          }
        });
      }
    },

    // contact form 7 support
    contactForm7: function() {
  		if( $('.wpcf7').length ) {
  			var wpcf7Elm = $( '.wpcf7' );
  			var actionUrl = $(wpcf7Elm).find('form').attr('action').split('#');

  			$(wpcf7Elm).find('form').attr('action', "#" + actionUrl[1]);

   			$(wpcf7Elm).find('form').each( function() {
  				var $form = $( this );
  				wpcf7.initForm( $form );
  				if ( wpcf7.cached ) {
  					wpcf7.refill( $form );
  				}
  			});
  		}
    },

    ajaxRequest: function(data) {
      return $.ajax({
  			url : helpful.ajax_url,
        data : data,
        method : 'POST',
      });
    }
  }

  $(function() {
    HelpfulPlugin.initClass();
  });

})(jQuery)
