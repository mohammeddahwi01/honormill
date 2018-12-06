<?php

namespace PluginCompany\ProductPdf\Controller\Adminhtml\Mpdf;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use PluginCompany\ProductPdf\Controller\Adminhtml\Mpdf;
use PluginCompany\ProductPdf\Setup\MpdfInstaller;

class Install extends Mpdf
{
    /**
     * @var MpdfInstaller
     */
    private $installer;

    /**
     * @param Context $context
     * @param MpdfInstaller $installer
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        MpdfInstaller $installer
    ) {
        $this->installer = $installer;
        parent::__construct($context, $resultPageFactory);
    }


    public function execute()
    {
        if($this->isMpdfInstalled()){
            return $this->_redirect('*/*/index');
        }
        return $this->runInstall();
    }

    private function runInstall()
    {
        $this->installMPDF();
        return $this->_redirect('*/*/index', ['tried_automatic_install' => true]);
    }

    private function installMPDF()
    {
        $this->installer
            ->runInstall();
        return $this;
    }

    private function isMpdfInstalled()
    {
        return MpdfInstaller::isMpdfInstalled();
    }

}
