<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SearchSuiteSphinx\Model\System\Config\Source;

use \Sphinx\SphinxClient;

/**
 * Used in creating options for "Search Results Ranker" setting
 */
class RankingMode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
                    ['value' => SphinxClient::SPH_RANK_PROXIMITY_BM25, 'label' => __('SPH_RANK_PROXIMITY_BM25')],
                    ['value' => SphinxClient::SPH_RANK_BM25, 'label' => __('SPH_RANK_BM25')],
                    ['value' => SphinxClient::SPH_RANK_NONE, 'label' => __('SPH_RANK_NONE')],
                    ['value' => SphinxClient::SPH_RANK_WORDCOUNT, 'label' => __('SPH_RANK_WORDCOUNT')],
                    ['value' => SphinxClient::SPH_RANK_PROXIMITY, 'label' => __('SPH_RANK_PROXIMITY')],
                    ['value' => SphinxClient::SPH_RANK_MATCHANY, 'label' => __('SPH_RANK_MATCHANY')],
                    ['value' => SphinxClient::SPH_RANK_FIELDMASK, 'label' => __('SPH_RANK_FIELDMASK')],
                    ['value' => SphinxClient::SPH_RANK_SPH04, 'label' => __('SPH_RANK_SPH04')]
               ];
    }
}
