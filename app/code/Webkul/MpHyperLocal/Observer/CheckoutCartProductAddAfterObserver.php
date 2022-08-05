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

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;

class CheckoutCartProductAddAfterObserver implements ObserverInterface
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
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\App\Request\Http $request,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->helper = $helper;
        $this->mpHelper = $mpHelper;
        $this->cart = $cart;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->layoutFactory = $layoutFactory;
        $this->request = $request;
        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_sessionManager = $sessionManager;
        $this->_objectManager = $objectManager;
        $this->_remoteAddressInstance = $this->_objectManager->get(
            'Magento\Framework\HTTP\PhpEnvironment\RemoteAddress'
        );
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
        $savedAddress = $this->helper->getSavedAddress();
        /*checks whether the settings to
        allow single seller checkout in admin is set to yes or not*/
        if ($moduleStatus && $allow) {
            $count=0;
            if ($savedAddress) {
                $productid=$observer->getQuoteItem()->getProductId();
                $quoteItem = $observer->getEvent()->getQuoteItem();
                $options = $quoteItem->getBuyRequest()->getData();
                /*checks for seller assign product*/
                if (array_key_exists("mpassignproduct_id", $options)) {
                    $sellerId = $this->helper->getSellerIdFromMpassign($options["mpassignproduct_id"]);
                } else {
                    $sellerId = $this->mpHelper->getSellerIdByProductId(
                        $productid
                    );
                }
                foreach ($cart->getAllItems() as $item) {
                    $options = $item->getBuyRequest()->getData();
                    /*checks for seller assign product*/
                    if (array_key_exists("mpassignproduct_id", $options)) {
                        $tempSellerId = $this->helper->getSellerIdFromMpassign($options["mpassignproduct_id"]);
                    } else {
                        $tempSellerId = $this->mpHelper->getSellerIdByProductId(
                            $item->getProductId()
                        );
                    }

                    if ($sellerId!=$tempSellerId) {
                        $count++;
                    }
                }
            } else {
                throw new LocalizedException(
                    __('Please select your location!')
                );
            }

            if ($count>0) {
                //$message = __('At a time you can add only one store\'s product in the cart');
                $message = __('You can not add products in your cart from two different sellers');
                //$urlencodeMessage = urlencode($message);
                //$this->set($urlencodeMessage, 10);

                throw new LocalizedException($message); 
            }
        }
    }

    public function set($value, $duration)
    {
        $metadata = $this->_cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration($duration)
            ->setPath($this->_sessionManager->getCookiePath())
            ->setDomain($this->_sessionManager->getCookieDomain());

        $this->_cookieManager->setPublicCookie(
            'mage-messages',
            $value,
            $metadata
        );
    }

    public function getRemoteAddress()
    {
        return $this->_remoteAddressInstance->getRemoteAddress();
    }
}
