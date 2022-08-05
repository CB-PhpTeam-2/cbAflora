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

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Webkul\Marketplace\Helper\Data as MpHelper;
use Webkul\Marketplace\Model\Orders;
use Webkul\MpTwilioSMSNotification\Helper\Data;

class SalesOrderShipmentSaveAfterObserver implements ObserverInterface
{
    /**
     * @var Webkul\MpTwilioSMSNotification\Helper\Data
     */
    private $helperData;
    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    private $helperMarketplace;
    /**
     * @var Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;
    /**
     * @var Magento\Catalog\Model\Product
     */
    private $productModel;
    /**
     * @var Magento\Customer\Model\Customer
     */
    private $customerModel;
    /**
     * @var Webkul\Marketplace\Model\Orders
     */
    private $orderModelMp;
    /**
     * @var Magento\Sales\Api\OrderItemRepositoryInterface
     */
    private $orderItemRepository;

    /**
     * @param Data                         $helperData
     * @param Customer                     $customerModel
     * @param Orders                       $orderModelMp
     * @param MpHelper                     $helperMarketplace
     * @param Product                      $productModel
     * @param ManagerInterface             $messageManager
     * @param OrderItemRepositoryInterface $orderItemRepository
     */
    public function __construct(
        Data $helperData,
        Customer $customerModel,
        Orders $orderModelMp,
        MpHelper $helperMarketplace,
        Product $productModel,
        ManagerInterface $messageManager,
        OrderItemRepositoryInterface $orderItemRepository
    ) {
        $this->orderModelMp = $orderModelMp;
        $this->customerModel = $customerModel;
        $this->productModel = $productModel;
        $this->messageManager = $messageManager;
        $this->helperMarketplace = $helperMarketplace;
        $this->helperData = $helperData;
        $this->orderItemRepository = $orderItemRepository;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helperData->getTwilioStatus()) {
            $shipment = $observer->getEvent()->getShipment();
            if (empty($shipment)) {
                return;
            }
            if ($this->helperData->isCustomerNotificationEnabled()) {
                $this->sendShipmentSMSToCustomer($shipment);
            }
            $this->sendShipmentSMSToSeller($shipment);
        }
    }

    /**
     * Send Shipment SMS to Seller
     *
     * @param  \Magento\Sales\Model\Order\Shipment $shipment
     * @return void
     */
    private function sendShipmentSMSToSeller($shipment)
    {
        $twilioClient = $this->helperData->makeTwilloClient();
        $shippingItemsCategorizedBySeller = [];
        $shipmentItems = $this->getVisibleShipmentItems($shipment->getItems());
        $mpOrderHistoryUrl = $this->helperData->getMpOrderHistoryUrl();
        $orderIncrementId = $shipment->getOrder()->getIncrementId();
        $shipmentIncrementId = $shipment->getIncrementId();

        foreach ($shipmentItems as $shipmentItem) {
            $mpProductCollection = $this->helperMarketplace
                ->getSellerProductDataByProductId($shipmentItem->getProductId());
            foreach ($mpProductCollection as $mpProduct) {
                $mpSellerId = $mpProduct->getSellerId();
                if (!isset($shippingItemsCategorizedBySeller[$mpSellerId])) {
                    $mpSellerCustomerData = $this->helperData->getCustomer($mpSellerId);
                    $mpSellerInfo['firstname'] = $mpSellerCustomerData->getFirstname();
                    $mpSellerInfo['email'] = $mpSellerCustomerData->getEmail();
                    $mpSellerCollection = $this->helperMarketplace->getSellerDataBySellerId($mpSellerId);
                    foreach ($mpSellerCollection as $mpSeller) {
                        $mpSellerInfo['contactNumber'] = str_replace(
                            " ",
                            "",
                            $mpSeller->getContactNumber()
                        );
                    }
                    $shippingItemsCategorizedBySeller[$mpSellerId]['sellerInfo'] = $mpSellerInfo;
                }
                $shippingItemsCategorizedBySeller[$mpSellerId]['shippingItems'][] = $shipmentItem;
            }
        }
        foreach ($shippingItemsCategorizedBySeller as $item) {
            $mpSellerInfo = $item['sellerInfo'];
            $shipmentItemsString =
                $this->getShipmentItemsToString($item['shippingItems']);
            $sellerFirstname = $mpSellerInfo['firstname'];
            $sellerEmail = $mpSellerInfo['email'];
            if (!empty($mpSellerInfo) &&
                !empty($mpSellerInfo['contactNumber'])
            ) {
                $content = __(
                    "Hi Seller %1, The item(s) %2 has been shipped with Shipment Id #%3" .
                    " for Order #%4. Please visit %5 or check your mail %6 for more details",
                    $sellerFirstname,
                    $shipmentItemsString,
                    $shipmentIncrementId,
                    $orderIncrementId,
                    $mpOrderHistoryUrl,
                    $sellerEmail
                );
                try {
                    $this->helperData->sendMessage(
                        $twilioClient,
                        $mpSellerInfo['contactNumber'],
                        $content
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
        }
    }

    /**
     * Send Shipment SMS to Customer
     *
     * @param  \Magento\Sales\Model\Order\Shipment $shipment
     * @return void
     */
    private function sendShipmentSMSToCustomer($shipment)
    {
        $twilioClient = $this->helperData->makeTwilloClient();
        $order = $shipment->getOrder();
        $orderIncrementId = $order->getIncrementId();
        $shipmentIncrementId = $shipment->getIncrementId();
        $customerName = $this->helperData->getCustomerName($order);
        $customerTelephone = $this->helperData->getCustomerTelephone($order);
        $customerOrderHistoryUrl = $this->helperData->getSalesOrderHistoryUrl();
        $customerEmail = $order->getCustomerEmail();
        $visibleShipmentItems = $this->getVisibleShipmentItems($shipment->getItems());
        $shipmentItemsString =
            $this->getShipmentItemsToString($visibleShipmentItems);
        if (!empty($customerTelephone) &&
            !empty($shipmentItemsString)
        ) {
            $body = __(
                "Hi %1, The items(s) %2 has been shipped" .
                " with Shipment Id #%3 for your Order #%4." .
                " Please visit %5 or check your mail %6 for more details",
                $customerName,
                $shipmentItemsString,
                $shipmentIncrementId,
                $orderIncrementId,
                $customerOrderHistoryUrl,
                $customerEmail
            );
            try {
                $this->helperData->sendMessage(
                    $twilioClient,
                    $customerTelephone,
                    $body
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
    }

    /**
     * Get visible shipment items from list of shipment items.
     *
     * @param  \Magento\Sales\Api\Data\ShipmentItemInterface[]|
     *         \Magento\Sales\Model\ResourceModel\Order\Shipment\Item\Collection
     *         $shipmentItems
     * @return \Magento\Sales\Api\Data\ShipmentItemInterface[]
     */
    private function getVisibleShipmentItems($shipmentItems)
    {
        $shipmentItemsArray = [];
        foreach ($shipmentItems as $shipmentItem) {
            if (empty($this->orderItemRepository
                    ->get($shipmentItem->getOrderItemId())
                    ->getParentItemId()) &&
                !empty($shipmentItem->getQty())
            ) {
                $shipmentItemsArray[] = $shipmentItem;
            }
        }

        return $shipmentItemsArray;
    }

    /**
     * Get a comma seperated string of shipment items product name
     * including quantity from list of shipment items.
     *
     * @param  \Magento\Sales\Api\Data\ShipmentItemInterface[] $shipmentItems
     * @return string
     */
    private function getShipmentItemsToString($shipmentItems)
    {
        $lastShipmentItem = array_pop($shipmentItems);
        $lastShipmentItem = empty($lastShipmentItem)
            ? ''
            : $this->helperData->getOrderItemName($this->orderItemRepository->get(
                $lastShipmentItem->getOrderItemId()
            )) . ' (x' . (int)$lastShipmentItem->getQty() . ')';
        $resultString =
            ltrim(
                array_reduce(
                    $shipmentItems,
                    function ($carry, $shipmentItem) {
                        return $carry . ', ' . $this->helperData->getOrderItemName(
                            $this->orderItemRepository->get($shipmentItem->getOrderItemId())
                        ) . ' (x' . (int)$shipmentItem->getQty() . ')';
                    }
                ),
                ', '
            );

        return empty($resultString) ? $lastShipmentItem : ($resultString . ', '.__('and').' ' . $lastShipmentItem);
    }
}
