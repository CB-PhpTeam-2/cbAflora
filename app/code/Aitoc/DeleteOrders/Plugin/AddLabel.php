<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2019 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DeleteOrders
 */

/**
 * Copyright © 2018 Aitoc. All rights reserved.
 */

namespace Aitoc\DeleteOrders\Plugin;

use Aitoc\DeleteOrders\Model\ArchiveRepository;
use Magento\Backend\Model\View\Result\Page as ResultPage;

/**
 * Class AddLabel
 * Class provides after Plugin on Magento\Sales\Controller\Adminhtml\Order\View::execute
 * to replace add "archived" label to order vew
 * @package Aitoc\DeleteOrders\Plugin
 */
class AddLabel
{
    /**
     * @var ArchiveRepository
     */
    protected $archiveRepository;

    /**
     * AddLabel constructor.
     *
     * @param ArchiveRepository $archiveRepository
     */

    public function __construct(ArchiveRepository $archiveRepository)
    {
        $this->archiveRepository = $archiveRepository;
    }

    /**
     * @param $subject
     * @param $result
     * @return ResultPage|\Magento\Backend\Model\View\Result\Redirect
     */
    public function afterExecute($subject, $result)
    {
        if ($result instanceof ResultPage) {
            $archiveMessage = __("Archived");
            $orderId = $subject->getRequest()->getParam("order_id");
            $order = $this->archiveRepository->getByOrderId($orderId);
            if ($order->getId()) {
                $currentTitle = $result->getConfig()->getTitle()->getShort() . " " . $archiveMessage;
                $result->getConfig()->getTitle()->prepend($currentTitle);
            }
        }
        return $result;
    }
}
