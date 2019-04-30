(function($) {

  var HelpfulDesign = {
    initClass: function() {

      if( $('#helpful_theme').length ) {
        this.initThemePreview();
      }

      if( $('#helpful_css').length ) {
        this.initCodeMirror();
      }
    },

    initThemePreview: function() {
      var theme_select = $('#helpful_theme');
      var preview_container = $('#theme-preview');
      var preview_controls = $('#theme-preview-controls');
      var preview_device = $('#theme-preview-device');

      console.log(theme_select)

      $(theme_select).on('change', function(e) {
        console.log(e);
        var current_value = $(this).val();

        if( 'theme' == current_value ) {
          $(preview_container).slideUp();
        } else {
          $(preview_container).find('#hf-prev').removeClass().addClass('helpful helpful-theme-' + current_value);
          $(preview_container).find('#theme-preview-device').css({'width':'100%'}).removeClass().addClass('hfd');
          $(preview_container).slideDown();
        }
      });

      $(preview_controls).find('.show-laptop').click(function(e) {
        console.log(e);
        $(preview_device).animate({'width':'100%'}).removeClass().addClass('hfd');
        e.preventDefault();
      });

      $(preview_controls).find('.show-tablet').click(function(e) {
        console.log(e);
        $(preview_device).animate({'width':'768px'}).removeClass().addClass('hft');
        e.preventDefault();
      });

      $(preview_controls).find('.show-smartphone').click(function(e) {
        console.log(e);
        $(preview_device).animate({'width':'360px'}).removeClass().addClass('hfm');
        e.preventDefault();
      });

      $(preview_controls).find('.close').click(function(e) {
        console.log(e);
        $(preview_container).slideUp();
        e.preventDefault();
      });
    },

    initCodeMirror: function() {
      CodeMirror.fromTextArea(document.getElementById('helpful_css'), {
        lineNumbers: true,
        mode: "text/javascipt",
        theme: "blackboard"
      });
    }
  }

  $(function() {
    HelpfulDesign.initClass();
  });
})(jQuery)
