<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SearchSuiteSphinx\Controller\Adminhtml\Generate;

use Magento\Backend\App\Action\Context;
use MageWorx\SearchSuiteSphinx\Helper\Sphinx as HelperSphinx;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * SearchSuiteSphinx generate Sphinx config action
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
     * @param JsonFactory $resultJsonFactory
     * @param Context $context
     * @param HelperSphinx $helperSphinx
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        Context $context,
        HelperSphinx $helperSphinx
    ) {
        $this->helperSphinx      = $helperSphinx;
        $this->resultJsonFactory = $resultJsonFactory;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $configFileResult = $this->helperSphinx->createSphinxConfig();
        $result           = $this->resultJsonFactory->create();
        $result->setData($configFileResult);

        return $result;
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
