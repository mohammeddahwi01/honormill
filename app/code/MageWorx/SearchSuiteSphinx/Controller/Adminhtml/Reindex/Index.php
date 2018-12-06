<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SearchSuiteSphinx\Controller\Adminhtml\Reindex;

use \Magento\Backend\App\Action\Context;
use \MageWorx\SearchSuiteSphinx\Helper\Sphinx as HelperSphinx;
use \Magento\Framework\Controller\Result\JsonFactory;

/**
 * SearchSuiteSphinx run Sphinx search action
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \MageWorx\SearchSuiteSphinx\Helper\Sphinx
     */
    protected $helperSphinx;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param HelperSphinx $helperSphinx
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        HelperSphinx $helperSphinx,
        JsonFactory $resultJsonFactory
    ) {
        $this->helperSphinx = $helperSphinx;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Check Sphinx availabilty
     *
     * @return json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        return $result->setData($this->helperSphinx->runSphinxReindex());
    }

    /**
     * Is access to section allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MageWorx_SearchSuiteSphinx::config_searchsuite');
    }
}
