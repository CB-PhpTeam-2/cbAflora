<?php
/**
 * @category  Webkul
 * @package   Webkul_AdvancedLayeredNavigation
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\AdvancedLayeredNavigation\Model\ResourceModel;

/**
 * AdvancedLayeredNavigation CarouselFilter mysql resource.
 */
class CarouselFilter extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('wk_layered_carousel_options', 'entity_id');
    }
}
