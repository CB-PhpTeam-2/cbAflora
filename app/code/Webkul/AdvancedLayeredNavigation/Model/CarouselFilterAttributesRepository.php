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
use Webkul\AdvancedLayeredNavigation\Model\ResourceModel\CarouselFilter\Collection;
use Webkul\AdvancedLayeredNavigation\Model\ResourceModel\CarouselFilterAttributes\CollectionFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CarouselFilterAttributesRepository implements
    \Webkul\AdvancedLayeredNavigation\Api\CarouselFilterAttributesRepositoryInterface
{
    /**
     * resource model
     * @var \Webkul\AdvancedLayeredNavigation\Model\ResourceModel\CarouselFilterAttributes
     */
    protected $resourceModel;

    public function __construct(
        CarouselFilterAttributesFactory $carouselFactory,
        CollectionFactory $collectionFactory,
        \Webkul\AdvancedLayeredNavigation\Model\ResourceModel\CarouselFilterAttributes $resourceModel
    ) {
        $this->resourceModel = $resourceModel;
        $this->carouselFactory = $carouselFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * get collection by entity id
     * @param  int $entityId
     * @return object
     */
    public function getCollectionById($entityId)
    {
        $carouselDetails = $this->carouselFactory->create()->load($entityId);
        return $carouselDetails;
    }
}
