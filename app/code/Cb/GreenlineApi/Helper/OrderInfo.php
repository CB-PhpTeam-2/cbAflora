<?php

namespace Cb\GreenlineApi\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Webkul\Marketplace\Model\OrdersFactory as MpOrdersFactory;

class OrderInfo extends AbstractHelper {

    /**
     * @var MpOrdersFactory
     */
    protected $mpOrdersFactory;

    public function __construct(
    \Magento\Framework\View\Element\Context $context,
    MpOrdersFactory $mpOrdersFactory
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->mpOrdersFactory = $mpOrdersFactory;
    }

    /**
     * Return the seller Order data.
     *
     * @return \Webkul\Marketplace\Api\Data\OrdersInterface
     */
    public function getOrderinfo($orderId = '', $sellerId)
    {
        $data = [];
        $model = $this->mpOrdersFactory->create()
        ->getCollection()
        ->addFieldToFilter(
            'seller_id',
            $sellerId
        )
        ->addFieldToFilter(
            'order_id',
            $orderId
        );

        $salesOrder = $this->mpOrdersFactory->create()->getCollection()->getTable('sales_order');

        $model->getSelect()->join(
            $salesOrder.' as so',
            'main_table.order_id = so.entity_id',
            ["order_approval_status" => "order_approval_status"]
        )->where("so.order_approval_status=1");
        foreach ($model as $tracking) {
            return $tracking;
        }

        return false;
    }

}
