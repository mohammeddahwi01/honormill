<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model\Import;

class Uploader extends \Magento\CatalogImportExport\Model\Import\Uploader
{
    /**
     * Uploader constructor.
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb
     * @param \Magento\MediaStorage\Helper\File\Storage $coreFileStorage
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $validator
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filesystem\File\ReadFactory $readFactory
     * @param \Firebear\ImportExport\Model\Filesystem\File\ReadFactory $fireReadFactory
     * @param null $filePath
     */
    public function __construct(
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb,
        \Magento\MediaStorage\Helper\File\Storage $coreFileStorage,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $validator,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\File\ReadFactory $readFactory,
        \Firebear\ImportExport\Model\Filesystem\File\ReadFactory $fireReadFactory,
        $filePath = null
    ) {
        parent::__construct(
            $coreFileStorageDb,
            $coreFileStorage,
            $imageFactory,
            $validator,
            $filesystem,
            $readFactory,
            $filePath
        );

        $this->_readFactory = $fireReadFactory;
    }
}
