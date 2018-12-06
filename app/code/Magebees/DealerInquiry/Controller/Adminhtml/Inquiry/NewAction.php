<?php
/***************************************************************************
 Extension Name	: Dealer Inquiry
 Extension URL	: https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright		: Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email	: support@magebees.com 
 ***************************************************************************/
namespace Magebees\DealerInquiry\Controller\Adminhtml\Inquiry;

class NewAction extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
