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
namespace Webkul\MpHyperLocal\Model;

use Webkul\MpHyperLocal\Api\Data\ShipRateInterface;
use Magento\Framework\Model\AbstractModel;

class ShipRate extends AbstractModel implements ShipRateInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'mp_hyper_local_shipping_rate';

    /**
     * @var string
     */
    protected $_cacheTag = 'mp_hyper_local_shipping_rate';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'mp_hyper_local_shipping_rate';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpHyperLocal\Model\ResourceModel\ShipRate::class);
    }
    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set EntityId.
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Get getSellerId.
     *
     * @return varchar
     */
    public function getSellerId()
    {
        return $this->getData(self::SELLER_ID);
    }

    /**
     * Set SellerId.
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }

    /**
     * Get DistanceFrom.
     *
     * @return decimal
     */
    public function getDistanceFrom()
    {
        return $this->getData(self::DISTANCE_FROM);
    }

    /**
     * Set DistanceFrom.
     */
    public function setDistanceFrom($distanceFrom)
    {
        return $this->setData(self::DISTANCE_FROM, $distanceFrom);
    }

    /**
     * Get DistanceTo.
     *
     * @return decimal
     */
    public function getDistanceTo()
    {
        return $this->getData(self::DISTANCE_TO);
    }

    /**
     * Set DistanceTo.
     */
    public function setDistanceTo($distanceTo)
    {
        return $this->setData(self::DISTANCE_TO, $distanceTo);
    }

    /**
     * Get WeightFrom.
     *
     * @return decimal
     */
    public function getWeightFrom()
    {
        return $this->getData(self::WEIGHT_FROM);
    }

    /**
     * Set WeightFrom.
     */
    public function setWeightFrom($weightFrom)
    {
        return $this->setData(self::WEIGHT_FROM, $weightFrom);
    }

    /**
     * Get WeightTo.
     *
     * @return decimal
     */
    public function getWeightTo()
    {
        return $this->getData(self::WEIGHT_TO);
    }

    /**
     * Set WeightTo.
     */
    public function setWeightTo($weightTo)
    {
        return $this->setData(self::WEIGHT_TO, $weightTo);
    }

    /**
     * Get Cost.
     *
     * @return decimal
     */
    public function getCost()
    {
        return $this->getData(self::COST);
    }

    /**
     * Set Cost.
     */
    public function setCost($cost)
    {
        return $this->setData(self::COST, $cost);
    }

    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set CreatedAt.
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
