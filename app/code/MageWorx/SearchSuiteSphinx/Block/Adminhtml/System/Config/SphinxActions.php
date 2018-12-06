<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SearchSuiteSphinx\Block\Adminhtml\System\Config;

use MageWorx\SearchSuiteSphinx\Helper\Sphinx as HelperSphinx;

/**
 * SearchSuiteSphinx system block to display Run button in module settings
 */
class SphinxActions extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \MageWorx\SearchSuiteSphinx\Helper\Sphinx
     */
    protected $helperSphinx;

    /**
     * Check constructor.
     *
     * @param HelperSphinx $helperSphinx
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        HelperSphinx $helperSphinx,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->helperSphinx = $helperSphinx;
        parent::__construct($context, $data);
    }
    
    /**
     * Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('MageWorx_SearchSuiteSphinx::sphinx-search-btns.phtml');
    }
    
    /**
     * Add buttons to config field
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getButtonsHtml()
    {
        $runUrl = $this->_urlBuilder->getUrl('mageworx_searchsuitesphinx/run/index/');
        $runButton = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'run_sphinx_search',
                'label' => __('Run'),
                'onclick' => 'javascript:sphinxSearchAction(this, "' . $runUrl . '"); return false;',
            ]
        );

        $stopUrl =  $this->_urlBuilder->getUrl('mageworx_searchsuitesphinx/stop/index/');
        $stopButton = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'stop_sphinx_search',
                'label' => __('Stop'),
                'onclick' => 'javascript:sphinxSearchAction(this, "' . $stopUrl . '"); return false;',
            ]
        );

        $checkUrl = $this->_urlBuilder->getUrl('mageworx_searchsuitesphinx/check/index/');
        $checkButton = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'check_sphinx_availability',
                'label' => __('Check'),
                'onclick' => 'javascript:sphinxSearchAction(this, "' . $checkUrl . '"); return false;',
            ]
        );

        $reindexUrl = $this->_urlBuilder->getUrl('mageworx_searchsuitesphinx/reindex/index/');
        $reindexButton = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'reindex_sphinx_search',
                'label' => __('Reindex All'),
                'onclick' => 'javascript:sphinxSearchAction(this, "' . $reindexUrl . '"); return false;',
            ]
        );
        
        return $checkButton->toHtml() . $reindexButton->toHtml() . $runButton->toHtml() . $stopButton->toHtml();
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        $result = $this->helperSphinx->checkSphinxStatus();
        return $result['status'] ? 'success' : 'error';
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        $result = $this->helperSphinx->checkSphinxStatus();
        return $result['msg'];
    }
}
