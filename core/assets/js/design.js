(function($) {

  var HelpfulDesign = {
    initClass: function() {

      if( $("#helpful_theme").length ) {
        this.initThemePreview();
      }

      if( $("#helpful_css").length ) {
        this.initCodeMirror();
      }
    },

    initThemePreview: function() {
      var themeSelect = $("#helpful_theme");
      var previewContainer = $("#theme-preview");
      var previewControls = $("#theme-preview-controls");
      var previewDevice = $("#theme-preview-device");
      
      $(themeSelect).on("change", function(e) {
        var currentValue = $(this).val();
        if( "theme" == currentValue ) {
          $(previewContainer).slideUp();
        } else {
          $(previewContainer).find("#hf-prev").removeClass().addClass("helpful helpful-theme-" + currentValue);
          $(previewContainer).find("#theme-preview-device").css({"width":"100%"}).removeClass().addClass("hfd");
          $(previewContainer).slideDown();
        }
      });

      $(previewControls).find(".show-laptop").click(function(e) {
        $(previewDevice).animate({"width":"100%"}).removeClass().addClass("hfd");
        e.preventDefault();
      });

      $(previewControls).find(".show-tablet").click(function(e) {
        $(previewDevice).animate({"width":"768px"}).removeClass().addClass("hft");
        e.preventDefault();
      });

      $(previewControls).find(".show-smartphone").click(function(e) {
        $(previewDevice).animate({"width":"360px"}).removeClass().addClass("hfm");
        e.preventDefault();
      });

      $(previewControls).find(".close").click(function(e) {
        $(previewContainer).slideUp();
        e.preventDefault();
      });
    },

    initCodeMirror: function() {
      CodeMirror.fromTextArea(document.getElementById("helpful_css"), {
        lineNumbers: true,
        mode: "text/javascipt",
        theme: "blackboard"
      });
    }
  };

  $(function() {
    HelpfulDesign.initClass();
  });
})(jQuery);
