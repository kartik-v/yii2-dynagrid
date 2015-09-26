/*!
 * @package   yii2-dynagrid
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015
 * @version   1.4.5
 *
 * JQuery Plugin for yii2-dynagridDetail. Allows saving/deleting the dynagridDetail 
 * filter or sort details.
 * 
 * Author: Kartik Visweswaran
 * Copyright: 2015, Kartik Visweswaran, Krajee.com
 * For more JQuery plugins visit http://plugins.krajee.com
 * For more Yii related demos visit http://demos.krajee.com
 */
(function ($) {
    "use strict";
    var isEmpty = function (value, trim) {
            return value === null || value === undefined || value === [] || value === '' || trim && $.trim(value) === '';
        },
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
            var self = this, $form = self.$form, $formContainer = self.$formContainer, $list = self.$list;
            self.$btnSave.on('click', function () {
                $form.hide();
                $formContainer.prepend(self.submitMessage);
                setTimeout(function () {
                    $form.submit();
                }, 1000);
            });
            self.$btnDelete.on('click', function () {
                if (isEmpty(self.$element.val())) {
                    $form.trigger('reset.yiiActiveForm');
                    return;
                }
                if (!window.confirm(self.deleteConfirmation)) {
                    return;
                }
                var $el = $form.find('input[name="deleteDetailFlag"]');
                $el.val(1);
                $form.hide();
                $formContainer.prepend(self.deleteMessage);
                setTimeout(function () {
                    $form.submit();
                }, 1000);
            });
            $list.on('change', function () {
                var val = $list.val(), name = $list.find('option:selected').text();
                if (!isEmpty(val)) {
                    self.$element.val(val);
                    self.$name.val(name);
                    $.ajax({
                        type: 'post',
                        url: self.configUrl,
                        dataType: 'json',
                        data: self.$form.serialize(),
                        success: function (data) {
                            if (data.status === 'success') {
                                $form.find('.dynagrid-settings-text').html(data.content);
                            }
                        }
                    });

                }
            });
            $form.off('afterValidate').on('afterValidate', function (e, msg) {
                $.each(msg, function (key, value) {
                    if (value.length > 0) {
                        $form.show();
                        $formContainer.find('.dynagrid-submit-message').remove();
                        return true;
                    }
                });
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
        deleteConfirmation: 'Are you sure you want to delete all your grid personalization settings?',
        configUrl: '',
        modalId: '',
        dynaGridId: ''
    };
}(window.jQuery));