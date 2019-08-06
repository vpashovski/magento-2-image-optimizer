/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ImageOptimizer
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/modal',
], function ($, confirmation, alert) {
    "use strict";
    var btnOptimize = $('#optimize_image');

    $.widget('mageplaza.imageoptimizer', {
        options: {
            index: 0,
            isStop: false,
            confirmMessage: $.mage.__('Too many images will take a long time to optimize. Are you sure you want to optimize all images?')
        },

        _create: function () {
            this.initListener();
        },

        initListener: function () {
            var self = this;

            btnOptimize.on('click', function () {
                if (self.options.isEnabled === '0') {
                    alert({
                        title: $.mage.__('Optimize Image'),
                        content: $.mage.__('The module has been disabled.'),
                    });

                    return;
                }
                self.openConfirmModal();
            });
        },

        openConfirmModal: function () {
            var collection = this.options.collection.items;

            if (collection.length > 0) {
                this.getConfirmModal();
            } else {
                alert({
                    title: $.mage.__('Optimize Image'),
                    content: $.mage.__('You need to scan all images before starting optimization process.'),
                });
            }
        },

        getConfirmModal: function () {
            var self = this;

            confirmation({
                title: $.mage.__('Optimize Image'),
                content: this.options.confirmMessage,
                actions: {
                    confirm: function () {
                        var processModal = $('#mpimageoptimizer-modal');

                        processModal.modal({
                            'type': 'popup',
                            'title': 'Optimize Image',
                            'responsive': true,
                            'modalClass': 'mpimageoptimizer-modal-popup',
                            'buttons': [
                                {
                                    text: $.mage.__('Stop'),
                                    class: 'action-stop-optimize',
                                    click: function () {
                                        self.options.isStop = true;
                                        confirmation({
                                            content: $.mage.__('Are you sure you will stop image optimization?'),
                                            actions: {
                                                confirm: function () {
                                                    location.reload();
                                                },
                                                cancel: function () {
                                                    self.options.isStop = false;
                                                    self.loadAjax();
                                                }
                                            }
                                        });
                                    }
                                },
                                {
                                    text: $.mage.__('Close'),
                                    class: 'action-close-optimize',
                                    click: function () {
                                        location.reload();
                                    }
                                }
                            ]
                        });
                        processModal.modal('openModal');
                        self.optimizeImage();
                    }
                }
            });
        },

        optimizeImage: function () {
            this.options.index = 0;

            this.loadAjax();
        },

        loadAjax: function () {
            var self       = this,
                collection = this.options.collection.items,
                contentProcessing = $('.mpimageoptimizer-modal-content-processing'),
                item = collection[this.options.index],
                percent = 100 * (this.options.index + 1) / collection.length;

            if (this.options.isStop) {
                return;
            }

            if (this.options.index >= collection.length) {
                contentProcessing.text($.mage.__('Image optimization completed'));
                $('button.action-stop-optimize').hide();
                $('button.action-close-optimize').show();

                return;
            }
            contentProcessing.text(
                $.mage.__('Processing... ')
                + ' (' + (this.options.index + 1)
                + '/' + this.options.collection.items.length + ')'
            );
            this.options.index++;

            return $.ajax({
                url: this.options.url,
                data: {image_id: item.image_id}
            }).done(function (data) {
                self.getContent(percent, data.path, data.status);
                self.loadAjax(collection);
            }).fail(function (data) {
                self.getContent(percent, data.path, data.status);
                self.loadAjax(collection);
            });
        },

        getContent: function (percent, path, status) {
            $('#progress-bar-optimize').width(percent.toFixed(2) + '%');
            $('#mpimageoptimizer-modal-percent').text(percent.toFixed(2) + '%');
            $('#mpimageoptimizer-modal-content').append('<p>' + path + ': ' + '<strong>' + status + '</strong>' + '</p>');
        }
    });

    return $.mageplaza.imageoptimizer;
});