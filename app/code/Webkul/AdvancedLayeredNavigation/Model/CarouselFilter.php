<?php
/**
 * @category  Webkul
 * @package   Webkul_AdvancedLayeredNavigation
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\AdvancedLayeredNavigation\Model;

use Webkul\AdvancedLayeredNavigation\Api\Data\CarouselFilterInterface;
use Magento\Framework\DataObject\IdentityInterface;

class CarouselFilter extends \Magento\Framework\Model\AbstractModel implements CarouselFilterInterface //, IdentityInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'wk_layered_carousel_options';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_layered_carousel_options';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_layered_carousel_options';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\AdvancedLayeredNavigation\Model\ResourceModel\CarouselFilter::class);
    }
    /**
     * Get Id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set Id.
     */
    public function setId($entityId)
    {
        return $this->setData(self::ID, $entityId);
    }

    /**
     * Get title.
     *
     * @return int
     */
    public function getCarouselId()
    {
        return $this->getData(self::CAROUSELID);
    }

    /**
     * Set title.
     */
    public function setCarouselId($carouselId)
    {
        return $this->setData(self::CAROUSELID, $carouselId);
    }

    /**
     * Get ImagePath.
     *
     * @return varchar
     */
    public function getImagePath()
    {
        return $this->getData(self::IMAGE_PATH);
    }

    /**
     * Set ImagePath.
     */
    public function setImagePath($imagePath)
    {
        return $this->setData(self::IMAGE_PATH, $imagePath);
    }

    /**
     * Get attributeCode.
     *
     * @return varchar
     */
    public function getAttributeOptionId()
    {
        return $this->getData(self::OPTIONID);
    }

    /**
     * Set attributeCode.
     */
    public function setAttributeOptionId($optionId)
    {
        return $this->setData(self::OPTIONID, $optionId);
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
