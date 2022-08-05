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
use Aitoc\DeleteOrders\Model\ArchiveRepository;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Registry;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class MassRestore
 *
 * @package Aitoc\DeleteOrders\Controller\Adminhtml\ArchiveOrder
 */
class MassRestore extends Action
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
     * @var $collectionFactory
     */
    protected $collectionFactory;

    /**
     * @var $filter
     */
    protected $filter;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Restore constructor.
     * @param Context $context
     * @param ArchiveRepository $archiveRepository
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        ArchiveRepository $archiveRepository,
        CollectionFactory $collectionFactory,
        Filter $filter,
        Registry $registry
    ) {
        $this->archiveRepository = $archiveRepository;
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        $this->registry = $registry;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $this->registry->register('mass_action_flag', 1);
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $this->registry->unregister('mass_action_flag');
        $redirectResult = $this->resultRedirectFactory->create();
        try {
            foreach ($collection as $item) {
                $this->archiveRepository->deleteByOrderId($item->getId());
            }
            $this->messageManager->addSuccessMessage(__('%1 order(s) were successfully restored.', $collection->getSize()));
            $redirectResult->setPath('deleteorders/archiveorder');
            return $redirectResult;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $redirectResult->setPath('deleteorders/archiveorder');
        return $redirectResult;

    }
}
