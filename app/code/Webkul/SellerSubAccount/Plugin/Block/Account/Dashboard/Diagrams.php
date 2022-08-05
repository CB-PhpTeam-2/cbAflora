<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubAccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubAccount\Plugin\Block\Account\Dashboard;

use Webkul\SellerSubAccount\Helper\Data as HelperData;
use Webkul\Marketplace\Helper\Data as MarketplaceHelper;
use Magento\Sales\Model\Order;
use Webkul\Marketplace\Model\Saleslist;

class Diagrams
{
    /**
     * @var HelperData
     */
    public $_helper;

    /**
     * @var MarketplaceHelper
     */
    public $_marketplaceHelper;

    /**
     * @var Order
     */
    public $_order;

    /**
     * @var Saleslist
     */
    public $_saleslist;

    /**
     * @param HelperData          $helper
     * @param MarketplaceHelper   $marketplaceHelper
     * @param Order               $order
     * @param Saleslist           $saleslist
     */
    public function __construct(
        HelperData $helper,
        MarketplaceHelper $marketplaceHelper,
        Order $order,
        Saleslist $saleslist
    ) {
        $this->_helper = $helper;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_order = $order;
        $this->_saleslist = $saleslist;
    }

    /**
     * beforeGetYearlySale.
     *
     * @param \Webkul\Marketplace\Block\Account\Dashboard\Diagrams $block
     * @param $sellerId
     *
     * @return int
     */
    public function beforeGetYearlySale(
        \Webkul\Marketplace\Block\Account\Dashboard\Diagrams $block,
        $sellerId
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            return [$sellerId];
        }
        $sellerId = $this->_helper->getSubAccountSellerId();
        return [$sellerId];
    }

    /**
     * beforeGetMonthlySale.
     *
     * @param \Webkul\Marketplace\Block\Account\Dashboard\Diagrams $block
     * @param $sellerId
     *
     * @return int
     */
    public function beforeGetMonthlySale(
        \Webkul\Marketplace\Block\Account\Dashboard\Diagrams $block,
        $sellerId
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            return [$sellerId];
        }
        $sellerId = $this->_helper->getSubAccountSellerId();
        return [$sellerId];
    }

    /**
     * beforeGetWeeklySale.
     *
     * @param \Webkul\Marketplace\Block\Account\Dashboard\Diagrams $block
     * @param $sellerId
     *
     * @return int
     */
    public function beforeGetWeeklySale(
        \Webkul\Marketplace\Block\Account\Dashboard\Diagrams $block,
        $sellerId
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            return [$sellerId];
        }
        $sellerId = $this->_helper->getSubAccountSellerId();
        return [$sellerId];
    }

    /**
     * beforeGetDailySale.
     *
     * @param \Webkul\Marketplace\Block\Account\Dashboard\Diagrams $block
     * @param $sellerId
     *
     * @return int
     */
    public function beforeGetDailySale(
        \Webkul\Marketplace\Block\Account\Dashboard\Diagrams $block,
        $sellerId
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            return [$sellerId];
        }
        $sellerId = $this->_helper->getSubAccountSellerId();
        return [$sellerId];
    }
}
