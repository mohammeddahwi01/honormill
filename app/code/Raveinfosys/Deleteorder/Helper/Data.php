<?php 
namespace Raveinfosys\Deleteorder\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    protected $_scopeConfig;
	
	const XML_PATH_ORDER_STATUS = 'deleteorder/general/order_status';

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->_scopeConfig = $scopeConfig;
    }

    public function getAllowedOrderStatus()
    {
        $statuses = $this->_scopeConfig->getValue(
			self::XML_PATH_ORDER_STATUS,
			ScopeInterface::SCOPE_STORE
		);
        $allowedStatus = explode(",", $statuses);
        return $allowedStatus;
    }
	
}
