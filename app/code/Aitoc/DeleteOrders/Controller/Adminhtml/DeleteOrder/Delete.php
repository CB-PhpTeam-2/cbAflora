<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2019 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DeleteOrders
 */

/**
 * Copyright © 2018 Aitoc. All rights reserved.
 */

namespace Aitoc\DeleteOrders\Controller\Adminhtml\DeleteOrder;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Aitoc\DeleteOrders\Model\ArchiveRepository;
use Aitoc\DeleteOrders\Helper\Data;
use Magento\Framework\Exception\LocalizedException;
use Cb\GreenlineApi\Model\OrderExportHistoryFactory;

/**
 * Class Delete
 *
 * @package Aitoc\DeleteOrders\Controller\Adminhtml\DeleteOrder
 */
class Delete extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aitoc_DeleteOrders::delete_action';

    /**
     * @var ArchiveRepository
     */
    protected $archiveRepository;

    protected $_orderExportHistoryFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Delete constructor.
     * @param Context $context
     * @param ArchiveRepository $archiveRepository
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        ArchiveRepository $archiveRepository,
        OrderExportHistoryFactory $orderExportHistoryFactory,
        Data $helper
    ) {
        $this->archiveRepository = $archiveRepository;
        $this->_orderExportHistoryFactory = $orderExportHistoryFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $redirectResult = $this->resultRedirectFactory->create();
        $redirectRoute = "sales/order";
        if ($orderId = $this->getRequest()->getParam("order_id")) {
            try {
                $archiveOrder = $this->archiveRepository->getByOrderId($orderId);
                if ($archiveOrder->getId()) {
                    $redirectRoute = "deleteorders/archiveorder";
                }
                $this->helper->deleteOrder($orderId);

                $historyFactory = $this->_orderExportHistoryFactory->create()->getCollection()->addFieldToFilter('order_id', $orderId);
                if($historyFactory->getSize() > 0){
                    $historyId = $historyFactory->getFirstItem()->getId();
                    $history = $this->_orderExportHistoryFactory->create()->load($historyId);
                    $history->delete();
                }

                $this->messageManager->addSuccessMessage(__('Order was successfully deleted'));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        $redirectResult->setPath($redirectRoute);
        return $redirectResult;
    }
}
