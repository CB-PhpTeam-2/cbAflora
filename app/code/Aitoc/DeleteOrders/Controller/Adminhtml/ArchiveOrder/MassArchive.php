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
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Aitoc\DeleteOrders\Helper\Data;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class MassArchive
 *
 * @package Aitoc\DeleteOrders\Controller\Adminhtml\ArchiveOrder
 */
class MassArchive extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aitoc_DeleteOrders::archive_action';

    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var $filter
     */
    protected $filter;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * MassArchive constructor.
     * @param Context $context
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param Filter $filter
     */
    public function __construct(
        Context $context,
        OrderCollectionFactory $orderCollectionFactory,
        Filter $filter,
        Data $helper
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->filter = $filter;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->orderCollectionFactory->create());
        $redirectResult = $this->resultRedirectFactory->create();
        try {
            foreach ($collection as $item) {
                $this->helper->archiveOrder($item->getId());
            }
            $this->messageManager->addSuccessMessage(__('%1 order(s) were successfully archived.', $collection->getSize()));
            $redirectResult->setPath('sales/order');
            return $redirectResult;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $redirectResult->setPath('sales/order');
        return $redirectResult;

    }
}
