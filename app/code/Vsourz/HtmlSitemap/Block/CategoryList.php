<?php

namespace Vsourz\HtmlSitemap\Block;

/**
 * Class View
 * @package Vsourz\HtmlSitemap\Block
 */
class CategoryList extends \Magento\Framework\View\Element\Template
{
    protected $categoryFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryFactory
    ) {
        parent::__construct($context);
        $this->categoryFactory = $categoryFactory;
    }

    public function catagorylistrecursiveHtml($categoryy)
    {
        $html = '';

        $html .= '<ul class="cat">';
            foreach ($categoryy as $catt) :
                $haschildren = '';
                if (count($catt->getChildrenCategories())) {
                    $haschildren = ' isparent';
                }

                $html .= '<li class="category-item level-' . ($catt->getLevel() - 1) . $haschildren .'">';
                    $html .= '<a href="'.$catt->getUrl(). '" title="' . $catt->getName() . '">';
                        $html .= $catt->getName();
                    $html .= '</a>';
                    if ($haschildren) :
                        $html .= $this->catagorylistrecursiveHtml($catt->getChildrenCategories());
                    endif;
                $html .= '</li>';
            endforeach;
        $html .= '</ul>';
        return $html;
    }

    public function getCatCollection()
    {

        $categories = $this->categoryFactory->create()
            ->addAttributeToFilter('is_active', 1)
            ->addLevelFilter(1)
            ->addAttributeToSort('position')
            ->addAttributeToSelect('name');

        return $categories;
    }
}
