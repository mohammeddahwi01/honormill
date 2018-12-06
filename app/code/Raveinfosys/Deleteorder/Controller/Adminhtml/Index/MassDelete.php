<?php 
namespace Raveinfosys\Deleteorder\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Raveinfosys\Deleteorder\Model\Remove;
use Raveinfosys\Deleteorder\Helper\Data;

class MassDelete extends Action
{
    protected $_orderCollectionFactory;
	
    protected $request;
	
    protected $filter;
	
    protected $remove;
	
    protected $helper;

    /**
     * @var helper
     */
   
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $orderCollectionFactory,
        Remove $remove,
        Data $helper
    ) {
   
        $this->filter = $filter;
        $this->request = $context->getRequest();
        $this->remove = $remove;
        $this->helper = $helper;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context);
    }

    public function getOrderCollection()
    {
        $collection = $this->_orderCollectionFactory->create();
        return $collection;
    }
 
    public function execute()
    {
        if ($this->filter->getCollection($this->getOrderCollection())) {
            try {
                $collection = $this->filter->getCollection($this->getOrderCollection());
                $countCancelOrder = $this->remove->remove($collection);
                $countNonCancelOrder = $collection->count() - $countCancelOrder;
                if ($countNonCancelOrder && $countCancelOrder) {
                    $mod_setting_url = "Please check delete order <a href='".$this->getUrl('adminhtml/system_config/edit/section/deleteorder/', $paramsHere = ['_current'=>true])."'>configuration.</a>";
                    $this->messageManager->addError(__('Some order(s) could not be deleted. Only selected order status can be deleted. '.$mod_setting_url));
                }
                if ($countCancelOrder) {
                    $this->messageManager->addSuccess(__('Total of %1 record were successfully deleted.', $countCancelOrder));
                }
                $this->_redirect('sales/order/index');
            } catch (\Exception $e) {
                 $this->messageManager->addError($e->getMessage());
            }
        } else {
            $this->_redirect('sales/order/index');
        }
    }
}
