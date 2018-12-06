<?php
/***************************************************************************
 Extension Name	: Dealer Inquiry
 Extension URL	: https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright		: Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email	: support@magebees.com 
 ***************************************************************************/
namespace Magebees\DealerInquiry\Controller\Adminhtml\Inquiry;

class Grid extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $this->getResponse()->setBody($this->_view->getLayout()->createBlock('Magebees\DealerInquiry\Block\Adminhtml\Inquiry\Grid')->toHtml());
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_DealerInquiry::inquiry');
    }
}
