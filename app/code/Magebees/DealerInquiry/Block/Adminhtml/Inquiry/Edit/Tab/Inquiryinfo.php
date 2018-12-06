<?php
/***************************************************************************
 Extension Name	: Dealer Inquiry
 Extension URL	: https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright		: Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email	: support@magebees.com 
 ***************************************************************************/
namespace Magebees\DealerInquiry\Block\Adminhtml\Inquiry\Edit\Tab;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class Inquiryinfo extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_systemStore;
    protected $_countryFactory;
    protected $_scopeConfig;
    protected $_storeManager;
    protected $_country;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Directory\Model\Config\Source\Country $country,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_country = $country;
        $this->_countryFactory = $countryFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

   
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('inquiry');
        $countrycode = $model->getCountry();
        $dealer_data = $model->getData();
        $country = $this->_country->toOptionArray();
        foreach ($country as $country) {
            if ($country['value']==$model->getCountry()) {
                $dealer_data['country'] = $country['label'];
                break;
            }
        }
                
        $statearray =$this->_countryFactory->create()->setId(
            $countrycode
        )->getLoadedRegionCollection()->toOptionArray();
        if (!empty($statearray)) {
            foreach ($statearray as $_state) {
                if ($_state['value']==$dealer_data['state']) {
                    $dealer_data['state'] = $_state['label'];
                    break;
                }
            }
        }
        
        if ($model->getIsCustCreated() == '1') {
            $dealer_data['is_cust_created']="Created";
        } else {
            $dealer_data['is_cust_created']="Not Created";
        }
        
        //set storeview name
        $dealer_data['store_id'] = $this->_systemStore->getStoreName($dealer_data['store_id']);
        
        $isElementDisabled = false;
      
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Dealer Details')]);

        if ($model->getId()) {
            $fieldset->addField('dealer_id', 'hidden', ['name' => 'dealer_id']);
        }

        $fieldset->addField(
            'first_name',
            'label',
            [
                'name' => 'first_name',
                'label' => __('First Name'),
                'title' => __('First Name'),
            ]
        );
        
		$fieldset->addField(
			'last_name',
			'label',
			[
				'name' => 'last_name',
				'label' => __('Last Name'),
				'title' => __('Last Name'),
			]
		);
        
        $fieldset->addField(
            'email',
            'label',
            [
                'name' => 'email',
                'label' => __('Email'),
                'title' => __('Email'),
            ]
        );
        $fieldset->addField(
            'company',
            'label',
            [
                'name' => 'company',
                'label' => __('Company Name'),
                'title' => __('Company Name'),
            ]
        );
        if ($dealer_data['taxvat']) {
            $fieldset->addField(
                'taxvat',
                'label',
                [
                    'name' => 'taxvat',
                    'label' => __('Tax/VAT Number'),
                    'title' => __('Tax/VAT Number'),
                ]
            );
        }
        if ($dealer_data['address']) {
            $fieldset->addField(
                'address',
                'label',
                [
                    'name' => 'address',
                    'label' => __('Address'),
                    'title' => __('Address'),
                ]
            );
        
        
            $fieldset->addField(
                'city',
                'label',
                [
                'name' => 'city',
                'label' => __('City'),
                'title' => __('City'),
                ]
            );
        
        
            $fieldset->addField(
                'state',
                'label',
                [
                'name' => 'state',
                'label' => __('State'),
                'title' => __('State'),
                ]
            );
        
        
            $fieldset->addField(
                'country',
                'label',
                [
                'name' => 'country',
                'label' => __('Country'),
                'title' => __('country'),
                ]
            );
        
        
            $fieldset->addField(
                'zip',
                'label',
                [
                'name' => 'zip',
                'label' => __('Zip'),
                'title' => __('Zip'),
                ]
            );
        
			$fieldset->addField(
				'phone',
				'label',
				[
					'name' => 'phone',
					'label' => __('Phone'),
					'title' => __('Phone'),
				]
			);
		}
        if ($dealer_data['website']) {
            $fieldset->addField(
                'website',
                'label',
                [
                'name' => 'website',
                'label' => __('Website'),
                'title' => __('Website'),
                ]
            );
        }
        $fieldset->addField(
            'is_cust_created',
            'label',
            [
                'name' => 'is_cust_created',
                'label' => __('Is Customer Created'),
                'title' => __('Is Customer Created'),
            ]
        );
        $fieldset->addField(
            'bus_desc',
            'label',
            [
                'name' => 'bus_desc',
                'label' => __('Business Description'),
                'title' => __('Business Description'),
            ]
        );
        $fieldset->addField(
            'store_id',
            'label',
            [
                'name' => 'store_id',
                'label' => __('Store View'),
                'title' => __('Store View'),
            ]
        );
        $fieldset->addField(
            'creation_time',
            'label',
            [
                'name' => 'creation_time',
                'label' => __('Created Date'),
                'title' => __('Created Date'),
                //'format'=> 'dd/MM/yyyy HH:mm:ss',
            ]
        );
        
        if ($dealer_data['date_time']) {
            $this->setDateTime($this->_scopeConfig->getValue('inquiry/change_label/date_time', ScopeInterface::SCOPE_STORE));
            $fieldset->addField(
                'date_time',
                'label',
                [
                    'name' => 'date_time',
                    'label' => __($this->getDateTime()),
                    'title' => __($this->getDateTime()),
                ]
            );
        }
    
        if ($dealer_data['extra_one']) {
            $this->setExtraOne($this->_scopeConfig->getValue('inquiry/change_label/extra_one', ScopeInterface::SCOPE_STORE));
            $fieldset->addField(
                'extra_one',
                'label',
                [
                    'name' => 'extra_one',
                    'label' => __($this->getExtraOne()),
                    'title' => __($this->getExtraOne()),
                ]
            );
        }
        
        if ($dealer_data['extra_two']) {
            $this->setExtraTwo($this->_scopeConfig->getValue('inquiry/change_label/extra_two', ScopeInterface::SCOPE_STORE));
            $fieldset->addField(
                'extra_two',
                'label',
                [
                    'name' => 'extra_two',
                    'label' => __($this->getExtraTwo()),
                    'title' => __($this->getExtraTwo()),
                ]
            );
        }
        
        if ($dealer_data['extra_three']) {
            $this->setExtraThree($this->_scopeConfig->getValue('inquiry/change_label/extra_three', ScopeInterface::SCOPE_STORE));
            $fieldset->addField(
                'extra_three',
                'label',
                [
                    'name' => 'extra_three',
                    'label' => __($this->getExtraThree()),
                    'title' => __($this->getExtraThree()),
                ]
            );
        }
            
        $path = $this->_storeManager->getStore()->getBaseUrl(DirectoryList::MEDIA) . 'inquiry';
        if ($dealer_data['file_name']) {
            $file_names = explode('|', $dealer_data['file_name']);
            foreach ($file_names as $key => $file) {
                $file_value = "<a href='".$path.$file."' target='_blank'>".$file."</a>";
                $fieldset->addField(
                    'filename_'.$key,
                    'label',
                    [
                        'name' => 'filename_'.$key,
                        'label' => __('Uploaded File'),
                        'title' => __('Uploaded File'),
                        'after_element_html' => $file_value,
                    ]
                );
            }
        }
            
        $form->setValues($dealer_data);
        //$form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Dealer Details');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Dealer Details');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
