<?php
/**
 * @category  Webkul
 * @package   Webkul_AdvancedLayeredNavigation
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\AdvancedLayeredNavigation\Api\Data;

interface CarouselFilterInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const ID = 'entity_id';
    const IMAGE_PATH = 'image_path';
    const CAROUSELID = 'carousel_id';
    const CREATED_AT = 'created_at';
    const OPTIONID = 'attribute_option_id';
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
     * Get getCarouselId.
     * @return string
     */
    public function getCarouselId();

    /**
     * set setCarouselId.
     * @return $this
     */
    public function setCarouselId($carouselId);

    /**
     * Get ImagePath.
     * @return string
     */
    public function getImagePath();

    /**
     * set ImagePath.
     * @return $this
     */
    public function setImagePath($imagePath);

    /**
     * Get AttributeOptionId.
     * @return string
     */
    public function getAttributeOptionId();

    /**
     * set AttributeOptionId.
     * @return $this
     */
    public function setAttributeOptionId($optionId);
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
}
