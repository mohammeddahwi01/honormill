<?php
/***************************************************************************
 Extension Name : Dealer Inquiry
 Extension URL  : https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright      : Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email  : support@magebees.com 
 ***************************************************************************/
namespace Magebees\DealerInquiry\Block;

use Magento\Store\Model\ScopeInterface;

/**
 * Class InquiryLink
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class InquiryLink extends \Magento\Framework\View\Element\Html\Link
{

   
    /**
     * @return string
     */
    public function getHref()
    {
        $url_key = $this->_scopeConfig->getValue('inquiry/settings/url_key', ScopeInterface::SCOPE_STORE);
        return $this->getUrl($url_key, ['_secure' => true]);
    }
    
    public function getLabel()
    {
        return $this->_scopeConfig->getValue('inquiry/settings/link_label', ScopeInterface::SCOPE_STORE);
    }
    
    public function isEnable()
    {
        return $this->_scopeConfig->getValue('inquiry/settings/enable_link', ScopeInterface::SCOPE_STORE);
    }
}
