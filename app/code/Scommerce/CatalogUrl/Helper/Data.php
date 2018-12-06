<?php
/**
 * Scommerce_CategoryUrl Helper
 *
 * @category   Scommerce
 * @package    Scommerce_CatalogUrl
 * @author     Sommerce Mage <core@scommerce-mage.co.uk>
 */

namespace Scommerce\CatalogUrl\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 * @package Scommerce\CatalogUrl\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ENABLED              = 'scommerce_url/general/enabled';
    const LICENSE_KEY          = 'scommerce_url/general/license_key';
    const EXCLUDE_CATEGORIES   = 'scommerce_url/general/exclude_categories';
    const REMOVE_CATEGORY_PATH = 'scommerce_url/general/remove_category_path';

    /* @var \Scommerce\Core\Helper\Data */
    protected $coreHelper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Scommerce\Core\Helper\Data $coreHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Scommerce\Core\Helper\Data $coreHelper
    ) {
        parent::__construct($context);
        $this->coreHelper = $coreHelper;
    }

    /**
     * Is Catalog Url module active
     *
     * @return bool
     */
    public function isCatalogUrlActive()
    {
        $enabled = $this->isSetFlag(self::ENABLED);
        return $this->isCliMode() ? $enabled : $enabled && $this->isLicenseValid();
    }

    /**
     * Returns license key administration configuration option
     *
     * @return string
     */
    public function getLicenseKey()
    {
        return $this->getValue(self::LICENSE_KEY);
    }

    /**
     * Get flag for remove category path
     *
     * @return bool
     */
    public function getRemoveCategoryPath()
    {
        return $this->isSetFlag(self::REMOVE_CATEGORY_PATH);
    }

    /**
     * Get exclude categories ids
     *
     * @return string|null '1,4,6' etc
     */
    public function getExcludeCategories()
    {
        return $this->getValue(self::EXCLUDE_CATEGORIES);
    }

    /**
     * Returns whether license key is valid or not
     *
     * @return bool
     */
    public function isLicenseValid()
    {
        $sku = strtolower(str_replace('\\Helper\\Data','',str_replace('Scommerce\\','',get_class($this))));
        return $this->coreHelper->isLicenseValid($this->getLicenseKey(),$sku);
    }

    /**
     * Helper method for retrieve config value by path and scope
     *
     * @param string $path The path through the tree of configuration values, e.g., 'general/store_information/name'
     * @param string $scopeType The scope to use to determine config value, e.g., 'store' or 'default'
     * @param null|string $scopeCode
     * @return mixed
     */
    protected function getValue($path, $scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->getValue($path, $scopeType, $scopeCode);
    }

    /**
     * Helper method for retrieve config flag by path and scope
     *
     * @param string $path The path through the tree of configuration values, e.g., 'general/store_information/name'
     * @param string $scopeType The scope to use to determine config value, e.g., 'store' or 'default'
     * @param null|string $scopeCode
     * @return bool
     */
    protected function isSetFlag($path, $scopeType = ScopeInterface::SCOPE_STORE, $scopeCode = null)
    {
        return $this->scopeConfig->isSetFlag($path, $scopeType, $scopeCode);
    }

    /**
     * Check if running in cli mode
     *
     * @return bool
     */
    protected function isCliMode()
    {
        return php_sapi_name() === 'cli';
    }
}
