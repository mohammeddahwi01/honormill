<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Model\Attribute\OptionValue;

use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use MageWorx\OptionBase\Model\AttributeInterface;
use MageWorx\OptionFeatures\Model\Image as ImageModel;
use MageWorx\OptionFeatures\Model\ResourceModel\Image\Collection as ImageCollection;
use MageWorx\OptionFeatures\Model\ImageFactory;
use MageWorx\OptionBase\Model\Product\Option\AbstractAttribute;

class Image extends AbstractAttribute implements AttributeInterface
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var ImageFactory
     */
    protected $imageFactory;

    /**
     * @var ImageCollection
     */
    protected $imageCollection;

    /**
     * @param ResourceConnection $resource
     * @param ImageFactory $imageFactory
     * @param ImageCollection $imageCollection
     * @param Helper $helper
     */
    public function __construct(
        ResourceConnection $resource,
        ImageFactory $imageFactory,
        ImageCollection $imageCollection,
        Helper $helper
    ) {
        $this->helper = $helper;
        $this->imageFactory = $imageFactory;
        $this->imageCollection = $imageCollection;
        parent::__construct($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return Helper::KEY_IMAGE;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOwnTable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTableName($type = '')
    {
        $map = [
            'product' => ImageModel::TABLE_NAME,
            'group' => ImageModel::OPTIONTEMPLATES_TABLE_NAME
        ];
        if (!$type) {
            return $map[$this->entity->getType()];
        }
        return $map[$type];
    }

    /**
     * {@inheritdoc}
     */
    public function collectData($entity, $options)
    {
        $this->entity = $entity;

        $images = [];
        foreach ($options as $option) {
            if (empty($option['values'])) {
                continue;
            }
            foreach ($option['values'] as $value) {
                if (!isset($value[Helper::KEY_IMAGE])) {
                    continue;
                }
                $data = json_decode($value[Helper::KEY_IMAGE], true);
                if (json_last_error() == JSON_ERROR_NONE) {
                    $images[$value['mageworx_option_type_id']] = $data;
                } else {
                    parse_str($value[Helper::KEY_IMAGE], $images[$value['mageworx_option_type_id']]);
                }
            }
        }

        return $this->collectImages($images);
    }

    /**
     * Save images
     *
     * @param $items
     * @return array
     */
    protected function collectImages($items)
    {
        $data = [];
        foreach ($items as $imageKey => $images) {
            if (isset($images['optionfeatures']['media_gallery']['images'])) {
                $data['delete'][] = [
                    ImageModel::COLUMN_MAGEWORX_OPTION_TYPE_ID => $imageKey
                ];

                foreach ($images['optionfeatures']['media_gallery']['images'] as $imageItem) {
                    if (!empty($imageItem['removed'])) {
                        continue;
                    }
                    $imageText = $this->removeSpecialChars($imageItem['label']);
                    $imageData = [
                        'mageworx_option_type_id' => $imageKey,
                        'sort_order' => $imageItem['position'],
                        'title_text' => htmlspecialchars($imageText, ENT_COMPAT, 'UTF-8', false),
                        'media_type' => $imageItem['custom_media_type'],
                        'color' => $imageItem['color'],
                        'value' => $imageItem['file'],
                        ImageModel::COLUMN_HIDE_IN_GALLERY => $imageItem[ImageModel::COLUMN_HIDE_IN_GALLERY],
                    ];
                    foreach ($this->helper->getImageAttributes() as $attributeCode => $attributeLabel) {
                        if (isset($images[$attributeCode])
                            && $imageItem['file']
                            && $images[$attributeCode] == $imageItem['file']
                        ) {
                            $imageData[$attributeCode] = 1;
                        } else {
                            $imageData[$attributeCode] = 0;
                        }
                    }
                    $data['save'][] = $imageData;
                }
            } elseif (!empty($images) && !isset($images['base_image'])) {
                $data['delete'][] = [
                    ImageModel::COLUMN_MAGEWORX_OPTION_TYPE_ID => $imageKey
                ];

                foreach ($images as $imageItem) {
                    if (!empty($imageItem['removed'])) {
                        continue;
                    }
                    $imageText = $this->removeSpecialChars($imageItem['title_text']);
                    $imageData = [
                        'mageworx_option_type_id' => $imageKey,
                        'sort_order' => $imageItem['sort_order'],
                        'title_text' => htmlspecialchars($imageText, ENT_COMPAT, 'UTF-8', false),
                        'media_type' => $imageItem['custom_media_type'],
                        'color' => $imageItem['color'],
                        'value' => $imageItem['value'],
                        ImageModel::COLUMN_HIDE_IN_GALLERY => $imageItem[ImageModel::COLUMN_HIDE_IN_GALLERY],
                    ];
                    foreach ($this->helper->getImageAttributes() as $attributeCode => $attributeLabel) {
                        $imageData[$attributeCode] = $imageItem[$attributeCode];
                    }
                    $data['save'][] = $imageData;
                }
            }
        }
        return $data;
    }

    /**
     * Delete old option value images
     *
     * @param $data
     * @return void
     */
    public function deleteOldData($data)
    {
        $mageworxOptionValueIds = [];
        foreach ($data as $dataItem) {
            $mageworxOptionValueIds[] = $dataItem[ImageModel::COLUMN_MAGEWORX_OPTION_TYPE_ID];
        }
        if (!$mageworxOptionValueIds) {
            return;
        }
        $tableName = $this->resource->getTableName($this->getTableName());
        $conditions = ImageModel::COLUMN_MAGEWORX_OPTION_TYPE_ID .
            " IN (" . "'" . implode("','", $mageworxOptionValueIds) . "'" . ")";
        $this->resource->getConnection()->delete(
            $tableName,
            $conditions
        );
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataForFrontend($object)
    {
        $imagesData = [];
        $tooltipImage = '';
        if (!empty($object->getTooltipImage())) {
            $tooltipImage = $this->helper->getThumbImageUrl(
                $object->getTooltipImage(),
                Helper::IMAGE_MEDIA_ATTRIBUTE_TOOLTIP_IMAGE
            );
        };
        $imagesData['tooltip_image'] = $tooltipImage;
        return [$this->getName() => $imagesData];
    }

    /**
     * Remove backslashes and new line symbols from string
     *
     * @param $string string
     * @return string
     */
    public function removeSpecialChars($string)
    {
        $string = str_replace(["\n","\r"], '', $string);
        return stripslashes($string);
    }
}
