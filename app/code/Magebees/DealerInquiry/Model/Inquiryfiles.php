<?php
/***************************************************************************
 Extension Name	: Dealer Inquiry
 Extension URL	: https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright		: Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email	: support@magebees.com 
 ***************************************************************************/
namespace Magebees\DealerInquiry\Model;

class Inquiryfiles extends \Magento\Framework\Model\AbstractModel
{
    
     /**
      * Initialization
      *
      * @return void
      */
    protected function _construct()
    {
        $this->_init('Magebees\DealerInquiry\Model\ResourceModel\Inquiryfiles');
    }
}
