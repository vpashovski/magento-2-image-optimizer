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
    'Magento_Ui/js/grid/tree-massactions',
    'Magento_Ui/js/modal/confirm',
    'Mageplaza_ImageOptimizer/js/grid/button/optimize',
    'Magento_Ui/js/modal/modal'
], function ($, TreeMassactions, confirmation, imageOptimizer) {
    'use strict';

    return TreeMassactions.extend({
        optimizeImage: function (action, data) {
            var selectedImages = this.getSelectedImages(action, data).selected,
                collection     = {items: []},
                total = selectedImages.length,
                confirmMessage = $.mage.__('Too many images will take a long time to optimize. Are you sure you want to optimize the selected image(s)?')
                    + ' (' + total + ' record' + (total > 1 ? 's' : '') + ')';

            $.each(selectedImages, function (index, value) {
                collection.items[index] = {image_id: value};
            });

            imageOptimizer({url: action.url, collection: collection, confirmMessage: confirmMessage}).openConfirmModal();
        },

        getSelectedImages: function (action, data) {
            var itemsType  = data.excludeMode ? 'excluded' : 'selected',
                selections = {};

            selections[itemsType] = data[itemsType];

            if (!selections[itemsType].length) {
                selections[itemsType] = false;
            }

            _.extend(selections, data.params || {});

            return selections;
        }
    });
});