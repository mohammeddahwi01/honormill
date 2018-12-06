<?php
/***************************************************************************
 Extension Name	: Dealer Inquiry
 Extension URL	: https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright		: Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email	: support@magebees.com 
 ***************************************************************************/
namespace Magebees\DealerInquiry\Block;

use Magento\Store\Model\ScopeInterface;

class Inquiry extends \Magento\Framework\View\Element\Template
{
    protected $_topLink;
        
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Model\Config\Source\Country $country,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\View\Element\Html\Link\Current $topLink,
        \Magento\Customer\Model\Session $session
    ) {
        parent::__construct($context);
        $this->_countryFactory = $countryFactory;
        $this->_country = $country;
        $this->_topLink = $topLink;
        $this->session = $session;
                        
        //Set Configuration values
        $this->setBtnTxt($this->_scopeConfig->getValue('inquiry/settings/submit_label', ScopeInterface::SCOPE_STORE));
        $this->setHeading($this->_scopeConfig->getValue('inquiry/settings/heading', ScopeInterface::SCOPE_STORE));
        $this->setDisplayHeading($this->_scopeConfig->getValue('inquiry/settings/display_heading', ScopeInterface::SCOPE_STORE));
        $this->setHeadingHide($this->_scopeConfig->getValue('inquiry/settings/display_heading', ScopeInterface::SCOPE_STORE));
        $this->setDesc($this->_scopeConfig->getValue('inquiry/settings/description', ScopeInterface::SCOPE_STORE));
        
        //Set Field Label Text
        $this->setFirstName($this->_scopeConfig->getValue('inquiry/change_label/first_name', ScopeInterface::SCOPE_STORE));
        $this->setLastName($this->_scopeConfig->getValue('inquiry/change_label/last_name', ScopeInterface::SCOPE_STORE));
        $this->setCompanyName($this->_scopeConfig->getValue('inquiry/change_label/company_name', ScopeInterface::SCOPE_STORE));
        $this->setVatNumber($this->_scopeConfig->getValue('inquiry/change_label/vat_number', ScopeInterface::SCOPE_STORE));
        $this->setAddress($this->_scopeConfig->getValue('inquiry/change_label/address', ScopeInterface::SCOPE_STORE));
        $this->setCity($this->_scopeConfig->getValue('inquiry/change_label/city', ScopeInterface::SCOPE_STORE));
        $this->setState($this->_scopeConfig->getValue('inquiry/change_label/state', ScopeInterface::SCOPE_STORE));
        $this->setCountry($this->_scopeConfig->getValue('inquiry/change_label/country', ScopeInterface::SCOPE_STORE));
        $this->setZip($this->_scopeConfig->getValue('inquiry/change_label/zip', ScopeInterface::SCOPE_STORE));
        $this->setContactNumber($this->_scopeConfig->getValue('inquiry/change_label/contact_number', ScopeInterface::SCOPE_STORE));
        $this->setEmail($this->_scopeConfig->getValue('inquiry/change_label/email', ScopeInterface::SCOPE_STORE));
        $this->setWebsite($this->_scopeConfig->getValue('inquiry/change_label/website', ScopeInterface::SCOPE_STORE));
        $this->setDescription($this->_scopeConfig->getValue('inquiry/change_label/description', ScopeInterface::SCOPE_STORE));
        $this->setCaptcha($this->_scopeConfig->getValue('inquiry/change_label/captcha', ScopeInterface::SCOPE_STORE));
        $this->setDateTime($this->_scopeConfig->getValue('inquiry/change_label/date_time', ScopeInterface::SCOPE_STORE));
        $this->setUploadFile($this->_scopeConfig->getValue('inquiry/change_label/upload_file', ScopeInterface::SCOPE_STORE));
        
        
        $this->setExtraOne($this->_scopeConfig->getValue('inquiry/change_label/extra_one', ScopeInterface::SCOPE_STORE));
        $this->setExtraTwo($this->_scopeConfig->getValue('inquiry/change_label/extra_two', ScopeInterface::SCOPE_STORE));
        $this->setExtraThree($this->_scopeConfig->getValue('inquiry/change_label/extra_three', ScopeInterface::SCOPE_STORE));
        
        //Show/Hide Fields
        
        $this->setVatNumberHide($this->_scopeConfig->getValue('inquiry/label_hide/vat_number', ScopeInterface::SCOPE_STORE));
        $this->setAddressHide($this->_scopeConfig->getValue('inquiry/label_hide/address', ScopeInterface::SCOPE_STORE));
        $this->setCityHide($this->_scopeConfig->getValue('inquiry/label_hide/city', ScopeInterface::SCOPE_STORE));
        $this->setStateHide($this->_scopeConfig->getValue('inquiry/label_hide/state', ScopeInterface::SCOPE_STORE));
        $this->setCountryHide($this->_scopeConfig->getValue('inquiry/label_hide/country', ScopeInterface::SCOPE_STORE));
        $this->setZipHide($this->_scopeConfig->getValue('inquiry/label_hide/zip', ScopeInterface::SCOPE_STORE));
        $this->setWebsiteHide($this->_scopeConfig->getValue('inquiry/label_hide/website', ScopeInterface::SCOPE_STORE));
        $this->setCaptchaHide($this->_scopeConfig->getValue('inquiry/label_hide/captcha', ScopeInterface::SCOPE_STORE));
        $this->setDateTimeHide($this->_scopeConfig->getValue('inquiry/label_hide/date_time', ScopeInterface::SCOPE_STORE));
        $this->setUploadFileHide($this->_scopeConfig->getValue('inquiry/label_hide/upload_file', ScopeInterface::SCOPE_STORE));
        $this->setExtraOneHide($this->_scopeConfig->getValue('inquiry/label_hide/extra_one', ScopeInterface::SCOPE_STORE));
        $this->setExtraTwoHide($this->_scopeConfig->getValue('inquiry/label_hide/extra_two', ScopeInterface::SCOPE_STORE));
        $this->setExtraThreeHide($this->_scopeConfig->getValue('inquiry/label_hide/extra_three', ScopeInterface::SCOPE_STORE));
    }

    public function isCaptchaEnable()
    {
        return $this->_scopeConfig->getValue('inquiry/label_hide/captcha', ScopeInterface::SCOPE_STORE);
    }
    /**
     * Returns action url for inquiry form
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl('inquiry/index/save', ['_secure' => true]);
    }
    
    public function getRefreshUrl()
    {
        return $this->getUrl('inquiry/index/refresh', ['_secure' => true]);
    }
    
    public function getCountryDropDown()
    {
        return $this->_country->toOptionArray();
    }
    
    public function getStateFromCountry($countrycode)
    {
        $statearray = [];
        if ($countrycode != '') {
            $statearray = $this->_countryFactory->create()->setId(
                $countrycode
            )->getLoadedRegionCollection()->toOptionArray();
        }
        return $statearray;
    }
            
    //add form data into session
    public function getFormData()
    {
        $data = $this->session->getInquiryFormData();
        $this->session->setInquiryFormData(null);
        return $data;
    }
}
