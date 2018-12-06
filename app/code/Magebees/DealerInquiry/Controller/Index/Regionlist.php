<?php
/***************************************************************************
 Extension Name : Dealer Inquiry
 Extension URL  : https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright      : Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email  : support@magebees.com 
 ***************************************************************************/
 
namespace Magebees\DealerInquiry\Controller\Index;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Regionlist extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
    
        $this->_countryFactory = $countryFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        $state = "";
        $countrycode = $this->getRequest()->getParam('country');
        if ($countrycode != '') {
            $statearray =$this->_countryFactory->create()->setId(
                $countrycode
            )->getLoadedRegionCollection()->toOptionArray();
            if (!empty($statearray)) {
                $state = "<select id='state' name='state' class='drop class='input-text required-entry' data-validate='{required:true}'><option value=''>--Please Select--</option>";
                foreach ($statearray as $_state) {
                    if ($_state['value']) {
                        $state .= "<option value='".$_state['value']."'>" . $_state['label'] . "</option>";
                    }
                }
                $state .= "</select>";
            } else {
                $state .= "<input type='text' id='state' name='state' class='input-text' data-validate='{required:true}'/>";
            }
        }
        
        $result['htmlconent']=$state;
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
        );
    }
}
