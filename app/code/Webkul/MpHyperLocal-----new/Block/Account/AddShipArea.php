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
namespace Webkul\MpHyperLocal\Block\Account;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\SessionFactory;
use Webkul\MpHyperLocal\Model\ShipAreaFactory;
use Webkul\MpHyperLocal\Helper\Data;

class AddShipArea extends Template
{
    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Webkul\MpHyperLocal\Model\ShipAreaFactory
     */
    protected $shipAreaFactory;

    /**
     * @var \Webkul\MpHyperLocal\Helper\Data
     */
    protected $helper;

    /**
     * @param Context $context
     * @param SessionFactory $customerSessionFactory
     * @param ShipAreaFactory $shipAreaFactory
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        SessionFactory $customerSessionFactory,
        ShipAreaFactory $shipAreaFactory,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSessionFactory = $customerSessionFactory;
        $this->shipAreaFactory = $shipAreaFactory;
        $this->helper = $helper;
    }

    /**
     * Get All Shipping Area for Seller
     *
     * @return object $shipAreaCollection
     */
    public function getAllShipArea()
    {
        $sellerId = $this->customerSessionFactory->create()->getCustomerId();
        $shipAreaCollection = $this->shipAreaFactory->create()->getCollection()
                                ->addFieldToFilter('seller_id', $sellerId);
        return $shipAreaCollection;
    }

    /**
     * Return HyperLocal Helper.
     *
     * @return object
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * Get Form Save Action
     *
     * @return string
     */
    public function getSaveAction()
    {
        $saveUrl = $this->getUrl('mphyperlocal/account/savearea', [
                '_secure' => $this->getRequest()->isSecure()
            ]);
        return $saveUrl;
    }

    /**
     * Get Delete Url
     *
     * @param int $locationId
     * @return string
     */
    public function getDeleteUrl($locationId)
    {
        return $this->getUrl(
            'mphyperlocal/account/deletearea',
            [
                '_secure' => $this->getRequest()->isSecure(),
                'id'=>$locationId
            ]
        );
    }
}
