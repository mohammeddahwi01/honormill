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

use Magento\Framework\App\Filesystem\DirectoryList;
use PluginCompany\ProductPdf\System\Exec;

class MpdfInstaller
{
    /** @var Exec */
    private $exec;

    /** @var DirectoryList */
    private $directoryList;

    public function __construct(
        Exec $exec,
        DirectoryList $directoryList
    ) {
        $this->exec = $exec;
        $this->directoryList = $directoryList;
        return $this;
    }

    public function runInstall()
    {
        putenv('COMPOSER_HOME=' . $this->getRootDir() . '/vendor/bin/composer');
        $this->exec
            ->execute(
                "cd {$this->getRootDir()};
                 {$this->exec->getPhpExecutable()} ./vendor/bin/composer require --no-interaction mpdf/mpdf"
            );
        $this->exec
            ->execute(
                "cd {$this->getRootDir()};
                chmod -Rf 777 ./vendor/mpdf"
            );
        return $this;
    }

    private function getRootDir()
    {
        return $this->directoryList->getRoot();
    }

    static public function isMpdfInstalled()
    {
        if(class_exists('\\Mpdf\\Mpdf')){
            return true;
        }
        return false;
    }
}