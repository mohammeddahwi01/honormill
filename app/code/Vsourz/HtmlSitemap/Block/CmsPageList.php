<?php

namespace Vsourz\HtmlSitemap\Block;

/**
 * Class View
 * @package Vsourz\HtmlSitemap\Block
 */
class CmsPageList extends \Magento\Framework\View\Element\Template
{
    protected $customerSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
    }

    public function isUserLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    public function getAllCmsPages()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cmscollection = $objectManager->get('\Magento\Cms\Model\ResourceModel\Page\CollectionFactory')->create();
        // add Filter if you want
        $cmscollection->addFieldToFilter('is_active', \Magento\Cms\Model\Page::STATUS_ENABLED);
        $pages = [];
        foreach ($cmscollection as $page) {
            if ($page->getIdentifier() == 'no-route') {
                continue;
            }

            $cmsId = $page->getId();
            $pages[$cmsId]['id'] = $page->getId();
            $pages[$cmsId]['title'] = $page->getTitle();
            $pages[$cmsId]['link_url'] = $page->getIdentifier();
        }

        return $pages;
    }
}
