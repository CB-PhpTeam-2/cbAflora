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

use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
use Webkul\MpHyperLocal\Model\ShipRateFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Webkul\Marketplace\Helper\Data as MpHelper;

class AddRate extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Webkul\MpHyperLocal\Model\ShipRateFactory
     */
    private $shipRate;

    /**
     * @param Session $customerSession,
     * @param Context $context,
     * @param ShipAreaFactory $shipArea,
     * @param array   $data = []
     */
    public function __construct(
        Session $customerSession,
        Context $context,
        ShipRateFactory $shipRate,
        PriceCurrencyInterface $priceCurrency,
        MpHelper $mpHelper,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->shipRate = $shipRate;
        $this->priceCurrency = $priceCurrency;
        $this->mpHelper = $mpHelper;
        parent::__construct($context, $data);
    }

    /**
     * getAuctionProduct
     * @return bool|array
     */
    public function getAllShipRate()
    {
        $sellerId = $this->customerSession->getCustomerId();
        $shipAreaColl = $this->shipRate->create()->getCollection()->addFieldToFilter('seller_id', $sellerId);
        return $shipAreaColl;
    }

    /**
     * getSaveAction
     * @return string
     */
    public function getSaveAction()
    {
        return $this->getUrl('mphyperlocal/account/addrate', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * getDeleteUrl
     * @param int $locationId
     * @return string
     */
    public function getDeleteUrl($rateId)
    {
        return $this->getUrl(
            'mphyperlocal/account/deleterate',
            [
                '_secure' => $this->getRequest()->isSecure(),
                'id'=>$rateId
            ]
        );
    }

    /**
     * Get formatted by price
     *
     * @param   $price
     * @return array || float
     */
    public function getFormatedPrice($price)
    {
        $currency = $this->mpHelper->getCurrencySymbol();
        return $this->priceCurrency->format($price, true, 2, null, $currency);
    }
}
