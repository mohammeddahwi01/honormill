<?php
/***************************************************************************
 Extension Name	: Dealer Inquiry
 Extension URL	: https://www.magebees.com/dealer-inquiry-for-magento-2.html
 Copyright		: Copyright (c) 2016 MageBees, http://www.magebees.com
 Support Email	: support@magebees.com 
 ***************************************************************************/
namespace Magebees\DealerInquiry\Controller\Adminhtml\Inquiry;

class MassDelete extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $dealerIds = $this->getRequest()->getParam('inquiry');
        if (!is_array($dealerIds) || empty($dealerIds)) {
            $this->messageManager->addError(__('Please select product(s).'));
        } else {
            try {
                foreach ($dealerIds as $dealerId) {
                    $banner = $this->_objectManager->get('Magebees\DealerInquiry\Model\Inquiry')->load($dealerId);
                    $banner->delete();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been deleted.', count($dealerIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
         $this->_redirect('*/*/');
    }
}
