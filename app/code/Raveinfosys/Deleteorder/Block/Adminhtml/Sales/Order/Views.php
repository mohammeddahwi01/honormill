<?php 
namespace Raveinfosys\Deleteorder\Block\Adminhtml\Sales\Order;

use Magento\Sales\Block\Adminhtml\Order\View;

class Views extends View
{
    public function beforeSetLayout(View $view)
    {
		$view->addButton(
			'button_id',
			[
				'label'     =>  __('Delete Order'),
				'class'     =>  'go',
				'onclick'   =>  "confirmSetLocation('Are you sure you want to do this?', '{$this->getDeleteUrl()}');"
			]
		);
    }

    public function getDeleteUrl()
    {
        return $this->getUrl('deleteorder/order/delete', ['_current'=>true]);
    }
}
