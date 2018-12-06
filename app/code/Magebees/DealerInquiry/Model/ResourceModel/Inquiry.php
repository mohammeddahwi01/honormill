<?php
/***************************************************************************
 Extension Name	: Dealer Inquiry
 Extension URL	: https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright		: Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email	: support@magebees.com 
 ***************************************************************************/
namespace Magebees\DealerInquiry\Model\ResourceModel;

/**
 * Review resource model
 */
class Inquiry extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $_inquiryfilesFactory;
    /**
     * Define main table. Define other tables name
     *
     * @return void
     */
     
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magebees\DealerInquiry\Model\InquiryfilesFactory $inquiryfilesFactory
    ) {
        parent::__construct($context);
        $this->_inquiryfilesFactory = $inquiryfilesFactory;
    }
     
    protected function _construct()
    {
        $this->_init('magebees_dealer_inquiry', 'dealer_id');
    }
    
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $inq_file_model = $this->_inquiryfilesFactory->create();
        if ($object->getFileName()) {
            foreach ($object->getFileName() as $fname) {
                $data['dealer_id'] = $object->getDealerId();
                $data['file_name'] = $fname;
                $inq_file_model->setData($data);
                $inq_file_model->save();
            }
        }
        return parent::_afterSave($object);
    }
}
