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

use Aitoc\DeleteOrders\Api\Data\RulesInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Rules
 *
 * @package Aitoc\DeleteOrders\Model
 */
class Rules extends AbstractModel implements RulesInterface
{
    /**
     * Class constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Aitoc\DeleteOrders\Model\ResourceModel\Rules');
        $this->setIdFieldName('entity_id');
    }

    /**
     * @return int entityId
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @param int $entityId
     *
     * @return $this
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * @return string title
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @return int scope
     */
    public function getScope()
    {
        return $this->getData(self::SCOPE);
    }

    /**
     * @param int $scope
     *
     * @return $this
     */
    public function setScope($scope)
    {
        return $this->setData(self::SCOPE, $scope);
    }

    /**
     * @return string statuses
     */
    public function getOrderStatues()
    {
        return $this->getData(self::ORDER_STATUSES);
    }

    /**
     * @param string $statuses
     *
     * @return $this
     */
    public function setOrderStatues($statuses)
    {
        return $this->setData(self::ORDER_STATUSES, $statuses);
    }

    /**
     * @return int action
     */
    public function getAction()
    {
        return $this->getData(self::ACTION);
    }

    /**
     * @param int $action
     *
     * @return $this
     */
    public function setAction($action)
    {
        return $this->setData(self::ACTION, $action);
    }

    /**
     * @return int time
     */
    public function getTime()
    {
        return $this->getData(self::TIME);
    }

    /**
     * @param int $time
     *
     * @return $this
     */
    public function setTime($time)
    {
        return $this->setData(self::TIME, $time);
    }

    /**
     * @return bool is_active
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * @param bool $is_active
     *
     * @return $this
     */
    public function setIsActive($is_active)
    {
        return $this->setData(self::IS_ACTIVE, $is_active);
    }
}
