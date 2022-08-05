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

interface RulesInterface
{
    /**
     * Constants defined for keys of data array
     */
    const ENTITY_ID = 'entity_id';
    const TITLE = 'title';
    const SCOPE = 'scope';
    const ORDER_STATUSES = 'order_statuses';
    const ACTION = 'action';
    const TIME = 'time';
    const IS_ACTIVE = 'is_active';

    /**
     * Returns entity_id field
     *
     * @return int
     */
    public function getEntityId();

    /**
     * @param int $entity_id
     *
     * @return $this
     */
    public function setEntityId($entity_id);

    /**
     * Returns title field
     *
     * @return string
     */
    public function getTitle();

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title);

    /**
     * Returns scope field
     *
     * @return int
     */
    public function getScope();

    /**
     * @param int $scope
     *
     * @return $this
     */
    public function setScope($scope);

    /**
     * Returns order_statuses field
     *
     * @return string
     */
    public function getOrderStatues();

    /**
     * @param string $statuses
     *
     * @return $this
     */
    public function setOrderStatues($statuses);

    /**
     * Returns action field
     *
     * @return int
     */
    public function getAction();

    /**
     * @param int $action
     *
     * @return $this
     */
    public function setAction($action);

    /**
     * Returns time field
     *
     * @return int
     */
    public function getTime();

    /**
     * @param int $time
     *
     * @return $this
     */
    public function setTime($time);

    /**
     * Returns action field
     *
     * @return bool
     */
    public function getIsActive();

    /**
     * @param bool $is_active
     *
     * @return $this
     */
    public function setIsActive($is_active);
}
