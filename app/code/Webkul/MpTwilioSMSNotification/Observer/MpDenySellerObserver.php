<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpTwilioSMSNotification
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpTwilioSMSNotification\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\MpTwilioSMSNotification\Helper\Data;
use Webkul\Marketplace\Helper\Data as MpHelper;
use Magento\Framework\Message\ManagerInterface;
use Magento\Catalog\Model\Product;

class MpDenySellerObserver implements ObserverInterface
{
    /**
     * @var Webkul\MpTwilioSMSNotification\Helper\Data
     */
    protected $_helperData;
    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    protected $_helperMarketplace;
    /**
     * @var Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @param Data                                        $helperData
     * @param MpHelper                                    $helperMarketplace
     * @param ManagerInterface                            $messageManager
     */
    public function __construct(
        Data $helperData,
        MpHelper $helperMarketplace,
        ManagerInterface $messageManager
    ) {
        $this->_messageManager = $messageManager;
        $this->_helperMarketplace = $helperMarketplace;
        $this->_helperData = $helperData;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_helperData->getTwilioStatus()) {
            $customer = $observer->getSeller();
            $sellerId = $observer->getSeller()->getId();
            $client = $this->_helperData->makeTwilloClient();
            $mpSellerCollection = $this->_helperMarketplace
                                ->getSellerDataBySellerId($sellerId);
            foreach ($mpSellerCollection as $mpSeller) {
                $sellerName=$customer->getFirstname();
                $toNumber = str_replace(
                    " ",
                    "",
                    $mpSeller->getContactNumber()
                );
                $body = __(
                    "Hi %1, you seller account deny by admin on %2 For reason please check your mail at %3",
                    $sellerName,
                    $this->_helperData->getSiteUrl(),
                    $customer->getEmail()
                );
                try {
                    $this->_helperData->sendMessage($client, $toNumber, $body);
                } catch (\Exception $e) {
                    $this->_messageManager->addError($e->getMessage());
                }
            }
        }
    }
}
