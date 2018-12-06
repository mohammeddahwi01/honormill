<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Controller\Adminhtml\Option\Value\Gallery;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\MediaStorage\Model\File\Uploader;
use MageWorx\OptionFeatures\Model\Product\Option\Value\Media\Config;

class Upload extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::products';

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var Config
     */
    protected $mediaConfig;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var WriteFactory
     */
    protected $directoryWriteFactory;

    /**
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param Config $mediaConfig
     * @param Filesystem $filesystem
     * @param WriteFactory $directoryWriteFactory
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        Config $mediaConfig,
        Filesystem $filesystem,
        WriteFactory $directoryWriteFactory
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->mediaConfig = $mediaConfig;
        $this->filesystem = $filesystem;
        $this->directoryWriteFactory = $directoryWriteFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        if ($this->getRequest()->getPost('hex')) {
            try {
                $hex = $this->getRequest()->getPost('hex');

                $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $filename = $hex . '.jpg';
                $fileNameCorrected = Uploader::getCorrectFileName($filename);
                $filePath = Uploader::getDispretionPath($filename) .
                    DIRECTORY_SEPARATOR .
                    $fileNameCorrected;
                $absolutePatToFile = $mediaDirectory->getAbsolutePath($this->mediaConfig->getBaseMediaPath());
                $path = $absolutePatToFile . $filePath;

                $image = imagecreatetruecolor(400, 400);
                list($r, $g, $b) = sscanf($hex, "%02x%02x%02x");
                $color = imagecolorallocate($image, $r, $g, $b);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                imagefill($image, 0, 0, $color);

                $dirPath = str_ireplace($fileNameCorrected, '', $path);
                /** @var \Magento\Framework\Filesystem\Directory\Write $directoryWrite */
                $this->directoryWriteFactory->create($dirPath);
                if (!file_exists($dirPath) && !is_dir($dirPath)) {
                    mkdir($dirPath, 0777, true);
                }
                imagejpeg($image, $path);

                $result = [
                    'name' => $fileNameCorrected,
                    'type' => mime_content_type($path),
                    'error' => 0,
                    'size' => filesize($path),
                    'file' => $filePath,
                    'url' => $this->mediaConfig->getMediaUrl($filePath),
                    'custom_media_type' => 'color',
                    'color' => $hex,
                ];
            } catch (\Exception $e) {
                $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            }
        } else {
            try {
                /** @var Uploader $uploader */
                $uploader = $this->_objectManager->create(
                    'Magento\MediaStorage\Model\File\Uploader',
                    ['fileId' => 'image']
                );
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                /** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
                $imageAdapter = $this->_objectManager->get('Magento\Framework\Image\AdapterFactory')->create();
                $uploader->addValidateCallback('catalog_product_image', $imageAdapter, 'validateUploadFile');
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
                $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
                    ->getDirectoryRead(DirectoryList::MEDIA);
                $result = $uploader->save($mediaDirectory->getAbsolutePath($this->mediaConfig->getBaseMediaPath()));

                $this->_eventManager->dispatch(
                    'mageworx_optionfeatures_upload_image_after',
                    ['result' => $result, 'action' => $this]
                );

                unset($result['tmp_name']);
                unset($result['path']);

                $result['url'] = $this->mediaConfig->getMediaUrl($result['file']);
                $result['custom_media_type'] = 'image';
            } catch (\Exception $e) {
                $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
            }
        }

        /** @var \Magento\Framework\Controller\Result\Raw $response */
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($result));

        return $response;
    }
}
