<?php
/**
 * Magento\Catalog\Model\Product\Url descendant to get protected fields
 *
 * @category   Scommerce
 * @package    Scommerce_CatalogUrl
 * @author     Sommerce Mage <core@scommerce-mage.co.uk>
 */

namespace Scommerce\CatalogUrl\Model\Product;

/**
 * Class Url
 * @package Scommerce\CatalogUrl\Model\Product
 */
class Url
{
    /* @var \Magento\Catalog\Model\CategoryFactory */
    protected $categoryFactory;

    /* @var \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator */
    protected $productUrlPathGenerator;

    /* @var \Scommerce\CatalogUrl\Helper\Data */
    protected $helper;

    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator $productUrlPathGenerator,
        \Scommerce\CatalogUrl\Helper\Data $helper
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->productUrlPathGenerator = $productUrlPathGenerator;
        $this->helper = $helper;
    }

    /**
     * Generating product request path
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function generateProductRequestPath($product)
    {
        $product = $product->load($product->getId()); // Really hack for product listing in grid/list
        $productPrimaryCategory = $product->getCustomAttribute('product_primary_category');
        if (! $productPrimaryCategory) return '';
        $primaryCategoryId = $productPrimaryCategory->getValue();
        if (is_array($primaryCategoryId)) $primaryCategoryId = end($primaryCategoryId);
        $primaryCategoryId = (int)$primaryCategoryId;
        if (empty($primaryCategoryId)) return '';
        $category = $this->categoryFactory->create()->load($primaryCategoryId);
        if (! $category->getId()) return '';
        $category = $this->helper->getRemoveCategoryPath() ? null : $category;
        return $this->productUrlPathGenerator
            ->getUrlPathWithSuffix($product, $product->getStoreId(), $category);
    }

}
