<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

namespace Amasty\Feed\Block\Adminhtml\Feed\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Delivery extends Generic implements TabInterface
{
    protected $_systemStore;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getTabLabel()
    {
        return __('Delivery');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('Delivery');
    }

    /**
     * Returns status flag about this tab can be showed or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return Form
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_amfeed_feed');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('feed_');

        $fieldset = $form->addFieldset('delivery_fieldset', ['legend' => __('Delivery')]);

        $fieldset->addField(
            'delivery_enabled',
            'select',
            [
                'label' => __('Enabled'),
                'title' => __('Enabled'),
                'name' => 'delivery_enabled',
                'options' => [
                    '1' => __('Yes'),
                    '0' => __('No')
                ]
            ]
        );

        $fieldset->addField(
            'delivery_host',
            'text',
            [
                'name' => 'delivery_host',
                'label' => __('Host'),
                'title' => __('Host'),
                'after_element_html' => '<small>'.__('Add port if necessary (example.com:321)').'</small>'
            ]
        );

        $fieldset->addField(
            'delivery_type',
            'select',
            [
                'label' => __('Delivery Type'),
                'title' => __('Delivery Type'),
                'name' => 'delivery_type',
                'options' => [
                    'ftp' => __('FTP'),
                    'sftp' => __('SFTP')
                ],
            ]
        );

        $fieldset->addField(
            'delivery_user',
            'text',
            [
                'name' => 'delivery_user',
                'label' => __('User'),
                'title' => __('User')
            ]
        );

        $fieldset->addField(
            'delivery_password',
            'password',
            [
                'name' => 'delivery_password',
                'label' => __('Password'),
                'title' => __('Password')
            ]
        );

        $fieldset->addField(
            'delivery_path',
            'text',
            [
                'name' => 'delivery_path',
                'label' => __('Path'),
                'title' => __('Path')
            ]
        );

        $fieldset->addField(
            'delivery_passive_mode',
            'select',
            [
                'label' => __('Passive Mode'),
                'title' => __('Passive Mode'),
                'name' => 'delivery_passive_mode',
                'options' => [
                    '1' => __('Yes'),
                    '0' => __('No')
                ]
            ]
        );

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getFormHtml()
    {
        $formHtml = parent::getFormHtml();

        $formHtml .= '<script>
            require(["prototype"], function(){
                function onChangeDeliveryType(){
                    if ($("feed_delivery_type" ).value == "ftp"){
                        $("feed_delivery_passive_mode").up(".admin__field").show();
                    } else {
                        $("feed_delivery_passive_mode").up(".admin__field").hide()
                    }
                }

                $("feed_delivery_type" ).observe("change", onChangeDeliveryType);

                onChangeDeliveryType();
            });
        </script>';

        return $formHtml;
    }
}
