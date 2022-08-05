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

use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Context;
use Aitoc\DeleteOrders\Model\ArchiveRepository;

/**
 * Class OrderButtons
 *
 * @package Aitoc\DeleteOrders\Plugin
 */
class OrderButtons
{
    /**
     * Authorization level of a basic admin session
     *
     */
    const ADMIN_ACL_RESOURCE_PREFIX = 'Aitoc_DeleteOrders::';

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ArchiveRepository
     */
    protected $archiveRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var int
     */
    protected $orderId;

    /**
     * @var \Magento\Backend\Block\Widget\Button\ButtonList
     */
    protected $buttonList;

    /**
     * @param Context $context
     * @param ArchiveRepository $archiveRepository
     */
    public function __construct(Context $context, ArchiveRepository $archiveRepository)
    {
        $this->context = $context;
        $this->request = $context->getRequest();
        $this->archiveRepository = $archiveRepository;
        $this->authorization = $context->getAuthorization();
        $this->orderId =  $this->request->getParam("order_id");
    }

    /**
     * @param Context $context
     * @param ButtonList $buttonList
     * @returm ButtonList
     */
    public function afterGetButtonList(Context $context, ButtonList $buttonList)
    {
        $this->buttonList = $buttonList;
        $archivedOrder = $this->archiveRepository->getByOrderId($this->orderId);
        if ($this->request->getFullActionName() == 'sales_order_view') {
            $this->generateButton('delete');
            if ($archivedOrder->getId()) {
                $this->generateButton('restore');
            } else {
                $this->generateButton('archive');
            }
        }
        return $this->buttonList;
    }

    /**
     * @param ButtonList $buttonList
     * @param $action
     */
    private function generateButton($action)
    {
        if ($this->authorization->isAllowed(self::ADMIN_ACL_RESOURCE_PREFIX . $action . '_action')) {
            $urlPath = $action == 'delete' ? 'deleteorders/deleteorder/' : 'deleteorders/archiveorder/';
            $message = __('Are you sure you want to ' . $action . ' an order?');
            $this->buttonList->add(
                $action . '_button',
                [
                    'label' => __(ucfirst($action)),
                    'onclick' => "confirmSetLocation('{$message}', '{$this->createUrl($urlPath . $action, $this->orderId)}')",
                ]
            );
        }
    }

    /**
     * @param string $path
     * @param int $order_id
     * @return string
     */
    public function createUrl($path, $order_id)
    {
        return  $this->context->getUrlBuilder()->getUrl($path, ['order_id' => $order_id]);
    }
}
