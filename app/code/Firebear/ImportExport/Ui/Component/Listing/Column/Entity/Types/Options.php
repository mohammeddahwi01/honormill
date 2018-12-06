<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Ui\Component\Listing\Column\Entity\Types;

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
     * Options constructor.
     * @param \Firebear\ImportExport\Model\ExportFactory $export
     * @param \Magento\ImportExport\Model\Source\Export\Entity $entity
     */
    public function __construct(
        \Firebear\ImportExport\Model\ExportFactory $export,
        \Firebear\ImportExport\Ui\Component\Listing\Column\Entity\Export\Options $entity
    ) {
        $this->export = $export;
        $this->entity = $entity;
    }

    /**
     * @var array
     */
    protected $options;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $entities = $this->entity->toOptionArray();
        $options  = [];
        foreach ($entities as $key => $item) {
            $childs = [];
            if ($item['value']) {
                $fields = $this->export->create()->setData(['entity' => $item['value']])->getFields();
                $childs[] = ['label' => $item['label'], 'value' => $item['value']];
                foreach ($fields as $name => $field) {
                    if (isset($field['optgroup-name']) && $name != $item['value']) {
                        $childs[] = ['label' => $field['label'], 'value' => $name, 'dep' => $field['optgroup-name']];
                    }
                }
                $options[$item['value']] = $childs;
            }
        }

        $this->options = $options;

        return $this->options;
    }
}
