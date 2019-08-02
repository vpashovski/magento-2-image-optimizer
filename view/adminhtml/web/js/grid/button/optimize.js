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
    'mage/storage',
    'Magento_Ui/js/modal/modal'
], function ($, confirmation) {
    "use strict";
    var btnOptimize = $('#optimize_image');

    $.widget('mageplaza.imageoptimizer', {
        options: {
            index: 0
        },

        _create: function () {
            this.initListener();
        },

        initListener: function () {
            var self = this;

            btnOptimize.on('click', function () {
                confirmation({
                    title: $.mage.__('Optimize Image'),
                    content: $.mage.__('Too many images will take a long time to optimize. Are you sure you want to optimize all images?'),
                    actions: {
                        confirm: function () {
                            var processModal = $('#mpimageoptimizer-modal');

                            processModal.modal({
                                'type': 'popup',
                                'title': 'Optimize Image',
                                'responsive': true,
                                'buttons': [{
                                    text: $.mage.__('Stop'),
                                    class: 'action-stop-optimize',
                                    click: function () {
                                        confirmation({
                                            content: $.mage.__('Are you sure you will stop image optimization?'),
                                            actions: {
                                                confirm: function(){
                                                    location.reload();
                                                }
                                            }
                                        });
                                    }
                                }]
                            });
                            processModal.modal('openModal');
                            self.optimizeImage();
                        }
                    }
                });
            });
        },

        optimizeImage: function () {
            this.options.index = 0;

            this.loadAjax();
        },

        loadAjax: function () {
            var self =  this,
                collection = this.options.collection.items,
                item = collection[this.options.index],
                percent;

            if (this.options.index === 0) {
                percent = 100 / collection.length;
            } else {
                percent = 100 * this.options.index / collection.length;
            }

            this.options.index++;
            if (this.options.index >= collection.length) {
                return;
            }

            return $.ajax({
                url: this.options.url,
                data: {
                    image_id: item.image_id,
                    path: item.path
                }
            }).done(function (data) {
                self.getContent(percent, item.path, data.status);
                self.loadAjax();
            }).fail(function (data) {
                self.getContent(percent, item.path, data.status);
                self.loadAjax();
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