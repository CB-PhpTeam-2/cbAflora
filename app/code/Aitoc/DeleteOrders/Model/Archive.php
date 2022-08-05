<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2019 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DeleteOrders
 */

/**
 * Copyright © 2018 Aitoc. All rights reserved.
 */

namespace Aitoc\DeleteOrders\Model;

use Aitoc\DeleteOrders\Api\Data\ArchiveInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Archive
 *
 * @package Aitoc\DeleteOrders\Model
 */
class Archive extends AbstractModel implements ArchiveInterface
{
    /**
     * Class constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Aitoc\DeleteOrders\Model\ResourceModel\Archive');
        $this->setIdFieldName('record_id');
    }

    /**
     * @return int recordId
     */
    public function getRecordId()
    {
        return $this->getData(self::RECORD_ID);
    }

    /**
     * @param int $recordId
     *
     * @return $this
     */
    public function setRecordId($recordId)
    {
        return $this->setData(self::RECORD_ID, $recordId);
    }

    /**
     * @return int orderId
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @param int $orderId
     *
     * @return $this
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @return string archived_at
     */
    public function getArchivedAt()
    {
        return $this->getData(self::ARCHIVED_AT);
    }

    /**
     * @param string $date
     *
     * @return $this
     */
    public function setArchivedAt($date)
    {
        return $this->setData(self::ARCHIVED_AT, $date);
    }
}
