<?php
/***************************************************************************
 Extension Name	: Dealer Inquiry
 Extension URL	: https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright		: Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email	: support@magebees.com 
 ***************************************************************************/

namespace Magebees\DealerInquiry\Controller\Adminhtml\Inquiry;

use Magento\Backend\App\Action\Context;

class Createcustomer extends \Magento\Backend\App\Action
{
    protected $_inquiryFactory;
    protected $_customerFactory;
    protected $_addressFactory;
    protected $_storeManager;
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;
    /**
     * Customer Email template config path
     */
    const XML_PATH_CUSTOMER_EMAIL_TEMPLATE = 'inquiry/create_account/email_template';

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magebees\DealerInquiry\Model\InquiryFactory $inquiryFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    ) {
        parent::__construct($context);
        $this->_inquiryFactory = $inquiryFactory;
        $this->_customerFactory = $customerFactory;
        $this->_addressFactory = $addressFactory;
        $this->_storeManager = $storeManager;
        $this->_transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $dealer_data = $this->_inquiryFactory->create()->load($id);
        $customer = $this->_customerFactory->create()->setWebsiteId($dealer_data['website_id']);//get customer data from model
        
        $load_by_email = $customer->loadByEmail($dealer_data['email']);// check customer exist or not
        if (!$load_by_email->isEmpty()) {
            $this->messageManager->addError(
                __('The customer account already created.')
            );
            $this->_redirect('*/*/');
            return;
        } else {
            $randompass = $this->randomPassword(9, 'standard');//generate random password
            $customer_data = $customer->getCollection()->getData();
            $scopeConfig = $this->_objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
            $customer_group = $scopeConfig->getValue('inquiry/settings/customer_group', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            
            $customer->setGroupId($customer_group)
                ->setWebsiteId($dealer_data['website_id'])
                ->setStoreId($dealer_data['store_id'])
                ->setFirstname($dealer_data['first_name'])
                ->setLastname($dealer_data['last_name'])
                ->setEmail($dealer_data['email'])
                ->setPassword($randompass)
                ->setTaxvat($dealer_data['taxvat'])
                ->setCustomerActivated('1')
                ->setConfirmation(null);
                    
            $customer->save();
            $this->addAddress($dealer_data, $customer->getId());
            $this->sendMail($dealer_data, $randompass);
            $this->messageManager->addSuccess(
                __('The customer has been created successfully.')
            );
            //for update is customer created status
            $dealer_data->setData('is_cust_created', '1');
            $dealer_data->save();
            
            $this->_redirect('*/*/');
            return;
        }
        $this->_redirect('*/*/');
        return;
    }
    
    public function addAddress($dealer_data, $id)
    {
        $_custom_address =  [
        'street' =>  [
            '0' => $dealer_data['address'],
        ],
        'firstname' => $dealer_data['first_name'],
        'lastname' => $dealer_data['last_name'],
        'company' => $dealer_data['company'],
        'city' => $dealer_data['city'],
        'region_id' => '',
        'region' => $dealer_data['state'],
        'postcode' => $dealer_data['zip'],
        'country_id' => $dealer_data['country'],
        'telephone' => $dealer_data['phone'],
        ];
        
        $customerAddress = $this->_addressFactory->create();
        $customerAddress->setData($_custom_address)
        ->setCustomerId($id)
        ->setIsDefaultBilling('1')
        ->setIsDefaultShipping('1')
        ->setSaveInAddressBook('1');
        
        try {
            $customerAddress->save();
        } catch (\Exception $ex) {
        }
        return;
    }
    
    public function randomPassword($pwdLength = 8, $pwdType = 'standard')
    {
        // $pwdType can be one of these:
        //    test .. .. .. always returns the same password = "test"
        //    any  .. .. .. returns a random password, which can contain strange characters
        //    alphanum . .. returns a random password containing alphanumerics only
        //    standard . .. same as alphanum, but not including l10O (lower L, one, zero, upper O)
        $ranges='';
     
        if ('test'==$pwdType) {
            return 'test';
        } elseif ('standard'==$pwdType) {
            $ranges='65-78,80-90,97-107,109-122,50-57';
        } elseif ('alphanum'==$pwdType) {
            $ranges='65-90,97-122,48-57';
        } elseif ('any'==$pwdType) {
            $ranges='40-59,61-91,93-126';
        }
     
        if ($ranges<>'') {
            $range=explode(',', $ranges);
            $numRanges=count($range);
            mt_srand(time()); //not required after PHP v4.2.0
            $p='';
            for ($i = 1; $i <= $pwdLength; $i++) {
                $r=mt_rand(0, $numRanges-1);
                list($min,$max)=explode('-', $range[$r]);
                $p.=chr(mt_rand($min, $max));
            }
            return $p;
        }
    }
    
    public function sendMail($dealer_data, $randompass)
    {
        $store = $this->_storeManager->getStore()->load($dealer_data['store_id']);
        if (!$dealer_data) {
            $this->_redirect('*/*/');
            return;
        }
        $scopeConfig = $this->_objectManager->get('\Magento\Framework\App\Config\ScopeConfigInterface');
        $helper = $this->_objectManager->get('\Magebees\DealerInquiry\Helper\Data');
        $owner_email = $helper->getOwnerEmail();
        $owner_name = $scopeConfig->getValue('trans_email/ident_general/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $subject = $scopeConfig->getValue('inquiry/create_account/subject', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $data['name'] = $dealer_data['first_name'].' '.$dealer_data['last_name'];
        $data['email'] = $dealer_data['email'];
        $data['password'] = $randompass;
        $store_id = $dealer_data['store_id'];
        $postObject = new \Magento\Framework\DataObject();
        $postObject->setData($data);

        $this->inlineTranslation->suspend();
        try {
            $this->_transportBuilder
             ->setTemplateIdentifier(
                 $scopeConfig->getValue(
                     self::XML_PATH_CUSTOMER_EMAIL_TEMPLATE,
                     \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                 )
             )->setTemplateOptions(
                 [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $store_id,
                 ]
             )->setTemplateVars(
                 [
                    'store' => $store,
                    'customer' => $postObject,
                    'subject'=>$subject,
                    
                 ]
             )->setFrom(
                 [
                    'email'=> $owner_email,
                    'name' => $owner_name
                 ]
             )
            ->addTo(
                $data['email'],
                $data['name']
            );
            
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addError(
                __($e->getMessage())
            );
        }
    }
}
