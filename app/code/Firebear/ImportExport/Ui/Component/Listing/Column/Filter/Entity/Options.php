<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Ui\Component\Listing\Column\Filter\Entity;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    /**
     * @var \Firebear\ImportExport\Model\ExportFactory
     */
    protected $export;

    /**
     * @var \Magento\ImportExport\Model\Source\Export\Entity
     */
    protected $entity;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory
     */
    protected $collection;

    /**
     * @var \Firebear\ImportExport\Model\Export\Dependencies\Config
     */
    protected $config;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var \Firebear\ImportExport\Model\Source\Factory
     */
    protected $createFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Type\CollectionFactory
     */
    protected $typeCollection;

    /**
     * @var \Firebear\ImportExport\Model\Export\Product\Additional
     */
    protected $additional;

    /**
     * Options constructor.
     * @param \Firebear\ImportExport\Model\ExportFactory $export
     * @param \Firebear\ImportExport\Ui\Component\Listing\Column\Entity\Export\Options $entity
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $collectionFactory
     * @param \Firebear\ImportExport\Model\Export\Dependencies\Config $config
     * @param \Firebear\ImportExport\Model\Source\Factory $createFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Type\CollectionFactory $typeCollection
     * @param \Firebear\ImportExport\Model\Export\Product\Additional $additional
     */
    public function __construct(
        \Firebear\ImportExport\Model\ExportFactory $export,
        \Firebear\ImportExport\Ui\Component\Listing\Column\Entity\Export\Options $entity,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $collectionFactory,
        \Firebear\ImportExport\Model\Export\Dependencies\Config $config,
        \Firebear\ImportExport\Model\Source\Factory $createFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Type\CollectionFactory $typeCollection,
        \Firebear\ImportExport\Model\Export\Product\Additional $additional
    ) {
        $this->export = $export;
        $this->entity = $entity;
        $this->collection = $collectionFactory;
        $this->config = $config;
        $this->createFactory = $createFactory;
        $this->typeCollection = $typeCollection;
        $this->additional = $additional;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $entities = $this->entity->toOptionArray();
        $options = [];
        $data = $this->config->get();
        $extTypes = [];
        foreach ($data as $typeName => $type) {
            $extTypes[] = $typeName;
        }
        foreach ($entities as $item) {
            if ($item['value']) {
                if (!in_array($item['value'], $extTypes)) {
                    $options[$item['value']] = $this->getFromAttributes($item['value']);
                    if (in_array($item['value'], ['advanced_pricing', 'catalog_product'])) {
                        foreach ($this->uniqualFields() as $field) {
                            $options[$item['value']][] = $field;
                        }
                    }
                } else {
                    $options += $this->getFromTables();
                }
            }
        }
        $this->options = $options;

        return $this->options;
    }

    /**
     * @param $type
     * @return array
     */
    protected function getFromAttributes($type)
    {
        $options = [];
        if ($type == 'advanced_pricing') {
            $type = 'catalog_product';
        }

        $types = $this->typeCollection->create()->addFieldToFilter('entity_type_code', $type);
        if ($types->getSize()) {
            $collection = $this->collection->create()->addFieldToFilter(
                'entity_type_id',
                $types->getFirstItem()->getId()
            );
            foreach ($collection as $item) {
                $options[] = [
                    'value' => $item->getAttributeCode(),
                    'label' => $item->getFrontendLabel() ? $item->getFrontendLabel() : $item->getAttributeCode()
                ];
            }
        }

        return $options;
    }

    /**
     * @return array
     */
    protected function getFromTables()
    {
        $options = [];
        $data = $this->config->get();;
        foreach ($data as $typeName => $type) {
            $model = $this->createFactory->create($type['model']);
            $options += $model->getFieldsForFilter();
        }

        return $options;
    }

    protected function uniqualFields()
    {
        return $this->additional->toOptionArray();
    }
}
