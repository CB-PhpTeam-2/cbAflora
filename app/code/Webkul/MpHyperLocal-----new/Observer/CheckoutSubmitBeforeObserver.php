<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpHyperLocal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpHyperLocal\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class CheckoutSubmitBeforeObserver implements ObserverInterface
{
    /**
     * @var \Webkul\MpHyperLocal\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    public function __construct(
        \Webkul\MpHyperLocal\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->helper = $helper;
        $this->mpHelper = $mpHelper;
        $this->cart = $cart;
    }

    /**
     * [execute executes when checkout_cart_product_add_after event hit]
     * @param  \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $cart = $this->cart->getQuote();
        $allow = $this->helper->getAllowSingleSellerSettings();
        $moduleStatus = $this->helper->isEnabled();

        /*checks whether the settings to
        allow single seller checkout in admin is set to yes or not*/
        if ($moduleStatus && $allow) {
            $sellerIds=[];
            foreach ($cart->getAllItems() as $item) {
                $options = $item->getBuyRequest()->getData();
                /*checks for seller assign product*/
                if (array_key_exists("mpassignproduct_id", $options)) {
                    $tempSellerId = $this->helper->getSellerIdFromMpassign(
                        $options["mpassignproduct_id"]
                    );
                } else {
                    $tempSellerId = $this->mpHelper->getSellerIdByProductId(
                        $item->getProductId()
                    );
                }
                $sellerIds[] = $tempSellerId;
            }
            $sellerIds = array_unique($sellerIds);
            if (count($sellerIds) > 1) {
                throw new LocalizedException(
                    __(
                        'At a time you can add only one store\'s product in the cart. Please update your cart with only one store\'s product(s).'
                    )
                );
            }
        }
    }
}
