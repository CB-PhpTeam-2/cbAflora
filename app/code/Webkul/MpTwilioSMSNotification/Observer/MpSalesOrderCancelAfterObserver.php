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
use Webkul\Marketplace\Helper\Data as MpHelper;
use Webkul\Marketplace\Model\Orders;
use Webkul\MpTwilioSMSNotification\Helper\Data;

class MpSalesOrderCancelAfterObserver implements ObserverInterface
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
        ManagerInterface $messageManager
    ) {
        $this->orderModelMp = $orderModelMp;
        $this->customerModel = $customerModel;
        $this->productModel = $productModel;
        $this->messageManager = $messageManager;
        $this->helperMarketplace = $helperMarketplace;
        $this->helperData = $helperData;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->helperData->getTwilioStatus()) {
            $twilioClient = $this->helperData->makeTwilloClient();
            $order = $observer->getEvent()->getOrder();
            $sellerId = $observer->getSellerId();
            $orderEntityId = $order->getEntityId();
            $orderIncrementId = $order->getIncrementId();
            $mpOrderHistoryUrl = $this->helperData->getMpOrderHistoryUrl();
            $sellerOrders = $this->orderModelMp->getCollection()
                                               ->addFieldToFilter(
                                                   'order_id',
                                                   $orderEntityId
                                               )->addFieldToFilter(
                                                   'seller_id',
                                                   $sellerId
                                               )->addFieldToFilter(
                                                   'is_canceled',
                                                   ['eq' => 1]
                                               );
            foreach ($sellerOrders as $sellerOrder) {
                $sellerOrderItemProductIds = $sellerOrder->getProductIds();
                $orderItems = $order->getItems();
                $cancelledOrderItems = $this->getCancelledOrderItems($orderItems);
                $sellerCancelledOrderItems = $this->helperData
                    ->getSellerOrderItems(
                        $orderItems,
                        $sellerOrderItemProductIds
                    );
                if (empty($sellerCancelledOrderItems)) {
                    continue;
                }
                $userData = $this->helperData->getCustomer($sellerId);
                $mpSellerCollection = $this->helperMarketplace
                    ->getSellerDataBySellerId(
                        $sellerId
                    );
                $orderItemsStringRepresentation =
                    $this->getOrderItemsToString($sellerCancelledOrderItems);
                foreach ($mpSellerCollection as $sellerData) {
                    $sellerFirstname = $userData->getFirstname();
                    $sellerContactNumber = str_replace(
                        " ",
                        "",
                        $sellerData->getContactNumber()
                    );
                    $sellerEmail = $userData->getEmail();
                    $content = __(
                        "Hi Seller %1, the Order #%2 for item(s) %3 has been canceled, " .
                        "Please visit %4 or check your mail %5 for more details",
                        $sellerFirstname,
                        $orderIncrementId,
                        $orderItemsStringRepresentation,
                        $mpOrderHistoryUrl,
                        $sellerEmail
                    );
                    if ($this->helperData->isCustomerNotificationEnabled()) {
                        $this->helperData->sendSMSToCustomer(
                            $order,
                            $orderItemsStringRepresentation,
                            $this->messageManager
                        );
                    }
                    try {
                        $this->helperData->sendMessage(
                            $twilioClient,
                            $sellerContactNumber,
                            $content
                        );
                    } catch (\Exception $e) {
                        $this->messageManager->addError($e->getMessage());
                    }
                    break;
                }
            }
        }
    }

    /**
     * Get cancelled order items from order items list
     *
     * @param  \Magento\Sales\Api\Data\OrderItemInterface[]|
     *         \Magento\Sales\Model\ResourceModel\Item\Collection
     *         $orderItems
     * @return \Magento\Sales\Api\Data\OrderItemInterface[]
     */
    private function getCancelledOrderItems($orderItems)
    {
        $orderItemsArray = [];
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getStatusId() === \Magento\Sales\Model\Order\Item::STATUS_CANCELED &&
                empty($orderItem->getParentItemId() &&
                !empty($orderItem->getQtyCanceled()))
            ) {
                $orderItemsArray[] = $orderItem;
            }
        }

        return $orderItemsArray;
    }

    /**
     * Get a comma seperated string of product names including
     * product quantity from list of order items.
     *
     * @param  \Magento\Sales\Api\OrderItemInterface[] $orderItems
     * @return string
     */
    private function getOrderItemsToString($orderItems)
    {
        $lastOrderItem = array_pop($orderItems);
        $lastOrderItem = empty($lastOrderItem)
            ? ''
            : $this->helperData->getOrderItemName($lastOrderItem) . ' (x' .
                (int)$lastOrderItem->getQtyCanceled() . ')';
        $resultString =
            ltrim(
                array_reduce(
                    $orderItems,
                    function ($carry, $orderItem) {
                        return $carry . ', ' . $this->helperData->getOrderItemName($orderItem) . ' (x' .
                            (int)$orderItem->getQtyCanceled() . ')';
                    }
                ),
                ', '
            );

        return empty($resultString) ? $lastOrderItem : ($resultString . ', '.__('and').' ' . $lastOrderItem);
    }
}
