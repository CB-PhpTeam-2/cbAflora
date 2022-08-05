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
namespace Webkul\MpHyperLocal\Model\ResourceModel\Outlet\Front;

use Magento\Framework\Api\Search\SearchResultInterface;
use Webkul\MpHyperLocal\Model\ResourceModel\Outlet\Collection as OutletCollection;

class Collection extends OutletCollection implements SearchResultInterface
{
    protected $aggregations;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        \Webkul\Marketplace\helper\Data $mpHelper,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->mpHelper = $mpHelper;
        $this->setMainTable($mainTable);
    }

    public function getAggregations()
    {
        return $this->aggregations;
    }

    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    public function getSearchCriteria()
    {
        return null;
    }

    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    public function getTotalCount()
    {
        return $this->getSize();
    }

    public function setTotalCount($totalCount)
    {
        return $this;
    }

    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * seller id filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $inventorySourceTable = $this->getTable('inventory_source');
        $mpHelper = $this->mpHelper;
        $sellerId = $mpHelper->getCustomerId();
        $this->getSelect()->join(
            $inventorySourceTable.' as cgf',
            'main_table.source_code = cgf.source_code',
            [
                'name'=>'name',
                'enabled'=>'enabled'
            ]
        )->where('main_table.seller_id = ?', $sellerId);
        parent::_renderFiltersBefore();
    }
    /**
     * inherit
     */
    protected function _initSelect()
    {
        $this->addFilterToMap('source_code', 'main_table.source_code');
        parent::_initSelect();
    }
}
