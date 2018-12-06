<?php
/***************************************************************************
 Extension Name	: Dealer Inquiry
 Extension URL	: https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright		: Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email	: support@magebees.com 
 ***************************************************************************/
namespace Magebees\DealerInquiry\Controller;

/**
 * Inquiry Custom router Controller Router
 *
 */
class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->actionFactory = $actionFactory;
        $this->_response = $response;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Validate and Match
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $is_enable = $this->_scopeConfig->getValue('inquiry/settings/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $custom_url_key = $this->_scopeConfig->getValue('inquiry/settings/url_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        $identifier = trim($request->getPathInfo(), '/');
        
        if ($is_enable) {
            if (strpos($identifier, 'inquiry') !== false) {
                /*
				 * We must set module, controller path and action name + we will set page id 5 witch is about us page on
				 * default magento 2 installation with sample data.
				 */
                $request->setModuleName('inquiry')->setControllerName('index')->setActionName('index');
            } elseif (strpos($identifier, $custom_url_key) !== false) {
                /*
				 * We must set module, controller path and action name for our controller class(Controller/Test/Test.php)
				 */
                $request->setModuleName('inquiry')->setControllerName('index')->setActionName('index');
            } else {
                //There is no match
                return;
            }
        } else {
            return;
        }

        /*
         * We have match and now we will forward action
         */
        return $this->actionFactory->create(
            'Magento\Framework\App\Action\Forward',
            ['request' => $request]
        );
    }
}
