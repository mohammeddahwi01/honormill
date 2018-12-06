<?php
namespace Raveinfosys\Deleteorder\Controller\Adminhtml\Order;

class Delete extends \Magento\Backend\App\Action
{
    protected $order;
    protected $remove;
    protected $request;
    protected $helper;
  
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Model\Order $order,
        \Raveinfosys\Deleteorder\Model\Remove $remove,
        \Raveinfosys\Deleteorder\Helper\Data $helper
    ) {
        $this->request = $context->getRequest();
        $this->remove = $remove;
        $this->order = $order;
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function getOrder()
    {
        $orderId = $this->request->getParam("order_id");
        $order = $this->order->load($orderId, 'entity_id');
        return $order;
    }
    
    public function execute()
    {
        $order = $this->getOrder();
        $allowedOrderStatus = $this->helper->getAllowedOrderStatus();

        if (($id = $this->request->getParam("order_id")) && (in_array($order->getStatus(), $allowedOrderStatus))) {
            try {
                $this->remove->removeById($order);
                $this->messageManager->addSuccess(__('Order has been deleted successfully.'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        } else {
			$configUrl = $this->getUrl(
				'adminhtml/system_config/edit/section/deleteorder/',
				$paramsHere = ['_current' => true]
			);

            $this->messageManager->addError(__('Only selected order status can be deleted. Please check Delete Order <a href="' . $configUrl . '">configuration.</a>'));
        }
		$this->_redirect('sales/order/index');
    }
}
