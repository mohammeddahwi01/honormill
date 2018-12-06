<?php
/***************************************************************************
 Extension Name	: Dealer Inquiry
 Extension URL	: https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright		: Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email	: support@magebees.com 
 ***************************************************************************/
namespace Magebees\DealerInquiry\Model;

class Customergroup implements \Magento\Framework\Option\ArrayInterface
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Customer\Model\Group $customerGroupModel,
        array $data = []
    ) {
        $this->customerGroupModel = $customerGroupModel;
    }
    
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $group = $this->customerGroupModel;
        return $group->getCollection()->toOptionArray();
    }
}
