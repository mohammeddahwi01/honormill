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
namespace PluginCompany\ProductPdf\Model;

use Magento\Framework\Filesystem\Io\FileFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Module\Dir\Reader;

class FontDirIO
{
    /** @var FileFactory */
    private $ioFileFactory;

    /** @var \Magento\Framework\Filesystem\Io\File  */
    private $io;

    /** @var Reader */
    private $moduleDirReader;

    public function __construct(
        Context $context,
        FileFactory $ioFileFactory,
        Reader $moduleDirReader
    )
    {
        $this->ioFileFactory = $ioFileFactory;
        $this->io = $this->ioFileFactory->create();
        $this->moduleDirReader = $moduleDirReader;
        return $this;
    }

    public function readFontDir()
    {
        $this->io->open(array('path' => $this->getGoogleFontDir()));
        return $this->io->ls();
    }

    public function doesFontDirExist()
    {
        return is_dir($this->getGoogleFontDir());
    }

    public function getGoogleFontDir()
    {
        return $this->moduleDirReader->getModuleDir(null, 'PluginCompany_ProductPdf')
            . '/view/base/googlefonts/';
    }

    /**
     * @return \Magento\Framework\Filesystem\Io\File
     */
    public function getIo()
    {
        return $this->io;
    }

    /**
     * @return Reader
     */
    public function getModuleDirReader()
    {
        return $this->moduleDirReader;
    }



}

