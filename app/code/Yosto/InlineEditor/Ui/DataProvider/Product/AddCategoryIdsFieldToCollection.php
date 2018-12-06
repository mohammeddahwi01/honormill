<?php
/**
 * Copyright © 2018 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\InlineEditor\Ui\DataProvider\Product;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;
use Yosto\InlineEditor\Observer\ProductCollectionLoadAfter;

class AddCategoryIdsFieldToCollection implements AddFieldToCollectionInterface
{
    /**
     * @inheritdoc
     */
    public function addField(Collection $collection, $field, $alias = null)
    {
        $collection->setData(ProductCollectionLoadAfter::IS_LOAD_CATEGORY_IDS, true);
    }
}