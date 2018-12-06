<?php
/***************************************************************************
 Extension Name	: Dealer Inquiry
 Extension URL	: https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright		: Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email	: support@magebees.com 
 ***************************************************************************/
namespace Magebees\DealerInquiry\Block\Adminhtml;

class Inquiry extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_inquiry';
        $this->_blockGroup = 'Magebees_DealerInquiry';
        $this->_headerText = __('Inquiry');
        parent::_construct();
        $this->removeButton('add');
    }
}
