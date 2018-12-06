<?php
namespace Vsourz\HtmlSitemap\Block;

use Magento\Catalog\Model\Product\Visibility;

/**
 * Class View
 * @package Vsourz\HtmlSitemap\Block
 */
class ProductsList extends \Magento\Framework\View\Element\Template
{
    protected $_productsCollection;
    protected $_productCollectionFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\View\Element\Template\Context $context
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_products = $collection;
        parent::__construct($context);
    }

    public function getProductsAllCollection()
    {
        $this->_productsCollection = $this->_productCollectionFactory->create();
        $this->_productsCollection
        ->addAttributeToSelect('name')
        ->addAttributeToSort('name')
        ->setPageSize(0)
        ->addAttributeToFilter(
            [
                ['attribute'=> 'status','eq' => 1]
            ]
        )
        ->addAttributeToFilter(
            [
                [
                    'attribute'=> 'visibility',
                    'in' => [
                        \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG,
                        \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH,
                        \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
                    ]
                ]
            ]
        );
        return $this->_productsCollection;
    }

    public function getProductsCollection($char)
    {
        $this->_productsCollection = $this->_productCollectionFactory->create();
        $this->_productsCollection
        ->addAttributeToSelect('name')
        ->addAttributeToSort('name')
        ->setPageSize(0)
        ->addAttributeToFilter(
            [
                ['attribute'=> 'status','eq' => 1]
            ]
        )
        ->addAttributeToFilter(
            [
                [
                    'attribute'=> 'visibility',
                    'in' => [
                        \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_CATALOG,
                        \Magento\Catalog\Model\Product\Visibility::VISIBILITY_IN_SEARCH,
                        \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
                    ]
                ]
            ]
        );
        if ($char == '#') {
            $this->_productsCollection->addAttributeToFilter(
                [
                    ['attribute'=> 'name','regexp' => '^[^a-zA-Z]']
                ]
            );
        } else {
            $this->_productsCollection->addAttributeToFilter(
                [
                    ['attribute'=> 'name','like' => $char.'%']
                ]
            );
        }

        return $this->_productsCollection;
    }

    public function collectionToHtml($pcollection, $char)
    {
        $html = '';
        $charClass = '';
        if (count($pcollection)) {
            if ($char == '#') {
                $charClass = 'undefined';
            } else {
                $charClass = $char;
            }

            $charClass = strtolower($charClass);
            $html .= '<div class="product-list-by-char char-' . $charClass . '">';
            $html .= '<h5 class="active char-title"><span class="char">' . $char . '</span> <span class="pcount">(' . count($pcollection) . ')</span></h5>';
            $html .= '<ul>';
            foreach ($pcollection as $_product) :
                $html .= '<li class="product-list-item">';
                    $html .= '<a href="' . $_product->getProductUrl() . '" title="' . $_product->getName() . '">' . $_product->getName() . '</a>';
                $html .= '</li>';
            endforeach;
            $html .= '</ul>';
            $html .= '</div>';
        }
        return $html;
    }
}
