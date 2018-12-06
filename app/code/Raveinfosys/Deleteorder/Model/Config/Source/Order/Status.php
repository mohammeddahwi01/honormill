<?php 
namespace Raveinfosys\Deleteorder\Model\Config\Source\Order;

use Magento\Sales\Model\ResourceModel\Order\Status\Collection;
use Magento\Framework\Option\ArrayInterface;

class Status implements ArrayInterface
{
    protected $orderStatusCollection;
	
    public function __construct(
        Collection $orderStatusCollection
    ) {
         $this->orderStatusCollection = $orderStatusCollection;
    }

    public function toOptionArray()
    {
        return $this->orderStatusCollection->toOptionArray();
    }
}
