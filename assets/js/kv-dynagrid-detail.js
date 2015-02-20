/*!
 * @package   yii2-dynagrid
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015
 * @version   1.4.2
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
    var isEmpty = function (value, trim) {
        return value === null || value === undefined || value == []
        || value === '' || trim && $.trim(value) === '';
    };

    var DynagridDetail = function (element, options) {
        this.$element = $(element);
        this.submitMessage = options.submitMessage;
        this.deleteMessage = options.deleteMessage;
        this.deleteConfirmation = options.deleteConfirmation;
        this.configUrl = options.configUrl;
        this.modalId = options.modalId;
        this.init();
        this.listen();
    };

    DynagridDetail.prototype = {
        constructor: DynagridDetail,
        init: function () {
            var self = this, $modal = $('#' + self.modalId);
            //$modal.appendTo('body');
            self.$form = self.$element.closest('form');
            self.$formContainer = self.$form.parent();
            var $form = self.$form;
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
                    if (data.status == 'success') {
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
                if (!confirm(self.deleteConfirmation)) {
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
                            var $out = $form.find('.dynagrid-settings-text');
                            if (data.status == 'success') {
                                $form.find('.dynagrid-settings-text').html(data.content);
                            }
                        }
                    });

                }
            });
            $form.on('afterValidate', function (e, msg) {
                for (var key in msg) {
                    if (msg[key].length > 0) {
                        $form.show();
                        $formContainer.find('.dynagrid-submit-message').remove();
                        return;
                    }
                }
            });
        },
    };

    // dynagridDetail plugin definition
    $.fn.dynagridDetail = function (option) {
        var args = Array.apply(null, arguments);
        args.shift();
        return this.each(function () {
            var $this = $(this),
                data = $this.data('dynagridDetail'),
                options = typeof option === 'object' && option;

            if (!data) {
                $this.data('dynagridDetail', (data = new DynagridDetail(this,
                    $.extend({}, $.fn.dynagridDetail.defaults, options, $(this).data()))));
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
        modalId: ''
    };
}(jQuery));