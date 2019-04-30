(function($){
  if( $("#helpful_widget").length ) {
    $("#helpful_widget").find("strong").each(function(){
      var headline = $(this),
          parent = $(headline).parent("div"),
          child = $(parent).children().last();
      $(headline).addClass("clickable");
      $(child).hide();
      $(headline).toggle(function(){
        $(child).show();
      }, function() {
        $(child).hide();
      });
    });
  }
})(jQuery);
