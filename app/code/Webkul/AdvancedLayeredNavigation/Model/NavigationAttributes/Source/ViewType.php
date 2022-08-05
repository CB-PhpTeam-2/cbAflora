<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AdvancedLayeredNavigation
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\AdvancedLayeredNavigation\Model\NavigationAttributes\Source;

class ViewType implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Webkul\AdvancedLayeredNavigation\Model\Manageattribute
     */
    protected $attributes;

    /**
     * value for allow option in config dropdown
     */
    const VERTICAL = 0;

    /**
     * value forrestrict option in config dropdown
     */
    const HORIZONTAL = 1;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::VERTICAL , 'label' => __('Allow Vertical')],
            ['value' => self::HORIZONTAL , 'label' => __('Allow Horizontal')]
        ];
    }
}
