<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionInventory\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * OptionInventory Data Helper.
 * @package MageWorx\OptionInventory\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const KEY_QTY = 'qty';
    const KEY_MANAGE_STOCK = 'manage_stock';

    /**
     * XML config path show option qty on frontend
     */
    const XML_PATH_DISPLAY_OPTION_INVENTORY_ON_FRONTEND =
        'mageworx_apo/optioninventory/display_option_inventory_on_frontend';

    /**
     * XML config path show out of stock message
     */
    const XML_PATH_DISPLAY_OUT_OF_STOCK_MESSAGE = 'mageworx_apo/optioninventory/display_out_of_stock_message';

    /**
     * XML config path disable out of stock options
     */
    const XML_PATH_DISABLE_OUT_OF_STOCK_OPTIONS = 'mageworx_apo/optioninventory/disable_out_of_stock_options';

    /**
     * Check if 'show option qty on frontend' are enable
     *
     * @param int $storeId
     * @return bool
     */
    public function isDisplayOptionInventoryOnFrontend($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DISPLAY_OPTION_INVENTORY_ON_FRONTEND,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if 'show out of stock message' are enable
     *
     * @param int $storeId
     * @return bool
     */
    public function isDisplayOutOfStockMessage($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DISPLAY_OUT_OF_STOCK_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if 'disable out of stock options' are enable
     *
     * @param int $storeId
     * @return bool
     */
    public function isDisabledOutOfStockOptions($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DISABLE_OUT_OF_STOCK_OPTIONS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
