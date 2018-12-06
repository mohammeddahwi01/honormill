<?php
/***************************************************************************
 Extension Name	: Dealer Inquiry
 Extension URL	: https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright		: Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email	: support@magebees.com 
 ***************************************************************************/
namespace Magebees\DealerInquiry\Controller\Index;

use \Magento\Framework\App\Action\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;

class Save extends Action
{
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;
    
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    
    /**
     * DealerInquiry Model Factory
     * @var \Magebees\DealerInquiry\Model\InquiryFactory
     */
    protected $_inquiryFactory;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;
    
    /**
     * Save Post Data
     */
    protected $_data;
    
    /**
     * Owner Email config path
     */
    const XML_PATH_OWNER_EMAIL_TEMPLATE = 'inquiry/admin_email/email_template';

    /**
     * Customer Email template config path
     */
    const XML_PATH_CUSTOMER_EMAIL_TEMPLATE = 'inquiry/customer_email/email_template';
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magebees\DealerInquiry\Model\InquiryFactory $inquiryFactory,
        $data = []
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->inlineTranslation = $inlineTranslation;
        $this->_storeManager = $storeManager;
        $this->_transportBuilder = $transportBuilder;
        $this->_date = $date;
        $this->_inquiryFactory = $inquiryFactory;
    }
    
    /**
     * Post DealerInquiry
     *
     * @return void
     * @throws \Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        
        $post = $this->getRequest()->getPostValue();
        if (!$post) {
             return $resultRedirect;
        }
        $captcha =  $this->getRequest()->getParam("captcha");
        $captcha_code =  $this->getCaptchaSession();
        if (!empty($captcha) && $captcha != $captcha_code) {
            $this->messageManager->addError(__('Captcha code does not match!'));
            $this->setInquiryDataSession($post); // set form data to session
            return $resultRedirect;
        }
            
        $inquiryfactory = $this->_inquiryFactory->create();
        $collection = $inquiryfactory->getCollection()
                                ->addFieldToFilter('email', $post['email'])
                                ->addFieldToFilter('store_id', $this->_storeManager->getStore()->getId());
            
        if (!$collection->getSize()) {//check email exist or not in storeview
            $post['website_id'] = $this->_storeManager->getWebsite()->getId();
            $post['store_id'] = $this->_storeManager->getStore()->getId();
            $post['creation_time'] = $this->_date->date();//Y-m-d H:i:s
            $country = $this->_objectManager->create('Magento\Directory\Model\Config\Source\Country')->toOptionArray();
            
            if(isset($post['country'])) {
                foreach ($country as $country) {
                    if ($country['value']==$post['country']) {
                        $post['country_name'] = $country['label'];
                        break;
                    }
                }
            }
            
            //for check customer is already created
            $customer = $this->_objectManager->create('Magento\Customer\Model\Customer');
            $customer->setWebsiteId($post['website_id']);
            $customer->loadByEmail($post['email']);
            if ($customer->getId()) {
                $post['is_cust_created']=1;
            }
                    
            //for upload multiple files
            $uploaded_files = [];
            $files = $this->getRequest()->getFiles();
            if (!empty($files['upload_file']['name'][0])) {
                foreach ($files['upload_file'] as $key => $value) {
                    $image_count = count($files['upload_file'][$key]);
                    for ($i=0; $i<$image_count; $i++) {
                        $uploaded_files[$i]['upload_file'][$key] = $value[$i];
                    }
                }
                
                for ($i=0; $i<$image_count; $i++) {
                    $files = $uploaded_files[$i];
                    $result = $this->uploadFiles();
                    if (array_key_exists('success', $result)) {
                        $post['file_name'][] = $result['success'];
                    } else {
                        $this->messageManager->addError(__($result['fail']));
                        return $resultRedirect;
                    }
                }
            }

            if(isset($post['extra_three']) && $post['extra_three']) {
                $post['extra_three'] = implode(',', $post['extra_three']);
            }
            
            $inquiryfactory->setData($post);
            $inquiryfactory->save();
            $this->_data = $post;
            $this->sendOwnerEmail(); //send email to store owner
            $this->sendCustomerEmail(); //send thank you mail to customer
            
            $success_msg = $this->_scopeConfig->getValue('inquiry/settings/success_message', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $this->messageManager->addSuccess(__($success_msg));
        } else {
            $this->messageManager->addError(__('Email already exist!'));
            $this->setInquiryDataSession($post); // set form data to session
            return $resultRedirect;
        }
        return $resultRedirect;
    }
    
    public function setInquiryDataSession($post)
    {
        $session = $this->_objectManager->get('Magento\Customer\Model\Session');
        return $session->setInquiryFormData($post);
    }
    
    public function getCaptchaSession()
    {
        $session = $this->_objectManager->get('Magento\Customer\Model\Session');
        return $session->getInquiryCaptcha();
    }
    
    public function uploadFiles()
    {
        if (isset($files['upload_file']['name']) && $files['upload_file']['name'] != '') {
            try {
                $uploader = $this->_objectManager->create('Magento\MediaStorage\Model\File\Uploader', ['fileId' => 'upload_file']);
                $allowed_ext = $this->_scopeConfig->getValue('inquiry/label_hide/file_ext', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $allowed_ext_array = [];
                $allowed_ext_array = explode(',', $allowed_ext);
                $uploader->setAllowedExtensions($allowed_ext_array);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
                    ->getDirectoryRead(DirectoryList::MEDIA);
                $result = $uploader->save($mediaDirectory->getAbsolutePath('inquiry/'));
                unset($result['tmp_name']);
                unset($result['path']);
                $res['success'] = $result['file'];
                return $res;
            } catch (\Exception $e) {
                $res['fail']= $e->getMessage();
                return $res;
            }
        }
    }
        
    public function sendOwnerEmail()
    {
        $data = $this->_data;
        $helper = $this->_objectManager->get('\Magebees\DealerInquiry\Helper\Data');
        $sender_email = $helper->getOwnerEmail();
        $owner_name = $this->_scopeConfig->getValue('trans_email/ident_general/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                
        $subject = $this->_scopeConfig->getValue('inquiry/admin_email/subject', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $data['name'] = $data['first_name'].' '.$data['last_name'];
        $data['store'] =        $this->_storeManager->getStore();
        if (!empty($data['file_name'])) {
            $path = $this->_storeManager->getStore()->getBaseUrl(DirectoryList::MEDIA) . 'inquiry';
            foreach ($data['file_name'] as $key => $fname) {
                $file_link[$key] = "<a href='".$path.$fname."' target='_blank'>".$fname."</a>";
            }
            $data['uploaded_files'] = implode(', ', $file_link);
        }

        $postObject = new \Magento\Framework\DataObject();
        $postObject->setData($data);
        
        $labelObject = new \Magento\Framework\DataObject();
        $label_data = $this->_scopeConfig->getValue('inquiry/change_label', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        $labelObject->setData($label_data);
        if (!$data) {
             return $resultRedirect;
        }

        $this->inlineTranslation->suspend();
        try {
            $this->_transportBuilder
             ->setTemplateIdentifier(
                 $this->_scopeConfig->getValue(
                     self::XML_PATH_OWNER_EMAIL_TEMPLATE,
                     \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                 )
             )->setTemplateOptions(
                 [
                    'area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                    'store' => $this->_storeManager->getStore()->getId(),
                 ]
             )->setTemplateVars([
                                'data' => $postObject,
                                'label' => $labelObject,
                                'owner'=> $owner_name,
                                'subject' => $subject
                                ])
            ->setFrom(
                [
                    'email' => $data['email'],
                    'name' => $data['name']
                ]
            )
            ->addTo(
                $sender_email,
                $owner_name
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
    
    public function sendCustomerEmail()
    {
        $post = $this->getRequest()->getPostValue();
        
        $helper = $this->_objectManager->get('\Magebees\DealerInquiry\Helper\Data');
        $owner_email = $helper->getOwnerEmail();
        
        $owner_name = $this->_scopeConfig->getValue('trans_email/ident_general/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $customer_email = $post['email'];
        $subject = $this->_scopeConfig->getValue('inquiry/customer_email/subject', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $customer_name = $post['first_name'].' '.$post['last_name'];
        
        if (!$post) {
            return $resultRedirect;
        }

        $this->inlineTranslation->suspend();
        try {
            $this->_transportBuilder
             ->setTemplateIdentifier(
                 $this->_scopeConfig->getValue(
                     self::XML_PATH_CUSTOMER_EMAIL_TEMPLATE,
                     \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                 )
             )->setTemplateOptions(
                 [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId(),
                 ]
             )->setTemplateVars(
                 [
                    'store' => $this->_storeManager->getStore(),
                    'name' => $customer_name,
                    'subject'=>$subject
                    
                 ]
             )->setFrom(
                 [
                    'email' => $owner_email,
                    'name' => $owner_name
                 ]
             )
            ->addTo(
                $customer_email,
                $customer_name
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
