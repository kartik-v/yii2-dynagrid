/*!
 * @package   yii2-dynagrid
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2019
 * @version   1.5.1
 *
 * JQuery Plugin for yii2-dynagridDetail. Allows saving/deleting the dynagridDetail 
 * filter or sort details.
 *
 * For more JQuery plugins visit http://plugins.krajee.com
 * For more Yii related demos visit http://demos.krajee.com
 */
(function ($) {
    "use strict";
    var $h, DynagridDetail;

    $h = {
        isEmpty: function (value, trim) {
            return value === null || value === undefined || value === [] || value === '' || trim && $.trim(value) === '';
        },
        handler: function ($el, event, callback) {
            var ns = '.dynagridDetail', ev = event.split(' ').join(ns + ' ') + ns;
            if (!$el || !$el.length) {
                return;
            }
            $el.off(ev).on(ev, callback);
        }
    };

    DynagridDetail = function (element, options) {
        var self = this;
        self.$element = $(element);
        $.each(options, function (key, value) {
            self[key] = value;
        });
        self.init();
        self.listen();
    };

    DynagridDetail.prototype = {
        constructor: DynagridDetail,
        init: function () {
            var self = this, $modal = $('#' + self.modalId), $dynaGridId = $('#' + self.dynaGridId),
                $form = self.$element.closest('form');
            $modal.insertAfter($dynaGridId);
            self.$form = $form;
            self.$formContainer = $form.parent();
            self.$btnSave = $form.find('.dynagrid-detail-save');
            self.$btnDelete = $form.find('.dynagrid-detail-delete');
            self.$list = $form.find('.dynagrid-detail-list');
            self.$name = $form.find('.dynagrid-detail-name');
        },
        process: function (vUrl) {
            var self = this;
            $.ajax({
                type: 'post',
                url: vUrl,
                dataType: 'json',
                data: self.$form.serialize(),
                beforeSend: function () {
                    self.$form.hide();
                    self.$formContainer.prepend(self.submitMessage);
                },
                success: function (data) {
                    if (data.status === 'success') {
                        self.$formContainer.html(data.content);
                    }
                }
            });
        },
        listen: function () {
            var self = this;
            $h.handler(self.$btnSave, 'click', $.proxy(self._submit, self));
            $h.handler(self.$btnDelete, 'click', $.proxy(self._delete, self));
            $h.handler(self.$list, 'change', function () {
                var $form = self.$form, val = $(this).val(), name = $(this).find('option:selected').text();
                if (!$h.isEmpty(val)) {
                    self.$name.val(name);
                    $.ajax({
                        type: 'post',
                        url: self.configUrl,
                        dataType: 'json',
                        data: $form.serialize(),
                        success: function (data) {
                            if (data.status) {
                                $form.find('.dynagrid-settings-text').html(data.content);
                            }
                        }
                    });

                }
            });
            $h.handler(self.$form, 'afterValidate', function (e, msg) {
                $.each(msg, function (key, value) {
                    if (value.length > 0) {
                        self.$form.show();
                        self.$formContainer.find('.dynagrid-submit-message').remove();
                        return true;
                    }
                });
            });
        },
        _submitForm: function (msg) {
            var self = this, $form = self.$form, $formContainer = self.$formContainer;
            $form.hide();
            $formContainer.prepend(msg);
            setTimeout(function () {
                $form.submit();
            }, 1000);
        },
        _submit: function () {
            var self = this;
            self._submitForm(self.submitMessage);
        },
        _delete: function () {
            var self = this, $el, dialogLib = window[self.dialogLib];
            if ($h.isEmpty(self.$list.val())) {
                self.$form.trigger('reset.yiiActiveForm');
                return;
            }
            dialogLib.confirm(self.deleteConfirmation, function (result) {
                if (result) {
                    $el = self.$form.find('input[name="deleteDetailFlag"]');
                    if ($el && $el.length) {
                        $el.val(1);
                    }
                    self._submitForm(self.deleteMessage);
                }
            });
        }
    };

    // dynagridDetail plugin definition
    $.fn.dynagridDetail = function (option) {
        var args = Array.apply(null, arguments);
        args.shift();
        return this.each(function () {
            var $this = $(this), data = $this.data('dynagridDetail'), options = typeof option === 'object' && option;

            if (!data) {
                data = new DynagridDetail(this, $.extend({}, $.fn.dynagridDetail.defaults, options, $(this).data()));
                $this.data('dynagridDetail', data);
            }

            if (typeof option === 'string') {
                data[option].apply(data, args);
            }
        });
    };

    $.fn.dynagridDetail.defaults = {
        submitMessage: '',
        deleteMessage: '',
        deleteConfirmation: '',
        configUrl: '',
        modalId: '',
        dynaGridId: '',
        dialogLib: 'krajeeDialog'
    };
}(window.jQuery));