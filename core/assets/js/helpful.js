(function($) {

    "use strict";

    const HelpfulPlugin = {
        el: ".helpful",
        vote: "helpful_save_vote",
        feedback: "helpful_save_feedback",
        helpful: helpful,
        initPlugin: function() {
            const self = this;

            if (window.performance) {
                if (2 === performance.navigation.type) {
                    self.hasUserVoted();
                }
            }

            if (self.el.length < 1) {
                return;
            }

            $(document).on("click", ".helpful .helpful-controls button", function(e) {
                e.preventDefault();

                var currentButton = $(this);
                var currentForm = $(currentButton).closest(".helpful");
                var ajaxData = {};

                $.extend(ajaxData, helpful.ajax_data);
                $.extend(ajaxData, $(currentButton).data());
                ajaxData.action = self.vote;

                var request = self.ajaxRequest(ajaxData);

                request.done(function(response) {
                    $(currentForm).find(".helpful-header").remove();
                    $(currentForm).find(".helpful-controls").remove();
                    $(currentForm).find(".helpful-footer").remove();
                    $(currentForm).find(".helpful-content").html(response);
                    self.feedbackForm(currentForm);
                });
            });

            $.each($(".helpful"), function() {
                var currentContainer = $(this);

                if ($(currentContainer).is(".helpful-prevent-form")) {
                    self.feedbackForm(currentContainer);
                }

                if ($(currentContainer).find(".helpful-toggle-feedback").length) {
                    $(currentContainer).find(".helpful-toggle-feedback").click(function(e) {
                        e.preventDefault();
                        $(this).parent().find("div").removeAttr("hidden");
                        $(this).remove();
                    });
                }
            });
        },
        feedbackForm: function(currentForm) {
            var self = this;

            $(currentForm).on("click", ".helpful-cancel", function(e) {
                e.preventDefault();

                var ajaxData = {
                    "action": "helpful_save_feedback",
                    "cancel": 1,
                    "type": $(currentForm).find("[name='type']").val(),
                    "_wpnonce": $(currentForm).find("[name='_wpnonce']").val(),
                    "post_id": $(currentForm).find("[name='post_id']").val(),
                };

                var request = self.ajaxRequest(ajaxData);

                request.done(function(response) {
                    $(currentForm).find(".helpful-content").html(response);
                });
            });

            $(currentForm).on("submit", ".helpful-feedback-form", function(e) {
                e.preventDefault();

                var formData = $(this).serializeArray();
                var ajaxData = {};

                $.each(formData, function(i, field) {
                    ajaxData[field.name] = field.value;
                });

                $(currentForm).remove(".danger");

                let key;
                let required = [];

                $(currentForm).find("[required]").each(function() {
                    if (!$.trim(this.value).length) {
                        $(this).after("<req class=\"danger\">" + helpful.translations.fieldIsRequired + "</req>");
                        required.push(key);
                    }
                });

                if (required.length) {
                    return;
                }

                var request = self.ajaxRequest(ajaxData);

                request.done(function(response) {
                    $(currentForm).find(".helpful-content").html(response);
                });
            });
        },
        hasUserVoted: function() {
            const self = this;

            let ajaxData = this.helpful.user_voted;

            $(self.el).hide();

            let request = self.ajaxRequest(ajaxData);

            request.done(function(response) {
                if (response.success) {
                    $(self.el).remove();
                } else {
                    $(self.el).show();
                }
            });

            return;
        },
        ajaxRequest: function(data) {
            if (typeof this.helpful.ajax_session !== "undefined") {
                data.session = this.helpful.ajax_session;
            }

            return $.ajax({
                url: this.helpful.ajax_url,
                data: data,
                method: "POST",
                cache: false,
            });
        },
    };

    $(function() {
        HelpfulPlugin.initPlugin();
    });

})(jQuery);