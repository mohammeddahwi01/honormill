<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Model\ResourceModel;

class CollectionUpdaterRegistry
{
    /**
     * Current product or group type
     *
     * @var string
     */
    protected $currentEntityType;

    /**
     * Current product/group Id
     *
     * @var int
     */
    protected $currentEntityId;

    /**
     * Current product row id (actual only if Magento EE)
     *
     * @var int
     */
    protected $currentRowId;

    /**
     * Array of product/group option's IDs
     *
     * @var array
     */
    protected $optionIds;

    /**
     * Array of product/group option value's IDs
     *
     * @var array
     */
    protected $optionValueIds;

    /**
     * @param string $currentEntityType
     * @param int $currentEntityId
     * @param array $optionIds
     * @param array $optionValueIds
     */
    public function __construct(
        $currentEntityType = '',
        $currentEntityId = 0,
        $optionIds = [],
        $optionValueIds = []
    ) {
        $this->currentEntityType = $currentEntityType;
        $this->currentEntityId = $currentEntityId;
        $this->optionIds = $optionIds;
        $this->optionValueIds = $optionValueIds;
    }

    /**
     * Set current product or group entity id
     *
     * @param int $entityId
     */
    public function setCurrentEntityId($entityId)
    {
        $this->currentEntityId = $entityId;
    }

    /**
     * Get current product or group entity id
     *
     * @return int
     */
    public function getCurrentEntityId()
    {
        return $this->currentEntityId;
    }

    /**
     * Set current product row id
     *
     * @param int $entityId
     */
    public function setCurrentRowId($entityId)
    {
        $this->currentRowId = $entityId;
    }

    /**
     * Get current product row id
     *
     * @return int
     */
    public function getCurrentRowId()
    {
        return $this->currentRowId;
    }

    /**
     * Set current product or group entity type
     *
     * @param string $entityType
     */
    public function setCurrentEntityType($entityType)
    {
        $this->currentEntityType = $entityType;
    }

    /**
     * Get current product or group entity type
     *
     * @return string
     */
    public function getCurrentEntityType()
    {
        return $this->currentEntityType;
    }

    /**
     * Set array of product/group option's IDs
     *
     * @param array $optionIds
     */
    public function setOptionIds($optionIds)
    {
        $this->optionIds = $optionIds;
    }

    /**
     * Get array of product/group option's IDs
     *
     * @return array
     */
    public function getOptionIds()
    {
        return $this->optionIds;
    }

    /**
     * Set array of product/group option value's IDs
     *
     * @param array $optionValueIds
     */
    public function setOptionValueIds($optionValueIds)
    {
        $this->optionValueIds = $optionValueIds;
    }

    /**
     * Get array of product/group option value's IDs
     *
     * @return array
     */
    public function getOptionValueIds()
    {
        return $this->optionValueIds;
    }
}
