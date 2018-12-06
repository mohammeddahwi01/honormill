<?php
/***************************************************************************
 Extension Name	: Dealer Inquiry
 Extension URL	: https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright		: Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email	: support@magebees.com 
 ***************************************************************************/
namespace Magebees\DealerInquiry\Controller\Adminhtml\Inquiry;

class Delete extends \Magento\Backend\App\Action
{
    protected $_inquiryFactory;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magebees\DealerInquiry\Model\InquiryFactory $inquiryFactory
    ) {
        parent::__construct($context);
        $this->_inquiryFactory = $inquiryFactory;
    }

    public function execute()
    {
        $dealerId = $this->getRequest()->getParam('id');
        try {
            $dealer = $this->_inquiryFactory->create()->load($dealerId);
            $dealer->delete();
            $this->messageManager->addSuccess(
                __('Deleted successfully !')
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect('*/*/');
    }
}
