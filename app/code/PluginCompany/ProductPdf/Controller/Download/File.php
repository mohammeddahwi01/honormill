<?php
/**
 *
 * Created by:  Milan Simek
 * Company:     Plugin Company
 *
 * LICENSE: http://plugin.company/docs/magento-extensions/magento-extension-license-agreement
 *
 * YOU WILL ALSO FIND A PDF COPY OF THE LICENSE IN THE DOWNLOADED ZIP FILE
 *
 * FOR QUESTIONS AND SUPPORT
 * PLEASE DON'T HESITATE TO CONTACT US AT:
 *
 * SUPPORT@PLUGIN.COMPANY
 *
 */

namespace PluginCompany\ProductPdf\Controller\Download;

use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use PluginCompany\ProductPdf\Adapter\PdfGenerator\Mpdf;
use PluginCompany\ProductPdf\Adapter\PdfGenerator\PdfGeneratorInterface;
use PluginCompany\ProductPdf\Model\FontDirIO;
use PluginCompany\ProductPdf\Setup\FontDownloader;
use Psr\Log\LoggerInterface;
use PluginCompany\ProductPdf\Controller\Download\File\CallExit;

class File extends Action {

    /**
     * @var ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var PdfGeneratorInterface
     */
    private $pdfGenerator;

    private $product;

    private $pdfBlock;
    /**
     * @var CallExit
     */
    private $callExit;
    /**
     * @var FontDownloader
     */
    private $fontDownloader;

    public function __construct(
        Context $context,
        ProductFactory $catalogProductFactory,
        Registry $registry,
        LoggerInterface $logger,
        Mpdf $pdfGenerator,
        FontDownloader $fontDownloader,
        CallExit $callExit
    ) {
        $this->catalogProductFactory = $catalogProductFactory;
        $this->registry = $registry;
        $this->logger = $logger;
        $this->pdfGenerator = $pdfGenerator;
        $this->callExit = $callExit;
        $this->fontDownloader = $fontDownloader;
        parent::__construct(
            $context
        );
    }

    public function execute()
    {
        if (!$this->getProductId()) {
            return $this->getResponse()->setBody(__("Error downloading file"));
        }
        try {
            return $this->runExecute();
        } catch (\Exception $e){
            $this->logger->log(500, $e->getMessage());
        }
    }

    private function runExecute()
    {
        if(!$this->isMpdfInstalled()){
            return $this->showMpdfNotFoundError();
        }
        $this
            ->downloadFontsIfNeeded()
            ->registerProduct()
        ;
        if($this->getRequest()->getParam('html')){
            return $this->printHtml();
        }
        return $this->generatePdf();
    }

    private function isMpdfInstalled()
    {
        return class_exists('Mpdf\Mpdf');
    }

    private function downloadFontsIfNeeded()
    {
        $this->fontDownloader->installIfNotAvailable();
        return $this;
    }

    private function registerProduct()
    {
        $this->registry->register(
            'current_product',
            $this->getProduct()
        );
        $this->registry->register(
            'product',
            $this->getProduct()
        );
        return $this;
    }

    private function printHtml()
    {
        return $this->getResponse()
            ->setBody(
                $this->getPdfBlock()->toHtml()
            );
    }

    private function getPdfBlock()
    {
        if (!$this->hasPdfBlock()) {
            $this->initPdfBlock();
        }
        return $this->pdfBlock;
    }

    private function hasPdfBlock()
    {
        return isset($this->pdfBlock);
    }

    private function initPdfBlock()
    {
        $this->pdfBlock = $this->getNewPdfBlock();
        return $this;
    }

    private function getNewPdfBlock()
    {
        return $this->_view->getLayout()
            ->createBlock('PluginCompany\ProductPdf\Block\Pdf')
            ->setProduct($this->getProduct());
    }

    private function getProduct()
    {
        if (!$this->hasProduct()) {
            $this->initProduct();
        }
        return $this->product;
    }

    private function hasProduct()
    {
        return isset($this->product);
    }

    private function initProduct()
    {
        $this->product = $this->catalogProductFactory->create()
            ->load($this->getProductId());
        return $this;
    }

    private function getProductId()
    {
        return $this->getRequest()->getParam('id');
    }

    private function generatePdf()
    {
        try {
            $this->addFooterToPdf();
            $this->streamPdf();
            $this->callExit();
        }
        catch(\Throwable $e) {
            $this->handleError($e);
        }
        catch(\Exception $e) {
            $this->handleError($e);
        }
    }

    private function handleError($e)
    {
        if(stristr($e->getMessage(), 'Temporary files directory')){
            return $this->showTempFileWriteError();
        }
        if(stristr($e->getMessage(), 'Permission denied')){
            return $this->showTempFileWriteError();
        }
        throw $e;
    }

    private function showTempFileWriteError()
    {
        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $this->getResponse();
        return $response
            ->setContent(
                $this->_view
                    ->getLayout()
                    ->createBlock('Magento\Framework\View\Element\Template')
                    ->setTemplate('PluginCompany_ProductPdf::error/mpdf_temp_dir.phtml')
                    ->toHtml()
            )
            ->sendResponse();
    }

    private function showMpdfNotFoundError()
    {
        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $this->getResponse();
        $response
            ->setContent(
                "<p>Please install the mPDF library on your server to view the PDF file. You can use the automatic installer in the admin area to finish the installation.</p>"
            )
            ->sendResponse()
            ;
    }

    private function addFooterToPdf()
    {
        if(!$this->getPdfBlock()->canShowFooter()) {
            return $this;
        }
        $this->pdfGenerator
            ->setMarginBottom(15)
            ->setHtmlFooter($this->getPdfBlock()->getFooterHtml())
        ;
        return $this;
    }


    private function streamPdf()
    {
        $this->pdfGenerator
            ->setFileName($this->getFileName())
            ->generate($this->getPdfBlock());
    }

    private function getFileName()
    {
        return urldecode(str_replace(' ', '_', $this->getProduct()->getName())).'.pdf';
    }

    /**
     * Call exit
     *
     * @return void
     */
    protected function callExit()
    {
        $this->callExit->doCallExit();
    }

}
