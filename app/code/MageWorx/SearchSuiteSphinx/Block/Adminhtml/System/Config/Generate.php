<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SearchSuiteSphinx\Block\Adminhtml\System\Config;

/**
 * SearchSuiteSphinx system block to display Generate button in module settings
 */
class Generate extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Generate constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
    
    /**
     * Set template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('MageWorx_SearchSuiteSphinx::generate-sphinx-config-btn.phtml');
    }
    
    /**
     * Add generate button to config field
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }
        
    /**
     * Return generate button html
     *
     * @param string $sku
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'generate_sphinx_config',
                'label' => __('Generate'),
                'onclick' => 'javascript:checkFileGeneration(this, "' . $this->_urlBuilder->getUrl('mageworx_searchsuitesphinx/generate/index/') . '"); return false;'
            ]
        );
        
        return $button->toHtml();
    }
}
