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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CarouselFilterRepository implements \Webkul\AdvancedLayeredNavigation\Api\CarouselFilterRepositoryInterface
{
    /**
     * resource model
     * @var \Webkul\AdvancedLayeredNavigation\Model\ResourceModel\CarouselFilter
     */
    protected $resourceModel;

    public function __construct(
        CarouselFilterFactory $carouselFactory,
        \Webkul\AdvancedLayeredNavigation\Model\ResourceModel\CarouselFilter\CollectionFactory $collectionFactory,
        \Webkul\AdvancedLayeredNavigation\Model\ResourceModel\CarouselFilter $resourceModel
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
