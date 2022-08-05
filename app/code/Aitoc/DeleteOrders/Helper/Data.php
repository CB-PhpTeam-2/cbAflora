<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2019 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DeleteOrders
 */

/**
 * Copyright © 2018 Aitoc. All rights reserved.
 */

namespace Aitoc\DeleteOrders\Helper;

use Magento\Sales\Model\Order\InvoiceRepository;
use Magento\Sales\Model\Order\CreditmemoRepository;
use Magento\Sales\Model\Order\ShipmentRepository;
use Aitoc\DeleteOrders\Model\ArchiveFactory;
use Aitoc\DeleteOrders\Model\ArchiveRepository;
use Magento\Sales\Model\OrderRepository;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Data
 *
 * @package Aitoc\DeleteOrders\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var InvoiceRepository
     */
    protected $invoiceRepository;

    /**
     * @var CreditmemoRepository
     */
    protected $creditMemoRepository;

    /**
     * @var ShipmentRepository
     */
    protected $shipmentRepository;

    /**
     * @var ArchiveRepository
     */
    protected $archiveRepository;

    /**
     * @var ArchiveFactory
     */
    protected $archiveFactory;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * Data constructor.
     *
     * @param InvoiceRepository $invoiceRepository
     * @param CreditmemoRepository $creditMemoRepository
     * @param ShipmentRepository $shipmentRepository
     * @param ArchiveRepository $archiveRepository
     * @param ArchiveFactory $archiveFactory
     * @param OrderRepository $orderRepository

     */
    public function __construct(
        InvoiceRepository $invoiceRepository,
        CreditmemoRepository $creditMemoRepository,
        ShipmentRepository $shipmentRepository,
        ArchiveRepository $archiveRepository,
        ArchiveFactory $archiveFactory,
        OrderRepository $orderRepository
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->creditMemoRepository = $creditMemoRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->archiveRepository = $archiveRepository;
        $this->archiveFactory = $archiveFactory;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Delete order entities such as invoices, shipments and credit memos.
     */
    /**
     * @param $order
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteOrderEntities($order)
    {
        $invoices = $order->getInvoiceCollection();
        $shipments = $order->getShipmentsCollection();
        $creditMemos = $order->getCreditmemosCollection();

        foreach ($invoices as $invoice) {
            $this->invoiceRepository->deleteById($invoice->getId());
        }
        foreach ($shipments as $shipment) {
            $this->shipmentRepository->deleteById($shipment->getId());
        }
        foreach ($creditMemos as $creditMemo) {
            $creditMemoEntity = $this->creditMemoRepository->get($creditMemo->getId());
            $this->creditMemoRepository->delete($creditMemoEntity);
        }
    }

    /**
     * @param $orderId
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function archiveOrder($orderId)
    {
        $archiveModel = $this->archiveFactory->create();
        $archiveModel->addData([
            'order_id' => $orderId
        ]);
        $this->archiveRepository->save($archiveModel);

        return true;
    }

    /**
     * @param $orderId
     * @return bool
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteOrder($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        $this->deleteOrderEntities($order);
        $this->orderRepository->delete($order);

        return true;
    }
}
