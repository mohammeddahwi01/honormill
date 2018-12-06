<?php
/**
 * Scommerce_CategoryUrl Plugin for Product Url rewrite
 *
 * @category   Scommerce
 * @package    Scommerce_CatalogUrl
 * @author     Sommerce Mage <core@scommerce-mage.co.uk>
 */

namespace Scommerce\CatalogUrl\Plugin\Product;

/**
 * Class Url
 * @package Scommerce\CatalogUrl\Plugin\Product
 */
class Url
{
    /* @var \Scommerce\CatalogUrl\Helper\Data */
    protected $helper;

    /* @var \Scommerce\CatalogUrl\Model\Product\Url */
    protected $url;

    /**
     * @param \Scommerce\CatalogUrl\Helper\Data $helper
     * @param \Scommerce\CatalogUrl\Model\Product\Url $url
     */
    public function __construct(
        \Scommerce\CatalogUrl\Helper\Data $helper,
        \Scommerce\CatalogUrl\Model\Product\Url $url
    ) {
        $this->helper = $helper;
        $this->url    = $url;
    }

    /**
     * @param \Magento\Catalog\Model\Product\Url $subject
     * @param \Magento\Catalog\Model\Product $product
     * @param array $params
     * @return array
     * @see \Magento\Catalog\Model\Product\Url::getUrl()
     */
    public function beforeGetUrl(
        \Magento\Catalog\Model\Product\Url $subject,
        \Magento\Catalog\Model\Product $product,
        $params = []
    ) {
        if ($this->helper->isCatalogUrlActive()) {
            $requestPath = $this->url->generateProductRequestPath($product);
            if ($requestPath) $product->setRequestPath($requestPath);
        }

        return [$product, $params];
    }

}
