<?php
/**
 * Copyright © 2017 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */

namespace Yosto\InlineEditor\Ui\Component\Listing;

/**
 * Class Columns
 * @package Yosto\InlineEditor\Ui\Component\Listing
 */
class Columns extends \Magento\Ui\Component\Listing\Columns
{
    /**
     * Default columns max order
     */
    const DEFAULT_COLUMNS_MAX_ORDER = 100;

    /**
     * @var \Magento\Catalog\Ui\Component\Listing\Attribute\RepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var array
     */
    protected $filterMap = [
        'default' => 'text',
        'select' => 'select',
        'boolean' => 'select',
        'multiselect' => 'select',
        'date' => 'dateRange',
    ];

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Catalog\Ui\Component\ColumnFactory $columnFactory
     * @param \Magento\Catalog\Ui\Component\Listing\Attribute\RepositoryInterface $attributeRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Catalog\Ui\Component\ColumnFactory $columnFactory,
        \Magento\Catalog\Ui\Component\Listing\Attribute\RepositoryInterface $attributeRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->columnFactory = $columnFactory;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $acceptType = ['select', 'multiselect', 'date', 'boolean','textarea', 'text', 'price', 'weight'];
        $columnSortOrder = self::DEFAULT_COLUMNS_MAX_ORDER;
        foreach ($this->attributeRepository->getList() as $attribute) {
            $config = [];
            if (!isset($this->components[$attribute->getAttributeCode()])) {
                if (in_array($attribute->getFrontendInput(), $acceptType)) {
                    $config['editor'] = $this->getInputType($attribute->getFrontendInput());
                }
                $config['sortOrder'] = ++$columnSortOrder;
                if ($attribute->getIsFilterableInGrid()) {
                    $config['filter'] = $this->getFilterType($attribute->getFrontendInput());
                }
                $column = $this->columnFactory->create($attribute, $this->getContext(), $config);
                $column->prepare();
                $this->addComponent($attribute->getAttributeCode(), $column);
            }
        }
        parent::prepare();
    }

    /**
     * @param $frontendInput
     * @return string
     */
    public function getInputType($frontendInput)
    {
        switch ($frontendInput) {
            case 'select':
                return 'select';
            case 'multiselect':
                return 'select';
            case 'date':
                return 'date';
            case 'boolean':
                return 'boolean';
            case 'price':
                return 'text';
            case 'weight':
                return 'text';
            case 'textarea':
                return 'text';
            case 'text':
                return 'text';
        }


    }
    /**
     * Retrieve filter type by $frontendInput
     *
     * @param string $frontendInput
     * @return string
     */
    protected function getFilterType($frontendInput)
    {
        return isset($this->filterMap[$frontendInput]) ? $this->filterMap[$frontendInput] : $this->filterMap['default'];
    }
}