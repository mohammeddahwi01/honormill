<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Ui\Component\Listing\Column\Filter;

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
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
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
     * @var \Firebear\ImportExport\Helper\Data
     */
    protected $helper;

    /**
     * @var \Firebear\ImportExport\Model\Export\Product\Additional
     */
    protected $additional;

    /**
     * Options constructor.
     * @param \Firebear\ImportExport\Model\ExportFactory $export
     * @param \Firebear\ImportExport\Ui\Component\Listing\Column\Entity\Export\Options $entity
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $collection
     * @param \Firebear\ImportExport\Model\Export\Dependencies\Config $config
     * @param \Firebear\ImportExport\Model\Source\Factory $createFactory
     * @param \Firebear\ImportExport\Helper\Data $helper
     * @param \Firebear\ImportExport\Model\Export\Product\Additional $additional
     */
    public function __construct(
        \Firebear\ImportExport\Model\ExportFactory $export,
        \Firebear\ImportExport\Ui\Component\Listing\Column\Entity\Export\Options $entity,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $collection,
        \Firebear\ImportExport\Model\Export\Dependencies\Config $config,
        \Firebear\ImportExport\Model\Source\Factory $createFactory,
        \Firebear\ImportExport\Helper\Data $helper,
        \Firebear\ImportExport\Model\Export\Product\Additional $additional
    ) {
        $this->export = $export;
        $this->entity = $entity;
        $this->collection = $collection;
        $this->config = $config;
        $this->createFactory = $createFactory;
        $this->helper = $helper;
        $this->additional = $additional;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->getFromAttributes();
        $options += $this->getFromTables();
        $this->options = $options;

        return $this->options;
    }

    /**
     * @return array
     */
    protected function getFromAttributes()
    {
        $options = [];
        $options['attr'] = [];
        $collection = $this->collection;
        foreach ($collection as $item) {
            $select = [];
            if ($item->getFrontendInput() == \Magento\ImportExport\Model\Export::FILTER_TYPE_SELECT) {
                if ($optionsAttr = $item->getSource()->getAllOptions()) {
                    foreach ($optionsAttr as $option) {
                        $select[] = ['label' => $option['label'], 'value' => $option['value']];
                    }
                }
            }
            $type = $item->getFrontendInput();

            if ($item->getFrontendInput() != 'select'
                && in_array($item->getBackendType(), ['int', 'decimal'])) {
                $type = 'int';
            }
            if (in_array($item->getFrontendInput(), ['textarea', 'media_image', 'image', 'multiline', 'gallery'])) {
                $type = 'text';
            }
            if (in_array($item->getFrontendInput(), ['hidden'])) {
                $type = 'not';
            }
            if (in_array($item->getFrontendInput(), ['multiselect'])) {
                $type = 'select';
            }
            if ($item->getFrontendInput() == 'boolean') {
                $type = 'select';
                $select[] = ['label' => __('Yes'), 'value' => 1];
                $select[] = ['label' => __('No'), 'value' => 0];
            }
            if ($item->getAttributeCode() == 'category_ids') {
                $type = 'int';
            }

            $options['attr'][] =
                [
                    'field' => $item->getAttributeCode(),
                    'type' => $type,
                    'select' => $select
                ];
        }

        foreach ($this->additional->getAdditionalFields() as $field) {
            $options['attr'][] = $field;
        }


        return $options;
    }

    /**
     * @return array
     */
    protected function getFromTables()
    {
        $options = [];
        $data = $this->config->get();
        foreach ($data as $typeName => $type) {
            $model = $this->createFactory->create($type['model']);
            $options += $model->getFieldColumns();
        }

        return $options;
    }
}
