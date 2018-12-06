<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionDependency\Model\Attribute\OptionValue;

use MageWorx\OptionDependency\Helper\Data as Helper;
use MageWorx\OptionDependency\Model\Attribute\TitleId as DefaultTitleId;

class TitleId extends DefaultTitleId
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return Helper::KEY_OPTION_TYPE_TITLE_ID;
    }
}
