<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2019 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DeleteOrders
 */

/**
 * Copyright © 2018 Aitoc. All rights reserved.
 */

namespace Aitoc\DeleteOrders\Controller\Adminhtml\ArchiveOrder;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Aitoc\DeleteOrders\Model\ArchiveRepository;

/**
 * Class Restore
 *
 * @package Aitoc\DeleteOrders\Controller\Adminhtml\ArchiveOrder
 */
class Restore extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aitoc_DeleteOrders::restore_action';

    /**
     * @var ArchiveRepository
     */
    protected $archiveRepository;

    /**
     * Restore constructor.
     * @param Context $context
     * @param ArchiveRepository $archiveRepository
     */
    public function __construct(Context $context, ArchiveRepository $archiveRepository)
    {
        $this->archiveRepository = $archiveRepository;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $redirectResult = $this->resultRedirectFactory->create();
        if ($orderId = $this->getRequest()->getParam("order_id")) {
            try {
                $this->archiveRepository->deleteByOrderId($orderId);
                $this->messageManager->addSuccessMessage(__('Order was successfully restored'));
                $redirectResult->setPath('sales/order/view', ["order_id" => $orderId]);
                return $redirectResult;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        $redirectResult->setPath('sales/order');
        return $redirectResult;

    }
}
