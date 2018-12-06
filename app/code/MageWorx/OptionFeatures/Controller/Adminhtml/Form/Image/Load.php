<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Controller\Adminhtml\Form\Image;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\View\Result\PageFactory;

class Load extends Action
{
    /**
     * Page factory
     *
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * Raw factory
     *
     * @var RawFactory
     */
    protected $rawFactory;

    /**
     * Load constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param RawFactory $rawFactory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        RawFactory $rawFactory
    ) {
        $this->rawFactory = $rawFactory;
        $this->pageFactory = $pageFactory;

        return parent::__construct($context);
    }

    /**
     * Render block form
     *
     * @return Raw
     * @throws \Exception
     */
    public function execute()
    {
        try {
            $response = [
                'result' => $this->getResultHtml(),
                'status' => true,
            ];
        } catch (\Exception $e) {
            $response = [
                'error' => $e->getMessage(),
                'status' => false,
            ];
        }

        /** @var  $result */
        $result = $this->rawFactory->create()->setContents(json_encode($response));

        return $result;
    }

    /**
     * @return string
     */
    protected function getResultHtml()
    {
        $resultPage = $this->pageFactory->create();
        $resultPage->addHandle('option_value_images');

        return $resultPage->getLayout()->renderElement('content');
    }
}
