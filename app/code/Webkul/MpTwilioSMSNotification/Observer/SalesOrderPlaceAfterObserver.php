<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   WebkulMpTwilioSMSNotification
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpTwilioSMSNotification\Observer;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Webkul\Marketplace\Helper\Data as MpHelper;
use Webkul\Marketplace\Model\Orders;
use Webkul\MpTwilioSMSNotification\Helper\Data;

class SalesOrderPlaceAfterObserver implements ObserverInterface
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
     * @param Data             $helperData
     * @param Customer         $customerModel
     * @param Orders           $orderModelMp
     * @param MpHelper         $helperMarketplace
     * @param Product          $productModel
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Data $helperData,
        Customer $customerModel,
        Orders $orderModelMp,
        MpHelper $helperMarketplace,
        Product $productModel,
        ManagerInterface $messageManager,
        \Magento\Framework\Pricing\Helper\Data $priceHelper
    ) {
        $this->orderModelMp = $orderModelMp;
        $this->customerModel = $customerModel;
        $this->productModel = $productModel;
        $this->messageManager = $messageManager;
        $this->helperMarketplace = $helperMarketplace;
        $this->helperData = $helperData;
        $this->priceHelper = $priceHelper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helperData->getTwilioStatus()) {
            $order = $observer->getOrder();
            if (empty($order)) {
                return;
            }
            if ($this->helperData->isCustomerNotificationEnabled()) {
                $this->sendOrderPlacedSMSToCustomer($order);
            }
            $this->sendOrderPlacedSMSToSeller($order);
        }
    }

    /**
     * Send Order Placed SMS to Customer
     *
     * @param  \Magento\Sales\Model\Order $order
     * @return void
     */
    private function sendOrderPlacedSMSToCustomer($order)
    {
        $twilioClient = $this->helperData->makeTwilloClient();
        $customerName = $this->helperData->getCustomerName($order);
        $customerTelephone = $this->helperData->getCustomerTelephone($order);
        $orderIncrementedId = $order->getIncrementId();
        $customerOrderHistoryUrl = $this->helperData->getSalesOrderHistoryUrl();
        $visibleOrderItems = $this->getVisibleOrderItems($order->getItems());
        $orderItemsString = $this->getOrderItemsToString($visibleOrderItems);
        $customerEmail = $order->getCustomerEmail();
        if (!empty($customerTelephone) &&
            !empty($orderItemsString)
        ) {
            $body = __(
                "Hi %1, your Order #%2 for item(s) %3 has been successfully placed," .
                " Please visit %4 or check your email %5 for more details",
                $customerName,
                $orderIncrementedId,
                $orderItemsString,
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

            $orderAmount = $this->priceHelper->currency($order->getPayment()->getAmountOrdered(), true, false);

            $additionalInformation = [];
            $additionalInformation= $order->getPayment()->getAdditionalInformation();

            if(sizeof($additionalInformation) > 0){
                $body = __(
                    "Hi %1, you have paid the amount %2 successfully by %3 ." .
                    " Your transaction id is %4",
                    $customerName,
                    $orderAmount,
                    $additionalInformation['method_title'],
                    $additionalInformation['paysafe_txn_id']
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
    }

    /**
     * Send Order Placed SMS to seller
     *
     * @param  \Magento\Sales\Model\Order $order
     * @return void
     */
    private function sendOrderPlacedSMSToSeller($order)
    {
        $twilioClient = $this->helperData->makeTwilloClient();
        $orderEntityId = $order->getEntityId();
        $orderIncrementedId = $order->getIncrementId();
        $sellerOrder =
            $this->orderModelMp
                 ->getCollection()
                 ->addFieldToFilter(
                     'order_id',
                     $orderEntityId
                 )->addFieldToFilter(
                     'seller_id',
                     ['neq' => 0]
                 );
        foreach ($sellerOrder as $info) {
            $userData = $this->helperData
                ->getCustomer(
                    $info->getSellerId()
                );
            $mpSellerCollection = $this->helperMarketplace
                ->getSellerDataBySellerId(
                    $info->getSellerId()
                );
            $sellerOrderItemProductIds = $info->getProductIds();
            $visibleOrderItems = $this->getVisibleOrderItems(
                $order->getItems()
            );
            $sellerOrderItems = $this->helperData
                ->getSellerOrderItems(
                    $visibleOrderItems,
                    $sellerOrderItemProductIds
                );
            if (empty($sellerOrderItems)) {
                continue;
            }
            foreach ($mpSellerCollection as $sellerData) {
                $sellerFirstName = $userData->getFirstname();
                $sellerContactNumber = str_replace(
                    " ",
                    "",
                    $sellerData->getContactNumber()
                );
                $orderItemsString = $this->getOrderItemsToString(
                    $sellerOrderItems
                );
                $mpSellerOrderHistoryUrl = $this->helperData->getMpOrderHistoryUrl();
                $sellerEmail = $userData->getEmail();
                $content = __(
                    "Hi Seller %1, an Order #%2 for item(s) %3 has been placed," .
                    " Please visit %4 or check your mail %5 for more details",
                    $sellerFirstName,
                    $orderIncrementedId,
                    $orderItemsString,
                    $mpSellerOrderHistoryUrl,
                    $sellerEmail
                );
                try {
                    $this->helperData->sendMessage(
                        $twilioClient,
                        $sellerContactNumber,
                        $content
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
        }
    }

    /**
     * Remove items with parent id null and/or that have
     * ordered quantity equal to zero.
     *
     * @param  \Magento\Sales\Api\Data\OrderItemInterface[]|
     *         \Magento\Sales\Model\ResourceModel\Item\Collection
     *         $orderItems
     * @return \Magento\Sales\Api\Data\OrderItemInterface[]
     */
    private function getVisibleOrderItems($orderItems)
    {
        $orderItemsArray = [];
        foreach ($orderItems as $orderItem) {
            if (empty($orderItem->getParentItemId()) &&
               !empty($orderItem->getQtyOrdered())
            ) {
                $orderItemsArray[] = $orderItem;
            }
        }

        return $orderItemsArray;
    }

    /**
     * Get a comma seperated string of product name with
     * quantity from list of order items.
     *
     * @param \Magento\Sales\Api\Data\OrderItemInterface[] $orderItems
     * @return string
     */
    private function getOrderItemsToString($orderItems)
    {
        $lastOrderItem = array_pop($orderItems);
        $lastOrderItem = empty($lastOrderItem)
            ? ''
            : $this->helperData
                ->getOrderItemName($lastOrderItem) . ' (x' .
                (int)$lastOrderItem->getQtyOrdered() . ')';
        $resultString =
            ltrim(
                array_reduce(
                    $orderItems,
                    function ($carry, $orderItem) {
                        return $carry . ', ' . $this->helperData
                            ->getOrderItemName($orderItem) . ' (x' .
                            (int)$orderItem->getQtyOrdered() . ')';
                    }
                ),
                ', '
            );

        return empty($resultString) ? $lastOrderItem : ($resultString . ', '.__('and').' ' . $lastOrderItem);
    }
}
