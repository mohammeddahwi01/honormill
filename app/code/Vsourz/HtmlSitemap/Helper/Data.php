<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vsourz\HtmlSitemap\Helper;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Contact base helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_ENABLED = 'htmlsitemap/htmlsitemap/enabled';

    /**
     * Check if enabled
     *
     * @return string|null
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if Preducts to show
     *
     * @return string|null
     */
    public function isProductEnabled()
    {
        return $this->scopeConfig->getValue(
            'htmlsitemap/general/product_enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isAccountPagesEnabled()
    {
        return $this->scopeConfig->getValue(
            'htmlsitemap/general/accountpages_enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isContactPageEnabled()
    {
        return $this->scopeConfig->getValue(
            'htmlsitemap/general/contact_enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getCustomLinks()
    {
        return $this->scopeConfig->getValue(
            'htmlsitemap/general/custom_links',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
