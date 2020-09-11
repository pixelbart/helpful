(function ($) {

  "use strict";

  const HelpfulAdminWidget = {
    el: "#helpful_widget",
    loader: "<div class=\"loader\"><i class=\"dashicons dashicons-update\"></i></div>",
    canvas: "<canvas class=\"chart\"></canvas>",
    initWidget: function () {
      const self = this;

      // init stats for today
      self.getStats();

      // get stats on click refresh button
      $(this.el).on("click", ".refresh", function (e) {
        if (e.target !== e.currentTarget) {
          return;
        }

        self.getStats();
      });

      // get stats on change event
      $(this.el).on("change", "select", function (e) {
        if (e.target !== e.currentTarget) {
          return;
        }

        var hidden = ["total", "today", "yesterday", "week", "month"];

        if (hidden.indexOf(this.value) !== -1) {
          $(self.el).find("select[name=year]").val((new Date).getFullYear());
          $(self.el).find("select[name=year]").parent().attr("hidden", "hidden");
        } else {
          $(self.el).find("select[name=year]").parent().removeAttr("hidden");
        }

        self.getStats();
      });

      $(".helpful-widget-panel").each(function () {
        var currentTab = $(this);
        self.togglePanel(currentTab);
      });

      return;
    },
    getStats: function () {
      const self = this;
      var form = $(self.el).find(".helpful-widget-form");
      var container = $(self.el).find(".helpful-widget-content");
      var data = $(form).serializeArray();

      $(container).html(self.loader);

      self.ajaxRequest(data).done(function (response) {
        response = response.data;
        if (!("status" in response)) {

          $(container).html(self.canvas);
          var canvas = $(container).find(".chart")[0].getContext("2d");

          // show percentages on doughnuts
          if ("doughnut" === response.type) {
            response.options.tooltips = {
              callbacks: {
                label: function (tooltipItem, data) {
                  var dataset = data.datasets[tooltipItem.datasetIndex];
                  var meta = dataset._meta[Object.keys(dataset._meta)[0]];
                  var total = meta.total;
                  var currentValue = dataset.data[tooltipItem.index];
                  var percentage = parseFloat((currentValue / total * 100).toFixed(1));
                  return currentValue + ' (' + percentage + '%)';
                },
                title: function (tooltipItem, data) {
                  return data.labels[tooltipItem[0].index];
                }
              }
            };
          }
          // show percentages on bars
          else {
            response.options.tooltips = {
              callbacks: {
                label: function (tooltipItem, data) {
                  var total = 0;
                  var pro = data.datasets[0].data[tooltipItem.index];
                  var contra = data.datasets[1].data[tooltipItem.index];
                  
                  total = pro + contra;

                  var dataset = data.datasets[tooltipItem.datasetIndex];
                  var currentValue = dataset.data[tooltipItem.index];
                  var percentage = parseFloat((currentValue / total * 100).toFixed(1));
                  return currentValue + " (" + percentage + "%)";
                },
                title: function (tooltipItem, data) {
                  return data.labels[tooltipItem[0].index];
                }
              }
            };
          }
      
          new Chart(canvas, response);
        } else {
          $(container).html(response.message);
        }
      });

      return;
    },
    togglePanel: function (tabElement) {
      var currentButton = $(tabElement).find("button")[0];
      $(currentButton).on("click", function (e) {
        e.preventDefault();
        $(tabElement).toggleClass("active");
      });
    },
    ajaxRequest: function (data) {
      return $.ajax({
        url: ajaxurl,
        data: data,
        method: "GET",
      });
    },
  };

  $(function () {
    HelpfulAdminWidget.initWidget();
  });

})(jQuery);