<?php

namespace Paysafe\Payment\Block\Account;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\SessionFactory;
use Webkul\Marketplace\Model\SellerFactory;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Webkul\Marketplace\Helper\Data as Mphelper;

class Configuration extends Template
{
    /**
     * @param Context $context
     * @param SessionFactory $customerSessionFactory
     * @param SellerFactory $sellerFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        SessionFactory $customerSessionFactory,
        SellerFactory $sellerFactory,
        CollectionFactory $customerFactory,
        Mphelper $mpHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSessionFactory = $customerSessionFactory;
        $this->sellerFactory = $sellerFactory;
        $this->_customerFactory = $customerFactory;
        $this->mpHelper = $mpHelper;
    }

    /**
     * getIsSeller
     *
     * @return bool|array
     */
    public function getIsSeller()
    {
        $sellerId = $this->mpHelper->getCustomerId();
        $sellerOrigin = $this->sellerFactory->create()->getCollection()
                            ->addFieldToFilter('seller_id', $sellerId)
                            ->setPageSize(1)->getFirstItem();
        return $sellerOrigin->getIsSeller();
    }

    /**
     * getConfiguration
     *
     * @return bool|array
     */
    public function getConfiguration()
    {
        $sellerId = $this->mpHelper->getCustomerId();
        $paysafeCredential = $this->_customerFactory->create()
                            ->addFieldToFilter('entity_id', $sellerId)
                            ->setPageSize(1)->getFirstItem();
        return $paysafeCredential;
    }

    /**
     * Get Form Save Action URL
     *
     * @return string
     */
    public function getSaveAction()
    {
        return $this->getUrl('paysafe/account/configuration', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * Return Marketplace Helper
     *
     * @return string
     */
    public function getMarketplaceHelper()
    {
        return $this->mpHelper;
    }
}
