<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionBase\Plugin;

use \Magento\Catalog\Block\Product\View\Options;
use \Magento\Catalog\Model\Product\Option;
use \Zend\Stdlib\StringWrapper\MbString;

/**
 * This plugin adds option_id to html elements.
 */
class AroundOptionsHtml
{
    /**
     * @var MbString
     */
    protected $mbString;
    
    /**
     * These nodes should be found and filled
     * before return to the page.
     *
     * @var array
     */
    protected $voidNodes = [
        'textarea'
    ];

    /**
     * @param MbString $mbString
     */
    public function __construct(
        MbString $mbString
    ) {
        $this->mbString = $mbString;
    }


    /**
     * @param Options $subject
     * @param \Closure $proceed
     * @return string
     */
    public function aroundGetOptionHtml(Options $subject, \Closure $proceed, Option $option)
    {
        $result = $proceed($option);
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;

        $this->mbString->setEncoding('UTF-8', 'html-entities');
        $result = $this->mbString->convert($result);

        libxml_use_internal_errors(true);
        $dom->loadHTML($result);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        // find and prepare void nodes
        $query = '//*[not(node())]';
        $nodes = $xpath->query($query);

        foreach ($nodes as $node) {
            if (in_array($node->nodeName, $this->voidNodes)) {
                // Fill the found node with 'NOT_VOID' comment. Remove this comment later before return $result.
                $node->appendChild(new \DOMComment('NOT_VOID'));
            }
        }

        $xpath->query('//div')->item(0)->setAttribute("option_id", $option->getMageworxOptionId());

        $resultBody = $dom->getElementsByTagName('body')->item(0);
        $result = $this->getInnerHtml($resultBody, $option);
        return str_replace('<!--NOT_VOID-->', '', $result);
    }

    /**
     * @param \DOMElement $node
     * @param Option $option
     * @return string
     */
    protected function getInnerHtml(\DOMElement $node, Option $option)
    {
        $innerHTML= '';
        $children = $node->childNodes;
        foreach ($children as $child) {
            $innerHTML .= $child->ownerDocument->saveXML($child);

            if (strpos($innerHTML, '</script>') !== false) {
                $innerHTML = str_replace(
                    ['<script type="text/x-magento-init"><![CDATA[', ']]></script>'],
                    ['<script type="text/x-magento-init">', '</script>'],
                    $innerHTML
                );
                if ($option->getType() == Option::OPTION_TYPE_DATE ||
                    $option->getType() == Option::OPTION_TYPE_DATE_TIME ||
                    $option->getType() == Option::OPTION_TYPE_TIME
                ) {
                    $innerHTML = str_replace(
                        ['<![CDATA['],
                        [''],
                        $innerHTML
                    );
                    $innerHTML = str_replace(
                        [']]>'],
                        [''],
                        $innerHTML
                    );
                }
            }
        }

        return $innerHTML;
    }
}
