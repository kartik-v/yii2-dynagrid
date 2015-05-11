/*!
 * @package   yii2-dynagrid
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015
 * @version   1.4.2
 *
 * JQuery Plugin for yii2-dynagrid.
 * 
 * Author: Kartik Visweswaran
 * Copyright: 2015, Kartik Visweswaran, Krajee.com
 * For more JQuery plugins visit http://plugins.krajee.com
 * For more Yii related demos visit http://demos.krajee.com
 */
(function ($) {
    var isEmpty = function (value, trim) {
        return value === null || value === undefined || value == []
        || value === '' || trim && $.trim(value) === '';
    };

    var getFormObjectId = function ($element) {
        var id = $element.attr('name');
        return id.toLowerCase().replace(/-/g, '_') + '_activeform'
    };

    var cacheActiveForm = function ($element) {
        var $form = $element.closest('form'), objActiveForm = $form.data('yiiActiveForm'),
            id = getFormObjectId($element);
        if (isEmpty(id) || isEmpty(objActiveForm)) {
            return;
        }
        window[id] = objActiveForm;
    };

    var Dynagrid = function (element, options) {
        this.$element = $(element);
        this.submitMessage = options.submitMessage;
        this.deleteMessage = options.deleteMessage;
        this.deleteConfirmation = options.deleteConfirmation;
        this.modalId = options.modalId;
        this.init();
        this.listen();
    };

    var isSubmitted = false;

    Dynagrid.prototype = {
        constructor: Dynagrid,
        init: function () {
            var self = this, $modal = $('#' + self.modalId),
                obj = getFormObjectId(self.$element), $form = self.$element.closest('form');
            self.$form = $form;
            if (isEmpty(window[obj])) {
                cacheActiveForm(self.$element);
            }
            // $modal.appendTo('body');
            self.$visibleEl = $form.find(".sortable-visible");
            self.$hiddenEl = $form.find(".sortable-hidden");
            self.$visibleKeys = $form.find('input[name="visibleKeys"]');
            self.$btnSubmit = $modal.find('.dynagrid-submit');
            self.$btnDelete = $modal.find('.dynagrid-delete');
            self.$btnReset = $modal.find('.dynagrid-reset');
            self.$formContainer = $form.parent();
            self.setColumnKeys();
            self.visibleContent = self.$visibleEl.html();
            self.hiddenContent = self.$hiddenEl.html();
            self.visibleSortableOptions = window[self.$visibleEl.attr('data-krajee-sortable')];
            self.hiddenSortableOptions = window[self.$hiddenEl.attr('data-krajee-sortable')];
        },
        listen: function () {
            var self = this, $form = self.$form, $formContainer = self.$formContainer,
                objActiveForm = self.$form.data('yiiActiveForm');
            self.$btnSubmit.off('click').on('click', function () {
                self.setColumnKeys();
                self.$visibleKeys.val(self.visibleKeys);
                $form.hide();
                $formContainer.prepend(self.submitMessage);
                setTimeout(function () {
                    $form.submit();
                }, 1000);
            });
            self.$btnDelete.off('click').on('click', function () {
                if (!confirm(self.deleteConfirmation)) {
                    return;
                }
                var $el = $form.find('input[name="deleteFlag"]');
                $el.val(1);
                $form.hide();
                $formContainer.prepend(self.deleteMessage);
                setTimeout(function () {
                    $form.submit();
                }, 1000);
            });
            self.$btnReset.off('click').on('click', function () {
                self.$visibleEl.sortable('destroy').html(self.visibleContent);
                self.$hiddenEl.sortable('destroy').html(self.hiddenContent);
                self.setColumnKeys();
                $formContainer.find('.dynagrid-submit-message').remove();
                self.$visibleEl.sortable(self.visibleSortableOptions);
                self.$hiddenEl.sortable(self.hiddenSortableOptions);
                $form.trigger('reset.yiiActiveForm');
                setTimeout(function () {
                    $form.find("select").trigger("change");
                }, 100);
            });
            $form.off('afterValidate').on('afterValidate', function (e, msg) {
                for (var key in msg) {
                    if (msg[key].length > 0) {
                        $form.show();
                        $formContainer.find('.dynagrid-submit-message').remove();
                        return;
                    }
                }
            });

        },
        reset: function () {
            var self = this, $form = self.$element.closest('form'),
                id = getFormObjectId(self.$element), objActiveForm = window[id];
            if (!isEmpty(objActiveForm)) {
                $form.yiiActiveForm('destroy');
                $form.yiiActiveForm(objActiveForm.attributes, objActiveForm.settings);
            }
            $form.find('select[data-krajee-select2]').each(function () {
                var $el = $(this);
                $.when($el.select2(window[$el.attr('data-krajee-select2')])).done(function () {
                    if ($el.select2 !== undefined) {
                        initSelect2Loading($el.attr('id'), '.select2-container--krajee');
                    }
                });
            });
            $form.find('[data-krajee-sortable]').each(function () {
                var $el = $(this);
                if ($el.sortable !== undefined) {
                    $el.sortable('destroy');
                }
                $el.sortable(window[$el.attr('data-krajee-sortable')]);
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
                $this.data('dynagrid',
                    (data = new Dynagrid(this, $.extend({}, $.fn.dynagrid.defaults, options, $(this).data()))));
            }

            if (typeof option === 'string') {
                data[option].apply(data, args);
            }
        });
    };

    $.fn.dynagrid.defaults = {
        submitMessage: '',
        deleteMessage: '',
        deleteConfirmation: 'Are you sure you want to delete all your grid personalization settings?',
        modalId: ''
    };
}(jQuery));