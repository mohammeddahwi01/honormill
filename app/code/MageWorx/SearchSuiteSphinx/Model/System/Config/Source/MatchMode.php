<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SearchSuiteSphinx\Model\System\Config\Source;

use \Sphinx\SphinxClient;

/**
 * Used in creating options for "Matching Mode" setting
 */
class MatchMode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
                    ['value' => SphinxClient::SPH_MATCH_ALL, 'label' => __('SPH_MATCH_ALL')],
                    ['value' => SphinxClient::SPH_MATCH_ANY, 'label' => __('SPH_MATCH_ANY')],
                    ['value' => SphinxClient::SPH_MATCH_PHRASE, 'label' => __('SPH_MATCH_PHRASE')],
                    ['value' => SphinxClient::SPH_MATCH_EXTENDED, 'label' => __('SPH_MATCH_EXTENDED')],
                    ['value' => SphinxClient::SPH_MATCH_FULLSCAN, 'label' => __('SPH_MATCH_FULLSCAN')],
               ];
    }
}
