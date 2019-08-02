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

        /**
         *
         * @param record
         * @returns {string}
         */
        getLabel: function (record) {
            var result;

            switch (record[this.index]){
                case 'pending':
                    result = '<span class="mp-grid-severity-pending"><span>PENDING</span></span>';
                    break;
                case 'error':
                    result = '<span class="mp-grid-severity-error"><span>ERROR</span></span>';
                    break;
                case 'success':
                    result = '<span class="mp-grid-severity-success"><span>SUCCESS</span></span>';
                    break;
                case 'skipped':
                    result = '<span class="mp-grid-severity-skip"><span>SKIPPED</span></span>';
                    break;
            }

            return result;
        },
    });
});
