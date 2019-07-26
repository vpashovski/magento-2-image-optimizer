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

namespace Mageplaza\ImageOptimizer\Helper;

use Exception;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Mageplaza\ImageOptimizer\Model\Config\Source\Status;
use Mageplaza\ImageOptimizer\Model\ResourceModel\Image\Collection as ImageOptimizerCollection;
use Mageplaza\ImageOptimizer\Model\ResourceModel\Image\CollectionFactory;

/**
 * Class Data
 * @package Mageplaza\ImageOptimizer\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'mpimageoptimizer';

    /**
     * @var File
     */
    protected $driverFile;

    /**
     * @var AdapterFactory
     */
    protected $adapterFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param File $driverFile
     * @param AdapterFactory $imageFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        File $driverFile,
        AdapterFactory $imageFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->driverFile   = $driverFile;
        $this->adapterFactory = $imageFactory;
        $this->collectionFactory = $collectionFactory;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getOptimizeOptions($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getModuleConfig('optimize_options' . $code, $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getIncludeDirectories($storeId = null)
    {
        try {
            $directories = $this->unserialize($this->getModuleConfig('image_directory/include_directories', $storeId));
        } catch (Exception $e) {
            $directories = [];
        }

        $result = [];
        foreach ($directories as $key => $directory) {
            $result[$key] = $directory['path'];
        }

        return $result;
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getExcludeDirectories($storeId = null)
    {
        try {
            $directories = $this->unserialize($this->getModuleConfig('image_directory/exclude_directories', $storeId));
        } catch (Exception $e) {
            $directories = [];
        }

        $result = [];
        foreach ($directories as $key => $directory) {
            $result[$key] = $directory['path'];
        }

        return $result;
    }

    /**
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getCronJobConfig($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getModuleConfig('cron_job' . $code, $storeId);
    }

    /**
     * @return mixed
     */
    public function skipTransparentImage()
    {
        return $this->getOptimizeOptions('skip_transparent_img');
    }

    /**
     * @return array
     * @throws FileSystemException
     */
    public function scanFiles()
    {
        $images             = [];
        $includePatterns    = ['#.jpg#', '#.png#', '#.gif#', '#.tif#', '#.bmp#'];
        /** @var ImageOptimizerCollection $collection */
        $excludeDirectories   = $this->getExcludeDirectories();
        $excludeDirectories[] = 'pub/media/catalog/product/cache/';
        $includeDirectories   = $this->getIncludeDirectories();
        if (empty($includeDirectories)) {
            $includeDirectories = ['pub/media/'];
        }
        foreach ($includeDirectories as $directory) {
            if ($this->driverFile->isExists($directory)) {
                $files = $this->driverFile->readDirectoryRecursively($directory);
                foreach ($files as $file) {
                    foreach ($excludeDirectories as $excludeDirectory) {
                        if (preg_match('[' . $excludeDirectory . ']', $file)) {
                            continue 2;
                        }
                    }
                    foreach ($includePatterns as $pattern) {
                        if (preg_match($pattern, $file)
                            && !array_key_exists($file, $images)
                            && !$this->collectionFactory->create()->addFieldToFilter('path', $file)->getSize()
                        ) {
                            $images[$file] = [
                                'path' => $file,
                                'status' => Status::PENDING
                            ];
                        }
                    }
                }
            }
        }
        $images = array_merge($this->collectionFactory->create()->getData(), array_values($images));

        return $images;
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public function checkTransparentImage($path)
    {
        $image = $this->adapterFactory->create();
        $image->open($path);
        $width         = $image->getOriginalWidth();
        $height        = $image->getOriginalHeight();

        for ($i = 0; $i < $width; $i++) {
            for ($j = 0; $j < $height; $j++) {
                $rgba = $image->getColorAt($i, $j);
                if ($rgba['alpha'] === 127) {
                    return true;
                }
            }
        }

        return false;
    }
}
