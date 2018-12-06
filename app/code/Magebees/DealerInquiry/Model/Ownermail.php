<?php
/***************************************************************************
 Extension Name	: Dealer Inquiry
 Extension URL	: https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright		: Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email	: support@magebees.com 
 ***************************************************************************/
namespace Magebees\DealerInquiry\Model;

class Ownermail implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' =>'default', 'label' => __('Default General Contact')],
            ['value' =>'custom', 'label' => __('Add new email for Owner')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    
    
    public function toArray()
    {
        return ['default'=> __('Default General Contact'), 'custom'=> __('Add new email for Owner')];
    }
}
