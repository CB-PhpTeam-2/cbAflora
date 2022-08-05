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
use Webkul\Marketplace\Model\SellerFactory;
use Webkul\MpHyperLocal\Helper\Data;

class Origin extends Template
{
    /**
     * @param Context $context
     * @param SessionFactory $customerSessionFactory
     * @param SellerFactory $sellerFactory
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        SessionFactory $customerSessionFactory,
        SellerFactory $sellerFactory,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSessionFactory = $customerSessionFactory;
        $this->sellerFactory = $sellerFactory;
        $this->helper = $helper;
    }

    /**
     * getOrigin
     *
     * @return bool|array
     */
    public function getOrigin()
    {
        $sellerId = $this->customerSessionFactory->create()->getCustomerId();
        $sellerOrigin = $this->sellerFactory->create()->getCollection()
                            ->addFieldToFilter('seller_id', $sellerId)
                            ->setPageSize(1)->getFirstItem();
        return $sellerOrigin;
    }

    /**
     * Return Hyperlocal Helper
     *
     * @return string
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * Get Form Save Action URL
     *
     * @return string
     */
    public function getSaveAction()
    {
        return $this->getUrl('mphyperlocal/account/origin', ['_secure' => $this->getRequest()->isSecure()]);
    }
}
