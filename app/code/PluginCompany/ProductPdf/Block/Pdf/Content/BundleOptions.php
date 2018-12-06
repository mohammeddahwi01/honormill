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

class BundleOptions extends Content
{
    protected $_template = 'PluginCompany_ProductPdf::pdf/content/options/bundle.phtml';

    /**
     * Return an array of bundle product options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->getLayout()
            ->createBlock('Magento\Bundle\Block\Catalog\Product\View\Type\Bundle')
            ->setProduct($this->getProduct())
            ->getOptions();
    }

    public function getFullImageUrl($path)
    {
        return $this->getFullProductImageUrl($path);
    }

    public function getFormattedItemPrice($item)
    {
        return $this->formatCurrency(
            $this->getItemPrice($item)
        );
    }

    private function getItemPrice($item)
    {
        return $item->getPrice();
    }

}

