<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SeoRichData
 */


namespace Amasty\SeoRichData\Plugin\Pricing\Render;

class Amount
{
    /**
     * Remove magento price rich data
     * @param \Magento\Framework\Pricing\Render\Amount $subject
     * @param $result
     * @return mixed
     */
    public function afterToHtml(
        $subject,
        $result
    ) {
        $result = preg_replace('|itemprop=".*"|U', '', $result);
        $result = preg_replace('|itemtype=".*"|U', '', $result);
        $result = str_replace('itemscope', '', $result);

        return $result;
    }
}
