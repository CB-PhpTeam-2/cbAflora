<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2019 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DeleteOrders
 */

/**
 * Copyright © 2018 Aitoc. All rights reserved.
 */

namespace Aitoc\DeleteOrders\Cron;

use Aitoc\DeleteOrders\Model\ResourceModel\Rules\CollectionFactory as RulesCollection;
use Aitoc\DeleteOrders\Model\ResourceModel\Order\CollectionFactory as OrderCollection;
use Magento\Framework\Registry;
use Aitoc\DeleteOrders\Helper\Data;

class OrderProcess
{
    /**
     * @var RulesCollection
     */
    protected $rulesCollection;

    /**
     * @var OrderCollection
     */
    protected $orderCollection;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Data
     */
    protected $helper;


    public function __construct(
        RulesCollection $rulesCollection,
        OrderCollection $orderCollection,
        Registry $registry,
        Data $helper
    ) {
        $this->rulesCollection = $rulesCollection;
        $this->orderCollection = $orderCollection;
        $this->registry = $registry;
        $this->helper = $helper;
    }


    public function execute()
    {
        $this->registry->register('isSecureArea', true);
        $currentDate = date("Y-m-d H:i:s");
        $rulesCollection = $this->rulesCollection->create()
            ->addFieldToFilter('is_active', 1)
            ->load();
        foreach ($rulesCollection as $rule) {
            $scope = $rule->getScope();//either sales_order_grid or archive_order_grid
            $orderStatuses = explode(",", $rule->getOrderStatuses());
            $action = $rule->getAction();//either delete or archive
            $formatDate = date('Y-m-d H:i:s', strtotime($currentDate . ' -' . $rule->getTime() . ' day'));
            if ($action == "1") {
                $this->archiveOrders($orderStatuses, $formatDate);
            } elseif ($action == "0") {
                $this->deleteOrders($scope, $orderStatuses, $formatDate);
            }
        }
        $this->registry->unregister('isSecureArea');
    }

    public function archiveOrders($orderStatuses, $formatDate)
    {
        $orderCollection = $this->orderCollection->create();
        //join the archive table to check whether the order has already been archived.
        $orderCollection->joinArchiveTable()
            ->addFieldToFilter('updated_at', ['lteq' => $formatDate])
            ->addFieldToFilter('status', ['in' => $orderStatuses])
            ->load();

        if ($orderCollection->getSize()) {
            foreach ($orderCollection as $order) {
                $this->helper->archiveOrder($order->getId());
            }
        }
    }

    public function deleteOrders($scope, $orderStatuses, $formatDate)
    {
        $orderCollection = $this->orderCollection->create();
        if ($scope == "1") {
            $orderCollection->addFieldToFilter('updated_at', ['lteq' => $formatDate]);
        } elseif ($scope == "0") {
            $orderCollection->joinArchiveTable(["order_id", "archived_at"], true)
                ->addFieldToFilter('archived_at', ['lteq' => $formatDate]);
        }
        $orderCollection->addFieldToFilter('status', ['in' => $orderStatuses])->load();
        if ($orderCollection->getSize()) {
            foreach ($orderCollection as $order) {
                $this->helper->deleteOrder($order->getId());
            }
        }
    }
}
