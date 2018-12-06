<?php
/**
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
 */
namespace PluginCompany\ProductPdf\Adapter\PdfGenerator;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\AbstractBlock;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf as MpdfLib;
use PluginCompany\ProductPdf\Model\FontDirIO;
use PluginCompany\ProductPdf\Model\FontReader;

// Report all errors except E_NOTICE
// Mpdf library contains notice level errors
error_reporting(E_ALL & ~E_NOTICE);

class Mpdf extends DataObject implements PdfGeneratorInterface
{

    private $defaults = [
        'default_font' => 'opensans',
        'margin_left' => 0,
        'margin_right' => 0,
        'margin_top' => 12,
        'margin_bottom' => 5,
        'margin_header' => 0,
        'margin_footer' => 7,
    ];

    /** @var  \Mpdf\Mpdf */
    private $mpdf;
    /** @var AbstractBlock */
    private $block;
    /** @var FontReader */
    private $fontReader;
    /** @var FontDirIO */
    private $fontDirIO;

    public function __construct(
        FontReader $fontReader,
        FontDirIO $fontDirIO
    ) {
        $this->setData($this->defaults);
        $this->fontReader = $fontReader;
        $this->fontDirIO = $fontDirIO;
        return $this;
    }

    public function generate(AbstractBlock $block)
    {
        $this->block = $block;
        $this
            ->createMpdfWithSettings()
            ->addFooter()
            ->addHtmlContent()
            ->stream()
        ;
    }

    private function createMpdfWithSettings()
    {
        if(!$this->fontDirIO->doesFontDirExist()){
            throw new \Exception('Google font dir doesn\'t exist');
        }
        $this->mpdf = new MpdfLib(
            $this->getData() + $this->getFontDirs() + $this->getFontData()
        );
        $this->mpdf->useSubstitutions = true;
        $this->mpdf->use_kwt = true;
        return $this;
    }

    private function getFontDirs()
    {
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        return [
            'fontDir' => array_merge(
                $fontDirs, [ $this->getGoogleFontDir() ]
            )
        ];
    }

    private function getGoogleFontDir()
    {
        return $this->fontDirIO->getGoogleFontDir();
    }

    private function getFontData()
    {
        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];
        return [
            'fontdata' => array_merge(
                $fontData, $this->getGoogleFontData()
            )
        ];
    }

    private function getGoogleFontData()
    {
        return $this->fontReader->getAdditionalFontsForMpdf();
    }

    private function addFooter()
    {
        if(!$this->getHtmlFooter()) {
            return $this;
        }
        $this->mpdf->SetHtmlFooter(
            $this->getHtmlFooter()
        );
        return $this;
    }

    private function addHtmlContent()
    {
        $this->mpdf->WriteHTML(
            $this->block->toHtml()
        );
        return $this;
    }


    /**
     * @return string
     */
    private function stream()
    {
        $this->mpdf->Output(urldecode($this->getFileName()), 'I');
    }

    public function setDefaultFont($value)
    {
        return parent::setDefaultFont($value);
    }

    public function setMarginLeft($value)
    {
        return parent::setMarginLeft($value);
    }

    public function setMarginRight($value)
    {
        return parent::setMarginRight($value);
    }

    public function setMarginTop($value)
    {
        return parent::setMarginTop($value);
    }

    public function setMarginBottom($value)
    {
        return parent::setMarginBottom($value);
    }

    public function setMarginHeader($value)
    {
        return parent::setMarginHeader($value);
    }

    public function setMarginFooter($value)
    {
        return parent::setMarginFooter($value);
    }

    public function setOrientation($value)
    {
        return parent::setOrientation($value);
    }

    public function setFileName($value)
    {
        return parent::setFileName($value);
    }

    public function setHtmlFooter($value)
    {
        return parent::setHtmlFooter($value);
    }
}