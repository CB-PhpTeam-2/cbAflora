<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpHyperLocal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpHyperLocal\Controller\Outlet;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class Massdisable extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    const STATUS = 0;
    const TABLE_NAME = 'inventory_source';
    const CUSTOM_TABLE_NAME = 'marketplace_seller_outlet';

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    private $filter;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Webkul\MpHyperLocal\Model\ResourceModel\Outlet\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\MpHyperLocal\Model\ResourceModel\Outlet\CollectionFactory $collectionFactory
     * @param \Magento\Customer\Model\Url $modelUrl
     */
    public function __construct(
        \Webkul\Marketplace\Helper\Data $helperData,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MpHyperLocal\Model\ResourceModel\Outlet\CollectionFactory $collectionFactory,
        \Magento\Customer\Model\Url $modelUrl,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->filter = $filter;
        $this->helperData = $helperData;
        $this->_customerSession  = $customerSession;
        $this->collectionFactory = $collectionFactory;
        $this->modelUrl = $modelUrl;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        parent::__construct($context);
    }

    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }


    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->modelUrl->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    public function execute()
    {
        $isPartner = $this->helperData->isSeller();
        if ($isPartner == 1) {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $sourceCodes = [];
            $entityIds = [];
            foreach ($collection as $source) {
                $sourceCodes[] = $source->getSourceCode();
                $entityIds[] = $source->getEntityId();
            }
            $update = ['enabled' => self::STATUS];
            $customUpdate = ['status' => self::STATUS];
            $customWhere = ['entity_id IN (?)' => $entityIds];
            $where = ['source_code IN (?)' => $sourceCodes];
            try {
                $this->connection->beginTransaction();
                $this->connection->update($this->resource->getTableName(self::TABLE_NAME), $update, $where);
                $this->connection->update($this->resource->getTableName(self::CUSTOM_TABLE_NAME), $customUpdate, $customWhere);
                $this->connection->commit();
                $this->messageManager->addSuccess(__("A total of %1 outlet(s) were disabled.", $collection->getSize()));
            } catch (\Exception $e) {
                $this->connection->rollBack();
            }
            $this->_redirect("*/*/index");
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
