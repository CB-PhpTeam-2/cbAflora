<?php
/**
 * @category  Webkul
 * @package   Webkul_AdvancedLayeredNavigation
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\AdvancedLayeredNavigation\Api\Data;

interface CarouselFilterAttributesInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const ID = 'entity_id';
    const ATTRIBUTE_CODE = 'attribute_code';
    const CATEGORIES = 'categories';
    const CREATED_AT = 'created_at';
    const TITLE = 'title';

    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId();

    /**
     * set ID.
     *
     * @return $this
     */
    public function setId($entityId);

    /**
     * Get AttributeCode.
     * @return string
     */
    public function getAttributeCode();

    /**
     * set AttributeCode.
     * @return $this
     */
    public function setAttributeCode($attributeCode);

    /**
     * Get categories.
     * @return string
     */
    public function getCategories();

    /**
     * set categories.
     * @return $this
     */
    public function setCategories($categories);

    /**
     * Get CreatedAt.
     * @return string
     */
    public function getCreatedAt();

    /**
     * set CreatedAt.
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get getTitle.
     * @return string
     */
    public function getTitle();

    /**
     * set CreatedAt.
     * @return $this
     */
    public function setTitle($title);
}
