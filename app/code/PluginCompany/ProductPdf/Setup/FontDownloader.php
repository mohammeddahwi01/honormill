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
namespace PluginCompany\ProductPdf\Setup;

use PluginCompany\ProductPdf\Model\FontDirIO;

class FontDownloader
{

    const FONT_PACKAGE_URL = 'https://codeload.github.com/PluginCompany/product-pdf-fonts-mirror/tar.gz/1.0.1';

    /** @var \Magento\Framework\Filesystem\Io\File  */
    private $io;

    /** @var FontDirIO  */
    private $fontDirIO;

    public function __construct(
        FontDirIO $fontDirIO
    ) {
        $this->fontDirIO = $fontDirIO;
        $this->io = $this->io = $fontDirIO->getIo();
        return $this;
    }

    public function installIfNotAvailable()
    {
        if($this->fontDirIO->doesFontDirExist()){
            return $this;
        }
        return $this->execute();
    }

    public function execute()
    {
        $this
            ->removeOldFiles()
            ->downloadPackage()
            ->extractPackage()
            ->renameFolder()
            ->cleanUp()
        ;
        return $this;
    }

    private function removeOldFiles()
    {
        if(is_dir($this->getFontDir())){
            $this->io->rmdir(
                $this->getFontDir(),
                true
            );
        }
        if(is_file($this->getFontPackagePath())) {
            $this->io->rm($this->getFontPackagePath());
        }
        $this->removeTempFontDir();
        return $this;
    }

    private function downloadPackage()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::FONT_PACKAGE_URL);
        $fp = fopen($this->getFontPackagePath(), 'w');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_exec ($ch);
        curl_close ($ch);
        fclose($fp);
        return $this;
    }

    private function getFontPackagePath($tarOnly = false)
    {
        if($tarOnly){
            return $this->getViewBaseDir() . 'fonts.tar';
        }
        return $this->getViewBaseDir() . 'fonts.tar.gz';
    }

    private function getViewBaseDir()
    {
        return $this->fontDirIO->getModuleDirReader()->getModuleDir(null, 'PluginCompany_ProductPdf')
            . '/view/base/';
    }

    private function getFontDir()
    {
        return $this->fontDirIO->getGoogleFontDir();
    }

    private function getExtractedPackageDir()
    {
        return $this->getViewBaseDir() . 'product-pdf-fonts-mirror-1.0.1';
    }

    private function extractPackage()
    {
        if(class_exists('\PharData')){
            $p = new \PharData($this->getFontPackagePath());
            $p->extractTo($this->getViewBaseDir());
        }else{
            exec("cd {$this->getViewBaseDir()}; tar -zxf {$this->getFontPackagePath()}");
        }
        return $this;
    }

    private function renameFolder()
    {
        @rename(
            $this->getExtractedPackageDir() . '/googlefonts',
            $this->getViewBaseDir() . 'googlefonts'
        );
        return $this;
    }

    private function cleanUp()
    {
        $this
            ->removePackage()
            ->removeTempFontDir();
        ;

        return $this;
    }

    private function removePackage()
    {
        $this->io->rm(
            $this->getFontPackagePath()
        );
        return $this;
    }

    private function removeTempFontDir()
    {
        if(!is_dir($this->getExtractedPackageDir())){
            return $this;
        }
       $this->io->rmdir(
            $this->getExtractedPackageDir(),
            true
        );
        return $this;
    }

}