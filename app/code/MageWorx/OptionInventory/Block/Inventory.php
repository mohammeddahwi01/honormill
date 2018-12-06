<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OptionInventory\Block;

use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Inventory extends Template
{
    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        $this->jsonEncoder = $jsonEncoder;
    }

    /**
     * @return string
     */
    public function getJsonData()
    {
        $data = [
            'stock_message_url' => $this->_urlBuilder->getUrl('mageworx_optioninventory/stockmessage/update')
        ];

        return $this->jsonEncoder->encode($data);
    }
}
