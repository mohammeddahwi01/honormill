<?php
/***************************************************************************
 Extension Name : Dealer Inquiry
 Extension URL  : https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright      : Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email  : support@magebees.com 
 ***************************************************************************/
namespace Magebees\DealerInquiry\Controller\Index;

use \Magento\Framework\App\Action\Action;

class Refresh extends Action
{
    protected $_helper;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magebees\DealerInquiry\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->_helper = $helper;
    }
    
    public function execute()
    {
        $image_name = $this->_helper->createCaptchaImage();
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($image_name)
        );
    }
}
