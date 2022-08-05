<?php
/**
 * @category  Webkul
 * @package   Webkul_AdvancedLayeredNavigation
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\AdvancedLayeredNavigation\Model;

use Webkul\AdvancedLayeredNavigation\Api\Data\CarouselFilterAttributesInterface;
use Magento\Framework\DataObject\IdentityInterface;

class CarouselFilterAttributes extends \Magento\Framework\Model\AbstractModel implements CarouselFilterAttributesInterface //, IdentityInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'wk_layered_carousel_attributes';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_layered_carousel_attributes';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_layered_carousel_attributes';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\AdvancedLayeredNavigation\Model\ResourceModel\CarouselFilterAttributes::class);
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
     * Get attributeCode.
     *
     * @return varchar
     */
    public function getAttributeCode()
    {
        return $this->getData(self::ATTRIBUTE_CODE);
    }

    /**
     * Set attributeCode.
     */
    public function setAttributeCode($attributeCode)
    {
        return $this->setData(self::ATTRIBUTE_CODE, $attributeCode);
    }

    /**
     * Get categories.
     *
     * @return varchar
     */
    public function getCategories()
    {
        return $this->getData(self::CATEGORIES);
    }

    /**
     * Set categories.
     */
    public function setCategories($categories)
    {
        return $this->setData(self::CATEGORIES, $categories);
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

    /**
     * Get getTitle.
     *
     * @return varchar
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Set setTitle.
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }
}
