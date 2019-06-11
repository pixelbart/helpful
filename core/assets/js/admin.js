(function ($) {

  const HelpfulAdmin = {
    loader: "<div class='helpful_loader'><i class='dashicons dashicons-update'></i></div>",
    canvas: "<canvas class='chart'></canvas>",
    init: function () {
      var self = this;

      $(".helpful-admin-panel").each(function () {
        var currentTab = $(this);
        self.togglePanel(currentTab);
      });

      $("select.linked").on("change", function (e) {
        e.preventDefault();
        window.location.href = $(this).find("option:selected").val();
      });

      if ($('.helpful-date').length > 0) {
        self.datePicker();
      }

      if ($('.helpful-range-form').length > 0) {
        self.getStatsRange();
        $('.helpful-range-form').on('change', function () {
          self.getStatsRange();
        });
      }

      if ($('.helpful-total').length > 0) {
        self.getStatsTotal();
      }
    },
    togglePanel: function (tabElement) {
      var currentButton = $(tabElement).find("button")[0];
      $(currentButton).on("click", function (e) {
        e.preventDefault();
        $(tabElement).toggleClass("active");
      });      
    },
    datePicker: function () {
      $('.helpful-date').datepicker({
        changeMonth: true,
        changeYear: true,
        dateFormat: 'yy-mm-dd',
      });
    },
    getStatsRange: function () {
      const self = this;
      var canvas;
      var el = $('.helpful-range');
      var form = $('.helpful-range-form');
      var data = $(form).serializeArray();

      $(el).html(self.loader);

      self.ajaxRequest(data).done(function (response) {
        console.log(response);
        if (!("status" in response)) {
          $(el).html(self.canvas);
          canvas = $(el).find('.chart')[0].getContext("2d");
          new Chart(canvas, response);
        } else {
          $(el).html(response.message);
        }
      });

      return;
    },
    getStatsTotal: function () {
      const self = this;
      var canvas;
      var el = $('.helpful-total');
      var data = { 'action': 'helpful_total_stats', '_wpnonce': helpful_admin.nonce };

      $(el).html(self.loader);

      self.ajaxRequest(data).done(function (response) {
        if (!("status" in response)) {
          $(el).html(self.canvas);
          canvas = $(el).find('.chart')[0].getContext("2d");
          new Chart(canvas, response);
        } else {
          $(el).html(response.message);
        }
      });

      return;      
    },
    ajaxRequest: function (data) {
      return $.ajax({
        url: helpful_admin.ajax_url,
        data: data,
        method: "POST",
      });
    },
  }

  $(function () {
    HelpfulAdmin.init();
  });

})(jQuery)