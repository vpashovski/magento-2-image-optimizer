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
 * @category  Mageplaza
 * @package   Mageplaza_ImageOptimizer
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

define([
    'underscore',
    'Magento_Ui/js/grid/columns/select'
], function (_, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Mageplaza_ImageOptimizer/grid/cells/text'
        },

        getStatusColor: function (row) {
            var color;

            switch (row.status){
                case 'pending':
                    color = '#f0ad4e';
                    break;
                case 'error':
                    color = '#d9534f';
                    break;
                case 'success':
                    color = '#5cb85c';
                    break;
                case 'skipped':
                    color = '#337ab7';
                    break;
            }

            return color;
        }
    });
});
