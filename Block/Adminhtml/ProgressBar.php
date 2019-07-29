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

namespace Mageplaza\ImageOptimizer\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Mageplaza\ImageOptimizer\Helper\Data;
use Mageplaza\ImageOptimizer\Model\Config\Source\Status;
use Mageplaza\ImageOptimizer\Model\ResourceModel\Image\Collection;
use Mageplaza\ImageOptimizer\Model\ResourceModel\Image\CollectionFactory;

/**
 * Class ProgressBar
 * @package Mageplaza\ImageOptimizer\Block\Adminhtml
 */
class ProgressBar extends Template
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Status
     */
    protected $imageStatus;

    /**
     * ProgressBar constructor.
     *
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param Data $helperData
     * @param Status $imageStatus
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        Data $helperData,
        Status $imageStatus,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->helperData = $helperData;
        $this->imageStatus = $imageStatus;

        parent::__construct($context, $data);
    }

    /**
     * @return Collection
     */
    public function getImageCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * @return int
     */
    public function getTotalImage()
    {
        return $this->getImageCollection()->getSize();
    }

    /**
     * @param $status
     *
     * @return int
     */
    public function getTotalByStatus($status)
    {
        $collection = $this->getImageCollection();
        $collection->addFieldToFilter('status', $status);

        return $collection->getSize();
    }

    /**
     * @param $status
     *
     * @return string
     */
    public function getWidthByStatus($status)
    {
        $width = $this->getTotalByStatus($status)/$this->getTotalImage();

        return round($width * 100) . '%';
    }

    /**
     * @param $status
     *
     * @return string
     */
    public function getContent($status)
    {
        return $this->getWidthByStatus($status) . ' ' . __($status) . ' (' . $this->getTotalByStatus($status) . '/' . $this->getTotalImage() . ')';
    }
}
