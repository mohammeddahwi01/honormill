<?php
/* 
 * @package Credevlabz/Testimonials
 * @category Controller
 * @author Aman Srivastava <http://amansrivastava.in>
 *
 */

namespace Credevlabz\Testimonials\Controller\Index;

use \Magento\Framework\App\Action\Action;

class Index extends Action
{
    /** @var  \Magento\Framework\View\Result\Page */
    protected $resultPageFactory;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     */
    public function __construct(\Magento\Framework\App\Action\Context $context,
                                \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Testimonials Index, shows a list of recent Testimonials.
     *
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $resultPageFactory = $this->resultPageFactory->create();

        // Add page title
        $resultPageFactory->getConfig()->getTitle()->set(__('Testimonials'));

        // Add breadcrumb
        /** @var \Magento\Theme\Block\Html\Breadcrumbs */
        $breadcrumbs = $resultPageFactory->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb('home', [
                'label' => __('Home'),
                'title' => __('Home'),
                'link' => $this->_url->getUrl('')
            ]
        );
        $breadcrumbs->addCrumb('credevlabz_testimonials', [
                'label' => __('Testimonials'),
                'title' => __('Testimonials')
            ]
        );
        //return $this->resultPageFactory->create();
        return $resultPageFactory;
    }
}