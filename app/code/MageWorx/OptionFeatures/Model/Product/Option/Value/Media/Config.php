<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Model\Product\Option\Value\Media;

use Magento\Catalog\Model\Product\Media\ConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config implements ConfigInterface
{
    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $file
     * @return string
     */
    public function getMediaPath($file)
    {
        return $this->getBaseMediaPath() . '/' . $this->prepareFile($file);
    }

    /**
     * Filesystem directory path of option value images
     * relatively to media folder
     *
     * @return string
     */
    public function getBaseMediaPath()
    {
        return 'mageworx/optionfeatures/product/option/value';
    }

    /**
     * @param string $file
     * @return string
     */
    protected function prepareFile($file)
    {
        return ltrim(str_replace('\\', '/', $file), '/');
    }

    /**
     * @param string $file
     * @return string
     */
    public function getMediaUrl($file)
    {
        return $this->getBaseMediaUrl() . '/' . $this->prepareFile($file);
    }

    /**
     * @return string
     */
    public function getBaseMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(
            UrlInterface::URL_TYPE_MEDIA
        ) . $this->getBaseMediaPath();
    }

    /**
     * @param string $file
     * @return string
     */
    public function getUrl($file)
    {
        return rtrim($this->getBaseUrl(), '/') . '/' . ltrim($this->prepareFile($file), '/');
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(
            UrlInterface::URL_TYPE_MEDIA
        );
    }
}
