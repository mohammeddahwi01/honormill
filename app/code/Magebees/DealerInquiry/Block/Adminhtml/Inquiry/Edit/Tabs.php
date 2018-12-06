<?php
/***************************************************************************
 Extension Name	: Dealer Inquiry
 Extension URL	: https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright		: Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email	: support@magebees.com 
 ***************************************************************************/
namespace Magebees\DealerInquiry\Block\Adminhtml\Inquiry\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {

        parent::_construct();
        $this->setId('inquiry_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Dealer Information'));
    }
}
