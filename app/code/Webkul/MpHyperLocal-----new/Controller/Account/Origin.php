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
use Webkul\MpHyperLocal\Model\ShipRateFactory;
use Webkul\Marketplace\Model\SellerFactory;
use Webkul\Marketplace\Helper\Data;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

class Origin extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var Webkul\MpHyperLocal\Model\ShipRateFactory
     */
    private $shipRate;

    /**
     * @var Webkul\Marketplace\Model\SellerFactory
     */
    private $sellerFactory;

    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    protected $_mpHelper;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CustomerSession $customerSession
     * @param ShipRateFactory $shipRate
     * @param SellerFactory $sellerFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CustomerSession $customerSession,
        ShipRateFactory $shipRate,
        SellerFactory $sellerFactory,
        Data $data,
        FormKeyValidator $formKeyValidator
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->shipRate = $shipRate;
        $this->customerSession = $customerSession;
        $this->sellerFactory = $sellerFactory;
        $this->_mpHelper = $data;
        $this->formKeyValidator = $formKeyValidator;
        parent::__construct($context);
    }
    
    /**
     * Add Shipping Origin page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $sellerId = $this->_mpHelper->getCustomerId();
        if ($this->getRequest()->isPost()) {
            try {
                if (!$this->formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory->create()->setPath(
                        'mphyperlocal/account/origin',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
                $data = $this->getRequest()->getParams();
                $sellerData = $this->sellerFactory->create()->getCollection()
                ->addFieldToFilter('seller_id', $sellerId);
                if ($sellerData->getSize()) {
                    foreach ($sellerData as $seller) {
                        $seller->setOriginAddress($data['origin_address']);
                        $seller->setLatitude($data['latitude']);
                        $seller->setLongitude($data['longitude']);
                        $seller->setRadius($data['radius']);
                        $seller->save();
                    }
                    $this->messageManager->addSuccess(__('Your shipping origin has been successfully saved.'));
                } else {
                    $this->messageManager->addError(__('Invalid seller'));
                }

                return $this->resultRedirectFactory->create()->setPath('mphyperlocal/account/origin');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $this->resultRedirectFactory->create()->setPath('mphyperlocal/account/origin');
            }
        } elseif ($this->_mpHelper->isSeller()) {
            $resultPage = $this->resultPageFactory->create();
            if ($this->_mpHelper->getIsSeparatePanel()) {
                $resultPage->addHandle('mphyperlocal_layout2_account_origin');
            }
            $resultPage->getConfig()->getTitle()->set(__('Add Shipping Origin'));
            return $resultPage;
        } else {
            $this->_forward('defaultNoRoute');
        }
    }
}
