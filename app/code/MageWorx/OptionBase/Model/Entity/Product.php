<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Model\Entity;

class Product extends Base
{
    protected $entityType = 'product';

    /**
     * @return string
     */
    public function getType()
    {
        return $this->entityType;
    }

    /**
     * Get product id
     * @return string
     */
    public function getDataObjectId()
    {
        return $this->getBaseHelper()->isEnterprise() ?
            $this->getDataObject()->getRowId() :
            $this->getDataObject()->getId();
    }

    /**
     * Get product field name
     * @return string
     */
    public function getDataObjectIdName()
    {
        return 'product_id';
    }

    /**
     * @inherit
     */
    protected function getGroupMageworxOptionId($option)
    {
        return '';
    }

    /**
     * @inherit
     */
    protected function getGroupMageworxOptionTypeId($value)
    {
        return '';
    }
}
