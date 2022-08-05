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
use Aitoc\DeleteOrders\Helper\Data;

/**
 * Class Archive
 *
 * @package Aitoc\DeleteOrders\Controller\Adminhtml\ArchiveOrder
 */

class Archive extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aitoc_DeleteOrders::archive_action';

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Archive constructor.
     * @param Context $context
     * @param Data $helper
     */
    public function __construct(Context $context, Data $helper)
    {
        $this->helper = $helper;
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
                $this->helper->archiveOrder($orderId);
                $this->messageManager->addSuccessMessage(__('Order was successfully archived'));
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
