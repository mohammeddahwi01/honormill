<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Block\Adminhtml\GoogleWizard\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Element\Dependence;

class Delivery extends TabGeneric
{
    const STEP = 4;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Amasty\Feed\Model\ResourceModel\Feed\Grid\ExecuteModeList
     */
    private $mode;

    /**
     * @var \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory
     */
    private $fieldFactory;

    /**
     * @var \Amasty\Feed\Model\CronProvider
     */
    private $cronProvider;

    public function __construct(\Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Amasty\Feed\Model\ResourceModel\Feed\Grid\ExecuteModeList $mode,
        \Amasty\Feed\Model\RegistryContainer $registryContainer,
        \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory $fieldFactory,
        \Amasty\Feed\Model\CronProvider $cronProvider,
        array $data = []
    ) {
        $this->mode = $mode;
        $this->layoutFactory = $layoutFactory;
        $this->feldsetId = 'amfeed_delivery';
        $this->legend = __('Upload feeds to Google servers automatically?');
        $this->fieldFactory = $fieldFactory;
        $this->cronProvider = $cronProvider;
        parent::__construct($context, $registry, $formFactory, $registryContainer, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Step 4: Run and Upload');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Step 4: Run and Upload');
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareNotEmptyForm()
    {
        $model = $this->_coreRegistry->registry('current_amfeed_feed');

        if (!$model) {

            $this->_coreRegistry->register('current_amfeed_feed', true);

            list($categoryMappingId, $feedId, $step) = $this->getFeedStateConfiguration();

            /** @var \Magento\Framework\Data\Form $form */
            $form = $this->_formFactory->create();
            $form->setHtmlIdPrefix('feed_');

            $fieldset = $form->addFieldset('schedule_fieldset', ['legend' => __('Generate settings')]);

            $executeMode = $fieldset->addField(
                'execute_mode',
                'select',
                [
                    'label' => __('Generate feed'),
                    'name' => 'execute_mode',
                    'values' => $this->mode->toOptionArray()
                ]
            );

            $cronDay = $fieldset->addField(
                'cron_day',
                'select',
                [
                    'label' => __('Day'),
                    'name' => 'cron_day',
                    'values' => $this->cronProvider->getOptionWeekdays(),
                ]
            );

            $cronTime = $fieldset->addField(
                'cron_time',
                'select',
                [
                    'label' => __('Time'),
                    'name' => 'cron_time',
                    'values' => $this->cronProvider->getCronTime(),
                ]
            );

            $fieldset = $form->addFieldset('delivery_fieldset', ['legend' => __('FTP Settings')]);

            $fieldset->addField(
                'filename',
                'text',
                [
                    'name' => 'filename',
                    'label' => __('File Name'),
                    'title' => __('File Name'),
                    'required' => true
                ]
            );

            $fieldset->addField(
                'setup_complete',
                'hidden',
                [
                    'name'  => 'setup_complete',
                    'value' => 1
                ]
            );

            $enabledSelect = $fieldset->addField(
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
                    'required' => true,
                    'note' => '<small>' . __('Add port if necessary (example.com:321)') . '</small>'
                ]
            );

            $typeSelect = $fieldset->addField(
                'delivery_type',
                'select',
                [
                    'label' => __('Protocol'),
                    'title' => __('Protocol'),
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
                    'title' => __('User'),
                    'required' => true
                ]
            );

            $fieldset->addField(
                'delivery_password',
                'password',
                [
                    'name' => 'delivery_password',
                    'label' => __('Password'),
                    'title' => __('Password'),
                    'required' => true
                ]
            );

            $fieldset->addField(
                'delivery_path',
                'text',
                [
                    'name' => 'delivery_path',
                    'label' => __('Path'),
                    'title' => __('Path'),
                    'required' => true
                ]
            );

            $modeSelect = $fieldset->addField(
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

            $button = $fieldset->addField(
                'button',
                'button',
                []
            );
            $button->setRenderer(
                $this->getLayout()->createBlock('\Amasty\Feed\Block\Adminhtml\Feed\Edit\Tab\Buttons\TestConnection')
            );

            $dependArray = [];
            foreach ($fieldset->getChildren() as $element) {
                if ($element->getHtmlId() !== $enabledSelect->getHtmlId()) {
                    $this->addDepend($element->getHtmlId(), $enabledSelect->getHtmlId(), '1', $dependArray);
                }
            }
            $this->addDepend($modeSelect->getHtmlId(), $typeSelect->getHtmlId(), 'ftp', $dependArray);

            $this->addDepend($cronTime->getHtmlId(), $executeMode->getHtmlId(), 'schedule', $dependArray);
            $this->addDepend($cronDay->getHtmlId(), $executeMode->getHtmlId(), 'schedule', $dependArray);
            $this->depend($dependArray);

            $this->setForm($form);

            return parent::_prepareForm();
        }
    }

    /**
     * @param string $whatId
     * @param string $fromId
     * @param array|string $value
     * @param array[] $dependArray
     */
    private function addDepend($whatId, $fromId, $value, &$dependArray)
    {
        $dependArray[] = [
            'what' => $whatId,
            'from' => $fromId,
            'value' => is_array($value) ? implode(",", $value) : $value
        ];
    }

    /**
     * @param array[] $source
     */
    private function depend($source)
    {
        /** @var Dependence $block */
        $block = $this->getLayout()->createBlock(
            Dependence::class
        );

        /** @var array $depend */
        foreach ($source as $depend) {
            if ($depend['what'] == 'feed_filename' || $depend['what'] == 'feed_setup_complete') {
                continue;
            }
            $refField = $this->fieldFactory->create(
                [
                    'fieldData' => [
                        'value' => $depend['value'],
                        'separator' => ','
                    ],
                    'fieldPrefix' => ''
                ]
            );

            $block->addFieldMap($depend['what'], $depend['what'])
                ->addFieldMap($depend['from'], $depend['from'])
                ->addFieldDependence(
                    $depend['what'],
                    $depend['from'],
                    $refField
                );
        }

        $this->setChild(
            'form_after',
            $block
        );
    }
}
