<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionBase\Ui\DataProvider\Product\Form\Modifier;

interface ModifierInterface
{
    /**
     * Check is current modifier for the product only
     *
     * @return bool
     */
    public function isProductScopeOnly();
}
