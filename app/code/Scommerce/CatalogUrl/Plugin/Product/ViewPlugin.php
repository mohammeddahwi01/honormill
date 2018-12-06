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
 * Class ViewPlugin
 * @package Scommerce\CatalogUrl\Plugin\Product
 */
class ViewPlugin
{
    /* @var \Magento\Framework\Registry */
    protected $coreRegistry;

    /* @var \Magento\Framework\App\Response\Http */
    protected $response;

    /* @var \Magento\Framework\UrlInterface */
    protected $url;

    /* @var \Scommerce\CatalogUrl\Helper\Data */
    protected $helper;

    /**
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http $response
     * @param \Magento\Framework\UrlInterface $url
     * @param \Scommerce\CatalogUrl\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Framework\UrlInterface $url,
        \Scommerce\CatalogUrl\Helper\Data $helper
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->response     = $response;
        $this->url          = $url;
        $this->helper       = $helper;
    }

    /**
     * @param \Magento\Catalog\Controller\Product\View $subject
     * @param \Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page $result
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page|\Magento\Framework\App\Response\Http
     * @see \Magento\Catalog\Controller\Product\View::execute()
     */
    public function afterExecute(\Magento\Catalog\Controller\Product\View $subject, $result)
    {
        if(!$this->helper->isCatalogUrlActive()) return $result;

        /* @var \Magento\Catalog\Model\Product $product */
        $product = $this->coreRegistry->registry('current_product');
        if (! $product) return $result;
        $url = $product->getProductUrl();
        return $this->url->getCurrentUrl() == $url ? $result : $this->response->setRedirect($url);
    }

}
