<?php
/***************************************************************************
 Extension Name : Dealer Inquiry
 Extension URL  : https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright      : Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email  : support@magebees.com 
 ***************************************************************************/
namespace Magebees\DealerInquiry\Model;

class PageLayout implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface
     */
    protected $pageLayoutBuilder;

    /**
     * @param \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder
     */
    public function __construct(\Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder)
    {
        $this->pageLayoutBuilder = $pageLayoutBuilder;
    }
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->pageLayoutBuilder->getPageLayoutsConfig()->toOptionArray();
    }
}
