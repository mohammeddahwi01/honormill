<?php
/***************************************************************************
 Extension Name : Dealer Inquiry
 Extension URL  : https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright      : Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email  : support@magebees.com 
 ***************************************************************************/
namespace Magebees\DealerInquiry\Controller\Index;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;
use \Magento\Framework\App\Action\Action;

class Index extends Action
{
    /**
    * Enabled config path
    */
    const XML_PATH_ENABLED = 'inquiry/settings/enable';
    
    protected $resultPageFactory;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    protected $_countryFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    protected $metaDeatils = [];
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Model\Config\Source\Country $countryFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeConfig;
        $this->_countryFactory = $countryFactory;
    }
    
    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE)) {
            throw new NotFoundException(__('Page not found.'));
        }
        return parent::dispatch($request);
    }
    
    public function getMetaDetails()
    {
        $this->metaDeatils['title']=$this->scopeConfig->getValue('inquiry/settings/meta_title', ScopeInterface::SCOPE_STORE);
        $this->metaDeatils['keywords']=$this->scopeConfig->getValue('inquiry/settings/meta_keywords', ScopeInterface::SCOPE_STORE);
        $this->metaDeatils['description']=$this->scopeConfig->getValue('inquiry/settings/meta_description', ScopeInterface::SCOPE_STORE);
    }
    
    public function execute()
    {
        $this->getMetaDetails();
        $this->_view->loadLayout();
        $page_layout = $this->scopeConfig->getValue('inquiry/settings/page_layout', ScopeInterface::SCOPE_STORE);
        $this->_view->getPage()->getConfig()->setPageLayout($page_layout);
        $this->_view->getPage()->getConfig()->getTitle()->set($this->metaDeatils['title']);
        $this->_view->getPage()->getConfig()->setDescription($this->metaDeatils['description']);
        $this->_view->getPage()->getConfig()->setKeywords($this->metaDeatils['keywords']);
        $this->_view->renderLayout();
    }
}
