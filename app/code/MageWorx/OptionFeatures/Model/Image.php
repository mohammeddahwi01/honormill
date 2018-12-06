<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionFeatures\Model;

use Magento\Framework\Model\AbstractExtensibleModel;

class Image extends AbstractExtensibleModel
{
    const TABLE_NAME = 'mageworx_optionfeatures_option_type_image';
    const OPTIONTEMPLATES_TABLE_NAME = 'mageworx_optiontemplates_group_option_type_image';

    const COLUMN_OPTION_TYPE_IMAGE_ID = 'option_type_image_id';
    const COLUMN_MAGEWORX_OPTION_TYPE_ID = 'mageworx_option_type_id';
    const COLUMN_MEDIA_TYPE = 'media_type';
    const COLUMN_VALUE = 'value';
    const COLUMN_TITLE_TEXT = 'title_text';
    const COLUMN_SORT_ORDER = 'sort_order';
    const COLUMN_BASE_IMAGE = 'base_image';
    const COLUMN_TOOLTIP_IMAGE = 'tooltip_image';
    const COLUMN_DISPLAY_ON_HOVER = 'display_on_hover';
    const COLUMN_COLOR = 'color';
    const COLUMN_REPLACE_MAIN_GALLERY_IMAGE = 'replace_main_gallery_image';
    const COLUMN_HIDE_IN_GALLERY = 'disabled';

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('MageWorx\OptionFeatures\Model\ResourceModel\Image');
        $this->setIdFieldName('option_type_image_id');
    }
}
