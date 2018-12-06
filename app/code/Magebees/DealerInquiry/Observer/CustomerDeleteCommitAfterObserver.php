<?php
/***************************************************************************
 Extension Name	: Dealer Inquiry
 Extension URL	: https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright		: Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email	: support@magebees.com 
 ***************************************************************************/
namespace Magebees\DealerInquiry\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * DealerInquiry Observer Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerDeleteCommitAfterObserver implements ObserverInterface
{
    protected $_inquiryFactory;
    
    public function __construct(
        \Magebees\DealerInquiry\Model\InquiryFactory $inquiryFactory
    ) {
        $this->_inquiryFactory = $inquiryFactory;
    }
    /**
     * Customer delete after commit event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent();
        $email = $event->getCustomer()->getData('email');
        $website_id = $event->getCustomer()->getData('website_id');
        $dealer_data = $this->_inquiryFactory->create();
        $dealer_collection = $dealer_data->getCollection()
                            ->addFieldToFilter('email', $email)
                            ->addFieldToFilter('website_id', $website_id);
        foreach ($dealer_collection as $d) {
            $data = $dealer_data->load($d->getDealerId());
            $data->setData('is_cust_created', '0');
            $data->save();
        }
    }
}
