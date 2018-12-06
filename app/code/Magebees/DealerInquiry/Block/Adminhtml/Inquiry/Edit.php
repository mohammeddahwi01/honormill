<?php
/***************************************************************************
 Extension Name	: Dealer Inquiry
 Extension URL	: https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright		: Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email	: support@magebees.com 
 ***************************************************************************/
namespace Magebees\DealerInquiry\Block\Adminhtml\Inquiry;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Magebees_DealerInquiry';
        $this->_controller = 'adminhtml_inquiry';

        parent::_construct();

       //$this->buttonList->update('save', 'label', __('Save Block'));
        $this->buttonList->remove('save');
       
        $this->addButton('adminhtml_inquiry', [
            'label' => __('Create Customer'),
            'class' =>'save',
            'onclick' => "setLocation('{$this->getUrl('*/*/createcustomer', array('id' => $this->getRequest()->getParam('dealer_id')))}')",
        ]);
        //  $this->buttonList->update('delete', 'label', __('Delete Dealer'));
            $this->buttonList->add(
                'delete',
                [
                'label'=>'Delete Dealer',
                'onclick' => "setLocation('{$this->getUrl('*/*/delete', array('id' => $this->getRequest()->getParam('dealer_id')))}')",
                ]
            );
       /*  $this->buttonList->add(
            'saveandcontinue',
            array(
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => array(
                    'mage-init' => array('button' => array('event' => 'saveAndContinueEdit', 'target' => '#edit_form'))
                )
            ),
            -100
        ); */

            $this->_formScripts[] = "
            
        ";
    }
}
