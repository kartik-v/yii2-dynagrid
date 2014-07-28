/*!
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014
 * @version 1.0.0
 *
 * JQuery Plugin for yii2-dynagrid.
 * 
 * Author: Kartik Visweswaran
 * Copyright: 2014, Kartik Visweswaran, Krajee.com
 * For more JQuery plugins visit http://plugins.krajee.com
 * For more Yii related demos visit http://demos.krajee.com
 */
(function ($) {
    var isEmpty = function (value, trim) {
        return value === null || value === undefined || value == []
            || value === '' || trim && $.trim(value) === '';
    };

    var Dynagrid = function (element, options) {
        this.$element = $(element);
        this.submitMessage = options.submitMessage;
        this.init();
        this.listen();
    };

    var isSubmitted = false;

    Dynagrid.prototype = {
        constructor: Dynagrid,
        init: function () {
            var self = this;
            self.$form = self.$element.closest('form');
            var $form = self.$form;
            self.$visibleEl = $form.find(".sortable-visible");
            self.$hiddenEl = $form.find(".sortable-hidden");
            self.$visibleKeys = $form.find('input[name="visibleKeys"]');
            self.$btnSubmit = $form.find('.dynagrid-submit');
            self.$btnReset = $form.find('.dynagrid-reset');
            self.$formContainer = self.$form.parent();
            self.setColumnKeys();
            self.visibleContent = self.$visibleEl.html();
            self.hiddenContent = self.$hiddenEl.html();
            self.visibleSortableOptions = window[self.$visibleEl.data('pluginOptions')];
            self.hiddenSortableOptions = window[self.$hiddenEl.data('pluginOptions')];
        },
        listen: function () {
            var self = this;
            self.$btnSubmit.on('click', function () {
                self.setColumnKeys();
                self.$visibleKeys.val(self.visibleKeys);
                self.$form.hide();
                self.$formContainer.prepend(self.submitMessage);
                setTimeout(function () {
                    self.$form.submit();
                }, 1000);
            });
            self.$btnReset.on('click', function () {
                self.$visibleEl.html(self.visibleContent);
                self.$hiddenEl.html(self.hiddenContent);
                self.setColumnKeys();
                self.$formContainer.find('.dynagrid-submit-message').remove();
                self.$visibleEl.sortable(self.visibleSortableOptions);
                self.$hiddenEl.sortable(self.hiddenSortableOptions);
            });
            self.$form.on('submit', function (e) {
                var $form = $(this), chkError = '';
                $form.find('.help-block').each(function () {
                    chkError = $(this).text();
                    if (!isEmpty(chkError.trim())) {
                        $form.show();
                        $form.parent().find('.dynagrid-submit-message').remove();
                        return;
                    }
                });
            });
        },
        setColumnKeys: function () {
            var self = this;
            self.visibleKeys = self.$visibleEl.find('li').map(function (i, n) {
                return $(n).attr('id');
            }).get().join(',');

        },
    };

    // dynagrid plugin definition
    $.fn.dynagrid = function (option) {
        var args = Array.apply(null, arguments);
        args.shift();
        return this.each(function () {
            var $this = $(this),
                data = $this.data('dynagrid'),
                options = typeof option === 'object' && option;

            if (!data) {
                $this.data('dynagrid', (data = new Dynagrid(this, $.extend({}, $.fn.dynagrid.defaults, options, $(this).data()))));
            }

            if (typeof option === 'string') {
                data[option].apply(data, args);
            }
        });
    };

    $.fn.dynagrid.defaults = {
        submitMessage: 'Applying configuration &hellip;',
    };
}(jQuery));