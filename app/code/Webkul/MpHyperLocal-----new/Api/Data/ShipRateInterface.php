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
namespace Webkul\MpHyperLocal\Api\Data;

interface ShipRateInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ID = 'entity_id';
    const SELLER_ID = 'seller_id';
    const DISTANCE_FROM = 'distance_from';
    const DISTANCE_TO = 'distance_to';
    const WEIGHT_FROM = 'weight_from';
    const WEIGHT_TO = 'weight_to';
    const COST = 'cost';
    const CREATED_AT = 'created_at';

    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getId();

    /**
     * Set EntityId.
     */
    public function setId($id);

    /**
     * Get getSellerId.
     *
     * @return varchar
     */
    public function getSellerId();

    /**
     * Set SellerId.
     */
    public function setSellerId($sellerId);

    /**
     * Get DistanceFrom.
     *
     * @return decimal
     */
    public function getDistanceFrom();

    /**
     * Set DistanceFrom.
     */
    public function setDistanceFrom($distanceFrom);

    /**
     * Get DistanceTo.
     *
     * @return decimal
     */
    public function getDistanceTo();

    /**
     * Set DistanceTo.
     */
    public function setDistanceTo($distanceTo);

    /**
     * Get WeightFrom.
     *
     * @return decimal
     */
    public function getWeightFrom();

    /**
     * Set WeightFrom.
     */
    public function setWeightFrom($weightFrom);

    /**
     * Get WeightTo.
     *
     * @return decimal
     */
    public function getWeightTo();

    /**
     * Set WeightTo.
     */
    public function setWeightTo($weightTo);

    /**
     * Get Cost.
     *
     * @return decimal
     */
    public function getCost();

    /**
     * Set Cost.
     */
    public function setCost($cost);

    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getCreatedAt();

    /**
     * Set CreatedAt.
     */
    public function setCreatedAt($createdAt);
}
