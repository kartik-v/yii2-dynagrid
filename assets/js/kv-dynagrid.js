/*!
 * @package   yii2-dynagrid
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2017
 * @version   1.4.7
 *
 * JQuery Plugin for yii2-dynagrid.
 * 
 * For more JQuery plugins visit http://plugins.krajee.com
 * For more Yii related demos visit http://demos.krajee.com
 */
(function ($) {
    "use strict";
    var $h, Dynagrid;
    $h = {
        isEmpty: function (value, trim) {
            return value === null || value === undefined || value === [] || value === '' || trim && $.trim(value) === '';
        },
        getFormObjectId: function ($element) {
            return $element.attr('name').toLowerCase().replace(/-/g, '_') + '_activeform';
        },
        cacheActiveForm: function ($element) {
            var $form = $element.closest('form'), objActiveForm = $form.data('yiiActiveForm'),
                id = $h.getFormObjectId($element);
            if ($h.isEmpty(id) || $h.isEmpty(objActiveForm)) {
                return;
            }
            window[id] = objActiveForm;
        },
        handler: function ($el, event, callback) {
            var self = this, ns = '.dynagrid', ev = event.split(' ').join(ns + ' ') + ns;
            if (!$el || !$el.length) {
                return;
            }
            $el.off(ev).on(ev, callback);
        }            
    };
    Dynagrid = function (element, options) {
        var self = this;
        self.$element = $(element);
        $.each(options, function (key, value) {
            self[key] = value;
        });
        self.init();
        self.listen();
    };

    Dynagrid.prototype = {
        constructor: Dynagrid,
        init: function () {
            var self = this, $modal = $('#' + self.modalId), $dynaGridId = $('#' + self.dynaGridId),
                obj = $h.getFormObjectId(self.$element), $form = self.$element.closest('form');
            self.$form = $form;
            if ($h.isEmpty(window[obj])) {
                $h.cacheActiveForm(self.$element);
            }
            $modal.insertAfter($dynaGridId);
            self.$visibleEl = $form.find(".sortable-visible");
            self.$hiddenEl = $form.find(".sortable-hidden");
            self.$visibleKeys = $form.find('input[name="visibleKeys"]');
            self.$btnSubmit = $modal.find('.dynagrid-submit');
            self.$btnDelete = $modal.find('.dynagrid-delete');
            self.$btnReset = $modal.find('.dynagrid-reset');
            self.$formContainer = $form.parent();
            self._setColumnKeys();
            self.visibleContent = self.$visibleEl.html();
            self.hiddenContent = self.$hiddenEl.html();
            self.visibleSortableOptions = window[self.$visibleEl.attr('data-krajee-sortable')];
            self.hiddenSortableOptions = window[self.$hiddenEl.attr('data-krajee-sortable')];
        },
        listen: function () {
            var self = this;
            $h.handler(self.$btnSubmit, 'click', $.proxy(self._submit, self));
            $h.handler(self.$btnDelete, 'click', $.proxy(self._delete, self));
            $h.handler(self.$btnReset, 'click', $.proxy(self._reset, self));
            $h.handler(self.$form, 'keypress', function (e) {
                var key = e.which || e.keyCode || 0;
                if (key === 13) {
                    e.preventDefault();
                    self._submit();
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
        _setColumnKeys: function () {
            var self = this;
            self.visibleKeys = self.$visibleEl.find('li').map(function (i, n) {
                return $(n).attr('id');
            }).get().join(',');

        },
        _submitForm: function(msg) {
            var self = this, $form = self.$form, $formContainer = self.$formContainer;
            $form.hide();
            $formContainer.prepend(msg);
            setTimeout(function () {
                $form.submit();
            }, 1000);
        },
        _submit: function() {
            var self = this;
            self._setColumnKeys();
            self.$visibleKeys.val(self.visibleKeys);
            self._submitForm(self.submitMessage);
        },
        _delete: function() {
            var self = this, $el, dialogLib = window[self.dialogLib];
            dialogLib.confirm(self.deleteConfirmation, function (result) {
                if (result) {
                    $el = self.$form.find('input[name="deleteFlag"]');
                    if ($el && $el.length) {
                        $el.val(1);
                    }
                    self._submitForm(self.deleteMessage);
                }
            });
        },
        _reset: function () {
            var self = this;
            self.$visibleEl.sortable('destroy').html(self.visibleContent);
            self.$hiddenEl.sortable('destroy').html(self.hiddenContent);
            self._setColumnKeys();
            self.$formContainer.find('.dynagrid-submit-message').remove();
            self.$visibleEl.sortable(self.visibleSortableOptions);
            self.$hiddenEl.sortable(self.hiddenSortableOptions);
            self.$form.trigger('reset.yiiActiveForm');
            setTimeout(function () {
                self.$form.find("select").trigger("change");
            }, 100);
        },
        reset: function () {
            var self = this, $form = self.$element.closest('form'),
                id = $h.getFormObjectId(self.$element), objActiveForm = window[id];
            if (!$h.isEmpty(objActiveForm)) {
                $form.yiiActiveForm('destroy');
                $form.yiiActiveForm(objActiveForm.attributes, objActiveForm.settings);
            }
            $form.find('select[data-krajee-select2]').each(function () {
                var $el = $(this), settings = window[$el.attr('data-krajee-select2')] || {};
                if ($el.data('select2')) {
                    $el.select2('destroy');
                }
                $.when($el.select2(settings)).done(function () {
                    initS2Loading($el.attr('id'), '.select2-container--krajee'); // jshint ignore:line
                });
            });
            $form.find('[data-krajee-sortable]').each(function () {
                var $el = $(this);
                if ($el.data('sortable')) {
                    $el.sortable('destroy');
                }
                $el.sortable(window[$el.attr('data-krajee-sortable')]);
            });
        }
    };

    // dynagrid plugin definition
    $.fn.dynagrid = function (option) {
        var args = Array.apply(null, arguments);
        args.shift();
        return this.each(function () {
            var $this = $(this), data = $this.data('dynagrid'), options = typeof option === 'object' && option;

            if (!data) {
                data = new Dynagrid(this, $.extend({}, $.fn.dynagrid.defaults, options, $(this).data()));
                $this.data('dynagrid', data);
            }

            if (typeof option === 'string') {
                data[option].apply(data, args);
            }
        });
    };

    $.fn.dynagrid.defaults = {
        submitMessage: '',
        deleteMessage: '',
        deleteConfirmation: '',
        modalId: '',
        dynaGridId: '',
        dialogLib: 'krajeeDialog'
    };
}(window.jQuery));