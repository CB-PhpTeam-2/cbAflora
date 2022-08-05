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

interface ShipAreaInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ID = 'entity_id';
    const SELLER_ID = 'seller_id';
    const ADDRESS = 'address';
    const LATITUDE = 'latitude';
    const LONGITUDE = 'longitude';
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
     * Get Address.
     *
     * @return varchar
     */
    public function getAddress();

    /**
     * Set Address.
     */
    public function setAddress($address);

    /**
     * Get Latitude.
     *
     * @return varchar
     */
    public function getLatitude();

    /**
     * Set Latitude.
     */
    public function setLatitude($latitude);

    /**
     * Get Longitude.
     *
     * @return varchar
     */
    public function getLongitude();

    /**
     * Set Longitude.
     */
    public function setLongitude($longitude);

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
