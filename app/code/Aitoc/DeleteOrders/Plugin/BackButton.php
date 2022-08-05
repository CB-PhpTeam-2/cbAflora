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

/**
 * Class BackButton
 * Class provides after Plugin on Magento\Sales\Block\Adminhtml\Order\View::getBackUrl
 * to replace "back" button url on order view
 * @package Aitoc\DeleteOrders\Plugin
 */
class BackButton
{
    /**
     * @var ArchiveRepository
     */
    protected $archiveRepository;

    /**
     * @param ArchiveRepository $archiveRepository
     */
    public function __construct(ArchiveRepository $archiveRepository)
    {
        $this->archiveRepository = $archiveRepository;
    }

    /**
     * @param $subject
     * @param $url
     * @return string
     */
    public function afterGetBackUrl($subject, $url)
    {
        $order_id = $subject->getRequest()->getParam("order_id");
        $order = $this->archiveRepository->getByOrderId($order_id);
        if ($order->getId()) {
            return $subject->getUrl("deleteorders/archiveorder");
        }
        return $url;
    }
}
