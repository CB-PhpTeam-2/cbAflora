<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2019 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DeleteOrders
 */

/**
 * Copyright © 2018 Aitoc. All rights reserved.
 */

namespace Aitoc\DeleteOrders\Model\ResourceModel\Order;

use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;

class Collection extends OrderCollection
{
    /**
     * Add archive table to collection
     *
     * @param $fieldsToSelect
     * @param bool $isArchived If it is true, order collection will contain orders which in archive, otherwise it will contain only orders which are not archived.
     * @return $collection
     */
    public function joinArchiveTable($fieldsToSelect = ['order_id'], $isArchived = false)
    {
        $this->getSelect()->joinLeft(
            ['archive_table' => $this->getTable(\Aitoc\DeleteOrders\Model\ResourceModel\Archive::TABLE_NAME)],
            'main_table.entity_id = archive_table.order_id',
            $fieldsToSelect
        );
        $this->addFieldToFilter('archive_table.order_id', [$isArchived ? 'notnull' : 'null' => true]);
        return $this;
    }
}
