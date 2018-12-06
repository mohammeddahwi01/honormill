<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Model\Product\Option;

use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionBase\Model\AttributeInterface;

abstract class AbstractAttribute implements AttributeInterface
{
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var mixed
     */
    protected $entity;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return '';
    }

    /**
     * Check if attribute has own table in database
     *
     * @return bool
     */
    public function hasOwnTable()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getTableName($type = '')
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function collectData($entity, $options)
    {
        $this->entity = $entity;

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteOldData($data)
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataBeforeSave($object)
    {
        if (is_object($object)) {
            return $object->getData($this->getName());
        } elseif (is_array($object) && isset($object[$this->getName()])) {
            return $object[$this->getName()];
        }
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataForFrontend($object)
    {
        return [$this->getName() => $object->getData($this->getName())];
    }
}
