<?php
namespace PluginCompany\ProductPdf\Controller\Download\File;

class CallExit extends \Magento\Framework\App\Response\Http\FileFactory
{

    public function doCallExit()
    {
        $this->callExit();
    }

}