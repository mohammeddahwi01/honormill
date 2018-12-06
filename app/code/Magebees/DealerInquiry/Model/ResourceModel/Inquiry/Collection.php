<?php
/***************************************************************************
 Extension Name	: Dealer Inquiry
 Extension URL	: https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright		: Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email	: support@magebees.com 
 ***************************************************************************/
namespace Magebees\DealerInquiry\Model\ResourceModel\Inquiry;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magebees\DealerInquiry\Model\Inquiry', 'Magebees\DealerInquiry\Model\ResourceModel\Inquiry');
    }
    
    public function inquiryFilter($dealerid)
    {
        $this->getSelect()->join(
            ['magebees_dealer_inquiry_files' => $this->getTable('magebees_dealer_inquiry_files')],
            'main_table.dealer_id = magebees_dealer_inquiry_files.dealer_id',
            ['*']
        )
                ->where('magebees_dealer_inquiry_files.dealer_id = ?', $dealerid);
        return $this;
    }
}
