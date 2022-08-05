<?php

namespace Cb\GreenlineApi\Block\Account;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Webkul\Marketplace\Model\SellerFactory;
use Cb\GreenlineApi\Model\GreenlineCredentialFactory;
use Cb\GreenlineApi\Helper\Data;
use Webkul\Marketplace\Helper\Data as Mphelper;

class Configuration extends Template
{
    /**
     * @param Context $context
     * @param SellerFactory $sellerFactory
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        SellerFactory $sellerFactory,
        GreenlineCredentialFactory $greenlineCredentialFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        Data $helper,
        Mphelper $mpHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->sellerFactory = $sellerFactory;
        $this->greenlineCredentialFactory = $greenlineCredentialFactory;
        $this->_resource = $resource;
        $this->helper = $helper;
        $this->mpHelper = $mpHelper;
    }

    /**
     * getIsSeller
     *
     * @return bool|array
     */
    public function getIsSeller()
    {
        $sellerId = $this->_mpHelper->getCustomerId();
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
        $sellerCredential = $this->greenlineCredentialFactory->create()
                            ->getCollection()
                            ->addFieldToFilter('seller_id', $sellerId)
                            ->setPageSize(1)->getFirstItem();
        return $sellerCredential;
    }

    /**
     * Return GreenlineApi Helper
     *
     * @return string
     */
    public function getHelper()
    {
        return $this->helper;
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

    /**
     * Get Form Save Action URL
     *
     * @return string
     */
    public function getSaveAction()
    {
        return $this->getUrl('greenlineapi/account/configuration', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * getStatuses
     *
     * @return int
     */
    public function getStatuses()
    {
        $statuses = [];

        $statuses[] = array('value' => 0, 'label' => 'Disable');
        $statuses[] = array('value' => 1, 'label' => 'Enable');

        return $statuses;
    }

    public function getSellerCronData()
    {
        $connection  = $this->_resource->getConnection();
        $tablePrefix = $this->helper->getTablePrefix();
        $table = $connection->getTableName($tablePrefix.'greenlineapi_sellercron_allow');
        $sellerId = $this->mpHelper->getCustomerId();

        $query = "SELECT * FROM `".$table."` WHERE `seller_id` = $sellerId AND `allow` = '1' LIMIT 1";
        $collection = [];
        $collection = $connection->fetchAll($query);

        if(sizeof($collection) > 0){
            $collection = $collection[0];
        }

        return $collection;
    }
}
