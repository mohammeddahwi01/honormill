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
namespace PluginCompany\ProductPdf\Block\Pdf\Content;

use PluginCompany\ProductPdf\Block\Pdf\Content;

class Price extends Content
{

    protected $_template = 'PluginCompany_ProductPdf::pdf/content/price.phtml';

    public function getPriceHtml()
    {
        if($this->isProductBundle()){
            return $this->getBundlePriceHtml();
        }
        return $this->getProduct()->getFormatedPrice();
    }

    public function getBundlePriceHtml()
    {
        return $this->getBlockHtmlWithProduct(
            'PluginCompany\ProductPdf\Block\Pdf\Content\Bundle\Price'
        );
    }

}

