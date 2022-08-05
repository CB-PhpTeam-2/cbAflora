<?php
/**
 * @category  Webkul
 * @package   Webkul_AdvancedLayeredNavigation
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\AdvancedLayeredNavigation\Api;

/**
 * @api
 */
interface CarouselFilterAttributesRepositoryInterface
{
    /**
     * get collection by entity id
     * @param  int $entityId
     * @return object
     */
    public function getCollectionById($entityId);
}
