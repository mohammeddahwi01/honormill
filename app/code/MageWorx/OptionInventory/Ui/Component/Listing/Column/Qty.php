<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionInventory\Ui\Component\Listing\Column;

use MageWorx\OptionInventory\Helper\Stock as HelperStock;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Backend\Helper\Data as BackendHelper;

/**
 * Class Qty
 * @package MageWorx\OptionInventory\Ui\Component\Listing\Column
 */
class Qty extends Column
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $backendHelper;

    /**
     * @var HelperStock
     */
    protected $helperStock;

    /**
     * Qty constructor.
     *
     * @param HelperStock $helperStock
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param BackendHelper $backendHelper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        HelperStock $helperStock,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        BackendHelper $backendHelper,
        array $components = [],
        array $data = []
    ) {
        $this->helperStock = $helperStock;
        $this->backendHelper = $backendHelper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $qty = isset($item['qty']) ? $item['qty'] : null;
                $productId = isset($item['product_id']) ? $item['product_id'] : null;
                if (!$qty || !$productId) {
                    continue;
                }
                $item[$this->getData('name')] = $this->helperStock->floatingQty($qty, $productId);
            }
        }

        return $dataSource;
    }
}
