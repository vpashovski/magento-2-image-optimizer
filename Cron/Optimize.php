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

namespace Mageplaza\ImageOptimizer\Cron;

use Mageplaza\ImageOptimizer\Helper\Data;
use Mageplaza\ImageOptimizer\Model\Config\Source\Status;
use Mageplaza\ImageOptimizer\Model\ResourceModel\Image as ResourceImage;
use Mageplaza\ImageOptimizer\Model\ResourceModel\Image\Collection as ImageOptimizerCollection;
use Mageplaza\ImageOptimizer\Model\ResourceModel\Image\CollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Optimize
 * @package Mageplaza\ImageOptimizer\Cron
 */
class Optimize
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var ResourceImage
     */
    protected $resourceModel;

    /**
     * Collection Factory
     *
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Optimize constructor.
     *
     * @param Data $helperData
     * @param ResourceImage $resourceModel
     * @param CollectionFactory $collectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Data $helperData,
        ResourceImage $resourceModel,
        CollectionFactory $collectionFactory,
        LoggerInterface $logger
    ) {
        $this->helperData = $helperData;
        $this->resourceModel = $resourceModel;
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
    }

    public function execute()
    {
        if (!$this->helperData->isEnabled() || !$this->helperData->getCronJobConfig('enabled_optimize')) {
            return $this;
        }

        /** @var ImageOptimizerCollection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('status', Status::PENDING);
        $pathValues = $collection->getColumnValues('path');

//        foreach ($pathValues as $path) {
//
//        }
        return $this;
    }
}
