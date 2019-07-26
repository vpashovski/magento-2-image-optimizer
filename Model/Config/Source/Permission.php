<?php
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

namespace Mageplaza\ImageOptimizer\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Permission
 * @package Mageplaza\ImageOptimizer\Model\Config\Source
 */
class Permission implements OptionSourceInterface
{
    const READ = 1;
    const WRITE = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::READ, 'label' => __('Read')],
            ['value' => self::WRITE, 'label' => __('Write')],
        ];
    }
}