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
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Cb\GreenlineApi\Model\OrderExportHistoryFactory;
use Magento\Ui\Component\MassAction\Filter;
use Aitoc\DeleteOrders\Helper\Data;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class MassDelete
 *
 * @package Aitoc\DeleteOrders\Controller\Adminhtml\DeleteOrder
 */
class MassDelete extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Aitoc_DeleteOrders::delete_action';

    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;

    protected $_orderExportHistoryFactory;
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param Filter $filter
     * @param Data $helper
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        OrderCollectionFactory $orderCollectionFactory,
        OrderExportHistoryFactory $orderExportHistoryFactory,
        Filter $filter,
        Data $helper,
        Registry $registry
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->_orderExportHistoryFactory = $orderExportHistoryFactory;
        $this->filter = $filter;
        $this->helper = $helper;
        $this->registry = $registry;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $this->registry->register('mass_action_flag', 1);
        $collection = $this->filter->getCollection($this->orderCollectionFactory->create());
        $this->registry->unregister('mass_action_flag');
        $redirectResult = $this->resultRedirectFactory->create();
        $collectionSize = $collection->getSize();
        try {
            foreach ($collection as $item) {
                $this->helper->deleteOrder($item->getId());

                $historyFactory = $this->_orderExportHistoryFactory->create()->getCollection()->addFieldToFilter('order_id', $item->getId());
                if($historyFactory->getSize() > 0){
                    $historyId = $historyFactory->getFirstItem()->getId();
                    $history = $this->_orderExportHistoryFactory->create()->load($historyId);
                    $history->delete();
                }
            }
            $this->messageManager->addSuccessMessage(__('%1 order(s) have been deleted.', $collectionSize));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $redirectResult->setUrl($this->_redirect->getRefererUrl());
        return $redirectResult;

    }
}
