<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace PHXSolution\Override\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Contact\Controller\Index\Index
{
    /**
     * Show Contact Us page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Contact Us'));
        return $resultPage;
    }
}
