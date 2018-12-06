<?php
/***************************************************************************
 Extension Name	: Dealer Inquiry
 Extension URL	: https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright		: Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email	: support@magebees.com 
 ***************************************************************************/
namespace Magebees\DealerInquiry\Controller\Adminhtml\Inquiry;

use Magento\Backend\App\Action;

class Edit extends \Magento\Backend\App\Action
{
    protected $_coreRegistry = null;
    protected $resultPageFactory;
 
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }
 
   
    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magebees_DealerInquiry::inquiry_content');
        return $resultPage;
    }
    
    public function execute()
    {
        // 1. Get ID and create model
        
        //$id = $this->getRequest()->getParam('id');
        $id = $this->getRequest()->getParam('dealer_id');
        $model = $this->_objectManager->create('Magebees\DealerInquiry\Model\Inquiry');
        $registryObject = $this->_objectManager->get('Magento\Framework\Registry');
                
        
        
        // 2. Initial checking
        if ($id) {
            $inquiry_file_collection = $model->getCollection()->inquiryFilter($id);
            $filename = "";
            $collection_data = $inquiry_file_collection->getData();
            foreach ($collection_data as $data) {
                if (next($collection_data)) {
                    $filename.= $data['file_name']."|";
                } else {
                    $filename.= $data['file_name'];
                }
            }
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('Dealer Information Not Available.'));
                $this->_redirect('*/*/');
                return;
            }
        }
        // 3. Set entered data if was error when we do save
        $model->setData('file_name', $filename);
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        $registryObject->register('inquiry', $model);
        /* $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout(); */
        
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Dealer') : __('Add Dealer'),
            $id ? __('Edit Dealer') : __('Add Dealer')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Dealer Inquiry'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Dealers'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getFirstName()." ".$model->getLastName() : __('Add Group'));
 
        return $resultPage;
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_DealerInquiry::inquiry_content');
    }
}
