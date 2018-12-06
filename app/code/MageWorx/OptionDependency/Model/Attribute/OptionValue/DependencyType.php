<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionDependency\Model\Attribute\OptionValue;

use MageWorx\OptionDependency\Helper\Data as Helper;
use MageWorx\OptionDependency\Model\Attribute\DependencyType as DefaultDependencyType;

class DependencyType extends DefaultDependencyType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return Helper::KEY_OPTION_TYPE_DEPENDENCY_TYPE;
    }
}
