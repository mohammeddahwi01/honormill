<?php
/***************************************************************************
 Extension Name	: Dealer Inquiry
 Extension URL	: https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright		: Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email	: support@magebees.com 
 ***************************************************************************/
namespace Magebees\DealerInquiry\Controller\Adminhtml\Inquiry;

class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Magebees_DealerInquiry::inquiry';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magebees_DealerInquiry::inquiry_content');
        $resultPage->addBreadcrumb(__('Dealer Inquiry'), __('Dealer Inquiry'));
        $resultPage->addBreadcrumb(__('Manage Dealers'), __('Manage Dealers'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Dealers'));

        return $resultPage;
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_DealerInquiry::inquiry_content');
    }
}
