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

namespace Mageplaza\ImageOptimizer\Console\Command;

use Exception;
use Mageplaza\ImageOptimizer\Helper\Data;
use Mageplaza\ImageOptimizer\Model\Config\Source\Status;
use Mageplaza\ImageOptimizer\Model\ResourceModel\Image as ResourceImage;
use Mageplaza\ImageOptimizer\Model\ResourceModel\Image\Collection as ImageOptimizerCollection;
use Mageplaza\ImageOptimizer\Model\ResourceModel\Image\CollectionFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Optimize
 * @package Mageplaza\ImageOptimizer\Console\Command
 */
class Optimize extends Command
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
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('mpimageoptimizer:optimize');
        $this->setDescription('Image Optimizer console command.');

        parent::configure();
    }

    /**
     * Optimize constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param ResourceImage $resourceModel
     * @param Data $helperData
     * @param LoggerInterface $logger
     * @param string|null $name
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ResourceImage $resourceModel,
        Data $helperData,
        LoggerInterface $logger,
        string $name = null
    ) {
        parent::__construct($name);

        $this->collectionFactory = $collectionFactory;
        $this->resourceModel     = $resourceModel;
        $this->helperData        = $helperData;
        $this->logger            = $logger;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return $this|int|null
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->helperData->isEnabled()) {
            return $this;
        }
        /** @var ImageOptimizerCollection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('status', Status::PENDING);
        $collection->setPageSize($this->helperData->getCronJobConfig('limit_number'));

        foreach ($collection as $image) {
            try {
                $result = $this->helperData->optimizeImage($image->getData('path'));
                $data   = [
                    'optimize_size' => isset($result['error']) ? '' : $result['dest_size'],
                    'percent'       => isset($result['error']) ? '' : $result['percent'],
                    'status'        => isset($result['error']) ? Status::ERROR : Status::SUCCESS,
                    'message'       => isset($result['error']) ? $result['error_long'] : ''
                ];
                $image->addData($data);
                $this->resourceModel->save($image);
                $output->writeln(__('<info>Image %1 have been optimized successfully.</info>', $image->getData('path')));
            } catch (Exception $e) {
                $output->writeln('<error>Problem occurred during optimization.</error>');
                $this->logger->critical($e->getMessage());
            }
        }

        return $this;
    }
}
