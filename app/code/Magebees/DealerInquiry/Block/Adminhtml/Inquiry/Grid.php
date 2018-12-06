<?php
/***************************************************************************
 Extension Name	: Dealer Inquiry
 Extension URL	: https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright		: Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email	: support@magebees.com 
 ***************************************************************************/
namespace Magebees\DealerInquiry\Block\Adminhtml\Inquiry;

use \Magento\Reports\Block\Adminhtml\Sales\Grid\Column\Renderer\Date;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magebees\DealerInquiry\Model\InquiryFactory $inquiryFactory,
        array $data = []
    ) {
        $this->_inquiryFactory = $inquiryFactory;
        parent::__construct($context, $backendHelper, $data);
    }
    protected function _construct()
    {
        parent::_construct();
        $this->setId('inquiryGrid');
        $this->setDefaultSort('dealer_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
    
    protected function _prepareCollection()
    {
        $collection = $this->_inquiryFactory->create()->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('inquiry');
    
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                        'label' => __('Delete'),
                        'url' => $this->getUrl('inquiry/*/massDelete'),
                        'confirm' => __('Are you sure?')
                ]
        );
    
        return $this;
    }
        
    protected function _prepareColumns()
    {
        $this->addColumn(
            'dealer_id',
            [
                'header' => __('Dealer ID'),
                'type' => 'number',
                'index' => 'dealer_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        
        $this->addColumn(
            'first_name',
            [
                'header' => __('First Name'),
                'index' => 'first_name',
            ]
        );
        
        $this->addColumn(
            'last_name',
            [
                'header' => __('Last Name'),
                'index' => 'last_name',
            ]
        );
        
        $this->addColumn(
            'email',
            [
                'header' => __('Email'),
                'index' => 'email',
            ]
        );
        
        $this->addColumn(
            'is_cust_created',
            [
                'header' => __('Is Customer Created'),
                'index' => 'is_cust_created',
                'frame_callback' => [$this, 'isCustCreated'],
                'type' => 'options',
                'options' => \Magebees\DealerInquiry\Model\Status::getAvailableStatuses(),
            ]
        );
        
        $this->addColumn(
            'store_id',
            [
                'header' => __('Store View'),
                'index' => 'store_id',
                'type' => 'store',
            ]
        );
                
        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Create Customer'),
                        'url' => ['base' => '*/*/createcustomer'],
                        'field' => 'id',
                    ],
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action',
            ]
        );
        
        return parent::_prepareColumns();
    }
    
    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl(
            '*/*/edit',
            ['dealer_id' => $row->getId()]
        );
    }
    
    public function isCustCreated($value, $row, $column, $isExport)
    {
        if ($row->getIsCustCreated()) {
            $cell = '<span class="grid-severity-notice"><span>' . $value . '</span></span>';
        } else {
            $cell = '<span class="grid-severity-major"><span>' . $value . '</span></span>';
        }
        
        return $cell;
    }
}
