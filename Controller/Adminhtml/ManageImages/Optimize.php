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

namespace Mageplaza\ImageOptimizer\Controller\Adminhtml\ManageImages;

use CURLFile;
use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Mageplaza\ImageOptimizer\Controller\Adminhtml\Image;
use Mageplaza\ImageOptimizer\Helper\Data;
use Mageplaza\ImageOptimizer\Model\Config\Source\Quality;
use Mageplaza\ImageOptimizer\Model\Config\Source\Status;
use Mageplaza\ImageOptimizer\Model\ImageFactory;
use Mageplaza\ImageOptimizer\Model\ResourceModel\Image as ResourceImage;
use Mageplaza\ImageOptimizer\Model\ResourceModel\Image\CollectionFactory;
use Psr\Log\LoggerInterface;
use Zend_Http_Client;
use Zend_Http_Response;

/**
 * Class Optimize
 * @package Mageplaza\ImageOptimizer\Controller\Adminhtml\ManageImages
 */
class Optimize extends Image
{
    /**
     * @var CurlFactory
     */
    protected $curlFactory;

    /**
     * Optimize constructor.
     *
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param ImageFactory $imageFactory
     * @param ResourceImage $resourceModel
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     * @param Data $helperData
     * @param LoggerInterface $logger
     * @param CurlFactory $curlFactory
     */
    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        ImageFactory $imageFactory,
        ResourceImage $resourceModel,
        CollectionFactory $collectionFactory,
        Filter $filter,
        Data $helperData,
        LoggerInterface $logger,
        CurlFactory $curlFactory
    ) {
        $this->curlFactory = $curlFactory;

        parent::__construct(
            $context,
            $resultForwardFactory,
            $resultPageFactory,
            $imageFactory,
            $resourceModel,
            $collectionFactory,
            $filter,
            $helperData,
            $logger
        );
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->helperData->isEnabled() && !$this->getRequest()->getParam('isAjax')) {
            $this->messageManager->addErrorMessage(__('The module has been disabled.'));

            return $resultRedirect->setPath('*/*/');
        }

        $imageId = $this->getRequest()->getParam('image_id');
        try {
            /** @var \Mageplaza\ImageOptimizer\Model\Image $model */
            $model = $this->imageFactory->create();
            if ($imageId) {
                $this->resourceModel->load($model, $imageId);
                if ($imageId !== $model->getId()) {
                    if ($this->getRequest()->getParam('isAjax')) {
                        return $this->getResponse()->representJson(Data::jsonEncode(['status' => Status::ERROR]));
                    }
                    $this->messageManager->addErrorMessage(__('The wrong image is specified.'));

                    return $resultRedirect->setPath('*/*/');
                }

                if ($model->getData('status') === Status::SUCCESS) {
                    if ($this->getRequest()->getParam('isAjax')) {
                        return $this->getResponse()->representJson(Data::jsonEncode([
                            'status' => 'optimized',
                            'path'   => $model->getData('path')
                        ]));
                    }
                    $this->messageManager->addErrorMessage(__('The image(s) had already been optimized previously'));

                    return $resultRedirect->setPath('*/*/');
                }

                if ($model->getData('status') === Status::SKIPPED) {
                    if ($this->getRequest()->getParam('isAjax')) {
                        return $this->getResponse()->representJson(Data::jsonEncode($model->getData()));
                    }
                    $this->messageManager->addErrorMessage(__('The image(s) are skipped.'));

                    return $resultRedirect->setPath('*/*/');
                }
            }
            $result = $this->optimizeImage($model->getData('path'));
            $data   = [
                'optimize_size' => isset($result['error']) ? '' : $result['dest_size'],
                'percent'       => isset($result['error']) ? '' : $result['percent'],
                'status'        => isset($result['error']) ? Status::ERROR : Status::SUCCESS,
                'message'       => isset($result['error']) ? $result['error_long'] : ''
            ];
            $model->addData($data);
            $this->resourceModel->save($model);
            if ($this->getRequest()->getParam('isAjax')) {
                return $this->getResponse()->representJson(Data::jsonEncode($model->getData()));
            }
            $this->messageManager->addSuccessMessage(__('Image(s) have been optimized successfully.'));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->critical($e->getMessage());
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param $path
     *
     * @return array|mixed
     * @throws Exception
     */
    public function optimizeImage($path)
    {
        $result = [];
        if (!$this->helperData->fileExists($path)) {
            $result = [
                'error' => true,
                'error_long' => __('file does not exist')
            ];

            return $result;
        }

        $curl   = $this->curlFactory->create();
        $url    = 'http://api.resmush.it/?qlty=' . $this->getQuality();
        try {
            $params = $this->getParams($path);
            $curl->write(Zend_Http_Client::POST, $url, '1.1', [], $params);
            $resultCurl = $curl->read();
            if (!empty($resultCurl)) {
                $responseBody = Zend_Http_Response::extractBody($resultCurl);
                $result       += Data::jsonDecode($responseBody);
            }
        } catch (Exception $e) {
            $result['error']      = true;
            $result['error_long'] = $e->getMessage();
        }
        $curl->close();

        if (isset($result['dest'])) {
            $this->helperData->saveImage($result['dest'], $path);
        }

        return $result;
    }

    /**
     * @param $path
     *
     * @return array
     */
    public function getParams($path)
    {
        $mime   = mime_content_type($path);
        $info   = $this->helperData->getPathInfo($path);
        $name   = $info['basename'];
        $output = new CURLFile($path, $mime, $name);
        $params = [
            'files' => $output
        ];

        return $params;
    }

    /**
     * @return int|mixed
     */
    public function getQuality()
    {
        $quality = 100;
        if ($this->helperData->getOptimizeOptions('image_quality') === Quality::CUSTOM) {
            $quality = $this->helperData->getOptimizeOptions('quality_percent');
        }

        return $quality;
    }
}
