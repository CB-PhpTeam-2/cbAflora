<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2019 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DeleteOrders
 */

/**
 * Copyright © 2018 Aitoc. All rights reserved.
 */

namespace Aitoc\DeleteOrders\Api\Data;

interface ArchiveInterface
{
    /**
     * Constants defined for keys of data array
     */
    const RECORD_ID = 'record_id';
    const ORDER_ID = 'order_id';
    const ARCHIVED_AT = 'archived_at';

    /**
     * Returns record_id field
     *
     * @return int
     */
    public function getRecordId();

    /**
     * @param int $record_id
     *
     * @return $this
     */
    public function setRecordId($record_id);

    /**
     * Returns orderId field
     *
     * @return int
     */
    public function getOrderId();

    /**
     * @param int $order_id
     *
     * @return $this
     */
    public function setOrderId($order_id);

    /**
     * Returns archived_at field
     *
     * @return string
     */
    public function getArchivedAt();

    /**
     * @param string $date
     *
     * @return $this
     */
    public function setArchivedAt($date);
}
