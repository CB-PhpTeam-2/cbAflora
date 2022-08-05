<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpHyperLocal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpHyperLocal\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Webkul\MpHyperLocal\Model\ShipAreaFactory;

class DeleteArea extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Webkul\MpHyperLocal\Model\ShipAreaFactory
     */
    private $shipArea;

    /**
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param Amount $auctionAmt
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        ShipAreaFactory $shipArea
    ) {
        $this->customerSession = $customerSession;
        $this->shipArea = $shipArea;
        parent::__construct($context);
    }

    /**
     * Ship Area delete controller
     *
     * @return Magento\Backend\Model\View\Result\Redirect $resultRedirect
     */
    public function execute()
    {
        /** @var int $curntCustomerId */
        $curntCustomerId = $this->customerSession->getCustomerId();

        /** @var int $bidId */
        $areaId = $this->_request->getParam('id');

        /** @var Webkul\Auction\Model\Amount $bidRecord */
        $area = $this->shipArea->create()->load($areaId);
        if ($area->getEntityId() && $curntCustomerId == $area->getSellerId()) {
            $area->delete();
            $this->messageManager->addSuccess(__('Ship area removed successfully.'));
        } else {
            $this->messageManager->addError(__('Not permitted.'));
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setUrl($this->_url->getUrl('mphyperlocal/account/addshiparea'));
    }
}
