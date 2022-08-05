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
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Webkul\Marketplace\Model\SellerFactory;
use Webkul\Marketplace\Helper\Data;

class AddShipArea extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var Data
     */
    private $_mpHelper;

    /**
     * @param Context $context
     * @param PageFactory $_resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CustomerSession $customerSession,
        SellerFactory $sellerFactory,
        Data $data
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->sellerFactory = $sellerFactory;
        $this->_mpHelper = $data;
        parent::__construct($context);
    }

    /**
     * Add Shipping Area page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $sellerId = $this->customerSession->getCustomerId();
        $sellerData = $this->sellerFactory->create()->getCollection()
                                        ->addFieldToFilter('seller_id', $sellerId)
                                        ->setPageSize(1)->getFirstItem();
        if ($sellerData->getIsSeller()) {
            $resultPage = $this->resultPageFactory->create();
            if ($this->_mpHelper->getIsSeparatePanel()) {
                $resultPage->addHandle('mphyperlocal_layout2_account_addshiparea');
            }
            $resultPage->getConfig()->getTitle()->set(__('Add Shipping Area'));
            return $resultPage;
        } else {
            $this->_forward('defaultNoRoute');
        }
    }
}
